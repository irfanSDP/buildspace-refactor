<?php

use PCK\Companies\Company;
use PCK\Settings\Language;
use PCK\Users\User;
use PCK\Users\UserRepository;
use PCK\Companies\CompanyRepository;
use PCK\Forms\EmailResetPasswordForm;
use PCK\MyCompanyProfiles\MyCompanyProfileRepository;
use PCK\ThemeSettings\ThemeSettingRepository;

/**
 * AuthController Class
 *
 * Implements actions regarding user management
 */
class AuthController extends BaseController {

    private $compRepo;

    private $userRepo;

    private $myCompanyProfileRepo;
    private $themeSettingRepo;

    private $emailResetPasswordForm;

    public function __construct(
        CompanyRepository $compRepo,
        UserRepository $userRepo,
        MyCompanyProfileRepository $myCompanyProfileRepo,
        ThemeSettingRepository $themeSettingRepo,
        EmailResetPasswordForm $emailResetPasswordForm
    ) {
        $this->compRepo               = $compRepo;
        $this->userRepo               = $userRepo;
        $this->myCompanyProfileRepo   = $myCompanyProfileRepo;
        $this->themeSettingRepo       = $themeSettingRepo;
        $this->emailResetPasswordForm = $emailResetPasswordForm;
    }

    /**
     * Display the view of all current user listing
     *
     * @param $companyId
     *
     * @return string
     */
    public function index($companyId)
    {
        $company = $this->compRepo->findUsersWithCompanyId($companyId);

        return View::make('users.index', array(
            'company'              => $company,
            'importedUsers'        => $company->importedUsers,
            'currentUser'          => Confide::user(),
            'selectUserDataSource' => route('companies.importableUsers', array( $company->id )),
            'importUsersUrl'       => route('companies.importUsers', array( $company->id )),
        ));
    }

    /**
     * Displays the form for account creation
     *
     * @param $companyId
     *
     * @return  Illuminate\Http\Response
     */
    public function create($companyId)
    {
        $currentUser = Confide::user();
        $company     = $this->compRepo->find($companyId);

        return View::make(Config::get('confide::signup_form'), array(
            'pageTitle'   => trans('users.createNewUser'),
            'currentUser' => $currentUser,
            'company'     => $company,
            'backRoute'   => route('companies.users', array($company->id)),
        ));
    }

    /**
     * Stores new account
     *
     * @param $companyId
     *
     * @return  Illuminate\Http\Response
     */
    public function store($companyId)
    {
        $company = $this->compRepo->find($companyId);

        $input                    = Input::all();
        $input['relation_column'] = 'company_id';
        $input['company_id']      = $company->id;

        $user = $this->userRepo->signUp($input);

        if( ! $user->exists or (isset($user->errors) && $user->errors->count()))
        {
            Flash::error('Form Validation Error');

            return Redirect::back()->withInput(Input::except('password'))->with('errors', $user->errors);
        }

        $user = User::find($user->id);

        \Event::fire('user.newlyRegistered', $user);

        \Flash::success(trans('confide::confide.alerts.account_created'));

        return Redirect::route('companies.users', array( $company->id ));
    }

    /**
     * Query User by Id
     *
     * @param $companyId
     * @param $userId
     *
     * @return \Illuminate\View\View
     */
    public function show($companyId, $userId)
    {
        $currentUser = Confide::user();
        $company     = $this->compRepo->find($companyId);
        $user        = $this->userRepo->findByCompanyAndId($company, $userId);

        return View::make('users.show', array(
            'pageTitle'   => 'Update User',
            'currentUser' => $currentUser,
            'company'     => $company,
            'user'        => $user,
            'type'        => 'update',
            'backRoute'   => route('companies.users', array($company->id)),
        ));
    }

    public function update($companyId, $userId)
    {
        $input       = Input::all();
        $currentUser = Confide::user();
        $company     = $this->compRepo->find($companyId);
        $user        = $this->userRepo->findByCompanyAndId($company, $userId);
        $user        = $this->userRepo->update($user, $input);

        if( isset( $user->errors ) )
        {
            Flash::error('Form Validation Error');

            return Redirect::back()->withInput()->with('errors', $user->errors);
        }

        Flash::success(trans('users.userUpdated'));

        if( ( ! $currentUser->isGroupAdmin() ) && ( ! $currentUser->isSuperAdmin() ) )
        {
            // Redirect to to projects page if the current user no longer has access to the module.
            return Redirect::route('projects.index');
        }

        return Redirect::route('companies.users', array( $company->id ));
    }

