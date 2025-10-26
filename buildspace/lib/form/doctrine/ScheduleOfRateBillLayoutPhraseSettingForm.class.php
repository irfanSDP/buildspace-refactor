<?php

/**
 * ScheduleOfRateBillLayoutPhraseSetting form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ScheduleOfRateBillLayoutPhraseSettingForm extends BaseScheduleOfRateBillLayoutPhraseSettingForm
{
    public function configure()
    {
        $this->disableLocalCSRFProtection();

        $this->setValidators(array(
            'to_collection'               => new sfValidatorString(array( 'required' => false, 'trim' => true )),
            'currency'                    => new sfValidatorString(array( 'required' => false, 'trim' => true )),
            'collection_in_grid'          => new sfValidatorString(array( 'required' => false, 'trim' => true )),
            'element_header_bold'         => new sfValidatorBoolean(array( 'required' => false )),
            'element_header_underline'    => new sfValidatorBoolean(array( 'required' => false )),
            'element_header_italic'       => new sfValidatorBoolean(array( 'required' => false )),
            'element_note_top_left_row1'  => new sfValidatorString(array(
                    'required'   => false,
                    'trim'       => true,
                    'max_length' => 42
                )),
            'element_note_top_left_row2'  => new sfValidatorString(array(
                    'required'   => false,
                    'trim'       => true,
                    'max_length' => 42
                )),
            'element_note_top_right_row1' => new sfValidatorString(array(
                    'required'   => false,
                    'trim'       => true,
                    'max_length' => 42
                )),
        ));
    }
}
