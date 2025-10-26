<?php namespace PCK\FormOfTender;

use Illuminate\Database\Eloquent\Model;
use PCK\TenderAlternatives\TenderAlternativeOne;

class TenderAlternative extends Model {

    protected $table = 'form_of_tender_tender_alternatives';

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function isFirst()
    {
        return ( $this->tender_alternative_class_name == get_class(new TenderAlternativeOne) );
    }

    public static function getTenderAlternativeLabel($tenderAlternativeNumber)
    {
        $tenderAlternativeLabels = [
            trans('tenders.baseTender'),
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
        ];

        if( ! array_key_exists($tenderAlternativeNumber - 1, $tenderAlternativeLabels) ) throw new \Exception('Tender Alternative Label for the number does not exist');

        if( $tenderAlternativeNumber == 1 ) return $tenderAlternativeLabels[ $tenderAlternativeNumber - 1 ];

        return trans('tenders.tenderAlternativeX', array( 'number' => $tenderAlternativeLabels[ $tenderAlternativeNumber - 1 ] ));
    }

}