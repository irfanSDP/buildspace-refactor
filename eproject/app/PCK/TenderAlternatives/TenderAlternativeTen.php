<?php namespace PCK\TenderAlternatives;

class TenderAlternativeTen extends Calculation {

    protected $formula = 'A + ( A * ( G / 100 ) )';

    protected $viewName = 'tender_alternatives/ten';

}