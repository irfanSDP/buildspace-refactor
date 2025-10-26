<?php namespace PCK\TenderAlternatives;

class TenderAlternativeFour extends Calculation {

    protected $formula = '( A + B ) + ( ( D / 100 ) * ( C + B ) )';

    protected $viewName = 'tender_alternatives/four';

}