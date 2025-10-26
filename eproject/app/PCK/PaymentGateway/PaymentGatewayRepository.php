<?php namespace PCK\PaymentGateway;

use Illuminate\Support\Facades\Crypt;
use PCK\Helpers\StringOperations;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;
use PCK\Orders\OrderItem;
use PCK\Orders\OrderPayment;
use PCK\Orders\OrderRepository;
use PCK\Projects\ProjectRepository;
use PCK\Tenders\TenderRepository;

class PaymentGatewayRepository
{
    private $paymentGatewaySettingRepository;
    private $orderRepository;
    private $projectRepository;
    private $tenderRepository;

    public function __construct(
        PaymentGatewaySettingRepository $paymentGatewaySettingRepository,
        OrderRepository $orderRepository,
        ProjectRepository $projectRepository,
        TenderRepository $tenderRepository
    ) {
        $this->paymentGatewaySettingRepository = $paymentGatewaySettingRepository;
        $this->orderRepository = $orderRepository;
        $this->projectRepository = $projectRepository;
        $this->tenderRepository = $tenderRepository;
    }

    public function getPayButton($paymentGateway)
    {
        $setting = $this->paymentGatewaySettingRepository->getByGateway($paymentGateway);
        if (! $setting) {
            return false;
        }

        $imageUrl = empty($setting->button_image_url) ? $this->paymentGatewaySettingRepository->getImageUrl($setting->payment_gateway, $setting->is_sandbox) : $setting->button_image_url;

        $paymentGatewayData = [
            'payment_gateway' => $setting->payment_gateway,
            'image_url' => $imageUrl,
        ];

        return \View::make('payments.gateway.partials.payment-button', compact('paymentGatewayData'))->render();
    }

    public function getPaymentGatewayForm($paymentGateway, $user, $type, $origin=null, $projectId=null, $tenderId=null)
    {
        // Get the payment gateway setting
        $setting = $this->paymentGatewaySettingRepository->getByGateway($paymentGateway);
        if (! $setting) {
            return false;
        }

        if (! empty($projectId)) {  // If project ID is provided
            // Get the project and tender
            $project = $this->projectRepository->find($projectId);
            if (! $project) {
                return false;
            }
            if (! empty($tenderId)) {
                $tender = $this->tenderRepository->find($project, $tenderId);
            }
            $subsidiary = $project->subsidiary;
            $sellerCompanyId = $subsidiary->company_id;
        }

        // Description
        $description = $this->orderRepository->getTypeLabel($type);

        // Get the price (and project title if applicable)
        switch ($type) {
            case OrderItem::TYPE_VENDOR_REG:
                $price = VendorProfileModuleParameter::getValue('registration_price');
                break;

            case OrderItem::TYPE_VENDOR_RENEWAL:
                $price = VendorProfileModuleParameter::getValue('renewal_price');
                break;

            case OrderItem::TYPE_OPEN_TENDER:
                if (isset($project) && isset($tender)) {
                    $description .= ' ('.$project->reference.')';
                    $tenderInfo = $tender->openTenderPageInformation;
                    $price = $tenderInfo->open_tender_price;
                } else {
                    return false;
                }
                break;

            default:
                return false;
        }

        // Create the order
        $order = $this->orderRepository->create([
            'paymentGateway' => $setting->payment_gateway,
            'buyerId' => $user->id,
            'buyerCompanyId' => $user->company_id,
            'sellerCompanyId' => $sellerCompanyId ?? null,
            'type' => $type,
            'price' => $price,
            'description' => $description,
            'projectId' => $projectId ?? null,
            'tenderId' => $tenderId ?? null,
            'origin' => $origin ?? null,
        ]);
        if (! $order) {
            return false;
        }

        // Payment gateway specific formatting
        switch ($paymentGateway) {
            case PaymentGatewaySetting::GATEWAY_SENANGPAY:
                $description = StringOperations::replace($description, ' ', '_');
                break;

            default:
                // Do nothing
        }

        // Return the payment form
        switch ($setting->payment_gateway) {
            case PaymentGatewaySetting::GATEWAY_SENANGPAY:
                $paymentGatewayData = [
                    'detail' => $description,
                    'amount' => $price,
                    'order_id' => $order->reference_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->contact_number,
                ];
                if ($setting->is_sandbox) { // Sandbox
                    $baseUrl = 'https://sandbox.senangpay.my/';
                } else {    // Live
                    $baseUrl = 'https://app.senangpay.my/';
                }
                $paymentGatewayData['image_url'] = empty($setting->button_image_url) ? $baseUrl . 'public/img/pay.png' : $setting->button_image_url;
                $paymentGatewayData['payment_url'] = $baseUrl . 'payment/' . $setting->merchant_id;
                $paymentGatewayData['hash'] = $this->createHash($setting->payment_gateway, [
                    'str' => $setting->key1.$paymentGatewayData['detail'].$paymentGatewayData['amount'].$paymentGatewayData['order_id'],
                    'key' => $setting->key1
                ]);
                return \View::make('payments.gateway.partials.payment-form', compact('paymentGatewayData'))->render();

            default:
                // Do nothing
        }
        return false;
    }

