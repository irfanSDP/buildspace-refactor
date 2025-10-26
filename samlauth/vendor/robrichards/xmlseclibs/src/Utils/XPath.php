<?php

namespace RobRichards\XMLSecLibs\Utils;

class XPath
{
    const ALPHANUMERIC = '\w\d';
    const NUMERIC = '\d';
    const LETTERS = '\w';

    // FIX: put '-' at the end (or escape it) to avoid "invalid range" on PCRE2
    const EXTENDED_ALPHANUMERIC = '\w\d\s_:\.-';

    const SINGLE_QUOTE = '\'';
    const DOUBLE_QUOTE = '"';
    const ALL_QUOTES   = '[\'"]';

    /**
     * Filter an attribute value for safe inclusion in an XPath query.
     *
     * @param string $value The value to filter.
     * @param string $quotes The quotes used to delimit the value in the XPath query.
     *
     * @return string The filtered attribute value.
     */
    public static function filterAttrValue($value, $quotes = self::ALL_QUOTES)
    {
        return preg_replace('#' . $quotes . '#u', '', $value);
    }

    /**
     * Filter an attribute name for safe inclusion in an XPath query.
     *
     * @param string $name  The attribute name to filter.
     * @param mixed  $allow The set of characters to allow.
     *
     * @return string The filtered attribute name.
     */
    public static function filterAttrName($name, $allow = self::EXTENDED_ALPHANUMERIC)
    {
        return preg_replace('#[^' . $allow . ']#u', '', $name);
    }
}
