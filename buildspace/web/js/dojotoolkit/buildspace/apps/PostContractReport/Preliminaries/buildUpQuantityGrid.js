define('buildspace/apps/PostContractReport/Preliminaries/buildUpQuantityGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
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
    'dojo/i18n!buildspace/nls/BuildUpQuantityGrid'], function(declare, lang, array, domAttr, Menu, Selector, Rearrange, FormulatedColumn, evt, keys, currency, focusUtil, html, xhr, PopupMenuItem, Select, Textarea, FormulaTextBox, nls ){

    var BuildUpQuantityGrid = declare('buildspace.apps.PostContractReport.BuildUpQuantity.grid', dojox.grid.EnhancedGrid, {
        billColumnSettingId: null,
        BillItem: null,
        style: "border-top:none;",
        selectedItem: null,
        keepSelection: true,
        buildUpSummaryWidget: null,
        type: null,
        editable: false,
        disableEditingMode: false,
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});

            if (! args.editable) {
                this.disableEditingMode = true;
            }
        },
        canSort: function(inSortInfo){
            return false;
        },
        canEdit: function(inCell, inRowIndex) {
            if (this.disableEditingMode) {
                return false;
            }

            return this._canEdit;
        },
        dodblclick: function(e){
            //this.onRowDblClick(e);
        },
        updateTotalBuildUp: function(){
            this.buildUpSummaryWidget.refreshTotalQuantity();
        }
    });

    return declare('buildspace.apps.PostContractReport.BuildUpQuantityGrid', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        billColumnSettingId: null,
        type: null,
        BillItem: null,
        gridOpts: {},
        editable: false,
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            lang.mixin(self.gridOpts, { billColumnSettingId: self.billColumnSettingId, BillItem: self.BillItem, region:"center", type: self.type, editable: self.editable });
            var grid = this.grid = new BuildUpQuantityGrid(self.gridOpts);

            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });
});