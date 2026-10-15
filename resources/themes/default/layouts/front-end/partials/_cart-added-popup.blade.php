<div id="cart-added-popup" class="cart-added-popup">
    <button type="button" class="cart-added-popup-close" id="cart-popup-close">&times;</button>
    <div class="cart-added-popup-header">
        <span class="cart-added-check">&#10003;</span>
        <span class="cart-added-title">{{ translate('added_to_cart') }}</span>
    </div>
    <div class="cart-added-popup-product d-flex align-items-center gap-3 mt-3">
        <img id="cart-popup-img" src="" alt="" class="cart-popup-thumb rounded" width="64" height="64" style="object-fit:cover;">
        <div>
            <div id="cart-popup-name" class="cart-popup-product-name font-semi-bold fs-14"></div>
            <span id="cart-popup-type-badge" class="cart-popup-badge"></span>
        </div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <a id="cart-popup-view-cart" href="{{ route('shop-cart') }}" class="btn btn--primary flex-grow-1 text-center fs-14">
            {{ translate('view_cart') }}
        </a>
        <button type="button" id="cart-popup-continue" class="btn btn-outline-primary flex-grow-1 fs-14">
            {{ translate('continue_shopping') }}
        </button>
    </div>
</div>

<style>
    .cart-added-popup {
        position: fixed;
        top: 0;
        right: 0;
        width: 320px;
        max-width: 90vw;
        height: 100%;
        background: #fff;
        z-index: 1055;
        box-shadow: -4px 0 24px rgba(0,0,0,.18);
        padding: 28px 24px;
        transition: transform .3s ease;
        transform: translateX(100%);
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .cart-added-popup.open {
        transform: translateX(0);
    }
    .cart-added-popup-close {
        position: absolute;
        top: 14px;
        right: 18px;
        background: none;
        border: none;
        font-size: 22px;
        color: #555;
        cursor: pointer;
        line-height: 1;
    }
    .cart-added-popup-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-top: 8px;
    }
    .cart-added-check {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background: #27ae60;
        color: #fff;
        border-radius: 50%;
        font-size: 16px;
        font-weight: bold;
        flex-shrink: 0;
    }
    .cart-added-title {
        font-size: 16px;
        font-weight: 700;
        color: #222;
    }
    .cart-popup-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        margin-top: 4px;
    }
    .cart-popup-badge.digital {
        background: #e8f4fd;
        color: #1a73e8;
    }
    .cart-popup-badge.physical {
        background: #f0fdf4;
        color: #16a34a;
    }
</style>
