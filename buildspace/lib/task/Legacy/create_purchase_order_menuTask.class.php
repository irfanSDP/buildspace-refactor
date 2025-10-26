<?php

class create_purchase_order_menuTask extends sfBaseTask
{
	protected function configure()
	{
		$this->addOptions(array(
			new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
			new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
			new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
			// add your own options here
		));

		$this->namespace           = '';
		$this->name                = '1_5_0_create_purchase_order_menu';
		$this->briefDescription    = '';
		$this->detailedDescription = <<<EOF
The [1_5_0_create_purchase_order_menu|INFO] task does things.
Call it with:

  [php symfony 1_5_0_create_purchase_order_menu|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		// initialize the database connection
		$databaseManager = new sfDatabaseManager($this->configuration);
		$connection      = $databaseManager->getDatabase($options['connection'])->getConnection();

		$financeMainMenu = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Finance' ));
		$poReportMenu    = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Purchase Order' ));

		if ( $this->checkExistingPOMenu($poReportMenu) )
		{
			return $this->logSection('1_5_0_create_purchase_order_menu', 'Purchase Order Menu exists in the menu already.');
		}

		$this->insertSubChildMenus($financeMainMenu);

		return $this->logSection('1_5_0_create_purchase_order_menu', 'New Purchase Order Menu has been saved!');
	}

	/**
	 * @param $poReportMenu
	 * @return mixed
	 */
	protected function checkExistingPOMenu($poReportMenu)
	{
		return $poReportMenu;
	}

	/**
	 * @param $financeMainMenu
	 */
	protected function insertSubChildMenus($financeMainMenu)
	{
		$subMenu          = new Menu();
		$subMenu->title   = 'Purchase Order';
		$subMenu->sysname = 'PurchaseOrder';
		$subMenu->is_app  = true;

		$subMenu->getNode()->insertAsLastChildOf($financeMainMenu);

		$subMenu->free();
	}
}