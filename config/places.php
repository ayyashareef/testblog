<?php

/*
|--------------------------------------------------------------------------
| Maldives gazetteer
|--------------------------------------------------------------------------
|
| Every administrative atoll plus Malé, each with an approximate centre and
| ring size (rx/ry in degrees) used to draw the dot map. `islands` are names
| that count toward their atoll when mentioned. Aliases are matched as whole
| words; English aliases are case-sensitive so "Male" the city is not
| confused with "male" the word. A trailing `*` on a Dhivehi alias matches
| any word that starts with it, which catches suffixes like ގައި ("in").
|
*/

return [
    'MLE' => ['name' => 'Malé', 'name_dv' => 'މާލެ', 'lat' => 4.175, 'lng' => 73.509, 'rx' => 0.03, 'ry' => 0.03,
        'aliases' => ['Malé', 'Male\'', 'Male City', 'Male', 'Greater Malé', 'Hulhumalé', 'Hulhumale', 'Villimalé', 'Villimale', 'Hulhulé', 'Velana', 'މާލެ*', 'މާލޭ*', 'ހުޅުމާލެ*', 'ހުޅުމާލޭ*', 'ވިލިމާލެ*', 'ވެލާނާ*']],
    'HA' => ['name' => 'Haa Alif', 'name_dv' => 'ހާ އަލިފު', 'lat' => 6.95, 'lng' => 72.95, 'rx' => 0.17, 'ry' => 0.28,
        'aliases' => ['Haa Alif', 'HA.', 'Thiladhunmathi North', 'Dhidhdhoo', 'Hoarafushi', 'Ihavandhoo', 'ހާ އަލިފު*', 'ދިއްދޫ*', 'ހޯރަފުށި*']],
    'HDh' => ['name' => 'Haa Dhaalu', 'name_dv' => 'ހާ ދާލު', 'lat' => 6.6, 'lng' => 73.05, 'rx' => 0.18, 'ry' => 0.2,
        'aliases' => ['Haa Dhaalu', 'HDh.', 'Kulhudhuffushi', 'Hanimaadhoo', 'Nolhivaram', 'ހާ ދާލު*', 'ކުޅުދުއްފުށި*', 'ހަނިމާދޫ*']],
    'Sh' => ['name' => 'Shaviyani', 'name_dv' => 'ޝާވިޔަނި', 'lat' => 6.25, 'lng' => 73.2, 'rx' => 0.18, 'ry' => 0.2,
        'aliases' => ['Shaviyani', 'Sh.', 'Funadhoo', 'Milandhoo', 'Komandoo', 'ޝާވިޔަނި*', 'ފުނަދޫ*']],
    'N' => ['name' => 'Noonu', 'name_dv' => 'ނޫނު', 'lat' => 5.85, 'lng' => 73.38, 'rx' => 0.18, 'ry' => 0.2,
        'aliases' => ['Noonu', 'Manadhoo', 'Velidhoo', 'Holhudhoo', 'ނޫނު*', 'މަނަދޫ*', 'ވެލިދޫ*']],
    'R' => ['name' => 'Raa', 'name_dv' => 'ރާ', 'lat' => 5.55, 'lng' => 72.95, 'rx' => 0.14, 'ry' => 0.25,
        'aliases' => ['Raa Atoll', 'Raa', 'Ungoofaaru', 'Maduvvari', 'Alifushi', 'Ifuru', 'ރާ އަތޮޅު*', 'އުނގޫފާރު*', 'މަޑުއްވަރި*']],
    'B' => ['name' => 'Baa', 'name_dv' => 'ބާ', 'lat' => 5.1, 'lng' => 73.02, 'rx' => 0.2, 'ry' => 0.17,
        'aliases' => ['Baa Atoll', 'Baa', 'Hanifaru', 'Eydhafushi', 'Thulhaadhoo', 'Dharavandhoo', 'ބާ އަތޮޅު*', 'އޭދަފުށި*', 'ތުޅާދޫ*']],
    'Lh' => ['name' => 'Lhaviyani', 'name_dv' => 'ޅަވިޔަނި', 'lat' => 5.35, 'lng' => 73.55, 'rx' => 0.12, 'ry' => 0.15,
        'aliases' => ['Lhaviyani', 'Naifaru', 'Hinnavaru', 'Felivaru', 'ޅަވިޔަނި*', 'ނައިފަރު*', 'ހިންނަވަރު*']],
    'K' => ['name' => 'Kaafu', 'name_dv' => 'ކާފު', 'lat' => 4.3, 'lng' => 73.45, 'rx' => 0.2, 'ry' => 0.45,
        'aliases' => ['Kaafu', 'North Malé Atoll', 'South Malé Atoll', 'Maafushi', 'Thilafushi', 'Gulhifalhu', 'Guraidhoo', 'Huraa', 'Himmafushi', 'Thulusdhoo', 'Kaashidhoo', 'Dhiffushi', 'ކާފު*', 'މާފުށި*', 'ތިލަފުށި*', 'ގުޅިފަޅު*', 'ގުރައިދޫ*', 'ކާށިދޫ*']],
    'AA' => ['name' => 'Alif Alif', 'name_dv' => 'އަލިފު އަލިފު', 'lat' => 4.15, 'lng' => 72.85, 'rx' => 0.15, 'ry' => 0.3,
        'aliases' => ['Alif Alif', 'North Ari', 'Rasdhoo', 'Ukulhas', 'Thoddoo', 'Bodufolhudhoo', 'އަލިފު އަލިފު*', 'ރަސްދޫ*', 'ތޮއްޑޫ*', 'އުކުޅަސް*']],
    'ADh' => ['name' => 'Alif Dhaal', 'name_dv' => 'އަލިފު ދާލު', 'lat' => 3.65, 'lng' => 72.8, 'rx' => 0.15, 'ry' => 0.2,
        'aliases' => ['Alif Dhaal', 'Alif Dhaalu', 'South Ari', 'Mahibadhoo', 'Dhigurah', 'Maamigili', 'Dhangethi', 'އަލިފު ދާލު*', 'މަހިބަދޫ*', 'މާމިގިލި*', 'ދިގުރަށް*']],
    'V' => ['name' => 'Vaavu', 'name_dv' => 'ވާވު', 'lat' => 3.45, 'lng' => 73.5, 'rx' => 0.12, 'ry' => 0.2,
        'aliases' => ['Vaavu', 'Felidhoo', 'Fulidhoo', 'Keyodhoo', 'Thinadhoo, Vaavu', 'ވާވު*', 'ފެލިދޫ*', 'ފުލިދޫ*']],
    'M' => ['name' => 'Meemu', 'name_dv' => 'މީމު', 'lat' => 2.95, 'lng' => 73.5, 'rx' => 0.15, 'ry' => 0.2,
        'aliases' => ['Meemu', 'Muli', 'Dhiggaru', 'Kolhufushi', 'މީމު*', 'މުލި*', 'ދިއްގަރު*']],
    'F' => ['name' => 'Faafu', 'name_dv' => 'ފާފު', 'lat' => 3.2, 'lng' => 72.95, 'rx' => 0.12, 'ry' => 0.15,
        'aliases' => ['Faafu', 'Nilandhoo', 'Magoodhoo', 'Bileiydhoo', 'ފާފު*', 'ނިލަންދޫ*']],
    'Dh' => ['name' => 'Dhaalu', 'name_dv' => 'ދާލު', 'lat' => 2.85, 'lng' => 72.9, 'rx' => 0.13, 'ry' => 0.17,
        'aliases' => ['Dhaalu', 'Kudahuvadhoo', 'Meedhoo, Dhaalu', 'Rinbudhoo', 'ދާލު އަތޮޅު*', 'ކުޑަހުވަދޫ*']],
    'Th' => ['name' => 'Thaa', 'name_dv' => 'ތާ', 'lat' => 2.35, 'lng' => 73.1, 'rx' => 0.2, 'ry' => 0.2,
        'aliases' => ['Thaa Atoll', 'Thaa', 'Veymandoo', 'Thimarafushi', 'Guraidhoo, Thaa', 'Kinbidhoo', 'ތާ އަތޮޅު*', 'ވޭމަންޑޫ*', 'ތިމަރަފުށި*']],
    'L' => ['name' => 'Laamu', 'name_dv' => 'ލާމު', 'lat' => 1.9, 'lng' => 73.4, 'rx' => 0.15, 'ry' => 0.22,
        'aliases' => ['Laamu', 'Fonadhoo', 'Gan, Laamu', 'Isdhoo', 'Maandhoo', 'Kadhdhoo', 'ލާމު*', 'ފޮނަދޫ*', 'އިސްދޫ*', 'ކައްދޫ*']],
    'GA' => ['name' => 'Gaafu Alif', 'name_dv' => 'ގާފު އަލިފު', 'lat' => 0.62, 'lng' => 73.35, 'rx' => 0.24, 'ry' => 0.18,
        'aliases' => ['Gaafu Alif', 'Villingili, Gaafu Alif', 'Kooddoo', 'Dhaandhoo', 'Kolamaafushi', 'ގާފު އަލިފު*', 'ކޫއްޑޫ*']],
    'GDh' => ['name' => 'Gaafu Dhaalu', 'name_dv' => 'ގާފު ދާލު', 'lat' => 0.3, 'lng' => 73.12, 'rx' => 0.24, 'ry' => 0.18,
        'aliases' => ['Gaafu Dhaalu', 'Huvadhu', 'Thinadhoo', 'Gadhdhoo', 'Fiyoari', 'Kaadedhdhoo', 'ގާފު ދާލު*', 'ތިނަދޫ*', 'ގައްދޫ*', 'ހުވަދު*']],
    'Gn' => ['name' => 'Fuvahmulah', 'name_dv' => 'ފުވައްމުލައް', 'lat' => -0.3, 'lng' => 73.43, 'rx' => 0.03, 'ry' => 0.045,
        'aliases' => ['Fuvahmulah', 'Fuvammulah', 'Gnaviyani', 'ފުވައްމުލައް*', 'ފުވައްމުލަކު*']],
    'S' => ['name' => 'Addu', 'name_dv' => 'އައްޑު', 'lat' => -0.63, 'lng' => 73.15, 'rx' => 0.1, 'ry' => 0.08,
        'aliases' => ['Addu City', 'Addu', 'Seenu', 'Hithadhoo', 'Maradhoo', 'Feydhoo', 'Hulhumeedhoo', 'Meedhoo, Addu', 'Gan International', 'އައްޑު*', 'އައްޑޫ*', 'ހިތަދޫ*', 'މަރަދޫ*', 'ފޭދޫ*', 'ހުޅުމީދޫ*']],
];
