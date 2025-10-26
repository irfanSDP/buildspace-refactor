<?php namespace PCK\Helpers;

use Spatie\ArrayToXml\ArrayToXml;

class Xml {

    /**
     * Returns the element at the end of the path.
     *
     * @param \SimpleXMLElement $xml
     * @param array             $path
     * @param null              $castType
     *
     * @return null|\SimpleXMLElement|\SimpleXMLElement[]
     */
    public static function getXMLElement(\SimpleXMLElement $xml, array $path, $castType = null)
    {
        $xmlPath = $xml;
        foreach($path as $tag)
        {
            $xmlPath = $xmlPath->{$tag};

            if( empty( $xmlPath ) )
            {
                return null;
            }
        }

        if( ! is_null($castType) )
        {
            settype($xmlPath, $castType);
        }

        return $xmlPath;
    }

    /**
     * Returns the attribute of the element.
     *
     * @param \SimpleXMLElement $xml
     * @param                   $attribute
     * @param string            $castType
     *
     * @return null|\SimpleXMLElement[]
     */
    public static function getXMLElementAttribute(\SimpleXMLElement $xml, $attribute, $castType = 'string')
    {
        $elementAttribute = $xml->attributes()->{$attribute};

        if( empty( $elementAttribute ) )
        {
            return null;
        }

        settype($elementAttribute, $castType);

        return $elementAttribute;
    }

    /**
     * Converts array data into XML format.
     *
     * @param $dataArray
     *
     * @return string
     */
    public static function arrayToXml($dataArray)
    {
        return ArrayToXml::convert($dataArray, "", false);
    }

}