    public function showMyProfile()
    {
        $user = Confide::user();

        return View::make('users.profile_update', compact('user'));
    }

    public function updateMyProfile()
    {
        $user   = Confide::user();
        $inputs = Input::all();
        $form   = App::make('PCK\Forms\UpdateMyProfileForm');

        $form->validate($inputs);

        $this->userRepo->updateMyProfile($user, $inputs);

        Flash::success(trans('users.updatedYourDetails'));

        return Redirect::back();
    }

    /**
     * Displays the login form
     *
     * @return  Illuminate\Http\Response
     */
    public function login()
    {
        if( Confide::user() )
        {
            return Redirect::route('home.index');
        }

        $myCompanyProfile = $this->myCompanyProfileRepo->find();

        return View::make(Config::get('confide::login_form'), compact('myCompanyProfile'));
    }

    /**
     * Attempt to do login
     *
     * @return  Illuminate\Http\Response
     */
    public function doLogin()
    {
        $input = Input::all();

        if( $this->userRepo->login($input) )
        {
            $user = Confide::user();

            if( $user->account_blocked_status )
            {
                Confide::logout();

                $err_msg = trans('users.userAccountBlockedMessage');
            }
        }
        else
        {
            $err_msg = trans('confide::confide.alerts.wrong_credentials');

            if( $this->userRepo->isThrottled($input) )
            {
                $err_msg = trans('confide::confide.alerts.too_many_attempts');
            }
            elseif( $this->userRepo->existsButNotConfirmed($input) )
            {
                $err_msg = trans('confide::confide.alerts.not_confirmed');
            }
        }

        if( isset( $err_msg ) )
        {
            return Redirect::route('users.login')
                ->withInput(Input::except('password'))
                ->with('error', $err_msg);
        }

        return Redirect::intended(route('home.index'));
    }

    /**
     * Attempt to confirm account with code
     *
     * @param  string $code
     *
     * @return  Illuminate\Http\Response
     */
    public function confirm($code)
    {
        if( Confide::confirm($code) )
        {
            // will send a newly created password to the user after confirmation
            $this->userRepo->sendNewlyGeneratedPasswordEmail($code);

            $notice_msg = trans('confide::confide.alerts.confirmation');

            return Redirect::route('users.login')
                ->with('notice', $notice_msg);
        }
        else
        {
            $error_msg = trans('confide::confide.alerts.wrong_confirmation');

            return Redirect::route('users.login')
                ->with('error', $error_msg);
        }
    }

    /**
     * Displays the forgot password form
     */
    public function forgotPassword()
    {
        $myCompanyProfile = $this->myCompanyProfileRepo->find();

        $themeSettings = $this->themeSettingRepo->getImg();

        return View::make(Config::get('confide::forgot_password_form'), compact('myCompanyProfile', 'themeSettings'));
    }

    /**
     * Attempt to send change password link to the given email
     *
     * @return  Illuminate\Http\Response
     */
    public function doForgotPassword()
    {
        \Log::info('Requesting new login credentials: ' . Input::get('email'));

        if( Confide::forgotPassword(Input::get('email')) )
        {
            \Log::info('Sent new login credentials: ' . Input::get('email'));

            $notice_msg = trans('confide::confide.alerts.password_forgot');

            return Redirect::route('users.forgotPassword')
                ->with('notice', $notice_msg);
        }
        else
        {
            \Log::info('Failed sending new login credentials: ' . Input::get('email'));

            $error_msg = trans('confide::confide.alerts.wrong_password_forgot');

            return Redirect::action('AuthController@doForgotPassword')
                ->withInput()
                ->with('error', $error_msg);
        }
    }

    /**
     * Shows the change password form with the given token
     *
     * @param  string $token
     */
    public function resetPassword($token)
    {
        $user             = \Confide::userByResetPasswordToken($token);
        $myCompanyProfile = $this->myCompanyProfileRepo->find();

        $themeSettings = $this->themeSettingRepo->getImg();

        if( ! $user )
        {
            // return token expired page
            return View::make('auth.token_expired', compact('myCompanyProfile', 'themeSettings'));
        }

        return View::make(Config::get('confide::reset_password_form'), compact('token', 'myCompanyProfile', 'themeSettings'));
    }

