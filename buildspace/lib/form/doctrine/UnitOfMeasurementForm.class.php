<?php

/**
 * UnitOfMeasurement form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class UnitOfMeasurementForm extends BaseUnitOfMeasurementForm
{
    public function configure()
    {
        parent::configure();

        unset($this['display'], $this['dimensions_list'], $this['deleted_at'], $this['created_at'], $this['updated_at']);

        $this->setValidator('name', new sfValidatorString(array(
                'required'=>true,
                'max_length' => 200), array(
                'required'=>'Name is required',
                'max_length'=>'Name is too long (%max_length% character max)')
        ));

        $this->setValidator('symbol', new sfValidatorString(array(
                'required'=>true,
                'max_length' => 10), array(
                'required'=>'Symbol is required',
                'max_length'=>'Symbol is too long (%max_length% character max)')
        ));

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
        );
    }

    public function validateUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('u.id')->from('UnitOfMeasurement u');
        $query->where('LOWER(u.symbol) = ?', strtolower($values['symbol']));
        $query->andWhere('u.type = ?', $values['type']);
		$query->andWhere('u.display = ?', true);
        $query->andWhere('u.deleted_at IS NULL');

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another unit of measurement with that symbol.');

            if($this->object->isNew())
            {
                throw new sfValidatorErrorSchema($validator, array('symbol' => $sfError));
            }
            else
            {
                $uom = $query->fetchOne();
                if($this->object->id != $uom->id)
                {
                    throw new sfValidatorErrorSchema($validator, array('symbol' => $sfError));
                }
            }
        }

        return $values;
    }

    public function doSave($con = null)
    {
        if($this->object->isNew())
        {
            $this->object->setDisplay(true);
        }

        return parent::doSave($con);
    }
}
