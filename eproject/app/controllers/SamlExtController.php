<?php

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;      
use Illuminate\Support\Facades\Config;    
use Illuminate\Support\Facades\Response;

// ---- SimpleSAMLphp autoload (local) ----
$path = getenv('SAMLAUTH_PATH') ?: base_path('../../samlauth');

// Build the autoload path and require it
$sspAutoload = rtrim($path, '/\\') . '/lib/_autoload.php';

if (!file_exists($sspAutoload)) {
    throw new RuntimeException("SimpleSAMLphp autoload not found at: {$sspAutoload}");
}

require_once $sspAutoload;
/* for Docker From PCK*/
// $path = Config::get('laravel-saml::saml.sp_path', public_path() . "/sp/www");
// require_once( $path . '/lib/_autoload.php' );

use KnightSwarm\LaravelSaml\Account;
use PCK\Users\User;

class SamlExtController extends BaseController
{
    private $act;

    public function __construct(KnightSwarm\LaravelSaml\Account $act)
    {
        $this->account = $act;
      }

    public function login()
    {
        Checkpoint::reset();

        if ( Input::has('url') )
        {
            // only allow local urls as redirect destinations
            $url = Input::get('url');
            if ( !preg_match("~^(//|[^/]+:)~", $url) )
            {
                Session::flash('url.intended', $url);
            }
        }
        /*
         * We have to logout from laravel to clear laravel user session because both eproject and buildspace is a SP.
         * This is to solve issue when user logged out from buildspace but the laravel session is still valid even though
         * the saml session is already terminated.
         */
        Auth::logout();

        if ( !$this->account->samlLogged() )
        {
            \SimpleSAML_Session::getSessionFromRequest()->cleanup();

            $this->account->samlLogin();
        }

        if ( $this->account->samlLogged() )
        {
            $id = $this->account->getSamlUniqueIdentifier();
            if ( !$this->account->IdExists($id) )
            {
                if ( Config::get('laravel-saml::saml.can_create', true) )
                {
                    $this->account->createUser();
                }
                else
                {
                    return Response::make(Config::get('laravel-saml::saml.can_create_error'), 400);
                }
            }

            $this->account->laravelLogin($id);
        }

        if ( $this->account->samlLogged() && $this->account->laravelLogged() )
        {
            $intended = Session::get('url.intended');
            $intended = str_replace(Config::get('app.url'), '', $intended);
            Session::flash('url.intended', $intended);

            return Redirect::intended('/');
        }
    }
    
    public function maintenanceLogin()
    {
        Checkpoint::reset();

        if ( Input::has('url') )
        {
            // only allow local urls as redirect destinations
            $url = Input::get('url');
            if ( !preg_match("~^(//|[^/]+:)~", $url) )
            {
                Session::flash('url.intended', $url);
            }
        }
        /*
         * We have to logout from laravel to clear laravel user session because both eproject and buildspace is a SP.
         * This is to solve issue when user logged out from buildspace but the laravel session is still valid even though
         * the saml session is already terminated.
         */
        Auth::logout();

        if ( !$this->account->samlLogged() )
        {
            \SimpleSAML_Session::getSessionFromRequest()->cleanup();

            $this->account->samlLogin();
        }

        if ( $this->account->samlLogged() )
        {
            $id = $this->account->getSamlUniqueIdentifier();
            $user = User::find($id);

            if ($user->isSuperAdmin()) 
            {
                if ( !$this->account->IdExists($id) )
                {
                    if ( Config::get('laravel-saml::saml.can_create', true) )
                    {
                        $this->account->createUser();
                    }
                    else
                    {
                        return Response::make(Config::get('laravel-saml::saml.can_create_error'), 400);
                    }
                }
                $this->account->laravelLogin($id);
            }
            else
            {
                $auth_cookie = $this->account->logout();

                \SimpleSAML_Session::getSessionFromRequest()->cleanup();
        
                return Redirect::to(Config::get('laravel-saml::saml.logout_target'))->withCookie($auth_cookie);
            }
        }

        if ( $this->account->samlLogged() && $this->account->laravelLogged() )
        {
            $intended = Session::get('url.intended');
            $intended = str_replace(Config::get('app.url'), '', $intended);
            Session::flash('url.intended', $intended);

            return Redirect::intended('/');
        }
    }

    public function logout()
    {
        $auth_cookie = $this->account->logout();

        \SimpleSAML_Session::getSessionFromRequest()->cleanup();

        return Redirect::to(Config::get('laravel-saml::saml.logout_target'))->withCookie($auth_cookie);
    }

}
