<?php

/**
 * sfGuardUserProfile form base class.
 *
 * @method sfGuardUserProfile getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BasesfGuardUserProfileForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'user_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('sfGuardUser'), 'add_empty' => false)),
      'eproject_user_id' => new sfWidgetFormInputText(),
      'name'             => new sfWidgetFormInputText(),
      'contact_num'      => new sfWidgetFormInputText(),
      'profile_photo'    => new sfWidgetFormTextarea(),
      'language'         => new sfWidgetFormInputText(),
      'created_at'       => new sfWidgetFormDateTime(),
      'updated_at'       => new sfWidgetFormDateTime(),
      'created_by'       => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'       => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'       => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'user_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('sfGuardUser'), 'column' => 'id')),
      'eproject_user_id' => new sfValidatorInteger(),
      'name'             => new sfValidatorString(array('max_length' => 255)),
      'contact_num'      => new sfValidatorPass(array('required' => false)),
      'profile_photo'    => new sfValidatorString(array('required' => false)),
      'language'         => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'created_at'       => new sfValidatorDateTime(),
      'updated_at'       => new sfValidatorDateTime(),
      'created_by'       => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'       => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'       => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'sfGuardUserProfile', 'column' => array('user_id', 'eproject_user_id', 'deleted_at')))
    );

    $this->widgetSchema->setNameFormat('sf_guard_user_profile[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfGuardUserProfile';
  }

}
