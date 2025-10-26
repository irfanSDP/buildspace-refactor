<?php

class add_indexes_to_improve_queriesTask extends sfBaseTask
{
  protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace        = 'buildspace';
        $this->name             = '2_4_0-8-add_indexes_to_improve_queries';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
    The [2_4_0-8-add_indexes_to_improve_queries|INFO] task does things.
    Call it with:

    [php symfony 2_4_0-8-add_indexes_to_improve_queries|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $data = [
            'bs_tender_bill_item_rate_logs' => [
                'tender_bill_item_rate_logs_project_revision_id_idx' => 'project_revision_id',
                'tender_bill_item_rate_logs_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_bill_item_rate_logs' => [
                'bill_item_rate_logs_project_revision_id_idx' => 'project_revision_id'
            ],
            'bs_bill_items' => [
                'bill_items_project_revision_id_idx' => 'project_revision_id'
            ],
            'bs_variation_orders' => [
                'variation_orders_project_structure_id_idx' => 'project_structure_id'
            ],
            'bs_bill_elements' => [
                'bill_elements_project_structure_id_idx' => 'project_structure_id'
            ],
            'bs_bill_collection_pages' => [
                'bill_collection_pages_revision_id_idx' => 'revision_id',
                'bill_collection_pages_element_id_idx' => 'element_id'
            ],
            'bs_bill_pages' => [
                'bill_pages_revision_id_idx' => 'revision_id',
                'bill_pages_new_revision_id_idx' => 'new_revision_id',
                'bill_pages_element_id_idx' => 'element_id'
            ],
            'bs_post_contract_claims' => [
                'post_contract_claims_project_structure_id_idx' => 'project_structure_id'
            ],
            'bs_post_contract_standard_claim' => [
                'post_contract_standard_claim_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_bill_build_up_quantity_summaries' => [
                'bill_build_up_quantity_summaries_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_bill_build_up_rate_resource_trades' => [
                'bill_build_up_rate_resource_trades_bill_item_id_idx' => 'bill_item_id',
                'bill_bur_resource_trades_build_up_rate_resource_id_idx' => 'build_up_rate_resource_id'
            ],
            'bs_bill_item_cost_data_item' => [
                'bill_item_cost_data_item_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_bill_page_items' => [
                'bill_page_items_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_editor_bill_item_information' => [
                'editor_bill_item_information_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_location_assignments' => [
                'location_assignments_bill_item_id_idx' => 'bill_item_id',
                'location_assignments_project_structure_location_code_id_idx' => 'project_structure_location_code_id',
                'location_assignments_pre_defined_location_code_id_idx' => 'pre_defined_location_code_id'
            ],
            'bs_post_contract_bill_item_rates' => [
                'post_contract_bill_item_rates_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_post_contract_bill_item_type' => [
                'post_contract_bill_item_type_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_post_contract_imported_standard_claim' => [
                'post_contract_imported_standard_claim_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_post_contract_imported_preliminary_claim' => [
                'post_contract_imported_preliminary_claim_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_tender_bill_item_rates' => [
                'tender_bill_item_rates_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_variation_order_items' => [
                'variation_order_items_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_bill_build_up_rate_items' => [
                'bill_bur_items_bill_item_id_idx' => 'bill_item_id',
                'bill_bur_items_build_up_rate_resource_trade_id_idx' => 'build_up_rate_resource_trade_id',
                'bill_bur_items_build_up_rate_resource_id_idx' => 'build_up_rate_resource_id'
            ],
            'bs_bill_build_up_rate_resources' => [
                'bill_bur_resources_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_schedule_task_item_bill_items' => [
                'schedule_task_item_bill_items_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_bill_item_cost_data_prime_cost_sum_item' => [
                'bill_item_cost_data_pc_sum_item_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_bill_item_formulated_columns' => [
                'bill_item_formulated_columns_relation_id_idx' => 'relation_id'
            ],
            'bs_bill_item_type_references' => [
                'bill_item_type_references_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_editor_bill_item_not_listed' => [
                'editor_bill_item_not_listed_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_historical_rates' => [
                'historical_rates_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_sub_packages_bill_items' => [
                'sub_packages_bill_items_bill_item_id_idx' => 'bill_item_id'
            ],
            'bs_bill_build_up_rate_edges' => [
                'bill_build_up_rate_edges_node_to_idx' => 'node_to',
                'bill_build_up_rate_edges_node_from_idx' => 'node_from'
            ],
            'bs_bill_item_type_reference_edges' => [
                'bill_item_type_reference_edges_node_to_idx' => 'node_to',
                'bill_item_type_reference_edges_node_from_idx' => 'node_from'
            ]
        ];

        foreach($data as $tableName => $indexes)
        {
            foreach($indexes as $index => $column)
            {
                // check for index existence, if not then proceed with insertion query.
                $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM pg_indexes 
                WHERE tablename = '".strtolower($tableName)."'
                AND indexname = '".$index."');");

                $stmt->execute();

                $isIndexExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

                if ( $isIndexExists )
                {
                    $this->logSection('2_4_0-8-add_indexes_to_improve_queries', 'Index for '.$column.' already exists in '.$tableName.' table!');
                    continue;
                }

                $stmt = $con->prepare("CREATE INDEX ".$index." ON ".$tableName." (".$column.");");
                
                $stmt->execute();
                
                $this->logSection('2_4_0-8-add_indexes_to_improve_queries', 'Successfully added index for '.$column.' in '.$tableName.' table!');
            }
        }

        return $this->logSection('2_4_0-8-add_indexes_to_improve_queries', 'Migration done!');
    }
}
