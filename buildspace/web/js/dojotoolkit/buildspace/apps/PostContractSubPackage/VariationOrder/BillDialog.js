define('buildspace/apps/PostContractSubPackage/VariationOrder/BillDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/aspect",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/IndirectSelection",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, lang, connect, aspect, html, dom, keys, domStyle, EnhancedGrid, IndirectSelection, GridFormatter, nls){

    var BillGrid = declare('buildspace.apps.PostContractSubPackage.VariationOrder.BillGrid', EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        region: 'center',
        locked: false,
        variationOrder: null,
        variationOrderItem: null,
        variationOrderItemGrid: null,
        bill: null,
        dialogWidget: null,
        updateUrl: null,
        initialCheckboxSelection: false,
        constructor: function(args){
            this.selectedItemIds = [];
            this.unSelectedItemIds = [];

            this.connects = [];
            if(!args.locked && (args.type == 'update_total_unit-type_reference' || args.type == 'omit_from_bill-bill_item')){
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
        doclick: function(e){
            if(this.locked && (this.type == 'update_total_unit-type_reference' || this.type == 'omit_from_bill-bill_item')){
                return false;
            }

            this.inherited(arguments);
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            if(this.type == 'update_total_unit-type_reference' || this.type == 'omit_from_bill-bill_item'){
                aspect.after(this, "_onFetchComplete", function() {
                    if ( ! self.initialCheckboxSelection ) {
                        this.store.fetch({query: {selected:true}, queryOptions: {ignoreCase: true}, onComplete: this.markSelectedCheckBoxes, scope: this});

                        self.initialCheckboxSelection = true;
                    }
                });
            }

            if(!this.locked){
                this._connects.push(connect.connect(this, 'onCellClick', function(e){
                    self.selectItem(e);
                }));

                this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue){
                    self.toggleAllSelection(newValue);
                }));
            }
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this,
                item = self.getItem(rowIdx),
                store = self.store,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            if(val !== item[inAttrName][0]){
                pb.show();
                dojo.xhrPost({
                    url: this.updateUrl,
                    content: {
                        id: item.id,
                        attr_name: inAttrName,
                        val: val,
                        _csrf_token: item._csrf_token ? item._csrf_token : null
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            for(var property in resp.data){
                                if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                    store.setValue(item, property, resp.data[property]);
                                }
                            }

                            store.save();

                            var cell = self.getCellByField(inAttrName);
                            window.setTimeout(function() {
                                self.focus.setFocusIndex(rowIdx, cell.index);
                            }, 10);

                            pb.hide();
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });

            }

            self.inherited(arguments);
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

            if(item && (this.type == "update_total_unit-type_reference" && item.level[0] > 0) ||
                (this.type == "omit_from_bill-bill_item" && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER
                    && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                    && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID
                    && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE)){
                this.pushItemIdIntoGridArray(item, selected);
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
                            if(item && (grid.type == "update_total_unit-type_reference" && item.level[0] > 0) ||
                                (grid.type == "omit_from_bill-bill_item" && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER
                                    && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                                    && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID
                                    && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE)){
                                grid.selectedItemIds.push(item.id[0]);
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
                            if(item && (grid.type == "update_total_unit-type_reference" && item.level[0] > 0) ||
                                (grid.type == "omit_from_bill-bill_item" && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER
                                    && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                                    && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID
                                    && item.type[0] != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE)){
                                grid.unSelectedItemIds.push(item.id[0]);
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
        omitBillItems: function(typeUnit, isLastItem){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                }),
                xhrArgs = {
                    url: 'variationOrder/spOmitBillItems',
                    content: {
                        vid: self.variationOrderItem.id,
                        void: self.variationOrder.id,
                        last_item: isLastItem,
                        uid: typeUnit.id,
                        sid: [self.selectedItemIds],
                        usid: [self.unSelectedItemIds],
                        _csrf_token: self.variationOrderItem._csrf_token
                    },
                    handleAs: 'json',
                    load: function(resp){
                        if(resp.success){
                            if(self.variationOrderItemGrid){
                                self.variationOrderItemGrid.store.save();
                                self.variationOrderItemGrid.store.close();

                                var handle = aspect.after(self.variationOrderItemGrid, "_onFetchComplete", function() {
                                    handle.remove();
                                    this.scrollToRow(this.selection.selectedIndex);
                                });

                                self.variationOrderItemGrid.sort();
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

            if(self.selectedItemIds.length == 0){
                var onYes = function(){
                    pb.show();
                    dojo.xhrPost(xhrArgs);
                };

                var content = '<div>'+nls.areYouSureToRemoveAllUnitAssignedToVariationOrderItem+'</div>';
                buildspace.dialog.confirm(nls.confirmation,content,60,280, onYes);
            }else{
                pb.show();
                dojo.xhrPost(xhrArgs);
            }
        },
        saveTotalUnit: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                }),
                xhrArgs = {
                    url: 'variationOrder/spTotalUnitUpdate',
                    content: {
                        vid: self.variationOrderItem.id,
                        bid: self.variationOrderItem.bill_item_id[0] > 0 ? self.variationOrderItem.bill_item_id[0] : self.bill.id,
                        ids: [self.selectedItemIds],
                        _csrf_token: self.variationOrderItem._csrf_token
                    },
                    handleAs: 'json',
                    load: function(resp){
                        if(resp.success){
                            if(self.variationOrderItemGrid){

                                if(resp.id == -1){
                                    self.variationOrderItemGrid.disableToolbarButtons(true);
                                }

                                self.variationOrderItemGrid.store.save();
                                self.variationOrderItemGrid.store.close();

                                var handle = aspect.after(self.variationOrderItemGrid, "_onFetchComplete", function() {
                                    handle.remove();
                                    this.scrollToRow(this.selection.selectedIndex);
                                });

                                self.variationOrderItemGrid._refresh();
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
                    pb.show();
                    dojo.xhrPost(xhrArgs);
                };

                var content = '<div>'+nls.areYouSureToRemoveAllUnitAssignedToVariationOrderItem+'</div>';
                buildspace.dialog.confirm(nls.confirmation,content,60,280, onYes);
            }else{
                pb.show();
                dojo.xhrPost(xhrArgs);
            }
        }
    });

    var BillGridContainer = declare('buildspace.apps.PostContractSubPackage.VariationOrder.BillGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        variationOrder: null,
        variationOrderItem: null,
        variationOrderItemGrid: null,
        isLastItem: false,
        bill: null,
        typeUnit: null,
        dialogWidget: null,
        gridOpts: {},
        locked: false,
        type: null,
        pageId: 0,
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {
                type: this.type,
                variationOrder: this.variationOrder,
                variationOrderItem: this.variationOrderItem,
                bill: this.bill,
                dialogWidget: this.dialogWidget,
                variationOrderItemGrid: this.variationOrderItemGrid,
                locked: this.locked
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

            if(!this.locked){
                if(this.type == "update_total_unit-type_reference" || this.type == "omit_from_bill-bill_item"){
                    toolbar.addChild(new dijit.ToolbarSeparator());
                    toolbar.addChild(
                        new dijit.form.Button({
                            label: this.type == "omit_from_bill-bill_item" ? nls.import : nls.save,
                            iconClass:  this.type == "omit_from_bill-bill_item" ? "icon-16-container icon-16-import" : "icon-16-container icon-16-save",
                            onClick: function(){
                                if(self.type == "update_total_unit-type_reference"){
                                    grid.saveTotalUnit();
                                }else if(self.type == "omit_from_bill-bill_item"){
                                    grid.omitBillItems(self.typeUnit, self.isLastItem);
                                }
                            }
                        })
                    );
                }
            }

            this.addChild(toolbar);

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('SP_variationOrderBillGrid-'+this.variationOrderItem.id+'-stackContainer');
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

    return declare('buildspace.apps.PostContractSubPackage.VariationOrder.BillDialog', dijit.Dialog, {
        style:"padding:0;margin:0;",
        title: nls.updateTotalUnit,
        variationOrder: null,
        variationOrderItem: null,
        variationOrderItemGrid: null,
        isLastItem: false,
        type: null,
        locked: false,
        buildRendering: function(){
            this.title = this.type == 'update_total_unit' ? nls.updateTotalUnit : nls.omitFromBills;

            var self = this, updateUrl, type,  store, structure, formatter = new GridFormatter();

            var onRowDblClickFunc = function(e){}

            if(this.type == "update_total_unit" && this.variationOrderItem.bill_item_id[0] > 0){
                store = dojo.data.ItemFileWriteStore({
                    url: "variationOrder/getSpVariationOrderItemUnitList/vid/"+this.variationOrderItem.id,
                    clearOnClose: true
                });
                type = this.type+'-type_reference';
                updateUrl = 'variationOrder/renameUnit';
                structure = [
                    {name: 'No', field: 'id', styles: "text-align:center;", width: '30px', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.typeListTreeCellFormatter },
                    {name: nls.renameDescription, field: 'new_name', width:'150px', styles:'text-align:center;', editable: true, cellType: 'buildspace.widget.grid.cells.Textarea', formatter: formatter.typeListLevelFormatter }
                ];
            }else{
                store = dojo.data.ItemFileWriteStore({
                    url: "variationOrder/getSpBillList/vid/"+this.variationOrderItem.id+"/void/"+this.variationOrder.id,
                    clearOnClose: true
                });
                type = this.type;
                structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.title, field: 'title', width:'auto' }
                ];
                onRowDblClickFunc = function(e){
                    var rowIndex = e.rowIndex,
                        bill = this.getItem(rowIndex);

                    if(bill.id > 0){
                        self.createTypeReferenceGrid(bill);
                    }
                }
            }

            var grid = new BillGridContainer({
                type: type,
                variationOrderItem: this.variationOrderItem,
                variationOrderItemGrid: this.variationOrderItemGrid,
                isLastItem: this.isLastItem,
                dialogWidget: this,
                locked: this.locked,
                gridOpts: {
                    store: store,
                    structure: structure,
                    updateUrl: updateUrl,
                    onRowDblClick: onRowDblClickFunc
                }
            });

            var content = this.makeGridContainer(grid, nls.bills);
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
                formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "variationOrder/getSpTypeReferenceList/void/"+this.variationOrder.id+"/vid/"+this.variationOrderItem.id+"/bid/"+bill.id,
                    clearOnClose: true
                });

            new BillGridContainer({
                stackContainerTitle: bill.title,
                type: this.type+'-type_reference',
                variationOrderItem: this.variationOrderItem,
                variationOrderItemGrid: this.variationOrderItemGrid,
                isLastItem: this.isLastItem,
                bill: bill,
                dialogWidget: this,
                locked: this.locked,
                pageId: "SP_variationOrderTypeReference-"+this.variationOrderItem+"_"+bill.id,
                gridOpts: {
                    store: store,
                    updateUrl: 'variationOrder/renameUnit',
                    structure: [
                        {name: 'No', field: 'id', styles: "text-align:center;", width: '30px', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.typeListTreeCellFormatter },
                        {name: nls.renameDescription, field: 'new_name', width:'150px', styles:'text-align:center;', editable: true, cellType: 'buildspace.widget.grid.cells.Textarea', formatter: formatter.typeListLevelFormatter }
                    ],
                    onRowDblClick: function(e){
                        if(self.type == "omit_from_bill"){
                            var rowIndex = e.rowIndex,
                                typeUnit = this.getItem(rowIndex);

                            if(typeUnit.level > 0){
                                self.createBillElementGrid(typeUnit, bill);
                            }
                        }
                    }
                }
            });
        },
        createBillElementGrid: function(typeUnit, bill){
            var self = this,
                formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "variationOrder/getSpBillElementList/bid/"+bill.id+"/tid/"+typeUnit.id+"/void/"+this.variationOrder.id,
                    clearOnClose: true
                });

            new BillGridContainer({
                stackContainerTitle: typeUnit.description,
                type: this.type+'-bill_element',
                variationOrderItem: this.variationOrderItem,
                isLastItem: this.isLastItem,
                bill: bill,
                dialogWidget: this,
                locked: this.locked,
                pageId: "SP_variationOrderBillElement-"+this.variationOrderItem+"_"+bill.id,
                gridOpts: {
                    store: store,
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
            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "variationOrder/getSpBillItemList/eid/"+element.id+"/tid/"+typeUnit.id+"/void/"+this.variationOrder.id+"/vid/"+this.variationOrderItem.id,
                    clearOnClose: true
                });
            new BillGridContainer({
                stackContainerTitle: element.description,
                type: this.type+'-bill_item',
                variationOrder: this.variationOrder,
                variationOrderItem: this.variationOrderItem,
                variationOrderItemGrid: this.variationOrderItemGrid,
                isLastItem: this.isLastItem,
                bill: bill,
                typeUnit: typeUnit,
                dialogWidget: this,
                locked: this.locked,
                pageId: "SP_variationOrderBillItem-"+this.variationOrderItem+"_"+bill.id,
                gridOpts: {
                    store: store,
                    updateUrl: 'variationOrder/renameUnit',
                    structure: [
                        {name: nls.billReference, field: 'bill_ref', styles: "text-align:center;color:red;", width: '80px', formatter: formatter.unEditableCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                        {name: nls.type, field: 'type', width:'70px', styles: "text-align:center;", formatter: formatter.typeCellFormatter },
                        {name: nls.unit, field: 'uom_id', width:'70px', styles: "text-align:center;", formatter: formatter.unitIdCellFormatter },
                        {name: nls.qty, field: 'qty_per_unit-value', width:'90px', styles: "text-align:right;", formatter: formatter.billQuantityCellFormatter },
                        {name: nls.rate, field: 'rate', width:'120px', styles: "text-align:right;", formatter: formatter.unEditableCurrencyCellFormatter }
                    ]
                }
            });
        },
        makeGridContainer: function(content, title){
            var id = this.variationOrderItem.id,
                stackContainer = dijit.byId('SP_variationOrderBillGrid-'+id+'-stackContainer');

            if(stackContainer){
                dijit.byId('SP_variationOrderBillGrid-'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'SP_variationOrderBillGrid-'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'SP_variationOrderBillGrid-'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var width = this.type == "omit_from_bill" ? "950px" : "850px",
                borderContainer = new dijit.layout.BorderContainer({
                    style:"padding:0;margin:0;width:"+width+";height:450px;border:0px;",
                    gutters: false
                });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('SP_variationOrderBillGrid-'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('SP_variationOrderBillGrid-'+id+'-stackContainer');
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
});