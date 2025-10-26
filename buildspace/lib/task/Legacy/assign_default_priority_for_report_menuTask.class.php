<?php

class assign_default_priority_for_report_menuTask extends sfBaseTask {

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
		$this->name                = '1_7_0_2_assign_default_priority_for_report_menu';
		$this->briefDescription    = '';
		$this->detailedDescription = <<<EOF
The [1_7_0_2_assign_default_priority_for_report_menu|INFO] task does things.
Call it with:

  [php symfony 1_7_0_2_assign_default_priority_for_report_menu|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		// initialize the database connection
		$databaseManager = new sfDatabaseManager($this->configuration);
		$databaseManager->getDatabase($options['connection'])->getConnection();

		$pbReport   = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Project Builder Report' ));
		$tendReport = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Tendering Report' ));
		$pcReport   = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Post Contract Report' ));
		$lbReport   = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Library Manager Report' ));

		$pbReport->priority = 0;
		$pbReport->save();

		$tendReport->priority = 1;
		$tendReport->save();

		$pcReport->priority = 2;
		$pcReport->save();

		$lbReport->priority = 3;
		$lbReport->save();

		$this->logSection('1_7_0_2_assign_default_priority_for_report_menu', 'Successfully assign default priority for menus in Reports');
	}

}