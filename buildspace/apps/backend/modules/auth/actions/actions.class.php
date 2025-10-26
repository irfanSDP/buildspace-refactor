<?php

class authActions extends sfActions
{
    // GET /auth/login
    public function executeLogin(sfWebRequest $request)
    {
        // already logged in? just go home
        if ($this->getUser()->isAuthenticated()) {
            return $this->redirect('@homepage');
        }

        // IMPORTANT: render this view without the global JS app layout
        $this->setLayout(false);
    }

    // POST /auth/do-login
    public function executeDoLogin(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST));

        $username = trim($request->getParameter('username'));
        $password = $request->getParameter('password');

        $user = Doctrine_Core::getTable('sfGuardUser')
            ->findOneByUsername($username);

        if (
            !$user ||
            !$user->checkPassword($password) ||
            !$user->getIsActive()
        ) {
            $this->getUser()->setFlash('error', 'Invalid username/password');

            // re-render login page with no layout
            $this->setLayout(false);
            $this->setTemplate('login');
            return sfView::SUCCESS;
        }

        // successful login
        $this->getUser()->signIn($user, false);

        return $this->redirect('@homepage');
    }


    // GET /auth/logout
    public function executeLogout(sfWebRequest $request)
    {
        $this->getUser()->signOut();
        return $this->redirect('auth_login');
    }
}
