<?php

namespace Api;

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use PCK\Orders\OrderItemVendorRegPayment;
use PCK\Orders\Order;
use PCK\Orders\OrderItem;
use PCK\Orders\OrderPayment;
use PCK\Orders\OrderRepository;
use PCK\PaymentGateway\PaymentGatewayRepository;
use PCK\PaymentGateway\PaymentGatewaySetting;
use PCK\Payment\PaymentSettingRepository;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;
use PCK\Tenders\CompanyTender;
use PCK\VendorRegistration\Payment\VendorRegistrationPaymentRepository;
use PCK\VendorRegistration\Payment\VendorRegistrationPayment;
use Request;

class PaymentGatewayController extends \BaseController
{
    protected $langLocale;
    protected $paymentGateway;
    protected $orderItem;
    protected $buyerCompanyId;
    protected $paymentStatus;
    protected $paymentVerified;

    private $orderRepository;
    private $paymentGatewayRepository;
    private $paymentSettingRepository;
    private $vendorRegistrationPaymentRepository;

    public function __construct(
        OrderRepository $orderRepository,
        PaymentGatewayRepository $paymentGatewayRepository,
        PaymentSettingRepository $paymentSettingRepository,
        VendorRegistrationPaymentRepository $vendorRegistrationPaymentRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentGatewayRepository = $paymentGatewayRepository;
        $this->paymentSettingRepository = $paymentSettingRepository;
        $this->vendorRegistrationPaymentRepository = $vendorRegistrationPaymentRepository;
    }

    public function returnUrl($paymentGateway)
    {
        $failedUrl = \URL::route('home.index');

        $data = Request::all();
        if (empty($data)) {
            return \Redirect::to($failedUrl);
        }

        $this->paymentGateway = strtoupper($paymentGateway);
        $data['payment_gateway'] = $this->paymentGateway;

        // Process payment result
        $result = $this->paymentGatewayRepository->processResult($data);

        if (! $result) {
            return \Redirect::to($failedUrl);
        }
        if (! $result->verified) {
            return \Redirect::to($failedUrl);
        }
        $this->paymentVerified = true;
        $this->paymentStatus = $result->status;

        // Order
        $order = $result->order;

        // Order item
        $this->orderItem = $result->getOrderItem();
        if (! $this->orderItem) {
            return \Redirect::to($failedUrl);
        }

        // Buyer company ID
        $this->buyerCompanyId = $this->orderItem->orderSub->order->company_id;

        // Get lang locale
        switch ($this->orderItem->type) {
            case OrderItem::TYPE_OPEN_TENDER:
                $this->langLocale = 'ms'; // Malay
                break;

            default:
                $this->langLocale = 'en'; // English
        }

        // Notification message
        $notifyMsg = $this->getTrans('paymentGateway/paymentResult.referenceId', $this->langLocale).' '.$result->reference_id.': ';

        // Update payment details
        $this->orderRepository->updatePayment($result);

        // Perform action based on payment status
        switch ($this->paymentStatus) {
            case OrderPayment::STATUS_SUCCESS:
                $this->paymentSuccess();

                // Notification message
                \Flash::success($notifyMsg . $this->getTrans('paymentGateway/paymentResult.success', $this->langLocale));
                break;

            case OrderPayment::STATUS_FAILED:
                \Flash::error($notifyMsg . $this->getTrans('paymentGateway/paymentResult.failed', $this->langLocale));
                break;

            default:    // Pending
                \Flash::warning($notifyMsg . $this->getTrans('paymentGateway/paymentResult.pending', $this->langLocale));
        }

        // Redirect based on order's origin field
        switch ($order->origin) {
            case Order::ORIGIN_REG:
            case Order::ORIGIN_RENEWAL:
                return \Redirect::route('vendor.registration.payment.index');

            case Order::ORIGIN_TENDER:
                $project = $this->orderItem->project;
                if (! $project) {
                    return \Redirect::to($failedUrl);
                }
                return \Redirect::route('open_tenders.detail_project', [$project->id]);

            default:    // Default -> Home
                return \Redirect::route('home.index');
        }
    }

    public function callbackUrl($paymentGateway)
    {
        $response = 'NOT OK';
        $data = Request::all();

        if (empty($data)) {
            return $response;
        }

        $this->paymentGateway = strtoupper($paymentGateway);
        $data['payment_gateway'] = $this->paymentGateway;
        $data['is_ipn'] = true;

        // Process payment result
        $result = $this->paymentGatewayRepository->processResult($data);

        if (! $result) {
            return $response;
        }
        if (! $result->verified) {
            return $response;
        }
        $this->paymentVerified = true;
        $this->paymentStatus = $result->status;

        // Update payment details
        $this->orderRepository->updatePayment($result);

        // Order item
        $this->orderItem = $result->getOrderItem();
        if (! $this->orderItem) {
            return $response;
        }

        // Buyer company ID
        $this->buyerCompanyId = $this->orderItem->orderSub->order->company_id;

        // Perform action based on payment status
        switch ($this->paymentStatus) {
            case OrderPayment::STATUS_SUCCESS:
                $this->paymentSuccess();
                break;

            default:    // Pending
                // Do nothing
        }

        // Response to payment gateway
        switch ($this->paymentGateway) {
            case PaymentGatewaySetting::GATEWAY_SENANGPAY:
                $response = 'OK';
                break;

            default:
                // Do nothing
        }
        return $response;
    }

