define('buildspace/apps/PostContractReport/VariationOrder/BillDialog',[
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

    var BillGrid = declare('buildspace.apps.PostContractReport.VariationOrder.BillGrid', EnhancedGrid, {
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
                this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align:center;"}};
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
        }
    });

    var BillGridContainer = declare('buildspace.apps.PostContractReport.VariationOrder.BillGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        variationOrder: null,
        variationOrderItem: null,
        variationOrderItemGrid: null,
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
            lang.mixin(self.gridOpts, {type: self.type, variationOrder: self.variationOrder, variationOrderItem: self.variationOrderItem, bill: self.bill, dialogWidget: self.dialogWidget, variationOrderItemGrid: self.variationOrderItemGrid, locked: self.locked });

            var grid = this.grid = new BillGrid(self.gridOpts),
                toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    onClick: dojo.hitch(this.dialogWidget, "hide")
                })
            );

            this.addChild(toolbar);
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('variationOrderBillGrid-'+self.variationOrderItem.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                lang.mixin(child, {grid: grid});
                container.selectChild(self.pageId);
            }
        }
    });

    return declare('buildspace.apps.PostContractReport.VariationOrder.BillDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.updateTotalUnit,
        variationOrder: null,
        variationOrderItem: null,
        variationOrderItemGrid: null,
        type: null,
        locked: false,
        buildRendering: function(){
            this.title = this.type == 'update_total_unit' ? nls.updateTotalUnit : nls.omitFromBills;

            var self = this, type,  store, structure, formatter = new GridFormatter();

            var onRowDblClickFunc = function(e){};

            if(this.type == "update_total_unit" && this.variationOrderItem.bill_item_id[0] > 0){
                store = dojo.data.ItemFileWriteStore({
                    url: "variationOrder/getVariationOrderItemUnitList/vid/"+this.variationOrderItem.id,
                    clearOnClose: true
                });
                type = this.type+'-type_reference';
                structure = [
                    {name: 'No', field: 'id', styles: "text-align:center;", width: '30px', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.typeListTreeCellFormatter },
                    {name: nls.renameDescription, field: 'new_name', width:'150px', styles:'text-align:center;', formatter: formatter.typeListLevelFormatter }
                ];
            }else{
                store = dojo.data.ItemFileWriteStore({
                    url: "variationOrder/getBillList/vid/"+this.variationOrderItem.id+"/void/"+this.variationOrder.id,
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
                };
            }

            var grid = new BillGridContainer({
                type: type,
                variationOrderItem: this.variationOrderItem,
                variationOrderItemGrid: this.variationOrderItemGrid,
                dialogWidget: this,
                locked: this.locked,
                gridOpts: {
                    store: store,
                    structure: structure,
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
            key = e.keyCode;
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
                    url: "variationOrder/getTypeReferenceList/vid/"+this.variationOrderItem.id+"/bid/"+bill.id,
                    clearOnClose: true
                });

            new BillGridContainer({
                stackContainerTitle: bill.title,
                type: this.type+'-type_reference',
                variationOrderItem: this.variationOrderItem,
                variationOrderItemGrid: this.variationOrderItemGrid,
                bill: bill,
                dialogWidget: this,
                locked: this.locked,
                pageId: "variationOrderTypeReference-"+this.variationOrderItem+"_"+bill.id,
                gridOpts: {
                    store: store,
                    updateUrl: 'variationOrder/renameUnit',
                    structure: [
                        {name: 'No', field: 'id', styles: "text-align:center;", width: '30px', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.typeListTreeCellFormatter },
                        {name: nls.renameDescription, field: 'new_name', width:'150px', styles:'text-align:center;', formatter: formatter.typeListLevelFormatter }
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
                    url: "variationOrder/getBillElementList/bid/"+bill.id+"/tid/"+typeUnit.id,
                    clearOnClose: true
                });

            new BillGridContainer({
                stackContainerTitle: typeUnit.description,
                type: this.type+'-bill_element',
                variationOrderItem: this.variationOrderItem,
                bill: bill,
                dialogWidget: this,
                locked: this.locked,
                pageId: "variationOrderBillElement-"+this.variationOrderItem+"_"+bill.id,
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
                    url: "variationOrder/getBillItemList/eid/"+element.id+"/tid/"+typeUnit.id+"/void/"+this.variationOrder.id+"/vid/"+this.variationOrderItem.id,
                    clearOnClose: true
                });
            new BillGridContainer({
                stackContainerTitle: element.description,
                type: this.type+'-bill_item',
                variationOrder: this.variationOrder,
                variationOrderItem: this.variationOrderItem,
                variationOrderItemGrid: this.variationOrderItemGrid,
                bill: bill,
                typeUnit: typeUnit,
                dialogWidget: this,
                locked: this.locked,
                pageId: "variationOrderBillItem-"+this.variationOrderItem+"_"+bill.id,
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
                stackContainer = dijit.byId('variationOrderBillGrid-'+id+'-stackContainer');

            if(stackContainer){
                dijit.byId('variationOrderBillGrid-'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'variationOrderBillGrid-'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'variationOrderBillGrid-'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var width = this.type == "omit_from_bill" ? "950px" : "850px",
                borderContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:"+width+";height:450px;border:0px;",
                    gutters: false
                });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('variationOrderBillGrid-'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('variationOrderBillGrid-'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(), index = dojo.indexOf(children, page);

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