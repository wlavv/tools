<?php

return [
    'connection' => env('PRODUCT_IMAGE_REVIEW_DB_CONNECTION', env('DB_CONNECTION', 'mysql')),
    'database' => env('PRODUCT_IMAGE_REVIEW_PS_DATABASE'),
    'table_prefix' => env('PRODUCT_IMAGE_REVIEW_PS_PREFIX', 'ps_'),
    'asm_shop_id' => (int) env('PRODUCT_IMAGE_REVIEW_ASM_SHOP_ID', 2),
    'english_language_id' => (int) env('PRODUCT_IMAGE_REVIEW_ENGLISH_LANG_ID', 2),
    'store_url' => rtrim(env('PRODUCT_IMAGE_REVIEW_ASM_URL', 'https://www.all-stars-motorsport.com'), '/'),
    'thumbnail_type' => env('PRODUCT_IMAGE_REVIEW_THUMB_TYPE', 'small_default'),
    'large_image_type' => env('PRODUCT_IMAGE_REVIEW_LARGE_TYPE', 'large_default'),
    'per_page' => 10,
];
