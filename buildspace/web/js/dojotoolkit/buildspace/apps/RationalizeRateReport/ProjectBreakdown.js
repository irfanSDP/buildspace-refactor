define('buildspace/apps/RationalizeRateReport/ProjectBreakdown',[
    'dojo/_base/declare',
    'dojo/_base/connect',
    'dojox/grid/EnhancedGrid',
    "dojo/request",
    './PrintPreviewDialog/PrintSelectedBillGridDialog',
    'buildspace/widget/grid/cells/Formatter',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dojo/when",
    "dojo/store/Memory",
    './BillGrid',
    './lumpSumPercentDialog',
    './primeCostRateDialog',
    'dojo/aspect',
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/RationalizeRate'
], function(declare, connect, EnhancedGrid, request, PrintSelectedBillGridDialog, GridFormatter, DropDownButton, DropDownMenu, MenuItem, when, Memory, BillGrid, LumpSumPercentDialog, PrimeCostRateDialog, aspect, IndirectSelection, nls){

    var Grid = declare('buildspace.apps.RationalizeRateReport.ProjectBreakdownGrid', EnhancedGrid, {
        rootProject: null,
        explorer: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        tender_setting: null,
        tender_companies: null,
        rowSelector: '0px',
        gridContainer: null,
        constructor:function(args){
            var formatter         = this.formatter = new GridFormatter();
            this.tender_setting   = args.tender_setting;
            this.tender_companies = args.tender_companies;
            this.currencySetting  = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            this.generateColumnStructure();

            this.plugins = {indirectSelection: {headerSelector:true, width:"40px", styles:"text-align: center;"}};

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            this._connects.push(connect.connect(this, 'onCellClick', function(e) {
                if (e.cell.name !== "") {
                    return;
                }

                self.singleCheckBoxSelection(e);
            }));

            this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue) {
                self.toggleAllSelection(newValue);
            }));
        },
        singleCheckBoxSelection: function(e) {
            var self = this,
                rowIndex = e.rowIndex,
                checked = this.selection.selected[rowIndex],
                item = this.getItem(rowIndex);

            if ( checked ) {
                self.gridContainer.viewTendererBillPreviewStore.put({ id: item.id[0] });

                return self.getAffectedElementsAndItemsByBillId(item, 'add');
            } else {
                return self.getAffectedElementsAndItemsByBillId(item, 'remove');
            }
        },
        toggleAllSelection: function(checked) {
            var self = this, selection = this.selection;

            if (checked) {
                selection.selectRange(0, self.rowCount-1);
                self.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item.id > 0) {
                                self.gridContainer.viewTendererBillPreviewStore.put({ id: item.id[0] });
                            }
                        });
                    }
                });

                return self.getAffectedElementsAndItemsByBillId(null, 'add');
            } else {
                selection.deselectAll();

                return self.getAffectedElementsAndItemsByBillId(null, 'remove');
            }
        },
        generateColumnStructure: function(){
            var formatter = this.formatter;

            var fixedColumns = this.fixedColumns = {
                noscroll: false,
                width: '50',
                cells: [
                    [{
                        name: nls.description,
                        field: 'title',
                        width:'auto',
                        formatter: Formatter.treeCellFormatter
                    },{
                        name: nls.amount,
                        field: 'overall_total_after_markup',
                        width:'150px', styles:'color:blue;text-align: right;',
                        formatter: formatter.unEditableCurrencyCellFormatter
                    },{
                        name: nls.rationalizedTotal,
                        field: 'rationalized_overall_total_after_markup',
                        width:'150px', styles:'color:blue;text-align: right;',
                        formatter: formatter.unEditableCurrencyCellFormatter
                    }]
                ]
            };

            var columnToDisplay = fixedColumns;

            this.structure = columnToDisplay;
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        getAffectedElementsAndItemsByBillId: function(bill, type) {
            var self = this,
                selectedItemStore = self.gridContainer.viewTendererBillPreviewStore,
                bills = [];

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            if ( bill ) {
                bills.push(bill.id[0]);
            } else {
                selectedItemStore.query().forEach(function(item) {
                    bills.push(item.id);
                });

                bills = bills.reverse().filter(function (e, i, arr) {
                    return arr.indexOf(e, i+1) === -1;
                }).reverse();
            }

            var billIds = JSON.stringify(bills);

            pb.show();

            request.post('rationalizeRateReport/getAffectedElementsAndItems', {
                handleAs: 'json',
                data: {
                    bill_ids: billIds
                }
            }).then(function(data) {
                for (var billId in data) {
                    if ( ! self.gridContainer.viewTendererElementPreviewStore[billId] ) {
                        self.gridContainer.viewTendererElementPreviewStore[billId] = new Memory({ idProperty: 'id' });
                    }

                    if ( ! self.gridContainer.viewTendererItemPreviewStore[billId] ) {
                        self.gridContainer.viewTendererItemPreviewStore[billId] = new Memory({ idProperty: 'id' });
                    }
                }

                if ( type === 'add' ) {
                    for (var billId in data) {
                        self.gridContainer.viewTendererBillPreviewStore.put({ id: billId });

                        for (var elementId in data[billId]) {
                            self.gridContainer.viewTendererElementPreviewStore[billId].put({ id: elementId });

                            for (var itemIdIndex in data[billId][elementId]) {
                                self.gridContainer.viewTendererItemPreviewStore[billId].put({ id: data[billId][elementId][itemIdIndex] });
                            }
                        }
                    }
                } else {
                    for (var billId in data) {
                        self.gridContainer.viewTendererBillPreviewStore.remove(billId);

                        for (var elementId in data[billId]) {
                            self.gridContainer.viewTendererElementPreviewStore[billId].remove(elementId);

                            for (var itemIdIndex in data[billId][elementId]) {
                                self.gridContainer.viewTendererItemPreviewStore[billId].remove(data[billId][elementId][itemIdIndex]);
                            }
                        }
                    }
                }

                pb.hide();
            }, function(error) {
                pb.hide();
                console.log(error);
            });
        },
    });

    var ProjectBreakdown = declare('buildspace.apps.RationalizeRateReport.ProjectBreakdown', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        type: null,
        explorer: null,
        grid: null,
        viewTendererBillPreviewStore: null,
        viewTendererElementPreviewStore: null,
        viewTendererItemPreviewStore: null,
        constructor: function(args) {
            this.viewTendererBillPreviewStore    = [];
            this.viewTendererElementPreviewStore = [];
            this.viewTendererItemPreviewStore    = [];

            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);
            this.createBreakdownGrid();
        },
        reconstructBillContainer: function() {
            var controllerPane = dijit.byId('rationalizeRateReportBreakdown'+this.rootProject.id+'-controllerPane'),
                stackContainer = dijit.byId('rationalizeRateReportBreakdown'+this.rootProject.id+'-stackContainer');

            controllerPane.destroyRecursive();
            stackContainer.destroyRecursive();

            this.createBreakdownGrid();
        },
        createBreakdownGrid: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            self.viewTendererBillPreviewStore = new Memory({ idProperty: 'id' });

            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose:true,
                url:"rationalizeRate/getProjectBreakdown/id/"+self.rootProject.id
            });

            var grid = self.grid = Grid({
                id: 'rationalizedRate-bill-page-container-' + self.rootProject.id,
                rootProject: self.rootProject,
                explorer: self.explorer,
                gridContainer: self,
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.type[0] == buildspace.constants.TYPE_BILL)
                    {
                        self.createElementGrid(item, grid);
                    }
                },
                store: store
            });

            var stackContainer = dijit.byId('rationalizeRateReportBreakdown' + self.rootProject.id + '-stackContainer');

            if(stackContainer) {
                dijit.byId('rationalizeRateReportBreakdown' + self.rootProject.id + '-stackContainer').destroyRecursive();
            }

            stackContainer = self.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: 'rationalizeRateReportBreakdown' + self.rootProject.id + '-stackContainer'
            });

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'rationalizeRateReportBreakdown' + self.rootProject.id + '-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                id: 'rationalizeRateReportBreakdown'+self.rootProject.id+'-controllerPane',
                content: controller
            });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            var child = new dijit.layout.BorderContainer( {
                title: buildspace.truncateString(nls.bill, 60),
                style: 'padding:0px;width:100%;height:100%;',
                gutters: false
            });

            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important; border-bottom:none; padding:2px; width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id+'-'+this.type+'-ProjectElementSummarySelectedTenderer-button',
                    label: nls.projectSummary,
                    iconClass: "icon-16-container icon-16-print",
                    onClick: function() {
                        self.openBillPreviewDialogForSelectedTender();
                    }
                })
            );

            child.addChild(toolbar);
            child.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            stackContainer.addChild(child);

            dojo.subscribe('rationalizeRateReportBreakdown' + self.rootProject.id + '-stackContainer-selectChild', "", function(page) {
                var widget = dijit.byId('rationalizeRateReportBreakdown' + self.rootProject.id + '-stackContainer');
                if(widget) {
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page.id));
                    var pageIndex = 0,
                        childLength = children.length;

                    pageIndex = index = index + 1;

                    while(children.length > index) {
                        widget.removeChild(children[index]);
                        children[index].destroyRecursive();
                        index = index + 1;
                    }
                }
            });
        },
        createElementGrid: function(bill, breakdownGridStore){
            this.billId = bill.id;
            var self = this;

            if ( ! self.viewTendererElementPreviewStore[this.billId] ) {
                self.viewTendererElementPreviewStore[this.billId] = new Memory({ idProperty: 'id' });
            }

            if ( ! self.viewTendererItemPreviewStore[this.billId] ) {
                self.viewTendererItemPreviewStore[this.billId] = new Memory({ idProperty: 'id' });
            }

            var store = dojo.data.ItemFileWriteStore({
                url: "rationalizeRate/getElementList/id/" + bill.id,
                clearOnClose: true,
                urlPreventCache:true
            }),
            billInfoQuery = dojo.xhrGet({
                url: "billManager/getBillInfo",
                handleAs: "json",
                content: {
                    id: bill.id
                }
            }),
            me = this,
            pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show();

            billInfoQuery.then(function(billInfo) {
                pb.hide();
                try {

                    var grid = new BillGrid({
                        stackContainerTitle: bill.title,
                        billId: bill.id,
                        rootProject: me.rootProject,
                        pageId: 'element-page-' + bill.id,
                        id: 'rationalizedRate-element-page-container-' + bill.id,
                        type: 'element',
                        gridOpts: {
                            gridContainer: self,
                            store: store,
                            typeColumns : billInfo.column_settings,
                            currentGridType: 'element',
                            onRowDblClick: function(e) {
                                var self = this,
                                    item = self.getItem(e.rowIndex);
                                if(item.id[0] > 0 && item.description[0] !== null && item.description[0] !== '') {
                                    me.createItemGrid(item, billInfo, grid, bill);
                                }
                            }
                        }
                    });
                }
                catch(e){
                    console.debug(e);
                }
            });
        },
        createItemGrid: function(element, billInfo, elementGridStore, bill){
            this.billId = bill.id;

            var self = this,
                hierarchyTypes = {
                    options: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID_TEXT
                    ],
                    values: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID
                    ]
                },
                hierarchyTypesForHead = {
                    options: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT
                    ],
                    values: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER
                    ]
                },
                store = new dojo.data.ItemFileWriteStore({
                    url:"rationalizeRate/getItemList/id/"+element.id+"/bill_id/"+ bill.id,
                    clearOnClose: true
                }),
                unitQuery = dojo.xhrGet({
                    url: "billManager/getUnits/billId/"+ bill.id,
                    handleAs: "json"
                }),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show();

            unitQuery.then(function(uom){
                return uom;
            });

            return when(unitQuery, function(uom){
                pb.hide();
                try{
                    var grid = new BillGrid({
                        stackContainerTitle: element.description,
                        billId: bill.id,
                        rootProject: self.rootProject,
                        id: 'item-page-container-' + bill.id,
                        elementId: element.id,
                        pageId: 'item-page-' + bill.id,
                        type: 'tree',
                        gridOpts: {
                            gridContainer: self,
                            store: store,
                            escapeHTMLInData: false,
                            typeColumns : billInfo.column_settings,
                            markupSettings: billInfo.markup_settings,
                            elementGridStore: elementGridStore,
                            hierarchyTypes: hierarchyTypes,
                            hierarchyTypesForHead: hierarchyTypesForHead,
                            updateUrl: 'rationalizeRate/billItemRateUpdate',
                            unitOfMeasurements: uom,
                            currentGridType: 'item',
                        }
                    });
                }catch(e){console.debug(e);}
            },function(error){
                /* got fucked */
            });
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
        },
        openBillPreviewDialogForSelectedTender: function() {
            var self = this,
                companies = self.tender_companies,
                selectedBillStore = self.viewTendererBillPreviewStore,
                bills = [];

            selectedBillStore.query().forEach(function(item) {
                bills.push(item.id);
            });

            bills = bills.reverse().filter(function (e, i, arr) {
                return arr.indexOf(e, i+1) === -1;
            }).reverse();

            var billIds = JSON.stringify(bills);

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('rationalizeRateReport/getPrintingSelectedBillByTenderer', {
                handleAs: 'json',
                data: {
                    id: self.rootProject.id,
                    billIds: billIds
                }
            }).then(function(data) {
                var dialog = new PrintSelectedBillGridDialog({
                    title: nls.projectSummary,
                    data: data,
                    projectId: self.rootProject.id,
                    selectedBills: bills
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return cellValue > 0 ? cellValue : '';
        },
        treeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            var level = item.level*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;
            if(item.type < buildspace.apps.RationalizeRateReport.ProjectStructureConstants.TYPE_BILL){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        }
    };

    return ProjectBreakdown;
});