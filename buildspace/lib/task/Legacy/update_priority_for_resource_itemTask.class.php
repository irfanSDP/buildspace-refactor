<?php

class update_priority_for_resource_itemTask extends sfBaseTask
{
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
		$this->name                = '1_5_0_update_priority_for_resource_item';
		$this->briefDescription    = '';
		$this->detailedDescription = <<<EOF
The [1_5_0_update_priority_for_resource_item|INFO] task does things.
Call it with:

  [php symfony 1_5_0_update_priority_for_resource_item|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		// initialize the database connection
		$databaseManager = new sfDatabaseManager( $this->configuration );
		$connection      = $databaseManager->getDatabase($options['connection'])->getConnection();

		$valuesArray = array();

		$bqRootItems = $this->getBQRootItems();

		foreach ( $bqRootItems as $bqRootItem )
		{
			$valuesArray[] = "({$bqRootItem['id']}, {$bqRootItem['priority']})";

			unset($bqRootItem);
		}

		unset($bqRootItems);

		if ( count($valuesArray) == 0 )
		{
			$this->logSection('update-resource-item-priority', 'Nothing to be updated !');

			return;
		}

		$this->updatePriority($connection, $valuesArray);

		unset($valuesArray);

		$this->logSection('update-resource-item-priority', 'Successfully updated all the Resource Item(s) priority');
	}

	/**
	 * @return Doctrine_Collection|Doctrine_Collection_OnDemand|int|mixed
	 */
	protected function getBQRootItems()
	{
		return DoctrineQuery::create()
		->select('i.id, i.root_id, i.priority')
		->from('ResourceItem i')
		->where('i.root_id = i.id')
		->andWhere('i.level = 0')
		->fetchArray();
	}

	/**
	 * @param $connection
	 * @param $valuesArray
	 */
	protected function updatePriority($connection, $valuesArray)
	{
		$stmt = $connection->prepare("UPDATE " . ResourceItemTable::getInstance()->getTableName() . " SET
		priority = newvalues.root_priority FROM (VALUES " . implode(', ', $valuesArray) . ") AS newvalues (item_id, root_priority)
		WHERE id <> newvalues.item_id AND root_id = newvalues.item_id AND deleted_at IS NULL");

		$stmt->execute();
	}
}
