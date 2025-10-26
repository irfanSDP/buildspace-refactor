<?php

/**
 * BillLayoutSetting form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class BillLayoutSettingForm extends BaseBillLayoutSettingForm
{
    public function configure()
    {
        $this->disableLocalCSRFProtection();

        $this->setValidators(array(
            'font'                       => new sfValidatorString(array('required' => FALSE, 'trim' => TRUE)),
            'rounding_type'              => new sfValidatorString(array('required' => FALSE)),
            'size'                       => new sfValidatorInteger(array('required' => FALSE)),
            'comma_total'                => new sfValidatorBoolean(array('required' => FALSE)),
            'comma_rate'                 => new sfValidatorBoolean(array('required' => FALSE)),
            'comma_qty'                  => new sfValidatorBoolean(array('required' => FALSE)),
            'priceFormat'                => new sfValidatorString(array('required' => FALSE, 'trim' => TRUE)),
            'print_amt_col_only'         => new sfValidatorBoolean(array('required' => FALSE)),
            'print_without_price'        => new sfValidatorBoolean(array('required' => FALSE)),
            'add_psum_pcsum'             => new sfValidatorBoolean(array('required' => FALSE)),
            'print_dollar_cent'          => new sfValidatorBoolean(array('required' => FALSE)),
            'print_without_cent'         => new sfValidatorBoolean(array('required' => FALSE)),
            'print_full_decimal'         => new sfValidatorBoolean(array('required' => FALSE)),
            'switch_qty_unit_rate'       => new sfValidatorBoolean(array('required' => FALSE)),
            'print_element_header'       => new sfValidatorBoolean(array('required' => FALSE)),
            'print_element_grid'         => new sfValidatorBoolean(array('required' => FALSE)),
            'print_element_grid_once'    => new sfValidatorBoolean(array('required' => FALSE)),
            'add_cont'                   => new sfValidatorBoolean(array('required' => FALSE)),
            'indent_item'                => new sfValidatorBoolean(array('required' => FALSE)),
            'includeIAndOForBillRef'     => new sfValidatorBoolean(array('required' => FALSE)),
            'apply_binding_alignment'    => new sfValidatorBoolean(array('required' => FALSE)),
            'contd'                      => new sfValidatorString(array('required' => FALSE, 'trim' => TRUE)),
            'page_numbering_option'      => new sfValidatorString(array('required' => FALSE, 'trim' => TRUE)),
            'page_no_prefix'             => new sfValidatorString(array('required' => FALSE, 'trim' => TRUE)),
            'print_date_of_printing'     => new sfValidatorBoolean(array('required' => FALSE)),
            'print_grand_total_quantity' => new sfValidatorBoolean(array('required' => FALSE)),
            'align_element_to_left'      => new sfValidatorBoolean(array('required' => FALSE)),
            'close_grid'                 => new sfValidatorBoolean(array('required' => FALSE)),
        ));
    }
}