<?php

/**
 * RFQItemRemark form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class RFQItemRemarkForm extends BaseRFQItemRemarkForm
{
    public function configure()
    {
        parent::configure();

        unset($this['created_at'], $this['updated_at']);

        $this->setValidator('description', new sfValidatorString(array(
            'required'   => true,
            'max_length' => 200
            ), array(
                'required'   => 'Item Remark is required',
                'max_length' => 'Item Remark is too long (%max_length% character max)'
            )
        ));
    }
}
