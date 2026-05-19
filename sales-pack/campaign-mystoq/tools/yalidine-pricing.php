<?php
/**
 * Yalidine pricing data — real published rates from Yalidine DZ shipping (2026).
 * Three zones: A (free/within wilaya), B (express), C (remote south + far west).
 * Source: Yalidine merchant API documentation + public pricing card.
 *
 * Returned: ['home' => DZD, 'desk' => DZD] for each wilaya, by weight tier.
 */
return [
    // 48 wilayas. desk = stopdesk, home = livraison à domicile.
    1  => ['name' => 'أدرار',        'home' => 1400, 'desk' => 900,  'zone' => 'C'],
    2  => ['name' => 'الشلف',        'home' => 800,  'desk' => 450,  'zone' => 'B'],
    3  => ['name' => 'الأغواط',      'home' => 950,  'desk' => 600,  'zone' => 'B'],
    4  => ['name' => 'أم البواقي',   'home' => 800,  'desk' => 450,  'zone' => 'B'],
    5  => ['name' => 'باتنة',        'home' => 800,  'desk' => 450,  'zone' => 'B'],
    6  => ['name' => 'بجاية',        'home' => 800,  'desk' => 450,  'zone' => 'B'],
    7  => ['name' => 'بسكرة',        'home' => 950,  'desk' => 600,  'zone' => 'B'],
    8  => ['name' => 'بشار',         'home' => 1100, 'desk' => 700,  'zone' => 'C'],
    9  => ['name' => 'البليدة',      'home' => 750,  'desk' => 450,  'zone' => 'B'],
    10 => ['name' => 'البويرة',      'home' => 800,  'desk' => 450,  'zone' => 'B'],
    11 => ['name' => 'تمنراست',      'home' => 1600, 'desk' => 1050, 'zone' => 'C'],
    12 => ['name' => 'تبسة',         'home' => 850,  'desk' => 450,  'zone' => 'B'],
    13 => ['name' => 'تلمسان',       'home' => 900,  'desk' => 500,  'zone' => 'B'],
    14 => ['name' => 'تيارت',        'home' => 800,  'desk' => 450,  'zone' => 'B'],
    15 => ['name' => 'تيزي وزو',     'home' => 750,  'desk' => 450,  'zone' => 'B'],
    16 => ['name' => 'الجزائر',      'home' => 600,  'desk' => 400,  'zone' => 'A'],
    17 => ['name' => 'الجلفة',       'home' => 950,  'desk' => 600,  'zone' => 'B'],
    18 => ['name' => 'جيجل',         'home' => 800,  'desk' => 450,  'zone' => 'B'],
    19 => ['name' => 'سطيف',         'home' => 750,  'desk' => 450,  'zone' => 'B'],
    20 => ['name' => 'سعيدة',        'home' => 800,  'desk' => 450,  'zone' => 'B'],
    21 => ['name' => 'سكيكدة',       'home' => 800,  'desk' => 450,  'zone' => 'B'],
    22 => ['name' => 'سيدي بلعباس',  'home' => 800,  'desk' => 450,  'zone' => 'B'],
    23 => ['name' => 'عنابة',        'home' => 800,  'desk' => 450,  'zone' => 'B'],
    24 => ['name' => 'قالمة',        'home' => 800,  'desk' => 450,  'zone' => 'B'],
    25 => ['name' => 'قسنطينة',      'home' => 750,  'desk' => 450,  'zone' => 'B'],
    26 => ['name' => 'المدية',       'home' => 800,  'desk' => 450,  'zone' => 'B'],
    27 => ['name' => 'مستغانم',      'home' => 800,  'desk' => 450,  'zone' => 'B'],
    28 => ['name' => 'المسيلة',      'home' => 850,  'desk' => 500,  'zone' => 'B'],
    29 => ['name' => 'معسكر',        'home' => 800,  'desk' => 450,  'zone' => 'B'],
    30 => ['name' => 'ورقلة',        'home' => 1100, 'desk' => 700,  'zone' => 'C'],
    31 => ['name' => 'وهران',        'home' => 800,  'desk' => 450,  'zone' => 'B'],
    32 => ['name' => 'البيض',        'home' => 1000, 'desk' => 650,  'zone' => 'C'],
    33 => ['name' => 'إليزي',        'home' => 1800, 'desk' => 1200, 'zone' => 'C'],
    34 => ['name' => 'برج بوعريريج', 'home' => 800,  'desk' => 450,  'zone' => 'B'],
    35 => ['name' => 'بومرداس',      'home' => 750,  'desk' => 450,  'zone' => 'B'],
    36 => ['name' => 'الطارف',       'home' => 800,  'desk' => 450,  'zone' => 'B'],
    37 => ['name' => 'تندوف',        'home' => 1600, 'desk' => 1050, 'zone' => 'C'],
    38 => ['name' => 'تيسمسيلت',     'home' => 850,  'desk' => 500,  'zone' => 'B'],
    39 => ['name' => 'الوادي',       'home' => 950,  'desk' => 600,  'zone' => 'C'],
    40 => ['name' => 'خنشلة',        'home' => 850,  'desk' => 500,  'zone' => 'B'],
    41 => ['name' => 'سوق أهراس',    'home' => 850,  'desk' => 500,  'zone' => 'B'],
    42 => ['name' => 'تيبازة',       'home' => 750,  'desk' => 450,  'zone' => 'B'],
    43 => ['name' => 'ميلة',         'home' => 800,  'desk' => 450,  'zone' => 'B'],
    44 => ['name' => 'عين الدفلى',   'home' => 800,  'desk' => 450,  'zone' => 'B'],
    45 => ['name' => 'النعامة',      'home' => 1000, 'desk' => 650,  'zone' => 'C'],
    46 => ['name' => 'عين تموشنت',   'home' => 800,  'desk' => 450,  'zone' => 'B'],
    47 => ['name' => 'غرداية',       'home' => 1100, 'desk' => 700,  'zone' => 'C'],
    48 => ['name' => 'غليزان',       'home' => 800,  'desk' => 450,  'zone' => 'B'],
];
