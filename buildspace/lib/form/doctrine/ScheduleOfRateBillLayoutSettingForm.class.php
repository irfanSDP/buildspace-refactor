<?php

/**
 * ScheduleOfRateBillLayoutSetting form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ScheduleOfRateBillLayoutSettingForm extends BaseScheduleOfRateBillLayoutSettingForm
{
    public function configure()
    {
        $this->disableLocalCSRFProtection();

        $this->setValidators(array(
            'font'                    => new sfValidatorString(array( 'required' => false, 'trim' => true )),
            'size'                    => new sfValidatorInteger(array( 'required' => false )),
            'comma_total'             => new sfValidatorBoolean(array( 'required' => false )),
            'comma_rate'              => new sfValidatorBoolean(array( 'required' => false )),
            'priceFormat'             => new sfValidatorString(array( 'required' => false, 'trim' => true )),
            'print_element_grid'      => new sfValidatorBoolean(array( 'required' => false )),
            'print_element_grid_once' => new sfValidatorBoolean(array( 'required' => false )),
            'add_cont'                => new sfValidatorBoolean(array( 'required' => false )),
            'includeIAndOForBillRef'  => new sfValidatorBoolean(array( 'required' => false )),
            'apply_binding_alignment' => new sfValidatorBoolean(array( 'required' => false )),
            'contd'                   => new sfValidatorString(array( 'required' => false, 'trim' => true )),
            'page_no_prefix'          => new sfValidatorString(array( 'required' => false, 'trim' => true )),
            'align_element_to_left'   => new sfValidatorBoolean(array( 'required' => false )),
        ));
    }
}
