define('buildspace/apps/SubPackage/ImportBillItemDialog',[
    'dojo/_base/declare',
    'dojo/aspect',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/SubPackages'
], function(declare, aspect, lang, connect, when, html, dom, keys, domStyle, GridFormatter, IndirectSelection, nls){

    var ImportBillGrid = declare('buildspace.apps.SubPackage.ImportBillItemGrid', dojox.grid.EnhancedGrid, {
        type: null,
        itemId: 0,
        dialogWidget: null,
        _csrf_token: null,
        itemIds: [],
        subPackageGridStore: null,
        style: "border-top:none;",
        constructor: function(args){
            this.itemIds = [];
            this.connects = [];
            if(args.type == 'tree'){
                this.urlGetDescendantIds = 'subPackage/getBillItemDescendants';
                this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align: center;"}}
            }
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            if(self.type == 'tree'){
                aspect.after(this, "_onFetchComplete", function() {
                    this.store.fetch({query: {selected:true}, queryOptions: {ignoreCase: true}, onComplete: this.markSelectedCheckBoxes, scope: this});
                });
            }

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
        selectTree: function(e){
            var rowIndex = e.rowIndex,
                newValue = this.selection.selected[rowIndex],
                item = this.getItem(rowIndex), self = this, store = this.store;

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });
                pb.show();
                var itemIndex = -1;
                dojo.xhrGet({
                    url: this.urlGetDescendantIds,
                    content: {id: item.id},
                    handleAs: 'json',
                    load: function(data) {
                        dojo.forEach(data.items, function(itm){
                            store.fetchItemByIdentity({ 'identity' : itm.id,
                                onItem : function(node){
                                    if(node){
                                        itemIndex = node._0;
                                        if(node.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM){
                                            self.pushItemIdIntoGridArray(node, newValue);
                                        }
                                        self.selection[newValue ? 'addToSelection' : 'deselect'](itemIndex);
                                    }
                                }
                            });
                        });
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            }else{
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
        disableToolbarButtons: function(isDisable){
            var importBtn = dijit.byId('SubPackages-ImportBillItemsGrid-'+this.subPackage.id+'Import-button');
            importBtn._setDisabledAttr(isDisable);
        },
        markSelectedCheckBoxes: function(items, request){
            for(var i = 0; i < items.length; i++){
                var itemIndex = items[i]._0;
                this.pushItemIdIntoGridArray(items[i], true);
                this.selection.setSelected(itemIndex, true);
            }
        },
        import: function(element){
            var self = this,
                title = nls.removeAllBillItemsFrom+' '+buildspace.truncateString(self.subPackage.name, 25),
                msg = nls.areYouSureToRemoveAllBillItemsUnderElement+' <b>'+buildspace.truncateString(element.description, 40)+'</b>?',
                subPackageGridStore = self.subPackageGridStore,
                itemIds = self.itemIds,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.importing+'. '+nls.pleaseWait+'...'
                });

            pb.show();
            var xhrArgs = {
                url: 'subPackage/importBillItems',
                content: { id: self.subPackage.id, ids: [itemIds], _csrf_token: self._csrf_token, element_id: element.id },
                handleAs: 'json',
                load: function(data) {
                    if(data.success){
                        subPackageGridStore.fetchItemByIdentity({ 'identity' : self.subPackage.id,  onItem : function(subPackage){
                            subPackageGridStore.setValue(subPackage, 'est_amount', data.est_amount);
                            subPackageGridStore.setValue(subPackage, 'selected_amount', data.selected_amount);
                            subPackageGridStore.save();
                        }});

                        self.selection.deselectAll();
                        dojo.forEach(data.items, function(item){
                            self.store.fetchItemByIdentity({ 'identity' : item.id,  onItem : function(itm){
                                if(itm){
                                    var itemIndex = itm._0;
                                    self.selection.setSelected(itemIndex, true);
                                }
                            }});
                        });
                    }
                    pb.hide();
                },
                error: function(error) {
                    self.itemIds = [];
                    pb.hide();
                    self.dialogWidget.hide();
                }
            };

            if( itemIds.length > 0){
                dojo.xhrPost(xhrArgs);
            }else{
                new buildspace.dialog.confirm(title, msg, 80, 400, function() {
                    dojo.xhrPost(xhrArgs);
                }, function() {
                    pb.hide();
                });
            }
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var ImportGridContainer = declare('buildspace.apps.SubPackage.ImportBillGridContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        subPackage: null,
        stackContainerTitle: '',
        type: 'default',
        element: null,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            lang.mixin(self.gridOpts, { type: self.type, subPackage: self.subPackage, region:"center" });
            var grid = this.grid = new ImportBillGrid(self.gridOpts);
            if(self.type == 'tree'){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'SubPackages-ImportBillItemsGrid-'+self.subPackage.id+'Import-button',
                        label: nls.importToSubPackage,
                        iconClass: "icon-16-container icon-16-import",
                        onClick: function(){
                            grid.import(self.element);
                        }
                    })
                );
                self.addChild(toolbar);
            }
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('SubPackages-bill_items_import_'+self.subPackage.id+'-stackContainer');
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

    return declare('buildspace.apps.SubPackage.ImportBillItemDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importScheduleOfRateItems,
        subPackage: null,
        subPackageGridStore: null,
        rootProject: null,
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;
            this.title = nls.importBillItems+' :: '+buildspace.truncateString(this.subPackage.name, 45);
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
                style:"padding:0px;width:850px;height:350px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border:0px;"
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
                    url: "subPackage/getProjectBills/id/"+this.rootProject.id,
                    clearOnClose: true
                });

            var gridContainer = this.makeGridContainer(ImportGridContainer({
                stackContainerTitle: nls.bills,
                pageId: 'sub_packages_import-page_bill-'+this.subPackage.id,
                subPackage: this.subPackage,
                gridOpts: {
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.name, field: 'name', width:'auto' }
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.name[0] !== null){
                            self.createTradeGrid(_item);
                        }
                    }
                }
            }));
            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        createTradeGrid: function(bill){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"subPackage/getElementList/id/"+bill.id,
                    clearOnClose: true
                });

            ImportGridContainer({
                stackContainerTitle: bill.name,
                pageId: 'sub_packages_import-page_bill_element-'+this.subPackage.id+'_'+bill.id,
                subPackage: self.subPackage,
                gridOpts: {
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto'}
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createItemGrid(_item, bill);
                        }
                    }
                }
            });
        },
        createItemGrid: function(element, bill){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"subPackage/getBillItemList/sub_package_id/"+self.subPackage.id+"/bill_id/"+bill.id+"/id/"+element.id,
                    clearOnClose: true
                });

            ImportGridContainer({
                stackContainerTitle: element.description,
                pageId: 'sub_packages_import-page_bill_item-'+this.subPackage.id+'_'+element.id,
                subPackage: self.subPackage,
                type: 'tree',
                element: element,
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    subPackageGridStore: self.subPackageGridStore,
                    _csrf_token: element._csrf_token,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                        {name: nls.type, field: 'type', width:'80px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                        {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter}
                    ]
                }
            });
        },
        makeGridContainer: function(content){
            var id = this.subPackage.id,
                stackContainer = dijit.byId('SubPackages-bill_items_import_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('SubPackages-bill_items_import_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'SubPackages-bill_items_import_'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: nls.bills,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'SubPackages-bill_items_import_'+id+'-stackContainer'
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;border:0px;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            }));

            dojo.subscribe('SubPackages-bill_items_import_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('SubPackages-bill_items_import_'+id+'-stackContainer');
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