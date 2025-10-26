define('buildspace/apps/ProjectBuilderReport/BillManager/buildUpGrid',[
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
    'dijit/popup',
    'dijit/TooltipDialog',
    'dojo/i18n!buildspace/nls/BuildUpGrid'
], function(declare, lang, array, domAttr, Menu, Selector, Rearrange, FormulatedColumn, evt, keys, currency, focusUtil, html, xhr, PopupMenuItem, Select, Textarea, FormulaTextBox, popup, TooltipDialog, nls ){

    var BuildUpGrid = declare('buildspace.apps.ProjectBuilderReport.BillManager.BuildUp.grid', dojox.grid.EnhancedGrid, {
        itemId: -1,
        resource: null,
        BQItem: null,
        style: "border-top:none;",
        selectedItem: null,
        unitObj: null,
        keepSelection: true,
        rowSelector: '0px',
        addUrl: null,
        updateUrl: null,
        deleteUrl: null,
        pasteUrl: null,
        buildUpSummaryWidget: null,
        currentBillLockedStatus: false,
        disableEditingMode: false,
        constructor:function(args){
            this.rearranger       = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});
            this.currencySetting  = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            if (this.currentBillLockedStatus) {
                return false;
            }

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        onCellFocus: function(inCell, inRowIndex) {
            var self = this, item = self.getItem(inRowIndex), fieldName = inCell.field;

            if ( fieldName !== 'description' || item.id[0] < 0 || ! item.linked[0] ) {
                self.closeToolTipDialogIfAvailable();
                return;
            }

            // will call the api to get current's item parent root information
            var xhrArgs = {
                url: 'resourceLibrary/getBuildUpRatesToolTipInformation',
                content: {
                    id: item.id,
                    type: 'bill'
                },
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success) {
                        self.closeToolTipDialogIfAvailable();

                        var needTable = false,
                            toolTipContent = '<h1 style="font-size: 12px; text-shadow: 1px 1px 1px #000;background-color: rgba(35, 35, 35, .85); color: #fff; padding: 4px 10px;">'+resp.items[0].description+'</h1>';

                        if (Object.keys(resp.items).length > 1) {
                            needTable = true;

                            toolTipContent += '<table style="width: 100%; border-collapse: collapse;">';
                        }

                        dojo.forEach(resp.items, function(item, i){
                            if (i !== 0) {
                                var paddingLeftAmt = item.level * 16;

                                toolTipContent += '<tr><td style="border: 1px dotted #D5CDB5; font-weight: bold; font-size: 11px; padding-left:'+paddingLeftAmt+'px;">'+item.description+'</td></td>';
                            }
                        });

                        if (needTable) {
                            toolTipContent += '</table>';
                        }

                        // create a new tooltip based on the information returned from the api
                        self.myTooltipDialog = new TooltipDialog({
                            id: 'buildUpRatesTooltipDialog-'+self.itemId[0],
                            style: "width: 300px;",
                            content: toolTipContent
                        });

                        popup.open({
                            popup: self.myTooltipDialog,
                            around: inCell.view.rowNodes[inRowIndex],
                            orient: ['below']
                        });
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            };

            dojo.xhrGet(xhrArgs);
        },
        onRowMouseOut: function() {
            this.closeToolTipDialogIfAvailable();
        },
        updateTotalBuildUp: function(totalBuildUp){
            var _this = this,
                accContainer = dijit.byId('accPane-'+_this.resource.id+'-'+_this.BQItem.id);
            accContainer.set('title', this.resource.name+'<span style="color:blue;float:right;">'+this.currencySetting+'&nbsp;'+currency.format(totalBuildUp)+'</span>');
            this.buildUpSummaryWidget.refreshTotalCost();
        },
        closeToolTipDialogIfAvailable: function() {
            var myTooltipDialogContainer = dijit.byId('buildUpRatesTooltipDialog-'+this.itemId[0]);

            // close the opened dialog box, if any
            if (this.myTooltipDialog !== undefined && myTooltipDialogContainer !== undefined) {
                popup.close();

                myTooltipDialogContainer.destroy();
            }
        }
    });

    var BuildUpGridContainer = declare('buildspace.apps.ProjectBuilderReport.BillManager.BuildUpGrid', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        resource: null,
        BQItem: null,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { resource: self.resource, BQItem: self.BQItem, region:"center" });
            var grid = this.grid = new BuildUpGrid(self.gridOpts);

            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });

    return BuildUpGridContainer;
});