@extends('layouts.vendor.app')

@section('title', translate('bank_Info_View'))

@push('css_or_js')
<style>
    .bank-profile-card {
        border: 1px solid rgba(7, 59, 116, .1);
        border-radius: 16px;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .bank-profile-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 34px rgba(7, 59, 116, .12);
    }
    .bank-visual {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #073b74 0%, #0d5aa7 100%);
        box-shadow: 0 16px 30px rgba(7, 59, 116, .22);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .bank-visual:hover {
        transform: translateY(-3px) rotateX(1deg);
        box-shadow: 0 20px 38px rgba(7, 59, 116, .3);
    }
    .bank-visual::after {
        content: '';
        position: absolute;
        width: 180px;
        height: 180px;
        right: -70px;
        top: -90px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .08);
    }
    .bank-detail-row {
        border-bottom: 1px solid rgba(7, 59, 116, .07);
        transition: background-color .15s ease, padding .15s ease;
    }
    .bank-detail-row:hover {
        padding-inline: 8px;
        background: rgba(7, 59, 116, .04);
    }
    .bank-empty-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 20px;
        color: #073b74;
        background: rgba(7, 59, 116, .09);
        font-size: 11px;
        font-weight: 700;
    }
    .bank-visual .bank-empty-badge {
        color: #fff;
        background: rgba(255, 255, 255, .16);
    }
</style>
@endpush

@section('content')
    @php
        $hasBankInformation = filled($vendor->bank_name) && filled($vendor->account_no);
        $bankValue = fn ($value) => filled($value)
            ? e($value)
            : '<span class="bank-empty-badge"><i class="tio-add-circle"></i>' . translate('Add_information') . '</span>';
    @endphp
    <div class="content container-fluid">
        <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h2 class="h1 mb-1 text-capitalize d-flex align-items-center gap-2">
                    <i class="tio-credit-card-outlined" style="color:#073b74;"></i>
                    {{translate('my_bank_info')}}
                </h2>
                <p class="text-muted mb-0">{{ translate('Manage_the_bank_details_used_for_withdrawals_and_subscriptions.') }}</p>
            </div>
            <a href="{{ route('vendor.profile.update-bank-info', [$vendor->id]) }}" class="btn btn--primary">
                <i class="tio-{{ $hasBankInformation ? 'edit' : 'add' }} mr-1"></i>
                {{ $hasBankInformation ? translate('edit_bank_information') : translate('add_bank_information') }}
            </a>
        </div>
        @unless($hasBankInformation)
            <div class="alert d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3"
                 style="background:rgba(7,59,116,.06);border:1px solid rgba(7,59,116,.18);color:#073b74;">
                <span><i class="tio-info-outlined mr-1"></i> {{ translate('Add_your_bank_information_to_start_a_paid_plan_and_receive_withdrawals.') }}</span>
                <span class="bank-empty-badge"><i class="tio-warning-outlined"></i>{{ translate('Bank_information_required') }}</span>
            </div>
        @endunless
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card text-start bank-profile-card">
                    <div class="border-bottom d-flex gap-3 flex-wrap justify-content-between align-items-center px-4 py-3">
                        <div class="d-flex gap-2 align-items-center">
                            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/bank.png')}}" alt="" />
                            <h3 class="mb-0">{{translate('account_details')}} <span data-toggle="tooltip" data-placement="right" data-title="{{translate('update_your_bank_details_with_correct_information').'.'.translate('it_will_be_used_for_your_withdraw_request_transactions by admin').'.'}}"> <img src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/info.svg') }}" alt="" class="svg ml-1"> </span></h3>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            {{-- Bank Card --}}
                            <div class="col-lg-5">
                                <div class="rounded-3 p-4 text-white d-flex flex-column justify-content-between bank-visual"
                                     style="min-height: 200px;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-0 opacity-75" style="font-size:11px;letter-spacing:1px;">ACCOUNT HOLDER</p>
                                            <h5 class="mb-0 fw-bold" style="color:#fff;">{!! $bankValue($vendor->holder_name) !!}</h5>
                                        </div>
                                        <span class="badge bg-white text-primary fw-bold" style="font-size:11px;">
                                            {{ $vendor->account_type ? strtoupper($vendor->account_type) : translate('Not_Set') }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mb-0 opacity-75" style="font-size:11px;letter-spacing:1px;">ACCOUNT NUMBER</p>
                                        <h5 class="mb-1 fw-bold letter-spacing-2" style="color:#fff;">{!! $bankValue($vendor->account_no) !!}</h5>
                                        @if($vendor->iban)
                                            <p class="mb-0 opacity-75" style="font-size:10px;">IBAN: {{ $vendor->iban }}</p>
                                        @endif
                                    </div>
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <p class="mb-0 opacity-75" style="font-size:10px;">{!! $bankValue($vendor->bank_name) !!}</p>
                                            <p class="mb-0 opacity-75" style="font-size:10px;">{{ $vendor->bank_country ?? '' }}</p>
                                        </div>
                                        <div class="text-end">
                                            <p class="mb-0 opacity-75" style="font-size:10px;">{{ $vendor->currency_preference ?? '' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Details List --}}
                            <div class="col-lg-7">
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody>
                                        <tr class="bank-detail-row">
                                            <td class="text-muted ps-0" style="width:40%;font-size:13px;"><i class="tio-bank mr-2"></i>{{ translate('bank_Name') }}</td>
                                            <td class="fw-bold" style="font-size:13px;">{!! $bankValue($vendor->bank_name) !!}</td>
                                        </tr>
                                        <tr class="bank-detail-row">
                                            <td class="text-muted ps-0" style="font-size:13px;"><i class="tio-key mr-2"></i>{{ translate('SWIFT_BIC_Code') }}</td>
                                            <td class="fw-bold" style="font-size:13px;">{!! $bankValue($vendor->swift_code) !!}</td>
                                        </tr>
                                        <tr class="bank-detail-row">
                                            <td class="text-muted ps-0" style="font-size:13px;"><i class="tio-globe mr-2"></i>{{ translate('Bank_Country') }}</td>
                                            <td class="fw-bold" style="font-size:13px;">{!! $bankValue($vendor->bank_country) !!}</td>
                                        </tr>
                                        <tr class="bank-detail-row">
                                            <td class="text-muted ps-0" style="font-size:13px;"><i class="tio-shop mr-2"></i>{{ translate('branch_Name') }}</td>
                                            <td class="fw-bold" style="font-size:13px;">{!! $bankValue($vendor->branch) !!}</td>
                                        </tr>
                                        <tr class="bank-detail-row">
                                            <td class="text-muted ps-0" style="font-size:13px;"><i class="tio-label mr-2"></i>{{ translate('Branch_Code') }}</td>
                                            <td class="fw-bold" style="font-size:13px;">{!! $bankValue($vendor->branch_code) !!}</td>
                                        </tr>
                                        <tr class="bank-detail-row">
                                            <td class="text-muted ps-0" style="font-size:13px;"><i class="tio-dollar-outlined mr-2"></i>{{ translate('Currency_Preference') }}</td>
                                            <td class="fw-bold" style="font-size:13px;">{!! $bankValue($vendor->currency_preference) !!}</td>
                                        </tr>
                                        @if($vendor->bank_address)
                                        <tr>
                                            <td class="text-muted ps-0" style="font-size:13px;">{{ translate('Bank_Address') }}</td>
                                            <td class="fw-bold" style="font-size:13px;">{{ $vendor->bank_address }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
