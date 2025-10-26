<?php

/**
 * ProjectSummaryDefaultSetting form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProjectSummaryDefaultSettingForm extends BaseProjectSummaryDefaultSettingForm
{
  public function configure()
  {
    parent::configure();

    unset($this['created_at'], $this['updated_at'], $this['created_by'], $this['updated_by']);

    $this->setValidators(array(
            'first_row_text' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length' => 200
                ),
                array(
                    'max_length' => 'First Row Text is too long (%max_length% characters max)'
                )
            ),
            
            'second_row_text' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length' => 200
                ),
                array(
                    'max_length' => 'Second Row Text is too long (%max_length% characters max)'
                )
            ),

            'summary_title' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length' => 50
                ),
                array(
                    'max_length' => 'Summary Title is too long (%max_length% characters max)'
                )
            ),

            'carried_to_next_page_text' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length' => 100
                ),
                array(
                    'max_length' => 'Carried To Next Page Text is too long (%max_length% characters max)'
                )
            ),

            'continued_from_previous_page_text' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length' => 100
                ),
                array(
                    'max_length' => 'Continued From Previous Page is too long (%max_length% characters max)'
                )
            ),

            'page_number_prefix' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length' => 20
                ),
                array(
                    'max_length' => 'Page Number Prefix is too long (%max_length% characters max)'
                )
            ),

            'include_printing_date' => new sfValidatorString(
                array(
                    'required' => false
                )
            ),

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
