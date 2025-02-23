<?php

namespace Taitech\TravfdPhp;

use GuzzleHttp\Client;
use SimpleXMLElement;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Taitech\TravfdPhp\Helpers\CryptoHelper;

// use function Taitech\TravfdPhp\config;

/**
 * The `TraVfdClient` class is responsible for interacting with the TRA VFD (Tax Receipt Authority Virtual Fiscal Device) API.
 * It handles authentication, sending requests, and parsing responses.
 * 
 * @param string $baseUrl
 * @param string $token
 * @param \GuzzleHttp\Client $httpClient
 * 
 * The class has the following main methods:
 * 
 * @method array registerVfd(): Registers a VFD with the TRA VFD API.
 * @method array sendReceipt(array $receiptData): Sends a receipt to the TRA VFD API.
 * @method array sendZReport(array $reportData): Sends a Z-report to the TRA VFD API.
 * @method array verifyReceipt(string $receiptNumber): Verifies a receipt with the TRA VFD API.
 *
 * The class also has some private helper methods for handling the API requests and responses.
 */
class TravfdClient
{
    private string $baseUrl;
    private ?string $token;
    private Client $httpClient;

    public function __construct()
    {
        try {
            $this->baseUrl = config('travfd.base_url');
            $this->httpClient = new Client(['base_uri' => $this->baseUrl]);
            $this->token = $this->getValidToken();
        } catch (Exception $e) {
            throw new Exception("Initialization failed: " . $e->getMessage());
        }
    }

    /**
     * Load the PFX certificate and extract the private key.
     *
     * @param string $certPath Path to the PFX certificate file.
     * @param string $password Password for the certificate.
     * @return array Returns an array containing the private key and the certificate.
     * @throws Exception If the certificate cannot be loaded.
     */
    private function loadKeyCertificate(string $certPath, string $password): array
    {
        if (!file_exists($certPath)) {
            throw new Exception("Certificate file not found at $certPath.");
        }

        $certContent = file_get_contents($certPath);
        if (!$certContent) {
            throw new Exception("Failed to read the certificate file.");
        }

        $certs = [];
        if (!openssl_pkcs12_read($certContent, $certs, $password)) {
            throw new Exception("Failed to parse the PFX certificate. Ensure the password is correct.");
        }

        return [
            'privateKey' => $certs['pkey'] ?? '',
            'certKey'    => $certs['cert'] ?? '',
        ];
    }


    /**
     * Retrieves the authentication token for the TRA VFD API.
     *
     * This private method sends a request to the 'token' endpoint of the TRA VFD API to obtain an authentication token.
     * The request includes the TIN (Taxpayer Identification Number), username, and password configured in the application.
     *
     * @return string The authentication token, or an empty string if the token retrieval fails.
     */
    
