<?php namespace PCK\Settings;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class Settings extends Model {

    protected $table = 'user_settings';

    protected $fillable = [ 'user_id', 'language_id' ];

    public function user()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }

    public function language()
    {
        return $this->belongsTo('PCK\Settings\Language', 'language_id');
    }

    public static function initialise(User $user)
    {
        if( $user->settings ) return true;

        $defaultLanguage = Language::where('code', '=', ( getenv('DEFAULT_LANGUAGE_CODE') ? getenv('DEFAULT_LANGUAGE_CODE') : Language::LANG_ENGLISH_CODE ))->first();

        $object = self::create(array(
            'user_id'     => $user->id,
            'language_id' => $defaultLanguage->id,
        ));

        if( $object ) $user->settings = $object;

        return $object ? true : false;
    }

}