<?php namespace PCK\CPEGrades;

use Illuminate\Database\Eloquent\Model;
use PCK\Traits\TimeAccessorTrait;

class PreviousCPEGrade extends Model {

    use TimeAccessorTrait;

    const UNSPECIFIED_RECORD_GRADE = 'Unspecified';

    protected $table = 'previous_cpe_grades';

    protected $fillable = [
        'grade'
    ];

    public function contractors()
    {
        return $this->hasMany('PCK\Contractors\Contractor');
    }

}