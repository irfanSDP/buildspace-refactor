<?php

/**
 * RetentionSumCode form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class RetentionSumCodeForm extends BaseRetentionSumCodeForm
{
    public function configure()
    {
        parent::configure();

        unset($this['deleted_at'], $this['created_at'], $this['updated_at']);

        $this->setValidator('code', new sfValidatorString(array(
                'required'=>true,
                'max_length' => 30), array(
                'required'=>'Code is required',
                'max_length'=>'Code is too long (%max_length% character max)')
        ));
        
        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
        );
    }

    public function validateUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('r.id, r.code')->from('RetentionSumCode r');
        $query->where('LOWER(r.code) = ?', strtolower($values['code']));
        $query->andWhere('r.deleted_at IS NULL');

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another retention sum with that code.');

            if($this->object->isNew())
            {
                throw new sfValidatorErrorSchema($validator, array('code' => $sfError));
            }
            else
            {
                $retentionSumCode = $query->fetchOne();
                if($this->object->id != $retentionSumCode->id)
                {
                    throw new sfValidatorErrorSchema($validator, array('code' => $sfError));
                }
            }
        }
        return $values;
    }
}
