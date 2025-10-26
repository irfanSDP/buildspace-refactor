<?php namespace PCK\Payment;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $table = 'payment_settings';

    protected $fillable = [
        'name',
        'account_number',
        'is_user_selectable',
        'created_by',
        'updated_by',
    ];

    public static function getPaymentMethods()
    {
        $paymentMethods = [];

        foreach(self::where('is_user_selectable', true)->orderBy('id', 'ASC')->get() as $paymentMethod)
        {
            array_push($paymentMethods, [
                'id'   => $paymentMethod->id,
                'name' => $paymentMethod->name,
            ]);
        }

        return $paymentMethods;
    }
}