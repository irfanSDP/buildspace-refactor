<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_sf_guard_user_group';

    public function user()
    {
        return $this->belongsTo('PCK\Buildspace\User', 'user_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo('PCK\Buildspace\Group', 'group_id', 'id');
    }

    public static function removeBsUserFromAllGroups(User $bsUser)
    {
        \DB::connection('buildspace')
            ->table(with(new self)->getTable())
            ->where('user_id', $bsUser->id)
            ->delete();
    }
}