define('buildspace/apps/PostContract/BillManager/buildUpQuantityGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    'dojo/_base/event',
    'dojo/keys',
    'dojo/currency',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    'buildspace/widget/grid/cells/Select',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    'dojo/i18n!buildspace/nls/BuildUpQuantityGrid'], function(declare, lang, array, domAttr, evt, keys, currency, focusUtil, html, xhr, PopupMenuItem, Select, Textarea, FormulaTextBox, nls ){

    var BuildUpQuantityGrid = declare('buildspace.apps.PostContract.BillManager.BuildUpQuantity.grid', dojox.grid.EnhancedGrid, {
        billColumnSettingId: null,
        BillItem: null,
        style: "border-top:none;",
        selectedItem: null,
        keepSelection: true,
        buildUpSummaryWidget: null,
        type: null,
        canSort: function(inSortInfo){
            return false;
        },
        canEdit: function(inCell, inRowIndex) {
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
        },
        dodblclick: function(e){
            //this.onRowDblClick(e);
        }
    });

    return declare('buildspace.apps.PostContract.BillManager.BuildUpQuantityGrid', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        billColumnSettingId: null,
        type: null,
        BillItem: null,
        gridOpts: {},
        postCreate: function(){
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                billColumnSettingId: this.billColumnSettingId,
                BillItem: this.BillItem,
                region:"center",
                type: this.type
            });

            var grid = this.grid = new BuildUpQuantityGrid(this.gridOpts);

            this.addChild(grid);
        }
    });
});