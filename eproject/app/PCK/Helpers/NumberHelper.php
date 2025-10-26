<?php namespace PCK\Helpers;

class NumberHelper {

    public static function formatNumber($number, $decimalPlaces = 2, $thousandSeparator = ',', $decimalSeparator = '.', $currencySymbol = '') {
        if (! is_numeric($number)) {
            return $number;
        }

        // Convert the number to a string
        $numberString = strval($number);

        // Check if the number is negative
        $isNegative = $numberString[0] === '-';
        if ($isNegative) {
            $numberString = substr($numberString, 1);
        }

        // Split the number into integer and decimal parts
        $parts = explode('.', $numberString);
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '';

        // Reverse the integer part to insert commas
        $reversed = strrev($integerPart);

        // Use a regular expression to insert the thousand separator every three digits
        $formattedReversed = preg_replace('/(\d{3})(?=\d)/', '$1' . $thousandSeparator, $reversed);

        // Reverse the string again to get the original order
        $formattedInteger = strrev($formattedReversed);

        // Ensure the decimal part has the required number of decimal places
        if ($decimalPlaces > 0) {
            $decimalPart = str_pad($decimalPart, $decimalPlaces, '0', STR_PAD_RIGHT);
            $formattedDecimal = $decimalSeparator . substr($decimalPart, 0, $decimalPlaces);
        } else {
            $formattedDecimal = '';
        }

        // Combine the integer and decimal parts
        $formattedNumber = $formattedInteger . $formattedDecimal;

        // Add back the negative sign if the number was negative
        if ($isNegative) {
            $formattedNumber = '-' . $formattedNumber;
        }
        if (! empty($currencySymbol)) {
            $formattedNumber = $currencySymbol . $formattedNumber;
        }

        return $formattedNumber;
    }

    /**
     * Convert a value to a float.
     *
     * @param mixed $value The value to convert.
     * @param int $decimalPlaces The number of decimal places to round to.
     * @return float|null The converted value or null if the value is not a number.
     */
    public static function convertToFloat($value, $decimalPlaces=2) {
        if (! NumberHelper::isNumber($value)) {
            return null;
        }
        return round(floatval($value), $decimalPlaces);
    }

    /**
     * Check if a value is a number.
     *
     * @param mixed $value The value to check.
     * @param bool $unsigned Whether the number should be unsigned.
     * @return bool True if the value is a number, false otherwise.
     */
    public static function isNumber($value, $unsigned=false) {
        if (! is_numeric($value)) {
            return false;
        }
        if ($unsigned) {
            return $value >= 0;
        }
        return true;
    }

    /**
     * Get the maximum possible value for a given decimal precision and scale.
     *
     * @param int $precision The total number of digits.
     * @param int $scale The number of digits after the decimal point.
     * @return string The maximum value as a string.
     */
    public static function maxDecimalValue(int $precision, int $scale): string
    {
        // Digits allowed before the decimal point
        $wholeNumberDigits = $precision - $scale;

        // Generate the max number based on the allowed digits
        $maxWholePart = str_repeat('9', $wholeNumberDigits);
        $maxDecimalPart = str_repeat('9', $scale);

        // Combine whole part and decimal part
        if ($scale > 0) {
            return $maxWholePart . '.' . $maxDecimalPart;
        }

        return $maxWholePart;
    }

    public static function amountDifference($amount1, $amount2) {
        if (! self::isNumber($amount1) || ! self::isNumber($amount2)) {
            return null;
        }
        return abs($amount1 - $amount2);
    }

}