    /**
     * Attempt change password of the user
     *
     * @return  Illuminate\Http\Response
     */
    public function doResetPassword()
    {
        $inputs = Input::all();

        $this->emailResetPasswordForm->validate($inputs);

        // By passing an array with the token, password and confirmation
        if( $this->userRepo->resetPassword($inputs) )
        {
            $notice_msg = trans('confide::confide.alerts.password_reset');

            return Redirect::route('users.login')
                ->with('notice', $notice_msg);
        }
        else
        {
            $error_msg = trans('confide::confide.alerts.wrong_password_reset');

            return Redirect::back()
                ->withInput()
                ->with('token', $inputs['token'])
                ->with('error', $error_msg);
        }
    }

    public function resendValidationEmail($companyId, $userId)
    {
        $company = $this->compRepo->find($companyId);
        $user    = $this->userRepo->findByCompanyAndId($company, $userId);

        if( $user->confirmed )
        {
            \Flash::error(trans('users.userAlreadyConfirmed') . " {$user->email}");
        }
        else
        {
            \Event::fire('user.newlyRegistered', $user);

            \Flash::success(trans('users.reSentValidationEmail') . " ({$user->email})");
        }

        return Redirect::route('companies.users', array( $company->id ));
    }

    public function destroy($companyId, $userId)
    {
        $company = $this->compRepo->find($companyId);
        $user    = $this->userRepo->findByCompanyAndId($company, $userId);

        if( $user->confirmed )
        {
            \Flash::error(trans('users.userCannotBeDeleted') . " ({$user->email})");

            return Redirect::back();
        }

        try
        {
            $user->delete();

            \Flash::success(trans('users.userDeleted') . " ({$user->email})");
        }
        catch(Exception $e)
        {
            Flash::error(trans('users.userCannotBeDeleted') . " ({$user->email})");
        }

        return Redirect::route('companies.users', array( $company->id ));
    }

    /**
     * Log the user out of the application.
     *
     * @return  Illuminate\Http\Response
     */
    public function logout()
    {
        Confide::logout();

        return Redirect::route('users.login');
    }

    public function importUsers($companyId)
    {
        $success = $this->compRepo->importUsers(Company::find($companyId), Input::get('users'));

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function deport($companyId, $userId)
    {
        $user = User::find($userId);
        $this->compRepo->deportUser(Company::find($companyId), $user);

        Flash::success(trans('users.deportUserSuccess'));

        return Redirect::back();
    }

    public function getImportableUsers($companyId)
    {
        $company = Company::find($companyId);

        $users = $this->userRepo->getImportableUsers(Input::all(), $company);

        return Response::json($users);
    }

    public function editSettings()
    {
        $user = Confide::user();

        $languages = Language::all()->lists('name', 'id');

        return View::make('users.settings', compact('user', 'languages'));
    }

    public function updateSettings()
    {
        $user  = Confide::user();
        $input = Input::all();

        $user->settings->language_id = $input['language_id'];
        $user->settings->save();

        Flash::success(trans('users.updatedSettings'));

        return Redirect::back();
    }

    public function passwordUpdateForm()
    {
        $user = Confide::user();

        if( empty( $user->password_updated_at ) )
        {
            Flash::warning(trans('users.setNewPassword'));
        }
        elseif( ( \Carbon\Carbon::now()->diffInDays($user->password_updated_at) ) > intval(getenv('PASSWORD_VALID_DURATION')) )
        {
            Flash::warning(trans('users.passwordExpired') . ' ' . trans('users.setNewPassword'));
        }

        return View::make('users.password_update', compact('user'));
    }

    public function passwordUpdate()
    {
        $user   = Confide::user();
        $inputs = Input::all();
        $form   = App::make('PCK\Forms\PasswordUpdateForm');

        $form->validate($inputs);

        $this->userRepo->updatePassword($user, $inputs);

        Flash::success(trans('users.updatedYourDetails'));

        return Redirect::back();
    }

}
