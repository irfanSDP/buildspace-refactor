<?php

class create_menu_for_stock_in_out_moduleTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = '';
        $this->name                = '1_6_0_create_menu_for_stock_in_out_module';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [1_6_0_create_menu_for_stock_in_out_module|INFO] task does things.
Call it with:

  [php symfony 1_6_0_create_menu_for_stock_in_out_module|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection      = $databaseManager->getDatabase($options['connection'])->getConnection();

        $financeMainMenu = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Finance' ));

        $stockInMenu  = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Stock In' ));
        $stockOutMenu = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Stock Out' ));

        if ( ! $stockInMenu )
        {
            $subMenu          = new Menu();
            $subMenu->title   = 'Stock In';
            $subMenu->sysname = 'StockIn';
            $subMenu->is_app  = true;

            $subMenu->getNode()->insertAsLastChildOf($financeMainMenu);
        }

        if ( ! $stockOutMenu )
        {
            $subMenu          = new Menu();
            $subMenu->title   = 'Stock Out';
            $subMenu->sysname = 'StockOut';
            $subMenu->is_app  = true;

            $subMenu->getNode()->insertAsLastChildOf($financeMainMenu);
        }

        return $this->logSection('1_6_0_create_menu_for_stock_in_out_module', 'New Stock In and Out Menu has been saved into Finance Module!');
    }

}