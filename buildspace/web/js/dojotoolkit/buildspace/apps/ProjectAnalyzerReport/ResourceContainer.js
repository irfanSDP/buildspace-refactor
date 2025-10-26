define('buildspace/apps/ProjectAnalyzerReport/ResourceContainer',[
    'dojo/_base/declare',
    "dojo/aspect",
    'dojo/store/Memory',
    'dojo/request',
    './ResourceGrid',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ProjectAnalyzer'],
    function(declare, aspect, Memory, request, ResourceGrid, GridFormatter, nls) {

    return declare('buildspace.apps.ProjectAnalyzerReport.ResourceContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
        gutters: false,
        project: null,
        tradeSelectBoxStore: [],
        tradeItemSelectBoxStore: [],
        tradeBillItemSelectBoxStore: [],
        type: null,
        postCreate: function() {
            this.inherited(arguments);
            var self = this,
                formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "projectAnalyzer/getResources/pid/"+self.project.id,
                    clearOnClose: true
                }),
                grid = new ResourceGrid({
                    id: 'report_resource_analysis_category-'+self.project.id,
                    stackContainerTitle: nls.resources,
                    pageId: 'report_resource_analysis_category-'+self.project.id,
                    project: self.project,
                    gridOpts: {
                        gridContainer: self,
                        type: 'resource',
                        escapeHTMLInData: false,
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.description, field: 'name', width:'auto', formatter: formatter.analyzerDescriptionCellFormatter },
                            {name: nls.totalCost, field: 'total_cost', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.name[0] !== null){
                                self.createTradeGrid(_item);
                            }
                        }
                    }
                });

            var gridContainer = this.makeGridContainer(grid, nls.resources);
            this.addChild(gridContainer);
        },
        createTradeGrid: function(resource){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                url:"projectAnalyzer/getResourceTrades/pid/"+self.project.id+"/id/"+resource.id,
                clearOnClose: true
            });

            self.tradeSelectBoxStore         = new Memory({ idProperty: 'id' });
            self.tradeItemSelectBoxStore     = new Memory({ idProperty: 'id' });
            self.tradeBillItemSelectBoxStore = [];

            var grid = ResourceGrid({
                id: 'report_resource_analysis_trade-'+self.project.id,
                stackContainerTitle: resource.name,
                pageId: 'report_resource_analysis_trade-'+self.project.id+'_'+resource.id,
                project: self.project,
                resource: resource,
                type: self.type,
                gridOpts: {
                    gridContainer: self,
                    type: 'trade',
                    escapeHTMLInData: false,
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.analyzerDescriptionCellFormatter },
                        {name: nls.totalCost, field: 'total_cost', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if((_item.id == 'unsorted' ||_item.id > 0) && _item.description[0] !== null){
                            self.createItemGrid(_item, resource);
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

                        request.post('resourceProjectAnalyzerReport/getAffectedSelectionTradeItemsAndBillItems', {
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
        createItemGrid: function(trade, resource){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                url:"projectAnalyzer/getResourceItems/pid/"+self.project.id+"/rid/"+resource.id+"/id/"+trade.id,
                clearOnClose: true
            });

            var structure = [
                {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', formatter: formatter.analyzerRateCellFormatter},
                {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', formatter: formatter.analyzerWastageCellFormatter},
                {name: nls.totalQty, field: 'total_qty', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                {name: nls.totalCost, field: 'total_cost', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
            ];

            if(this.type == buildspace.constants.STATUS_POSTCONTRACT) {
                structure.push({name: nls.claimedQty, field: 'claim_quantity', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter});
                structure.push({name: nls.claimedAmount, field: 'claim_amount', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter});
            }

            var grid = ResourceGrid({
                id: 'report_resource_analysis_item-'+self.project.id,
                stackContainerTitle: trade.description,
                pageId: 'report_resource_analysis_item-'+self.project.id+'_'+trade.id,
                project: self.project,
                resource: resource,
                type: self.type,
                gridOpts: {
                    gridContainer: self,
                    trade: trade,
                    type: 'tradeItem',
                    escapeHTMLInData: false,
                    store: store,
                    unsorted: (trade.id == 'unsorted'),
                    updateUrl: 'projectAnalyzer/resourceItemUpdate',
                    structure: structure,
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if( _item.hasOwnProperty('multi-rate') && _item['multi-rate'][0] && e.cell.field == 'rate-value'){
                            return;
                        }else if( _item.hasOwnProperty('multi-wastage') && _item['multi-wastage'][0] && e.cell.field == 'wastage-value'){
                            return;
                        }else if(_item.id > 0 && _item.description[0] !== null && _item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER){
                            self.createBillGrid(_item, trade, resource);
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

                        request.post('resourceProjectAnalyzerReport/getAffectedSelectionTradeAndBillItems', {
                            handleAs: 'json',
                            data: {
                                pid: self.project.id,
                                trade_item_ids: tradeItemIds
                            }
                        }).then(function(data) {
                            var tradeGrid = dijit.byId('report_resource_analysis_trade-' + self.project.id);

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
        createBillGrid: function(item, trade, resource){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                url:"projectAnalyzer/getBills/pid/"+self.project.id+"/rid/"+resource.id+"/tid/"+trade.id+"/id/"+item.id,
                clearOnClose: true
            });

            var structure = [
                {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                {name: nls.description, field: 'description', width:'auto', formatter: formatter.analysisTreeCellFormatter},
                {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                {name: nls.totalQty, field: 'total_qty', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                {name: nls.totalCost, field: 'total_cost', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter}
            ];

            if(this.type == buildspace.constants.STATUS_POSTCONTRACT) {
                structure.push({name: nls.claimedQty, field: 'claim_quantity', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter});
                structure.push({name: nls.claimedAmount, field: 'claim_amount', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter});
            }

            var grid = ResourceGrid({
                id: 'report_resource_analysis_bill-'+self.project.id,
                stackContainerTitle: item.description,
                pageId: 'report_resource_analysis_bill-'+self.project.id+'_'+item.id,
                project: self.project,
                resource: resource,
                gridOpts: {
                    gridContainer: self,
                    trade: trade,
                    tradeItem: item,
                    type: 'billItem',
                    escapeHTMLInData: false,
                    store: store,
                    unsorted: (trade.id == 'unsorted'),
                    updateUrl: 'projectAnalyzer/billItemUpdate/rid/'+item.id,
                    structure: structure,
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
                            if ( grid.isInteger(billItem.id[0]) ) {
                                billItems.push(billItem.id[0]);
                            }
                        } else {
                            selectedBillItemStore.query().forEach(function(item) {
                                billItems.push(item.id);

                                if ( grid.isInteger(item.id) ) {
                                    billItems.push(item.id);
                                }
                            });

                            billItems = billItems.reverse().filter(function (e, i, arr) {
                                return arr.indexOf(e, i+1) === -1;
                            }).reverse();
                        }

                        var billItemIds = JSON.stringify(billItems);

                        pb.show();

                        request.post('resourceProjectAnalyzerReport/getAffectedSelectionTradeAndTradeItems', {
                            handleAs: 'json',
                            data: {
                                pid: self.project.id,
                                trade_id: grid.trade.id,
                                trade_item_id: grid.tradeItem.id,
                                bill_item_ids: billItemIds
                            }
                        }).then(function(data) {
                            var tradeGrid     = dijit.byId('report_resource_analysis_trade-' + self.project.id);
                            var tradeItemGrid = dijit.byId('report_resource_analysis_item-' + self.project.id);

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
        makeGridContainer: function(content, title){
            var id = this.project.id;
            var stackContainer = dijit.byId('projectAnalyzerReport-resource_project_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('projectAnalyzerReport-resource_project_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'projectAnalyzerReport-resource_project_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'projectAnalyzerReport-resource_project_'+id+'-stackContainer'
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

            dojo.subscribe('projectAnalyzerReport-resource_project_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('projectAnalyzerReport-resource_project_'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(), index = dojo.indexOf(children, page);

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