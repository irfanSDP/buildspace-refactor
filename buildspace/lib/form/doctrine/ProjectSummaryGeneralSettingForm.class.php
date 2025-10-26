<?php

/**
 * ProjectSummaryGeneralSetting form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProjectSummaryGeneralSettingForm extends BaseProjectSummaryGeneralSettingForm
{
    public function configure()
    {
        parent::configure();

        unset($this['created_at'], $this['updated_at']);

        $this->setValidator('summary_title', new sfValidatorString(array(
            'required' => false,
            'max_length' => 50), array(
            'max_length'=>'Summary Title is too long (%max_length% max)')
        ));

        $this->setValidator('additional_description', new sfValidatorString(array(
            'required' => false,
            'max_length' => 260), array(
            'max_length'=>'Additional Description Text is too long (%max_length% max)')
        ));

        $this->setValidator('carried_to_next_page_text', new sfValidatorString(array(
            'required' => false,
            'max_length' => 100), array(
            'max_length'=>'Carried To Next Page Text is too long (%max_length% max)')
        ));

        $this->setValidator('continued_from_previous_page_text', new sfValidatorString(array(
            'required' => false,
            'max_length' => 100), array(
            'max_length'=>'Continued From Previous Page Text is too long (%max_length% max)')
        ));

        $this->setValidator('page_number_prefix', new sfValidatorString(array(
            'required' => false,
            'max_length' => 20), array(
            'max_length'=>'Page Number Prefix is too long (%max_length% max)')
        ));
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null)
    {
        $this->setWidget('tax_name', new sfWidgetFormInputText());
        $this->setWidget('tax_percentage', new sfWidgetFormInputText());

        if(isset($taintedValues['include_tax']))
        {
            $this->setValidator('tax_name', new sfValidatorString(
                array(
                    'required' => true,
                    'min_length' => 1,
                ),
                array(
                    'required'   => 'Tax name is required',
                    'min_length' => 'Tax name must be at least (%min_length%) character or more',
                )
            ));

            $this->setValidator('tax_percentage', new sfValidatorNumber(
                array(
                    'required' => true,
                    'max'      => 100,
                ),
                array(
                    'required'   => 'Tax percentage is required',
                    'max'        => 'Tax percentage must be no more than (%max%) %',
                )
            ));
        }

        parent::bind($taintedValues, $taintedFiles);
    }
}