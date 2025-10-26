define('buildspace/apps/ScheduleOfRateLibrary/importSORItemDialog',[
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
    'dojo/i18n!buildspace/nls/ResourceLibrary',
	'buildspace/widget/grid/Filter'
], function(declare, lang, connect, when, html, dom, keys, domStyle, aspect, GridFormatter, IndirectSelection, nls, FilterToolbar){

    var ImportSORItemGrid = declare('buildspace.apps.ScheduleOfRateLibrary.ImportSORItemGrid', dojox.grid.EnhancedGrid, {
        type: null,
        selectedItem: null,
        libraryId: 0,
        libraryTradeId: 0,
        dialogWidget: null,
        _csrf_token: null,
        resourceGrid: null,
        style: "border-top:none;",
        constructor: function(args){
            this.itemIds = [];
            this.connects = [];
            if(args.type == 'tree'){
                this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align:center;"}}
            }

            this.inherited(arguments);
        },
        canSort: function(){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
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
            var importWithRateBtn = dijit.byId('ImportSORItemGrid-'+this.libraryTradeId+'_'+this.libraryId+'ImportWithRate-button');
            importWithRateBtn._setDisabledAttr(isDisable);
            var importWithoutRateBtn = dijit.byId('ImportSORItemGrid-'+this.libraryTradeId+'_'+this.libraryId+'ImportWithoutRate-button');
            importWithoutRateBtn._setDisabledAttr(isDisable);
        },
        importItem: function(withRate){
            var self = this,
                resourceGrid = self.resourceGrid,
                itemIds = self.itemIds,
                rowIndex = -1,
                targetId = null;
            if( itemIds.length > 0){
                var itemIndex = resourceGrid.getItemIndex(this.selectedItem);
                if(itemIndex > 0){
                    rowIndex = itemIndex-1;
                }
                if(rowIndex > -1){
                    var targetBillItem = resourceGrid.getItem(rowIndex);
                    targetId = targetBillItem.id;
                }
                var pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.importingData+'. '+nls.pleaseWait+'...'
                    });
                pb.show();

                dojo.xhrPost({
                    url: 'scheduleOfRateImport/importSORItems',
                    content: { id: targetId, trade_id: self.libraryTradeId, ids: [itemIds], with_rate: withRate, _csrf_token: self._csrf_token },
                    handleAs: 'json',
                    load: function(data) {
                        if(data.success){
                            resourceGrid.store.save();
                            resourceGrid.store.close();

                            var handle = aspect.after(resourceGrid, "_onFetchComplete", function() {
                                this.selection.clear();
                                handle.remove();
                                rowIndex = rowIndex == -1 ? 0 : rowIndex;
                                this.scrollToRow(rowIndex);
                                var newItemIdx = rowIndex == 0 ? rowIndex : rowIndex+1;
                                this.selection.setSelected(newItemIdx, true);
                                this.disableToolbarButtons(false);
                            });

                            resourceGrid._refresh();
                        }
                        self.itemIds = [];
                        pb.hide();
                        self.dialogWidget.hide();
                    },
                    error: function(error) {
                        self.itemIds = [];
                        resourceGrid.selection.clear();
                        resourceGrid.disableToolbarButtons(true);
                        pb.hide();
                        self.dialogWidget.hide();
                    }
                });
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

    var ImportGridContainer = declare('buildspace.apps.ScheduleOfRateLibrary.ImportSORItemGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        libraryId: 0,
        libraryTradeId: 0,
        gridOpts: {},
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {
                type: this.type,
                libraryId: this.libraryId,
                libraryTradeId: this.libraryTradeId,
                region:"center"
            });

            var grid = this.grid = new ImportSORItemGrid(this.gridOpts);
            var filter = new FilterToolbar({
                   grid: grid,
                   region: "top",
                   filterFields: this.filterFields
            });

            this.addChild(filter);

            if(this.type == 'tree'){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'ImportSORItemGrid-'+this.libraryTradeId+'_'+this.libraryId+'ImportWithRate-button',
                        label: nls.importWithRate,
                        iconClass: "icon-16-container icon-16-import",
                        onClick: function(){
                            grid.importItem(true);
                        }
                    })
                );
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'ImportSORItemGrid-'+this.libraryTradeId+'_'+this.libraryId+'ImportWithoutRate-button',
                        label: nls.importWithoutRate,
                        iconClass: "icon-16-container icon-16-import",
                        onClick: function(){
                            grid.importItem(false);
                        }
                    })
                );

                this.addChild(toolbar);
            }

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('resourceLibrary-item_import_'+this.libraryId+'_'+this.libraryTradeId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(this.stackContainerTitle, 45),
                    content: this,
                    id: this.pageId, executeScripts: true
                },node );

                container.addChild(child);
                lang.mixin(child, {grid: grid});

                container.selectChild(this.pageId);
            }
        }
    });

    return declare('buildspace.apps.ScheduleOfRateLibrary.ImportItemDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        selectedItem: null,
        libraryId: 0,
        tradeId: 0,
        resourceGrid: null,
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
                style:"padding:0px;width:980px;height:500px;",
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
                    url: "scheduleOfRateImport/getScheduleOfRateList"
                }),
                content = new ImportGridContainer({
                    stackContainerTitle: nls.scheduleOfRate,
                    pageId: 'import-page_library-'+this.libraryId+'_'+this.tradeId,
                    libraryId: this.libraryId,
                    filterFields:[{'name':'Title'}],
                    libraryTradeId: this.tradeId,
                    gridOpts: {
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.title, field: 'name', width:'auto' }
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.name[0] !== null){
                                self.createTradeGrid(_item);
                            }
                        }
                    }
                });
            var gridContainer = this.makeGridContainer(content, nls.scheduleOfRate);
            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        createTradeGrid: function(library){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose: true,
                url:"scheduleOfRate/getTradeList/id/"+library.id
            });

            new ImportGridContainer({
                stackContainerTitle: library.name,
                pageId: 'import-page_trade-'+this.libraryId+'_'+this.tradeId,
                libraryId: self.libraryId,
                libraryTradeId: self.tradeId,
                filterFields:[{'description':'Description'}],
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
                    }
                }
            });
        },
        createItemGrid: function(trade){
            var formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"scheduleOfRate/getItemList/id/"+trade.id
                });

            new ImportGridContainer({
                stackContainerTitle: trade.description,
                pageId: 'import-page_item-'+this.libraryId+'_'+this.tradeId,
                libraryId: this.libraryId,
                libraryTradeId: this.tradeId,
                filterFields:[{'description':'Description'},{'uom_symbol':'Unit'},{'rate-final_value':'Rate'}],
                type: 'tree',
                gridOpts: {
                    escapeHTMLInData: false,
                    store: store,
                    dialogWidget: this,
                    selectedItem: this.selectedItem,
                    resourceGrid: this.resourceGrid,
                    _csrf_token: trade._csrf_token,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                        {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                        {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                        {name: nls.rate, field: 'rate-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter}
                    ]
                }
            });
        },
        makeGridContainer: function(content, title){
            var id = this.libraryId+'_'+this.tradeId;
            var stackContainer = dijit.byId('resourceLibrary-item_import_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('resourceLibrary-item_import_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'resourceLibrary-item_import_'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'resourceLibrary-item_import_'+id+'-stackContainer'
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

            dojo.subscribe('resourceLibrary-item_import_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('resourceLibrary-item_import_'+id+'-stackContainer');
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