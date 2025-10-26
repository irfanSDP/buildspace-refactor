<?php

class myUser extends sfGuardSecurityUser
{
    public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
    {
        parent::initialize($dispatcher, $storage, $options);

        // If not authenticated, clear any stale namespace like before
        if (!$this->isAuthenticated()) {
            $this->getAttributeHolder()->removeNamespace('sfGuardSecurityUser');
            $this->user = null;
        }

        // === IMPORTANT CHANGE ===
        // Disable SAML enforcement for local login.
        // We do NOT call SimpleSAML_Auth_Simple at all.
        //
        // Old code:
        //
        // if ($this->getGuardUser()) {
        //     $simpleSAMLAuth = new SimpleSAML_Auth_Simple(sfConfig::get('app_saml_sp_name', 'default-sp'));
        //     $attributes = $simpleSAMLAuth->getAttributes();
        //     $eProjectUser = (... query by uid ...)
        //     if (!$eProjectUser || !$simpleSAMLAuth->isAuthenticated() || ...) {
        //         $this->signOut();
        //         SimpleSAML_Session::getSessionFromRequest()->cleanup();
        //     }
        // }
        //
        // We just skip that completely now.
    }
}