    private function getValidToken(): string
    {
        if ($token = Cache::get('travfd_token')) {
            return $token;
        }

        try {
            $response = $this->httpClient->post(config('travfd.endpoints.token'), [
                'json' => [
                    'tin' => config('travfd.credentials.tin'),
                    'username' => config('travfd.credentials.username'),
                    'password' => config('travfd.credentials.password'),
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            if (!empty($data['token'])) {
                Cache::put('travfd_token', $data['token'], Carbon::now()->addMinutes(55));
            }
            return $data['token'] ?? '';
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Registers a VFD with the TRA VFD API.
     *
     * This method sends a request to the 'register' endpoint of the TRA VFD API to register a VFD.
     * The request includes the TIN (Taxpayer Identification Number) configured in the application.
     *
     * @return array The response from the TRA VFD API, which may include an error message if the registration fails.
     */
    public function registerVfd(): array
    {
        return $this->sendRequest('POST', config('travfd.endpoints.register'), ['TIN' => config('travfd.credentials.tin')], true);
    }

    /**
     * Sends a receipt to the TRA VFD API.
     *
     * This method sends a request to the 'receipt' endpoint of the TRA VFD API to register a receipt.
     * The request includes the receipt data provided in the $receiptData parameter.
     *
     * @param array $receiptData The receipt data to be sent to the TRA VFD API.
     * @return array The response from the TRA VFD API, which may include an error message if the receipt registration fails.
     */
    public function sendReceipt(array $receiptData): array
    {
        $this->validateReceiptData($receiptData);
        return $this->sendRequest('POST', config('travfd.endpoints.receipt'), $receiptData, true, true);
    }

    /**
     * Sends a Z-Report to the TRA VFD API.
     *
     * This method sends a request to the 'z_report' endpoint of the TRA VFD API to register a Z-Report.
     * The request includes the Z-Report data provided in the $reportData parameter.
     *
     * @param array $reportData The Z-Report data to be sent to the TRA VFD API.
     * @return array The response from the TRA VFD API, which may include an error message if the Z-Report registration fails.
     */
    public function sendZReport(array $reportData): array
    {
        // return $this->sendRequest('z_report', $reportData);
        return $this->sendRequest('POST', config('travfd.endpoints.z_report'), $reportData, true, true);
    }

    /**
     * Verifies a receipt with the TRA VFD API.
     *
     * This method sends a request to the 'verify' endpoint of the TRA VFD API to verify a receipt.
     * The request includes the receipt number provided in the $receiptNumber parameter.
     *
     * @param string $receiptNumber The receipt number to be verified.
     * @return array The response from the TRA VFD API, which may include an error message if the verification fails.
     */
    public function verifyReceipt(string $receiptNumber): array
    {
        return $this->sendRequest('GET', config('travfd.endpoints.verify') . "?invoice={$receiptNumber}");
        // return $this->sendRequest('verify', ['receiptNumber' => $receiptNumber]);
    }


    /**
     * Sends a request to the TRA VFD API.
     * 
     * This method sends a request to the TRA VFD API using the provided HTTP method, endpoint, and data.
     * It handles authentication and error handling for the request.
     * @param string $method The HTTP method to use for the request.
     * @param string $endpointKey The key for the endpoint configuration.
     * @param array $data The data to be sent in the request.
     * @param bool $isXml Whether the request should be sent as XML.
     * @return array The response from the TRA VFD API, which may include an error message if the request fails.
     */ 
    private function sendRequest(string $method, string $endpoint, array $data = [], bool $isXml = false, bool $encrypt = false): array
    {
        try {
            $options = ['headers' => ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/xml']];
            if ($isXml) {
                $options['headers']['Content-Type'] = 'application/xml';
                $xmlData = $this->arrayToXml($data);
                $options['body'] = $encrypt ? CryptoHelper::encrypt($xmlData) : $xmlData;
            } else {
                $options['json'] = $data;
            }
            
            $response = $this->httpClient->request($method, $endpoint, $options);

            // Decrypt the response if it was encrypted
            $responseData = $response->getBody()->getContents();
            if ($encrypt) {
                $responseData = CryptoHelper::decrypt($responseData);
            }

            return $this->xmlToArray($responseData);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 401) { // Token expired
                $this->token = $this->getValidToken(); // Refresh token
                Cache::put('travfd_token', $this->token, Carbon::now()->addMinutes(55));
                return $this->sendRequest($method, $endpoint, $data, $isXml); // Retry
            }
            return ['error [Http:]' => $e->getMessage()];
        } catch (Exception $e) {
            return ['error [Exception:]' => $e->getMessage()];
        }

        
    }


    /**
     * Converts an associative array to an XML string.
     *
     * This private function takes an associative array and converts it to an XML string. If a SimpleXMLElement
     * is provided, the array is added as children to that element. Otherwise, a new SimpleXMLElement is created
     * with a 'Request' root element.
     *
     * @param array $data The associative array to be converted to XML.
     * @param SimpleXMLElement|null $xml The optional SimpleXMLElement to add the array data to.
     * @return string The XML string representation of the input array.
     */
    private function arrayToXml(array $data, SimpleXMLElement $xml = null): string
    {
        if ($xml === null) {
            $xml = new SimpleXMLElement('<Request/>');
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->arrayToXml($value, $xml->addChild($key));
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }

        return $xml->asXML();
    }

    /**
     * Converts an XML string to an associative array.
     *
     * This private function takes an XML string and converts it to an associative array representation.
     *
     * @param string $xml The XML string to be converted.
     * @return array The associative array representation of the input XML.
     */
    private function xmlToArray(string $xml): array
    {
        $simpleXml = new SimpleXMLElement($xml);
        return json_decode(json_encode($simpleXml), true);
    }

    /**
     * Validates the receipt data array.
     *
     * This private function takes an associative array of receipt data and performs validation on certain fields.
     * It removes non-numeric characters from the 'MOBILENUM' field and ensures the 'CUSTID' field is a valid 9-digit number
     * if the 'CUSTIDTYPE' field is set to 1.
     *
     * @param array $data The associative array of receipt data to be validated.
     */
    private function validateReceiptData(array &$data): void
    {
        if (isset($data['MOBILENUM'])) {
            $data['MOBILENUM'] = preg_replace('/[^0-9]/', '', $data['MOBILENUM']);
        }
        if (!empty($data['CUSTIDTYPE']) && $data['CUSTIDTYPE'] == 1 && !empty($data['CUSTID'])) {
            $data['CUSTID'] = preg_match('/^\d{9}$/', $data['CUSTID']) ? $data['CUSTID'] : null;
        }
    }
}