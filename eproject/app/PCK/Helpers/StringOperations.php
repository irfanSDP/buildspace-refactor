<?php namespace PCK\Helpers;

class StringOperations {

    /**
     * Wraps a string and breaks it into an array.
     *
     * @param     $string
     * @param int $maxCharsPerLine
     *
     * @return array
     */
    public static function wrapToArray($string, $maxCharsPerLine = 80)
    {
        $wrappedText = wordwrap($string, $maxCharsPerLine, "!");

        return explode("!", $wrappedText);
    }

    /**
     * Shortens the string and adds a postfix.
     * The length of the shortened string and the postfix together does not exceed the specified length.
     *
     * @param string $string
     * @param int    $length
     * @param string $postfix
     *
     * @return string
     */
    public static function shorten($string, $length, $postfix = '...')
    {
        $string = mb_convert_encoding($string, 'ASCII', 'UTF-8'); // Convert to ASCII to prevent any undesirable effects from unpredictable text

        $string = trim($string);

        // If string is already within the length limit
        if(strlen($string) == 0 || strlen($string) <= $length) {
            return $string;
        }

        // Length of the postfix (default is 3 for "...")
        $postfixLength = strlen($postfix);

        // Calculate the maximum length of the string excluding the postfix
        $substringLength = $length - $postfixLength;

        // Ensure substring length is not negative
        if ($substringLength < 0) {
            $substringLength = 0;
        }

        // Get the truncated string
        $newString = substr($string, 0, $substringLength);

        // Return the new string with the postfix
        return $newString . $postfix;
    }


    /**
     * Left pads a string.
     *
     * @param $string
     * @param $totalLength
     * @param $padding
     *
     * @return string
     */
    public static function pad($string, $totalLength, $padding)
    {
        return str_pad($string, $totalLength, $padding, STR_PAD_LEFT);
    }

    public static function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    public static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
    }

    public static function numberToAlphabet($number)
    {
        $number = intval($number);
        
        if ($number <= 0)
        {
           return '';
        }

        $alphabet = '';
        while($number != 0)
        {
           $p = ($number - 1) % 26;
           $number = intval(($number - $p) / 26);
           $alphabet = chr(65 + $p) . $alphabet;
       }

       return $alphabet;
    }

    public static function alphabetToNumber($alphabet)
    {
        $alphabet = strtoupper($alphabet);
        $number = 0;
        $length = strlen($alphabet);
        for ($i = 0; $i < $length; $i++)
        {
            $number += (ord($alphabet[$i]) - 64) * pow(26, $length - $i - 1);
        }
        return $number;
    }

    public static function replace($originalString, $search, $replace)
    {
        $replacedString = '';
        for ($i = 0; $i < strlen($originalString); $i++) {  // Loop through each character in the string
            if ($originalString[$i] === $search) {
                $replacedString .= $replace; // Replace the character
            } else {
                $replacedString .= $originalString[$i]; // Keep the character as is
            }
        }
        return $replacedString;
    }
}