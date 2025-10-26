require([
    'dojo/i18n!buildspace/nls/CostData',
    'dijit/layout/StackContainer',
    'dijit/layout/StackController',
    'buildspace/apps/CostData/_base'
], function(nls){
    buildspace.apps.CostData.Levels = {
        overallProjectCosting: 1,
        workCategory: 2,
        element: 3
    };

    buildspace.apps.CostData.ItemTypes = {
        STANDARD: 1,
        PROVISIONAL_SUM: 2,
        PRIME_COST_SUM: 3,
        PRIME_COST_RATE: 4,
        STANDARD_TEXT: nls.standard,
        PROVISIONAL_SUM_TEXT: nls.provisionalSum,
        PRIME_COST_SUM_TEXT: nls.primeCostSum,
        PRIME_COST_RATE_TEXT: nls.projectRatesAnalysis
    };

    buildspace.apps.CostData.getItemTypeText = function(type) {
        switch(parseInt(type)){
            case buildspace.apps.CostData.ItemTypes.STANDARD:
                return buildspace.apps.CostData.ItemTypes.STANDARD_TEXT;
            case buildspace.apps.CostData.ItemTypes.PROVISIONAL_SUM:
                return buildspace.apps.CostData.ItemTypes.PROVISIONAL_SUM_TEXT;
            case buildspace.apps.CostData.ItemTypes.PRIME_COST_SUM:
                return buildspace.apps.CostData.ItemTypes.PRIME_COST_SUM_TEXT;
            case buildspace.apps.CostData.ItemTypes.PRIME_COST_RATE:
                return buildspace.apps.CostData.ItemTypes.PRIME_COST_RATE_TEXT;
            default:
                return "";
        }
    }
});