    public function formatMessage($paymentGateway, $originalString)
    {
        switch ($paymentGateway) {
            case PaymentGatewaySetting::GATEWAY_SENANGPAY:
                return StringOperations::replace($originalString, '_', ' ');

            default:
                return $originalString;
        }
    }

    public function getStatus($paymentGateway, $statusId)
    {
        switch ($paymentGateway) {
            case PaymentGatewaySetting::GATEWAY_SENANGPAY:
                switch ($statusId) {
                    case 1:
                        return OrderPayment::STATUS_SUCCESS;
                    case 2:
                        return OrderPayment::STATUS_PENDING;
                    default:
                        return OrderPayment::STATUS_FAILED;
                }

            default:
                return 'UNKNOWN';
        }
    }

    public function createHash($paymentGateway, $data)
    {
        switch ($paymentGateway) {
            case PaymentGatewaySetting::GATEWAY_SENANGPAY:
                return hash_hmac('sha256', $data['str'], $data['key']);

            default:
                return false;
        }
    }

    public function verifyHash($paymentGateway, $receivedHash, $data) {
        switch ($paymentGateway) {
            case PaymentGatewaySetting::GATEWAY_SENANGPAY:
                $setting = $this->paymentGatewaySettingRepository->getByGateway($paymentGateway);
                if (! $setting) {
                    return false;
                }
                $str = $setting->key1;
                if (! empty($data['status_id'])) {
                    $str .= $data['status_id'];
                }
                if (! empty($data['order_id'])) {
                    $str .= $data['order_id'];
                }
                if (! empty($data['transaction_id'])) {
                    $str .= $data['transaction_id'];
                }
                if (! empty($data['msg'])) {
                    $str .= $data['msg'];
                }
                $hash = $this->createHash($paymentGateway, [
                    'str' => $str,
                    'key' => $setting->key1
                ]);
                if ($receivedHash === $hash) {
                    return true;
                }
                return false;

            default:
                return false;
        }
    }

    public function processResult($data)
    {
        switch ($data['payment_gateway']) {
            case PaymentGatewaySetting::GATEWAY_SENANGPAY:
                $order = $this->orderRepository->getOrderByReferenceId($data['order_id']);
                if (! $order || empty($data['hash'])) {
                    return false;
                }
                $record = new PaymentGatewayResult();
                $record->payment_gateway = $data['payment_gateway'];
                $record->transaction_id = $data['transaction_id'];
                $record->reference_id = $data['order_id'];
                $record->status = $this->getStatus($data['payment_gateway'], $data['status_id']);
                $record->info = $this->formatMessage($data['payment_gateway'], $data['msg']);
                $record->verified = $this->verifyHash($data['payment_gateway'], $data['hash'], $data);
                $record->is_ipn = $data['is_ipn'] ?? false;
                $record->data = json_encode($data);
                $record->save();
                return $record;

            default:
                return false;
        }
    }
}