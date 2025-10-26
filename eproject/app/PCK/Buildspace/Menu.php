<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model {

    const TITLE_ADMINISTRATION         = 'Administration';
    const TITLE_SYSTEM_ADMINISTRATION  = 'System Administration';
    const TITLE_REPORTS                = 'Reports';
    const TITLE_PROJECT_BUILDER        = 'Project Builder';
    const TITLE_PROJECT_BUILDER_REPORT = 'Project Builder Report';
    const TITLE_TENDERING              = 'Tendering';
    const TITLE_TENDERING_REPORT       = 'Tendering Report';
    const TITLE_POST_CONTRACT          = 'Post Contract';
    const TITLE_POST_CONTRACT_REPORT   = 'Post Contract Report';

    const BS_APP_IDENTIFIER_PROJECT_BUILDER                 = 1;
    const BS_APP_IDENTIFIER_TENDERING                       = 2;
    const BS_APP_IDENTIFIER_POST_CONTRACT                   = 3;
    const BS_APP_IDENTIFIER_PROJECT_BUILDER_REPORT          = 4;
    const BS_APP_IDENTIFIER_TENDERING_REPORT                = 5;
    const BS_APP_IDENTIFIER_POST_CONTRACT_REPORT            = 6;
    const BS_APP_IDENTIFIER_APPROVAL                        = 7;
    const BS_APP_IDENTIFIER_PROJECT_MANAGEMENT              = 8;
    const BS_APP_IDENTIFIER_RESOURCE_LIBRARY                = 9;
    const BS_APP_IDENTIFIER_SCHEDULE_OF_RATE_LIBRARY        = 10;
    const BS_APP_IDENTIFIER_BQ_LIBRARY                      = 11;
    const BS_APP_IDENTIFIER_COMPANY_DIRECTORIES             = 12;
    const BS_APP_IDENTIFIER_REQUEST_FOR_QUOTATION           = 13;
    const BS_APP_IDENTIFIER_PURCHASE_ORDER                  = 14;
    const BS_APP_IDENTIFIER_STOCK_IN                        = 15;
    const BS_APP_IDENTIFIER_STOCK_OUT                       = 16;
    const BS_APP_IDENTIFIER_RESOURCE_LIBRARY_REPORT         = 17;
    const BS_APP_IDENTIFIER_SCHEDULE_OF_RATE_LIBRARY_REPORT = 18;
    const BS_APP_IDENTIFIER_BQ_LIBRARY_REPORT               = 19;
    const BS_APP_IDENTIFIER_STOCK_IN_REPORT                 = 20;
    const BS_APP_IDENTIFIER_STOCK_OUT_REPORT                = 21;
    const BS_APP_IDENTIFIER_PRINTING_LAYOUT_SETTING         = 22;
    const BS_APP_IDENTIFIER_SYSTEM_MAINTENANCE              = 23;
    const BS_APP_IDENTIFIER_SYSTEM_ADMINISTRATION           = 24;
    const BS_APP_IDENTIFIER_EPROJECT_SITE_PROGRESS          = 25;

    const BS_APP_NAME_PROJECT_BUILDER                   = 'ProjectBuilder';
    const BS_APP_NAME_TENDERING                         = 'Tendering';
    const BS_APP_NAME_POST_CONTRACT                     = 'PostContract';
    const BS_APP_NAME_PROJECT_BUILDER_REPORT            = 'ProjectBuilderReport';
    const BS_APP_NAME_TENDERING_REPORT                  = 'TenderingReport';
    const BS_APP_NAME_POST_CONTRACT_REPORT              = 'PostContractReport';
    const BS_APP_NAME_APPROVAL                          = 'Approval';
    const BS_APP_NAME_PROJECT_MANAGEMENT                = 'ProjectManagement';
    const BS_APP_NAME_RESOURCE_LIBRARY                  = 'ResourceLibrary';
    const BS_APP_NAME_SCHEDULE_OF_RATE_LIBRARY          = 'ScheduleOfRateLibrary';
    const BS_APP_NAME_BQ_LIBRARY                        = 'BQLibrary';
    const BS_APP_NAME_COMPANY_DIRECTORIES               = 'CompanyDirectories';
    const BS_APP_NAME_REQUEST_FOR_QUOTATION             = 'RequestForQuotation';
    const BS_APP_NAME_PURCHASE_ORDER                    = 'PurchaseOrder';
    const BS_APP_NAME_STOCK_IN                          = 'StockIn';
    const BS_APP_NAME_STOCK_OUT                         = 'StockOut';
    const BS_APP_NAME_RESOURCE_LIBRARY_REPORT           = 'ResourceLibraryReport';
    const BS_APP_NAME_SCHEDULE_OF_RATE_LIBRARY_REPORT   = 'ScheduleOfRateLibraryReport';
    const BS_APP_NAME_BQ_LIBRARY_REPORT                 = 'BQLibraryReport';
    const BS_APP_NAME_STOCK_IN_REPORT                   = 'StockInReport';
    const BS_APP_NAME_STOCK_OUT_REPORT                  = 'StockOutReport';
    const BS_APP_NAME_PRINTING_LAYOUT_SETTING           = 'PrintingLayoutSetting';
    const BS_APP_NAME_SYSTEM_MAINTENANCE                = 'SystemMaintenance';
    const BS_APP_NAME_SYSTEM_ADMINISTRATION             = 'SystemAdministration';
    const BS_APP_NAME_EPROJECT_SITE_PROGRESS            = 'EprojectSiteProgress';

    protected $connection = 'buildspace';

    protected $table = 'bs_menus';

}