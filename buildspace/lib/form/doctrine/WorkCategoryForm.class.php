<?php

/**
 * WorkCategory form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class WorkCategoryForm extends BaseWorkCategoryForm
{
    public function configure()
    {
        parent::configure();

        unset($this['updated_by'],$this['created_by'],$this['deleted_at'], $this['created_at'],$this['updated_at']);

        $this->setValidators(array(
            'name' => new sfValidatorString(array(
                'required' => true,
                'max_length' => 100), array(
                'required' => 'Name is required',
                'max_length' => 'Name is too long(%max_length% character max)')),
            'description' => new sfValidatorPass()
        ));

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
        );
    }

    public function validateUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('w.id, w.name')->from('WorkCategory w');
        $query->where('LOWER(w.name) = ?', strtolower(trim($values['name'])));
        $query->andWhere('w.deleted_at IS NULL');

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another work category with this name.');

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

    public function doSave($con=null)
    {
        $nameToSearch = $this->object->isNew() ? $this->getValue('name') : $this->object->name;

        parent::doSave($con);

        $eProjectWorkCategory = EProjectWorkCategoryTable::getWorkCategoryByName($nameToSearch);

        if(!$eProjectWorkCategory)
        {
            $eProjectWorkCategory = new EProjectWorkCategory();

            $eProjectWorkCategory->created_at = date("Y-m-d H:i:s");

            $eProjectWorkCategory->identifier = self::getUniqueIdentifier($nameToSearch);
        }

        $eProjectWorkCategory->updated_at = date("Y-m-d H:i:s");

        $eProjectWorkCategory->name = $this->object->name;

        $eProjectWorkCategory->save();
    }

    private function getUniqueIdentifier($name)
    {
        // Firstly try to generate identifier from the name.
        $identifier = strtoupper(substr(preg_replace("/[^A-Za-z]/", '', $name), 0, 10));

        // If failed to generate identifier, use random string instead.
        $count = 0;
        // The number of possible permutations from the random string.
        // (letters [upper + lower ]) ^(length of string)
        // 52 ^10
        $maxIterations = pow(52, 10);
        while( ! EProjectWorkCategoryTable::identifierIsUnique($identifier) )
        {
            $identifier = '';
            //generate random string
            for($i = 0; $i < 10; $i++)
            {
                if( rand(0, 1) == 0 )
                {
                    //uppercase
                    $letter = chr(rand(64, 90));
                }
                else
                {
                    //lowercase
                    $letter = chr(rand(97, 122));
                }
                $identifier .= $letter;
            }

            $count++;
            if( $count >= $maxIterations )
            {
                throw new Exception('Cannot auto-generate identifier');
            }
        }

        return $identifier;
    }
}
