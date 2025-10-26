<?php

/**
 * PurchaseOrder form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PurchaseOrderForm extends BasePurchaseOrderForm
{
    public function configure()
    {
        parent::configure();

        unset($this['created_at'], $this['updated_at']);

        $this->validatorSchema->setPostValidator(
            new sfValidatorDoctrineUnique(array('model' => 'PurchaseOrder', 'column' => array('prefix', 'po_count')), array('invalid' => 'Sorry, currently entered PO No Prefix has been used.'))
        );
    }
}