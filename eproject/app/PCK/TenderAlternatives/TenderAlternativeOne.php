<?php namespace PCK\TenderAlternatives;

class TenderAlternativeOne extends Calculation {

    protected $formula = 'A + ( A * ( G / 100 ) )';

    protected $viewName = 'tender_alternatives/one';

}