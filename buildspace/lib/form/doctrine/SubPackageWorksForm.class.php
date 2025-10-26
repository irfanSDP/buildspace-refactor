<?php

/**
 * SubPackageWorks form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SubPackageWorksForm extends BaseSubPackageWorksForm
{
    public function configure()
    {
        parent::configure();

        $this->setValidators(array(
            'name' => new sfValidatorString(array(
                'required' => true,
                'max_length' => 100), array(
                'required' => 'Name is required',
                'max_length' => 'Name is too long(%max_length% character max)')),
            'type' => new sfValidatorString(
                array( 'required' => true ),
                array( 'required' => 'The type is required' )),
        ));

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
        );
    }

    public function validateUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('w.id, w.name')->from('SubPackageWorks w');
        $query->where('LOWER(w.name) = ?', strtolower($values['name']));
        $query->andWhere('w.type = ?', $values['type']);

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another record with this name.');

            if($this->object->isNew())
            {
                throw new sfValidatorErrorSchema($validator, array('name' => $sfError));
            }
            else
            {
                $work = $query->fetchOne();
                if($this->object->id != $work->id)
                {
                    throw new sfValidatorErrorSchema($validator, array('name' => $sfError));
                }
            }
        }

        return $values;
    }
}
