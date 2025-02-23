<?php

namespace Taitech\TravfdPhp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for the 'travfd' service.
 * 
 * @param string $baseUrl
 * @param string $token
 * @param \GuzzleHttp\Client $httpClient
 * 
 * @method array registerVfd(): Registers a VFD with the TRA VFD API.
 * @method array sendReceipt(array $receiptData): Sends a receipt to the TRA VFD API.
 * @method array sendZReport(array $reportData): Sends a Z-report to the TRA VFD API.
 * @method array verifyReceipt(string $receiptNumber): Verifies a receipt with the TRA VFD API.
 */
class Travfd extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'travfd';
    }
}