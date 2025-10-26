<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameIndexes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->renameContactGroupTenderDocumentPermissionLogsIndexes();
        $this->renameTemplateTenderDocumentFolderWorkCategory();
    }

    public function renameTemplateTenderDocumentFolderWorkCategory()
    {
        Schema::table('template_tender_document_folder_work_category', function (Blueprint $table)
        {
            if( \PCK\Helpers\DBHelper::constraintExists('template_tender_document_folder_work_category', 'template_tender_document_folder_work_category_work_category_id_', \PCK\Helpers\DBHelper::CONSTRAINT_TYPE_FOREIGN) )
            {
                $table->dropForeign('template_tender_document_folder_work_category_work_category_id_');
            }
            if( ! \PCK\Helpers\DBHelper::constraintExists('template_tender_document_folder_work_category', 'template_tender_doc_folder_wc_work_category_id_fk', \PCK\Helpers\DBHelper::CONSTRAINT_TYPE_FOREIGN) )
            {
                $table->foreign('work_category_id', 'template_tender_doc_folder_wc_work_category_id_fk')->references('id')->on('work_categories');
            }

            if( \PCK\Helpers\DBHelper::constraintExists('template_tender_document_folder_work_category', 'template_tender_document_folder_work_category_work_category_id_', \PCK\Helpers\DBHelper::CONSTRAINT_TYPE_UNIQUE) )
            {
                $table->dropUnique('template_tender_document_folder_work_category_work_category_id_');
            }
            if( ! \PCK\Helpers\DBHelper::constraintExists('template_tender_document_folder_work_category', 'template_tender_doc_folder_wc_work_category_id_unique', \PCK\Helpers\DBHelper::CONSTRAINT_TYPE_UNIQUE) )
            {
                $table->unique('work_category_id', 'template_tender_doc_folder_wc_work_category_id_unique');
            }
        });
    }

    public function renameContactGroupTenderDocumentPermissionLogsIndexes()
    {
        Schema::table('contract_group_tender_document_permission_logs', function (Blueprint $table)
        {
            if( \PCK\Helpers\DBHelper::constraintExists('contract_group_tender_document_permission_logs', 'contract_group_tender_document_permission_logs_assign_company_l', \PCK\Helpers\DBHelper::CONSTRAINT_TYPE_FOREIGN) )
            {
                $table->dropForeign('contract_group_tender_document_permission_logs_assign_company_l');
            }
            if( ! \PCK\Helpers\DBHelper::constraintExists('contract_group_tender_document_permission_logs', 'tender_doc_permission_log_assign_company_fk', \PCK\Helpers\DBHelper::CONSTRAINT_TYPE_FOREIGN) )
            {
                $table->foreign('assign_company_log_id', 'tender_doc_permission_log_assign_company_fk')->references('id')->on('assign_companies_logs');
            }

            if( \PCK\Helpers\DBHelper::constraintExists('contract_group_tender_document_permission_logs', 'contract_group_tender_document_permission_logs_assign_company_l', \PCK\Helpers\DBHelper::CONSTRAINT_TYPE_UNIQUE) )
            {
                $table->dropUnique('contract_group_tender_document_permission_logs_assign_company_l');
            }
            if( ! \PCK\Helpers\DBHelper::constraintExists('contract_group_tender_document_permission_logs', 'tender_doc_permission_log_assign_company_contract_grp_unique', \PCK\Helpers\DBHelper::CONSTRAINT_TYPE_UNIQUE) )
            {
                $table->unique(array( 'assign_company_log_id', 'contract_group_id' ), 'tender_doc_permission_log_assign_company_contract_grp_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

}
