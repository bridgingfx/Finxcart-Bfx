<?php

declare(strict_types=1);

$file = __DIR__ . '/../resources/lang/bd/new-messages.php';
$lines = file($file);

if ($lines === false) {
    fwrite(STDERR, "Failed to read file\n");
    exit(1);
}

$removeKeys = [
    'autocomplete_off_required',
    'oninput_this_value_this_value_replace_0_9_g',
    'width_60_height_60',
    'theme_root_path_theme_aster_2',
    'theme_root_path_theme_fashion_2',
    'aria_labelledby_examplemodallabel_aria_hidden_true',
    'required_2',
    'tabindex_1_aria_labelledby_examplemodallabel',
    'aria_hidden_true',
    'title_download',
    'autocomplete_off',
    'width_100',
    'xmlns_http_www_w3_org_1999_html',
    'description_160',
    'title_3',
    'title_go_to_product_page',
    'value',
    'min',
    'max_2',
    'value_2',
    'mt_0',
    'placeholder',
    'type_range',
    'width_80',
    'title_yesterday',
    'title_today',
    'tabindex_1',
    'aria_labelledby_imgviewmodal',
    'label_role_dialog',
    'aria_modal_true',
    'width_60',
    'action',
    'width_100px',
    'loading_lazy',
    'title_add_to_wishlist',
    'loading_lazy_2',
    'ad_group_sidebar',
    'form',
    'label_role_dialog_aria_modal_true',
    'method_get',
    'aria_selected_true',
    'for',
    'label',
    'enctype_multipart_form_data',
    'aria_labelledby_offcanvas',
    'status_form',
    'extensions_and_select_curl',
    'import_marked_from_https_cdn_jsdelivr_net_npm_marked_lib_marked_esm_js',
    'height_40',
    'height_40_2',
    'image_3',
    'onchange_getupdatedigitalvariationfunctionality_2',
    'download_4',
    'form_2',
    'aria_labelledby_offcanvas_currency',
    'required_3',
    'aria_labelledby_currencydeletemodal',
    'aria_hidden_true_2',
];

$replace = [
    'click_create_api_key_and_copy_it' => 'Click Create API Key and copy it',
    'the_last_updated_date_at_the_top_of_this_page_reflects_when_the_most_recent_changes_were_made_we_encourage_you_to_review_this_policy_periodically'
        => 'The Last updated date at the top of this page reflects when the most recent changes were made. We encourage you to review this policy periodically.',
    'used_for_the_shortcode_e_g_ad_group_your_slug'
        => 'Used for the shortcode, e.g., [ad group=your-slug]',
    'an_internal_name_for_you_to_identify_this_ad_e_g_homepage_summer_sale_banner'
        => 'An internal name for you to identify this ad, e.g., Homepage Summer Sale Banner.',
    'required_only_for_image_ads'
        => 'Required only for Image ads.',
    'required_for_code_or_text_ads_paste_your_adsense_code_custom_html_or_plain_text_here'
        => 'Required for Code or Text ads. Paste your AdSense code, custom HTML, or plain text here.',
    'by_providing_your_electronic_signature_below_and_clicking_i_agree_you_acknowledge_that_you_have_read_understood_and_agree_to_be_bound_by_this_agreement_this_electronic_signature_has_the_same_legal_effect_as_a_handwritten_signature_under_the_electronic_signatures_in_global_and_national_commerce_act_e_sign_and_similar_laws'
        => 'By providing your electronic signature below and clicking I Agree, you acknowledge that you have read, understood, and agree to be bound by this Agreement. This electronic signature has the same legal effect as a handwritten signature under the Electronic Signatures in Global and National Commerce Act (E-SIGN) and similar laws.',
];

$out = [];
$removed = 0;
$changed = 0;

foreach ($lines as $line) {
    if (preg_match('/^\s*"((?:\\\\.|[^"\\\\])*)"\s*=>\s*(.*)$/', $line, $m)) {
        $key = stripcslashes($m[1]);

        if (in_array($key, $removeKeys, true)) {
            $removed++;
            continue;
        }

        if (array_key_exists($key, $replace)) {
            $escaped = addcslashes($replace[$key], "\\\"");
            $line = "\t\"{$m[1]}\" => \"{$escaped}\"," . PHP_EOL;
            $changed++;
        }
    }

    $out[] = $line;
}

file_put_contents($file, implode('', $out));
echo "Removed {$removed}, changed {$changed}" . PHP_EOL;
