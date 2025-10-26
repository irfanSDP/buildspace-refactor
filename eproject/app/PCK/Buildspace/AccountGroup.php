<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Buildspace\Project as ProjectStructure;

class AccountGroup extends Model
{
    use SoftDeletingTrait;

    protected $connection = 'buildspace';
    protected $table      = 'bs_account_groups';

    public function accountCodes()
    {
        return $this->hasMany('PCK\Buildspace\AccountCode', 'account_group_id')->orderBy('priority', 'ASC');
    }
}

