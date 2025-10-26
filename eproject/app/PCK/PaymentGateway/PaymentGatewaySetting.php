<?php namespace PCK\PaymentGateway;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySetting extends Model
{
    protected $table = 'payment_gateway_settings';

    protected $fillable = [
        'payment_gateway',
        'is_sandbox',
        'is_active',
        'merchant_id',
        'key1',
        'key2',
    ];

    const GATEWAY_MANUAL = 'MANUAL';
    const GATEWAY_SENANGPAY = 'SENANGPAY';
    const GATEWAY_DEFAULT = self::GATEWAY_SENANGPAY;

    public function setKey1Attribute($value)
    {
        $this->attributes['key1'] = !empty($value) ? Crypt::encrypt($value) : null;
    }

    public function setKey2Attribute($value)
    {
        $this->attributes['key2'] = !empty($value) ? Crypt::encrypt($value) : null;
    }

    // Accessors for automatic decryption when getting the value
    public function getKey1Attribute($value)
    {
        if (!empty($value)) {
            try {
                return Crypt::decrypt($value);
            } catch (DecryptException $e) {
                // Handle decryption failure (e.g., log it) or return the original value
                return $value;
            }
        }

        return $value;
    }

    public function getKey2Attribute($value)
    {
        if (!empty($value)) {
            try {
                return Crypt::decrypt($value);
            } catch (DecryptException $e) {
                return $value;
            }
        }

        return $value;
    }

    public static function seed()
    {
        $dataTemplates = [
            [
                'payment_gateway'    => self::GATEWAY_MANUAL,
                'is_sandbox'         => false,
                'is_active'          => true,
                'merchant_id'        => 'merchantID',
                'key1'               => 'secretKey',
            ],
            [
                'payment_gateway'    => self::GATEWAY_SENANGPAY,
                'is_sandbox'         => true,
                'is_active'          => false,
                'merchant_id'        => 'merchantID',
                'key1'               => 'secretKey',
            ],
        ];

        foreach ($dataTemplates as $template) {
            $existingRecord = self::where('payment_gateway', $template['payment_gateway'])->first();

            if (! $existingRecord) {
                self::create($template);
            }
        }
    }

    public static function getTypeLabel($type)
    {
        switch($type) {
            case self::GATEWAY_MANUAL:
                return trans('paymentGateway/gateways.manual');

            case self::GATEWAY_SENANGPAY:
                return trans('paymentGateway/gateways.senangPay');

            default:
                throw new \Exception('Invalid type');
        }
    }

}