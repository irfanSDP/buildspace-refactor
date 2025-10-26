<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Companies\Company;
use PCK\Vendor\Vendor;

class AlterUniqueIndexesToIncludeContractGroupCategoryIdInCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->dropUnique('companies_reference_id_unique');
			$table->dropUnique('companies_reference_no_unique');
			$table->dropUnique('companies_registration_id_unique');
			$table->dropUnique('companies_tax_registration_id_unique');
			$table->dropUnique('companies_third_party_app_identifier_third_party_vendor_id_uniq');

			$table->unique(['contract_group_category_id', 'reference_id'], 'companies_reference_id_unique');
			$table->unique(['contract_group_category_id', 'reference_no'], 'companies_reference_no_unique');
			$table->unique(['contract_group_category_id', 'registration_id'], 'companies_registration_id_unique');
			$table->unique(['contract_group_category_id', 'tax_registration_id'], 'companies_tax_registration_id_unique');
			$table->unique(['contract_group_category_id', 'third_party_app_identifier', 'third_party_vendor_id'], 'companies_third_party_app_identifier_third_party_vendor_id_uniq');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->dropUnique('companies_reference_id_unique');
			$table->dropUnique('companies_reference_no_unique');
			$table->dropUnique('companies_registration_id_unique');
			$table->dropUnique('companies_tax_registration_id_unique');
			$table->dropUnique('companies_third_party_app_identifier_third_party_vendor_id_uniq');

			$table->unique(['reference_id'], 'companies_reference_id_unique');
			$table->unique(['reference_no'], 'companies_reference_no_unique');
			$table->unique(['registration_id'], 'companies_registration_id_unique');
			$table->unique(['tax_registration_id'], 'companies_tax_registration_id_unique');
			$table->unique(['third_party_app_identifier', 'third_party_vendor_id'], 'companies_third_party_app_identifier_third_party_vendor_id_uniq');
		});
	}

}
