<?php 

use Illuminate\Support\Facades\Redirect;
use PCK\ThemeSettings\ThemeSettingRepository;

class ThemeSettingsController extends \BaseController
{
    private $themeSettingRepo;

    public function __construct(ThemeSettingRepository $themeSettingRepo)
    {
        $this->themeSettingRepo = $themeSettingRepo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Redirect::route('theme.settings.edit');
    }

    /**
     * Edit
     */
    public function edit()
    {
        $themeSettings = $this->themeSettingRepo->find();

        return View::make('theme_settings.edit', array('themeSettings' => $themeSettings));
    }

    /**
     * Update
     */
    public function update()
    {
        $rules = array(
            'logo1' => 'image|mimes:png|max:12288', // Max 12MB
            'logo2' => 'image|mimes:png|max:12288', // Max 12MB
            'login_img' => 'image|mimes:png|max:12288', // Max 12MB
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            return Redirect::route('theme.settings.edit')->withErrors($validator);
        }

        $updateLogo1 = false;
        $updateLogo2 = false;
        $updateLogo3 = false;
        if (Input::file('logo1')) {
            $updateLogo1 = $this->themeSettingRepo->processImgUpdate(Input::file('logo1'), 'logo1');
        }
        if (Input::file('logo2')) {
            $updateLogo2 = $this->themeSettingRepo->processImgUpdate(Input::file('logo2'), 'logo2');
        }
        if (Input::file('login_img')) {
            $updateLogo3 = $this->themeSettingRepo->processImgUpdate(Input::file('login_img'), 'bg_image');
        }
        if ($updateLogo1 || $updateLogo2 || $updateLogo3) {
            \Flash::success(trans('forms.updateSuccessful'));
        }

        return Redirect::route('theme.settings.edit');

        /*$color = $request->button();
        if ($color = button('color1')) {
            $color = 'rgb(48, 106, 243)';
            DB::table('theme_settings')
                ->update(['bg_colour' => $color]);
        
        }
        if ($color = button('color2')) {
            $color = 'rgb(50, 233, 117)';
            DB::table('theme_settings')
                ->update(['bg_colour' => $color]);
        }
        if ($color = button('color3')) {
            $color = 'rgb(243, 56, 56)';
            DB::table('theme_settings')
                ->update(['bg_colour' => $color]);
        }
        if ($color = button('color4')) {
            $color = 'rgb(251, 50, 211)';
            DB::table('theme_settings')
                ->update(['bg_colour' => $color]);
        }*/
    }

    public function resetImages()
    {
        $this->themeSettingRepo->resetImages();

        return Response::json(array('success' => true));
    }
}