<?php

/*
 * This file is part of the sfSAMLplugin package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2010      Théophile Helleboid <t.helleboid@iariss.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSAMLPlugin configuration.
 *
 * @package    sfSAMLPlugin
 * @subpackage sfSAMLAuth
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Théophile Helleboid <t.helleboid@iariss.fr>
 */
class BasesfSAMLAuthActions extends sfActions {

	public function executeSignin(sfWebRequest $request)
	{
		$user = $this->getUser();

		if ( $user->isAuthenticated() )
		{
			return $this->redirect('@dashboard');
		}

		// Create SimpleSAML module
		$simpleSAMLAuth = new SimpleSAML_Auth_Simple('default-sp');

		// If the user is authenticated from the IdP
		if ( $simpleSAMLAuth->isAuthenticated() )
		{
			$attributes = $simpleSAMLAuth->getAttributes();

			$userEmail              = $attributes['email'][0];
			$userName               = $attributes['name'][0];
			$userAccessToBuildSpace = (boolean) $attributes['allowed_access_to_buildspace'][0];

			// check current logging-in user is authorized to use the application
			if ( !$userAccessToBuildSpace )
			{
				die( 'Sorry, you\'re not allowed to use BuildSpace.' );
			}

			// save the referer
			$referrer = $user->getReferer($request->getReferer());

			// Try to find the user with his uid
			$guard_user = Doctrine_Core::getTable('sfGuardUser')->findOneBy('username', $userEmail);

			if ( !$guard_user )
			{
				// the user doesn't exist, we create a new one with random password
				$guard_user = new sfGuardUser();
				$guard_user->setUsername($userEmail);
				$guard_user->setEmailAddress($userEmail);
				$guard_user->setPassword(md5(microtime() . $userEmail . mt_rand()));
				$guard_user->setIsActive(true);
			}

			$guard_user->setIsSuperAdmin((boolean) $attributes['is_super_admin'][0]);
			$guard_user->save();
			$guard_user->refresh();

			// will update profile as well
			$userProfile               = $guard_user->Profile;
			$userProfile->name         = $userName;
			$userProfile->company_name = $attributes['company_name'][0];
			$userProfile->company_role = $attributes['company_role'][0];
			$userProfile->save();

			// Let the User signin
			// The auth is not remembered : the IdP can decide that
			$this->getUser()->signin($guard_user, $remember = false);

			// always redirect to a URL set in app.yml
			// or to the referer
			// or to the homepage
			$signInURL = sfConfig::get('app_sf_guard_plugin_success_signin_url', $referrer);

			return $this->redirect('' != $signInURL ? $signInURL : '@homepage');
		}

		// the user is not authenticated in symfony and from the IdP
		if ( $request->isXmlHttpRequest() )
		{
			$this->getResponse()->setHeaderOnly(true);
			$this->getResponse()->setStatusCode(401);

			return sfView::NONE;
		}

		// if we have been forwarded, then the referer is the current URL
		// if not, this is the referer of the current request
		$user->setReferer($this->getContext()->getActionStack()->getSize() > 1 ? $request->getUri() : $request->getReferer());

		$this->url_idp = $simpleSAMLAuth->login();
	}

	public function executeSignout(sfWebRequest $request)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url' ));

		$this->getUser()->signOut();

		$simpleSAMLAuth = new SimpleSAML_Auth_Simple('default-sp');
		$simpleSAMLAuth->logout(url_for('@homepage', true), array(), true);

		// Nothing happen after there
		$this->redirect('@homepage');
	}

	public function executeSecure(sfWebRequest $request)
	{
		$this->getResponse()->setStatusCode(403);
	}

	public function executePassword(sfWebRequest $request)
	{
		throw new sfException('This method is not yet implemented.');
	}

}