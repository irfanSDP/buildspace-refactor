<?php namespace PCK\ThemeSettings;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use PCK\Helpers\Files;

class ThemeSettingRepository {

    const LOGO_DIR = 'upload/themes/theme-1/logo';
    const SETTINGS_DIR = 'config/themes';
    const SETTINGS_LOGO_FILE_1 = 'logo1-filepath.txt';
    const SETTINGS_LOGO_URL_1 = 'logo1-url.txt';
    const SETTINGS_LOGO_FILE_2 = 'logo2-filepath.txt';
    const SETTINGS_LOGO_URL_2 = 'logo2-url.txt';
    const LOGIN_IMG_DIR = 'upload/themes/theme-1/login_img';
    const SETTINGS_LOGIN_IMG_FILE = 'login_img-filepath.txt';
    const SETTINGS_LOGIN_IMG_URL = 'login_img-url.txt';


    public function find()
    {
        $record = ThemeSetting::where('active', true)->first();
        if (! $record) { // No active theme settings found -> Create new record with default settings
            $record = ThemeSetting::create(array(
                'active' => true
            ));
        }

        return $record;
    }

    public function getImg($option=2) {
        $record = $this->find();
        $record->img_path = '';
        $record->img_title = trans('themeSettings.logo');

        switch ($option) {
            case 2:
                if (file_exists(public_path('img/company-logo.png'))) { // Old uploaded logo
                    $record->img_path = asset('img/company-logo.png').'?v='.time();
                } else {
                    if (! empty($record->logo2)) {   // Themes logo
                        $imgDir = '/upload/themes/theme-1/logo';

                        if (file_exists(public_path($imgDir.'/'.$record->logo2))) {
                            $record->img_path = asset($imgDir.'/'.$record->logo2).'?v='.time();
                        }
                    }

                    if (empty($record->img_path)) { // No logo set -> Use default logo
                        $record->img_path = asset('img/buildspace-login-logo.png').'?v='.time();
                    }
                }
                break;

            case 3:
                if (file_exists(public_path('img/login_img.png'))) { // Old uploaded logo
                    $record->img_path = asset('img/login_img.png').'?v='.time();
                } else {
                    if (! empty($record->bg_image)) {
                        $imgDir = '/upload/themes/theme-1/login_img';

                        if (file_exists(public_path($imgDir.'/'.$record->bg_image))) {
                            $record->img_path = asset($imgDir.'/'.$record->bg_image).'?v='.time();
                        }
                    }

                    if (empty($record->img_path)) { // No logo set -> Use default logo
                        $record->img_path = base_path('../samlauth/www/resources/buildspacetheme1/images/login_img.png').'?v='.time();
                    }
                }
                break;

            default:
                if (! empty($record->logo1)) {   // Themes logo
                    $imgDir = '/upload/themes/theme-1/logo';

                    if (file_exists(public_path($imgDir.'/'.$record->logo1))) {
                        $record->img_path = asset($imgDir.'/'.$record->logo1);
                    }
                }
                if (empty($record->img_path)) { // No logo set -> Use default logo
                    $record->img_path = base_path('../samlauth/www/resources/buildspacetheme1/images/company-logo.png').'?v='.time();
                }
        }
        return $record;
    }

    public function update($update_data)
    {
        $record = $this->find();

        if (isset($update_data['logo1'])) {
            $record->logo1 = $update_data['logo1'];
        }
        if (isset($update_data['logo2'])) {
            $record->logo2 = $update_data['logo2'];
        }
        if (isset($update_data['theme_colour1'])) {
            $record->theme_colour1 = $update_data['theme_colour1'];
        }
        if (isset($update_data['theme_colour2'])) {
            $record->theme_colour2 = $update_data['theme_colour2'];
        }
        if (isset($update_data['bg_image'])) {
            $record->bg_image = $update_data['bg_image'];
        }

        return $record->save();
    }

