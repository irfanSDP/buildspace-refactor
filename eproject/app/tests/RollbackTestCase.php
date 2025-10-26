<?php

abstract class RollbackTestCase extends TestCase {

    private $transaction;
    protected $connections = array();

    public function setUp()
    {
        parent::setUp();
        $this->transaction = new \PCK\Helpers\DBTransaction($this->connections);
        $this->transaction->begin();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->transaction->rollback();
    }

}
