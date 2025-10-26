<?php

class create_menus_for_library_manager_reportsTask extends sfBaseTask
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
		$this->name                = '1_5_0_create_menus_for_library_manager_reports';
		$this->briefDescription    = '';
		$this->detailedDescription = <<<EOF
The [1_5_0_create_menus_for_library_manager_reports|INFO] task does things.
Call it with:

  [php symfony 1_5_0_create_menus_for_library_manager_reports|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		// initialize the database connection
		$databaseManager = new sfDatabaseManager($this->configuration);
		$connection      = $databaseManager->getDatabase($options['connection'])->getConnection();

		$reportsMainMenu          = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Reports' ));
		$libraryManagerReportMenu = Doctrine_Core::getTable('Menu')->findOneBy('title', array( 'Library Manager Report' ));

		if ( $this->checkExistingLibraryManagerReportMenu($libraryManagerReportMenu) )
		{
			return $this->logSection('1_5_0_create_menus_for_library_manager_reports', 'Library Manager Report Menu exists in the menu already.');
		}

		$libraryManagerReportMenu = $this->createMainLibraryManagerReportMenu($reportsMainMenu);

		$this->insertSubChildMenus($libraryManagerReportMenu);

		return $this->logSection('1_5_0_create_menus_for_library_manager_reports', 'New Library Manager Report Menu has been saved!');
	}

	/**
	 * @param $libraryManagerReportMenu
	 * @return mixed
	 */
	protected function checkExistingLibraryManagerReportMenu($libraryManagerReportMenu)
	{
		return $libraryManagerReportMenu;
	}

	/**
	 * @param $reportsMainMenu
	 * @return Menu
	 */
	protected function createMainLibraryManagerReportMenu($reportsMainMenu)
	{
		$libraryManagerReportMenu         = new Menu();
		$libraryManagerReportMenu->title  = 'Library Manager Report';
		$libraryManagerReportMenu->is_app = false;

		$libraryManagerReportMenu->getNode()->insertAsLastChildOf($reportsMainMenu);

		return $libraryManagerReportMenu;
	}

	/**
	 * @param $libraryManagerReportMenu
	 */
	protected function insertSubChildMenus($libraryManagerReportMenu)
	{
		// will insert sub-child library report's module
		$subReports = array(
			'ResourceLibraryReport'       => 'Resource Library Report',
			'ScheduleOfRateLibraryReport' => 'Schedule of Rate Report',
			'BQLibraryReport'             => 'BQ Library Report',
		);

		foreach ( $subReports as $sysName => $menuName )
		{
			$subMenu          = new Menu();
			$subMenu->title   = $menuName;
			$subMenu->sysname = $sysName;
			$subMenu->is_app  = true;

			$subMenu->getNode()->insertAsLastChildOf($libraryManagerReportMenu);

			$subMenu->free();

			unset( $subMenu );
		}
	}
}
