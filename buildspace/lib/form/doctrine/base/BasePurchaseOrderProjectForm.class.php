<?php

/**
 * PurchaseOrderProject form base class.
 *
 * @method PurchaseOrderProject getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BasePurchaseOrderProjectForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'purchase_order_id'    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PurchaseOrder'), 'add_empty' => false)),
      'project_structure_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Project'), 'add_empty' => true)),
      'created_at'           => new sfWidgetFormDateTime(),
      'updated_at'           => new sfWidgetFormDateTime(),
      'created_by'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'           => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'purchase_order_id'    => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('PurchaseOrder'), 'column' => 'id')),
      'project_structure_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Project'), 'column' => 'id', 'required' => false)),
      'created_at'           => new sfValidatorDateTime(),
      'updated_at'           => new sfValidatorDateTime(),
      'created_by'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'           => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'PurchaseOrderProject', 'column' => array('purchase_order_id', 'project_structure_id', 'deleted_at')))
    );

    $this->widgetSchema->setNameFormat('purchase_order_project[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PurchaseOrderProject';
  }

}
