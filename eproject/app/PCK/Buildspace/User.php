<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class User extends Model {

    use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_sf_guard_user';

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $user)
        {
            //sfGuardUser hashing style
            $salt = md5(rand(100000, 999999) . $user->username);
            $user->salt = $salt;
            $user->algorithm = 'sha1';
            $user->password = sha1($salt . $user->username);
        });
    }

    public function Profile()
    {
        return $this->hasOne('PCK\Buildspace\UserProfile', 'user_id');
    }

    public function Company()
    {
        return $this->hasOne('PCK\Companies\Company');
    }

    /**
     * Returns true if there exists a (not deleted) user in BuildSpace.
     *
     * @param $email
     *
     * @return bool
     */
    public static function exists($email)
    {
        return ( self::where('email_address', 'ilike', $email)->where('username', 'ilike', $email)->first() ? true : false );
    }
}