<?php namespace PCK\TenderAlternatives;

abstract class Calculation {

    protected $viewName;
    protected $formula;
    protected $adjustmentTotalFormula      = '';
    protected $adjustmentPercentageFormula = '';

    public function getFormula()
    {
        return $this->formula;
    }

    public function getAdjustmentTotalFormula()
    {
        return $this->adjustmentTotalFormula;
    }

    public function getAdjustmentPercentageFormula()
    {
        return $this->adjustmentPercentageFormula;
    }

    public function getViewName()
    {
        return $this->viewName;
    }

}