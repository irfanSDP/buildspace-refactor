define('buildspace/apps/ViewTendererReport/ProjectBreakdown',[
    'dojo/_base/declare',
    'dojo/_base/connect',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dijit/form/DropDownButton',
    'dijit/Menu',
    'dijit/DropDownMenu',
    'dijit/MenuItem',
    'dijit/PopupMenuItem',
    'dojo/when',
    'dojo/request',
    './PrintPreviewDialog/PrintSelectedBillGridDialog',
    './PrintPreviewDialog/PrintSelectedBillRevisionsGridDialog',
    './BillGrid',
    './SupplyOfMaterialBillGrid',
    './ScheduleOfRateBillGrid',
    './lumpSumPercentDialog',
    './primeCostRateDialog',
    'dojo/store/Memory',
    'dojo/aspect',
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/ViewTenderer'
], function(declare, connect, EnhancedGrid, GridFormatter, DropDownButton, Menu, DropDownMenu, MenuItem, PopupMenuItem, when, request, PrintSelectedBillGridDialog, PrintSelectedBillRevisionsGridDialog, BillGrid, SupplyOfMaterialBillGrid, ScheduleOfRateBillGrid, LumpSumPercentDialog, PrimeCostRateDialog, Memory, aspect, IndirectSelection, nls){

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return cellValue > 0 ? cellValue : '';
        },
        treeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            var level = item.level*16;
            cellValue = cellValue === null ? '&nbsp': cellValue;
            if(item.type < buildspace.apps.ViewTendererReport.ProjectStructureConstants.TYPE_BILL){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        }
    };

    var Grid = declare('buildspace.apps.ViewTendererReport.ProjectBreakdownGrid', EnhancedGrid, {
        builderContainer: null,
        rootProject: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        tender_setting: null,
        tender_companies: null,
        rowSelector: '0px',
        gridContainer: null,
        escapeHTMLInData: false,
        constructor:function(args){
            var formatter = this.formatter = new GridFormatter();
            this.tender_setting   = args.tender_setting;
            this.tender_companies = args.tender_companies;
            this.gridContainer    = args.gridContainer;
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

            request.post('viewTendererReporting/getAffectedElementsAndItems', {
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
                    }]
                ]
            },
            fixedColumnsAfterTypeColumns = this.generateContractorGrandTotalColumn();

            var columnToDisplay = fixedColumns;

            dojo.forEach(fixedColumnsAfterTypeColumns,function(column){
                columnToDisplay.cells[0].push(column);
            });

            this.structure = columnToDisplay;
        },
        generateContractorGrandTotalColumn: function(){
            var columns = [],
                companies = this.tender_companies,
                formatter = this.formatter,
                colCount = 0;

            dojo.forEach(companies,function(company){
                colCount++;
                var companyName = null;

                if(company.awarded)
                {
                    companyName = '<span style="color:blue;">'+buildspace.truncateString(company.name, 28)+'</span>';
                }
                else
                {
                    companyName = buildspace.truncateString(company.name, 28);
                }

                var structure = {
                    name: companyName,
                    field: company.id+'-overall_total_after_markup',
                    styles: "text-align:right;",
                    width: '120px',
                    formatter: formatter.unEditableCurrencyCellFormatter,
                    headerClasses: "typeHeader"+colCount,
                    noresize: true
                };
                columns.push(structure);
            });

            return columns;
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable)
        {
            var store = this.store;

            if(isDisable && buttonsToEnable instanceof Array )
            {
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label)
                {
                    var btn = dijit.byId(_this.rootProject.id+label+'Row-button');

                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        },
        destroy: function() {
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.ViewTendererReport.ProjectBreakdown', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        type: null,
        grid: null,
        builderContainer: null,
        viewTendererBillPreviewStore: null,
        viewTendererElementPreviewStore: null,
        viewTendererItemPreviewStore: null,
        viewTendererSupplyOfMaterialElementPreviewStore: null,
        viewTendererSupplyOfMaterialItemPreviewStore: null,
        constructor: function(args) {
            this.viewTendererBillPreviewStore                    = [];
            this.viewTendererElementPreviewStore                 = [];
            this.viewTendererItemPreviewStore                    = [];
            this.viewTendererSupplyOfMaterialElementPreviewStore = [];
            this.viewTendererSupplyOfMaterialItemPreviewStore    = [];

            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);
            this.createBreakdownGrid();
        },
        createBreakdownGrid: function(){
            var self = this;

            var tenderInfoQuery = dojo.xhrGet({
                url: "viewTenderer/getTenderInfo",
                handleAs: "json",
                content: {
                    id: this.rootProject.id
                }
            });

            tenderInfoQuery.then(function(tenderInfo) {
                self.viewTendererBillPreviewStore = new Memory({ idProperty: 'id' });
                self.tender_companies             = tenderInfo.tender_companies;

                var store = new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"viewTendererProjectBreakdown/"+self.rootProject.id+"/-1"
                });

                var grid = self.grid = new Grid({
                    id: 'viewTenderer-bill-page-container-' + self.rootProject.id,
                    builderContainer: self.builderContainer,
                    rootProject: self.rootProject,
                    tender_companies: tenderInfo.tender_companies,
                    tender_setting: tenderInfo.tender_setting,
                    store: store,
                    region: 'center',
                    gridContainer: self,
                    onRowDblClick: function(e) {
                        var me = this,
                            item = me.getItem(e.rowIndex);

                        switch(item.type[0]){
                            case buildspace.constants.TYPE_BILL:
                                self.createElementGrid(item, tenderInfo, grid);
                                break;
                            case buildspace.constants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                                self.createSupplyOfMaterialElementGrid(item, tenderInfo);
                                break;
                            case buildspace.constants.TYPE_SCHEDULE_OF_RATE_BILL:
                                self.createScheduleOfRateBillElementGrid(item, tenderInfo);
                                break;
                        }
                    }
                });

                var stackContainer = dijit.byId('viewTendererReportBreakdown' + self.rootProject.id + '-stackContainer');

                if(stackContainer) {
                    dijit.byId('viewTendererReportBreakdown' + self.rootProject.id + '-stackContainer').destroyRecursive();
                }

                stackContainer = self.stackContainer = new dijit.layout.StackContainer({
                    style: 'border:0px;width:100%;height:100%;',
                    region: "center",
                    id: 'viewTendererReportBreakdown' + self.rootProject.id + '-stackContainer'
                });

                var controller = new dijit.layout.StackController({
                    region: "top",
                    containerId: 'viewTendererReportBreakdown' + self.rootProject.id + '-stackContainer'
                });

                var controllerPane = new dijit.layout.ContentPane({
                    style: "padding:0px;overflow:hidden;",
                    baseClass: 'breadCrumbTrail',
                    region: 'top',
                    id: 'viewTendererReportBreakdown'+self.rootProject.id+'-controllerPane',
                    content: controller
                });

                self.addChild(stackContainer);
                self.addChild(controllerPane);

                // disable print by selected tenderer if there is no tenderer selected
                var disabledPrintSelectedTendererReport = false;

                if ( tenderInfo.tender_companies.length === 0 ) {
                    disabledPrintSelectedTendererReport = true;
                }

                if ( tenderInfo.tender_companies.length > 0 && tenderInfo.tender_companies[0].awarded === false ) {
                    disabledPrintSelectedTendererReport = true;
                }

                var child = new dijit.layout.BorderContainer( {
                    title: buildspace.truncateString(nls.bill, 60),
                    style: 'padding:0px;width:100%;height:100%;',
                    gutters: false
                });

                var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important; border-bottom:none; padding:2px; width:100%;"});

                var sortOptions = ['summaryAllTenderersLowToHighest', 'summaryAllTenderersHighToLowest'],
                    menu = new DropDownMenu({ style: "display: none;"}),
                    pSubMenu = new Menu();
                var summaryRevisionsSubMenu = new Menu();

                dojo.forEach(sortOptions, function(opt) {
                    var menuItem = new MenuItem({
                        id: opt+"-"+self.rootProject.id+"-menuItem",
                        label: nls[opt],
                        onClick: function() {
                            self.openBillPreviewDialogForAllTenderers(opt);
                        }
                    });
                    pSubMenu.addChild(menuItem);

                    var summaryRevisionMenuItem = new MenuItem({
                        id: opt + "-" + self.rootProject.id + '-' + self.type + "-revisionMenuItem",
                        label: nls[opt],
                        onClick: function() {
                            self.openBillRevisionsPreviewDialogForAllTenderers(opt);
                        }
                    });
                    summaryRevisionsSubMenu.addChild(summaryRevisionMenuItem);
                });

                menu.addChild(
                    new MenuItem({
                        label: nls.projectSummarySelectedTenderers,
                        iconClass: "icon-16-container icon-16-print",
                        disabled: disabledPrintSelectedTendererReport,
                        onClick: function() {
                            self.openBillPreviewDialogForSelectedTender();
                        }
                    })
                );

                var mainMenuItem = new PopupMenuItem({
                    label: nls.projectSummaryAllTenderers,
                    iconClass: "icon-16-container icon-16-print",
                    popup: pSubMenu
                });
                menu.addChild(mainMenuItem);

                var summaryRevisionsMenuItem = new PopupMenuItem({
                    label: nls.projectSummaryRevisionsAllTenderers,
                    iconClass: "icon-16-container icon-16-print",
                    popup: summaryRevisionsSubMenu
                });
                menu.addChild(summaryRevisionsMenuItem);

                toolbar.addChild(
                    new DropDownButton({
                        label: nls.summary,
                        id: self.rootProject.id+'-'+this.type+'-ProjectElementSummaryAllTenderersPrint-button',
                        iconClass: "icon-16-container icon-16-print",
                        dropDown: menu
                    })
                );

                child.addChild(toolbar);
                child.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

                stackContainer.addChild(child);

                dojo.subscribe('viewTendererReportBreakdown' + self.rootProject.id + '-stackContainer-selectChild', "", function(page) {
                    var widget = dijit.byId('viewTendererReportBreakdown' + self.rootProject.id + '-stackContainer');
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
            });
        },
        createElementGrid: function(bill, tenderInfo, breakdownGridStore) {
            this.billId = bill.id;

            if ( ! this.viewTendererElementPreviewStore[this.billId] ) {
                this.viewTendererElementPreviewStore[this.billId] = new Memory({ idProperty: 'id' });
            }

            if ( ! this.viewTendererItemPreviewStore[this.billId] ) {
                this.viewTendererItemPreviewStore[this.billId] = new Memory({ idProperty: 'id' });
            }

            var self = this;

            var store = dojo.data.ItemFileWriteStore({
                url: "viewTenderer/getElementList/id/" + bill.id,
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
                        id: 'viewTenderer-element-page-container-' + bill.id,
                        type: 'element',
                        gridOpts: {
                            builderContainer: self.builderContainer,
                            store: store,
                            tender_setting: tenderInfo.tender_setting,
                            tender_companies: tenderInfo.tender_companies,
                            typeColumns : billInfo.column_settings,
                            currentGridType: 'element',
                            gridContainer: self,
                            onRowDblClick: function(e) {
                                var inSelf = this,
                                    item = inSelf.getItem(e.rowIndex);
                                if(item.id[0] > 0 && item.description[0] !== null && item.description[0] !== '') {
                                    me.createItemGrid(item, billInfo, tenderInfo, grid, bill);
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
        createItemGrid: function(element, billInfo, tenderInfo, elementGridStore, bill){
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
                    url:"viewTenderer/getItemList/id/"+element.id+"/bill_id/"+ bill.id,
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
                            builderContainer: self.builderContainer,
                            store: store,
                            escapeHTMLInData: false,
                            typeColumns : billInfo.column_settings,
                            markupSettings: billInfo.markup_settings,
                            elementGridStore: elementGridStore,
                            hierarchyTypes: hierarchyTypes,
                            hierarchyTypesForHead: hierarchyTypesForHead,
                            tender_setting: tenderInfo.tender_setting,
                            tender_companies: tenderInfo.tender_companies,
                            unitOfMeasurements: uom,
                            currentGridType: 'item',
                            gridContainer: self,
                            editableCellDblClick: function(e) {
                                var colField = e.cell.field,
                                    rowIndex = e.rowIndex,
                                    item = this.getItem(rowIndex),
                                    billGridStore = this.store,
                                    splittedFieldName = colField.split("-");

                                if (item && (item.project_revision_deleted_at !== undefined && item.project_revision_deleted_at[0]))
                                {
                                    return false;
                                }

                                //If Type Reference Column Value
                                if(splittedFieldName.length == 3)
                                {
                                    var companyId = splittedFieldName[0];
                                    field = splittedFieldName[1];

                                    if(field == "rate" && item.id > 0){
                                        if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT){
                                            var lumpSumPercentDialog = new LumpSumPercentDialog({
                                                itemObj: item,
                                                companyId: companyId,
                                                rootProject: self.rootProject,
                                                currentBillLockedStatus: false,
                                                billGridStore: billGridStore
                                            });
                                            lumpSumPercentDialog.show();
                                        }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE){
                                            var pcRateDialog = new PrimeCostRateDialog({
                                                itemObj: item,
                                                companyId: companyId,
                                                rootProject: self.rootProject,
                                                billGridStore: billGridStore,
                                                currentBillLockedStatus: false
                                            });
                                            pcRateDialog.show();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }catch(e){console.debug(e);}
            },function(error){
                /* got fucked */
            });
        },
        createSupplyOfMaterialElementGrid: function(bill, tenderInfo){
            this.billId = bill.id;

            if ( ! this.viewTendererSupplyOfMaterialElementPreviewStore[this.billId] ) {
                this.viewTendererSupplyOfMaterialElementPreviewStore[this.billId] = new Memory({ idProperty: 'id' });
            }

            if ( ! this.viewTendererSupplyOfMaterialItemPreviewStore[this.billId] ) {
                this.viewTendererSupplyOfMaterialItemPreviewStore[this.billId] = new Memory({ idProperty: 'id' });
            }

            var store = dojo.data.ItemFileWriteStore({
                    url: "viewTenderer/getSupplyOfMaterialElementList/id/" + bill.id,
                    clearOnClose: true,
                    urlPreventCache:true
                }),
                me = this;

            try {
                var grid = new SupplyOfMaterialBillGrid({
                    stackContainerTitle: bill.title,
                    billId: bill.id,
                    rootProject: me.rootProject,
                    pageId: 'som_element-page-' + bill.id,
                    gridOpts: {
                        store: store,
                        tender_setting: tenderInfo.tender_setting,
                        tender_companies: tenderInfo.tender_companies,
                        gridContainer: this,
                        builderContainer: this.builderContainer,
                        id: 'viewTenderer-supplyOfMaterial-element-grid-' + bill.id,
                        onRowDblClick: function(e) {
                            var self = this,
                                item = self.getItem(e.rowIndex);
                            if(item.id[0] > 0 && item.description[0] !== null && item.description[0] !== '') {
                                me.createSupplyOfMaterialItemGrid(item, tenderInfo, grid, bill);
                            }
                        }
                    }
                });
            }
            catch(e){
                console.debug(e);
            }
        },
        createSupplyOfMaterialItemGrid: function(element, tenderInfo, elementGridStore, bill){
            this.billId = bill.id;

            var store = new dojo.data.ItemFileWriteStore({
                url:"viewTenderer/getSupplyOfMaterialItemList/id/"+element.id,
                clearOnClose: true
            });

            try {
                var grid = new SupplyOfMaterialBillGrid({
                    stackContainerTitle: element.description,
                    billId: bill.id,
                    rootProject: this.rootProject,
                    id: 'som_item-page-container-' + bill.id,
                    elementId: element.id,
                    pageId: 'som_item-page-' + bill.id,
                    type: 'tree',
                    gridOpts: {
                        store: store,
                        elementGridStore: elementGridStore,
                        tender_setting: tenderInfo.tender_setting,
                        tender_companies: tenderInfo.tender_companies,
                        updateUrl: 'viewTenderer/supplyOfMaterialItemUpdate',
                        currentGridType: 'item',
                        gridContainer: this,
                        builderContainer: this.builderContainer
                    }
                });
            }catch(e){console.debug(e);}

        },
        createScheduleOfRateBillElementGrid: function(bill, tenderInfo){
            this.billId = bill.id;

            if ( ! this.viewTendererElementPreviewStore[this.billId] ) {
                this.viewTendererElementPreviewStore[this.billId] = new Memory({ idProperty: 'id' });
            }

            if ( ! this.viewTendererItemPreviewStore[this.billId] ) {
                this.viewTendererItemPreviewStore[this.billId] = new Memory({ idProperty: 'id' });
            }

            var self = this;

            var store = dojo.data.ItemFileWriteStore({
                    url: "viewTenderer/getScheduleOfRateBillElementList/id/" + bill.id,
                    clearOnClose: true,
                    urlPreventCache:true
                }),
                me = this;

            try {
                var grid = new ScheduleOfRateBillGrid({
                    stackContainerTitle: bill.title,
                    billId: bill.id,
                    rootProject: me.rootProject,
                    pageId: 'sorb_element-page-' + bill.id,
                    id: 'viewTenderer-sorb_element-page-container-' + bill.id,
                    gridOpts: {
                        store: store,
                        tender_setting: tenderInfo.tender_setting,
                        tender_companies: tenderInfo.tender_companies,
                        gridContainer: self,
                        currentGridType: 'element',
                        builderContainer: self.builderContainer,
                        onRowDblClick: function(e) {
                            var inSelf = this,
                                item = inSelf.getItem(e.rowIndex);
                            if(item.id[0] > 0 && item.description[0] !== null && item.description[0] !== '') {
                                me.createScheduleOfRateBillItemGrid(item, tenderInfo, grid, bill);
                            }
                        }
                    }
                });
            }
            catch(e){
                console.debug(e);
            }
        },
        createScheduleOfRateBillItemGrid: function(element, tenderInfo, elementGridStore, bill){
            this.billId = bill.id;
            var self = this;

            var store = new dojo.data.ItemFileWriteStore({
                url:"viewTenderer/getScheduleOfRateBillItemList/id/"+element.id,
                clearOnClose: true
            });

            try {
                var grid = new ScheduleOfRateBillGrid({
                    stackContainerTitle: element.description,
                    billId: bill.id,
                    disableEditing: false,
                    rootProject: this.rootProject,
                    id: 'sorb_item-page-container-' + bill.id,
                    elementId: element.id,
                    pageId: 'sorb_item-page-' + bill.id,
                    type: 'tree',
                    gridOpts: {
                        store: store,
                        builderContainer: self.builderContainer,
                        elementGridStore: elementGridStore,
                        tender_setting: tenderInfo.tender_setting,
                        tender_companies: tenderInfo.tender_companies,
                        updateUrl: 'viewTenderer/scheduleOfRateBillItemUpdate',
                        gridContainer: self,
                        currentGridType: 'item'
                    }
                });
            }catch(e){console.debug(e);}

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

            request.post('viewTendererReporting/getPrintingSelectedBillByTenderer', {
                handleAs: 'json',
                data: {
                    id: self.rootProject.id,
                    billIds: billIds
                }
            }).then(function(data) {
                var companyId, companyName;

                dojo.forEach(companies, function(company) {
                    if (company.awarded)
                    {
                        companyId   = company.id;
                        companyName = buildspace.truncateString(company.name, 28);
                    }
                });

                var dialog = new PrintSelectedBillGridDialog({
                    title: nls.projectSummarySelectedTenderers,
                    companyId: companyId,
                    companyName: companyName,
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
        },
        openBillPreviewDialogForAllTenderers: function(type) {
            var self = this,
                companies = self.tender_companies,
                selectedBillStore = self.viewTendererBillPreviewStore,
                seletedTenderStore = self.builderContainer.selectedTenderers,
                tenderers = [],
                bills = [];

            seletedTenderStore.query().forEach(function(tenderer) {
                tenderers.push(tenderer.id);
            });

            selectedBillStore.query().forEach(function(item) {
                bills.push(item.id);
            });

            tenderers = tenderers.reverse().filter(function (e, i, arr) {
                return arr.indexOf(e, i+1) === -1;
            }).reverse();

            bills = bills.reverse().filter(function (e, i, arr) {
                return arr.indexOf(e, i+1) === -1;
            }).reverse();

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getPrintingSelectedBillByAllTenderer', {
                handleAs: 'json',
                data: {
                    id: self.rootProject.id,
                    tendererIds: JSON.stringify(tenderers),
                    billIds: JSON.stringify(bills),
                    type: type
                }
            }).then(function(data) {
                var dialog = new PrintSelectedBillGridDialog({
                    project: self.rootProject,
                    title: nls.projectSummaryAllTenderers + " (" + nls[type] + ")",
                    companies: companies,
                    data: data,
                    selectedTenderers: tenderers,
                    projectId: self.rootProject.id,
                    selectedBills: bills,
                    type: type
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openBillRevisionsPreviewDialogForAllTenderers: function(type) {
            var self = this,
                companies = self.tender_companies,
                selectedBillStore = self.viewTendererBillPreviewStore,
                seletedTenderStore = self.builderContainer.selectedTenderers,
                tenderers = [],
                bills = [];

            seletedTenderStore.query().forEach(function(tenderer) {
                tenderers.push(tenderer.id);
            });

            selectedBillStore.query().forEach(function(item) {
                bills.push(item.id);
            });

            tenderers = tenderers.reverse().filter(function (e, i, arr) {
                return arr.indexOf(e, i+1) === -1;
            }).reverse();

            bills = bills.reverse().filter(function (e, i, arr) {
                return arr.indexOf(e, i+1) === -1;
            }).reverse();

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getPrintingSelectedBillRevisionsByAllTenderer', {
                handleAs: 'json',
                data: {
                    id: self.rootProject.id,
                    tendererIds: JSON.stringify(tenderers),
                    billIds: JSON.stringify(bills),
                    type: type
                }
            }).then(function(data) {
                var dialog = new PrintSelectedBillRevisionsGridDialog({
                    project: self.rootProject,
                    title: nls.projectSummaryRevisionsAllTenderers + " (" + nls[type] + ")",
                    companies: companies,
                    data: data,
                    selectedTenderers: tenderers,
                    projectId: self.rootProject.id,
                    selectedBills: bills,
                    type: type
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        }
    });
});