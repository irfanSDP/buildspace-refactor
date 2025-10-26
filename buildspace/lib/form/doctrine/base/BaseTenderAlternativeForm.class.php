<?php

/**
 * TenderAlternative form base class.
 *
 * @method TenderAlternative getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseTenderAlternativeForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                             => new sfWidgetFormInputHidden(),
      'title'                          => new sfWidgetFormTextarea(),
      'description'                    => new sfWidgetFormTextarea(),
      'project_structure_id'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'add_empty' => false)),
      'tender_origin_id'               => new sfWidgetFormTextarea(),
      'project_revision_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectRevision'), 'add_empty' => true)),
      'deleted_at_project_revision_id' => new sfWidgetFormInputText(),
      'project_revision_deleted_at'    => new sfWidgetFormDateTime(),
      'is_awarded'                     => new sfWidgetFormInputCheckbox(),
      'created_at'                     => new sfWidgetFormDateTime(),
      'updated_at'                     => new sfWidgetFormDateTime(),
      'created_by'                     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                     => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'title'                          => new sfValidatorString(),
      'description'                    => new sfValidatorString(array('required' => false)),
      'project_structure_id'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'column' => 'id')),
      'tender_origin_id'               => new sfValidatorString(array('required' => false)),
      'project_revision_id'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectRevision'), 'column' => 'id', 'required' => false)),
      'deleted_at_project_revision_id' => new sfValidatorInteger(array('required' => false)),
      'project_revision_deleted_at'    => new sfValidatorDateTime(array('required' => false)),
      'is_awarded'                     => new sfValidatorBoolean(array('required' => false)),
      'created_at'                     => new sfValidatorDateTime(),
      'updated_at'                     => new sfValidatorDateTime(),
      'created_by'                     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                     => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('tender_alternative[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TenderAlternative';
  }

}
