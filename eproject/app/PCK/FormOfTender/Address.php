<?php namespace PCK\FormOfTender;

use Illuminate\Database\Eloquent\Model;

class Address extends Model {

    protected $table = 'form_of_tender_addresses';
    const DEFAULT_TEXT = 'Template Address';

    public function formOfTender()
    {
        return $this->belongsTo('PCK\FormOfTender\FormOfTender');
    }
}