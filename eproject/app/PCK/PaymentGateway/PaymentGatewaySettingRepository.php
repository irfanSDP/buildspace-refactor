<?php namespace PCK\PaymentGateway;

class PaymentGatewaySettingRepository
{
    /*public function __construct()
    {
        //
    }*/

    public function getById($id)
    {
        return PaymentGatewaySetting::find($id);
    }

    public function getByGateway($gateway, $isActive=null)
    {
        $query = PaymentGatewaySetting::where('payment_gateway', $gateway);

        if (! is_null($isActive)) {
            $query->where('is_active', $isActive);
        }
        return $query->first();
    }

    public function getDefaultGateway($isActive=null)
    {
        return $this->getByGateway(PaymentGatewaySetting::GATEWAY_DEFAULT, $isActive);
    }

    public function getAll($isActive=null)
    {
        if (is_null($isActive)) {
            return PaymentGatewaySetting::orderBy('id', 'DESC')->get();
        } else {
            return PaymentGatewaySetting::where('is_active', $isActive)->orderBy('id', 'DESC')->get();
        }
    }

    public function update($id, $inputs)
    {
        $record = $this->getById($id);
        if (! $record) {
            return false;
        }

        $record->is_active = $inputs['isActive'] ?? false;
        $record->is_sandbox = $inputs['isSandbox'] ?? false;

        if (isset($inputs['paymentGateway'])) {
            $record->payment_gateway = $inputs['paymentGateway'];
        }
        if (isset($inputs['merchantId'])) {
            $record->merchant_id = ! empty($inputs['merchantId']) ? $inputs['merchantId'] : 'merchantID';
        }
        if (isset($inputs['key1'])) {
            $record->key1 = ! empty($inputs['key1']) ? $inputs['key1'] : 'secretKey';
        }
        if (isset($inputs['key2'])) {
            $record->key2 = ! empty($inputs['key2']) ? $inputs['key2'] : 'secretKey2';
        }
        if (isset($inputs['buttonImageUrl'])) {
            $record->button_image_url = ! empty($inputs['buttonImageUrl']) ? $inputs['buttonImageUrl'] : null;
        }

        $record->save();

        return true;
    }

    public function getImageUrl($paymentGateway, $isSandbox=false)
    {
        $imgUrl = '';

        switch ($paymentGateway) {
            case PaymentGatewaySetting::GATEWAY_SENANGPAY:
                if ($isSandbox) {
                    $imgUrl = 'https://sandbox.senangpay.my/public/img/pay.png';
                } else {
                    $imgUrl = 'https://app.senangpay.my/public/img/pay.png';
                }
                break;

            default:
                // Do nothing;
        }
        return $imgUrl;
    }

    public function getSelections()
    {
        $selections = [];

        $settings = $this->getAll(true);

        foreach ($settings as $setting)
        {
            $selections[$setting->payment_gateway] = PaymentGatewaySetting::getTypeLabel($setting->payment_gateway);
        }

        return $selections;
    }
}