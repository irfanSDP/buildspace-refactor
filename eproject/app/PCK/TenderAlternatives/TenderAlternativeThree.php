<?php namespace PCK\TenderAlternatives;

class TenderAlternativeThree extends Calculation {

    protected $formula = 'A + ( ( D / 100 ) * C )';

    protected $viewName = 'tender_alternatives/three';

}