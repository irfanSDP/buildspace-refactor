<?php namespace PCK\Helpers;

class NumberToTextConverter extends \Numbers_Words {

    CONST DEFAULT_CURRENCY_SUB_UNIT_NAME = 'cent';

    public $MAX_DOLLAR_LENGTH = 14;

    protected $currencySubUnitName;

    public static function spellCurrencyAmount($number, $currencyName)
    {
        $converter = new static();

        return $converter->customisedToCurrency($number, $currencyName);
    }

    public function customisedToCurrency($number, $currencyName)
    {
        $currencyName = trim($currencyName);

        $this->currencySubUnitName = $this->getCurrencySubUnitName($currencyName);

        // Todo: Source? Workaround?
        // only accurate up to 14 digits(characteristic) and 2 decimal points(mantissa)
        $currency = explode(".", number_format($number, 2, '.', ''));

        $output = '';

        $currencyDollars = $currency[0];

        if( strlen($currencyDollars) > $this->MAX_DOLLAR_LENGTH )
        {
            // too many digits to be accurate
            \Log::warning('Too many digits for number to text converter to be accurate');

            return $output;
        }

        if( isset( $currency[1] ) )
        {
            $currencyCents = $currency[1];
        }

        if( isset( $currencyDollars ) && ( $currencyDollars != 0 ) )
        {
            $output = ucfirst($this->toWords($currencyDollars) . ' ' . $currencyName . $this->addSIfRequired($currencyName));
        }

        if( isset( $currencyCents ) && ( $currencyCents != 0 ) )
        {
            $output .= ' ' . $this->toWords($currencyCents) . ' ' . $this->currencySubUnitName . $this->addSIfRequired($this->currencySubUnitName);
        }

        return $output;
    }

    /**
     * Converts a number to its roman presentation.
     **/ 
    public static function numberToRoman($num, $toLowerCase=true)
    { 
        // Be sure to convert the given parameter into an integer
        $n = intval($num);
        $result = ''; 
    
        // Declare a lookup array that we will use to traverse the number: 
        $lookup = array(
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 
            'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 
            'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
        ); 
    
        foreach ($lookup as $roman => $value)  
        {
            // Look for number of matches
            $matches = intval($n / $value); 
    
            // Concatenate characters
            $result .= str_repeat($roman, $matches); 
    
            // Substract that from the number 
            $n = $n % $value; 
        } 

        return ($toLowerCase) ? strtolower($result) : $result; 
    }

    protected function addSIfRequired($currencyName)
    {
        return ! in_array(strtolower($currencyName), $this->_currency_names_without_plural) ? 's' : '';
    }

    public function setCurrencySubUnitName($currencySubUnitName)
    {
        $this->currencySubUnitName = $currencySubUnitName;
    }

    protected function getCurrencySubUnitName($currencyName)
    {
        if( ! empty( $this->currencySubUnitName ) ) return $this->currencySubUnitName;

        foreach($this->_currency_sub_units as $key => $name)
        {
            if( strtolower($key) == strtolower($currencyName) ) return $name;
        }

        return self::DEFAULT_CURRENCY_SUB_UNIT_NAME;
    }

    // Short term fix. Consider customisable currency settings.
    // Todo: fix.
    private $_currency_names_without_plural = array(
        'ringgit',
        'ringgit malaysia',
        'rupiah',
        'yen',
        'dong',
        'yuan',
        'baht',
        'sen',
    );

    // Short term fix. Consider customisable currency settings.
    // Todo: fix.
    private $_currency_sub_units = array(
        'lek'                       => 'qindarka',
        'Australian dollar'         => 'cent',
        'convertible marka'         => 'fenig',
        'lev'                       => 'stotinka',
        'real'                      => 'centavos',
        'Belarussian rouble'        => 'kopiejka',
        'Canadian dollar'           => 'cent',
        'Swiss franc'               => 'rapp',
        'Cypriot pound'             => 'cent',
        'Czech koruna'              => 'halerz',
        'Danish krone'              => 'ore',
        'kroon'                     => 'senti',
        'euro'                      => 'euro-cent',
        'pound'                     => 'pence',
        'Hong Kong dollar'          => 'cent',
        'Croatian kuna'             => 'lipa',
        'forint'                    => 'filler',
        'Rupiah'                    => 'sen',
        'new sheqel'                => 'agorot',
        'Icelandic króna'           => 'aurar',
        'yen'                       => 'sen',
        'litas'                     => 'cent',
        'lat'                       => 'sentim',
        'Macedonian dinar'          => 'deni',
        'Maltese lira'              => 'centym',
        'Ringgit'                   => 'sen',
        'Ringgit Malaysia'          => 'sen',
        'Norwegian krone'           => 'oere',
        'zloty'                     => 'grosz',
        'Romanian leu'              => 'bani',
        'Russian Federation rouble' => 'kopiejka',
        'Swedish krona'             => 'oere',
        'Tolar'                     => 'stotinia',
        'Slovak koruna'             => 'halierov',
        'lira'                      => 'kuruþ',
        'hryvna'                    => 'cent',
        'dollar'                    => 'cent',
        'dinars'                    => 'para',
        'rand'                      => 'cent',
        'Dong'                      => 'cent',
    );

}