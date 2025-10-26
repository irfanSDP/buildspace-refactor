<?php

/**
 * ProjectRevision form base class.
 *
 * @method ProjectRevision getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseProjectRevisionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                        => new sfWidgetFormInputHidden(),
      'project_structure_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'add_empty' => true)),
      'revision'                  => new sfWidgetFormInputText(),
      'version'                   => new sfWidgetFormInputText(),
      'current_selected_revision' => new sfWidgetFormInputCheckbox(),
      'locked_status'             => new sfWidgetFormInputCheckbox(),
      'tender_origin_id'          => new sfWidgetFormTextarea(),
      'created_at'                => new sfWidgetFormDateTime(),
      'updated_at'                => new sfWidgetFormDateTime(),
      'deleted_at'                => new sfWidgetFormDateTime(),
      'created_by'                => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'                        => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'project_structure_id'      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'column' => 'id', 'required' => false)),
      'revision'                  => new sfValidatorPass(array('required' => false)),
      'version'                   => new sfValidatorInteger(array('required' => false)),
      'current_selected_revision' => new sfValidatorBoolean(array('required' => false)),
      'locked_status'             => new sfValidatorBoolean(array('required' => false)),
      'tender_origin_id'          => new sfValidatorString(array('required' => false)),
      'created_at'                => new sfValidatorDateTime(),
      'updated_at'                => new sfValidatorDateTime(),
      'deleted_at'                => new sfValidatorDateTime(array('required' => false)),
      'created_by'                => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'ProjectRevision', 'column' => array('project_structure_id', 'version', 'current_selected_revision')))
    );

    $this->widgetSchema->setNameFormat('project_revision[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProjectRevision';
  }

}
