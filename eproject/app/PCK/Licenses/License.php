<?php namespace PCK\Licenses;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class License extends Model
{
    // use SoftDeletingTrait;

    protected $table = 'licenses';

    public static function getActiveLicense()
    {
        return self::first();
    }

    public static function checkValidity($licenseKey, $decryptionKey)
    {
        $decryptedLicenseData = unserialize(self::safeDecrypt($licenseKey, base64_decode($decryptionKey)));
        $validUntilDate = Carbon::parse($decryptedLicenseData['validUntilDateTime']);
        return $validUntilDate >= Carbon::now();
    }

    public static function getDecryptionKey($licenseKey)
    {
        $decryptionKey = null;
        $applicationId = getenv('APPLICATION_ID');
        $applicationKey = getenv('APPLICATION_KEY');

        $client = new Client(array(
            'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
            'base_uri' => getenv('INVOICE_SYSTEM_URL'),
        ));
        
        try
        {
            $response = $client->post('api/licensing/getDecryptionKey', array(
                'form_params' => array(
                    'applicationId'  => $applicationId,
                    'applicationKey' => $applicationKey,
                    'licenseKey'     => $licenseKey,
                )
            ));

            $response = json_decode($response->getBody());

            $decryptionKey = $response->decryptionKey;
        }
        catch(\Exception $e)
        {
           \Log::info("Get decryption key fails. [application_id: { $applicationId } application_key: { $applicationKey }] => {$e->getMessage()}");
        }

        return $decryptionKey;
    }

    public static function safeDecrypt(string $encrypted, string $key): string
    {   
        $decoded = base64_decode($encrypted);
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        $plain = sodium_crypto_secretbox_open(
            $ciphertext,
            $nonce,
            $key
        );

        if (!is_string($plain)) {
            throw new \Exception('Invalid License Key');
        }

        sodium_memzero($ciphertext);
        sodium_memzero($key);

        return $plain;
    }
}

