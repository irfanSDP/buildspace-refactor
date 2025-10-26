<?php namespace PCK\DigitalStar\Evaluation;

use Illuminate\Database\Eloquent\Model;

class DsCompanyScore extends Model {

    protected $table = 'ds_company_scores';

    protected $fillable = [
        'company_id',
        'score',
    ];

    public function company() {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public static function updateScore($companyId, $score) {
        $record = self::firstOrCreate(['company_id' => $companyId]);
        $record->score = $score;
        $record->save();

        return $record->id;
    }
}