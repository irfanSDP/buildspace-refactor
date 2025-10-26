define('buildspace/apps/ProjectManagement/TagBillItemGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/aspect',
    "dijit/focus",
    "dojo/_base/connect",
    "dojo/when",
    "dojo/dom",
    "dojo/dom-construct",
    'dojo/keys',
    "dojo/dom-style",
    'dojo/currency',
    'dojo/number',
    "dojox/grid/EnhancedGrid",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'buildspace/widget/grid/cells/Textarea',
    "buildspace/widget/grid/cells/Formatter",
    "./ClaimUpdateDialog",
    'dojo/i18n!buildspace/nls/ProjectManagement'
], function(declare, lang, aspect, focusUtil, connect, when, dom, domConstruct, keys, domStyle, currency, number, EnhancedGrid, IndirectSelection, cellTextarea, GridFormatter, ClaimUpdateDialog, nls){

    var BillGrid = declare('buildspace.apps.ProjectManagement.BillGrid', EnhancedGrid, {
        style: "border-top:none;",
        type: -1,
        rowSelector: '0px',
        region: 'center',
        projectScheduleId: -1,
        scheduleTaskItem: null,
        tagBillItemGrid: null,
        dialogWidget: null,
        initialCheckboxSelection: false,
        constructor: function(args){
            this.selectedItemIds = [];
            this.unSelectedItemIds = [];

            this.connects = [];

            if(args.type == 'bill_item' || args.type == 'update_total_unit'){
                this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align:center;"}}
            }

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            if(this.type == 'bill_item' || this.type == 'update_total_unit'){
                aspect.after(this, "_onFetchComplete", function() {
                    if ( ! self.initialCheckboxSelection ) {
                        this.store.fetch({query: {selected:true}, queryOptions: {ignoreCase: true}, onComplete: this.markSelectedCheckBoxes, scope: this});

                        self.initialCheckboxSelection = true;
                    }
                });

                this._connects.push(connect.connect(this, 'onCellClick', function(e){
                    self.selectItem(e);
                }));

                this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue){
                    self.toggleAllSelection(newValue);
                }));
            }
        },
        markSelectedCheckBoxes: function(items, request){
            for(var i = 0; i < items.length; i++){
                var itemIndex = items[i]._0;
                this.pushItemIdIntoGridArray(items[i], true);
                this.selection.setSelected(itemIndex, true);
            }
        },
        selectItem: function(e){
            var rowIndex = e.rowIndex,
                selected = this.selection.selected[rowIndex],
                item = this.getItem(rowIndex);

            switch(this.type){
                case "bill_item":
                    if(item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER
                        && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                        && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID
                        && item.grand_total[0] != 0){
                        this.pushItemIdIntoGridArray(item, selected);
                    }
                    break;
                case "update_total_unit":
                    this.pushItemIdIntoGridArray(item, selected);
                    break;
            }
        },
        pushItemIdIntoGridArray: function(item, selected){
            var grid = this,
                selectedIdx = dojo.indexOf(grid.selectedItemIds, item.id[0]),
                unSelectedIdx = dojo.indexOf(grid.unSelectedItemIds, item.id[0]);

            if(selected){
                if(selectedIdx == -1){
                    grid.selectedItemIds.push(item.id[0]);
                }

                if(unSelectedIdx != -1){
                    grid.unSelectedItemIds.splice(unSelectedIdx, 1);
                }
            }else{
                if(selectedIdx != -1){
                    grid.selectedItemIds.splice(selectedIdx, 1);
                }

                if(unSelectedIdx == -1){
                    grid.unSelectedItemIds.push(item.id[0]);
                }
            }
        },
        toggleAllSelection:function(checked){
            var grid = this, selection = grid.selection;
            if(checked){
                selection.selectRange(0, grid.rowCount-1);
                grid.selectedItemIds = [];
                grid.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item){
                                switch(grid.type){
                                    case "bill_item":
                                        if(item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER
                                            && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                                            && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID
                                            && item.grand_total[0] != 0){
                                            grid.selectedItemIds.push(item.id[0]);
                                        }
                                        break;
                                    case "update_total_unit":
                                        grid.selectedItemIds.push(item.id[0]);
                                        break;
                                }
                            }
                        });
                    }
                });
                grid.unSelectedItemIds = [];
            }else{
                grid.unSelectedItemIds = [];
                grid.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item){
                                switch(grid.type){
                                    case "bill_item":
                                        if(item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER
                                            && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                                            && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID
                                            && item.grand_total[0] != 0){
                                            grid.unSelectedItemIds.push(item.id[0]);
                                        }
                                        break;
                                    case "update_total_unit":
                                        grid.unSelectedItemIds.push(item.id[0]);
                                        break;
                                }
                            }
                        });
                    }
                });
                selection.deselectAll();
                grid.selectedItemIds = [];
            }
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        },
        tagBillItems: function(typeReferenceUnit){
            var itemId = (this.scheduleTaskItem.hasOwnProperty("idFromDB") && !isNaN(parseInt(this.scheduleTaskItem.idFromDB))) ? this.scheduleTaskItem.idFromDB : this.scheduleTaskItem.id;

            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                }),
                xhrArgs = {
                    url: 'projectManagement/tagBillItems',
                    content: {
                        id: itemId,
                        uid: typeReferenceUnit.id,
                        sid: [this.selectedItemIds],
                        usid: [this.unSelectedItemIds],
                        _csrf_token: this.scheduleTaskItem._csrf_token
                    },
                    handleAs: 'json',
                    load: function(resp){
                        if(resp.success){
                            if(self.tagBillItemGrid){
                                self.tagBillItemGrid.store.save();
                                self.tagBillItemGrid.store.close();
                                self.tagBillItemGrid.refreshGrid();
                            }
                        }

                        self.selectedItemIds = [];
                        self.unSelectedItemIds = [];
                        pb.hide();
                        self.dialogWidget.hide();
                    },
                    error: function(error) {
                        self.selectedItemIds = [];
                        self.unSelectedItemIds = [];
                        pb.hide();
                        self.dialogWidget.hide();
                    }
                };

            if(this.selectedItemIds.length == 0){
                var onYes = function(){
                    pb.show().then(function(){
                        dojo.xhrPost(xhrArgs);
                    });
                };

                var content = '<div>'+nls.areYouSureToRemoveAllTaggedBillItems+'</div>';
                buildspace.dialog.confirm(nls.confirmation,content,75,280, onYes);
            }else{
                pb.show().then(function(){
                    dojo.xhrPost(xhrArgs);
                });
            }
        },
        updateTotalUnits: function(scheduleTaskItemBillItem){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                }),
                xhrArgs = {
                    url: 'projectManagement/totalUnitsUpdate',
                    content: {
                        id: scheduleTaskItemBillItem.id,
                        sid: [this.selectedItemIds],
                        usid: [this.unSelectedItemIds],
                        _csrf_token: this.scheduleTaskItem._csrf_token
                    },
                    handleAs: 'json',
                    load: function(resp){
                        if(resp.success){
                            if(self.tagBillItemGrid){
                                self.tagBillItemGrid.store.save();
                                self.tagBillItemGrid.store.close();
                                self.tagBillItemGrid.refreshGrid();
                            }
                        }

                        self.selectedItemIds = [];
                        self.unSelectedItemIds = [];
                        pb.hide();
                        self.dialogWidget.hide();
                    },
                    error: function(error) {
                        self.selectedItemIds = [];
                        self.unSelectedItemIds = [];
                        pb.hide();
                        self.dialogWidget.hide();
                    }
                };

            if(this.selectedItemIds.length == 0){
                var onYes = function(){
                    pb.show().then(function(){
                        dojo.xhrPost(xhrArgs);
                    });
                };

                var content = '<div>'+nls.areYouSureToRemoveAllTaggedUnits+'</div>';
                buildspace.dialog.confirm(nls.confirmation,content,75,280, onYes);
            }else{
                pb.show().then(function(){
                    dojo.xhrPost(xhrArgs);
                });
            }
        }
    });

    var BillGridContainer = declare('buildspace.apps.ProjectManagement.BillGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        projectScheduleId: -1,
        scheduleTaskItem: null,
        typeReferenceUnit: null,
        scheduleTaskItemBillItem: null,
        tagBillItemGrid: null,
        dialogWidget: null,
        gridOpts: {},
        pageId: 0,
        type: -1,
        postCreate: function(){
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                scheduleTaskItem: this.scheduleTaskItem,
                dialogWidget: this.dialogWidget,
                tagBillItemGrid: this.tagBillItemGrid,
                type: this.type
            });

            var grid = this.grid = new BillGrid(this.gridOpts),
                toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    onClick: dojo.hitch(this.dialogWidget, "hide")
                })
            );

            if(this.type == 'bill_item' || this.type == 'update_total_unit'){
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.save,
                        iconClass: "icon-16-container icon-16-save",
                        style:"outline:none!important;",
                        onClick: this.type == 'bill_item' ? dojo.hitch(grid, "tagBillItems", this.typeReferenceUnit) : dojo.hitch(grid, "updateTotalUnits", this.scheduleTaskItemBillItem)
                    })
                );
            }

            this.addChild(toolbar);

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('tagBillItemGrid-'+this.projectScheduleId+'_'+this.scheduleTaskItem.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 45),
                    id: this.pageId,
                    content: this,
                    grid: grid,
                    executeScripts: true
                },node );
                container.addChild(child);
                container.selectChild(this.pageId);
            }
        }
    });

    var CustomFormatter = {
        billTypeCellFormatter: function(cellValue, rowIdx){
            return buildspace.getBillTypeText(cellValue);
        },
        totalUnitCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            cellValue = parseInt(cellValue) > 0 ? cellValue : "&nbsp;";

            if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                cell.customClasses.push('disable-cell');

                cellValue = '&nbsp;';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }

            return cellValue;
        }
    };

    var BillDialog = declare('buildspace.apps.ProjectManagement.BillDialog', dijit.Dialog, {
        style:"padding:0;margin:0;",
        title: nls.tagBillItems,
        projectScheduleId: -1,
        scheduleTaskItem: null,
        tagBillItemGrid: null,
        type: -1,
        scheduleTaskItemBillItem: null,
        buildRendering: function(){
            this.title = this.type == 'update_total_unit' ? nls.tagUnitsToBillItem : nls.tagBillItems;

            var self = this, store, structure, type, title, formatter = new GridFormatter();
            var onRowDblClickFunc = function(e){}

            if(this.type == 'update_total_unit'){
                store = dojo.data.ItemFileWriteStore({
                    url: "projectManagement/getScheduleTaskItemBillItemUnitList/id/"+this.scheduleTaskItemBillItem.id,
                    clearOnClose: true
                });
                structure = [
                    {name: 'No', field: 'id', styles: "text-align:center;", width: '30px', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.typeListTreeCellFormatter }
                ];
                type = 'update_total_unit';
                title = nls.units;
            }else{
                store = dojo.data.ItemFileWriteStore({
                    url: "projectManagement/getBillList/id/"+this.projectScheduleId,
                    clearOnClose: true
                });
                structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.title, field: 'title', width:'auto' },
                    {name: nls.billType, field: 'bill_type', width:'120px', styles:'text-align:center;', formatter: CustomFormatter.billTypeCellFormatter}
                ];
                onRowDblClickFunc = function(e){
                    var rowIndex = e.rowIndex,
                        bill = this.getItem(rowIndex);

                    if(bill.id > 0){
                        self.createTypeReferenceGrid(bill);
                    }
                };
                type = null;
                title = nls.bills;
            }

            var grid = new BillGridContainer({
                projectScheduleId: this.projectScheduleId,
                scheduleTaskItem: this.scheduleTaskItem,
                tagBillItemGrid: this.tagBillItemGrid,
                dialogWidget: this,
                type: type,
                scheduleTaskItemBillItem: this.scheduleTaskItemBillItem,
                gridOpts: {
                    store: store,
                    structure: structure,
                    onRowDblClick: onRowDblClickFunc
                }
            });

            var content = this.makeGridContainer(grid, title);
            content.startup();
            this.content = content;

            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function(e){
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createTypeReferenceGrid: function(bill){
            var self = this,
                formatter = new GridFormatter();

            var itemId = (this.scheduleTaskItem.hasOwnProperty("idFromDB") && !isNaN(parseInt(this.scheduleTaskItem.idFromDB))) ? this.scheduleTaskItem.idFromDB : this.scheduleTaskItem.id;

            new BillGridContainer({
                stackContainerTitle: bill.title,
                projectScheduleId: this.projectScheduleId,
                scheduleTaskItem: this.scheduleTaskItem,
                tagBillItemGrid: this.tagBillItemGrid,
                dialogWidget: this,
                pageId: "tagBillItemTypeReference-"+this.projectScheduleId+"_"+this.scheduleTaskItem.id,
                gridOpts: {
                    store: dojo.data.ItemFileWriteStore({
                        url: "projectManagement/getTypeReferenceList/id/"+itemId+"/bid/"+bill.id,
                        clearOnClose: true
                    }),
                    structure: [
                        {name: 'No', field: 'id', styles: "text-align:center;", width: '30px', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.typeListTreeCellFormatter }
                    ],
                    onRowDblClick: function(e){
                        var rowIndex = e.rowIndex,
                            typeUnit = this.getItem(rowIndex);

                        if(typeUnit.level > 0){
                            self.createBillElementGrid(typeUnit, bill);
                        }
                    }
                }
            });
        },
        createBillElementGrid: function(typeUnit, bill){
            var self = this,
                formatter = new GridFormatter();

            var itemId = (this.scheduleTaskItem.hasOwnProperty("idFromDB") && !isNaN(parseInt(this.scheduleTaskItem.idFromDB))) ? this.scheduleTaskItem.idFromDB : this.scheduleTaskItem.id;

            new BillGridContainer({
                stackContainerTitle: typeUnit.description,
                projectScheduleId: this.projectScheduleId,
                scheduleTaskItem: this.scheduleTaskItem,
                tagBillItemGrid: this.tagBillItemGrid,
                dialogWidget: this,
                pageId: "tagBillItemElement-"+this.projectScheduleId+"_"+this.scheduleTaskItem.id,
                gridOpts: {
                    store:  dojo.data.ItemFileWriteStore({
                        url: "projectManagement/getBillElementList/id/"+itemId+"/bid/"+bill.id+"/tid/"+typeUnit.id,
                        clearOnClose: true
                    }),
                    structure: [
                        {name: 'No', field: 'id', styles: "text-align:center;", width: '30px', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto' }
                    ],
                    onRowDblClick: function(e){
                        var rowIndex = e.rowIndex,
                            element = this.getItem(rowIndex);

                        if(element.id > 0){
                            self.createBillItemGrid(bill, element, typeUnit);
                        }
                    }
                }
            });
        },
        createBillItemGrid: function(bill, element, typeUnit){
            var itemId = (this.scheduleTaskItem.hasOwnProperty("idFromDB") && !isNaN(parseInt(this.scheduleTaskItem.idFromDB))) ? this.scheduleTaskItem.idFromDB : this.scheduleTaskItem.id;

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "projectManagement/getBillItemList/eid/"+element.id+"/tid/"+typeUnit.id+"/id/"+itemId,
                    clearOnClose: true
                });
            new BillGridContainer({
                stackContainerTitle: element.description,
                projectScheduleId: this.projectScheduleId,
                scheduleTaskItem: this.scheduleTaskItem,
                tagBillItemGrid: this.tagBillItemGrid,
                typeReferenceUnit: typeUnit,
                dialogWidget: this,
                pageId: "tagBillItemItem-"+this.projectScheduleId+"_"+this.scheduleTaskItem.id,
                type: 'bill_item',
                gridOpts: {
                    store: store,
                    escapeHTMLInData: false,
                    structure: [
                        {name: nls.billReference, field: 'bill_ref', styles: "text-align:center;color:red;", width: '80px', formatter: formatter.unEditableCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                        {name: nls.type, field: 'type', width:'70px', styles: "text-align:center;", formatter: formatter.typeCellFormatter },
                        {name: nls.grandTotal, field: 'grand_total', styles: "text-align:right;color:blue;", width: '100px', formatter: formatter.unEditableCurrencyCellFormatter, noresize: true}
                    ]
                }
            });
        },
        makeGridContainer: function(content, title){
            var id = this.projectScheduleId+'_'+this.scheduleTaskItem.id,
                stackContainer = dijit.byId('tagBillItemGrid-'+id+'-stackContainer');

            if(stackContainer){
                dijit.byId('tagBillItemGrid-'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:none;',
                region: "center",
                id: 'tagBillItemGrid-'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'tagBillItemGrid-'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0;margin:0;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;margin:0;width:780px;height:420px;border:0px;",
                gutters: false
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('tagBillItemGrid-'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('tagBillItemGrid-'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    if(children.length > index + 1){
                        page.grid.store.save();
                        page.grid.store.close();

                        var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                            handle.remove();
                            this.scrollToRow(this.selection.selectedIndex);
                        });

                        page.grid._refresh();
                    }

                    while(children.length > index+1 ){
                        index = index + 1;
                        widget.removeChild(children[ index ]);
                        children[ index ].destroyRecursive();
                    }
                }
            });

            return borderContainer;
        }
    });

    return declare('buildspace.apps.ProjectManagement.TagBillItemGrid', EnhancedGrid, {
        escapeHTMLInData: false,
        style: "border-top:none;",
        keepSelection: true,
        scheduleTaskItem: null,
        id: 'taggedBillItemEnhancedGrid',
        constructor: function(configuration, node){
            this.structure = this.constructStructure();
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on('RowClick', function(e) {
                var item = self.getItem(e.rowIndex);
                if(item && item.id > 0 && item.hasOwnProperty('type') && item.type > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID) {
                    self.disableToolbarButtons(false);
                }else{
                    self.disableToolbarButtons(true);
                }
            });

            var self = this;

            var handle = aspect.after( this, "_onFetchComplete", function() {
                handle.remove();
                self.getTotalDurationAndProductivity();
            } );
        },
        _refresh: function(isRender){
            this._clearData();
            this._fetch(0, isRender);

            var _grid = this;
            //fix weird issue when refreshing the grid, it always ended up focusing at the tab instead of the grid
            var handle = focusUtil.on("widget-focus", function(widget){
                if(widget.id == "TaskEditor-TabContainer_tablist_tabChild-billItemList"){
                    focusUtil.focus(dom.byId("taggedBillItemEnhancedGrid"));

                    handle.remove();

                    if (_grid.focus && _grid.focus.rowIndex >= 0 && _grid.focus.cell.index >= 0) {
                        _grid.focus.setFocusIndex(_grid.focus.rowIndex, _grid.focus.cell.index);
                    }
                }
            });
        },
        getTotalDurationAndProductivity: function(){
            var store = this.store;
            store.fetch({
                onComplete: function (items) {
                    var totalDur = 0;
                    var totalClaimAmt = 0;
                    var totalContractAmt = 0;
                    var duration    = 0
                    var claim       = 0;
                    var contractAmt = 0;

                    dojo.forEach(items, function(i){
                        duration    = number.parse(store.getValue(i, "duration_days"));
                        claim       = number.parse(store.getValue(i, "up_to_date_claim_amount"));
                        contractAmt = number.parse(store.getValue(i, "contract_amt"));

                        if(!isNaN(duration))
                            totalDur +=duration;

                        if(!isNaN(claim))
                            totalClaimAmt +=claim;

                        if(!isNaN(contractAmt))
                            totalContractAmt +=contractAmt;
                    });

                    dom.byId('totalDurationNode').textContent = number.format(totalDur, {places:2});

                    var completionPercentage = totalContractAmt != 0 ? totalClaimAmt/totalContractAmt * 100 : 0;

                    dom.byId('completionPercentageNode').textContent = number.format(completionPercentage, {places:2});

                    dom.byId('contractAmountNode').textContent = number.format(totalContractAmt, {places:2});
                }
            });
        },
        refreshGrid: function(){
            this.beginUpdate();

            this.store.close();

            var self = this;

            var handle = aspect.after( this, "_onFetchComplete", function() {
                handle.remove();
                self.getTotalDurationAndProductivity();
            } );

            this._refresh();

            this.endUpdate();
        },
        constructStructure: function(){
            var formatter = new GridFormatter();

            var CustomCellFormatter = {
                numberCellFormatter: function(cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx);

                    var value = number.parse(cellValue);
                    if((isNaN(value) || value == 0 || value == null) || (item && item.hasOwnProperty('type') && item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID)){
                        cellValue = "&nbsp;";
                    }else{
                        var formattedValue = number.format(value, {places:2});

                        cellValue = value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
                    }

                    if(item && !item.hasOwnProperty('type') || (item.hasOwnProperty('type') && item.type > 0 && item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID)){
                        cell.customClasses.push('disable-cell');
                    }

                    if (item.hasOwnProperty('type') && item.type < 1){
                        cell.customClasses.push('invalidTypeItemCell');
                        cellValue = "&nbsp;";
                    }

                    return cellValue;
                },
                currencyCellFormatter: function(cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx),
                        value = number.parse(cellValue);

                    if (item.hasOwnProperty('type') && item.type < 1){
                        cell.customClasses.push('invalidTypeItemCell');
                    }

                    if(isNaN(value) || value == 0 || value == null){
                        cellValue = "&nbsp;";
                    }else{
                        cellValue = currency.format(value);
                        cellValue = value >= 0 ? cellValue : '<span style="color:#FF0000">'+cellValue+'</span>';
                    }

                    if (item && item.hasOwnProperty('type') && item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                        cell.customClasses.push('disable-cell');
                        cellValue = '&nbsp;';
                    }

                    return cellValue;
                },
                productivityTypeCellFormatter: function(cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx);

                    if(item && item.hasOwnProperty('productivity_type') && item.hasOwnProperty('type') && item.type > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                        switch(item.productivity_type[0]){
                            case buildspace.constants.PROJECT_SCHEDULE_PRODUCTIVITY_TYPE_UNIT_PER_HOUR:
                                cellValue = buildspace.constants.PROJECT_SCHEDULE_PRODUCTIVITY_TYPE_UNIT_PER_HOUR_TEXT;
                                break;
                            default:
                                cellValue = "&nbsp;";
                        }
                    }else if (item && item.hasOwnProperty('type') && item.type < 1){
                        cell.customClasses.push('invalidTypeItemCell');
                        cellValue = "&nbsp;";
                    }else{
                        cell.customClasses.push('disable-cell');
                        cellValue = "&nbsp;";
                    }

                    return cellValue;
                },
                claimAmountCellFormatter: function(cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx),
                        formattedValue,
                        val = '&nbsp;';

                    if (item && item.hasOwnProperty('type') && item.type < 1){
                        cell.customClasses.push('invalidTypeItemCell');
                    }

                    if(item && item.hasOwnProperty('bill_type') && item.bill_type == buildspace.constants.BILL_TYPE_PRELIMINARY){
                        cell.customClasses.push('disable-cell');
                    }

                    if(item.hasOwnProperty('up_to_date_claim_amount') && item.id > 0){
                        if(item.up_to_date_claim_amount[0] == 'MULTI'){
                            val = '<span style="color:#ee9f05;">MULTI&nbsp;&nbsp;&nbsp;</span>';
                        }else{
                            var value = number.parse(item.up_to_date_claim_amount[0]);
                            if(isNaN(value) || value == 0 || value == null){
                                formattedValue = "&nbsp;";
                            }else{
                                formattedValue = currency.format(value);
                            }

                            val = value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';

                            if(item.type > 0 && item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                                cell.customClasses.push('disable-cell');
                                val = '&nbsp;';
                            }
                        }
                    }
                    return val;
                },
                claimPercentageCellFormatter: function(cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx),
                        val = '&nbsp;';

                    if (item && item.hasOwnProperty('type') && item.type < 1){
                        cell.customClasses.push('invalidTypeItemCell');
                    }

                    if(item && item.hasOwnProperty('bill_type') && item.bill_type == buildspace.constants.BILL_TYPE_PRELIMINARY){
                        cell.customClasses.push('disable-cell');
                    }

                    if(item.hasOwnProperty('up_to_date_claim_percentage') && item.id > 0){
                        if(item.up_to_date_claim_percentage[0] == 'MULTI'){
                            val = '<span style="color:#ee9f05;">MULTI&nbsp;&nbsp;&nbsp;</span>';
                        }else{
                            var value = number.parse(item.up_to_date_claim_percentage[0]);
                            if(isNaN(value) || value == 0 || value == null){
                                val = "&nbsp;";
                            }else{
                                var formattedValue = number.format(value, {places:2})+"%";
                                val = value >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
                            }

                            if(item.type > 0 && item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                                cell.customClasses.push('disable-cell');
                                val = '&nbsp;';
                            }
                        }
                    }
                    return val;
                }
            };
            return [{
                cells: [
                    [{name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', rowSpan: 2, formatter: formatter.rowCountCellFormatter },
                        {name: nls.total+' '+nls.unit, field: 'total_unit', width:'70px', rowSpan: 2, styles:'text-align:center;', formatter: CustomFormatter.totalUnitCellFormatter },
                        {name: nls.description, field: 'description', width:'680px', rowSpan: 2, formatter: formatter.analysisTreeCellFormatter },
                        {name: nls.unit, field: 'uom_symbol', width:'70px', rowSpan: 2, styles:'text-align:center;', formatter: formatter.unitIdCellFormatter },
                        {name: nls.qty+'/'+nls.unit, field: 'qty_per_unit', width:'80px', rowSpan: 2, styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                        {name: nls.total+' '+nls.qty, field: 'total_qty', width:'80px', rowSpan: 2, styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                        {
                            field:'productivity',
                            name:nls.productivity,
                            width:'80px',
                            styles: "text-align:center;",
                            rowSpan: 2,
                            editable:true,
                            cellType:'buildspace.widget.grid.cells.Textarea',
                            formatter: CustomCellFormatter.numberCellFormatter
                        },{
                        field:'productivity_type',
                        name:nls.productivityType,
                        width:'120px',
                        styles: "text-align:center;",
                        rowSpan: 2,
                        formatter: CustomCellFormatter.productivityTypeCellFormatter
                    },{
                        name: nls.noOfGang,
                        field: 'number_of_gang',
                        width: '60px',
                        styles: "text-align:center;",
                        rowSpan: 2,
                        editable:true,
                        cellType:'buildspace.widget.grid.cells.Textarea',
                        formatter: CustomCellFormatter.numberCellFormatter
                    },{
                        name: nls.hours,
                        field: "duration_hours",
                        width: '70px',
                        editable:true,
                        styles: "text-align:center;",
                        cellType:'buildspace.widget.grid.cells.Textarea',
                        formatter: CustomCellFormatter.numberCellFormatter
                    },{
                        name: nls.days,
                        field: "duration_days",
                        width: '70px',
                        editable:true,
                        styles: "text-align:center;",
                        cellType:'buildspace.widget.grid.cells.Textarea',
                        formatter: CustomCellFormatter.numberCellFormatter
                    },{
                        field:'contract_amt',
                        name:nls.contractAmount,
                        width:'120px',
                        styles: "text-align:right;",
                        rowSpan: 2,
                        formatter: formatter.unEditableCurrencyCellFormatter
                    },{
                        field:'up_to_date_claim_percentage',
                        name: '%',
                        width:'70px',
                        styles: "text-align:right;",
                        editable: true,
                        formatter: CustomCellFormatter.claimPercentageCellFormatter
                    },{
                        field:'up_to_date_claim_amount',
                        name:nls.amount,
                        width:'120px',
                        styles: "text-align:right;",
                        editable: true,
                        formatter: CustomCellFormatter.claimAmountCellFormatter
                    }],
                    [{
                        name: nls.duration,
                        styles:'text-align:center;',
                        noresize: true,
                        colSpan: 2,
                        headerClasses: "staticHeader "
                    },{
                        name: nls.actualWorkDone,
                        styles:'text-align:center;',
                        noresize: true,
                        colSpan: 2,
                        headerClasses: "staticHeader "
                    }]
                ]
            }];
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this,
                item = this.getItem(rowIdx),
                store = this.store,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
            var cell;

            if( inAttrName == 'up_to_date_claim_percentage' || inAttrName == 'up_to_date_claim_amount' ) {
                cell = this.getCellByField(inAttrName);
                pb.show().then( function() {
                    dojo.xhrPost( {
                        url: "projectManagement/updateClaim",
                        content: {
                            id: item.id[ 0 ],
                            attr_name: inAttrName,
                            val: val,
                            t: (inAttrName == 'up_to_date_claim_percentage') ? 'p' : 'a',
                            _csrf_token: item._csrf_token ? item._csrf_token : null
                        },
                        handleAs: 'json',
                        load: function(resp) {
                            if( resp.success ) {
                                if( item.id > 0 ) {
                                    for( var property in resp.data ) {
                                        if( property != store._getIdentifierAttribute() && item.hasOwnProperty( property ) ) {
                                            store.setValue( item, property, resp.data[ property ] );
                                        }
                                    }
                                    store.save();
                                }

                                window.setTimeout( function() {
                                    self.focus.setFocusIndex( rowIdx, cell.index );
                                }, 10 );

                                pb.hide();
                            }
                            self.refreshGrid();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    } );
                } );
                return;
            }

            if(val !== item[inAttrName][0] && item.id > 0){
                cell = this.getCellByField(inAttrName);

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: "projectManagement/taggedBillItemUpdate",
                        content: {
                            id: item.id[0],
                            attr_name: inAttrName,
                            val: val,
                            _csrf_token: item._csrf_token ? item._csrf_token : null
                        },
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                if(item.id > 0){
                                    for(var property in resp.data){
                                        if(property != store._getIdentifierAttribute() && item.hasOwnProperty(property)){
                                            store.setValue(item, property, resp.data[property]);
                                        }
                                    }
                                    store.save();
                                }

                                window.setTimeout(function() {
                                    self.focus.setFocusIndex(rowIdx, cell.index);
                                }, 10);

                                pb.hide();
                            }
                            self.refreshGrid();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        canEdit: function(inCell, inRowIndex){
            if(inCell != undefined){
                var item = this.getItem(inRowIndex);
                if (item && item.id > 0 && item.hasOwnProperty('type') && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                    if(inCell.field == 'up_to_date_claim_percentage' || inCell.field == 'up_to_date_claim_amount'){
                        if(item.hasOwnProperty('bill_type') && item.bill_type == buildspace.constants.BILL_TYPE_PRELIMINARY)
                            return false
                        else
                            return true;
                    }else{
                        return this._canEdit;
                    }
                }
            }
            return false;
        },
        openBillDialog: function(){
            var dialog = new BillDialog({
                projectScheduleId: this.projectSchedule.id[ 0 ],
                scheduleTaskItem: this.scheduleTaskItem,
                tagBillItemGrid: this
            });

            dialog.show();
        },
        dodblclick: function(e){
            var colField = e.cell.field,
                rowIndex = e.rowIndex,
                item = this.getItem( rowIndex );

            if( !(item && item.id > 0 && item.hasOwnProperty( 'type' ) && item.type[ 0 ] > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID) ) {
                return;
            }

            if( colField == "total_unit" ) {
                var billDialog = new BillDialog( {
                    type: 'update_total_unit',
                    scheduleTaskItemBillItem: item,
                    projectScheduleId: this.projectSchedule.id[ 0 ],
                    scheduleTaskItem: this.scheduleTaskItem,
                    tagBillItemGrid: this
                } );
                billDialog.show();
            }

            if( (colField == 'up_to_date_claim_percentage' || colField == 'up_to_date_claim_amount') && item.hasOwnProperty('bill_type') && item.bill_type != buildspace.constants.BILL_TYPE_PRELIMINARY ) {

                var claimUpdateDialog = new ClaimUpdateDialog( {
                    scheduleTaskItemBillItem: item,
                    type: colField,
                    tagBillItemGrid: this,
                    title: colField == 'up_to_date_claim_percentage' ? nls.actualWorkDone + " (%)" : nls.actualWorkDone + " (" + nls.amount + ")"
                } );

                claimUpdateDialog.show();
            }
        },
        deleteRow: function(item){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });

            var onYes = function(){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'projectManagement/taggedBillItemDelete',
                        content: {id: item.id, _csrf_token: item._csrf_token},
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                self.store.save();
                                self.store.close();
                                self.sort();
                            }
                            pb.hide();
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                        },
                        error: function(error) {
                            pb.hide();
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                        }
                    });
                });
            };

            buildspace.dialog.confirm(nls.confirmation,nls.deleteTaggedBillItemAndAllData,90,310, onYes);
        },
        onStyleRow: function(e) {
            this.inherited(arguments);

            if(e.node.children[0]){
                if(e.node.children[0].children[0].rows.length >= 2){
                    var elemToHide = e.node.children[0].children[0].rows[1],
                        childElement = e.node.children[0].children[0].rows[0].children;

                    elemToHide.parentNode.removeChild(elemToHide);

                    dojo.forEach(childElement, function(child, i){
                        var rowSpan = dojo.attr(child, 'rowSpan');

                        if(!rowSpan || rowSpan < 2)
                            dojo.attr(child, 'rowSpan', 2);
                    });
                }
            }
        },
        disableToolbarButtons: function(isDisable) {
            var deleteRowBtn = dijit.byId('ProjectManagement'+this.scheduleTaskItem.id+'-TaggedBillItemDeleteRow-button');
            if(deleteRowBtn)
                deleteRowBtn._setDisabledAttr(isDisable);
        }
    });

});