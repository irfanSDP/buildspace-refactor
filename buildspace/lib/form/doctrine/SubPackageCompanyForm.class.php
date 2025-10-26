<?php

/**
 * SubPackageCompany form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SubPackageCompanyForm extends BaseSubPackageCompanyForm
{
    public function configure()
    {
        unset($this['created_at'], $this['updated_at']);

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
        );

        parent::configure();
    }

    public function validateUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('xref.id, xref.sub_package_id, xref.company_id')->from('SubPackageCompany xref');
        $query->where('xref.sub_package_id = ?', $values['sub_package_id']);
        $query->andWhere('xref.company_id = ?', $values['company_id']);

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another record with sub_package_id: '.$values['sub_package_id'].' and company_id: '.$values['company_id'].'.');

            if($this->object->isNew())
            {
                throw new sfValidatorErrorSchema($validator, array('company_id' => $sfError));
            }
            else
            {
                $subPackageCompany = $query->fetchOne();
                if($this->object->id != $subPackageCompany->id)
                {
                    throw new sfValidatorErrorSchema($validator, array('company_id' => $sfError));
                }
            }
        }
        return $values;
    }
}
