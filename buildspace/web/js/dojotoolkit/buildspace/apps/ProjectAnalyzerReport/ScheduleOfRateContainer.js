define('buildspace/apps/ProjectAnalyzerReport/ScheduleOfRateContainer',[
    'dojo/_base/declare',
    'dojo/_base/connect',
    'dojo/request',
    "dojo/aspect",
    "dojo/when",
    "dojo/currency",
    'dojo/store/Memory',
    './ScheduleOfRateGrid',
    './buildUpGrid',
    './buildUpRateSummary',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ProjectAnalyzer'],
    function(declare, connect, request, aspect, when, currency, Memory, ScheduleOfRateGrid, BuildUpGrid, BuildUpRateSummary, GridFormatter, nls) {

    return declare('buildspace.apps.ProjectAnalyzerReport.ScheduleOfRateContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
        gutters: false,
        project: null,
        contractorList: null,
        tradeSelectBoxStore: [],
        tradeItemSelectBoxStore: [],
        tradeBillItemSelectBoxStore: [],
        postCreate: function() {
            this.inherited(arguments);

            var self = this,
                formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "projectAnalyzer/getScheduleOfRates/pid/"+self.project.id,
                    clearOnClose: true
                }),
                grid = new ScheduleOfRateGrid({
                    id: 'report_sor_analysis_sor-'+self.project.id,
                    stackContainerTitle: nls.scheduleOfRates,
                    pageId: 'report_sor_analysis_sor-'+self.project.id,
                    project: self.project,
                    contractorList: self.contractorList,
                    gridOpts: {
                        escapeHTMLInData: false,
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.name, field: 'name', width:'auto', formatter: formatter.analyzerDescriptionCellFormatter },
                            {name: nls.totalCost, field: 'total_cost', width:'150px', styles:'text-align:right;', formatter: formatter.currencyCellFormatter}
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if((_item.id != 'unsorted' && _item.id > 0) && _item.name[0] !== null){
                                self.createTradeGrid(_item);
                            }else if(_item.id == 'unsorted'){
                                self.createBillElementGrid(_item);
                            }
                        }
                    }
                });

            var gridContainer = this.makeGridContainer(grid, nls.scheduleOfRates);
            this.addChild(gridContainer);
        },
        createTradeGrid: function(scheduleOfRate){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"projectAnalyzer/getScheduleOfRateTrades/pid/"+self.project.id+"/id/"+scheduleOfRate.id,
                    clearOnClose: true
                });

            self.tradeSelectBoxStore         = new Memory({ idProperty: 'id' });
            self.tradeItemSelectBoxStore     = new Memory({ idProperty: 'id' });
            self.tradeBillItemSelectBoxStore = [];

            var grid = new ScheduleOfRateGrid({
                id: 'report_sor_analysis_trade-'+self.project.id,
                stackContainerTitle: scheduleOfRate.name,
                pageId: 'report_sor_analysis_trade-'+self.project.id+'_'+scheduleOfRate.id,
                project: self.project,
                type: 'trade',
                scheduleOfRate: scheduleOfRate,
                contractorList: self.contractorList,
                gridOpts: {
                    escapeHTMLInData: false,
                    _csrf_token: scheduleOfRate._csrf_token[0],
                    gridContainer: self,
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.analyzerDescriptionCellFormatter },
                        {name: nls.totalCost, field: 'total_cost', width:'150px', styles:'text-align:right;', formatter: formatter.currencyCellFormatter}
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if((_item.id == 'unsorted' ||_item.id > 0) && _item.description[0] !== null){
                            self.createItemGrid(_item, scheduleOfRate);
                        }
                    },
                    singleCheckBoxSelection: function(e) {
                        var grid = this,
                            rowIndex = e.rowIndex,
                            checked = grid.selection.selected[rowIndex],
                            item = grid.getItem(rowIndex);

                        if ( checked ) {
                            self.tradeSelectBoxStore.put({ id: item.id[0] });

                            return grid.getAffectedTradeItemsAndBillItems(item, 'add');
                        } else {
                            return grid.getAffectedTradeItemsAndBillItems(item, 'remove');
                        }
                    },
                    toggleAllSelection: function(checked) {
                        var grid = this, selection = grid.selection;

                        if (checked) {
                            selection.selectRange(0, grid.rowCount-1);
                            grid.store.fetch({
                                onComplete: function (items) {
                                    dojo.forEach(items, function (item, index) {
                                        if(item.id > 0) {
                                            grid.gridContainer.tradeSelectBoxStore.put({ id: item.id[0] });
                                        }
                                    });
                                }
                            });

                            return grid.getAffectedTradeItemsAndBillItems(null, 'add');
                        } else {
                            selection.deselectAll();

                            return grid.getAffectedTradeItemsAndBillItems(null, 'remove');
                        }
                    },
                    getAffectedTradeItemsAndBillItems: function(trade, type) {
                        var grid = this,
                            selectedTradeStore = self.tradeSelectBoxStore,
                            trades = [];

                        var pb = buildspace.dialog.indeterminateProgressBar({
                            title: nls.pleaseWait+'...'
                        });

                        pb.show();

                        if ( trade ) {
                            trades.push(trade.id[0]);
                        } else {
                            selectedTradeStore.query().forEach(function(item) {
                                trades.push(item.id);
                            });

                            trades = trades.reverse().filter(function (e, i, arr) {
                                return arr.indexOf(e, i+1) === -1;
                            }).reverse();
                        }

                        var tradeIds = JSON.stringify(trades);

                        pb.show();

                        request.post('scheduleOfRateProjectAnalyzerReport/getAffectedSelectionTradeItemsAndBillItems', {
                            handleAs: 'json',
                            data: {
                                pid: self.project.id,
                                trade_ids: tradeIds
                            }
                        }).then(function(data) {
                            for (var tradeId in data) {
                                if ( ! self.tradeBillItemSelectBoxStore[tradeId] ) {
                                    self.tradeBillItemSelectBoxStore[tradeId] = new Memory({ idProperty: 'id' });
                                }
                            }

                            if ( type === 'add' ) {
                                for (var tradeId in data) {
                                    for (var tradeItemIdIndex in data[tradeId]) {
                                        self.tradeItemSelectBoxStore.put({ id: tradeItemIdIndex });

                                        for ( var billItemKey in data[tradeId][tradeItemIdIndex] ) {
                                            self.tradeBillItemSelectBoxStore[tradeId].put({ id: data[tradeId][tradeItemIdIndex][billItemKey] });
                                        }
                                    }
                                }
                            } else {
                                for (var tradeId in data) {
                                    self.tradeSelectBoxStore.remove(tradeId);

                                    for (var tradeItemIdIndex in data[tradeId]) {
                                        self.tradeItemSelectBoxStore.remove(tradeItemIdIndex);

                                        for ( var billItemKey in data[tradeId][tradeItemIdIndex] ) {
                                            self.tradeBillItemSelectBoxStore[tradeId].remove(data[tradeId][tradeItemIdIndex][billItemKey]);
                                        }
                                    }
                                }
                            }

                            pb.hide();
                        }, function(error) {
                            pb.hide();
                            console.log(error);
                        });
                    }
                }
            });
        },
        getTradeIds: function()
        {
            var trades = [];
            this.tradeSelectBoxStore.query().forEach(function(item) {
                trades.push(item.id);
            });
            return trades;
        },
        createItemGrid: function(trade, scheduleOfRate){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"projectAnalyzer/getScheduleOfRateItems/pid/"+self.project.id+"/sorid/"+scheduleOfRate.id+"/id/"+trade.id,
                    clearOnClose: true
                });

            var grid = ScheduleOfRateGrid({
                id: 'report_sor_analysis_item-'+self.project.id,
                stackContainerTitle: trade.description,
                pageId: 'report_sor_analysis_item-'+self.project.id+'_'+trade.id,
                project: self.project,
                type: 'tradeItem',
                scheduleOfRate: scheduleOfRate,
                contractorList: self.contractorList,
                gridOpts: {
                    escapeHTMLInData: false,
                    trade: trade,
                    gridContainer: self,
                    store: store,
                    unsorted: trade.id == 'unsorted' ? true : false,
                    updateUrl: 'projectAnalyzer/scheduleOfRateItemUpdate',
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter},
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter},
                        {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                        {name: nls.rate, field: 'rate', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableAnalyzerRateCellFormatter},
                        {name: nls.itemMarkup+" (%)", field: 'item_markup-value', width:'100px', styles:'text-align:right;', formatter: formatter.analyzerItemMarkupCellFormatter},
                        {name: nls.totalQty, field: 'total_qty', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                        {name: nls.totalCost, field: 'total_cost', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                    ],
                    onRowDblClick: function(e){
                        var cell = e.cell,
                            idx = e.rowIndex,
                            _item = this.getItem(idx);
                        if(_item.id > 0 && cell.field != 'item_markup-value'){
                            self.createBillItemGrid(_item, trade, scheduleOfRate);
                        }
                    },
                    singleCheckBoxSelection: function(e) {
                        var grid = this,
                            rowIndex = e.rowIndex,
                            checked = grid.selection.selected[rowIndex],
                            item = grid.getItem(rowIndex);

                        if ( checked ) {
                            self.tradeItemSelectBoxStore.put({ id: item.id[0] });

                            return grid.getAffectedTradeAndBillItems(item, 'add');
                        } else {
                            return grid.getAffectedTradeAndBillItems(item, 'remove');
                        }
                    },
                    toggleAllSelection: function(checked) {
                        var grid = this, selection = grid.selection;

                        if (checked) {
                            selection.selectRange(0, grid.rowCount-1);
                            grid.store.fetch({
                                onComplete: function (items) {
                                    dojo.forEach(items, function (item, index) {
                                        if(item.id > 0) {
                                            grid.gridContainer.tradeItemSelectBoxStore.put({ id: item.id[0] });
                                        }
                                    });
                                }
                            });

                            return grid.getAffectedTradeAndBillItems(null, 'add');
                        } else {
                            selection.deselectAll();

                            return grid.getAffectedTradeAndBillItems(null, 'remove');
                        }
                    },
                    getAffectedTradeAndBillItems: function(tradeItem, type) {
                        var grid = this,
                            selectedTradeItemStore = self.tradeItemSelectBoxStore,
                            tradeItems = [];

                        var pb = buildspace.dialog.indeterminateProgressBar({
                            title: nls.pleaseWait+'...'
                        });

                        pb.show();

                        if ( tradeItem ) {
                            tradeItems.push(tradeItem.id[0]);
                        } else {
                            selectedTradeItemStore.query().forEach(function(item) {
                                tradeItems.push(item.id);
                            });

                            tradeItems = tradeItems.reverse().filter(function (e, i, arr) {
                                return arr.indexOf(e, i+1) === -1;
                            }).reverse();
                        }

                        var tradeItemIds = JSON.stringify(tradeItems);

                        pb.show();

                        request.post('scheduleOfRateProjectAnalyzerReport/getAffectedSelectionTradeAndBillItems', {
                            handleAs: 'json',
                            data: {
                                pid: self.project.id,
                                trade_item_ids: tradeItemIds
                            }
                        }).then(function(data) {
                            var tradeGrid = dijit.byId('report_sor_analysis_trade-' + self.project.id);

                            for (var tradeId in data) {
                                if ( ! self.tradeBillItemSelectBoxStore[tradeId] ) {
                                    self.tradeBillItemSelectBoxStore[tradeId] = new Memory({ idProperty: 'id' });
                                }
                            }

                            if ( type === 'add' ) {
                                for (var tradeId in data) {
                                    tradeGrid.grid.store.fetchItemByIdentity({
                                        identity: tradeId,
                                        onItem: function(node) {
                                            if ( ! node ) {
                                                return;
                                            }

                                            self.tradeSelectBoxStore.put({ id: tradeId });

                                            return tradeGrid.grid.rowSelectCell.toggleRow(node._0, true);
                                        }
                                    });

                                    for (var tradeItemIdIndex in data[tradeId]) {
                                        self.tradeItemSelectBoxStore.put({ id: tradeItemIdIndex });

                                        for ( var billItemKey in data[tradeId][tradeItemIdIndex] ) {
                                            self.tradeBillItemSelectBoxStore[tradeId].put({ id: data[tradeId][tradeItemIdIndex][billItemKey] });
                                        }
                                    }
                                }
                            } else {
                                for (var tradeId in data) {
                                    for (var tradeItemIdIndex in data[tradeId]) {
                                        self.tradeItemSelectBoxStore.remove(tradeItemIdIndex);

                                        for ( var billItemKey in data[tradeId][tradeItemIdIndex] ) {
                                            self.tradeBillItemSelectBoxStore[tradeId].remove(data[tradeId][tradeItemIdIndex][billItemKey]);
                                        }
                                    }

                                    // remove checked bill selection if no element is selected
                                    tradeGrid.grid.store.fetchItemByIdentity({
                                        identity: tradeId,
                                        onItem: function(node) {
                                            if ( ! node ) {
                                                return;
                                            }

                                            if ( self.tradeItemSelectBoxStore.data.length === 0 ) {
                                                self.tradeSelectBoxStore.remove(tradeId);

                                                return tradeGrid.grid.rowSelectCell.toggleRow(node._0, false);
                                            }
                                        }
                                    });
                                }
                            }

                            pb.hide();
                        }, function(error) {
                            pb.hide();
                            console.log(error);
                        });
                    }
                }
            });
        },
        createBillElementGrid: function(){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                url:"projectAnalyzer/getSorBillElements/pid/"+self.project.id,
                clearOnClose: true
            });

            var grid = ScheduleOfRateGrid({
                id: 'report_sor_analysis_bill_element-'+self.project.id+'_unsorted_container',
                stackContainerTitle: 'UNSORTED',
                pageId: 'report_sor_analysis_bill_element-'+self.project.id+'_unsorted',
                project: self.project,
                gridOpts: {
                    escapeHTMLInData: false,
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.analysisTreeCellFormatter},
                        {name: nls.totalCost, field: 'total_cost', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] != null && _item.type[0] > 0){
                            self.createUnsortedBillItemGrid(_item);
                        }
                    }
                }
            });
        },
        createUnsortedBillItemGrid: function(billElement){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"projectAnalyzer/getUnsortedSorBillItems/eid/"+billElement.id,
                    clearOnClose: true
                });

            if(this.contractorList && this.contractorList.items.length > 0){
                var descWidth = this.contractorList.items.length > 1 ? '500px' : 'auto';
                var structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width: descWidth, formatter: formatter.analysisTreeCellFormatter},
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                    {name: nls.totalQty, field: 'grand_total_quantity', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                    {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.total, field: 'grand_total', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                    {name: nls.itemMarkup+" (%)", field: 'markup_percentage-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaPercentageCellFormatter}
                ];

                dojo.forEach(this.contractorList.items, function(contractor){
                    var subConName = contractor.awarded ? '<p style="color:#0000FF!important;">'+buildspace.truncateString(contractor.name, 45)+'</p>': buildspace.truncateString(contractor.name, 45);
                    structure.push({
                        name: subConName,
                        field: 'contractor_rate-'+contractor.id+'-value',
                        width:'120px',
                        styles:'text-align:right;',
                        formatter: formatter.formulaCurrencyCellFormatter
                    });
                });
            }else{
                var structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.analysisTreeCellFormatter},
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                    {name: nls.totalQty, field: 'grand_total_quantity', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                    {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.total, field: 'grand_total', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                    {name: nls.itemMarkup+" (%)", field: 'markup_percentage-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaPercentageCellFormatter}
                ];
            }

            var grid = ScheduleOfRateGrid({
                stackContainerTitle: billElement.description,
                pageId: 'report_sor_analysis_bill_item-'+self.project.id+'_'+billElement.id,
                project: self.project,
                type: 'item',
                gridOpts: {
                    escapeHTMLInData: false,
                    store: store,
                    updateUrl: 'projectAnalyzer/scheduleOfRateBillItemUpdate',
                    structure: structure,
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item['rate-has_build_up'][0] && e.cell.field == 'rate-value'){
                            self.createBuildUpContainer(_item, {id: -1});
                        }
                    }
                }
            });
        },
        createBillItemGrid: function(item, trade, scheduleOfRate){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"projectAnalyzer/getSorBillItems/pid/"+self.project.id+"/sid/"+scheduleOfRate.id+"/tid/"+trade.id+"/id/"+item.id,
                    clearOnClose: true
                });

            if(this.contractorList && this.contractorList.items.length > 0){
                var descWidth = this.contractorList.items.length > 1 ? '500px' : 'auto';
                var structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width: descWidth, formatter: formatter.analysisTreeCellFormatter},
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                    {name: nls.totalQty, field: 'grand_total_quantity', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                    {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.total, field: 'grand_total', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                    {name: nls.itemMarkup+" (%)", field: 'markup_percentage-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaPercentageCellFormatter}
                ];

                dojo.forEach(this.contractorList.items, function(contractor){
                    var subConName = contractor.awarded ? '<p style="color:#0000FF!important;">'+buildspace.truncateString(contractor.name, 45)+'</p>': buildspace.truncateString(contractor.name, 45);
                    structure.push({
                        name: subConName,
                        field: 'contractor_rate-'+contractor.id+'-value',
                        width:'120px',
                        styles:'text-align:right;',
                        formatter: formatter.formulaCurrencyCellFormatter
                    });
                });
            }else{
                var structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.analysisTreeCellFormatter},
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                    {name: nls.totalQty, field: 'grand_total_quantity', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                    {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.total, field: 'grand_total', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                    {name: nls.itemMarkup+" (%)", field: 'markup_percentage-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaPercentageCellFormatter}
                ];
            }

            var grid = ScheduleOfRateGrid({
                id: 'report_sor_analysis_bill_item-'+self.project.id,
                stackContainerTitle: scheduleOfRate.name,
                pageId: 'report_sor_analysis_bill_item-'+self.project.id+'_'+scheduleOfRate.id,
                project: self.project,
                type: 'billItem',
                scheduleOfRate: scheduleOfRate,
                contractorList: self.contractorList,
                gridOpts: {
                    escapeHTMLInData: false,
                    trade: trade,
                    gridContainer: self,
                    store: store,
                    updateUrl: 'projectAnalyzer/scheduleOfRateBillItemUpdate',
                    structure: structure,
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item['rate-has_build_up'][0] && e.cell.field == 'rate-value'){
                            self.createBuildUpContainer(_item, scheduleOfRate);
                        }
                    },
                    singleCheckBoxSelection: function(e) {
                        var grid = this,
                            rowIndex = e.rowIndex,
                            checked = grid.selection.selected[rowIndex],
                            item = grid.getItem(rowIndex);

                        if ( ! self.tradeBillItemSelectBoxStore[grid.trade.id[0]] ) {
                            self.tradeBillItemSelectBoxStore[grid.trade.id[0]] = new Memory({ idProperty: 'id' });
                        }

                        if ( checked ) {
                            self.tradeBillItemSelectBoxStore[grid.trade.id[0]].put({ id: item.id[0] });

                            return grid.getAffectedTradeAndTradeItems(item, 'add');
                        } else {
                            return grid.getAffectedTradeAndTradeItems(item, 'remove');
                        }
                    },
                    toggleAllSelection: function(checked) {
                        var grid = this, selection = grid.selection;

                        if ( ! self.tradeBillItemSelectBoxStore[grid.trade.id[0]] ) {
                            self.tradeBillItemSelectBoxStore[grid.trade.id[0]] = new Memory({ idProperty: 'id' });
                        }

                        if (checked) {
                            selection.selectRange(0, grid.rowCount-1);
                            grid.store.fetch({
                                onComplete: function (items) {
                                    dojo.forEach(items, function (item, index) {
                                        if(item.id > 0) {
                                            self.tradeBillItemSelectBoxStore[grid.trade.id[0]].put({ id: item.id[0] });
                                        }
                                    });
                                }
                            });

                            return grid.getAffectedTradeAndTradeItems(null, 'add');
                        } else {
                            selection.deselectAll();

                            return grid.getAffectedTradeAndTradeItems(null, 'remove');
                        }
                    },
                    getAffectedTradeAndTradeItems: function(billItem, type) {
                        var grid = this,
                            selectedBillItemStore = self.tradeBillItemSelectBoxStore[grid.trade.id[0]],
                            billItems = [];

                        var pb = buildspace.dialog.indeterminateProgressBar({
                            title: nls.pleaseWait+'...'
                        });

                        pb.show();

                        if ( billItem ) {
                            billItems.push(billItem.id[0]);
                        } else {
                            selectedBillItemStore.query().forEach(function(item) {
                                billItems.push(item.id);
                            });

                            billItems = billItems.reverse().filter(function (e, i, arr) {
                                return arr.indexOf(e, i+1) === -1;
                            }).reverse();
                        }

                        var billItemIds = JSON.stringify(billItems);

                        pb.show();

                        request.post('scheduleOfRateProjectAnalyzerReport/getAffectedSelectionTradeAndTradeItems', {
                            handleAs: 'json',
                            data: {
                                pid: self.project.id,
                                trade_id: trade.id,
                                bill_item_ids: billItemIds
                            }
                        }).then(function(data) {
                            var tradeGrid     = dijit.byId('report_sor_analysis_trade-' + self.project.id);
                            var tradeItemGrid = dijit.byId('report_sor_analysis_item-' + self.project.id);

                            if ( type === 'add' ) {
                                for (var tradeId in data) {
                                    tradeGrid.grid.store.fetchItemByIdentity({
                                        identity: tradeId,
                                        onItem: function(node) {
                                            if ( ! node ) {
                                                return;
                                            }

                                            self.tradeSelectBoxStore.put({ id: tradeId });

                                            return tradeGrid.grid.rowSelectCell.toggleRow(node._0, true);
                                        }
                                    });

                                    for (var tradeItemIdIndex in data[tradeId]) {
                                        tradeItemGrid.grid.store.fetchItemByIdentity({
                                            identity: tradeItemIdIndex,
                                            onItem: function(node) {
                                                if ( ! node ) {
                                                    return;
                                                }

                                                self.tradeItemSelectBoxStore.put({ id: tradeItemIdIndex });

                                                return tradeItemGrid.grid.rowSelectCell.toggleRow(node._0, true);
                                            }
                                        });

                                        for ( var billItemKey in data[tradeId][tradeItemIdIndex] ) {
                                            self.tradeBillItemSelectBoxStore[tradeId].put({ id: data[tradeId][tradeItemIdIndex][billItemKey] });
                                        }
                                    }
                                }
                            } else {
                                for (var tradeId in data) {
                                    for (var tradeItemIdIndex in data[tradeId]) {
                                        for ( var billItemKey in data[tradeId][tradeItemIdIndex] ) {
                                            self.tradeBillItemSelectBoxStore[tradeId].remove(data[tradeId][tradeItemIdIndex][billItemKey]);
                                        }

                                        // remove checked trade item selection if no bill item is selected
                                        tradeItemGrid.grid.store.fetchItemByIdentity({
                                            identity: tradeItemIdIndex,
                                            onItem: function(node) {
                                                if ( ! node ) {
                                                    return;
                                                }

                                                if ( self.tradeBillItemSelectBoxStore[tradeId].data.length === 0 ) {
                                                    self.tradeItemSelectBoxStore.remove(tradeItemIdIndex);

                                                    return tradeItemGrid.grid.rowSelectCell.toggleRow(node._0, false);
                                                }
                                            }
                                        });
                                    }

                                    // remove checked trade selection if no trade item is selected
                                    tradeGrid.grid.store.fetchItemByIdentity({
                                        identity: tradeId,
                                        onItem: function(node) {
                                            if ( ! node ) {
                                                return;
                                            }

                                            if ( self.tradeItemSelectBoxStore.data.length === 0 ) {
                                                self.tradeSelectBoxStore.remove(tradeId);

                                                return tradeGrid.grid.rowSelectCell.toggleRow(node._0, false);
                                            }
                                        }
                                    });
                                }
                            }

                            pb.hide();
                        }, function(error) {
                            pb.hide();
                            console.log(error);
                        });
                    }
                }
            });
        },
        createBuildUpContainer: function(item, scheduleOfRate){
            var self = this,
                currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation,
                baseContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:100%;height:100%;border:0px;outline:none;",
                    gutters: false
                }),
                aContainer = new dijit.layout.AccordionContainer({
                    region: "center",
                    style:"padding:0px;width:100%;height:100%;border:0px;outline:none;"
                }),
                resourceQuery = dojo.xhrGet({
                    url: "projectAnalyzer/buildUpRateResourceList/id/"+item.id,
                    handleAs: "json"
                }),
                formatter = new GridFormatter(),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show();

            when(resourceQuery, function(resources){
                var buildUpSummaryWidget = new BuildUpRateSummary({
                    itemId: item.id,
                    container: baseContainer,
                    _csrf_token: item._csrf_token
                });
                dojo.forEach(resources, function(resource){
                    var store = new dojo.data.ItemFileWriteStore({
                        url:"projectAnalyzer/getBuildUpRateItemList/id/"+item.id+"/resource_id/"+resource.id,
                        clearOnClose: true
                    });
                    try{
                        var grid = new BuildUpGrid({
                            resource: resource,
                            billItem: item,
                            updateUrl:'projectAnalyzer/buildUpRateItemUpdate',
                            store: store,
                            buildUpSummaryWidget: buildUpSummaryWidget,
                            structure: [
                                {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                {name: nls.description, field: 'description', width:'auto', formatter: formatter.linkedCellFormatter },
                                {name: nls.number, field: 'number-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                {name: nls.constant, field: 'constant-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                {name: nls.qty, field: 'quantity-value', width:'70px', styles:'text-align:right;', formatter: formatter.formulaNumberCellFormatter},
                                {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                                {name: nls.rate, field: 'rate-value', width:'110px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                {name: nls.total, field: 'total', width:'110px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                                {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                {name: nls.lineTotal, field: 'line_total', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                            ]
                        });
                        aContainer.addChild(new dijit.layout.ContentPane({
                            title: resource.name+'<span style="color:blue;float:right;">'+currencySetting+'&nbsp;'+currency.format(resource.total_build_up)+'</span>',
                            style: "padding:0px;border:0px;",
                            doLayout: false,
                            id: 'accPane-'+resource.id+'-'+item.id,
                            content: grid
                        }));
                    }catch(e){console.log(e)}
                });

                baseContainer.addChild(aContainer);
                baseContainer.addChild(buildUpSummaryWidget);
                var container = dijit.byId('projectAnalyzer-sor_project_'+self.project.id+'-stackContainer');
                if(container){
                    var node = document.createElement("div");
                    var child = new dojox.layout.ContentPane({
                        title: buildspace.truncateString(item.description, 60),
                        style: "padding:0px;border:0px;",
                        id: 'report_sor_analysis_build_up-'+self.project.id+'_'+scheduleOfRate.id,
                        executeScripts: true },
                        node );
                    container.addChild(child);
                    child.set('content', baseContainer);
                    container.selectChild('report_sor_analysis_build_up-'+self.project.id+'_'+scheduleOfRate.id);
                }
                pb.hide();
            });
        },
        makeGridContainer: function(content, title){
            var id = this.project.id;
            var stackContainer = dijit.byId('projectAnalyzer-sor_project_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('projectAnalyzer-sor_project_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'projectAnalyzer-sor_project_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'projectAnalyzer-sor_project_'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('projectAnalyzer-sor_project_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('projectAnalyzer-sor_project_'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    while(children.length > index+1 ){
                        index = index + 1;
                        widget.removeChild(children[ index ]);
                        children[ index ].destroyRecursive(true);
                    }
                }
            });

            return borderContainer;
        },
        markedCheckBoxObject: function(grid, selectedRowStore) {
            var store = grid.store;

            selectedRowStore.query().forEach(function(item) {
                if (item.id == buildspace.constants.GRID_LAST_ROW) {
                    return;
                }

                store.fetchItemByIdentity({
                    identity: item.id,
                    onItem: function(node) {
                        if ( ! node ) {
                            return;
                        }

                        return grid.rowSelectCell.toggleRow(node._0, true);
                    }
                });
            });
        }
    });
});