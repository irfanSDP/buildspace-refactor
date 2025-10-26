<?php

/**
 * CompanyBranch form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class CompanyBranchForm extends BaseCompanyBranchForm
{
	public function configure()
	{
		parent::configure();

		$this->setValidators(array(
			'name'                       => new sfValidatorString(),
			'contact_person_name'        => new sfValidatorString(),
			'contact_person_direct_line' => new sfValidatorString(array('required' => false)),
			'contact_person_email'       => new sfValidatorEmail(array('required' => false), array('invalid' => 'Invalid E-Mail entered.')),
			'contact_person_mobile'      => new sfValidatorString(array('required' => false)),
			'address'                    => new sfValidatorString(array(), array('required' => 'The Branch Address field is required.')),
			'phone_number'               => new sfValidatorString(array('required' => false)),
			'fax_number'                 => new sfValidatorString(array('required' => false)),
			'postcode'                   => new sfValidatorString(array('required' => false)),
			'region_id'                  => new sfValidatorString(),
			'sub_region_id'              => new sfValidatorString(),
		));
	}
}