    public function resetImages()
    {
        $record = $this->find();
        $save = false;

        if (! empty($record->logo1)) {
            $imgDir = public_path(self::LOGO_DIR);
            $existingImgFile = $imgDir . '/' . $record->logo1;
            if (file_exists($existingImgFile)) {
                Files::deleteFile($existingImgFile);
            }
            Files::copy(base_path('../samlauth/www/resources/buildspacetheme1/images/company-logo.png'), $existingImgFile);

            $record->logo1 = null;
            $save = true;
        }
        if (! empty($record->logo2)) {
            $imgDir = public_path(self::LOGO_DIR);
            $existingImgFile = $imgDir . '/' . $record->logo2;
            if (file_exists($existingImgFile)) {
                Files::deleteFile($existingImgFile);
            }
            Files::copy(public_path('img/buildspace-login-logo.png'), $existingImgFile);

            $record->logo2 = null;
            $save = true;
        }
        if (! empty($record->bg_image)) {
            $imgDir = public_path(self::LOGIN_IMG_DIR);
            $existingImgFile = $imgDir . '/' . $record->bg_image;
            if (file_exists($existingImgFile)) {
                Files::deleteFile($existingImgFile);
            }
            Files::copy(base_path('../samlauth/www/resources/buildspacetheme1/images/login_img.png'), $existingImgFile);

            $record->bg_image = null;
            $save = true;
        }

        if ($save) {
            return $record->save();
        }
    }

    /**
     * Updates the image
     *
     * @param $uploadedFile
     * @param $imgFieldName
     *
     * @return bool
     * @throws \Exception
     */
    public function processImgUpdate($uploadedFile, $imgFieldName)
    {
        if (! Files::isImage($uploadedFile))
        {
            return false;
        }

        $themeSettings = $this->find();

        // Create a DateTime object representing the current moment.
        //$now = new \DateTime();

        // Format the current date and time as a compact string.
        // The format 'ymdHis' breaks down as follows:
        // 'y' - The year in two digits (e.g., '23' for 2023)
        // 'm' - The month in two digits (e.g., '03' for March)
        // 'd' - The day in two digits (e.g., '26' for the 26th)
        // 'H' - The hour in two digits, 24-hour format (e.g., '18' for 6 PM)
        // 'i' - The minute in two digits (e.g., '45' for 45 minutes past the hour)
        // 's' - The second in two digits (e.g., '00' for the top of the minute)
        // This pattern generates a unique identifier based on the precise moment it's executed, resulting in a string like '230326184500'.
        //$uniqueId = $now->format('ymdHis');

        // Get the original extension
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        switch ($imgFieldName) {
            case 'logo2':
                $imgDir = public_path(self::LOGO_DIR);  // File path
                $imageName = 'logo2' . /*'-' . $uniqueId .*/ '.' . $extension;  // New logo filename with the unique identifier
                break;
            case 'bg_image':
                $imgDir = public_path(self::LOGIN_IMG_DIR); // File path
                $imageName = 'login_img' . /*'-' . $uniqueId .*/ '.' . $extension;  // New logo filename with the unique identifier
                break;
            default:
                $imgDir = public_path(self::LOGO_DIR);  // File path
                $imageName = 'logo1' . /*'-' . $uniqueId .*/ '.' . $extension;  // New logo filename with the unique identifier
        }

        // Get existing file name (if saved)
        $existingImgFileName = $themeSettings->{"$imgFieldName"};

        if (! empty($existingImgFileName)) {
            $existingImgFile = $imgDir . '/' . $existingImgFileName;

            // Delete old file if exist
            if (file_exists($existingImgFile)) {
                Files::deleteFile($existingImgFile);
            }
        }

        // Move uploaded file
        Files::mkdirIfDoesNotExist($imgDir);   // Create the folder if it does not exist
        $uploadedFile->move($imgDir, $imageName);

        // Update the database record and return the result
        $this->update(array($imgFieldName => $imageName));

        return true;
    }

}