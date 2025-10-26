<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class PostContractLetterOfAward extends Model {

    const TYPE_1      = 1;
    const TYPE_1_TEXT = 'Letter of Award';
    const TYPE_1_CODE = 'LA';
    const TYPE_2      = 2;
    const TYPE_2_TEXT = 'Work Order';
    const TYPE_2_CODE = 'WO';

    protected $connection = 'buildspace';

    protected $table = 'bs_new_post_contract_form_information';

    public function projectStructure()
    {
        return $this->belongsTo('PCK\Buildspace\Project', 'project_structure_id');
    }

    public function retentionSumModules()
    {
        return $this->hasMany('PCK\Buildspace\PostContractLetterOfAwardRententionSumModule', 'new_post_contract_form_information_id');
    }

    public static function getTypeCode($type)
    {
        $types = array(
            self::TYPE_1 => self::TYPE_1_CODE,
            self::TYPE_2 => self::TYPE_2_CODE,
        );

        return $types[ $type ] ?? null;
    }

    public function getCodeAttribute()
    {
        $project = $this->projectStructure->mainInformation->getEProjectProject();

        // Uses the contract number of the Main Project.
        if( $parentProject = $project->parentProject ) $project = $parentProject;

        return $project->reference . '/' . self::getTypeCode($this->type) . str_pad($this->form_number, 3, '0', STR_PAD_LEFT);
    }
}