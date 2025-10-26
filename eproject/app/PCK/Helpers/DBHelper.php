<?php namespace PCK\Helpers;

class DBHelper {

    const CONSTRAINT_TYPE_FOREIGN = 'f';
    const CONSTRAINT_TYPE_UNIQUE  = 'u';

    public static function constraintExists($tableName, $constraintName, $constraintType)
    {
        $stmt = "SELECT count(con.conname)
        FROM pg_catalog.pg_constraint con
        INNER JOIN pg_catalog.pg_class rel
        ON rel.oid = con.conrelid
        INNER JOIN pg_catalog.pg_namespace nsp
        ON nsp.oid = connamespace
        WHERE rel.relname = '{$tableName}'
        AND con.conname = '{$constraintName}'
        AND con.contype = '{$constraintType}';";

        return \DB::select(\DB::raw($stmt))[0]->count >= 1;
    }

}