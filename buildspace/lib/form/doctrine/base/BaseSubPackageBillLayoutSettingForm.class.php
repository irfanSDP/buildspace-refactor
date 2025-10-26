<?php

/**
 * SubPackageBillLayoutSetting form base class.
 *
 * @method SubPackageBillLayoutSetting getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseSubPackageBillLayoutSettingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                         => new sfWidgetFormInputHidden(),
      'sub_package_id'             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SubPackage'), 'add_empty' => true)),
      'font'                       => new sfWidgetFormTextarea(),
      'rounding_type'              => new sfWidgetFormInputText(),
      'size'                       => new sfWidgetFormInputText(),
      'comma_total'                => new sfWidgetFormInputCheckbox(),
      'comma_rate'                 => new sfWidgetFormInputCheckbox(),
      'comma_qty'                  => new sfWidgetFormInputCheckbox(),
      'priceFormat'                => new sfWidgetFormChoice(array('choices' => array('normal' => 'normal', 'opposite' => 'opposite'))),
      'print_amt_col_only'         => new sfWidgetFormInputCheckbox(),
      'print_without_price'        => new sfWidgetFormInputCheckbox(),
      'print_full_decimal'         => new sfWidgetFormInputCheckbox(),
      'add_psum_pcsum'             => new sfWidgetFormInputCheckbox(),
      'print_dollar_cent'          => new sfWidgetFormInputCheckbox(),
      'print_without_cent'         => new sfWidgetFormInputCheckbox(),
      'switch_qty_unit_rate'       => new sfWidgetFormInputCheckbox(),
      'indent_item'                => new sfWidgetFormInputCheckbox(),
      'includeIAndOForBillRef'     => new sfWidgetFormInputCheckbox(),
      'apply_binding_alignment'    => new sfWidgetFormInputCheckbox(),
      'add_cont'                   => new sfWidgetFormInputCheckbox(),
      'contd'                      => new sfWidgetFormInputText(),
      'print_element_header'       => new sfWidgetFormInputCheckbox(),
      'print_element_grid'         => new sfWidgetFormInputCheckbox(),
      'print_element_grid_once'    => new sfWidgetFormInputCheckbox(),
      'page_numbering_option'      => new sfWidgetFormInputText(),
      'page_no_prefix'             => new sfWidgetFormInputText(),
      'print_date_of_printing'     => new sfWidgetFormInputCheckbox(),
      'print_grand_total_quantity' => new sfWidgetFormInputCheckbox(),
      'align_element_to_left'      => new sfWidgetFormInputCheckbox(),
      'close_grid'                 => new sfWidgetFormInputCheckbox(),
      'created_at'                 => new sfWidgetFormDateTime(),
      'updated_at'                 => new sfWidgetFormDateTime(),
      'created_by'                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                 => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'sub_package_id'             => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('SubPackage'), 'column' => 'id', 'required' => false)),
      'font'                       => new sfValidatorString(array('required' => false)),
      'rounding_type'              => new sfValidatorInteger(array('required' => false)),
      'size'                       => new sfValidatorInteger(array('required' => false)),
      'comma_total'                => new sfValidatorBoolean(array('required' => false)),
      'comma_rate'                 => new sfValidatorBoolean(array('required' => false)),
      'comma_qty'                  => new sfValidatorBoolean(array('required' => false)),
      'priceFormat'                => new sfValidatorChoice(array('choices' => array(0 => 'normal', 1 => 'opposite'), 'required' => false)),
      'print_amt_col_only'         => new sfValidatorBoolean(array('required' => false)),
      'print_without_price'        => new sfValidatorBoolean(array('required' => false)),
      'print_full_decimal'         => new sfValidatorBoolean(array('required' => false)),
      'add_psum_pcsum'             => new sfValidatorBoolean(array('required' => false)),
      'print_dollar_cent'          => new sfValidatorBoolean(array('required' => false)),
      'print_without_cent'         => new sfValidatorBoolean(array('required' => false)),
      'switch_qty_unit_rate'       => new sfValidatorBoolean(array('required' => false)),
      'indent_item'                => new sfValidatorBoolean(array('required' => false)),
      'includeIAndOForBillRef'     => new sfValidatorBoolean(array('required' => false)),
      'apply_binding_alignment'    => new sfValidatorBoolean(array('required' => false)),
      'add_cont'                   => new sfValidatorBoolean(array('required' => false)),
      'contd'                      => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'print_element_header'       => new sfValidatorBoolean(array('required' => false)),
      'print_element_grid'         => new sfValidatorBoolean(array('required' => false)),
      'print_element_grid_once'    => new sfValidatorBoolean(array('required' => false)),
      'page_numbering_option'      => new sfValidatorPass(array('required' => false)),
      'page_no_prefix'             => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'print_date_of_printing'     => new sfValidatorBoolean(array('required' => false)),
      'print_grand_total_quantity' => new sfValidatorBoolean(array('required' => false)),
      'align_element_to_left'      => new sfValidatorBoolean(array('required' => false)),
      'close_grid'                 => new sfValidatorBoolean(array('required' => false)),
      'created_at'                 => new sfValidatorDateTime(),
      'updated_at'                 => new sfValidatorDateTime(),
      'created_by'                 => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                 => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                 => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sub_package_bill_layout_setting[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SubPackageBillLayoutSetting';
  }

}
