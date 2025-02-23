# README.md

# TRA VFD Client for Laravel

**TRA VFD Client** is a Laravel-compatible Composer package for integrating with the Tanzania Revenue Authority (TRA) Virtual Fiscal Device (VFD) service. It provides an easy-to-use interface for:

- Registering a TIN to obtain a VFD number
- Fetching authentication tokens
- Submitting receipts/invoices
- Posting daily Z reports
- Verifying receipts/invoices

## Installation

### 1. Install via Composer

```sh

composer require taitech/travfd-php

```

### 2. Publish Configuration

```sh

php artisan vendor:publish --tag=config

```

### 3. Add Environment Variables

Update `.env` with TRA API credentials:

```env

TRS_VFD_API_BASE=https://virtual.tra.go.tz/efdmsRctApi
TRS_VFD_TIN=123456789
TRS_VFD_USERNAME=your_username
TRS_VFD_PASSWORD=your_password

```

## Usage

### Register VFD

```php

use Taitech\TravfdPhp\Facades\Trsvfd;
$response = Trsrfd::registerVfd();
print_r($response);

```

### Fetch Token

```php

$response = Trsvfd::getValidToken();
print_r($response);

```

### Submit Receipt

```php

$receiptData = [
    'Invoice' => [
        'TIN' => '123456789',
        'Date' => '2025-02-22',
        'Total' => '1000.00'
    ]
];
$response = Trsvfd::sendReceipt($receiptData);
print_r($response);

```

### Submit Z Report

```php

$response = Trsvfd::sendZReport([
    'TIN' => '123456789',
    'Date' => '2025-02-22',
    'TotalSales' => '5000.00'
]);
print_r($response);

```

### Verify Receipt

```php

$response = Travfd::verifyReceipt('INV-12345');
print_r($response);

```

## License

This package is licensed under the MIT License. See [LICENSE.md](LICENSE.md) for details.
