<?php

namespace Taitech\TravfdPhp;

use GuzzleHttp\Client;
use SimpleXMLElement;
use Exception;

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
class TraVfdClient
{
    private string $baseUrl;
    private string $token;
    private Client $httpClient;

    public function __construct()
    {
        $this->baseUrl = config('tra_vfd.base_url');
        $this->httpClient = new Client(['base_uri' => $this->baseUrl]);
        $this->token = $this->getToken();
    }

    /**
     * Retrieves the authentication token for the TRA VFD API.
     *
     * This private method sends a request to the 'token' endpoint of the TRA VFD API to obtain an authentication token.
     * The request includes the TIN (Taxpayer Identification Number), username, and password configured in the application.
     *
     * @return string The authentication token, or an empty string if the token retrieval fails.
     */
    private function getToken(): string
    {
        try {
            $response = $this->httpClient->post(config('tra_vfd.endpoints.token'), [
                'json' => [
                    'tin' => config('tra_vfd.credentials.tin'),
                    'username' => config('tra_vfd.credentials.username'),
                    'password' => config('tra_vfd.credentials.password'),
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
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
        return $this->sendRequest('register', ['TIN' => config('tra_vfd.credentials.tin')]);
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
        return $this->sendRequest('receipt', $receiptData);
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
        return $this->sendRequest('z_report', $reportData);
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
        return $this->sendRequest('verify', ['receiptNumber' => $receiptNumber]);
    }


    /**
     * Sends a request to the TRA VFD API.
     * 
     * @param string $endpointKey The key for
     * @param array $data The data to be sent in the request.
     * @return array The response from the TRA VFD API, which may include an error message if the request fails.
     */ 
    private function sendRequest(string $endpointKey, array $data): array
    {
        try {
            $xmlData = $this->arrayToXml($data);
            $response = $this->httpClient->post(config('tra_vfd.endpoints.' . $endpointKey), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/xml',
                    'Accept' => 'application/xml',
                ],
                'body' => $xmlData
            ]);

            return $this->xmlToArray($response->getBody()->getContents());
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
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
}