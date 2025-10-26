<?php

/**
 * VoFooterDefaultSetting form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class VoFooterDefaultSettingForm extends BaseVoFooterDefaultSettingForm
{
  public function configure()
  {
    parent::configure();

    unset($this['created_at'], $this['updated_at'], $this['created_by'], $this['updated_by']);

    $this->setValidators(array(
            'left_text' => new sfValidatorString(
                array(
                    'required' => false
                )
            ),

            'right_text' => new sfValidatorString(
                array(
                    'required' => false
                )
            )
        ));
  }
}
