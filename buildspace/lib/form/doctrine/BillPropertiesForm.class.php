<?php

class BillPropertiesForm extends BaseFormDoctrine
{
    public function setup()
    {
        $this->setWidgets(array(
            'title' => new sfWidgetFormInputText()
        ));

        $this->setValidators(array(
            'title' => new sfValidatorString(array('max_length' => 200))
        ));

        $this->widgetSchema->setNameFormat('bill_properties[%s]');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

        $this->setupInheritance();

        parent::setup();
    }

    public function getModelName()
    {
        return 'ProjectStructure';
    }
}