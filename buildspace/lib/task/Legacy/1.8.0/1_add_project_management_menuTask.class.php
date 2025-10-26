<?php

class add_project_management_menuTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = '';
        $this->name                = '1_8_0_1_add_project_management_menu';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [1_8_0_1_add_project_management_menu|INFO] task does things.
Call it with:

  [php symfony 1_8_0_1_add_project_management_menu|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $pmMenu = DoctrineQuery::create()
            ->select('m.id')
            ->from('Menu m')
            ->where('LOWER(m.sysname) = ?', 'projectmanagement')
            ->andWhere('m.is_app IS TRUE')
            ->fetchOne();

        if(!$pmMenu)
        {
            $prevMenu = DoctrineQuery::create()->select('m.priority')
                ->from('Menu m')
                ->where('LOWER(m.title) = ? ', 'post contract')
                ->andWhere('m.id = m.root_id')
                ->andWhere('m.level = 0')
                ->andWhere('m.is_app IS TRUE')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->limit(1)
                ->fetchOne();

            $priority = $prevMenu['priority'];

            Doctrine_Query::create()
                ->update('Menu')
                ->set('priority', 'priority + 1')
                ->where('priority > ?', $priority)
                ->andWhere('id = root_id')
                ->andWhere('level = 0')
                ->execute();

            $menu = new Menu();

            $menu->title = 'Project Management';
            $menu->icon = 'project_management';
            $menu->is_app = true;
            $menu->sysname = 'ProjectManagement';
            $menu->priority = $priority+1;
            $menu->lft = 1;
            $menu->rgt = 2;
            $menu->level = 0;

            $menu->save();

            $menu->root_id = $menu->id;

            $menu->save();
        }

        $this->logSection('1_8_0_1_add_project_management_menu', 'Successfully added Project Management menu!');
    }

}