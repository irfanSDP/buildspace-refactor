<?php namespace PCK\RequestForVariation;

use Illuminate\Database\Eloquent\Model;

class RequestForVariationFile extends Model {

    protected $table = 'request_for_variation_files';

    public function fileProperties()
    {
        return $this->hasOne('PCK\Base\Upload', 'id', 'cabinet_file_id');
    }
}

