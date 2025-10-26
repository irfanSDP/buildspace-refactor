<?php

/**
 * Dimension form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class DimensionForm extends BaseDimensionForm
{
    public function configure()
    {
        parent::configure();

        unset($this['unit_of_measurements_list'], $this['deleted_at'], $this['created_at'], $this['updated_at']);

        $this->setValidator('name', new sfValidatorString(array(
                'required'=>true,
                'max_length' => 50), array(
                'required'=>'Name is required',
                'max_length'=>'Name is too long (%max_length% character max)')
        ));

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
        );
    }

    public function validateUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('d.id, d.name')->from('Dimension d');
        $query->where('LOWER(d.name) = ?', strtolower($values['name']));
        $query->andWhere('d.deleted_at IS NULL');

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another dimension with that name.');

            if($this->object->isNew())
            {
                throw new sfValidatorErrorSchema($validator, array('name' => $sfError));
            }
            else
            {
                $dimension = $query->fetchOne();
                if($this->object->id != $dimension->id)
                {
                    throw new sfValidatorErrorSchema($validator, array('name' => $sfError));
                }
            }
        }
        return $values;
    }
}
