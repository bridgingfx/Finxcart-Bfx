<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FreelancerContract;
use App\Models\FreelancerContractDeliverableAttachment;
use App\Models\FreelancerContractMessageAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FreelancerContractAttachmentController extends Controller
{
    public function deliverable(FreelancerContractDeliverableAttachment $attachment): StreamedResponse
    {
        $contract = $attachment->deliverable?->milestone?->contract;
        $this->authorizeContractAccess($contract);

        return $this->download($attachment->disk_path, $attachment->original_name, $attachment->mime_type);
    }

    public function message(FreelancerContractMessageAttachment $attachment): StreamedResponse
    {
        $contract = $attachment->message?->contract;
        $this->authorizeContractAccess($contract);

        return $this->download($attachment->disk_path, $attachment->original_name, $attachment->mime_type);
    }

    private function authorizeContractAccess(?FreelancerContract $contract): void
    {
        abort_if($contract === null, 404);

        if (auth('admin')->check()) {
            return;
        }

        abort_unless(
            (auth('customer')->check() && $contract->isParticipant('customer', auth('customer')->id()))
            || (auth('freelancer')->check() && $contract->isParticipant('seller', auth('freelancer')->id())),
            403
        );
    }

    private function download(string $diskPath, string $originalName, ?string $mimeType): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($diskPath), 404);

        return Storage::disk('local')->download($diskPath, $originalName, array_filter([
            'Content-Type' => $mimeType,
        ]));
    }
}
