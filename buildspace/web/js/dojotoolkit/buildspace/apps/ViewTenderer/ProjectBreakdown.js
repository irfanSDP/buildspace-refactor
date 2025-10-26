define('buildspace/apps/ViewTenderer/ProjectBreakdown',[
    'dojo/_base/declare',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    './AssignContractorDialog',
    "dijit/MenuItem",
    "dojo/when",
    './BillGrid',
    './SupplyOfMaterialBillGrid',
    './ScheduleOfRateBillGrid',
    './lumpSumPercentDialog',
    './primeCostRateDialog',
    './HistoricalRateSearchDialog',
    "./TendererDetails",
    'dojo/aspect',
    'dojo/i18n!buildspace/nls/ViewTenderer'
], function(declare, EnhancedGrid, GridFormatter, DropDownButton, DropDownMenu, AssignContractorDialog, MenuItem, when, BillGrid, SupplyOfMaterialBillGrid, ScheduleOfRateBillGrid, LumpSumPercentDialog, PrimeCostRateDialog, HistoricalRateSearchDialog, TendererDetails, aspect, nls){

    var Grid = declare('buildspace.apps.ViewTenderer.ProjectBreakdownGrid', EnhancedGrid, {
        rootProject: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        tender_setting: null,
        tender_companies: null,
        rowSelector: '0px',
        escapeHTMLInData: false,
        borderContainerWidget: null,
        constructor:function(args){
            var formatter = this.formatter = new GridFormatter();
            this.tender_setting          = args.tender_setting;
            this.tender_companies        = args.tender_companies;
            this.currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            this.generateColumnStructure();

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
            this.on("RowClick", function(e){
                var colField = e.cell.field,
                    rowIndex = e.rowIndex,
                    _item = this.getItem(rowIndex);

                if(_item && parseInt(String(_item.id)) > 0 && parseInt(String(_item.type)) == buildspace.apps.ViewTenderer.ProjectStructureConstants.TYPE_BILL){
                    this.disableToolbarButtons(false, ['ExportItemFromBill'], ['ImportDropDown']);
                }else{
                    this.disableToolbarButtons(true);
                }

            }, true);
        },
        generateColumnStructure: function(){
            var formatter = this.formatter;

            this.fixedColumns = {
                noscroll: false,
                width: '50',
                cells: [
                    [{
                        name: 'No.',
                        field: 'count',
                        width:'30px',
                        styles:'text-align:center;',
                        formatter: Formatter.rowCountCellFormatter
                    },{
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
            };
            var fixedColumnsAfterTypeColumns = this.generateContractorGrandTotalColumn();

            var columnToDisplay = this.fixedColumns;

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

                if(company.awarded){
                    companyName = '<span style="color:blue;">'+buildspace.truncateString(company.name, 28)+'</span>';
                }else{
                    companyName = buildspace.truncateString(company.name, 28);
                }

                columns.push({
                    name: companyName,
                    field: company.id+'-overall_total_after_markup',
                    styles: "text-align:right;",
                    width: '120px',
                    formatter: formatter.unEditableCurrencyCellFormatter,
                    headerClasses: "typeHeader"+colCount,
                    noresize: true
                });
            });

            return columns;
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(_this.rootProject.id+label+'Row-button');

                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        },
        addTendererDetailsContainer: function(tendererId){
            var self = this;
            var id = 'project-'+self.rootProject.id+'-viewTendererDetails';
            var container = dijit.byId(id);

            if(container){
                this.borderContainerWidget.removeChild(container);
                container.destroy();
            }

            container = new TendererDetails({
                id: id,
                region: 'bottom',
                project: self.rootProject,
                company_id: tendererId,
                tender_companies: self.tender_companies,
                style:"padding:0px;margin:0px;width:100%;height:40%;",
                gutters: true
            });

            this.borderContainerWidget.addChild(container);

            return container;
        },
        onHeaderCellDblClick: function(e) {
            var slug = '-overall_total_after_markup';
            if(e.cell.field.endsWith(slug)){
                var companyId = e.cell.field.replace(slug, '');
                this.addTendererDetailsContainer(companyId);
            }
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return cellValue > 0 ? cellValue : '';
        },
        treeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            var level = parseInt(String(item.level))*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;
            if(item && parseInt(String(item.type)) < buildspace.apps.ViewTenderer.ProjectStructureConstants.TYPE_BILL){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        }
    };

    return declare('buildspace.apps.ViewTenderer.ProjectBreakdown', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        tenderAlternative: null,
        type: null,
        grid: null,
        postCreate: function(){
            this.inherited(arguments);
            this.createBreakdownGrid();
        },
        reconstructBillContainer: function() {
            var controllerPane = dijit.byId('viewTenderBreakdown'+this.rootProject.id+'-controllerPane'),
                stackContainer = dijit.byId('viewTenderBreakdown'+this.rootProject.id+'-stackContainer');
            
            if(controllerPane){
                controllerPane.destroyRecursive();
            }
            
            if(stackContainer){
                stackContainer.destroyRecursive();
            }
            

            this.createBreakdownGrid();
        },
        createBreakdownGrid: function(){
            var self = this;
            var project = this.rootProject;
            var tenderAlternative = this.tenderAlternative;
            
            var tenderInfoQuery = dojo.xhrGet({
                url: "viewTenderer/getTenderInfo",
                handleAs: "json",
                content: {
                    id: parseInt(String(project.id))
                }
            });

            tenderInfoQuery.then(function(tenderInfo) {
                var projectBreakdownUrl = "viewTendererProjectBreakdown/"+parseInt(String(project.id));

                if(tenderAlternative){
                    projectBreakdownUrl += "/"+parseInt(String(tenderAlternative.id))
                }else{
                    projectBreakdownUrl += "/-1";
                }
                var store = new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:projectBreakdownUrl
                });

                var grid = self.grid = Grid({
                    rootProject: self.rootProject,
                    tender_companies: tenderInfo.tender_companies,
                    tender_setting: tenderInfo.tender_setting,
                    borderContainerWidget: self,
                    onRowDblClick: function(e) {
                        var me = this,
                            item = me.getItem(e.rowIndex);

                        var tendererDetailscontainer = dijit.byId('project-'+self.rootProject.id+'-viewTendererDetails');

                        if(tendererDetailscontainer){
                            this.borderContainerWidget.removeChild(tendererDetailscontainer);
                            tendererDetailscontainer.destroy();
                        }

                        switch(parseInt(String(item.type))){
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
                    },
                    store: store
                });

                var stackContainer = dijit.byId('viewTenderBreakdown' + self.rootProject.id + '-stackContainer');

                if(stackContainer) {
                    dijit.byId('viewTenderBreakdown' + self.rootProject.id + '-stackContainer').destroyRecursive();
                }

                stackContainer = self.stackContainer = new dijit.layout.StackContainer({
                    style: 'border:0px;width:100%;height:100%;',
                    region: "center",
                    id: 'viewTenderBreakdown' + self.rootProject.id + '-stackContainer'
                });

                var controller = new dijit.layout.StackController({
                    region: "top",
                    containerId: 'viewTenderBreakdown' + self.rootProject.id + '-stackContainer'
                });

                var controllerPane = new dijit.layout.ContentPane({
                    style: "padding:0px;overflow:hidden;",
                    baseClass: 'breadCrumbTrail',
                    region: 'top',
                    id: 'viewTenderBreakdown'+self.rootProject.id+'-controllerPane',
                    content: controller
                });

                self.addChild(stackContainer);
                self.addChild(controllerPane);

                var child = new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(nls.bills, 60),
                    content: grid,
                    grid: grid
                });

                stackContainer.addChild(child);

                dojo.subscribe('viewTenderBreakdown' + self.rootProject.id + '-stackContainer-selectChild', "", function(page) {
                    var widget = dijit.byId('viewTenderBreakdown' + self.rootProject.id + '-stackContainer');
                    if(widget) {
                        var children = widget.getChildren(),
                            index = dojo.indexOf(children, page);

                        index = index + 1;

                        if(children.length > index){
                            while(children.length > index) {
                                widget.removeChild(children[index]);
                                children[index].destroyDescendants();
                                children[index].destroyRecursive();

                                index = index + 1;
                            }

                            if(page.grid){
                                var selectedIndex = page.grid.selection.selectedIndex;

                                page.grid.store.save();
                                page.grid.store.close();

                                var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                                    handle.remove();
                                    if(selectedIndex > -1){
                                        this.scrollToRow(selectedIndex);
                                        this.selection.setSelected(selectedIndex, true);
                                    }
                                });

                                page.grid.sort();
                            }
                        }
                    }
                });

            });
        },
        createElementGrid: function(bill, tenderInfo, breakdownGridStore){
            this.billId = bill.id;

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
                        id: 'element-page-container-' + bill.id,
                        gridOpts: {
                            store: store,
                            tender_setting: tenderInfo.tender_setting,
                            tender_companies: tenderInfo.tender_companies,
                            typeColumns : billInfo.column_settings,
                            currentGridType: 'element',
                            onRowDblClick: function(e) {
                                var self = this,
                                    item = self.getItem(e.rowIndex);
                                if(item && parseInt(String(item.id)) > 0 && String(item.description) !== null && String(item.description) !== '') {
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
                        disableEditing: self.rootProject.tendering_module_locked[0],
                        rootProject: self.rootProject,
                        id: 'item-page-container-' + bill.id,
                        elementId: element.id,
                        pageId: 'item-page-' + bill.id,
                        type: 'tree',
                        gridOpts: {
                            store: store,
                            escapeHTMLInData: false,
                            typeColumns : billInfo.column_settings,
                            markupSettings: billInfo.markup_settings,
                            elementGridStore: elementGridStore,
                            hierarchyTypes: hierarchyTypes,
                            hierarchyTypesForHead: hierarchyTypesForHead,
                            tender_setting: tenderInfo.tender_setting,
                            tender_companies: tenderInfo.tender_companies,
                            updateUrl: 'viewTenderer/billItemRateUpdate',
                            unitOfMeasurements: uom,
                            currentGridType: 'item',
                            editableCellDblClick: function(e) {
                                var colField = e.cell.field,
                                    rowIndex = e.rowIndex,
                                    item = this.getItem(rowIndex),
                                    billGridStore = this.store,
                                    splittedFieldName = colField.split("-");

                                if (item && (item.project_revision_deleted_at !== undefined && String(item.project_revision_deleted_at).toLowerCase() != 'false' && String(item.project_revision_deleted_at).length > 0)){
                                    return false;
                                }

                                if(colField == "historical_rate" && item && parseInt(String(item.id)) > 0 && (parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID)){
                                    var dialog = new HistoricalRateSearchDialog({
                                        targetBillItem: item,
                                        tendererGrid: this
                                    });

                                    dialog.show();
                                }

                                //If Type Reference Column Value
                                if(splittedFieldName.length == 3){
                                    var companyId = splittedFieldName[0];
                                    var field = splittedFieldName[1];

                                    if(field == "rate" && item && parseInt(String(item.id)) > 0){
                                        if(parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT){
                                            var lumpSumPercentDialog = new LumpSumPercentDialog({
                                                itemObj: item,
                                                companyId: companyId,
                                                rootProject: self.rootProject,
                                                currentBillLockedStatus: false,
                                                billGridStore: billGridStore
                                            });
                                            lumpSumPercentDialog.show();
                                        }else if(parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE){
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
                    id: 'som_element-page-container-' + bill.id,
                    gridOpts: {
                        store: store,
                        tender_setting: tenderInfo.tender_setting,
                        tender_companies: tenderInfo.tender_companies,
                        onRowDblClick: function(e) {
                            var self = this,
                                item = self.getItem(e.rowIndex);
                            if(item && parseInt(String(item.id)) > 0 && String(item.description) !== null && String(item.description) !== '') {
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
                    disableEditing: this.rootProject.tendering_module_locked[0],
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
                        currentGridType: 'item'
                    }
                });
            }catch(e){console.debug(e);}

        },
        createScheduleOfRateBillElementGrid: function(bill, tenderInfo){
            this.billId = bill.id;

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
                    id: 'sorb_element-page-container-' + bill.id,
                    gridOpts: {
                        store: store,
                        tender_setting: tenderInfo.tender_setting,
                        tender_companies: tenderInfo.tender_companies,
                        onRowDblClick: function(e) {
                            var self = this,
                                item = self.getItem(e.rowIndex);
                            if(item && parseInt(String(item.id)) > 0 && String(item.description) !== null && String(item.description) !== '') {
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

            var store = new dojo.data.ItemFileWriteStore({
                url:"viewTenderer/getScheduleOfRateBillItemList/id/"+element.id,
                clearOnClose: true
            });

            try {
                var grid = new ScheduleOfRateBillGrid({
                    stackContainerTitle: element.description,
                    billId: bill.id,
                    disableEditing: this.rootProject.tendering_module_locked[0],
                    rootProject: this.rootProject,
                    id: 'sorb_item-page-container-' + bill.id,
                    elementId: element.id,
                    pageId: 'sorb_item-page-' + bill.id,
                    type: 'tree',
                    gridOpts: {
                        store: store,
                        elementGridStore: elementGridStore,
                        tender_setting: tenderInfo.tender_setting,
                        tender_companies: tenderInfo.tender_companies,
                        updateUrl: 'viewTenderer/scheduleOfRateBillItemUpdate',
                        currentGridType: 'item'
                    }
                });
            }catch(e){console.debug(e);}

        }
    });
});