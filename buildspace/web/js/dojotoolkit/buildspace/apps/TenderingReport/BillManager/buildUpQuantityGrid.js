define('buildspace/apps/TenderingReport/BillManager/buildUpQuantityGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    'dojo/_base/connect',
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dijit/popup',
    'dijit/TooltipDialog'],
    function(declare, lang, array, domAttr, connect, Menu, Selector, Rearrange, FormulatedColumn, popup, TooltipDialog) {

    var BuildUpQuantityGrid = declare('buildspace.apps.TenderingReport.BillManager.BuildUpQuantity.grid', dojox.grid.EnhancedGrid, {
        billColumnSettingId: null,
        BillItem: null,
        style: "border:none!important;",
        selectedItem: null,
        keepSelection: true,
        buildUpSummaryWidget: null,
        type: null,
        disableEditingMode: false,
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});
        },
        postCreate: function(){
            var tooltipDialog = null;

            this.inherited(arguments);

            this._connects.push(connect.connect(this, 'onCellMouseOver', function(e) {
                var colField = e.cell.field,
                    rowIndex = e.rowIndex,
                    item = this.getItem(rowIndex);

                var fieldConstantName = colField.replace("-value", "");

                // will show tooltip for formula, if available
                if (typeof item[fieldConstantName+'-has_formula'] === 'undefined' || ! item[fieldConstantName+'-has_formula'][0] ) {
                    return;
                }

                var formulaValue = item[fieldConstantName+'-value'][0];

                // convert ITEM ID into ROW ID (if available)
                formulaValue = this.formulatedColumn.convertItemIdToRowIndex(formulaValue, rowIndex);

                if(tooltipDialog === null) {
                    tooltipDialog = new TooltipDialog({
                        content: formulaValue,
                        onMouseLeave: function() {
                            popup.close(tooltipDialog);
                        }
                    });

                    popup.open({
                        popup: tooltipDialog,
                        around: e.cellNode
                    });
                }
            }));

            this._connects.push(connect.connect(this, 'onCellMouseOut', function() {
                if(tooltipDialog !== null){
                    popup.close(tooltipDialog);
                    tooltipDialog = null;
                }
            }));

            this._connects.push(connect.connect(this, 'onStartEdit', function() {
                if(tooltipDialog !== null){
                    popup.close(tooltipDialog);
                    tooltipDialog = null;
                }
            }));
        },
        canSort: function(inSortInfo){
            return false;
        },
        updateTotalBuildUp: function(){
            this.buildUpSummaryWidget.refreshTotalQuantity();
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.TenderingReport.BillManager.BuildUpQuantityGrid', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;width:100%;height:100%;border:none!important;",
        gutters: false,
        billColumnSettingId: null,
        type: null,
        BillItem: null,
        disableEditingMode: false,
        gridOpts: {},
        postCreate: function(){
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                billColumnSettingId: this.billColumnSettingId,
                BillItem: this.BillItem,
                region:"center",
                type: this.type,
                disableEditingMode: this.disableEditingMode
            });

            var grid = this.grid = new BuildUpQuantityGrid(this.gridOpts);

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });
});