<?php

/**
 * RFQ form base class.
 *
 * @method RFQ getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseRFQForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'project_structure_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Project'), 'add_empty' => true)),
      'prefix'               => new sfWidgetFormTextarea(),
      'rfq_count'            => new sfWidgetFormInputText(),
      'type'                 => new sfWidgetFormInputText(),
      'region_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Region'), 'add_empty' => true)),
      'sub_region_id'        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SubRegion'), 'add_empty' => true)),
      'status'               => new sfWidgetFormInputText(),
      'created_at'           => new sfWidgetFormDateTime(),
      'updated_at'           => new sfWidgetFormDateTime(),
      'created_by'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'           => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'project_structure_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Project'), 'column' => 'id', 'required' => false)),
      'prefix'               => new sfValidatorString(array('required' => false)),
      'rfq_count'            => new sfValidatorInteger(array('required' => false)),
      'type'                 => new sfValidatorInteger(),
      'region_id'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Region'), 'column' => 'id', 'required' => false)),
      'sub_region_id'        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('SubRegion'), 'column' => 'id', 'required' => false)),
      'status'               => new sfValidatorInteger(array('required' => false)),
      'created_at'           => new sfValidatorDateTime(),
      'updated_at'           => new sfValidatorDateTime(),
      'created_by'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'           => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'RFQ', 'column' => array('project_structure_id', 'prefix', 'rfq_count', 'type')))
    );

    $this->widgetSchema->setNameFormat('rfq[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'RFQ';
  }

}
