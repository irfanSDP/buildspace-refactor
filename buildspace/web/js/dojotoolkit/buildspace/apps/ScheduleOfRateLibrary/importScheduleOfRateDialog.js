define('buildspace/apps/ScheduleOfRateLibrary/importScheduleOfRateDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/currency",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/ScheduleOfRateLibrary'
], function(declare, lang, connect, currency, when, html, dom, keys, domStyle, GridFormatter, IndirectSelection, nls){

    var ImportScheduleOfRateGrid = declare('buildspace.apps.ScheduleOfRateLibrary.ImportScheduleOfRateGrid', dojox.grid.EnhancedGrid, {
        type: null,
        itemId: 0,
        dialogWidget: null,
        _csrf_token: null,
        resource: null,
        itemIds: [],
        buildUpGridStore: null,
        buildUpSummaryWidget: null,
        scheduleOfRateItemId: -1,
        style: "border-top:none;",
        constructor: function(args){
            this.itemIds = [];
            this.connects = [];
            if(args.type == 'tree'){
                this.urlGetDescendantIds = 'scheduleOfRate/getResourceDescendantsForImport';
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

            this.pushItemIdIntoGridArray(item, newValue);
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
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            var importBtn = dijit.byId('ImportScheduleOfRateGrid-'+this.itemId+'_'+this.resource.id+'Import-button');
            importBtn._setDisabledAttr(isDisable);
        },
        import: function(){
            var self = this,
                buildUpGridStore = self.buildUpGridStore,
                itemIds = self.itemIds;

            if( itemIds.length > 0){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.importingItems+'. '+nls.pleaseWait+'...'
                });
                pb.show();
                var xhrArgs = {
                    url: 'scheduleOfRateImport/importSORBuildUpItems',
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

                            self.updateTotalBuildUp(data.totalBuildUp);
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
        updateTotalBuildUp: function(totalBuildUp){
            var accContainer = dijit.byId('accPane-'+this.resource.id+'-'+this.itemId);
            accContainer.set('title', this.resource.name+'<span style="color:blue;float:right;">'+buildspace.currencyAbbreviation+'&nbsp;'+currency.format(totalBuildUp)+'</span>');
            this.buildUpSummaryWidget.refreshTotalCost();
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var ImportGridContainer = declare('buildspace.apps.ScheduleOfRateLibrary.ImportScheduleOfRateGridContainer', dijit.layout.BorderContainer, {
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
            var grid = this.grid = new ImportScheduleOfRateGrid(self.gridOpts);
            if(self.type == 'tree'){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'ImportScheduleOfRateGrid-'+self.itemId+'_'+self.resource.id+'Import-button',
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

            var container = dijit.byId('scheduleOfRateBuildUpRate-resource_import_'+self.itemId+'-'+this.resource.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                container.selectChild(self.pageId);
            }
        }
    });

    return declare('buildspace.apps.ScheduleOfRateLibrary.ImportScheduleOfRateDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importFromScheduleOfRate,
        itemId: 0,
        resource: null,
        buildUpGridStore: null,
        buildUpSummaryWidget: null,
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
            key = e.keyCode;
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
                style:"padding:0px;width:1280px;height:600px;",
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
                    url:"scheduleOfRateImport/getScheduleOfRateListWithResource/resource_id/"+self.resource.resource_library_id
                }),
                content = ImportGridContainer({
                    stackContainerTitle: self.resource.name,
                    pageId: 'import-page_sor-'+self.resource.id,
                    resource: this.resource,
                    itemId: this.itemId,
                    gridOpts: {
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.name, field: 'name', width:'auto' }
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.name[0] !== null){
                                self.createScheduleOfRateTradeGrid(_item);
                            }
                        }
                    }
                });

            var gridContainer = this.makeGridContainer(content);

            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        createScheduleOfRateTradeGrid: function(selectedSOR) {
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"scheduleOfRateImport/getScheduleOfRateTradeListWithResource/resource_id/"+self.resource.resource_library_id+"/sor_id/"+selectedSOR.id
                });

            return new ImportGridContainer({
                stackContainerTitle: selectedSOR.name,
                pageId: 'import-page_sor_trade-'+selectedSOR.id,
                resource: this.resource,
                itemId: this.itemId,
                gridOpts: {
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto' }
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createItemGrid(selectedSOR, _item);
                        }
                    }
                }
            });
        },
        createItemGrid: function(selectedSOR, selectedSORTrade) {
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"scheduleOfRateImport/getScheduleOfRateItemListWithResource/resource_id/"+self.resource.resource_library_id+"/sor_id/"+selectedSOR.id+"/trade_id/"+selectedSORTrade.id
                });

            return new ImportGridContainer({
                stackContainerTitle: selectedSORTrade.description,
                pageId: 'import-page_sor_item-'+selectedSORTrade.id,
                resource: self.resource,
                itemId: self.itemId,
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    buildUpGridStore: self.buildUpGridStore,
                    structure: [
                        { name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        { name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                        { name: nls.type, field: 'type', width:'80px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                        { name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter },
                        { name: nls.rate, field: 'rate-value', width:'150px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter },
                        { name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;' }
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createBuildUpItemGrid(_item);
                        }
                    }
                }
            });
        },
        createBuildUpItemGrid: function(item){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"scheduleOfRateImport/getScheduleOfRateBuildUpRateItemListWithResource/sor_item_id/"+item.id+"/resource_library_id/"+self.resource.resource_library_id
                });

            return new ImportGridContainer({
                stackContainerTitle: item.description,
                pageId: 'import-sor_page_item-'+item.id,
                resource: self.resource,
                itemId: self.itemId,
                type: 'tree',
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    buildUpGridStore: self.buildUpGridStore,
                    buildUpSummaryWidget: self.buildUpSummaryWidget,
                    _csrf_token: item._csrf_token,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', cellType:'buildspace.widget.grid.cells.Textarea', formatter: formatter.linkedCellFormatter },
                        {name: nls.number, field: 'number-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                        {name: nls.constant, field: 'constant-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                        {name: nls.qty, field: 'quantity-value', width:'70px', styles:'text-align:right;', formatter: formatter.formulaNumberCellFormatter},
                        {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.linkedUnitIdCellFormatter},
                        {name: nls.rate, field: 'rate-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                        {name: nls.total, field: 'total', width:'80px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter},
                        {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                        {name: nls.lineTotal, field: 'line_total', width:'80px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter}
                    ],
                    onRowDblClick: function(e){}
                }
            });
        },
        makeGridContainer: function(content){
            var id = this.itemId+'-'+this.resource.id;
            var stackContainer = dijit.byId('scheduleOfRateBuildUpRate-resource_import_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('scheduleOfRateBuildUpRate-resource_import_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'scheduleOfRateBuildUpRate-resource_import_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: nls.resources,
                content: content
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'scheduleOfRateBuildUpRate-resource_import_'+id+'-stackContainer'
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

            dojo.subscribe('scheduleOfRateBuildUpRate-resource_import_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('scheduleOfRateBuildUpRate-resource_import_'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page.id));
                    index = index+1;
                    while( children.length > index ){
                        widget.removeChild(children[ index ]);
                        children[ index ].destroyRecursive(true);
                        index = index + 1;
                    }
                }
            });

            return borderContainer;
        }
    });
});