define('buildspace/apps/PostContractRemeasurementReport/buildUpGrid', [
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojo/_base/connect",
    "dijit/TooltipDialog",
    "dijit/popup",
    "dojox/grid/enhanced/plugins/Menu",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    'dojo/currency',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    'dojo/i18n!buildspace/nls/BuildUpGrid'
], function (declare, lang, array, domAttr, connect, TooltipDialog, popup, Menu, FormulatedColumn, evt, keys, currency, focusUtil, html, xhr, Textarea, FormulaTextBox, nls) {

    return declare('buildspace.apps.PostContractRemeasurementReport.BuildUpGrid', dojox.grid.EnhancedGrid, {
        resource: null,
        billItem: null,
        style: "border-top:none;",
        selectedItem: null,
        keepSelection: true,
        rowSelector: '0px',
        updateUrl: null,
        pasteUrl: null,
        buildUpSummaryWidget: null,
        constructor: function (args) {
            this.formulatedColumn = FormulatedColumn(this, {});
            this.currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;
        },
        canSort: function (inSortInfo) {
            return false;
        },
        postCreate: function () {
            var self = this;
            var tooltipDialog = null;

            self.inherited(arguments);

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
        },
        updateTotalBuildUp: function (totalBuildUp) {
            var accContainer = dijit.byId('accPane-' + this.resource.id + '-' + this.billItem.id);
            accContainer.set('title', this.resource.name + '<span style="color:blue;float:right;">' + this.currencySetting + '&nbsp;' + currency.format(totalBuildUp) + '</span>');
            this.buildUpSummaryWidget.refreshTotalCost();
        },
        destroy: function () {
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });
});