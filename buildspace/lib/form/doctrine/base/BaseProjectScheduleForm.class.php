<?php

/**
 * ProjectSchedule form base class.
 *
 * @method ProjectSchedule getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseProjectScheduleForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'title'                => new sfWidgetFormInputText(),
      'description'          => new sfWidgetFormTextarea(),
      'type'                 => new sfWidgetFormInputText(),
      'exclude_saturdays'    => new sfWidgetFormInputCheckbox(),
      'exclude_sundays'      => new sfWidgetFormInputCheckbox(),
      'start_date'           => new sfWidgetFormDate(),
      'timezone'             => new sfWidgetFormInputText(),
      'project_structure_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'add_empty' => false)),
      'sub_package_id'       => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SubPackage'), 'add_empty' => true)),
      'zoom'                 => new sfWidgetFormInputText(),
      'created_at'           => new sfWidgetFormDateTime(),
      'updated_at'           => new sfWidgetFormDateTime(),
      'created_by'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'           => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'title'                => new sfValidatorString(array('max_length' => 180)),
      'description'          => new sfValidatorString(array('required' => false)),
      'type'                 => new sfValidatorInteger(),
      'exclude_saturdays'    => new sfValidatorBoolean(array('required' => false)),
      'exclude_sundays'      => new sfValidatorBoolean(array('required' => false)),
      'start_date'           => new sfValidatorDate(),
      'timezone'             => new sfValidatorString(array('max_length' => 50)),
      'project_structure_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'column' => 'id')),
      'sub_package_id'       => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('SubPackage'), 'column' => 'id', 'required' => false)),
      'zoom'                 => new sfValidatorPass(array('required' => false)),
      'created_at'           => new sfValidatorDateTime(),
      'updated_at'           => new sfValidatorDateTime(),
      'created_by'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'           => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('project_schedule[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProjectSchedule';
  }

}
