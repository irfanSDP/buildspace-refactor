<?php

/**
 * Resource form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ResourceForm extends BaseResourceForm
{
    public function configure()
    {
        parent::configure();

        unset($this['deleted_at'], $this['created_at'], $this['updated_at']);

        $this->setValidator('name', new sfValidatorString(array(
                'required'=>true,
                'max_length' => 200), array(
                'required'=>'Name is required',
                'max_length'=>'Name is too long (%max_length% character max)')
        ));

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
        );
    }

    public function validateUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('r.id, r.name')->from('Resource r');
        $query->where('LOWER(r.name) = ?', strtolower($values['name']));
        $query->andWhere('r.deleted_at IS NULL');

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another resource with that name.');

            if($this->object->isNew())
            {
                throw new sfValidatorErrorSchema($validator, array('name' => $sfError));
            }
            else
            {
                $resource = $query->fetchOne();
                if($this->object->id != $resource->id)
                {
                    throw new sfValidatorErrorSchema($validator, array('name' => $sfError));
                }
            }
        }
        return $values;
    }
}
