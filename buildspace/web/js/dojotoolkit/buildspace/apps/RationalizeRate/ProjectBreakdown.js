define('buildspace/apps/RationalizeRate/ProjectBreakdown',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dojo/when",
    './BillGrid',
    './lumpSumPercentDialog',
    './primeCostRateDialog',
    'dojo/aspect',
    'dojo/i18n!buildspace/nls/RationalizeRate'
], function(declare, lang, EnhancedGrid, GridFormatter, DropDownButton, DropDownMenu, MenuItem, when, BillGrid, LumpSumPercentDialog, PrimeCostRateDialog, aspect, nls){

    var Grid = declare('buildspace.apps.RationalizeRate.ProjectBreakdownGrid', EnhancedGrid, {
        rootProject: null,
        explorer: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        tender_setting: null,
        tender_companies: null,
        rowSelector: '0px',
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
            var store = this.store;
            this.on("RowClick", function(e){
                var colField = e.cell.field,
                    rowIndex = e.rowIndex,
                    _item = this.getItem(rowIndex);

                if(_item && parseInt(String(_item.id)) > 0 && parseInt(String(_item.type)) == buildspace.apps.RationalizeRate.ProjectStructureConstants.TYPE_BILL){
                    this.disableToolbarButtons(false, ['ExportItemFromBill'], ['ImportDropDown']);
                }else{
                    this.disableToolbarButtons(true);
                }

            }, true);
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
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            var store = this.store;

            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(_this.rootProject.id+label+'Row-button');

                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var GridContainer = declare('buildspace.apps.RationalizeRate.GridContainer', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: '',
        project: null,
        rowSelector: null,
        gridOpts: {},
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, { rootProject:this.project, region:"center", borderContainerWidget: this });
            
            var grid = this.grid = new Grid(this.gridOpts);

            this.addChild(grid);

            var container = dijit.byId('rationalizeRateBreakdown'+this.project.id+'-stackContainer');
            if(container){
                var child = new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    id: this.pageId
                });
                container.addChild(child);
                child.set('content', this);
                container.selectChild(this.pageId);
            }
        }
    });

    var ProjectBreakdown = declare('buildspace.apps.RationalizeRate.ProjectBreakdown', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        type: null,
        explorer: null,
        grid: null,
        postCreate: function(){
            this.inherited(arguments);

            this.reconstructBillContainer();
        },
        reconstructBillContainer: function() {
            var controllerPane = dijit.byId('rationalizeRateBreakdown'+this.rootProject.id+'-controllerPane'),
                stackContainer = dijit.byId('rationalizeRateBreakdown'+this.rootProject.id+'-stackContainer');

            if(controllerPane && stackContainer){
                controllerPane.destroyRecursive();
                stackContainer.destroyRecursive();
            }

            var grid,
            stackContainerTitle;

            if(parseInt(this.rootProject.has_tender_alternative)){
                stackContainerTitle = nls.tenderAlternatives;
                grid = this.createTenderAlternativeGrid();
            }else{
                stackContainerTitle = nls.bills;
                grid = this.createBreakdownGrid();
            }

            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: 'rationalizeRateBreakdown' + this.rootProject.id + '-stackContainer'
            });

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'rationalizeRateBreakdown' + this.rootProject.id + '-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                id: 'rationalizeRateBreakdown'+this.rootProject.id+'-controllerPane',
                content: controller
            });

            this.addChild(stackContainer);
            this.addChild(controllerPane);

            if(grid){
                var child = new dojox.layout.ContentPane( {
                    title: stackContainerTitle,
                    content: grid
                });
    
                stackContainer.addChild(child);
            }
            
            var self = this;

            dojo.subscribe('rationalizeRateBreakdown' + this.rootProject.id + '-stackContainer-selectChild', "", function(page) {
                var widget = dijit.byId('rationalizeRateBreakdown' + self.rootProject.id + '-stackContainer');
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
        },
        createTenderAlternativeGrid: function(){
            var self = this;
            var store = new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"rationalizeRate/getTenderAlternatives/id/"+String(this.rootProject.id)
                });

            var grid = this.grid = Grid({
                rootProject: this.rootProject,
                explorer: this.explorer,
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item && parseInt(String(item.id)) > 0){
                        self.createTenderAlternativeBreakdownGrid(item);
                    }
                },
                store: store
            });

            return grid;
        },
        createTenderAlternativeBreakdownGrid: function(tenderAlternative){
            var self = this;
            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose:true,
                url:"rationalizeRate/getTenderAlternativeBills/id/"+String(tenderAlternative.id)
            });
            var container = new GridContainer({
                stackContainerTitle: tenderAlternative.title,
                project: this.rootProject,
                pageId: 'tender_alternative_bill-page-' + tenderAlternative.id,
                id: 'tender_alternative_bill-page-container-' + tenderAlternative.id,
                gridOpts: {
                    store: store,
                    onRowDblClick: function(e) {
                        var me = this,
                        item = me.getItem(e.rowIndex);

                        if(item && parseInt(String(item.id)) > 0 && parseInt(String(item.type)) == buildspace.constants.TYPE_BILL){
                            self.createElementGrid(item);
                        }
                    }
                }
            });
        },
        createBreakdownGrid: function(){
            var self = this;
            var store = new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"rationalizeRate/getProjectBreakdown/id/"+this.rootProject.id
                });

            var grid = this.grid = Grid({
                rootProject: this.rootProject,
                explorer: this.explorer,
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item && parseInt(String(item.id)) > 0 && parseInt(String(item.type)) == buildspace.constants.TYPE_BILL){
                        self.createElementGrid(item);
                    }
                },
                store: store
            });

            return grid;
        },
        createElementGrid: function(bill){
            this.billId = bill.id;

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

            pb.show().then(function(){
                billInfoQuery.then(function(billInfo) {
                    pb.hide();
                    try {
                        var grid = new BillGrid({
                            stackContainerTitle: bill.title,
                            billId: parseInt(String(bill.id)),
                            rootProject: me.rootProject,
                            pageId: 'element-page-' + bill.id,
                            id: 'element-page-container-' + bill.id,
                            gridOpts: {
                                store: store,
                                typeColumns : billInfo.column_settings,
                                currentGridType: 'element',
                                onRowDblClick: function(e) {
                                    var self = this,
                                        item = self.getItem(e.rowIndex);
                                    if(item && parseInt(String(item.id)) > 0 && String(item.description) !== null && String(item.description) !== '') {
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

            unitQuery.then(function(uom){
                return uom;
            });

            pb.show().then(function(){
                when(unitQuery, function(uom){
                    pb.hide();
                    try{
                        var grid = new BillGrid({
                            stackContainerTitle: element.description,
                            billId: parseInt(String(bill.id)),
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
                                updateUrl: 'rationalizeRate/billItemRateUpdate',
                                unitOfMeasurements: uom,
                                currentGridType: 'item'
                            }
                        });
                    }catch(e){console.debug(e);}
                },function(error){
                    /* got fucked */
                });
            });
            return 
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
            if(item && (parseInt(String(item.id)) == -9999 || parseInt(String(item.type)) < buildspace.apps.RationalizeRate.ProjectStructureConstants.TYPE_BILL)){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        }
    };

    return ProjectBreakdown;
});