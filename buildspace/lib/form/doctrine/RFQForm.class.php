<?php

/**
 * RFQ form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class RFQForm extends BaseRFQForm
{
    public function configure()
    {
        parent::configure();

        unset($this['created_at'], $this['updated_at']);

        $this->validatorSchema->setPostValidator(
            new sfValidatorDoctrineUnique(array('model' => 'RFQ', 'column' => array('prefix', 'rfq_count', 'type')), array('invalid' => 'Sorry, currently entered RFQ No Prefix has been used.'))
        );
    }
}