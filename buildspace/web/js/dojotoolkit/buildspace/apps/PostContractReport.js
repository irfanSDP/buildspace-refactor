require([
    'dojo/i18n!buildspace/nls/PostContract',
    'dojo/data/ItemFileWriteStore',
    'dijit/layout/TabContainer',
    'dojox/layout/ContentPane',
    'dojox/form/Manager',
    'dijit/form/Button',
    'dijit/Toolbar',
    'dijit/ToolbarSeparator',
    'buildspace/dijit/EditableTree',
    'buildspace/apps/PostContractReport/_base',
    'buildspace/apps/PostContractReport/ProjectListingGrid'
], function(nls){
    buildspace.apps.PostContractReport.ProjectStructureConstants = {
        TYPE_ROOT: 1,
        TYPE_LEVEL: 2,
        TYPE_BILL: 4,
        TYPE_SUPPLY_OF_MATERIAL_BILL: 8,
        TYPE_SCHEDULE_OF_RATE_BILL: 16,
        TYPE_VARIATION_ORDER: 32,
        TYPE_MATERIAL_ON_SITE: 64,
        TYPE_VARIATION_OF_PRICE: 128,
        BILL_STATUS_OPEN: 1,
        BILL_STATUS_CLOSED: 2,
        BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ITEM: 4,
        BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ELEMENT: 8,
        BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_BILL: 16,
        BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ITEM: 32,
        BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ELEMENT: 64,
        BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_BILL: 128,
        STATUS_PRETENDER: 1,
        STATUS_TENDERING: 2,
        STATUS_POSTCONTRACT: 4,
        STATUS_IMPORT: 8,
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
        STATUS_POSTCONTRACT_TEXT: nls.postContract,
        PRELIM_COLUMN_INITIAL: 'initial',
        PRELIM_COLUMN_RECURRING: 'recurring',
        PRELIM_COLUMN_TIMEBASED: 'timeBased',
        PRELIM_COLUMN_WORKBASED: 'workBased',
        PRELIM_COLUMN_FINAL: 'final',
        PRELIM_COLUMN_PREVIOUSCLAIM: 'previousClaim',
        PRELIM_COLUMN_CURRENTCLAIM: 'currentClaim',
        PRELIM_COLUMN_UPTODATECLAIM: 'upToDateClaim',
        OMISSION: 'omission',
        ADDITION: 'addition',
        MATERIAL_ON_SITE_THIS_CLAIM: 1,
        MATERIAL_ON_SITE_CLAIMED: 2
    };

    buildspace.apps.PostContractReport.getBillTypeText = function(itemType) {
        var itemTypeText;

        switch ( parseInt(itemType) )
        {
            case buildspace.apps.PostContractReport.ProjectStructureConstants.BILL_TYPE_STANDARD:
                itemTypeText = buildspace.apps.PostContractReport.ProjectStructureConstants.BILL_TYPE_STANDARD_TEXT;
                break;

            case buildspace.apps.PostContractReport.ProjectStructureConstants.BILL_TYPE_PROVISIONAL:
                itemTypeText = buildspace.apps.PostContractReport.ProjectStructureConstants.BILL_TYPE_PROVISIONAL_TEXT;
                break;

            case buildspace.apps.PostContractReport.ProjectStructureConstants.BILL_TYPE_PRELIMINARY:
                itemTypeText = buildspace.apps.PostContractReport.ProjectStructureConstants.BILL_TYPE_PRELIMINARY_TEXT;
                break;

            case buildspace.apps.PostContractReport.ProjectStructureConstants.BILL_TYPE_PRIMECOST:
                itemTypeText = buildspace.apps.PostContractReport.ProjectStructureConstants.BILL_TYPE_PRIMECOST_TEXT;
                break;

            default:
                itemTypeText = "";
                break;
        }

        return itemTypeText;
    };
});