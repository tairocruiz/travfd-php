<?php

namespace Taitech\TravfdPhp\Helpers;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


/**
 * Provides a set of utility methods for working with cryptographic operations, such as creating and verifying digital signatures, and encrypting and decrypting data.
 * 
 * @method static string createSignaature($privateKey, $message): Creates a digital signature for the given message using the provided private key.
 * 
 * 
 */
class CryptoHelper

    /**
     * Creates a digital signature for the given message using the provided private key.
     *
     * @param string $privateKey The private key to use for signing.
     * @param string $message The message to sign.
     * @return string The base64-encoded digital signature.
     */{
    public static function createSignature($privateKey, $message)
    {
        openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA1);
        return base64_encode($signature);
    }

    /**
     * Verifies the digital signature for the given message using the provided public key.
     *
     * @param string $publicKey The public key to use for verification.
     * @param string $message The message to verify.
     * @param string $signature The base64-encoded digital signature to verify.
     * @return bool True if the signature is valid, false otherwise.
     */
    public static function verifySignature($publicKey, $message, $signature)
    {
        return openssl_verify($message, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA1) === 1;
    }

    /**
     * Loads a private key from a PKCS#12 certificate file.
     *
     * @param string $filePath The path to the PKCS#12 certificate file.
     * @param string $password The password to decrypt the certificate file.
     * @return string|null The private key, or null if the operation failed.
     */
    public static function loadKeyCertificate($filePath, $password)
    {
        $pkcs12 = file_get_contents($filePath);
        openssl_pkcs12_read($pkcs12, $certs, $password);
        return $certs['pkey'] ?? null;
    }

    /**
     * Encrypts the given data using the public key.
     *
     * @param string $data The data to encrypt.
     * @return string The base64-encoded encrypted data.
     * @throws Exception If the public key cannot be loaded.
     */
    public static function encrypt(string $data): string
    {
        $publicKeyPath = config('travfd.encryption.private_key');

        $publicKey = file_get_contents($publicKeyPath);
        if (!$publicKey) {
            throw new Exception("Failed to load public key from {$publicKeyPath}");
        }

        openssl_public_encrypt($data, $encrypted, $publicKey);
        return base64_encode($encrypted);
    }

     /**
     * Decrypts the given data using the private key.
     *
     * @param string $data The base64-encoded encrypted data to decrypt.
     * @param string $password The password to decrypt the private key, if required.
     * @return string The decrypted data.
     * @throws Exception If the private key cannot be loaded or the password is invalid.
     */
    public static function decrypt(string $data, string $password = ''): string
    {
        $privateKey = file_get_contents(config('travfd.encryption.private_key'));
        if (!$privateKey) {
            throw new Exception("Failed to load private key from {".config('travfd.encryption.private_key')."}");
        }

        $res = openssl_pkey_get_private($privateKey, $password);
        if (!$res) {
            throw new Exception("Invalid private key or incorrect password.");
        }

        openssl_private_decrypt(base64_decode($data), $decrypted, $res);
        return $decrypted;
    }
}
