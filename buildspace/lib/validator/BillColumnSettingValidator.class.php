<?php

class BillColumnSettingValidatorSchema extends sfValidatorSchema
{
    protected $validatorSchema;

    public function __construct(sfValidatorSchema $validatorSchema)
    {
        $this->validatorSchema = $validatorSchema;
        parent::__construct();
    }

    public function doClean($values)
    {
        if(!BillColumnSettingForm::formValuesAreBlank($values))
        {
            $this->validatorSchema['name'] = new sfValidatorString(array(
                    'required'=>true,
                    'max_length'=>200,
                    'trim'=>true
                ),
                array('required'=>'Name is required')
            );

            return $this->validatorSchema->doClean($values);
        }
        return $values;
    }
}

?>
