<?php

namespace App\Http\Controllers\Admin\ThirdParty;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\ViewPaths\Admin\Mail;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\MailBrevoUpdateRequest;
use App\Http\Requests\Admin\MailUpdateRequest;
use App\Services\MailService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MailController extends BaseController
{

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly MailService $mailService,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView();
    }

    public function getView(): View
    {
        return view('admin-views.third-party.mail.index');
    }

    public function update(MailUpdateRequest $request): RedirectResponse
    {
        if ($request['status'] == 1) {
            $this->disableMailConfig(type: 'mail_config_sendgrid');
            $this->disableMailConfig(type: 'mail_config_brevo');
        }

        $dataArray = $this->mailService->getData(request: $request);
        $this->businessSettingRepo->updateOrInsert(type: 'mail_config', value: json_encode($dataArray));
        ToastMagic::success(translate('Configuration_updated_successfully'));
        return back();
    }

    public function updateSendGrid(MailUpdateRequest $request): RedirectResponse
    {
        if ($request['status'] == 1) {
            $this->disableMailConfig(type: 'mail_config');
            $this->disableMailConfig(type: 'mail_config_brevo');
        }
        $dataArray = $this->mailService->getData(request: $request);
        $this->businessSettingRepo->updateOrInsert(type: 'mail_config_sendgrid', value: json_encode($dataArray));
        ToastMagic::success(translate('SendGrid_Configuration_updated_successfully'));
        return back();
    }

    public function updateBrevo(MailBrevoUpdateRequest $request): RedirectResponse
    {
        if ($request['status'] == 1) {
            $this->disableMailConfig(type: 'mail_config');
            $this->disableMailConfig(type: 'mail_config_sendgrid');
        }
        $dataArray = $this->mailService->getData(request: $request);
        $this->businessSettingRepo->updateOrInsert(type: 'mail_config_brevo', value: json_encode($dataArray));
        ToastMagic::success(translate('Brevo_Configuration_updated_successfully'));
        return back();
    }

    private function disableMailConfig(string $type): void
    {
        $config = $this->businessSettingRepo->getFirstWhere(params: ['type' => $type]);
        if (!$config) {
            return;
        }
        $mailData = json_decode($config['value'], true);
        $mailDataArray = $this->mailService->getMailData(mailData: $mailData);
        $this->businessSettingRepo->updateOrInsert(type: $type, value: json_encode($mailDataArray));
    }

    public function send(Request $request): JsonResponse
    {
        $response = $this->mailService->sendMail(request: $request);
        return response()->json([
            'status' => $response['status'],
            'message' => $response['message'],
        ]);
    }
}
