<?php namespace PCK\IndonesiaCivilContract\EarlyWarning;

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class EarlyWarning extends Model {

    use TimestampFormatterTrait, ModuleAttachmentTrait;

    protected $fillable = array(
        'project_id',
        'user_id',
        'reference',
        'impact',
        'commencement_date',
    );

    protected $table = 'indonesia_civil_contract_early_warnings';

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function extensionsOfTime()
    {
        return $this->belongsToMany('PCK\IndonesiaCivilContract\ExtensionOfTime\ExtensionOfTime', 'indonesia_civil_contract_ew_eot', 'indonesia_civil_contract_ew_id', 'indonesia_civil_contract_eot_id')
            ->orderBy('id', 'desc');
    }

    public function lossAndExpenses()
    {
        return $this->belongsToMany('PCK\IndonesiaCivilContract\LossAndExpense\LossAndExpense', 'indonesia_civil_contract_ew_le', 'indonesia_civil_contract_ew_id', 'indonesia_civil_contract_le_id')
            ->orderBy('id', 'desc');
    }

}