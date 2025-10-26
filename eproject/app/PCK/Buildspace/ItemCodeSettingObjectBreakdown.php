<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Buildspace\Project as ProjectStructure;
use PCK\Buildspace\AccountGroup;

class ItemCodeSettingObjectBreakdown extends Model
{
    protected $connection = 'buildspace';
    protected $table      = 'bs_item_code_setting_object_breakdowns';

    public static function getRecordsBy($claimCertificateId)
    {
        return self::where('claim_certificate_id', $claimCertificateId)->get();
    }
}

