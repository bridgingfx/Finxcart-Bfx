<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait FileManagerTrait
{
    /**
     * upload method working for image
     * @param string $dir
     * @param string $format
     * @param $image
     * @return string
     */
    protected function upload(string $dir, string $format, $image = null): string
    {
        $storage = config('filesystems.disks.default') ?? 'public';

        if (!is_null($image)) {
            if (!$this->checkFileExists($dir)['status']) {
                Storage::disk($storage)->makeDirectory($dir);
            }

            $originalExtension = strtolower($image->getClientOriginalExtension());
            if ($originalExtension === 'svg') {
                // SVG is XML and can carry executable scripts; sanitise before storing to prevent stored XSS.
                $imageName = Carbon::now()->toDateString() . "-" . uniqid() . ".svg";
                Storage::disk($storage)->put($dir . $imageName, $this->sanitizeSvg(file_get_contents($image)));
                $this->copyToPublicStorage($storage, $dir, $imageName);
            } else {
                if (in_array(request()->ip(), ['127.0.0.1', '::1']) && !(imagetypes() & IMG_WEBP) || env('APP_DEBUG') && !(imagetypes() & IMG_WEBP)) {
                    $format = 'png';
                }
                $imageWebp = Image::make($image)->encode($format);
                $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
                Storage::disk($storage)->put($dir . $imageName, $imageWebp);
                $imageWebp->destroy();
                $this->copyToPublicStorage($storage, $dir, $imageName);
            }
        } else {
            $imageName = 'def.png';
        }

        cacheRemoveByType(type: 'file_manager');
        return $imageName;
    }

    /**
     * Strip scripting from SVG markup to neutralise stored-XSS payloads.
     * @param string $svg
     * @return string
     */
    protected function sanitizeSvg(string $svg): string
    {
        // remove <script> blocks
        $svg = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $svg);
        // remove inline event handlers e.g. onload=, onclick=
        $svg = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/is', '', $svg);
        // neutralise javascript:/data: URIs inside href/xlink:href/src
        $svg = preg_replace('/((?:xlink:href|href|src)\s*=\s*)(["\']?)\s*(?:javascript|data)\s*:[^"\'>\s]*/is', '$1$2', $svg);
        // remove <foreignObject> which can embed arbitrary HTML
        $svg = preg_replace('/<foreignObject\b[^>]*>.*?<\/foreignObject>/is', '', $svg);
        return $svg ?? '';
    }

    /**
     * @param string $dir
     * @param string $format
     * @param $file
     * @return string
     */
    public function fileUpload(string $dir, string $format, $file = null): string
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        if (!is_null($file)) {
            // Defence-in-depth: only allow known-safe document/media extensions, never executables.
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'mp3', 'mp4', 'wav', 'epub'];
            $extension = strtolower($format ?: $file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions, true)) {
                abort(403, translate('file_type_not_allowed') . '!');
            }
            $fileName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $extension;
            if (!$this->checkFileExists($dir)['status']) {
                Storage::disk($storage)->makeDirectory($dir);
            }
            if ($file) {
                Storage::disk($storage)->put($dir . $fileName, file_get_contents($file));
                $this->copyToPublicStorage($storage, $dir, $fileName);
            }
        } else {
            $fileName = 'def.png';
        }

        return $fileName;
    }

    /**
     * @param string $dir
     * @param $oldImage
     * @param string $format
     * @param $image
     * @param string $fileType image/file
     * @return string
     */
    public function update(string $dir, $oldImage, string $format, $image, string $fileType = 'image'): string
    {
        if ($this->checkFileExists(filePath: $dir . $oldImage)['status']) {
            Storage::disk($this->checkFileExists(filePath: $dir . $oldImage)['disk'])->delete($dir . $oldImage);
        }
        return $fileType == 'file' ? $this->fileUpload($dir, $format, $image) : $this->upload($dir, $format, $image);
    }

    /**
     * @param string $filePath
     * @return array
     */
    protected function  delete(string $filePath): array
    {
        if ($this->checkFileExists(filePath: $filePath)['status']) {
            Storage::disk($this->checkFileExists(filePath: $filePath)['disk'])->delete($filePath);
        }

        $publicPath = public_path('storage/' . $filePath);
        if (file_exists($publicPath)) {
            unlink($publicPath);
        }

        cacheRemoveByType(type: 'file_manager');
        return [
            'success' => 1,
            'message' => translate('Removed_successfully')
        ];
    }

    public function setStorageConnectionEnvironment(): void
    {
        $storageConnectionType = getWebConfig(name: 'storage_connection_type') ?? 'public';
        Config::set('filesystems.disks.default', $storageConnectionType);
        $storageConnectionS3Credential = getWebConfig(name: 'storage_connection_s3_credential');
        if ($storageConnectionType == 's3' && !empty($storageConnectionS3Credential)) {
            Config::set('filesystems.disks.' . $storageConnectionType, $storageConnectionS3Credential);
        }
    }

    private function copyToPublicStorage(string $disk, string $dir, string $fileName): void
    {
        if ($disk !== 'public') {
            return;
        }

        $sourcePath = storage_path('app/public/' . $dir . $fileName);
        $destPath   = public_path('storage/' . $dir . $fileName);

        if (file_exists($sourcePath) && !file_exists($destPath)) {
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            copy($sourcePath, $destPath);
        }
    }

    /**
     * Copy an already-stored image into another directory instead of re-decoding
     * and re-encoding the original upload — used when the same source image is
     * needed in a second location (e.g. product thumbnail reused as SEO meta
     * image), so it only pays the CPU cost of Image::make()->encode() once.
     */
    protected function duplicateStoredImage(string $sourceDir, string $sourceFileName, string $destDir): string
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $extension = pathinfo($sourceFileName, PATHINFO_EXTENSION) ?: 'webp';
        $destFileName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $extension;

        if (!$this->checkFileExists($destDir)['status']) {
            Storage::disk($storage)->makeDirectory($destDir);
        }

        Storage::disk($storage)->copy($sourceDir . $sourceFileName, $destDir . $destFileName);
        $this->copyToPublicStorage($storage, $destDir, $destFileName);

        return $destFileName;
    }

    private function checkFileExists(string $filePath): array
    {
        if (Storage::disk('public')->exists($filePath)) {
            return [
                'status' => true,
                'disk' => 'public'
            ];
        } elseif (config('filesystems.disks.default') == 's3' && Storage::disk('s3')->exists($filePath)) {
            return [
                'status' => true,
                'disk' => 's3'
            ];
        } else {
            return [
                'status' => false,
                'disk' => config('filesystems.disks.default') ?? 'public'
            ];
        }
    }
}
