<div class="inline-page-menu my-4">
    <ul class="list-unstyled">
        <li class="{{ Request::is('vendor/transaction/order-list') ?'active':'' }}"><a href="{{route('vendor.transaction.order-list')}}">{{translate('order_Transactions')}}</a></li>
        <li class="{{ Request::is('vendor/transaction/expense-list') ?'active':'' }}"><a href="{{route('vendor.transaction.expense-list')}}">{{translate('expense_Transactions')}}</a></li>
        <li class="{{ Request::is('vendor/transaction/commission-ledger') ?'active':'' }}"><a href="{{route('vendor.transaction.commission-ledger')}}">{{translate('commission_Ledger')}}</a></li>
    </ul>
</div>
