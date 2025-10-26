<?php

class buildspacemasterTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('version', sfCommandArgument::REQUIRED, 'The version you want to migrate to'),
            new sfCommandArgument('task-number', sfCommandArgument::OPTIONAL, 'The specific migration task that you want to run'),
        ));

        $this->namespace = 'buildspace';
        $this->name             = 'migrate';
        $this->briefDescription = 'Buildspace super migration task';
        $this->detailedDescription = <<<EOF
The [buildspace:migrate|INFO] task will runs migration tasks based on version specified:

  [./symfony buildspace:migrate 1_8_0 |INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $tasks = array();
        foreach ($this->commandApplication->getTasks() as $name => $task)
        {
            $strList = explode('-', $task->getName());

            if($strList && $strList[0] === $arguments['version'] && $task->getNamespace() == 'buildspace')
            {
                $tasks[(int)$strList[1]] = $task;
            }
        }

        if(empty($tasks))
        {
            throw new sfCommandException(sprintf('No migration tasks for version "%s".', $arguments['version']));
        }

        ksort($tasks);

        if(!empty($arguments['task-number']))
        {
            if(array_key_exists($arguments['task-number'], $tasks))
            {
                $tasks[$arguments['task-number']]->run();

                return $this->logSection('migrate', sprintf('Successfully ran task "%s"', $tasks[$arguments['task-number']]->getName()));
            }
            else
            {
                throw new sfCommandException(sprintf('No migration task with number "%s" for version "%s".', $arguments['task-number'], $arguments['version']));
            }
        }

        foreach($tasks as $task)
        {
            $task->run();
        }

        return $this->logSection('migrate', sprintf('Successfully migrated to version "%s"', $arguments['version']));
    }
}
