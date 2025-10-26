require([
    'dojo/i18n!buildspace/nls/CostData',
    'dijit/layout/StackContainer',
    'dijit/layout/StackController',
    'buildspace/apps/MasterCostData/_base'
], function(nls){
    buildspace.apps.MasterCostData.Levels = {
        overallProjectCosting: 1,
        workCategory: 2,
        element: 3
    };

    buildspace.apps.MasterCostData.ItemTypes = {
        STANDARD: 1,
        PROVISIONAL_SUM: 2,
        PRIME_COST_SUM: 3,
        PRIME_COST_RATE: 4,
        STANDARD_TEXT: nls.standard,
        PROVISIONAL_SUM_TEXT: nls.provisionalSum,
        PRIME_COST_SUM_TEXT: nls.primeCostSum,
        PRIME_COST_RATE_TEXT: nls.projectRatesAnalysis
    };

    buildspace.apps.MasterCostData.getItemTypeText = function(type) {
        switch(parseInt(type)){
            case buildspace.apps.MasterCostData.ItemTypes.STANDARD:
                return buildspace.apps.MasterCostData.ItemTypes.STANDARD_TEXT;
            case buildspace.apps.MasterCostData.ItemTypes.PROVISIONAL_SUM:
                return buildspace.apps.MasterCostData.ItemTypes.PROVISIONAL_SUM_TEXT;
            case buildspace.apps.MasterCostData.ItemTypes.PRIME_COST_SUM:
                return buildspace.apps.MasterCostData.ItemTypes.PRIME_COST_SUM_TEXT;
            case buildspace.apps.MasterCostData.ItemTypes.PRIME_COST_RATE:
                return buildspace.apps.MasterCostData.ItemTypes.PRIME_COST_RATE_TEXT;
            default:
                return "";
        }
    }
});