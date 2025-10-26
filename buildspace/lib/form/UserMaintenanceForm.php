<?php

class UserMaintenanceForm extends BasesfGuardUserForm {

	public function configure() {
		parent::configure();

		unset(
			$this['first_name'],
			$this['last_name'],
			$this['last_login'],
			$this['created_at'],
			$this['updated_at'],
			$this['salt'],
			$this['algorithm'],
			$this['is_active'],
			$this['password'],
			$this['permissions_list'],
			$this['groups_list'],
			$this['is_active'],
			$this['is_super_admin']
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
		);

		$profileForm = new sfGuardUserProfileForm( $this->object->Profile );
		$this->embedForm('Profile', $profileForm);
	}

	public function validateUniqueness(sfValidatorCallback $validator, array $values)
	{
		$query = DoctrineQuery::create()->select('u.id')->from('sfGuardUser u');
		$query->where('u.email_address = ?', strtolower($values['email_address']));
		$query->andWhere('u.username = ?', $values['username']);
		$query->andWhere('u.deleted_at IS NULL');

		if($query->count() > 0)
		{
			$sfError = new sfValidatorError($validator, 'There is already another user registered with the email address.');

			if ($this->object->isNew())
			{
				throw new sfValidatorErrorSchema($validator, array('symbol' => $sfError));
			}
			else
			{
				$user = $query->fetchOne();

				if($this->object->id != $user->id)
				{
					throw new sfValidatorErrorSchema($validator, array('symbol' => $sfError));
				}
			}
		}

		return $values;
	}

	public function doSave($conn = null)
	{
		if ( $this->getObject()->isNew() )
		{
			$this->getObject()->setIsActive(true);
			$this->getObject()->setPassword(sfConfig::get('app_default_user_password'));
		}

		parent::doSave($conn);
	}
}