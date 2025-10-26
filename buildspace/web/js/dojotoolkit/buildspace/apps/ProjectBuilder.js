require([
    'dojo/i18n!buildspace/nls/ProjectBuilder',
    'dijit/MenuBar',
    'dijit/layout/SplitContainer',
    'dijit/layout/TabContainer',
    'dojox/layout/ContentPane',
    'dojox/form/Manager',
    'dijit/form/Button',
    'dijit/Toolbar',
    'dijit/ToolbarSeparator',
    'buildspace/dijit/EditableTree',
    "dijit/PopupMenuItem",
    'dojo/data/ItemFileWriteStore',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/apps/ProjectBuilder/_base',
    'buildspace/apps/ProjectBuilder/ProjectListingGrid',
    'buildspace/apps/ProjectBuilder/BillManager',
    'buildspace/apps/ProjectBuilder/SupplyOfMaterialBillManager',
    'buildspace/apps/ProjectBuilder/ScheduleOfRateBillManager',
    'buildspace/apps/ProjectBuilder/BillManager/buildUpGrid',
    'buildspace/apps/ProjectBuilder/BillPrintoutSetting/headStyling',
    'buildspace/apps/ProjectBuilder/BillPrintoutSetting/fontNumberFormat',
    'buildspace/apps/ProjectBuilder/BillPrintoutSetting/pageFormat',
    'buildspace/apps/ProjectBuilder/BillPrintoutSetting/standardPhrases',
    'buildspace/apps/ProjectBuilder/BillPrintoutSetting/headerFooter',
    'buildspace/apps/ProjectBuilder/SupplyOfMaterialBillPrintoutSetting/headStyling',
    'buildspace/apps/ProjectBuilder/SupplyOfMaterialBillPrintoutSetting/fontNumberFormat',
    'buildspace/apps/ProjectBuilder/SupplyOfMaterialBillPrintoutSetting/pageFormat',
    'buildspace/apps/ProjectBuilder/SupplyOfMaterialBillPrintoutSetting/standardPhrases',
    'buildspace/apps/ProjectBuilder/SupplyOfMaterialBillPrintoutSetting/headerFooter',
    'buildspace/apps/ProjectBuilder/ScheduleOfRateBill/HeadStylingForm',
    'buildspace/apps/ProjectBuilder/ScheduleOfRateBill/FontNumberFormatForm',
    'buildspace/apps/ProjectBuilder/ScheduleOfRateBill/PageFormatForm',
    'buildspace/apps/ProjectBuilder/ScheduleOfRateBill/StandardPhrasesForm',
    'buildspace/apps/ProjectBuilder/ScheduleOfRateBill/HeaderFooterForm'
], function(nls){
    buildspace.apps.ProjectBuilder.ProjectStructureConstants = {
        TYPE_ROOT: 1,
        TYPE_LEVEL: 2,
        TYPE_BILL: 4,
        TYPE_SUPPLY_OF_MATERIAL_BILL: 8,
        TYPE_SCHEDULE_OF_RATE_BILL: 16,
        BILL_STATUS_OPEN: 1,
        BILL_STATUS_CLOSED: 2,
        BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ITEM: 4,
        BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ELEMENT: 8,
        BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_BILL: 16,
        BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ITEM: 32,
        BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ELEMENT: 64,
        BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_BILL: 128,
        BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_ITEM: 256,
        BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_ELEMENT: 512,
        BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_BILL: 1024,
        STATUS_PRETENDER: 1,
        STATUS_TENDERING: 2,
        STATUS_POSTCONTRACT: 4,
        BILL_TYPE_STANDARD: 1,
        BILL_TYPE_PROVISIONAL: 2,
        BILL_TYPE_PRELIMINARY: 4,
        BILL_TYPE_PRIMECOST: 8,
        BILL_TYPE_STANDARD_TEXT: nls.standard,
        BILL_TYPE_PROVISIONAL_TEXT: nls.standardProvisional,
        BILL_TYPE_PRELIMINARY_TEXT: nls.preliminary,
        BILL_TYPE_PRIMECOST_TEXT: nls.primeCostProvisional,
        BILL_TYPE_STANDARD_DESCRIPTION: nls.standardDescription,
        BILL_TYPE_PROVISIONAL_DESCRIPTION: nls.standardProvisionalDescription,
        BILL_TYPE_PRELIMINARY_DESCRIPTION: nls.preliminaryDescription,
        BILL_TYPE_PRIMECOST_DESCRIPTION: nls.primeCostProvisionalDescription,
        STATUS_PRETENDER_TEXT: nls.preTender,
        STATUS_TENDERING_TEXT: nls.tendering,
        STATUS_POSTCONTRACT_TEXT: nls.postContract
    };

    buildspace.apps.ProjectBuilder.getBillTypeText = function(type){
        switch(parseInt(type)){
            case buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PROVISIONAL:
                return buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PROVISIONAL_TEXT;
            case buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRELIMINARY:
                return buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRELIMINARY_TEXT;
            case buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_STANDARD:
                return buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_STANDARD_TEXT;
            case buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRIMECOST:
                return buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRIMECOST_TEXT;
            default:
                return "";
        }
    };

    buildspace.apps.ProjectBuilder.getRecalculateBillStatuses = function(){
        return [
            this.ProjectStructureConstants.BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ITEM,
            this.ProjectStructureConstants.BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ELEMENT,
            this.ProjectStructureConstants.BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_BILL,
            this.ProjectStructureConstants.BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ITEM,
            this.ProjectStructureConstants.BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ELEMENT,
            this.ProjectStructureConstants.BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_BILL,
            this.ProjectStructureConstants.BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_ITEM,
            this.ProjectStructureConstants.BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_ELEMENT,
            this.ProjectStructureConstants.BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_BILL
        ];
    };

    buildspace.apps.ProjectBuilder.isRecalculateBillStatus = function(status){
        return (this.getRecalculateBillStatuses().indexOf( parseInt(String(status)) ) > -1);
    };

    buildspace.apps.ProjectBuilder.NoMarkupItemTypes = [
        buildspace.constants.HIERARCHY_TYPE_ITEM_PC_RATE,
        buildspace.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT,
        buildspace.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE
    ];
});