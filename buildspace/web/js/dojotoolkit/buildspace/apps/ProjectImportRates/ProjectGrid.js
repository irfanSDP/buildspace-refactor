define('buildspace/apps/ProjectImportRates/ProjectGrid',[
    'dojo/_base/declare',
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/ProjectImportRates'
], function(declare, IndirectSelection, nls){

    return declare('buildspace.apps.ProjectImportRates.ProjectGrid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        element: null,
        region: 'center',
        constructor: function(args){
            if(args.type == 'tree'){
                this.escapeHTMLInData = false;
                this.id = "project_bill_item-grid";
                this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align:center;"}}
            }
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        }
    });
});