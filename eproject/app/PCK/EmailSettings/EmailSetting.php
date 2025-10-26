<?php namespace PCK\EmailSettings;

use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    protected $table = 'email_settings';

    const LOGO_FILE_DIRECTORY = 'upload'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'logo';

    const COMPANY_LOGO_ALIGNMENT_LEFT_IDENTIFIER  = 1;
    const COMPANY_LOGO_ALIGNMENT_RIGHT_IDENTIFIER = 2;

    const COMPANY_LOGO_ALIGNMENT_LEFT_VALUE  = 'left';
    const COMPANY_LOGO_ALIGNMENT_RIGHT_VALUE = 'right';

    public static function getCompanyLogoAlignmentValue($identifier)
    {
        $mapping = [
            self::COMPANY_LOGO_ALIGNMENT_LEFT_IDENTIFIER  => self::COMPANY_LOGO_ALIGNMENT_LEFT_VALUE,
            self::COMPANY_LOGO_ALIGNMENT_RIGHT_IDENTIFIER => self::COMPANY_LOGO_ALIGNMENT_RIGHT_VALUE,
        ];

        return $mapping[$identifier];
    }

    public static function getCompanyLogoAlignmentDropdownSelection()
    {
        return [
            self::COMPANY_LOGO_ALIGNMENT_LEFT_IDENTIFIER  => ucfirst(self::COMPANY_LOGO_ALIGNMENT_LEFT_VALUE),
            self::COMPANY_LOGO_ALIGNMENT_RIGHT_IDENTIFIER => ucfirst(self::COMPANY_LOGO_ALIGNMENT_RIGHT_VALUE),
        ];
    }

    public static function createDefault()
    {
        $emailSettings = EmailSetting::first();

        if(!$emailSettings)
        {
            $emailSettings = new EmailSetting;

            $emailSettings->company_logo_alignment_identifier = self::COMPANY_LOGO_ALIGNMENT_LEFT_IDENTIFIER;
            $emailSettings->save();
        }

        return $emailSettings;
    }
}