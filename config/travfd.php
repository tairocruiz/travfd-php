<?php
/**
 * This array configures the settings for the TRA VFD (Tanzania Revenue Authority Virtual Fiscal Device) API integration. It includes the base URL for the API, the various endpoints used for different operations, and the credentials required for authentication.
 * The `base_url` setting specifies the base URL for the TRA VFD API, which is used as the starting point for all API requests.
 * The `credentials` array holds the necessary information for authenticating with the TRA VFD API, including the TIN (Taxpayer Identification Number), username, password, the path to the client certificate, and the password for the certificate.
 */
return [


    'base_url' => env('TRA_VFD_API_BASE', 'https://virtual.tra.go.tz/efdmsRctApi'),

    /**
       ___________________________________________________________________________________
       \                                                                                 |
       \ The `endpoints` array defines the relative paths for various API endpoints,     |
       \ such as registration, token retrieval, receipt information, Z-report,           |
       \ and receipt verification.                                                       |
       \_________________________________________________________________________________|

     */
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
        'cert_path' => storage_path('certs/my_cert.pfx'),
        'cert_password' => 'my_cert_password',
    ],

    /**
     * 
     */
    'encryption' => [
        'private_key_path' => '',
    ],
];