    private function getTrans($key, $langLocale)
    {
        return trans($key, [], 'messages', $langLocale);
    }

    private function paymentSuccess()
    {
        switch ($this->orderItem->type) {
            case OrderItem::TYPE_VENDOR_REG:
            case OrderItem::TYPE_VENDOR_RENEWAL:
                $this->updateVendorRegistrationPayment();  // Update vendor registration payment
                break;

            case OrderItem::TYPE_OPEN_TENDER:
                $this->insertContractorIntoTenderDetails(); // Insert contractor into tender details
                break;

            default:    // Unknown
                // Do nothing
        }
    }

    private function updateVendorRegistrationPayment()
    {
        // Get vendor registration payment setting
        $paymentSetting = $this->paymentSettingRepository->getRecord(['name' => $this->paymentGateway, 'isUserSelectable' => false]);
        if (! $paymentSetting) {
            return false;
        }

        // Add vendor registration payment
        $paidDate = Carbon::now();
        $vendorRegistrationPayment = $this->vendorRegistrationPaymentRepository->addPayment([
            'company_id' => $this->buyerCompanyId,
            'payment_setting_id' => $paymentSetting->id,
            'currently_selected' => true,
            'paid_date' => $paidDate,
            'paid' => true,
            'successful_date' => $paidDate,
            'successful' => true,
        ]);

        // Create order item vendor registration payment record if not exists
        $orderItemVendorRegPayment = OrderItemVendorRegPayment::where('order_item_id', $this->orderItem->id)
            ->where('vendor_registration_payment_id', $vendorRegistrationPayment->id)
            ->first();
        if (! $orderItemVendorRegPayment) {
            $orderItemVendorRegPayment = new OrderItemVendorRegPayment();
            $orderItemVendorRegPayment->order_item_id = $this->orderItem->id;
            $orderItemVendorRegPayment->vendor_registration_payment_id = $vendorRegistrationPayment->id;
            $orderItemVendorRegPayment->save();
        }

        return true;
    }

    private function insertContractorIntoTenderDetails()
    {
        $companyId = $this->buyerCompanyId;
        $tender = $this->orderItem->tender;

        try{
            $contractor = $tender->listOfTendererInformation->selectedContractors()->where('company_id', $companyId)->first();

            if($contractor)
            {
                $tender->listOfTendererInformation->selectedContractors()->detach($companyId);
                $tender->callingTenderInformation->selectedContractors()->detach($companyId);
            }

            // attach into list of tenderer form
            $tender->listOfTendererInformation->selectedContractors()->attach($companyId, array(
                'added_by_gcd' => false,
                'status' => ContractorCommitmentStatus::TENDER_OK
            ));

            // attach into calling tender form
            $tender->callingTenderInformation->selectedContractors()->attach($companyId, ['status' => ContractorCommitmentStatus::TENDER_OK]);

            // create company tender record
            $companyTender             = new CompanyTender;
            $companyTender->tender_id  = $tender->id;
            $companyTender->company_id = $companyId;
            $companyTender->save();

        }catch (\Exception $e)
        {
            return $e->getMessage();
        }

        return true;
    }

    public function getPaymentBtn()
    {
        $input = Input::all();
        if (! isset($input['pg'])) {
            return Response::json(false);
        }

        $html = $this->paymentGatewayRepository->getPayButton($input['pg']);
        if (! $html) {
            return Response::json(false);
        }
        return Response::json([
            'html' => base64_encode($html),
        ]);
    }

    public function getPaymentForm()
    {
        $result = ['success' => false, 'msg' => ''];
        $this->langLocale = 'en';

        $input = Input::all();

        if (! isset($input['pg'])) {
            $result['msg'] = $this->getTrans('errors.anErrorHasOccured', $this->langLocale);
            return Response::json($result);
        }
        if (empty($input['d'])) {
            $result['msg'] = $this->getTrans('errors.anErrorHasOccured', $this->langLocale);
            return Response::json($result);
        }
        $data = unserialize(Crypt::decrypt($input['d']));

        if (! empty($data['lang'])) {
            $this->langLocale = $data['lang'];
        }

        $user = \Confide::user();
        if (! $user) {
            $result['msg'] = $this->getTrans('auth.loginRequired', $this->langLocale);
            return Response::json($result);
        }

        if (isset($data['projectId']) && isset($data['tenderId'])) {
            if ($this->orderRepository->getOrderByProjectTender($user->id, $data['projectId'], $data['tenderId'])) {
                $result['msg'] = $this->getTrans('orders.tenderPaymentExists', $this->langLocale);
                return Response::json($result);
            }
        } else {
            $allowPayment = $this->vendorRegistrationPaymentRepository->allowPayment($user->company);
            if (! $allowPayment['allow']) {
                $result['msg'] = $allowPayment['message'];
                return Response::json($result);
            }
        }

        $html = $this->paymentGatewayRepository->getPaymentGatewayForm(
            $input['pg'],
            $user,
            $data['type'],
            $data['origin'] ?? null,
            $data['projectId'] ?? null,
            $data['tenderId'] ?? null
        );
        if (! $html) {
            $result['msg'] = $this->getTrans('errors.anErrorHasOccured', $this->langLocale);
            return Response::json($result);
        }

        $result['success'] = true;
        $result['data'] = base64_encode($html);

        return Response::json($result);
    }
}
