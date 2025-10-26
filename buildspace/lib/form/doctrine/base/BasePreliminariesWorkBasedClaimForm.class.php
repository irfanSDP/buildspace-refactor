<?php

/**
 * PreliminariesWorkBasedClaim form base class.
 *
 * @method PreliminariesWorkBasedClaim getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BasePreliminariesWorkBasedClaimForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                              => new sfWidgetFormInputHidden(),
      'post_contract_bill_item_rate_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ItemRate'), 'add_empty' => false)),
      'builders_work_done'              => new sfWidgetFormInputText(),
      'total_builders_work'             => new sfWidgetFormInputText(),
      'total'                           => new sfWidgetFormInputText(),
      'revision_id'                     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Revision'), 'add_empty' => false)),
      'created_at'                      => new sfWidgetFormDateTime(),
      'updated_at'                      => new sfWidgetFormDateTime(),
      'created_by'                      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                      => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                              => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'post_contract_bill_item_rate_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ItemRate'), 'column' => 'id')),
      'builders_work_done'              => new sfValidatorNumber(array('required' => false)),
      'total_builders_work'             => new sfValidatorNumber(array('required' => false)),
      'total'                           => new sfValidatorNumber(array('required' => false)),
      'revision_id'                     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Revision'), 'column' => 'id')),
      'created_at'                      => new sfValidatorDateTime(),
      'updated_at'                      => new sfValidatorDateTime(),
      'created_by'                      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                      => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'PreliminariesWorkBasedClaim', 'column' => array('post_contract_bill_item_rate_id', 'revision_id', 'deleted_at')))
    );

    $this->widgetSchema->setNameFormat('preliminaries_work_based_claim[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PreliminariesWorkBasedClaim';
  }

}
