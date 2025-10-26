<?php

/**
 * Company form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class CompanyForm extends BaseCompanyForm
{
    public function configure()
    {
        unset($this['reference_id'],  $this['sub_packages_list'], $this['name'], $this['registration_no'], $this['address'], $this['phone_number'], $this['fax_number'], $this['region_id'], $this['sub_region_id'], $this['deleted_at'], $this['created_at'], $this['updated_at']);

        $this->setValidator('shortname', new sfValidatorString(array(
            'trim' => true,
            'required' => true,
            'max_length' => 20), array(
                'required' => 'Short Name is required',
                'max_length' => 'Short Name is too long (%max_length% max)')
        ));

        $this->setValidator('contact_person_email', new sfValidatorEmail(array(
            'trim' => true,
            'required' => false), array(
                'invalid' => 'Invalid E-mail entered')
        ));

        $this->setValidator('about', new sfValidatorString(array(
            'trim' => true,
            'required' => false)
        ));

        parent::configure();
    }

}