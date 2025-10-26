<?php namespace PCK\Settings;

use Illuminate\Database\Eloquent\Model;

class Language extends Model {

    /*
     * A full list of ISO 639-1 codes can be found here:
     * https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
     * */

    const LANG_ENGLISH_NAME = 'English';
    const LANG_ENGLISH_CODE = 'en';

    const LANG_INDONESIAN_NAME = 'Bahasa Indonesia';
    const LANG_INDONESIAN_CODE = 'id';

    const LANG_VIETNAMESE_NAME = 'Tiếng Việt';
    const LANG_VIETNAMESE_CODE = 'vi';

    protected $fillable = [ 'name', 'code' ];

    public static function getLanguageListing()
    {
        return array(
            self::LANG_ENGLISH_CODE    => self::LANG_ENGLISH_NAME,
            self::LANG_INDONESIAN_CODE => self::LANG_INDONESIAN_NAME,
            self::LANG_VIETNAMESE_CODE => self::LANG_VIETNAMESE_NAME,
        );
    }
}