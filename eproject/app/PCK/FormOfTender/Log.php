<?php namespace PCK\FormOfTender;

use Illuminate\Database\Eloquent\Model;
use PCK\Tenders\Tender;

class Log extends Model {

    protected $table = 'form_of_tender_logs';

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function formOfTender()
    {
        return $this->belongsTo('PCK\FormOfTender\FormOfTender');
    }

    public static function getByTender(Tender $tender)
    {
        return $tender->formOfTender->logs;
    }

}