<?php

class switch_menu_ordering_between_stock_in_and_stock_outTask extends sfBaseTask {

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
		$this->name                = '1_7_0_1_switch_menu_ordering_between_stock_in_and_stock_out';
		$this->briefDescription    = '';
		$this->detailedDescription = <<<EOF
The [1_7_0_1_switch_menu_ordering_between_stock_in_and_stock_out|INFO] task does things.
Call it with:

  [php symfony 1_7_0_1_switch_menu_ordering_between_stock_in_and_stock_out|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		// initialize the database connection
		$databaseManager = new sfDatabaseManager($this->configuration);
		$databaseManager->getDatabase($options['connection'])->getConnection();

		$compDir  = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Company Directory' ));
		$rfq      = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Request For Quotation' ));
		$po       = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Purchase Order' ));
		$stockIn  = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Stock In' ));
		$stockOut = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Stock Out' ));

		$compDir->priority = 0;
		$compDir->save();

		$rfq->priority = 1;
		$rfq->save();

		$po->priority = 2;
		$po->save();

		$stockIn->priority = 3;
		$stockIn->lft      = 8;
		$stockIn->rgt      = 9;
		$stockIn->save();

		$stockOut->priority = 4;
		$stockOut->lft      = 10;
		$stockOut->rgt      = 11;
		$stockOut->save();

		// for Report Module
		$reportResource = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Resource Library Report' ));
		$reportSor      = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Schedule of Rate Report' ));
		$reportBq       = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'BQ Library Report' ));

		$reportResource->priority = 0;
		$reportResource->lft      = 9;
		$reportResource->rgt      = 10;
		$reportResource->save();

		$reportSor->priority = 1;
		$reportSor->lft      = 11;
		$reportSor->rgt      = 12;
		$reportSor->save();

		$reportBq->priority = 2;
		$reportBq->lft      = 13;
		$reportBq->rgt      = 14;
		$reportBq->save();

		$this->logSection('1_7_0_1_switch_menu_ordering_between_stock_in_and_stock_out', 'Successfully swap menu\'s ordering between Stock In and Stock Out!');
	}

}