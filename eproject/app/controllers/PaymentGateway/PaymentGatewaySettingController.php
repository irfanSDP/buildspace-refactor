<?php

namespace PaymentGateway;

use PCK\PaymentGateway\PaymentGatewaySetting;
use PCK\PaymentGateway\PaymentGatewaySettingRepository;

class PaymentGatewaySettingController extends \BaseController
{
    protected $paymentGatewaySettingRepository;

    public function __construct(
        PaymentGatewaySettingRepository $paymentGatewaySettingRepository
    ) {
        $this->paymentGatewaySettingRepository = $paymentGatewaySettingRepository;
    }

    public function edit()
    {
        $record = $this->paymentGatewaySettingRepository->getByGateway(PaymentGatewaySetting::GATEWAY_SENANGPAY);
        $gatewayImg = $this->paymentGatewaySettingRepository->getImageUrl($record->payment_gateway, $record->is_sandbox);

        return \View::make('payments.gateway.edit', compact('record', 'gatewayImg'));
    }

    public function update($id)
    {
        $input = \Input::all();

        $success = $this->paymentGatewaySettingRepository->update($id, $input);
        if ($success) {
            \Flash::success(trans('forms.updateSuccessful'));
        } else {
            \Flash::error(trans('forms.anErrorOccured'));
        }

        return \Redirect::route('payment-gateway.settings.edit');
    }
}
