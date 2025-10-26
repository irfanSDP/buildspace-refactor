<?php

/**
 * PostContractClaimTopManagementVerifier form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class PostContractClaimTopManagementVerifierForm extends BasePostContractClaimTopManagementVerifierForm
{
  public function configure()
  {
    parent::configure();

    unset($this['id']);
    unset($this['object_id']);
    unset($this['object_type']);
    unset($this['sequence']);
    unset($this['created_at']);
    unset($this['updated_at']);
    unset($this['_csrf_token']);
  }

  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    $this->setWidget('_csrf_token', new sfWidgetFormInputText());

    $this->setValidators(array(
        '_csrf_token' => new SfValidatorString(
            array(
                'required' => true,
            )
        )
    ));

    $this->setWidget('id', new sfWidgetFormInputText());
    $this->setValidator('id', new sfValidatorInteger(
      array(
        'required' => true,
      )
    ));

    $this->setWidget('objectId', new sfWidgetFormInputText());
    $this->setValidator('objectId', new sfValidatorInteger(
      array(
        'required' => true,
      )
    ));

    $this->setWidget('user_id', new sfWidgetFormInputText());
    $this->setValidator('user_id', new sfValidatorInteger(
      array(
        'required' => true,
      ),
      array(
          'required' => 'Please select a user from the list.'
      )
    ));
  
    
    parent::bind($taintedValues, $taintedFiles);
  }
}
