<?php 

use PCK\EmailSettings\EmailSetting;
use PCK\EmailSettings\EmailReminderSetting;
use PCK\Forms\EmailReminderSettingForm;
use PCK\Forms\EmailSettingForm;

use PCK\Helpers\Files;
use Intervention\Image\ImageManagerStatic as Image;

class EmailSettingsController extends Controller
{
    private $form;
    private $emailSettingForm;

    public function __construct(EmailSettingForm $emailSettingForm, EmailReminderSettingForm $form)
    {
        $this->form = $form;
        $this->emailSettingForm = $emailSettingForm;
    }

    public function edit()
    {
        $emailSettings = EmailSetting::first();

        if(!$emailSettings)
        {
            $emailSettings = EmailSetting::createDefault();
        }

        return View::make('email_settings.edit', [
            'emailSettings' => $emailSettings,
        ]);
    }

    public function update()
    {
        $inputs = Input::all();

        $this->emailSettingForm->validate($inputs);

        $emailSettings = EmailSetting::first();

        if(!$emailSettings)
        {
            $emailSettings = new EmailSetting;
        }

        if( Input::hasFile('footer_logo_image') && Files::isImage(Input::file('footer_logo_image')))
        {
            $logoFile = Input::file('footer_logo_image');
            $fullPath = public_path().DIRECTORY_SEPARATOR.EmailSetting::LOGO_FILE_DIRECTORY;

            Files::mkdirIfDoesNotExist($fullPath);

            if( strlen($emailSettings->footer_logo_image) > 0 && file_exists($fullPath.DIRECTORY_SEPARATOR.$emailSettings->footer_logo_image) )
            {
                Files::deleteFile($fullPath.DIRECTORY_SEPARATOR.$emailSettings->footer_logo_image);
            }

            $footerLogoFilename = 'email_footer_logo-'.time().'.'.$logoFile->getClientOriginalExtension();
            
            $logoFile->move($fullPath.DIRECTORY_SEPARATOR, $footerLogoFilename);

            $emailSettings->footer_logo_image = $footerLogoFilename;

            $resizeImage = ((int)$inputs['resize_footer_image']==1);

            $emailSettings->resize_footer_image = $resizeImage;

            if($resizeImage)
            {
                $emailSettings->footer_logo_width   = (int)$inputs['footer_logo_width'];
                $emailSettings->footer_logo_height  = (int)$inputs['footer_logo_height'];

                Image::make($fullPath.DIRECTORY_SEPARATOR.$footerLogoFilename)->resize((int)$inputs['footer_logo_width'], (int)$inputs['footer_logo_height'])->save();
            }
            else
            {
                $emailSettings->footer_logo_width   = 0;
                $emailSettings->footer_logo_height  = 0;
            }
        }
        else
        {
            $emailSettings->resize_footer_image = false;
            $emailSettings->footer_logo_width   = 0;
            $emailSettings->footer_logo_height  = 0;
        }

        $emailSettings->company_logo_alignment_identifier = (int)$inputs['company_logo_alignment_identifier'];
        $emailSettings->save();

        Flash::success(trans('email.emailSettingsSavedSuccessfully'));

        return Redirect::route('email.setttings.edit');
    }

    public function footerLogoDelete()
    {
        $emailSettings = EmailSetting::first();

        if(!$emailSettings or strlen($emailSettings->footer_logo_image) == 0)
        {
            return Redirect::route('email.setttings.edit');
        }

        $fullPath = public_path().DIRECTORY_SEPARATOR.EmailSetting::LOGO_FILE_DIRECTORY;

        if( strlen($emailSettings->footer_logo_image) > 0 && file_exists($fullPath.DIRECTORY_SEPARATOR.$emailSettings->footer_logo_image) )
        {
            Files::deleteFile($fullPath.DIRECTORY_SEPARATOR.$emailSettings->footer_logo_image);
        }

        $emailSettings->footer_logo_image = null;
        $emailSettings->resize_footer_image = false;
        $emailSettings->footer_logo_width   = 0;
        $emailSettings->footer_logo_height  = 0;

        $emailSettings->save();

        return Redirect::route('email.setttings.edit');
    }

    public function emailReminderSettingsUpdate()
    {
        $inputs = Input::all();
        $errors = null;

        try
        {
            $this->form->validate($inputs);

            $emailReminderSetting                                            = EmailReminderSetting::first();
            $emailReminderSetting->tender_reminder_before_closing_date_value = $inputs['tender_reminder_before_closing_date_value'];
            $emailReminderSetting->tender_reminder_before_closing_date_unit  = $inputs['tender_reminder_before_closing_date_unit'];
            $emailReminderSetting->save();

            $emailReminderSetting = EmailReminderSetting::find($emailReminderSetting->id);

            Flash::success(trans('email.emailReminderSettingsSaved'));
        }
        catch(Exception $e)
        {
            $errors = $e->getErrors();
        }

        return Redirect::back()->withErrors($errors)->withInput(Input::all());
    }
}