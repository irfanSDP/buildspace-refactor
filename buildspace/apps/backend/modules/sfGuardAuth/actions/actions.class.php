<?php

    /**
     * sfGuardAuth actions.
     *
     * @package    buildspace
     * @subpackage sfGuardAuth
     * @author     1337 developers
     * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
     */
    require_once(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'sfDoctrineGuardPlugin'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'sfGuardAuth'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'BasesfGuardAuthActions.class.php');

    class sfGuardAuthActions extends BasesfGuardAuthActions
    {
        /**
         * Executes index action
         *
         * @param sfRequest $request A request object
         */
        public function executeIndex(sfWebRequest $request)
        {

        }

        public function executeSignin($request)
        {
            sfConfig::set('sf_web_debug', false);

            $user = $this->getUser();

            if($request->isXmlHttpRequest() and !$user->isAuthenticated())
            {
                $this->getResponse()->setHeaderOnly(true);
                $this->getResponse()->setStatusCode(401);
                return sfView::NONE;
            }
        }

        protected function isFormValid(sfWebRequest $request, sfForm $form)
        {
            if($request->isMethod('post'))
            {
                $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
            }
            return $form->isValid() ? true : false;
        }
    }
