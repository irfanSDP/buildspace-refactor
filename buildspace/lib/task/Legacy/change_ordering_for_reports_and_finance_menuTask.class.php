<?php

class change_ordering_for_reports_and_finance_menuTask extends sfBaseTask
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
		$this->name                = '1_5_0_change_ordering_for_reports_and_finance_menu';
		$this->briefDescription    = '';
		$this->detailedDescription = <<<EOF
The [1_5_0_change_ordering_for_reports_and_finance_menu|INFO] task does things.
Call it with:

  [php symfony 1_5_0_change_ordering_for_reports_and_finance_menu|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		// initialize the database connection
		$databaseManager = new sfDatabaseManager($this->configuration);
		$connection      = $databaseManager->getDatabase($options['connection'])->getConnection();

		$reportMenu  = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Reports' ));
		$financeMenu = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Finance' ));

		// will cache the priority, first
		$reportPriority  = $reportMenu->priority;
		$financePriority = $financeMenu->priority;

		// save the priority of finance for report menu
		$reportMenu->priority = $financePriority;
		$reportMenu->save();

		// save the priority of report for finance menu
		$financeMenu->priority = $reportPriority;
		$financeMenu->save();

		$this->logSection('1_5_0_change_ordering_for_reports_and_finance_menu', 'Successfully swap menu\'s ordering between Report and Finance!');
	}
}
