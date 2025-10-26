<?php

/**
 * CostData form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class CostDataForm extends BaseCostDataForm
{
    public function configure()
    {
        parent::configure();

        $this->setWidget('awarded_date',new sfWidgetFormDateTime());
        $this->setWidget('approved_date',new sfWidgetFormDateTime());
        $this->setWidget('adjusted_date',new sfWidgetFormDateTime());

        $this->setValidators(array(
            'approved_date'            => new sfValidatorDate(
                array(
                    'required' => false,
                )
            ),
            'awarded_date'            => new sfValidatorDate(
                array(
                    'required' => false,
                )
            ),
            'adjusted_date'            => new sfValidatorDate(
                array(
                    'required' => false,
                )
            ),
        ));
    }
}
