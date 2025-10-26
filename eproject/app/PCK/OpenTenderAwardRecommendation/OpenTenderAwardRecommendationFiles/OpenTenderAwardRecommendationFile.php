<?php namespace PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationFiles;

use Illuminate\Database\Eloquent\Model;

class OpenTenderAwardRecommendationFile extends Model {
    protected $table = 'open_tender_award_recommendation_files';

    public function fileProperties()
    {
        return $this->hasOne('PCK\Base\Upload', 'id', 'cabinet_file_id');
    }
}

