<?php namespace PCK\SystemEvents;

use PCK\Helpers\Mailer;
use PCK\EmailNotificationSettings\EmailNotificationSetting;
use PCK\MyCompanyProfiles\MyCompanyProfile;

class UserEvents {

    public function sendNewlyRegisteredEmail($user)
    {
        if( ! \Config::get('confide::signup_email') )
        {
            return;
        }

        $companyLogoPath = public_path().MyCompanyProfile::getLogoPath();

        if(!file_exists($companyLogoPath)) $companyLogoPath = null;

        $subject = trans('confide::confide.email.account_confirmation.subject');

        Mailer::queue(
            \Config::get('confide::email_queue'),
            \Config::get('confide::email_account_confirmation'),
            $user,
            $subject,
            array(
                'subject'           => $subject,
                'name'              => $user->name,
                'confirmationCode'  => $user->confirmation_code,
                'companyLogoPath'   => $companyLogoPath,
            ),
            true
        );
    }

    public function sendNewlyRegisteredVendorEmail($user)
    {
        if( ! \Config::get('confide::signup_email') )
        {
            return;
        }

        $accountConfirmationEmailSettings = EmailNotificationSetting::where('setting_identifier', '=', EmailNotificationSetting::NOTIFICATION_TO_UNREGISTEDRED_VENDOR_ON_LOGIN_ACCOUNT_CREATION)->first();

        if( ! $accountConfirmationEmailSettings->activated ) return;

        $additionalContent = $accountConfirmationEmailSettings->modifiable_contents;

        $companyLogoPath = public_path().MyCompanyProfile::getLogoPath();

        if(!file_exists($companyLogoPath)) $companyLogoPath = null;

        $subject = getenv('VENDOR_REGISTRATION_EMAIL_SUBJECT') ? getenv('VENDOR_REGISTRATION_EMAIL_SUBJECT') : trans('confide::confide.email.account_confirmation.subject');

        Mailer::queue(
            \Config::get('confide::email_queue'),
            \Config::get('confide::email_account_confirmation'),
            $user,
            $subject,
            array(
                'subject'           => $subject,
                'name'              => $user->name,
                'confirmationCode'  => $user->confirmation_code,
                'additionalContent' => $additionalContent,
                'companyLogoPath'   => $companyLogoPath,
            ),
            true
        );
    }
}