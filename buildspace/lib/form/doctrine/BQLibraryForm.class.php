<?php

class BQLibraryForm extends BaseBQLibraryForm
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
        $query = DoctrineQuery::create()->select('l.id, l.name')->from('BQLibrary l');
        $query->where('LOWER(l.name) = ?', strtolower($values['name']));
        $query->andWhere('l.deleted_at IS NULL');

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another library with that name.');

            if($this->object->isNew())
            {
                throw new sfValidatorErrorSchema($validator, array('name' => $sfError));
            }
            else
            {
                $library = $query->fetchOne();
                if($this->object->id != $library->id)
                {
                    throw new sfValidatorErrorSchema($validator, array('name' => $sfError));
                }
            }
        }
        return $values;
    }
}
