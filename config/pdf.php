<?php
//
//return [
//	'mode'                  => 'utf-8',
//	'format'                => 'A4',
//	'author'                => '',
//	'subject'               => '',
//	'keywords'              => '',
//	'creator'               => 'Laravel Pdf',
//	'display_mode'          => 'fullpage',
//	'tempDir'               => base_path('../temp/'),
//	'pdf_a'                 => false,
//	'pdf_a_auto'            => false,
//	'icc_profile_path'      => ''
//];


return [
   //  'defaultCssFile' => base_path('public/css/pdf.css'),
    'font_path' => base_path('resources/fonts/'),
    'font_data' => [
        'examplefont' => [
            'R' => 'k.ttf',    // regular font
            'B' => 'k.ttf',       // optional: bold font
            'I' => 'k.ttf',     // optional: italic font
            'BI' => 'k.ttf', // optional: bold-italic font
            'useOTL' => 0xFF,
            'useKashida' => 222,
        ],
        'arabic' => [
            'R' => 'arabic.ttf',    // regular font
            'B' => 'arabic.ttf',       // optional: bold font
            'I' => 'arabic.ttf',     // optional: italic font
            'BI' => 'arabic.ttf', // optional: bold-italic font
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ],
    ],
    'mode' => 'utf-8',
    'format' => 'A4',
    'author' => 'HR Alamin',
    'subject' => 'Arabic words',
    'keywords' => '',
    'creator' => 'hralamin.xyz',
    'display_mode' => 'fullpage',
    'tempDir' => base_path('storage/app/mpdf'),
    'pdf_a' => false,
    'pdf_a_auto' => false,
    'icc_profile_path' => '',


    'default_font_size'          => '14',
    'default_font'               => 'FreeSans',
    'margin_left'                => 10,
    'margin_right'               => 10,
    'margin_top'                 => 10,
    'margin_bottom'              => 10,
    'margin_header'              => 0,
    'margin_footer'              => 0,
    'orientation'                => 'P',
    'title'                      => 'Laravel mPDF',
    'watermark'                  => 'asdf',
    'show_watermark'             => true,
    'show_watermark_image'       => false,
    'watermark_font'             => 'sans-serif',
    'watermark_text_alpha'       => 0.8,
    'watermark_image_path'       => '',
    'watermark_image_alpha'      => 0.2,
    'watermark_image_size'       => 'D',
    'watermark_image_position'   => 'P',
    'custom_font_dir'            => '',
    'custom_font_data'           => [],
    'auto_language_detection'    => true,
    'temp_dir'                   => storage_path('app'),
    'pdfa'                       => true,
    'pdfaauto'                   => true,
];