<?php

/**
 * BillLayoutPhrase form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class BillLayoutPhraseForm extends BaseBillLayoutPhraseForm
{
    public function configure()
    {
        $this->disableLocalCSRFProtection();

        $this->setValidators(array(
            'to_collection'               => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'table_header_description'    => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'table_header_unit'           => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'table_header_qty'            => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'table_header_rate'           => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'table_header_amt'            => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'currency'                    => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'cents'                       => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'collection_in_grid'          => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary'                     => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary_in_grid'             => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'totalPerUnitPrefix'          => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'totalUnitPrefix'             => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'totalPerTypePrefix'          => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary_page_no'             => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary_tender'              => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary_page_one'            => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary_page_two'            => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary_page_three'          => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary_page_four'           => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary_page_five'           => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary_page_six'            => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary_page_seven'          => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary_page_eight'          => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'summary_page_nine'           => new sfValidatorString(array('required' => false, 'trim' => TRUE)),
            'element_header_bold'         => new sfValidatorBoolean(array('required' => false)),
            'element_header_underline'    => new sfValidatorBoolean(array('required' => false)),
            'element_header_italic'       => new sfValidatorBoolean(array('required' => false)),
            'element_footer_bold'         => new sfValidatorBoolean(array('required' => false)),
            'element_footer_underline'    => new sfValidatorBoolean(array('required' => false)),
            'element_footer_italic'       => new sfValidatorBoolean(array('required' => false)),
            'element_note_top_left_row1'  => new sfValidatorString(array('required' => false, 'trim' => TRUE, 'max_length' => 42)),
            'element_note_top_left_row2'  => new sfValidatorString(array('required' => false, 'trim' => TRUE, 'max_length' => 42)),
            'element_note_top_right_row1' => new sfValidatorString(array('required' => false, 'trim' => TRUE, 'max_length' => 42)),
            'element_note_bot_left_row1'  => new sfValidatorString(array('required' => false, 'trim' => TRUE, 'max_length' => 42)),
            'element_note_bot_left_row2'  => new sfValidatorString(array('required' => false, 'trim' => TRUE, 'max_length' => 42)),
            'element_note_bot_right_row1' => new sfValidatorString(array('required' => false)),
            'element_note_bot_right_row2' => new sfValidatorString(array('required' => false)),
        ));
    }
}