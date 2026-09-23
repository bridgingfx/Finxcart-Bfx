<div class="card mb-2">
    <div class="card-body">
        <form action="" id="form-data" method="GET" novalidate>
            <h4 class="mb-3">{{translate('filter_Data')}}</h4>
            <div class="row gx-2 gy-3 align-items-center text-left">
                <div class="col-sm-6 col-md-3">
                    <select class="form-control __form-control" name="date_type" id="date_type">
                        <option value="this_year" {{ $date_type == 'this_year'? 'selected' : '' }}>{{translate('this_Year')}}</option>
                        <option value="this_month" {{ $date_type == 'this_month'? 'selected' : '' }}>{{translate('this_Month')}}</option>
                        <option value="this_week" {{ $date_type == 'this_week'? 'selected' : '' }}>{{translate('this_Week')}}</option>
                        <option value="today" {{ $date_type == 'today'? 'selected' : '' }}>{{translate('today')}}</option>
                        <option value="custom_date" {{ $date_type == 'custom_date'? 'selected' : '' }}>{{translate('custom_Date')}}</option>
                    </select>
                </div>
                <div class="col-sm-6 col-md-3" id="from_div">
                    <div class="form-floating">
                        <input type="date" name="from" value="{{$from}}" id="from_date" class="form-control">
                        <label>{{translate('start_date')}}</label>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3" id="to_div">
                    <div class="form-floating">
                        <input type="date" value="{{$to}}" name="to" id="to_date" class="form-control">
                        <label>{{translate('end_date')}}</label>
                    </div>
                </div>
                <div class="col-sm-6 col-md-1">
                    <button type="submit" class="btn btn--primary px-4 px-md-5">
                        {{translate('filter')}}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
