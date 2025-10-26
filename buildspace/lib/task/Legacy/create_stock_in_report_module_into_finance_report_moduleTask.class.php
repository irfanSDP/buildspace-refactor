<?php

class create_stock_in_report_module_into_finance_report_moduleTask extends sfBaseTask {

	protected function configure()
	{
		$this->addOptions(array(
			new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
			new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
			new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
			// add your own options here
		));

		$this->namespace           = '';
		$this->name                = '1_7_0_4_create_stock_in_report_module_into_finance_report_module';
		$this->briefDescription    = '';
		$this->detailedDescription = <<<EOF
The [1_7_0_4_create_stock_in_report_module_into_finance_report_module|INFO] task does things.
Call it with:

  [php symfony 1_7_0_4_create_stock_in_report_module_into_finance_report_module|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		// initialize the database connection
		$databaseManager = new sfDatabaseManager($this->configuration);
		$databaseManager->getDatabase($options['connection'])->getConnection();

		$financeReport = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Finance Report' ));
		$stockInMenu   = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Stock In Report' ));

		// need finance report to exist then only can enter Stock In Report's module
		if ( !$financeReport )
		{
			return $this->logSection('1_7_0_4_create_stock_in_report_module_into_finance_report_module', 'Finance Report\'s Main Menu must be inserted in order to enter Stock In Report\'s module.');
		}

		if ( $stockInMenu )
		{
			return $this->logSection('1_7_0_4_create_stock_in_report_module_into_finance_report_module', 'Stock In Report\'s Menu has been inserted.');
		}

		$stockInMenu          = new Menu();
		$stockInMenu->title   = 'Stock In Report';
		$stockInMenu->sysname = 'StockInReport';
		$stockInMenu->is_app  = true;

		$stockInMenu->getNode()->insertAsFirstChildOf($financeReport);

		return $this->logSection('1_7_0_4_create_stock_in_report_module_into_finance_report_module', 'Stock In Report\'s Menu created successfully.');
	}

}