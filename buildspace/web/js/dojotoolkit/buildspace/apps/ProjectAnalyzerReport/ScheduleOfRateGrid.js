define('buildspace/apps/ProjectAnalyzerReport/ScheduleOfRateGrid',[
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
    'dojo/json',
    "buildspace/widget/grid/plugins/FormulatedColumn",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    './ScheduleOfRatePrintPreviewDialog/PrintSelectedScheduleOfRateItemGridDialog',
    './ScheduleOfRatePrintPreviewDialog/PrintSelectedTradeItemBillItemGridDialog',
    './ScheduleOfRatePrintPreviewDialog/PrintPreviewFormDialog',
    'dojo/i18n!buildspace/nls/ProjectAnalyzer'
], function(declare, connect, lang, request, Button, DropDownButton, DropDownMenu, MenuItem, Memory, aspect, JSON, FormulatedColumn, IndirectSelection, PrintSelectedScheduleOfRateItemGridDialog, PrintSelectedTradeItemBillItemGridDialog, PrintPreviewFormDialog, nls) {

    var ScheduleOfRateGrid = declare('buildspace.apps.ProjectAnalyzerReport.ScheduleOfRateEnhancedGrid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        region: 'center',
        tradeId: -1,
        updateUrl: null,
        project: null,
        unsorted: false,
        gridContainer: null,
        constructor: function(args) {
            if ( args.type === 'trade' || args.type === 'tradeItem' || args.type === 'billItem' ) {
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

            if ( self.type === 'trade' || self.type === 'tradeItem' || self.type === 'billItem' ) {
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
        onStyleRow: function(e) {
            this.inherited(arguments);
            if(e.node.children[0].children[0].rows.length >= 2){
                dojo.style(e.node.children[0].children[0].rows[1],'display','none');
            }
        }
    });

    return declare('buildspace.apps.ProjectAnalyzerReport.ScheduleOfRateGrid', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        gridOpts: {},
        pageId: 0,
        type: null,
        scheduleOfRate: null,
        contractorList: [],
        postCreate: function() {
            var self = this;
            this.inherited(arguments);
            lang.mixin(self.gridOpts, {project: self.project, type: this.type });
            var menu;

            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important; border-bottom:none; padding:2px; width:100%;"});
            var grid = this.grid = new ScheduleOfRateGrid(self.gridOpts);

            if ( this.type === 'trade' ) {

                menu = new DropDownMenu({ style: "display: none;"});

                menu.addChild(new MenuItem({
                    label: nls.sorItems,
                    iconClass: "icon-16-container icon-16-print",
                    onClick: function() {
                        self.openSelectedSoRItemPrintPreviewDialog();
                    }
                }));

                menu.addChild(new MenuItem({
                    label: nls.sorItemsWithSelectedTendererRates,
                    iconClass: "icon-16-container icon-16-print",
                    disabled: (self.contractorList && self.contractorList.items.length > 0 && self.contractorList.items[0]['awarded']) ? false : true,
                    onClick: function() {
                        self.openSelectedSoRItemBySelectedTendererPrintPreviewDialog();
                    }
                }));

                menu.addChild(new MenuItem({
                    label: nls.scheduleOfRateItemsCostAnalysis,
                    iconClass: "icon-16-container icon-16-print",
                    onClick: function() {
                        self.printSelectedSoRItemCostAnalysis();
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
            } else if ( this.type === 'tradeItem' ) {
                menu = new DropDownMenu({ style: "display: none;"});

                menu.addChild(new MenuItem({
                    label: nls.sorItems,
                    onClick: function() {
                        self.openSelectedSoRItemPrintPreviewDialog();
                    }
                }));

                menu.addChild(new MenuItem({
                    label: nls.sorWithBillItems,
                    onClick: function() {
                        self.openSelectedBillItemPrintPreviewDialog();
                    }
                }));

                menu.addChild(new MenuItem({
                    label: nls.sorWithBillItemsWithSelectedTendererRates,
                    disabled: (self.contractorList && self.contractorList.items.length > 0 && self.contractorList.items[0]['awarded']) ? false : true,
                    onClick: function() {
                        self.openSelectedBillItemWithTendererRatesPrintPreviewDialog();
                    }
                }));

                menu.addChild(new MenuItem({
                    label: nls.billItemsCostAnalysis,
                    onClick: function() {
                        self.printBillItemCostAnalysis();
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
            } else if ( this.type === 'billItem' ) {
                menu = new DropDownMenu({ style: "display: none;"});

                menu.addChild(new MenuItem({
                    label: nls.sorWithBillItems,
                    onClick: function() {
                        self.openSelectedBillItemPrintPreviewDialog();
                    }
                }));

                menu.addChild(new MenuItem({
                    label: nls.sorWithBillItemsWithSelectedTendererRates,
                    disabled: (self.contractorList && self.contractorList.items.length > 0 && self.contractorList.items[0]['awarded']) ? false : true,
                    onClick: function() {
                        self.openSelectedBillItemWithTendererRatesPrintPreviewDialog();
                    }
                }));

                menu.addChild(new MenuItem({
                    label: nls.billItemsCostAnalysis,
                    onClick: function() {
                        self.printBillItemCostAnalysis();
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
            }

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('projectAnalyzer-sor_project_'+self.project.id+'-stackContainer');
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
                tradeItems = [];

            tradeItems = self.getTradeItemIds();

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                request.post('scheduleOfRateProjectAnalyzerReport/getPrintingSelectedTradeItems', {
                    handleAs: 'json',
                    data: {
                        pid: self.project.id,
                        trade_item_ids: JSON.stringify(self.arrayUnique(tradeItems)),
                        scheduleOfRateId: self.scheduleOfRate.id
                    }
                }).then(function(data) {
                    var dialog = new PrintSelectedScheduleOfRateItemGridDialog({
                        project: self.project,
                        title: nls.sorItems,
                        data: data,
                        selectedItems: tradeItems,
                        projectId: self.project.id,
                        scheduleOfRateId: self.scheduleOfRate.id,
                        printURL: 'printReport/printSelectedScheduleOfRateTradeItems',
                        exportURL: 'exportExcelReport/exportSelectedScheduleOfRateTradeItems'
                    });

                    dialog.show();

                    pb.hide();
                }, function(error) {
                    console.log(error);
                    pb.hide();
                });
            });
        },
        openSelectedSoRItemBySelectedTendererPrintPreviewDialog: function() {
            var self = this,
                tradeItemStore = self.gridOpts.gridContainer.tradeItemSelectBoxStore,
                tradeItems = [];

            tradeItemStore.query().forEach(function(item) {
                tradeItems.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                request.post('scheduleOfRateProjectAnalyzerReport/getPrintingSelectedTradeItemsWithSelectedTenderer', {
                    handleAs: 'json',
                    data: {
                        pid: self.project.id,
                        trade_item_ids: JSON.stringify(self.arrayUnique(tradeItems)),
                        scheduleOfRateId: self.scheduleOfRate.id
                    }
                }).then(function(data) {
                    var dialog = new PrintSelectedScheduleOfRateItemGridDialog({
                        project: self.project,
                        title: nls.sorItemsWithSelectedTendererRates,
                        data: data,
                        selectedItems: tradeItems,
                        projectId: self.project.id,
                        scheduleOfRateId: self.scheduleOfRate.id,
                        printURL: 'printReport/printSelectedScheduleOfRateTradeItemsWithSelectedTendererRates',
                        exportURL: 'exportExcelReport/exportSelectedScheduleOfRateTradeItemsWithSelectedTendererRates'
                    });

                    dialog.show();

                    pb.hide();
                }, function(error) {
                    console.log(error);
                    pb.hide();
                });
            });
        },
        printSelectedSoRItemCostAnalysis: function() {
            var dialog = new PrintPreviewFormDialog({
                title: nls.scheduleOfRateItemsCostAnalysis,
                selectedRows: this.gridOpts.gridContainer.getTradeIds(),
                printURL: null,
                exportURL: 'exportExcelReport/exportSelectedScheduleOfRateTradeItemsCostAnalysis',
                projectId: this.project.id[0],
                scheduleOfRateId: this.scheduleOfRate.id[0],
                _csrf_token: this.gridOpts._csrf_token
            });
            dialog.show();
        },
        openSelectedBillItemPrintPreviewDialog: function() {
            if ( ! this.gridOpts.gridContainer.tradeBillItemSelectBoxStore[this.gridOpts.trade.id[0]] ) {
                this.gridOpts.gridContainer.tradeBillItemSelectBoxStore[this.gridOpts.trade.id[0]] = new Memory({ idProperty: 'id' });
            }

            var self = this,
                tradeItemStore = self.gridOpts.gridContainer.tradeItemSelectBoxStore,
                tradeBillItemStore = self.gridOpts.gridContainer.tradeBillItemSelectBoxStore[self.gridOpts.trade.id[0]],
                tradeItemIds = [],
                billItems = [];

            tradeItemStore.query().forEach(function(item) {
                tradeItemIds.push(item.id);
            });

            tradeBillItemStore.query().forEach(function(item) {
                billItems.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                request.post('scheduleOfRateProjectAnalyzerReport/getPrintingSelectedBillItems', {
                    handleAs: 'json',
                    data: {
                        pid: self.project.id,
                        scheduleOfRateId: self.scheduleOfRate.id,
                        scheduleOfRateTradeId: self.gridOpts.trade.id,
                        tradeItemIds: JSON.stringify(self.arrayUnique(tradeItemIds)),
                        bill_item_ids: JSON.stringify(self.arrayUnique(billItems))
                    }
                }).then(function(data) {
                    var dialog = new PrintSelectedTradeItemBillItemGridDialog({
                        project: self.project,
                        title: nls.sorItems + ' (' + self.gridOpts.trade.description + ')',
                        data: data,
                        tradeItemIds: tradeItemIds,
                        selectedItems: billItems,
                        projectId: self.project.id,
                        scheduleOfRateId: self.scheduleOfRate.id,
                        tradeId: self.gridOpts.trade.id,
                        contractorList: self.contractorList,
                        printURL: 'printReport/printSelectedScheduleOfRateTradeBillItem',
                        exportURL: 'exportExcelReport/exportSelectedScheduleOfRateTradeBillItem'
                    });

                    dialog.show();

                    pb.hide();
                }, function(error) {
                    console.log(error);
                    pb.hide();
                });
            });
        },
        openSelectedBillItemWithTendererRatesPrintPreviewDialog: function() {
            if ( ! this.gridOpts.gridContainer.tradeBillItemSelectBoxStore[this.gridOpts.trade.id[0]] ) {
                this.gridOpts.gridContainer.tradeBillItemSelectBoxStore[this.gridOpts.trade.id[0]] = new Memory({ idProperty: 'id' });
            }

            var self = this,
                tradeItemStore = self.gridOpts.gridContainer.tradeItemSelectBoxStore,
                tradeBillItemStore = self.gridOpts.gridContainer.tradeBillItemSelectBoxStore[self.gridOpts.trade.id[0]],
                tradeItemIds = [],
                billItems = [];

            tradeItemStore.query().forEach(function(item) {
                tradeItemIds.push(item.id);
            });

            tradeBillItemStore.query().forEach(function(item) {
                billItems.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                request.post('scheduleOfRateProjectAnalyzerReport/getPrintingSelectedBillItemsWithSelectedTendererRates', {
                    handleAs: 'json',
                    data: {
                        pid: self.project.id,
                        scheduleOfRateId: self.scheduleOfRate.id,
                        scheduleOfRateTradeId: self.gridOpts.trade.id,
                        tradeItemIds: JSON.stringify(self.arrayUnique(tradeItemIds)),
                        bill_item_ids: JSON.stringify(self.arrayUnique(billItems))
                    }
                }).then(function(data) {
                    var dialog = new PrintSelectedTradeItemBillItemGridDialog({
                        project: self.project,
                        title: nls.sorWithBillItemsWithSelectedTendererRates + ' (' + self.gridOpts.trade.description + ')',
                        data: data,
                        tradeItemIds: tradeItemIds,
                        selectedItems: billItems,
                        projectId: self.project.id,
                        scheduleOfRateId: self.scheduleOfRate.id,
                        tradeId: self.gridOpts.trade.id,
                        printURL: 'printReport/printSelectedScheduleOfRateTradeBillItemWithSelectedTendererRates',
                        exportURL: 'exportExcelReport/exportSelectedScheduleOfRateTradeBillItemWithSelectedTendererRates'
                    });

                    dialog.show();

                    pb.hide();
                }, function(error) {
                    console.log(error);
                    pb.hide();
                });
            });
        },
        arrayUnique: function(array) {
            return array.reverse().filter(function (e, i, arr) {
                return arr.indexOf(e, i+1) === -1;
            }).reverse();
        },
        getTradeItemIds: function(){
            var tradeItemStore = this.gridOpts.gridContainer.tradeItemSelectBoxStore,
                tradeItemIds = [];

            tradeItemStore.query().forEach(function(item) {
                tradeItemIds.push(item.id);
            });

            return tradeItemIds;
        },
        getBillItemIds: function(){
            if ( ! this.gridOpts.gridContainer.tradeBillItemSelectBoxStore[this.gridOpts.trade.id[0]] ) {
                this.gridOpts.gridContainer.tradeBillItemSelectBoxStore[this.gridOpts.trade.id[0]] = new Memory({ idProperty: 'id' });
            }
            var tradeBillItemStore = this.gridOpts.gridContainer.tradeBillItemSelectBoxStore[this.gridOpts.trade.id[0]],
                billItems = [];

            tradeBillItemStore.query().forEach(function(item) {
                billItems.push(item.id);
            });

            return billItems;
        },
        printBillItemCostAnalysis: function()
        {
            var dialog = new PrintPreviewFormDialog({
                title: nls.billItemsCostAnalysis,
                selectedRows: this.getBillItemIds(),
                tradeItemIds: this.getTradeItemIds(),
                printURL: null,
                exportURL: 'exportExcelReport/exportSelectedScheduleOfRateTradeBillItemCostAnalysis',
                projectId: this.project.id[0],
                scheduleOfRateId: this.scheduleOfRate.id[0],
                tradeId: this.gridOpts.trade.id[0],
                _csrf_token: this.gridOpts.trade._csrf_token[0]
            });
            dialog.show();
        }
    });
});