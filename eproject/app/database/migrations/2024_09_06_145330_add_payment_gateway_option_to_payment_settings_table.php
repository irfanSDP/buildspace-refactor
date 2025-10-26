<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\PaymentGateway\PaymentGatewaySetting;
use PCK\Payment\PaymentSetting;
use PCK\Users\User;

class AddPaymentGatewayOptionToPaymentSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('payment_settings', 'is_user_selectable'))
        {
            Schema::table('payment_settings', function (Blueprint $table) {
                $table->boolean('is_user_selectable')->default(true)->after('account_number');
            });
        }

        // Insert payment settings
        $superAdmin = User::where('is_super_admin', true)->first();
        $paymentSettings = [
            [
                'name'            => PaymentGatewaySetting::GATEWAY_SENANGPAY,
                'account_number'  => '0000000000',
                'is_user_selectable' => false,
                'created_by'      => $superAdmin->id,
                'updated_by'      => $superAdmin->id,
            ],
        ];

        foreach($paymentSettings as $paymentSetting)
        {
            if (PaymentSetting::where('name', $paymentSetting['name'])->where('is_user_selectable', $paymentSetting['is_user_selectable'])->exists())
            {
                continue;
            }

            $record = new PaymentSetting();
            $record->name           = $paymentSetting['name'];
            $record->account_number = $paymentSetting['account_number'];
            $record->is_user_selectable = $paymentSetting['is_user_selectable'];
            $record->created_by     = $paymentSetting['created_by'];
            $record->updated_by     = $paymentSetting['updated_by'];
            $record->save();
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        // Delete payment settings
        $paymentSettings = [
            PaymentGatewaySetting::GATEWAY_SENANGPAY,
        ];

        if (PaymentSetting::whereIn('name', $paymentSettings)->where('is_user_selectable', false)->exists())
        {
            PaymentSetting::whereIn('name', $paymentSettings)->where('is_user_selectable', false)->delete();
        }

        if (Schema::hasColumn('payment_settings', 'is_user_selectable'))
        {
            Schema::table('payment_settings', function (Blueprint $table) {
                $table->dropColumn('is_user_selectable');
            });
        }
	}

}
