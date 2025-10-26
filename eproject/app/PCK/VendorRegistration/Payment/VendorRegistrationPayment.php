<?php namespace PCK\VendorRegistration\Payment;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Payment\PaymentSetting;
use PCK\Companies\Company;
use PCK\Helpers\StringOperations;
use PCK\Statuses\FormStatus;

class VendorRegistrationPayment extends Model implements FormStatus
{
    use ModuleAttachmentTrait;

    protected $table = 'vendor_registration_payments';

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public function paymentSetting()
    {
        return $this->belongsTo('PCK\Payment\PaymentSetting');
    }

    public function orderItemVendorRegPayment()
    {
        return $this->hasOne('PCK\Orders\OrderItemVendorRegPayment');
    }

    public function isSubmitted()
    {
        return $this->submitted;
    }

    public function isPaid()
    {
        return $this->paid;
    }

    public function isCompleted()
    {
        return $this->successful;
    }

    public function isRejected()
    {
        return ($this->status == self::STATUS_REJECTED);
    }

    public function getStatusText()
    {
        if($this->isCompleted()) return trans('vendorManagement.completed');
        if($this->isPaid()) return trans('vendorManagement.paid');
        if($this->isSubmitted()) return trans('vendorManagement.submitted');

        return null;
    }

    public static function getNextFreeRunningNumber($paymentSettingId)
    {
        $record = self::where('payment_setting_id', $paymentSettingId)->orderBy('running_number', 'DESC')->first();

        if(is_null($record)) return 1;

        return ($record->running_number + 1);
    }

    public static function getCurrentlySelectedPaymentMethodRecord(Company $company)
    {
        return self::where('company_id', $company->id)->where('currently_selected', true)->first();
    }

    public static function selectedPaymentMethod(PaymentSetting $paymentSetting, $companyId=null)
    {
        // latest not submitted record for a given company
        $companyId = is_null($companyId) ? \Confide::user()->company->id : $companyId;
        $record = self::where('company_id', $companyId)->where('payment_setting_id', $paymentSetting->id)->first();

        if (! $record) {
            $record                     = new self();
            $record->company_id         = $companyId;
            $record->payment_setting_id = $paymentSetting->id;
            $record->running_number     = self::getNextFreeRunningNumber($paymentSetting->id);
        }

        if (! $record->currently_selected) {
            $record->currently_selected = true;
            $record->save();
            $record = self::find($record->id);
        }

        self::deselectOtherRecords($record);

        return $record;
    }

    public static function deselectOtherRecords(self $selectedRecord)
    {
        self::where('company_id', $selectedRecord->company_id)
            ->where('id', '!=' ,$selectedRecord->id)
            ->update(['currently_selected' => false]);
    }

    public function getVirtualAccountNumber()
    {
        return ($this->paymentSetting->account_number . '-' . StringOperations::pad($this->running_number, 10, '0'));
    }

    public static function getAllRecordsByCompany(Company $company)
    {
        return self::where('company_id', $company->id)->orderBy('id', 'ASC')->get();
    }

    public static function getVendorRegistrationPayments(Company $company)
    {
        $payments = [];
        $payment  = self::getCurrentlySelectedPaymentMethodRecord($company);

        if($payment)
        {
            array_push($payments, [
                'label'             => $payment->paymentSetting->name,
                'values'            => [$payment->paymentSetting->account_number],
                'route_attachments' => route('vendor.registration.payment.attachements.get', [$payment->id]),
                'attachments_count' => $payment->attachments->count(),
            ]);

            return $payments;
        }

        return [];
    }
}