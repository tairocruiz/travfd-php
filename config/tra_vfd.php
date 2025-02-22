<?php

return [
    'base_url' => env('TRA_VFD_API_BASE', 'https://virtual.tra.go.tz/efdmsRctApi'),
    'endpoints' => [
        'register' => '/api/vfdRegReq',
        'token' => '/vfdtoken',
        'receipt' => '/api/efdmsRctInfo',
        'z_report' => '/api/efdmszreport',
        'verify' => '/efdmsRctVerify/Home/Index',
    ],
    'credentials' => [
        'tin' => env('TRA_VFD_TIN', ''),
        'username' => env('TRA_VFD_USERNAME', ''),
        'password' => env('TRA_VFD_PASSWORD', ''),
    ],
];
