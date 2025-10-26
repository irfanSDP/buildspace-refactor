define('buildspace/apps/PostContractSubPackageReport/StandardBillManager',[
    'dojo/_base/declare',
    'dojo/_base/connect',
    "dojo/dom-style",
    "dojo/when",
    'dojo/number',
    "dojo/currency",
    "dijit/layout/ContentPane",
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    "buildspace/apps/ProjectBuilder/BillManager/ScheduleOfQuantityGrid",
    "./BillManager/buildUpQuantityGrid",
    "./BillManager/buildUpQuantitySummary",
    'dojo/request',
    'dojo/aspect',
    'dojo/json',
    'dojo/store/Memory',
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dijit/DropDownMenu',
    'dijit/form/DropDownButton',
    'dijit/MenuItem',
    './BillManager/StandardBillGrid',
    './BillManager/PrintPreviewElementByTypesDialog',
    './BillManager/PrintPreviewElementByTypesByUnitsWithClaimDialog',
    'dojo/i18n!buildspace/nls/PostContract'],
    function(declare, connect, domStyle, when, number, currency, ContentPane, EnhancedGrid, GridFormatter, ScheduleOfQuantityGrid, BuildUpQuantityGrid, BuildUpQuantitySummary, request, aspect, JSON, Memory, IndirectSelection, DropDownMenu, DropDownButton, MenuItem, StandardBillGrid, PrintPreviewElementByTypesDialog, PrintPreviewElementByTypesByUnitsWithClaimDialog, nls) {

        var Formatter = {
            rowCountCellFormatter: function(cellValue, rowIdx, cell){
                var item = this.grid.getItem(rowIdx);

                if (item.type != undefined && item.type < 1)
                {
                    cell.customClasses.push('invalidTypeItemCell');
                }

                return cellValue > 0 ? cellValue : '';
            }
        };

        var Grid = declare('buildspace.apps.PostContractSubPackageReport.TypeGrid', EnhancedGrid, {
            rootProject: null,
            style: "border:none;",
            region: 'center',
            pageId: null,
            billId: null,
            subPackage: null,
            updateUrl: null,
            keepSelection: true,
            rowSelector: '0px',
            constructor:function(args) {
                var formatter = new GridFormatter();

                this.structure = {
                    noscroll: false,
                    cells: [
                        [{
                            name: 'No.',
                            field: 'count',
                            width:'30px',
                            styles:'text-align:center;',
                            formatter: Formatter.rowCountCellFormatter,
                            rowSpan : 2
                        }, {
                            name: nls.description,
                            field: 'description',
                            width:'auto',
                            formatter: formatter.typeListTreeCellFormatter,
                            rowSpan : 2
                        },{
                            name: nls.amount,
                            field: 'total_per_unit',
                            width:'120px',
                            styles:'text-align: right;color:blue;',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            rowSpan : 2
                        },{
                            name: nls.percent,
                            field: 'up_to_date_percentage',
                            width:'120px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.unEditablePercentageCellFormatter,
                            styles:'text-align: right;'
                        },{
                            name: nls.amount,
                            field: 'up_to_date_amount',
                            width:'120px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            styles:'text-align: right;'
                        }],
                        [{
                            name: nls.upToDateClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader1",
                            colSpan : 2
                        }]
                    ]
                };

                this.plugins = {indirectSelection: {headerSelector:true, width:"40px", styles:"text-align: center;"}};

                this.inherited(arguments);
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
            canSort: function(inSortInfo){
                return false;
            },
            dodblclick: function(e){
                this.onRowDblClick(e);
            },
            onStyleRow: function(e) {
                this.inherited(arguments);

                if(e.node.children[0])
                {
                    if(e.node.children[0].children[0].rows.length >= 2)
                    {
                        var elemToHide = e.node.children[0].children[0].rows[1],
                            childElement = e.node.children[0].children[0].rows[0].children;

                        elemToHide.parentNode.removeChild(elemToHide);

                        dojo.forEach(childElement, function(child, i)
                        {
                            var rowSpan = dojo.attr(child, 'rowSpan');

                            if(!rowSpan || rowSpan < 2)
                                dojo.attr(child, 'rowSpan', 2);
                        });
                    }
                }
            },
            reload: function(){
                this.store.close();
                this._refresh();
            },
            singleCheckBoxSelection: function(e) {
                var self = this,
                    rowIndex = e.rowIndex,
                    checked = this.selection.selected[rowIndex],
                    item = this.getItem(rowIndex);

                // used to store removeable selection
                self.removedIds = [];

                if ( checked ) {
                    self.gridContainer.typesPreviewStore.put({ id: item.id[0] });

                    return self.getAffectedElementsAndItemsByBillId(item, 'add');
                } else {
                    self.gridContainer.typesPreviewStore.remove(item.id[0]);

                    self.removedIds.push(item.id[0]);

                    return self.getAffectedElementsAndItemsByBillId(item, 'remove');
                }
            },
            toggleAllSelection: function(checked) {
                var self = this, selection = this.selection;

                // used to store removeable selection
                self.removedIds = [];

                if (checked) {
                    selection.selectRange(0, self.rowCount-1);
                    self.store.fetch({
                        onComplete: function (items) {
                            dojo.forEach(items, function (item, index) {
                                if(item.id) {
                                    self.gridContainer.typesPreviewStore.put({ id: item.id[0] });
                                }
                            });
                        }
                    });

                    return self.getAffectedElementsAndItemsByBillId(null, 'add');
                } else {
                    selection.deselectAll();

                    self.store.fetch({
                        onComplete: function (items) {
                            dojo.forEach(items, function (item, index) {
                                if(item.id) {
                                    self.gridContainer.typesPreviewStore.remove(item.id[0]);

                                    self.removedIds.push(item.id[0]);
                                }
                            });
                        }
                    });

                    return self.getAffectedElementsAndItemsByBillId(null, 'remove');
                }
            },
            getAffectedElementsAndItemsByBillId: function(type, method) {
                var self = this,
                    selectedItemStore = self.gridContainer.typesPreviewStore,
                    types = [];

                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });

                pb.show();

                if (method === 'add') {
                    selectedItemStore.query().forEach(function(item) {
                        types.push(item.id);
                    });
                } else {
                    for (var typeKeyIndex in self.removedIds) {
                        types.push(self.removedIds[typeKeyIndex]);
                    }
                }

                types = types.reverse().filter(function (e, i, arr) {
                    return arr.indexOf(e, i+1) === -1;
                }).reverse();

                pb.show();

                request.post('postContractStandardBillClaimReporting/getAffectedElementsAndItems', {
                    handleAs: 'json',
                    data: {
                        id: self.billId,
                        type_ids: JSON.stringify(types)
                    }
                }).then(function(data) {
                    if ( method === 'add' ) {
                        for (var typeId in data) {
                            if ( ! self.gridContainer.elementPreviewStore[typeId] ) {
                                self.gridContainer.elementPreviewStore[typeId] = new Memory({ idProperty: 'id' });
                            }

                            if ( ! self.gridContainer.itemPreviewStore[typeId] ) {
                                self.gridContainer.itemPreviewStore[typeId] = new Memory({ idProperty: 'id' });
                            }

                            for (var elementId in data[typeId]) {
                                self.gridContainer.elementPreviewStore[typeId].put({ id: elementId });

                                for (var itemIdIndex in data[typeId][elementId]) {
                                    self.gridContainer.itemPreviewStore[typeId].put({ id: data[typeId][elementId][itemIdIndex] });
                                }
                            }
                        }
                    } else {
                        for (var typeKeyIndex in self.removedIds) {
                            self.gridContainer.elementPreviewStore[self.removedIds[typeKeyIndex]] = new Memory({ idProperty: 'id' });
                            self.gridContainer.itemPreviewStore[self.removedIds[typeKeyIndex]]    = new Memory({ idProperty: 'id' });
                        }
                    }

                    pb.hide();
                }, function(error) {
                    pb.hide();
                    console.log(error);
                });
            }
        });

        return declare('buildspace.apps.PostContractSubPackageReport.StandardBillManager', dijit.layout.BorderContainer, {
            style: "padding:0px;border:0px;width:100%;height:100%;",
            gutters: false,
            billId: null,
            subPackage: null,
            rootProject: null,
            typesPreviewStore: [],
            elementPreviewStore: [],
            itemPreviewStore: [],
            constructor: function(args) {
                this.typesPreviewStore   = [];
                this.elementPreviewStore = [];
                this.itemPreviewStore    = [];

                this.inherited(arguments);
            },
            postCreate: function() {
                this.inherited(arguments);
                var self = this;
                self.createTypeGrid();

                dojo.subscribe('subPackagePostContractReportStandardBill' + self.billId + '-stackContainer-selectChild', "", function(page) {
                    var widget = dijit.byId('subPackagePostContractReportStandardBill' + self.billId + '-stackContainer');
                    if(widget) {
                        var children = widget.getChildren();
                        var index = dojo.indexOf(children, dijit.byId(page));

                        index = index + 1;

                        if(children.length > index){
                            while(children.length > index) {
                                widget.removeChild(children[index]);
                                children[index].destroyDescendants();
                                children[index].destroyRecursive();

                                index = index + 1;
                            }
                        }
                    }
                });
            },
            createTypeGrid: function() {
                var self = this;

                var stackContainer = dijit.byId('subPackagePostContractReportStandardBill' + this.billId + '-stackContainer');

                if(stackContainer) {
                    dijit.byId('subPackagePostContractReportStandardBill' + this.billId + '-stackContainer').destroyRecursive();
                }

                stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                    style: 'border:0px;width:100%;height:100%;',
                    region: "center",
                    id: 'subPackagePostContractReportStandardBill' + this.billId + '-stackContainer'
                });

                var billInfoQuery = dojo.xhrGet({
                    url: "postContract/getBillInfo",
                    handleAs: "json",
                    content: {
                        id: this.billId
                    }
                }),
                me = this;

                billInfoQuery.then(function(billInfo) {
                    self.typesPreviewStore = new Memory({ idProperty: 'id' });
                    self.billInfo          = billInfo;

                    try {
                        var store = dojo.data.ItemFileWriteStore({
                            url: 'subPackagePostContractStandardBillClaim/getTypeList/id/' + self.billId + '/sub_package_id/' + self.subPackage.id,
                            clearOnClose: true
                        });

                        var grid = new Grid ({
                            billId: me.billId,
                            rootProject: me.rootProject,
                            subPackage: me.subPackage,
                            pageId: 'type-page-' + me.billId,
                            id: 'postContractReport-type-page-container-' + me.billId,
                            updateUrl: 'subPackagePostContractStandardBillClaim/updateTypeList',
                            store: store,
                            gridContainer: self,
                            onRowDblClick: function(e) {
                                var self = this,
                                    item = self.getItem(e.rowIndex);

                                if(item.level[0] > 0)
                                {
                                    me.createElementGrid(item, billInfo, grid);
                                }
                            }
                        });

                        var borderContainer = new dijit.layout.BorderContainer({
                            style: "padding:0px;width:100%;height:100%;",
                            stackContainerTitle: '',
                            gutters: false
                        });

                        var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important; border-bottom:none; padding:2px; width:100%;"});

                        var printTypeMenu = new DropDownMenu({ style: "display: none;"});

                        var printTypeMenuItemOne = new MenuItem({
                            label: nls.types,
                            onClick: function() {
                                self.openPrintPreviewDialogByTypes();
                            }
                        });
                        printTypeMenu.addChild(printTypeMenuItemOne);

                        var printTypeMenuItemTwo = new MenuItem({
                            label: nls.unitsWithClaimOnly,
                            onClick: function() {
                                self.openPrintPreviewDialogByUnitsWithClaim();
                            }
                        });
                        printTypeMenu.addChild(printTypeMenuItemTwo);

                        var printTypeMenuItemThree = new MenuItem({
                            label: nls.allUnits,
                            onClick: function() {
                                self.openPrintPreviewDialogByAllUnits();
                            }
                        });
                        printTypeMenu.addChild(printTypeMenuItemThree);

                        toolbar.addChild(
                            new DropDownButton({
                                label: nls.type + ' / ' + nls.unit,
                                iconClass: "icon-16-container icon-16-print",
                                dropDown: printTypeMenu
                            })
                        );

                        borderContainer.addChild(toolbar);
                        borderContainer.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

                        var stackPane = new dijit.layout.ContentPane({
                            title: nls.type + ' / ' + nls.unit,
                            content: borderContainer,
                            grid: grid
                        });

                        var controller = new dijit.layout.StackController({
                            region: "top",
                            containerId: 'subPackagePostContractReportStandardBill' + me.billId + '-stackContainer'
                        });

                        stackContainer.addChild(stackPane);

                        var controllerPane = new dijit.layout.ContentPane({
                            style: "padding:0px;overflow:hidden;",
                            baseClass: 'breadCrumbTrail',
                            region: 'top',
                            id: 'billGrid'+me.billId+'-controllerPane',
                            content: controller
                        });

                        me.addChild(stackContainer);
                        me.addChild(controllerPane);
                    }
                    catch(e){
                        console.debug(e);
                    }
                });
            },
            createElementGrid: function (typeItem, billInfo, typeItemGridStore) {
                var self       = this;
                var typeItemId = typeItem.id[0];

                if ( ! self.elementPreviewStore[typeItemId] ) {
                    self.elementPreviewStore[typeItemId] = new Memory({ idProperty: 'id' });
                }

                if ( ! self.itemPreviewStore[typeItemId] ) {
                    self.itemPreviewStore[typeItemId] = new Memory({ idProperty: 'id' });
                }

                //get type Item by With Id
                var typeItemQuery = dojo.xhrGet({
                    url: "subPackagePostContractStandardBillClaim/getTypeItem",
                    handleAs: "json",
                    content: {
                        type_id    : typeItem.relation_id,
                        project_id : self.rootProject.id,
                        counter    : typeItem.count
                    }
                });

                typeItemQuery.then(function(typeItem) {
                    var me = self;

                    var store = dojo.data.ItemFileWriteStore({
                        url: 'subPackagePostContractStandardBillClaim/getBillElementList/id/' + self.billId+"/project_id/"+ self.rootProject.id+"/sub_package_id/"+ self.subPackage.id +"/type_ref_id/"+ typeItem.id ,
                        clearOnClose: true,
                        urlPreventCache: true
                    });

                    var grid = new StandardBillGrid({
                        stackContainerTitle: (typeItem.new_name.length > 0) ? typeItem.relation_name + ' :: ' + typeItem.new_name : typeItem.relation_name + ' :: ' + typeItem.description,
                        billId: self.billId,
                        subPackage: self.subPackage,
                        rootProject: self.rootProject,
                        pageId: 'element-page-' + self.billId,
                        id: 'postContractReport-element-page-container-' + self.billId,
                        typeItem: typeItem,
                        type: 'element',
                        gridOpts: {
                            typeItemId: typeItemId,
                            store: store,
                            typeColumns : billInfo.column_settings,
                            bqCSRFToken: billInfo.bqCSRFToken,
                            claimRevision: billInfo.claim_project_revision_status,
                            selectedClaimRevision: billInfo.current_selected_claim_project_revision_status,
                            currentGridType: 'element',
                            gridContainer: self,
                            onRowDblClick: function(e) {
                                var self = this,
                                    item = self.getItem(e.rowIndex);

                                if(item.id[0] > 0 && item.description[0] !== null && item.description[0] !== '') {
                                    me.createItemGrid(item, typeItemId, typeItem, billInfo, grid);
                                }
                            }
                        }
                    });
                });
            },
            createItemGrid: function(element, typeItemId, typeItem, billInfo, elementGridStore ){
                var billId = this.billId;

                var self = this,
                    store = new dojo.data.ItemFileWriteStore({
                        url:"subPackagePostContractStandardBillClaim/getItemList/id/"+element.id+"/bill_id/"+ billId+"/type_ref_id/"+ element.claim_type_ref_id+"/project_id/"+ self.rootProject.id + '/sub_package_id/' + self.subPackage.id,
                        clearOnClose: true
                    });

                    try{
                        var grid = new StandardBillGrid({
                            stackContainerTitle: element.description,
                            billId: billId,
                            disableEditing: false,
                            rootProject: self.rootProject,
                            subPackage: self.subPackage,
                            id: 'postContractReport-item-page-container-' + billId,
                            elementId: element.id,
                            pageId: 'item-page-' + billId,
                            type: 'tree',
                            typeItem: typeItem,
                            gridOpts: {
                                typeItemId: typeItemId,
                                store: store,
                                escapeHTMLInData: false,
                                elementGridStore: elementGridStore,
                                claimRevision: billInfo.claim_project_revision_status,
                                selectedClaimRevision: billInfo.current_selected_claim_project_revision_status,
                                currentGridType: 'item',
                                gridContainer: self,
                                onRowDblClick: function(e) {
                                    var colField = e.cell.field,
                                        rowIndex = e.rowIndex,
                                        item = this.getItem(rowIndex);

                                    if(colField == 'qty_per_unit'){
                                        if(item[colField+'-has_build_up'][0]){
                                            var billColumnSettingId = typeItem.relation_id;

                                            var dimensionColumnQuery = dojo.xhrPost({
                                                url: "billBuildUpQuantity/getDimensionColumnStructure",
                                                content:{uom_id: item.uom_id[0]},
                                                handleAs: "json"
                                            });
                                            var pb = buildspace.dialog.indeterminateProgressBar({
                                                title:nls.pleaseWait+'...'
                                            });
                                            pb.show();
                                            dimensionColumnQuery.then(function(dimensionColumns){
                                                self.createBuildUpQuantityContainer(item, dimensionColumns, billColumnSettingId);
                                                pb.hide();
                                            });
                                        }
                                    }
                                },
                                editableCellDblClick : function (e)
                                {
                                    var colField = e.cell.field,
                                        rowIndex = e.rowIndex,
                                        item = this.getItem(rowIndex),
                                        billGridStore = this.store,
                                        disableEditingMode = false;

                                    if(colField == "rate" && item.id > 0){

                                        if(number.parse(item.up_to_date_percentage) || number.parse(item.current_percentage) || number.parse(item.prev_percentage)){
                                            disableEditingMode = true;
                                        }

                                        if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT){

                                            var lumpSumPercentDialog = new LumpSumPercentDialog({
                                                itemObj: item,
                                                projectId: self.rootProject.id,
                                                billGridStore: billGridStore,
                                                elementGridStore: elementGridStore,
                                                disableEditingMode: disableEditingMode
                                            });

                                            lumpSumPercentDialog.show();
                                        }

                                    }
                                }
                            }
                        });
                    }catch(e){console.debug(e);}
            },
            createBuildUpQuantityContainer: function(item, dimensionColumns, billColumnSettingId){
                var self = this,
                    scheduleOfQtyGrid,
                    baseContainer = new dijit.layout.BorderContainer({
                        style:"padding:0;margin:0;width:100%;height:100%;border:none;outline:none;",
                        gutters: false
                    }),
                    tabContainer = new dijit.layout.TabContainer({
                        nested: true,
                        style: "padding:0;border:none;margin:0;width:100%;height:100%;",
                        region: 'center'
                    }),
                    type = buildspace.constants.QUANTITY_PER_UNIT_ORIGINAL,
                    formatter = new GridFormatter(),
                    scheduleOfQtyQuery = dojo.xhrGet({
                        url: "billBuildUpQuantity/getLinkInfo/id/"+item.id+"/bcid/"+billColumnSettingId+"/t/"+type,
                        handleAs: "json"
                    }),
                    store = new dojo.data.ItemFileWriteStore({
                        url:"billBuildUpQuantity/getBuildUpQuantityItemList/bill_item_id/"+item.id+"/bill_column_setting_id/"+billColumnSettingId+"/type/"+type,
                        clearOnClose: true
                    }),
                    hasLinkedQty = false,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.pleaseWait+'...'
                    });

                pb.show();

                scheduleOfQtyQuery.then(function(linkInfo){
                    var structure = [{
                        name: 'No',
                        field: 'id',
                        styles: "text-align:center;",
                        width: '30px',
                        formatter: formatter.rowCountCellFormatter
                    }, {
                        name: nls.description,
                        field: 'description',
                        width: 'auto',
                        cellType: 'buildspace.widget.grid.cells.Textarea'
                    },{
                        name: nls.factor,
                        field: 'factor-value',
                        width:'100px',
                        styles:'text-align:right;',
                        formatter: formatter.formulaNumberCellFormatter
                    }];

                    dojo.forEach(dimensionColumns, function(dimensionColumn){
                        var column = {
                            name: dimensionColumn.title,
                            field: dimensionColumn.field_name,
                            width:'100px',
                            styles:'text-align:right;',
                            formatter: formatter.formulaNumberCellFormatter
                        };
                        structure.push(column);
                    });

                    var totalColumn = {
                        name: nls.total,
                        field: 'total',
                        width:'100px',
                        styles:'text-align:right;',
                        formatter: formatter.numberCellFormatter
                    };
                    structure.push(totalColumn);

                    var signColumn = {
                        name: nls.sign,
                        field: 'sign',
                        width: '70px',
                        styles: 'text-align:center;',
                        formatter: formatter.signCellFormatter
                    };
                    structure.push(signColumn);

                    var buildUpSummaryWidget = new BuildUpQuantitySummary({
                        itemId: item.id,
                        billColumnSettingId: billColumnSettingId,
                        type: type,
                        hasLinkedQty: linkInfo.has_linked_qty,
                        container: baseContainer,
                        _csrf_token: item._csrf_token,
                        disableEditingMode: true
                    });

                    if(linkInfo.has_linked_qty){
                        hasLinkedQty = true;
                        scheduleOfQtyGrid = ScheduleOfQuantityGrid({
                            title: nls.scheduleOfQuantities,
                            BillItem: item,
                            billColumnSettingId: billColumnSettingId,
                            disableEditingMode: true,
                            stackContainerId: 'subPackagePostContractReportStandardBill' + self.billId + '-stackContainer',
                            gridOpts: {
                                qtyType: type,
                                buildUpSummaryWidget: buildUpSummaryWidget,
                                store: new dojo.data.ItemFileWriteStore({
                                    url:"billBuildUpQuantity/getScheduleOfQuantities/id/"+item.id+"/bcid/"+billColumnSettingId+"/type/"+type,
                                    clearOnClose: true
                                }),
                                structure: [
                                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                                    {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                                    {name: nls.qty, field: 'quantity-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaNumberCellFormatter}
                                ]
                            }
                        });
                    }

                    var manualGrid = new dojox.grid.EnhancedGrid({
                        title: nls.manualQtyItems,
                        region: 'center',
                        store: store,
                        structure: structure
                    });

                    tabContainer.addChild(manualGrid);

                    if(hasLinkedQty){
                        tabContainer.addChild(scheduleOfQtyGrid);
                    }

                    baseContainer.addChild(tabContainer);
                    baseContainer.addChild(buildUpSummaryWidget);

                    var container = dijit.byId('subPackagePostContractReportStandardBill' + self.billId + '-stackContainer');

                    if(container){
                        var node = document.createElement("div");
                        var child = new dojox.layout.ContentPane( {
                                title: buildspace.truncateString(item.description, 45)+' ('+nls.buildUpQuantity+' - '+item.uom_symbol+')',
                                id: 'subPackageBuildUpQuantityPage-'+item.id,
                                style: "padding:0px;border:0px;",
                                content: baseContainer,
                                grid: hasLinkedQty ? scheduleOfQtyGrid.grid : null,
                                executeScripts: true },
                            node );
                        container.addChild(child);
                        container.selectChild('subPackageBuildUpQuantityPage-'+item.id);
                    }

                    pb.hide();
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
            openPrintPreviewDialogByTypes: function() {
                var self = this, columnSettings = self.billInfo.column_settings;

                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });

                pb.show();

                request.post('postContractSubPackageStandardBill/getPrintingPreviewDataByTypes', {
                    handleAs: 'json',
                    data: {
                        subPackageId: self.subPackage.id,
                        bill_id: self.billId
                    }
                }).then(function(data) {
                    var dialog = new PrintPreviewElementByTypesDialog({
                        project: self.rootProject,
                        title: nls.types,
                        data: data,
                        subPackageId: self.subPackage.id,
                        billId: self.billId,
                        columnSettings: columnSettings
                    });

                    dialog.show();
                    pb.hide();
                }, function(error) {
                    console.log(error);
                    pb.hide();
                });
            },
            openPrintPreviewDialogByUnitsWithClaim: function() {
                var self = this;

                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });

                pb.show();

                request.post('postContractSubPackageStandardBill/getPrintingPreviewDataByUnitsWithClaim', {
                    handleAs: 'json',
                    data: {
                        subPackageId: self.subPackage.id,
                        bill_id: self.billId
                    }
                }).then(function(data) {
                    var dialog = new PrintPreviewElementByTypesByUnitsWithClaimDialog({
                        project: self.rootProject,
                        title: nls.unitsWithClaimOnly,
                        data: data.items,
                        subPackageId: self.subPackage.id,
                        billId: self.billId,
                        columnSettings: data.billColumns,
                        dynamicUnitStructure: data.gridStructure,
                        printURL: 'postContractSubPackageStandardBill/printElementWithClaimByTypes',
                        exportURL: 'postContractSubPackageStandardBillExportExcelReporting/exportExcelElementWithClaimByTypes'
                    });

                    dialog.show();
                    pb.hide();
                }, function(error) {
                    console.log(error);
                    pb.hide();
                });
            },
            openPrintPreviewDialogByAllUnits: function() {
                var self = this, selectedTypeStore = self.typesPreviewStore, types = [];

                selectedTypeStore.query().forEach(function(item) {
                    types.push(item.id);
                });

                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });

                pb.show();

                request.post('postContractSubPackageStandardBill/getPrintingPreviewDataBySelectedUnits', {
                    handleAs: 'json',
                    data: {
                        subPackageId: self.subPackage.id,
                        bill_id: self.billId,
                        itemIds: JSON.stringify(self.arrayUnique(types))
                    }
                }).then(function(data) {
                    var dialog = new PrintPreviewElementByTypesByUnitsWithClaimDialog({
                        project: self.rootProject,
                        title: nls.allUnits,
                        data: data.items,
                        columnSettings: data.billColumns,
                        dynamicUnitStructure: data.gridStructure,
                        selectedRows: self.arrayUnique(types),
                        subPackageId: self.subPackage.id,
                        billId: self.billId,
                        printURL: 'postContractSubPackageStandardBill/printElementWithClaimByTypesBySelectedUnits',
                        exportURL: 'postContractSubPackageStandardBillExportExcelReporting/exportExcelElementWithClaimByTypesBySelectedUnits'
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