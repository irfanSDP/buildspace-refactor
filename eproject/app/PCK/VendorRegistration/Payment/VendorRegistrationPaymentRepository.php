<?php namespace PCK\VendorRegistration\Payment;

use Carbon\Carbon;
use PCK\Companies\Company;
use PCK\Payment\PaymentSetting;

class VendorRegistrationPaymentRepository
{
    public function addPayment($data)
    {
        $record = new VendorRegistrationPayment();
        $record->company_id         = $data['company_id'];
        $record->payment_setting_id = $data['payment_setting_id'];
        $record->running_number     = VendorRegistrationPayment::getNextFreeRunningNumber($data['payment_setting_id']);
        $record->currently_selected = $data['currently_selected'] ?? false;

        if (isset($data['paid_date'])) {
            $record->paid_date = $data['paid_date'];
        }
        if (isset($data['paid'])) {
            $record->paid = $data['paid'];
        }
        if (isset($data['successful_date'])) {
            $record->successful_date = $data['successful_date'];
        }
        if (isset($data['successful'])) {
            $record->successful = $data['successful'];
        }

        $record->save();
        $record = VendorRegistrationPayment::find($record->id);

        if ($record->currently_selected) {
            VendorRegistrationPayment::deselectOtherRecords($record);
        }

        return $record;
    }

    public function getSelectedPaymentMethod(PaymentSetting $paymentSetting, $companyId=null)
    {
        return VendorRegistrationPayment::selectedPaymentMethod($paymentSetting, $companyId);
    }

    public function getVirtualAccountNumber(PaymentSetting $paymentSetting)
    {
        $vendorRegistrationPaymentRecord = $this->getSelectedPaymentMethod($paymentSetting);
        return $vendorRegistrationPaymentRecord->getVirtualAccountNumber();
    }

    public function getLatestPaidViaPaymentGateway($companyId)
    {
        return VendorRegistrationPayment::where('company_id', $companyId)
            ->where('paid', true)
            ->whereHas('orderItemVendorRegPayment')
            ->orderBy('paid_date', 'desc')
            ->first();
    }

    public function getLatestPaid($companyId)
    {
        return VendorRegistrationPayment::where('company_id', $companyId)
            ->where('paid', true)
            ->orderBy('paid_date', 'desc')
            ->first();
    }

    public function allowPayment(Company $company)
    {
        $result = ['allow' => false, 'message' => null];

        $latestPaid = $this->getLatestPaid($company->id);
        //$isInRenewalPeriod = $company->vendorRegistration->isLatestFinalized() && $company->inRenewalPeriod();
        $isInRenewalPeriod = $company->inRenewalPeriod();

        if ($latestPaid) { // Paid record exists
            if (! is_null($company->expiry_date)) {  // Expiry exists -> is renewal
                if ($isInRenewalPeriod) {   // In renewal period
                    $paidDate = Carbon::parse($latestPaid->paid_date);

                    if ($paidDate->gte($company->getRenewalWindowStartDate())) {   // Already paid in renewal period, deny
                        $result['message'] = trans('vendorRegistrationPayment.paymentAlreadyMade');
                        return $result;
                    } else {    // Not paid in renewal period, allow
                        $result['allow'] = true;
                        return $result;
                    }
                } else {    // Not in renewal period, deny
                    $result['message'] = trans('vendorRegistrationPayment.notWithinRenewalPeriod');
                    return $result;
                }
            }

            // No expiry (new registration) and has paid record -> deny
            $result['allow'] = false;
            $result['message'] = trans('vendorRegistrationPayment.paymentAlreadyMade');
            return $result;
        }

        // No paid record, handle first payment
        if (! is_null($company->expiry_date)) { // Expiry exists -> is renewal
            if ($isInRenewalPeriod) {   // In renewal period, allow payment
                $result['allow'] = true;
                return $result;
            } else {    // Not in renewal period, deny
                $result['message'] = trans('vendorRegistrationPayment.notWithinRenewalPeriod');
                return $result;
            }
        }

        // No expiry and paid record, allow payment (new registration)
        $result['allow'] = true;
        return $result;
    }
}