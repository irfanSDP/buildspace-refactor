define('buildspace/apps/ProjectBuilder/BillManager/importResourceDialog',[
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
    'dojo/i18n!buildspace/nls/BuildUpGrid'
], function(declare, lang, connect, when, html, dom, keys, domStyle, aspect, GridFormatter, IndirectSelection, nls){

    var ImportResourceGrid = declare('buildspace.apps.ProjectBuilder.BillManager.ImportResourceGrid', dojox.grid.EnhancedGrid, {
        type: null,
        itemId: 0,
        dialogWidget: null,
        _csrf_token: null,
        resource: null,
        itemIds: [],
        buildUpGridStore: null,
        style: "border-top:none;",
        constructor: function(args){
            this.itemIds = [];
            this.connects = [];
            if(args.type == 'tree'){
                this.urlGetDescendantIds = 'billBuildUpRate/getResourceDescendantsForImport';
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

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });
                pb.show();
                var itemIndex = -1;
                var xhrArgs = {
                    url: this.urlGetDescendantIds,
                    content: {id: item.id},
                    handleAs: 'json',
                    load: function(data) {
                        dojo.forEach(data.items, function(itm){
                            store.fetchItemByIdentity({ 'identity' : itm.id,
                                onItem : function(node){
                                    itemIndex = node._0;
                                    self.pushItemIdIntoGridArray(node, newValue);
                                    self.selection[newValue ? 'addToSelection' : 'deselect'](itemIndex);
                                }
                            });
                        });
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                };

                dojo.xhrPost(xhrArgs);
            }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM){
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
            var importBtn = dijit.byId('ImportResourceGrid-'+this.itemId+'_'+this.resource.id+'Import-button');
            importBtn._setDisabledAttr(isDisable);
        },
        import: function(){
            var self = this,
                buildUpGridStore = self.buildUpGridStore,
                itemIds = self.itemIds;
            if( itemIds.length > 0){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.importing+'. '+nls.pleaseWait+'...'
                });
                pb.show();
                var xhrArgs = {
                    url: 'billBuildUpRate/importResourceItems',
                    content: { id: self.itemId, rid: self.resource.id, ids: [itemIds], _csrf_token: self._csrf_token },
                    handleAs: 'json',
                    load: function(data) {
                        if(data.success){
                            buildUpGridStore.fetchItemByIdentity({ 'identity' : buildspace.constants.GRID_LAST_ROW,  onItem : function(itm){
                                buildUpGridStore.deleteItem(itm);
                                buildUpGridStore.save();//save deleted default last row before we add it back from the return request
                                dojo.forEach(data.items, function(item){
                                    buildUpGridStore.newItem(item);
                                });
                                buildUpGridStore.save();
                            }});
                        }
                        self.itemIds = [];
                        pb.hide();
                        self.dialogWidget.hide();
                    },
                    error: function(error) {
                        self.itemIds = [];
                        pb.hide();
                        self.dialogWidget.hide();
                    }
                };

                dojo.xhrPost(xhrArgs);
            }
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var ImportGridContainer = declare('buildspace.apps.ProjectBuilder.BillManager.ImportResourceGridContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        resource: null,
        stackContainerTitle: '',
        itemId: 0,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { type: self.type, resource: self.resource, itemId: self.itemId, region:"center" });
            var grid = this.grid = new ImportResourceGrid(self.gridOpts);
            if(self.type == 'tree'){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'ImportResourceGrid-'+self.itemId+'_'+self.resource.id+'Import-button',
                        label: nls.importToBuildUp,
                        iconClass: "icon-16-container icon-16-import",
                        onClick: function(){
                            grid.import();
                        }
                    })
                );
                self.addChild(toolbar);
            }
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('billBuildUpRate-resource_import_'+self.itemId+'-'+this.resource.id+'-stackContainer');
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

    return declare('buildspace.apps.ProjectBuilder.BillManager.ImportResourceDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importFromResourceLibrary,
        BQItem: null,
        resource: null,
        buildUpGridStore: null,
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
                    clearOnClose: true,
                    url: "resourceLibrary/getTradeList/id/"+this.resource.resource_library_id
                }),
                content = ImportGridContainer({
                    stackContainerTitle: this.resource.name,
                    pageId: 'import-page_resource-'+this.resource.id,
                    resource: this.resource,
                    itemId: this.BQItem.id,
                    gridOpts: {
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.description, field: 'description', width:'auto' }
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.description[0] !== null){
                                self.createItemGrid(_item);
                            }
                        }
                    }
                });
            var gridContainer = this.makeGridContainer(content);
            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        createItemGrid: function(tradeObj){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"resourceLibrary/getItemList/id/"+tradeObj.id
                });

            var grid = ImportGridContainer({
                stackContainerTitle: tradeObj.description,
                pageId: 'import-page_resource-'+tradeObj.id,
                resource: self.resource,
                itemId: self.BQItem.id,
                type: 'tree',
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    buildUpGridStore: self.buildUpGridStore,
                    _csrf_token: tradeObj._csrf_token,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                        {name: nls.type, field: 'type', width:'80px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                        {name: nls.constant, field: 'constant-final_value', width:'80px', styles:'text-align:right;', formatter: formatter.currencyCellFormatter},
                        {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                        {name: nls.rate, field: 'rate-final_value', width:'80px', styles:'text-align:right;', formatter: formatter.currencyCellFormatter},
                        {name: nls.wastage+' (%)', field: 'wastage-final_value', width:'80px', styles:'text-align:right;', formatter: formatter.currencyCellFormatter}
                    ]
                }
            });
        },
        makeGridContainer: function(content){
            var id = this.BQItem.id+'-'+this.resource.id;
            var stackContainer = dijit.byId('billBuildUpRate-resource_import_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('billBuildUpRate-resource_import_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'billBuildUpRate-resource_import_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: nls.trade,
                content: content,
                grid: content.grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'billBuildUpRate-resource_import_'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;border:0px;",
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

            dojo.subscribe('billBuildUpRate-resource_import_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('billBuildUpRate-resource_import_'+id+'-stackContainer');
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