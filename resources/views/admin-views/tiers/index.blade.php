@extends('layouts.admin.app')

@section('title', translate('Product_Tiers'))

@section('content')
    <div class="content container-fluid">

        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{-- Placeholder image for a pricing icon --}}
                <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/inhouse-product-list.png') }}" alt="">
                {{ translate('Product_Tier_List') }}
                <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">{{ $tiers->count() }}</span>
            </h2>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-12">
                        <h3 class="mb-3">{{ translate('Tier_Management_Panel') }}</h3>
                        <p class="text-muted">{{ translate('This_list_shows_all_available_product_tiers_and_their_configurations_as_per_the_business_proposal.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card">
                    <div class="px-3 py-4">
                        <div class="row align-items-center">
                            <div class="col-lg-12 d-flex flex-wrap gap-3 justify-content-lg-end">
                                {{-- Link to the create page for new tiers --}}
                                <a href="{{ route('admin.tiers.create') }}" class="btn btn-primary">
                                    <i class="fi fi-sr-add"></i>
                                    <span class="text">{{ translate('add_new_tier') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable"
                               class="table table-hover table-borderless table-thead-bordered align-middle">
                            <thead class="text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('Tier_Name') }}</th>
                                <th class="text-center">{{ translate('Fee_Structure') }}</th>
                                <th class="text-center">{{ translate('Price_Threshold') }}</th>
                                <th class="text-center">{{ translate('Target_Category') }}</th>
                                <th class="text-center">{{ translate('Free_First_Month') }}</th>
                                <th class="text-center">{{ translate('Status') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($tiers as $key => $tier)
                                <tr>
                                    <th scope="row">{{ $key + 1 }}</th>
                                    <td>
                                        <div class="media align-items-center gap-2">
                                            <div>
                                                <div class="media-body text-dark fw-bold text-hover-primary">
                                                    {{ $tier['name'] }}
                                                </div>
                                                <small class="text-muted">{{ Str::limit($tier['ideal_product_types'], 40) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($tier['is_commission_only'])
                                            <span class="badge text-bg-danger">{{ ($tier['sales_commission_rate']) . '% ' . translate('Commission') }}</span>
                                        @else
                                            {{ setCurrencySymbol(amount: $tier['monthly_fee_usd'], currencyCode: 'USD') . '/' . translate('month') }}
                                            @if($tier['sales_commission_rate'] > 0)
                                                <br><small class="text-muted">+ {{ ($tier['sales_commission_rate']) . '% ' . translate('Commission') }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($tier['is_recurring_focus'])
                                            <span class="badge text-bg-info">{{ translate('N/A') . ' (' . translate('Recurring_Focus') . ')' }}</span>
                                        @else
                                            @if($tier['price_threshold_min_usd'] !== null && $tier['price_threshold_max_usd'] !== null)
                                                {{ setCurrencySymbol(amount: $tier['price_threshold_min_usd'], currencyCode: 'USD') . ' - ' . setCurrencySymbol(amount: $tier['price_threshold_max_usd'], currencyCode: 'USD') }}
                                            @elseif($tier['price_threshold_max_usd'] !== null)
                                                {{ translate('Under') . ' ' . setCurrencySymbol(amount: $tier['price_threshold_max_usd'], currencyCode: 'USD') }}
                                            @elseif($tier['price_threshold_min_usd'] !== null)
                                                {{ translate('Over') . ' ' . setCurrencySymbol(amount: $tier['price_threshold_min_usd'], currencyCode: 'USD') }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $tier['target_category'] }}
                                    </td>
                                    <td class="text-center">
                                        @if($tier['is_free_first_month'])
                                            <span class="badge text-bg-success">{{ translate('Yes') }}</span>
                                        @else
                                            <span class="badge text-bg-secondary">{{ translate('No') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.tiers.toggle-active', $tier['id']) }}" method="POST" class="d-inline" novalidate>
                                            @csrf
                                            @method('PATCH')
                                            <label class="switcher mb-0" title="{{ ($tier['is_active'] ?? true) ? translate('click_to_deactivate') : translate('click_to_activate') }}">
                                                <input type="checkbox" class="switcher_input"
                                                       onchange="this.form.submit()"
                                                       {{ ($tier['is_active'] ?? true) ? 'checked' : '' }}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            {{-- View/Edit link will go to the 'edit' resource route --}}
                                            <a class="btn btn-outline-primary icon-btn"
                                               title="{{ translate('edit') }}" href="{{ route('admin.tiers.edit', $tier['id']) }}"><i class="fi fi-sr-pencil"></i></a>


                                            {{-- Delete Form (Requires a dedicated route and model binding in web.php) --}}
                                            <span class="btn btn-outline-danger icon-btn delete-data"
                                                  title="{{ translate('delete') }}"
                                                  data-id="tier-{{ $tier['id'] }}">
                                               <i class="fi fi-rr-trash"></i>
                                            </span>
                                        </div>
                                        <form action="{{ route('admin.tiers.destroy',[$tier['id']]) }}"
                                              method="post" id="tier-{{ $tier['id'] }}" novalidate>
                                            @csrf @method('delete')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($tiers->isEmpty())
                        @include('layouts.admin.partials._empty-state',['text' => 'no_product_tiers_found'],['image' => 'default'])
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
