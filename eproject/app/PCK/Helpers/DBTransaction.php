<?php namespace PCK\Helpers;

use DB;

class DBTransaction {

    private $foreignConnections;

    public function __construct(array $foreignConnections = array())
    {
        $this->foreignConnections = $foreignConnections;
    }

    public function begin()
    {
        DB::beginTransaction();

        foreach($this->foreignConnections as $connection)
        {
            DB::connection($connection)->beginTransaction();
        }
    }

    public function rollback()
    {
        DB::rollBack();

        foreach($this->foreignConnections as $connection)
        {
            DB::connection($connection)->rollBack();
        }
    }

    public function commit()
    {
        DB::commit();

        foreach($this->foreignConnections as $connection)
        {
            DB::connection($connection)->commit();
        }
    }

}