<?php

namespace App\Enums;

enum EmailTemplateKey
{
    const ADD_FUND_TO_WALLET = 'add-fund-to-wallet';
    const REGISTRATION = 'registration';
    const REGISTRATION_APPROVED = 'registration-approved';
    const REGISTRATION_DENIED = 'registration-denied';
    const VENDOR_VERIFICATION_SUBMITTED = 'vendor-verification-submitted';
    const VENDOR_VERIFICATION_APPROVED = 'vendor-verification-approved';
    const VENDOR_VERIFICATION_DENIED = 'vendor-verification-denied';
    const VENDOR_REGISTRATION_OTP = 'vendor-registration-otp';
    const ACCOUNT_SUSPENDED = 'account-suspended';
    const ACCOUNT_ACTIVATION = 'account-activation';
    const ACCOUNT_BLOCK = 'account-block';
    const ACCOUNT_UNBLOCK = 'account-unblock';
    const STATUS_UPDATE = 'status-update';
    const ORDER_STATUS_UPDATE = 'order-status-update';
    const DIGITAL_PRODUCT_DOWNLOAD = 'digital-product-download';
    const DIGITAL_PRODUCT_OTP = 'digital-product-otp';
    const ORDER_PLACE = 'order-place';
    const ORDER_DElIVERED = 'order-delivered';
    const ORDER_RECEIVED = 'order-received';
    const FORGET_PASSWORD = 'forgot-password';
    const REGISTRATION_VERIFICATION = 'registration-verification';
    const REGISTRATION_FROM_POS = 'registration-from-pos';
    const RESET_PASSWORD_VERIFICATION = 'reset-password-verification';
// const ORDER_RECEIVED = '';
//     const NEW_CHAT_MESSAGE = '';
//     const NEW_CHAT_MESSAGE_CUSTOMER = '';
//     const NEW_QUOTE_REQUEST = '';
//     const QUOTE_REQUEST_REPLIED = '';
//     const QUOTE_REQUEST_ACCEPTED = '';
//     const QUOTE_REQUEST_DECLINED = '';
    const NEW_CHAT_MESSAGE = 'new-chat-messages';
    const NEW_CHAT_MESSAGE_CUSTOMER = 'new-chat-message-customer';
    const NEW_QUOTE_REQUEST = 'new-quote-request';
    const QUOTE_REQUEST_REPLIED = 'quote-request-replied';
    const QUOTE_REQUEST_ACCEPTED = 'quote-request-accepted';
    const QUOTE_REQUEST_DECLINED = 'quote-request-declined';

    const ADMIN_EMAIL_LIST = [
        EmailTemplateKey::ORDER_RECEIVED,
    ];
    const VENDOR_EMAIL_LIST = [
        EmailTemplateKey::REGISTRATION,
        EmailTemplateKey::REGISTRATION_APPROVED,
        EmailTemplateKey::REGISTRATION_DENIED,
        EmailTemplateKey::VENDOR_VERIFICATION_SUBMITTED,
        EmailTemplateKey::VENDOR_VERIFICATION_APPROVED,
        EmailTemplateKey::VENDOR_VERIFICATION_DENIED,
        EmailTemplateKey::VENDOR_REGISTRATION_OTP,
        EmailTemplateKey::ACCOUNT_SUSPENDED,
        EmailTemplateKey::ACCOUNT_ACTIVATION,
        EmailTemplateKey::FORGET_PASSWORD,
        EmailTemplateKey::ORDER_RECEIVED,
        EmailTemplateKey::NEW_CHAT_MESSAGE,
        EmailTemplateKey::NEW_QUOTE_REQUEST,
        EmailTemplateKey::QUOTE_REQUEST_ACCEPTED,
        EmailTemplateKey::QUOTE_REQUEST_DECLINED,
    ];
    const CUSTOMER_EMAIL_LIST = [
        EmailTemplateKey::ORDER_PLACE,
        // EmailTemplateKey::FORGET_PASSWORD,
        EmailTemplateKey::REGISTRATION_VERIFICATION,
        EmailTemplateKey::REGISTRATION_FROM_POS,
        EmailTemplateKey::ACCOUNT_BLOCK ,
        EmailTemplateKey::ACCOUNT_UNBLOCK ,
        EmailTemplateKey::DIGITAL_PRODUCT_DOWNLOAD,
        EmailTemplateKey::DIGITAL_PRODUCT_OTP,
        EmailTemplateKey::ADD_FUND_TO_WALLET ,
        EmailTemplateKey::FORGET_PASSWORD ,
        EmailTemplateKey::QUOTE_REQUEST_REPLIED,
        EmailTemplateKey::NEW_CHAT_MESSAGE_CUSTOMER,
        EmailTemplateKey::ORDER_STATUS_UPDATE,
    ];
    const DELIVERY_MAN_EMAIL_LIST = [
        EmailTemplateKey::RESET_PASSWORD_VERIFICATION ,
    ];
}
