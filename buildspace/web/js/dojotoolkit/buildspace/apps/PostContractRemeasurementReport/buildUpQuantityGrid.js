define('buildspace/apps/PostContractRemeasurementReport/buildUpQuantityGrid', [
        'dojo/_base/declare',
        'dojo/_base/lang',
        "dojo/_base/array",
        "dojo/dom-attr",
        "dojo/_base/connect",
        "dijit/TooltipDialog",
        "dijit/popup",
        "dojox/grid/enhanced/plugins/Menu",
        "dojox/grid/enhanced/plugins/Selector",
        "dojox/grid/enhanced/plugins/Rearrange",
        "buildspace/widget/grid/plugins/FormulatedColumn"],
    function (declare, lang, array, domAttr, connect, TooltipDialog, popup, Menu, Selector, Rearrange, FormulatedColumn) {

        var BuildUpQuantityGrid = declare('buildspace.apps.PostContractRemeasurementReport.BuildUpQuantity.grid', dojox.grid.EnhancedGrid, {
            BillItem: null,
            billColumnSettingId: null,
            style: "border-top:none;",
            selectedItem: null,
            keepSelection: true,
            buildUpSummaryWidget: null,
            type: null,
            locked: false,
            disableEditingMode: false,
            constructor: function (args) {
                this.rearranger = Rearrange(this, {});
                this.formulatedColumn = FormulatedColumn(this, {});
                this.disableEditingMode = true;
            },
            canSort: function (inSortInfo) {
                return false;
            },
            postCreate: function () {
                var tooltipDialog = null;

                this.inherited(arguments);

                this._connects.push(connect.connect(this, 'onCellMouseOver', function (e) {
                    var colField = e.cell.field,
                        rowIndex = e.rowIndex,
                        item = this.getItem(rowIndex);

                    var fieldConstantName = colField.replace("-value", "");

                    // will show tooltip for formula, if available
                    if (typeof item[fieldConstantName + '-has_formula'] === 'undefined' || !item[fieldConstantName + '-has_formula'][0]) {
                        return;
                    }

                    var formulaValue = item[fieldConstantName + '-value'][0];

                    // convert ITEM ID into ROW ID (if available)
                    formulaValue = this.formulatedColumn.convertItemIdToRowIndex(formulaValue, rowIndex);

                    if (tooltipDialog === null) {
                        tooltipDialog = new TooltipDialog({
                            content: formulaValue,
                            onMouseLeave: function () {
                                popup.close(tooltipDialog);
                            }
                        });

                        popup.open({
                            popup: tooltipDialog,
                            around: e.cellNode
                        });
                    }
                }));

                this._connects.push(connect.connect(this, 'onCellMouseOut', function () {
                    if (tooltipDialog !== null) {
                        popup.close(tooltipDialog);
                        tooltipDialog = null;
                    }
                }));

                this._connects.push(connect.connect(this, 'onStartEdit', function () {
                    if (tooltipDialog !== null) {
                        popup.close(tooltipDialog);
                        tooltipDialog = null;
                    }
                }));
            }
        });

        return declare('buildspace.apps.PostContractRemeasurement.BuildUpQuantityGrid', dijit.layout.BorderContainer, {
            style: "padding:0px;width:100%;height:100%;border:0px;",
            gutters: false,
            type: null,
            BillItem: null,
            locked: false,
            gridOpts: {},
            postCreate: function () {
                this.inherited(arguments);

                lang.mixin(this.gridOpts, {
                    billColumnSettingId: this.billColumnSettingId,
                    BillItem: this.BillItem,
                    region: "center",
                    type: this.type,
                    locked: this.locked
                });

                var grid = this.grid = new BuildUpQuantityGrid(this.gridOpts);

                this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content: grid, region: 'center'}));
            }
        });
    });