<?php

class sfGuardUserProfileForm extends BasesfGuardUserProfileForm
{
    public function configure()
    {
        unset( $this['user_id'], $this['profile_photo'], $this['created_at'], $this['updated_at'], $this['created_by'], $this['updated_by'], $this['deleted_at'] );
    }
}