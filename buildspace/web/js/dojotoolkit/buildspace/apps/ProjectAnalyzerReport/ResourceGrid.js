define('buildspace/apps/ProjectAnalyzerReport/ResourceGrid',[
    'dojo/_base/declare',
    'dojo/_base/connect',
    'dojo/_base/lang',
    'dojo/request',
    'dijit/form/Button',
    'dijit/form/DropDownButton',
    'dijit/DropDownMenu',
    'dijit/MenuItem',
    'dojo/store/Memory',
    'dojo/aspect',
    "buildspace/widget/grid/plugins/FormulatedColumn",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'buildspace/apps/ProjectAnalyzerReport/plugins/TotalPane',
    'buildspace/apps/ProjectAnalyzerReport/MultiRateAnalyzerDialog',
    './ResourcePrintPreviewDialog/PrintSelectedResourceItemGridDialog',
    './ResourcePrintPreviewDialog/PrintSelectedTradeItemBillItemGridDialog',
    'dojo/i18n!buildspace/nls/ProjectAnalyzer'
], function(declare, connect, lang, request, Button, DropDownButton, DropDownMenu, MenuItem, Memory, aspect, FormulatedColumn, IndirectSelection, TotalPane, MultiRateAnalyzerDialog, PrintSelectedResourceItemGridDialog, PrintSelectedTradeItemBillItemGridDialog, nls){

    var ResourceGrid = declare('buildspace.apps.ProjectAnalyzerReport.ResourceEnhancedGrid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        region: 'center',
        updateUrl: null,
        project: null,
        unsorted: false,
        trade: null,
        constructor: function(args) {
            if ( args.type !== 'resource' ) {
                this.plugins = {indirectSelection: {headerSelector:true, width:"40px", styles:"text-align: center;"}};
            }

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function() {
            this.formulatedColumn = FormulatedColumn(this,{});

            var self = this, store;
            self.inherited(arguments);

            if ( self.type === 'tradeItem' ) {
                store = self.gridContainer.tradeItemSelectBoxStore;
            } else if ( self.type === 'billItem' ) {
                store = self.gridContainer.tradeBillItemSelectBoxStore[self.trade.id[0]];
            }

            if ( store ) {
                aspect.after(self, "_onFetchComplete", function() {
                    self.gridContainer.markedCheckBoxObject(self, store);
                });
            }
        },
        startup: function() {
            this.inherited(arguments);
            var self = this;

            if ( self.type !== 'resource' ) {
                this._connects.push(connect.connect(this, 'onCellClick', function(e) {
                    if (e.cell.name !== "") {
                        return;
                    }

                    self.singleCheckBoxSelection(e);
                }));

                this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue) {
                    self.toggleAllSelection(newValue);
                }));
            }
        },
        isInteger: function(x) {
            return x % 1 === 0;
        }
    });

    return declare('buildspace.apps.ProjectAnalyzerReport.ResourceGrid', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        gridOpts: {},
        pageId: 0,
        type: null,
        postCreate: function() {
            var self = this;
            this.inherited(arguments);
            lang.mixin(self.gridOpts, {project: self.project });

            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important; border-bottom:none; padding:2px; width:100%;"});
            var grid    = this.grid = new ResourceGrid(self.gridOpts);

            if ( this.gridOpts.type === 'trade' ) {
                toolbar.addChild(
                    new Button({
                        label: nls.print + ' ' + nls.resourceItems,
                        iconClass: "icon-16-container icon-16-print",
                        onClick: function() {
                            self.openSelectedSoRItemPrintPreviewDialog();
                        }
                    })
                );

                this.addChild(toolbar);
            } else if ( this.gridOpts.type === 'tradeItem' ) {
                var menu = new DropDownMenu({ style: "display: none;"});

                menu.addChild(new MenuItem({
                    label: nls.resourceItems,
                    onClick: function() {
                        self.openSelectedSoRItemPrintPreviewDialog();
                    }
                }));

                menu.addChild(new MenuItem({
                    label: nls.resourceWithBillItems,
                    onClick: function() {
                        self.openSelectedBillItemPrintPreviewDialog();
                    }
                }));

                toolbar.addChild(
                    new DropDownButton({
                        label: nls.print,
                        iconClass: "icon-16-container icon-16-print",
                        dropDown: menu
                    })
                );

                this.addChild(toolbar);
            } else if ( this.gridOpts.type === 'billItem' ) {
                toolbar.addChild(
                    new Button({
                        label: nls.resourceWithBillItems,
                        iconClass: "icon-16-container icon-16-print",
                        onClick: function() {
                            self.openSelectedBillItemPrintPreviewDialog();
                        }
                    })
                );

                this.addChild(toolbar);
            }

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('projectAnalyzerReport-resource_project_'+self.project.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                lang.mixin(child, {grid: grid});
                container.selectChild(self.pageId);
            }
        },
        openSelectedSoRItemPrintPreviewDialog: function() {
            var self = this,
                tradeItemStore = self.gridOpts.gridContainer.tradeItemSelectBoxStore,
                tradeItems = [];

            tradeItemStore.query().forEach(function(item) {
                tradeItems.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('resourceProjectAnalyzerReport/getPrintingSelectedTradeItems', {
                handleAs: 'json',
                data: {
                    pid: self.project.id,
                    trade_item_ids: JSON.stringify(self.arrayUnique(tradeItems)),
                    resourceId: self.resource.id,
                    type: self.type
                }
            }).then(function(data) {
                var dialog = new PrintSelectedResourceItemGridDialog({
                    project: self.project,
                    resourceId: self.resource.id,
                    projectId: self.project.id,
                    title: nls.resourceItems,
                    data: data,
                    selectedItems: tradeItems,
                    type: self.type
                });

                dialog.show();

                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openSelectedBillItemPrintPreviewDialog: function() {
            if ( ! this.gridOpts.gridContainer.tradeBillItemSelectBoxStore[this.gridOpts.trade.id[0]] ) {
                this.gridOpts.gridContainer.tradeBillItemSelectBoxStore[this.gridOpts.trade.id[0]] = new Memory({ idProperty: 'id' });
            }

            var self = this,
                tradeItemSelectBoxStore = self.gridOpts.gridContainer.tradeItemSelectBoxStore,
                tradeBillItemStore = self.gridOpts.gridContainer.tradeBillItemSelectBoxStore[self.gridOpts.trade.id[0]],
                tradeItemIds = [],
                billItems = [];

            tradeItemSelectBoxStore.query().forEach(function(item) {
                tradeItemIds.push(item.id);
            });

            tradeBillItemStore.query().forEach(function(item) {
                billItems.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('resourceProjectAnalyzerReport/getPrintingSelectedBillItems', {
                handleAs: 'json',
                data: {
                    pid: self.project.id,
                    resource_id: self.resource.id,
                    trade_id: self.gridOpts.trade.id,
                    tradeItemIds: JSON.stringify(self.arrayUnique(tradeItemIds)),
                    bill_item_ids: JSON.stringify(self.arrayUnique(billItems))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedTradeItemBillItemGridDialog({
                    project: self.project,
                    title: nls.resourceWithBillItems + ' (' + self.gridOpts.trade.description + ')',
                    data: data,
                    resourceId: self.resource.id,
                    tradeId: self.gridOpts.trade.id,
                    projectId: self.project.id,
                    tradeItemIds: tradeItemIds,
                    selectedItems: billItems,
                    contractorList: self.contractorList
                });

                dialog.show();

                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        arrayUnique: function(array) {
            return array.reverse().filter(function (e, i, arr) {
                return arr.indexOf(e, i+1) === -1;
            }).reverse();
        }
    });
});