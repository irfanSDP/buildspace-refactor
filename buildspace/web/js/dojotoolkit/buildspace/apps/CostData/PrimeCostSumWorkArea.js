define('buildspace/apps/CostData/PrimeCostSumWorkArea',[
    'dojo/_base/declare',
    './PrimeCostSumBreakdown',
    './NominatedSubContractorColumns',
    'dojo/i18n!buildspace/nls/CostData'], function(declare, PrimeCostSumBreakdown, NominatedSubContractorColumns, nls){

    return declare('buildspace.apps.CostData.PrimeCostSumWorkArea', dijit.layout.TabContainer, {
        region: "center",
        style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        costData: null,
        stackContainer: null,
        mainBreakdownGrid: null,
        editable: false,
        postCreate: function(){
            var breakdown = PrimeCostSumBreakdown({
                title: nls.breakdown,
                stackContainer: this.stackContainer,
                mainBreakdownGrid: this.mainBreakdownGrid,
                editable: this.editable,
                costData: this.costData
            });
            this.addChild(breakdown);

            var columnsGrid = NominatedSubContractorColumns({
                title: nls.nominatedSubContractorColumns,
                editable: this.editable,
                costData: this.costData
            });
            this.addChild(columnsGrid);
        }
    });
});