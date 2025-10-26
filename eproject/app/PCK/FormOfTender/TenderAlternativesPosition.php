<?php namespace PCK\FormOfTender;

use Illuminate\Database\Eloquent\Model;

class TenderAlternativesPosition extends Model {

    protected $table = 'tender_alternatives_position';

    protected $fillable = [
        'form_of_tender_id',
        'position',
    ];

    public function formOfTender()
    {
        return $this->belongsTo('PCK\FormOfTender\FormOfTender');
    }
}