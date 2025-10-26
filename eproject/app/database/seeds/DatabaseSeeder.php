<?php

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        $this->call('CountriesTableSeeder');
        $this->call('StatesTableSeeder');
        $this->call('NotificationCategoriesTableSeeder');
        $this->call('ContractGroupsTableSeeder');
        $this->call('ContractPAM2006Seeder');
        $this->call('MyCompanyProfileTableSeeder');
        $this->call('LanguagesTableSeeder');
        $this->call('UsersTableSeeder');
        $this->call('ClauseItemsTableSeeder');
        $this->call('WorkCategoriesTableSeeder');
        $this->call('WorkSubcategoriesTableSeeder');
        $this->call('CPEGradesTablesSeeder');
        $this->call('ContractorRegistrationStatusesTableSeeder');
        $this->call('FormOfTenderTableSeeder');
        $this->call('FormOfTenderAllTenderAlternativesTableSeeder');
        $this->call('ContractGroupCategoriesTableSeeder');
        $this->call('TemplateTenderDocumentFoldersTableSeeder');
        $this->call('ContractGroupsTableSeeder_addProjectManager');
        $this->call('ContractGroupCategoriesTableSeeder_addProjectManager');
        $this->call('ContractGroupCategoriesTableSeeder_addContractor');
        $this->call('ContractGroupCategoriesTableSeeder_addConsultant');
        $this->call('ContractGroupsTableSeeder_ContractGroupCategoriesTableSeeder_addConsultants3And4And5');
        $this->call('ContractGroupsTableSeeder_defaultNames');
        $this->call('ProjectRolesTableSeeder');
        $this->call('FormOfTenderTableSeeder_addFontSize');
        $this->call('BS_UserTableSeeder_createIfNonExistent');
        $this->call('ClauseItemsTableSeeder_renameClauses');
        $this->call('ContractsTableSeeder_IndonesiaCivilContract');
        $this->call('ClausesTableSeeder_addTypeColumnData');
        $this->call('ClausesTableSeeder_addIndonesiaCivilContractClauses');
        $this->call('UserSettingsTableSeeder');
        $this->call('ProjectLabourRatesTableSeeder');
        $this->call('TechnicalEvaluationSetReferenceTableSeeder');
        $this->call('AddRequestForVariationCategoriesTableSeeder');
        $this->call('LetterOfAwardExistingProjectsTableSeeder');
        $this->call('CurrencySettingsTableSeeder');
        $this->call('SystemModuleConfigurationTableSeeder');
        $this->call('ContractGroupsTableSeeder_addConsultants6to17');
    }

}
