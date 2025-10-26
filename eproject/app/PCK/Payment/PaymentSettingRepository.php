<?php namespace PCK\Payment;

class PaymentSettingRepository
{
    public function getRecord($data)
    {
        $query = PaymentSetting::query();
        if (isset($data['id'])) {
            $query->where('id', $data['id']);
        }
        if (isset($data['name'])) {
            $query->where('name', $data['name']);
        }
        if (isset($data['isUserSelectable'])) {
            $query->where('is_user_selectable', $data['isUserSelectable']);
        }
        return $query->first();
    }

    public function getAllRecords()
    {
        $payments = [];

        foreach(PaymentSetting::where('is_user_selectable', true)->orderBy('id', 'ASC')->get() as $payment)
        {
            $payments[] = [
                'id' => $payment->id,
                'name' => $payment->name,
                'accountNumber' => $payment->account_number,
                'route_edit' => route('payment.settings.update', [$payment->id]),
                'route_delete' => route('payment.settings.delete', [$payment->id])
            ];
        }

        return $payments;
    }

    public function createNewRecord($inputs)
    {
        $record = new PaymentSetting();
        $record->name           = $inputs['name'];
        $record->account_number = $inputs['accountNumber'];
        $record->created_by     = \Confide::user()->id;
        $record->updated_by     = \Confide::user()->id;
        $record->save();

        return PaymentSetting::find($record->id);
    }

    public function updateRecord(PaymentSetting $record, $inputs)
    {
        $record->name           = $inputs['name'];
        $record->account_number = $inputs['accountNumber'];
        $record->updated_by     = \Confide::user()->id;
        $record->save();

        return PaymentSetting::find($record->id);
    }
}