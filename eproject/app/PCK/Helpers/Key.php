<?php namespace PCK\Helpers;

class Key {

    const DEFAULT_KEY_LENGTH = 50;

    /**
     * Creates a unique key in the specified table.
     * The key is made up of random characters.
     *
     * @param $table
     * @param $column
     *
     * @return string
     */
    public static function createKey($table, $column = 'key')
    {
        while( self::keyInTable($table, $string = self::generateRandomString(self::DEFAULT_KEY_LENGTH), $column) )
        {
            //loop until unique key is generated
        }

        return $string;
    }

    /**
     * Creates a unique key across all specified tables
     *
     * @param array $tablesAndColumns (in the format [ tableName1 => columnName1, tableName2 => columnName2 ])
     *
     * @return string
     */
    public static function createUniqueKeyAcrossTables(array $tablesAndColumns)
    {
        $reLook = true;

        while( $reLook )
        {
            $reLook = false;
            $firstKeyValuePair = each($tablesAndColumns);
            $string = self::createKey($firstKeyValuePair['key'], $firstKeyValuePair['value']);

            foreach($tablesAndColumns as $table => $column)
            {
                if( self::keyInTable($table, $string, $column) )
                {
                    $reLook = true;
                    break;
                }
            }
        }

        return $string;
    }

    /**
     * Generates a random string
     *
     * @param $len
     *
     * @return string
     */
    public static function generateRandomString($len)
    {
        return str_random($len);
    }

    /**
     * Checks if the key is valid (i.e. present in the table).
     * Returns true if the key exists.
     *
     * @param $table
     * @param $key
     * @param $column
     *
     * @return bool
     */
    public static function keyInTable($table, $key, $column = 'key')
    {
        $result = \DB::table($table)
            ->select('id')
            ->where($column, '=', $key)
            ->first();

        if( $result ) return true;

        return false;
    }

    /**
     * Returns true if the key is unique.
     * Returns false if key is not unique, or if key does not exist in the table.
     *
     * @param        $table
     * @param        $key
     * @param string $column
     *
     * @return bool
     */
    public static function isUnique($table, $key, $column = 'key')
    {
        return ( \DB::table($table)->select('id')->where($column, '=', $key)->count() == 1 );
    }

}