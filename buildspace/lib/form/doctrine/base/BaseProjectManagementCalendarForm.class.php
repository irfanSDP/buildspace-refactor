<?php

/**
 * ProjectManagementCalendar form base class.
 *
 * @method ProjectManagementCalendar getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseProjectManagementCalendarForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'project_structure_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'add_empty' => false)),
      'description'          => new sfWidgetFormTextarea(),
      'event_type'           => new sfWidgetFormInputText(),
      'is_holiday'           => new sfWidgetFormInputCheckbox(),
      'start_date'           => new sfWidgetFormDate(),
      'end_date'             => new sfWidgetFormDate(),
      'created_at'           => new sfWidgetFormDateTime(),
      'updated_at'           => new sfWidgetFormDateTime(),
      'created_by'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'project_structure_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'column' => 'id')),
      'description'          => new sfValidatorString(),
      'event_type'           => new sfValidatorInteger(),
      'is_holiday'           => new sfValidatorBoolean(array('required' => false)),
      'start_date'           => new sfValidatorDate(),
      'end_date'             => new sfValidatorDate(),
      'created_at'           => new sfValidatorDateTime(),
      'updated_at'           => new sfValidatorDateTime(),
      'created_by'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('project_management_calendar[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProjectManagementCalendar';
  }

}
