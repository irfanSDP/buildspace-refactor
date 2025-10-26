<?php namespace PCK\TenderAlternatives;

class TenderAlternativeEight extends Calculation {

    protected $formula = '( A + B ) + ( F ) + ( D / 100 ) * ( ( C + B ) + F )';

    protected $adjustmentTotalFormula = '( A + B ) * ( E / 100 )';

    protected $adjustmentPercentageFormula = '( F / ( A + B ) ) * 100';

    protected $viewName = 'tender_alternatives/eight';

}