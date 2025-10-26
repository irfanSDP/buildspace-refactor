define('buildspace/apps/Tendering/BillManager/importItemDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/aspect",
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/BillManagerImport'
], function(declare, lang, connect, when, html, dom, keys, domStyle, aspect, GridFormatter, IndirectSelection, nls){

    var ImportItemGrid = declare('buildspace.apps.Tendering.BillManager.ImportItemGrid', dojox.grid.EnhancedGrid, {
        type: null,
        selectedItem: null,
        billId: 0,
        billElementId: 0,
        dialogWidget: null,
        _csrf_token: null,
        billGrid: null,
        style: "border-top:none;",
        currentBQAddendumId: -1,
        constructor: function(args){
            this.itemIds = [];
            this.connects = [];
            if(args.type == 'tree'){
                this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align:center;"}}
            }

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex);
                if(self.type == 'tree'){
                    if(item && item.id > 0){
                        self.disableToolbarButtons(false);
                    }else{
                        self.disableToolbarButtons(true);
                    }
                }
            });

            if(this.type == 'tree'){
                this._connects.push(connect.connect(this, 'onCellClick', function(e){
                    self.selectTree(e);
                }));
                this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue){
                    self.toggleAllSelection(newValue);
                }));
            }
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        disableToolbarButtons: function(isDisable){
            var importWithRateBtn = dijit.byId('ImportItemGrid-'+this.billElementId+'_'+this.billId+'ImportWithRate-button');
            importWithRateBtn._setDisabledAttr(isDisable);
            var importWithoutRateBtn = dijit.byId('ImportItemGrid-'+this.billElementId+'_'+this.billId+'ImportWithoutRate-button');
            importWithoutRateBtn._setDisabledAttr(isDisable);
        },
        import: function(withRate){
            var self = this,
                billGrid = self.billGrid,
                itemIds = self.itemIds,
                rowIndex = -1,
                targetId = null;
            if( itemIds.length > 0){
                var itemIndex = billGrid.getItemIndex(this.selectedItem);
                if(itemIndex > 0){
                    rowIndex = itemIndex-1;
                }
                if(rowIndex > -1){
                    var targetBillItem = billGrid.getItem(rowIndex);
                    targetId = targetBillItem.id;
                }
                var pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.importingData+'. '+nls.pleaseWait+'...'
                    });
                pb.show();
                var xhrArgs = {
                    url: 'billManagerImport/importBillItems',
                    content: { id: targetId, element_id: self.billElementId, ids: [itemIds], with_rate: withRate, currentBQAddendumId: self.currentBQAddendumId, _csrf_token: self._csrf_token },
                    handleAs: 'json',
                    load: function(data) {
                        if(data.success){
                            billGrid.store.save();
                            billGrid.store.close();

                            var handle = aspect.after(billGrid, "_onFetchComplete", function() {
                                handle.remove();

                                if(billGrid.selection.selectedIndex > -1) {
                                    this.scrollToRow(billGrid.selection.selectedIndex);
                                }

                                this.disableToolbarButtons(false);
                            });

                            billGrid.sort();
                        }
                        self.itemIds = [];
                        pb.hide();
                        self.dialogWidget.hide();
                    },
                    error: function(error) {
                        self.itemIds = [];
                        billGrid.selection.clear();
                        billGrid.disableToolbarButtons(true);
                        pb.hide();
                        self.dialogWidget.hide();
                    }
                };

                dojo.xhrPost(xhrArgs);
            }
        },
        selectTree: function(e){
            var rowIndex = e.rowIndex,
                newValue = this.selection.selected[rowIndex],
                item = this.getItem(rowIndex);
            if(item){
                this.pushItemIdIntoGridArray(item, newValue);
            }
        },
        pushItemIdIntoGridArray: function(item, select){
            var grid = this;
            var idx = dojo.indexOf(grid.itemIds, item.id[0]);
            if(select){
                if(idx == -1){
                    grid.itemIds.push(item.id[0]);
                }
            }else{
                if(idx != -1){
                    grid.itemIds.splice(idx, 1);
                }
            }
        },
        toggleAllSelection:function(checked){
            var grid = this, selection = grid.selection;
            if(checked){
                selection.selectRange(0, grid.rowCount-1);
                grid.itemIds = [];
                grid.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item.id > 0){
                                grid.itemIds.push(item.id[0]);
                            }
                        });
                    }
                });
            }else{
                selection.deselectAll();
                grid.itemIds = [];
            }
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var ImportGridContainer = declare('buildspace.apps.Tendering.BillManager.ImportItemGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        billId: 0,
        billElementId: 0,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { type: self.type, billId: self.billId, billElementId: self.billElementId, region:"center" });
            var grid = this.grid = new ImportItemGrid(self.gridOpts);
            if(self.type == 'tree'){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'ImportItemGrid-'+self.billElementId+'_'+self.billId+'ImportWithRate-button',
                        label: nls.importWithRate,
                        iconClass: "icon-16-container icon-16-import",
                        onClick: function(){
                            grid.import(true);
                        }
                    })
                );
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'ImportItemGrid-'+self.billElementId+'_'+self.billId+'ImportWithoutRate-button',
                        label: nls.importWithoutRate,
                        iconClass: "icon-16-container icon-16-import",
                        onClick: function(){
                            grid.import(false);
                        }
                    })
                );
                self.addChild(toolbar);
            }
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('billManager-item_import_'+this.billId+'_'+this.billElementId+'-stackContainer');
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

    return declare('buildspace.apps.Tendering.BillManager.ImportItemDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importFromItemLibrary,
        selectedItem: null,
        billId: 0,
        elementId: 0,
        billGrid: null,
        currentBQAddendumId: -1,
        buildRendering: function(){
            var content = this.createContent();
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
        createContent: function(){
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:950px;height:500px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: "billManagerImport/getLibraryList"
                }),
                content = new ImportGridContainer({
                    stackContainerTitle: nls.libraries,
                    pageId: 'import-page_library-'+this.billId+'_'+this.elementId,
                    billId: this.billId,
                    billElementId: this.elementId,
                    gridOpts: {
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.title, field: 'name', width:'auto' }
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.name[0] !== null){
                                self.createElementGrid(_item);
                            }
                        },
                        currentBQAddendumId: self.currentBQAddendumId
                    }
                });
            var gridContainer = this.makeGridContainer(content,nls.libraries);
            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        createElementGrid: function(library){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose: true,
                url:"billManagerImport/getElementList/id/"+library.id
            });

            var grid = new ImportGridContainer({
                stackContainerTitle: library.name,
                pageId: 'import-page_element-'+this.billId+'_'+this.elementId,
                billId: self.billId,
                billElementId: self.elementId,
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto' }
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createItemGrid(_item);
                        }
                    },
                    currentBQAddendumId: self.currentBQAddendumId
                }
            });
        },
        createItemGrid: function(element){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"billManagerImport/getItemList/id/"+element.id
                });

            var grid = new ImportGridContainer({
                stackContainerTitle: element.description,
                pageId: 'import-page_item-'+this.billId+'_'+this.elementId,
                billId: self.billId,
                billElementId: self.elementId,
                type: 'tree',
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    selectedItem: self.selectedItem,
                    billGrid: self.billGrid,
                    _csrf_token: element._csrf_token,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                        {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                        {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                        {name: nls.rate, field: 'rate-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter}
                    ],
                    currentBQAddendumId: self.currentBQAddendumId
                }
            });
        },
        makeGridContainer: function(content, title){
            var id = this.billId+'_'+this.elementId;
            var stackContainer = dijit.byId('billManager-item_import_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('billManager-item_import_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'billManager-item_import_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'billManager-item_import_'+id+'-stackContainer'
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

            dojo.subscribe('billManager-item_import_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('billManager-item_import_'+id+'-stackContainer');
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
                        children[ index ].destroyRecursive(true);
                    }
                }
            });

            return borderContainer;
        }
    });
});