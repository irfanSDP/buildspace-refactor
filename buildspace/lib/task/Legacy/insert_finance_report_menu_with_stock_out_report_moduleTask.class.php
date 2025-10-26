<?php

class insert_finance_report_menu_with_stock_out_report_moduleTask extends sfBaseTask {

	protected function configure()
	{
		// // add your own arguments here
		// $this->addArguments(array(
		//   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
		// ));

		$this->addOptions(array(
			new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
			new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
			new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
			// add your own options here
		));

		$this->namespace           = '';
		$this->name                = '1_7_0_3_insert_finance_report_menu_with_stock_out_report_module';
		$this->briefDescription    = '';
		$this->detailedDescription = <<<EOF
The [1_7_0_3_insert_finance_report_menu_with_stock_out_report_module|INFO] task does things.
Call it with:

  [php symfony 1_7_0_3_insert_finance_report_menu_with_stock_out_report_module|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		// initialize the database connection
		$databaseManager = new sfDatabaseManager($this->configuration);
		$databaseManager->getDatabase($options['connection'])->getConnection();

		$reportMain    = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Reports' ));
		$financeReport = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Finance Report' ));

		if ( $financeReport )
		{
			return $this->logSection('1_7_0_3_insert_finance_report_menu_with_stock_out_report_module', 'Finance Report\'s Main Menu has been inserted before!');
		}

		$financeReport           = new Menu();
		$financeReport->title    = 'Finance Report';
		$financeReport->is_app   = false;
		$financeReport->priority = 4;
		$financeReport->save();

		$financeReport->getNode()->insertAsLastChildOf($reportMain);

		$stockOutMenu          = new Menu();
		$stockOutMenu->title   = 'Stock Out Report';
		$stockOutMenu->sysname = 'StockOutReport';
		$stockOutMenu->is_app  = true;

		$stockOutMenu->getNode()->insertAsLastChildOf($financeReport);

		return $this->logSection('1_7_0_3_insert_finance_report_menu_with_stock_out_report_module', 'Finance Report\'s Main Menu Successfully Inserted!');
	}

}