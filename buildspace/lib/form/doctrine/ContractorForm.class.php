<?php

/**
 * Company form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ContractorForm extends BaseCompanyForm
{
    public function configure()
    {
        unset($this['company_business_type_id'], $this['contact_person_email'], $this['contact_person_direct_line'], $this['contact_person_mobile'], $this['fax_number'], $this['registration_no'], $this['created_at'], $this['updated_at']);

        $this->setValidator('name', new sfValidatorString(array(
                'required' => true,
                'max_length' => 250), array(
                'required'=>'Company name is required',
                'max_length'=>'Company name is too long (%max_length% character max)')
        ));

        $this->setValidator('postcode', new sfValidatorString(array(
                'required' => false,
                'max_length' => 20), array(
                'max_length'=>'Postcode is too long (%max_length% character max)')
        ));

        $this->setValidator('phone_number', new sfValidatorString(array(
                'required' => false,
                'max_length' => 20), array(
                'max_length'=>'Phone No. is too long (%max_length% character max)')
        ));

        $this->setValidator('contact_person_name', new sfValidatorString(array(
                'required' => false,
                'max_length' => 150), array(
                'max_length'=>'Name is too long (%max_length% character max)')
        ));

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
        );

        parent::configure();
    }

    public function validateUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('c.id, c.name')->from('Company c');
        $query->where('LOWER(c.name) = ?', strtolower($values['name']));
        $query->andWhere('c.deleted_at IS NULL');

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another record with that name.');

            if($this->object->isNew())
            {
                throw new sfValidatorErrorSchema($validator, array('name' => $sfError));
            }
            else
            {
                $company = $query->fetchOne();
                if($this->object->id != $company->id)
                {
                    throw new sfValidatorErrorSchema($validator, array('name' => $sfError));
                }
            }
        }
        return $values;
    }
}