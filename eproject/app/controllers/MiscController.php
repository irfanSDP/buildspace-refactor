<?php

class MiscController extends \BaseController {

    public function spellCurrencyAmount()
    {
        $amount       = Input::get('amount');
        $currencyName = Input::get('currency_name');

        return \PCK\Helpers\NumberToTextConverter::spellCurrencyAmount($amount, $currencyName);
    }

}