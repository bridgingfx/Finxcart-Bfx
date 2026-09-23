<?php

namespace App\Services;

use App\Models\FreelancerContract;
use App\Models\FreelancerContractMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class FreelancerContractMessageService
{
    /**
     * @param UploadedFile[] $files
     */
    public function send(FreelancerContract $contract, string $senderType, int $senderId, ?string $body, array $files = []): FreelancerContractMessage
    {
        return DB::transaction(function () use ($contract, $senderType, $senderId, $body, $files) {
            $message = $contract->messages()->create([
                'sender_type' => $senderType,
                'sender_id' => $senderId,
                'body' => $body,
            ]);

            foreach ($files as $file) {
                $diskPath = $file->store("freelancer-contracts/{$contract->id}/messages", 'local');

                $message->attachments()->create([
                    'disk_path' => $diskPath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }

            return $message->load('attachments');
        });
    }
}
