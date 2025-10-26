<?php namespace PCK\TenderAlternatives;

use Math\Parser;

class Calculator {

    private $valueA = 0;
    private $valueB = 0;
    private $valueC = 0;
    private $valueD = 0;
    private $valueE = 0;
    private $valueF = 0;
    private $valueG = 0;

    private $inputValueE = 0;
    private $inputValueF = 0;

    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function setValueA($value)
    {
        $this->valueA = $value;
    }

    public function setValueB($value)
    {
        $this->valueB = $value;
    }

    public function setValueC($value)
    {
        $this->valueC = $value;
    }

    public function setValueD($value)
    {
        $this->valueD = $value;
    }

    public function setValueE($value)
    {
        $this->inputValueE = $value;

        $this->valueE = $value;
    }

    public function setValueF($value)
    {
        $this->inputValueF = $value;

        $this->valueF = $value;
    }

    public function setValueG($value)
    {
        $this->valueG = $value;
    }

    public function getTotalAmount(Calculation $calculationObject)
    {
        $this->recalculateAdjustmentValues($calculationObject);

        return $this->getValueFromFormula($calculationObject->getFormula());
    }

    protected function resetAdjustmentValuesInput()
    {
        $this->valueE = $this->inputValueE;
        $this->valueF = $this->inputValueF;
    }

    protected function recalculateAdjustmentValues(Calculation $calculationObject)
    {
        $this->resetAdjustmentValuesInput();

        // If percentage is set.
        if( ! empty( $this->valueE ) && $this->valueE != 0 )
        {
            $this->valueF = $this->getValueFromFormula($calculationObject->getAdjustmentTotalFormula());
        }
        else
        {
            if( ! empty( $this->valueA ) && $this->valueA != 0 )
            {
                $this->valueE = $this->getValueFromFormula($calculationObject->getAdjustmentPercentageFormula());
            }
        }
    }

    public function getAdjustmentPercentage()
    {
        return $this->valueE;
    }

    protected function getValueFromFormula($formula)
    {
        $array = $this->getColumnsValue();

        // will replace formula's value with actual value
        $newFormula = str_replace(array_keys($array), array_values($array), $formula);

        /*
         * For 'Cannot tokenize empty string' error
         * */

        $amount = 0;

        if( ! empty( $newFormula ) )
        {
            $amount = $this->parser->evaluate($newFormula);
        }

        return $amount;
    }

    public function getColumnsValue()
    {
        return array(
            'A' => $this->valueA,
            'B' => $this->valueB,
            'C' => $this->valueC,
            'D' => $this->valueD,
            'E' => $this->valueE,
            'F' => $this->valueF,
            'G' => $this->valueG,
        );
    }

}