<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use GuzzleHttp\Client;

use PCK\Buildspace\Project;

class TenderAlternative extends Model {

    use SoftDeletingTrait;

    protected $connection = 'buildspace';
    protected $table      = 'bs_tender_alternatives';

    public function project()
    {
        return $this->belongsTo('PCK\Buildspace\Project', 'project_structure_id');
    }

    public function tenderAlternativeBills()
    {
        return $this->hasMany('PCK\Buildspace\TenderAlternativeBill', 'tender_alternative_id');
    }
}