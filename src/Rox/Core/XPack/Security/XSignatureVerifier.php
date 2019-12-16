<?php

namespace Rox\Core\XPack\Security;

use Rox\Core\Security\SignatureVerifierInterface;

class XSignatureVerifier implements SignatureVerifierInterface
{
    /**
     * @param string $data
     * @param string $signatureBase64
     * @return bool
     */
    function verify($data, $signatureBase64)
    {
        if (!function_exists('openssl_verify')) {
            return true;
        }
        $cert = chunk_split(self::ROXCertificateBase64);
        $publicKey = openssl_pkey_get_public("-----BEGIN CERTIFICATE-----\n${cert}-----END CERTIFICATE-----");
        $signature = base64_decode($signatureBase64);
        return openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    const ROXCertificateBase64 = "MIIDWDCCAkACCQDR039HDUMyzTANBgkqhkiG9w0BAQUFADBuMQswCQYDVQQHEwJjYTETMBEGA1UEChMKcm9sbG91dC5pbzERMA8GA1UECxMIc2VjdXJpdHkxFzAVBgNVBAMTDnd3dy5yb2xsb3V0LmlvMR4wHAYJKoZIhvcNAQkBFg9leWFsQHJvbGxvdXQuaW8wHhcNMTQwODE4MDkzNjAyWhcNMjQwODE1MDkzNjAyWjBuMQswCQYDVQQHEwJjYTETMBEGA1UEChMKcm9sbG91dC5pbzERMA8GA1UECxMIc2VjdXJpdHkxFzAVBgNVBAMTDnd3dy5yb2xsb3V0LmlvMR4wHAYJKoZIhvcNAQkBFg9leWFsQHJvbGxvdXQuaW8wggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDq8GMRFLyaQVDEdcHlYm7NnGrAqhLP2E/27W21yTQein7r8FOT/7jJ0PLpcGLw/3zDT5wzIJ3OtFy4HWre2hn7wmt+bI+bbS/9kKrmqkpjAj1+PwnB4lhEad27lolMCuz5purqi209k7q51IMdfq0/Ot7P/Bmp+LBNs2F4jMsPYxZUUYkVTAmPqgnwxuWoJZan/OGNjtj9OGg8eOcOfcyxC4GDR/Yail+kht4I/HHesSXVukqXntsbdgnXKFkX682TuFPc3pd8ly+6N6OSWpbNV8UmEVZygnxWT3vxBT2TWvFexbW52KOFY91wIkjt+IPEMPJBPPDiN9J2nuttvfMpAgMBAAEwDQYJKoZIhvcNAQEFBQADggEBAIXrD6YsIhZa6fYDAR8huP0V3BRwMKjeLGLCXLzvuPaoQGDhn4RJNgz3leNcomIkV/AwneeS9BXgBAcEKjNeLD+nW58RSRnAfxDT5cUtQgIeR6dFmEK05u+8j/cK3VO410xr0taNMbmJfEn07WjfCdcJS3hsGJuVmEUC85KYznbIcafQMGklLYArXYVnR3XKqzxcLohSPX99weujH5wt78Zy3pXxuYCDETwhgcCYCQaZz7mpvtSOub3JQT+Ir5cBSdyI1oPI2dIamUL5+ntTyll/1rbYj83qREw8PKA9Q0KIIgfpggy19TS9zknwOLz44wRdLyT2tFoaiRqHvm6JKaA=";
}
