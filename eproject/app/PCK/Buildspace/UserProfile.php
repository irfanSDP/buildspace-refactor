<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Users\User as EProjectUser;

class UserProfile extends Model {

    use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_sf_guard_user_profile';

    public function User()
    {
        return $this->hasOne('PCK\Buildspace\User', 'id', 'user_id');
    }

    public function getEProjectUser()
    {
        return EProjectUser::find($this->eproject_user_id);
    }
}