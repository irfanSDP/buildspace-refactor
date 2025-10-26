<?php namespace PCK\FormOfTender;

use Illuminate\Database\Eloquent\Model;
use PCK\Helpers\ModelOperations;

class FormOfTender extends Model
{
    protected $table = 'form_of_tenders';

    const DEFAULT_NAME = 'Default';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $details)
        {
            $details->deleteRelatedModels();
        });
    }

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function address()
    {
        return $this->hasOne('PCK\FormOfTender\Address');
    }

    public function clauses()
    {
        return $this->hasMany('PCK\FormOfTender\Clause')->orderBy('sequence_number', 'asc');
    }

    public function header()
    {
        return $this->hasOne('PCK\FormOfTender\Header');
    }

    public function logs()
    {
        return $this->hasMany('PCK\FormOfTender\Log')->orderBy('created_at', 'DESC');
    }

    public function printSettings()
    {
        return $this->hasOne('PCK\FormOfTender\PrintSettings');
    }

    public function tenderAlternatives()
    {
        return $this->hasMany('PCK\FormOfTender\TenderAlternative');
    }

    public function tenderAlternativePositions()
    {
        return $this->hasMany('PCK\FormOfTender\TenderAlternativesPosition')->orderBy('position', 'ASC');
    }

    protected function deleteRelatedModels()
    {
        $address = $this->address;
        $header  = $this->header;

        if( isset( $address ) ) $address->delete();

        if( isset( $header ) ) $header->delete();

        ModelOperations::deleteWithTrigger(array(
            $this->clauses,
            $this->logs,
            $this->printSettings,
            $this->tenderAlternatives,
            $this->tenderAlternativePositions,
        ));
    }

}