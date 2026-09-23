<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait  PdfGenerator
{
    /**
     * Invoice/PDF templates render <img src="..."> tags built via asset()/
     * getStorageImages(), which point back at this app's own public/storage
     * URL. mPDF fetches every <img> src over HTTP during WriteHTML(). When
     * that URL is this same host, the request deadlocks under a
     * single-worker server (php artisan serve) or any low-concurrency PHP
     * worker pool: the fetch queues behind the very request that's trying to
     * make it, and no per-request timeout on either side rescues it because
     * there is no working thread free to answer — this was observed to hang
     * checkout (order confirmation email -> invoice PDF generation) for 90s+
     * with no error logged. Rewriting same-host "{APP_URL}/storage/..."
     * URLs to their local filesystem path before WriteHTML() avoids the
     * network round-trip entirely (mPDF reads local paths directly).
     */
    private static function localizeStorageImageUrls(string $html): string
    {
        $localRoot = str_replace('\\', '/', rtrim(Storage::disk('public')->path(''), '/\\'));

        // Cover every URL base the app might have rendered the <img> src with:
        // the storage disk's configured URL (usually APP_URL-based) and, since
        // asset()/url() can also fall back to the current request's host when
        // APP_URL isn't forced consistently across environments, the running
        // request's own root URL too. Whichever one matches gets rewritten to
        // a local file:// path so mPDF never makes an HTTP round-trip back to
        // this same app to fetch its own image.
        $candidateBases = array_unique(array_filter([
            rtrim((string) config('filesystems.disks.public.url', ''), '/'),
            rtrim(url('/'), '/') . '/storage',
            rtrim(request()?->getSchemeAndHttpHost() ?? '', '/') . '/storage',
        ]));

        foreach ($candidateBases as $base) {
            $html = str_replace($base . '/', 'file:///' . $localRoot . '/', $html);
        }

        return $html;
    }

    public static function generatePdf($view, $filePrefix, $filePostfix, $pdfType = null, $requestFrom = 'admin'): void
    {
        $mpdf = new \Mpdf\Mpdf(['default_font' => 'FreeSerif', 'mode' => 'utf-8', 'format' => [190, 250], 'autoLangToFont' => true]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        if ($pdfType = 'invoice') {
            $footerHtml = self::footerHtml($requestFrom);
            $mpdf->SetHTMLFooter($footerHtml);
        }
        $mpdf_view = $view;
        $mpdf_view = self::localizeStorageImageUrls($mpdf_view->render());
        $mpdf->WriteHTML($mpdf_view);
        $mpdf->Output($filePrefix . $filePostfix . '.pdf', 'D');
    }

    public static function storePdf($view, $filePrefix, $filePostfix, $pdfType = null, $requestFrom = 'admin'): string
    {
        $mpdf = new \Mpdf\Mpdf(['default_font' => 'FreeSerif', 'mode' => 'utf-8', 'format' => [190, 250], 'autoLangToFont' => true]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        if ($pdfType = 'invoice') {
            $footerHtml = self::footerHtml($requestFrom);
            $mpdf->SetHTMLFooter($footerHtml);
        }
        $mpdf_view = $view;
        $mpdf_view = self::localizeStorageImageUrls($mpdf_view->render());
        $mpdf->WriteHTML($mpdf_view);

        $fileName = $filePrefix . $filePostfix . '.pdf';
        $directory = 'invoices';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        $filePath = Storage::disk('public')->path($directory . '/' . $fileName);
        $mpdf->Output($filePath, \Mpdf\Output\Destination::FILE);
        return $filePath;
    }

    public static function footerHtml(string $requestFrom): string
    {
        $getCompanyPhone = getWebConfig(name: 'company_phone');
        $getCompanyEmail = getWebConfig(name: 'company_email');
        if ($requestFrom == 'web' && theme_root_path() == 'theme_aster' || theme_root_path() == 'theme_fashion') {
            return '<div style="width:560px;margin: 0 auto;background-color: #1455AC">
                <table class="fs-10">
                    <tr>
                        <td style="padding: 10px">
                            <span style="color:#ffffff;">' . url('/') . '</span>
                        </td>
                        <td style="padding: 10px">
                            <span style="color:#ffffff;">' . $getCompanyPhone . '</span>
                        </td>
                        <td style="padding: 10px">
                            <span style="color:#ffffff;">' . $getCompanyEmail . '</span>
                        </td>
                    </tr>
                </table>
            </div>';
        } else {
            return '<div style="width:520px;margin: 0 auto;background-color: #F2F4F7;padding: 11px 19px 10px 32px;">
            <table class="fs-10">
                <tr>
                    <td>
                        <span>' . url('/') . '</span>
                    </td>
                    <td>
                        <span>' . $getCompanyPhone . '</span>
                    </td>
                    <td>
                        <span>' . $getCompanyEmail . '</span>
                    </td>
                </tr>
            </table>
        </div>';
        }

    }
}
