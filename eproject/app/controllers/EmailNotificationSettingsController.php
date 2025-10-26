<?php 

use PCK\Helpers\DBTransaction;
use PCK\EmailNotificationSettings\EmailNotificationSetting;
use PCK\EmailNotificationSettings\EmailNotificationSettingRepository;
use PCK\MyCompanyProfiles\MyCompanyProfile;

class EmailNotificationSettingsController extends Controller
{
    private $emailNotificationSettingRepository;

    public function __construct(EmailNotificationSettingRepository $emailNotificationSettingRepository)
    {
        $this->emailNotificationSettingRepository = $emailNotificationSettingRepository;
    }

    public function index()
    {
        $companyLogoPath  = MyCompanyProfile::getLogoPath();

        return View::make('module_parameters.email_notification_settings.index', [
            'companyLogoPath' => $companyLogoPath,
        ]);
    }

    public function getExternalUsersEmailNotificationSettings()
    {
        $settings = $this->emailNotificationSettingRepository->getExternalUserEmailNotificationSettings();

        return Response::json($settings);
    }

    public function getInternalUsersEmailNotificationSettings()
    {
        $settings = $this->emailNotificationSettingRepository->getInternalUserEmailNotificationSettings();

        return Response::json($settings);
    }

    public function updateActivationStatus($settingId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $setting = EmailNotificationSetting::find($settingId);
            $row     = $this->emailNotificationSettingRepository->updateActivationStatus($setting);

            $row['route_update_status'] = route('email.notification.setting.activation.status.update', [$row->id]);

            if(in_array($row->setting_identifier, EmailNotificationSetting::getEmailNotificationSettingsIdentifiersForExternalUsers()))
            {
                $row['route_get_content']    = route('email.notification.setting.modifiable.contents.get', [$row->id]);
                $row['route_update_content'] = route('email.notification.setting.modifiable.contents.update', [$row->id]);
            }

            unset($row['setting_identifier'], $row['modifiable_contents'], $row['created_at'], $row['updated_at']);

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
            'row'     => $row,
        ]);
    }

    public function getModifiableContents($settingId)
    {
        $setting  = EmailNotificationSetting::find($settingId);
        $contents = is_null($setting->modifiable_contents) ? '' : $setting->modifiable_contents;

        return Response::json($contents);
    }

    public function updateModifiableContents($settingId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $setting = EmailNotificationSetting::find($settingId);
            $this->emailNotificationSettingRepository->updateModifiableContents($setting, $inputs);

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function getEmailContentsPreview($settingId)
    {
        $setting  = EmailNotificationSetting::find($settingId);
        $contents = $setting->getPreviewContents();

        return Response::json([
            'contents' => $contents,
        ]);
    }
}