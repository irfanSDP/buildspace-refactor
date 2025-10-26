<?php namespace PCK\FormOfTender;

use Illuminate\Database\Eloquent\Model;

class Header extends Model {

    const TEMPLATE_HEADER = '[Template Header]';
    const DEFAULT_TEXT = 'Template Header';

    protected $table = 'form_of_tender_headers';

    protected $fillable = [
        'form_of_tender_id',
        'header_text',
    ];

    public function formOfTender()
    {
        return $this->belongsTo('PCK\FormOfTender\FormOfTender');
    }
}