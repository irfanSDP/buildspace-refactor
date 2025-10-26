<?php

class create_total_without_tax_column_into_bs_stock_in_invoice_items_tableTask extends sfBaseTask {

	protected function configure()
	{
		$this->addOptions(array(
			new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
			new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
			new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
		));

		$this->namespace           = '';
		$this->name                = '1_7_0_5_create_total_without_tax_column_into_bs_stock_in_invoice_items_table';
		$this->briefDescription    = '';
		$this->detailedDescription = <<<EOF
The [1_7_0_5_create_total_without_tax_column_into_bs_stock_in_invoice_items_table|INFO] task does things.
Call it with:

  [php symfony 1_7_0_5_create_total_without_tax_column_into_bs_stock_in_invoice_items_table|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		// initialize the database connection
		$databaseManager = new sfDatabaseManager($this->configuration);
		$connection      = $databaseManager->getDatabase($options['connection'])->getConnection();

		$results = $this->checkColumnExistence($connection);

		if ( !empty( $results ) )
		{
			return $this->log('total_without_tax\'s column has been added into bs_stock_in_invoice_items before.');
		}

		$this->createColumn($connection);

		$this->log('Successfully added total_without_tax\'s column into bs_stock_in_invoice_items');
	}

	private function checkColumnExistence($connection)
	{
		$stmt = $connection->prepare("SELECT column_name FROM information_schema.columns
		WHERE table_name='bs_stock_in_invoice_items' and column_name='total_without_tax'");

		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function createColumn($connection)
	{
		$stmt = $connection->prepare("ALTER TABLE bs_stock_in_invoice_items ADD total_without_tax NUMERIC(18,5) DEFAULT 0");

		$stmt->execute();
	}

}