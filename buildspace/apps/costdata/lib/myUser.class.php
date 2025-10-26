<?php

class myUser extends sfGuardSecurityUser {

    /**
     * Initializes the sfGuardSecurityUser object.
     *
     * @param sfEventDispatcher $dispatcher The event dispatcher object
     * @param sfStorage         $storage    The session storage object
     * @param array             $options    An array of options
     * @return bool|void
     */
    public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
    {
        parent::initialize($dispatcher, $storage, $options);

        if ( !$this->isAuthenticated() )
        {
            // remove user if timeout
            $this->getAttributeHolder()->removeNamespace('sfGuardSecurityUser');
            $this->user = null;
        }

        // if current user is logged in then the system will check whether it is still logged in for SimpleSAML
        if ( $this->getGuardUser() )
        {
            $simpleSAMLAuth = new SimpleSAML_Auth_Simple(sfConfig::get('app_saml_sp_name', 'default-sp'));

            $attributes = $simpleSAMLAuth->getAttributes();
            
            $eProjectUser = (array_key_exists('uid', $attributes) && !empty($attributes['uid'][0])) ? EProjectUserTable::getInstance()->findOneBy('id', $attributes['uid'][0]) : null;
            // if not then we will force the user to logout from the system
            if (!$eProjectUser or !$simpleSAMLAuth->isAuthenticated() or $eProjectUser->email != $this->getGuardUser()->username)
            {
                $this->signOut();
                SimpleSAML_Session::getSessionFromRequest()->cleanup();
            }
        }
    }

}
