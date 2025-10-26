<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class TenderAlternativeBill extends Model {

    protected $connection = 'buildspace';
    protected $table      = 'bs_tender_alternatives_bills';

    public function projectStructure()
    {
        return $this->belongsTo('PCK\Buildspace\Project', 'project_structure_id');
    }

    public function tenderAlternative()
    {
        return $this->belongsTo('PCK\Buildspace\TenderAlternative');
    }
}