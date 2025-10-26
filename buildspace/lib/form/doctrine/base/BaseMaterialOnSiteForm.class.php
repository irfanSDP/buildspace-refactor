<?php

/**
 * MaterialOnSite form base class.
 *
 * @method MaterialOnSite getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseMaterialOnSiteForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                    => new sfWidgetFormInputHidden(),
      'project_structure_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Project'), 'add_empty' => true)),
      'description'           => new sfWidgetFormInputText(),
      'status'                => new sfWidgetFormInputText(),
      'reduction_percentage'  => new sfWidgetFormInputText(),
      'total'                 => new sfWidgetFormInputText(),
      'total_after_reduction' => new sfWidgetFormInputText(),
      'priority'              => new sfWidgetFormInputText(),
      'created_at'            => new sfWidgetFormDateTime(),
      'updated_at'            => new sfWidgetFormDateTime(),
      'created_by'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'            => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                    => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'project_structure_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Project'), 'column' => 'id', 'required' => false)),
      'description'           => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'status'                => new sfValidatorInteger(array('required' => false)),
      'reduction_percentage'  => new sfValidatorNumber(array('required' => false)),
      'total'                 => new sfValidatorNumber(array('required' => false)),
      'total_after_reduction' => new sfValidatorNumber(array('required' => false)),
      'priority'              => new sfValidatorInteger(array('required' => false)),
      'created_at'            => new sfValidatorDateTime(),
      'updated_at'            => new sfValidatorDateTime(),
      'created_by'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'            => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('material_on_site[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'MaterialOnSite';
  }

}
