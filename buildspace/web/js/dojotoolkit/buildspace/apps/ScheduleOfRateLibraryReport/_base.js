define(["dojo/_base/declare",
    "dojo/dom-style",
    "dojo/request",
    "dojo/aspect",
    "dojo/when",
    'dojo/_base/event',
    "dijit/Menu",
    'dojo/keys',
    'dojo/on',
    'dojo/store/Memory',
    "dojo/dom-geometry",
    "dijit/Tooltip",
    "./buildUpGrid",
    "./buildUpSummary",
    "buildspace/widget/grid/cells/Formatter",
    "dojo/currency",
    "dijit/layout/AccordionContainer",
    "dijit/layout/AccordionPane",
    "dojo/i18n!buildspace/nls/ScheduleOfRateLibrary"], function(declare, domStyle, request, aspect, when, evt, Menu, keys, on, Memory, geom, Tooltip, BuildUpGrid, BuildUpSummary, GridFormatter, currency, AccordionContainer, AccordionPane, nls){

    return declare('buildspace.apps.ScheduleOfRateLibraryReport', buildspace.apps._App, {
        win: null,
        selectedElementStore: [],
        selectedItemStore: [],
        elementItemStore: [],
        init: function(args){
            var self = this;
            this.win = new buildspace.widget.Window({
                title: nls.appName,
                onClose: dojo.hitch(this, "kill")
            });

            var borderContainer = this.borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;width:100%;"});
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.toggleSidebar,
                    iconClass: "icon-16-container icon-16-arrow_bidirectional",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(self,'toggleSidebar')
                })
            );

            var right = this.tabArea = new dijit.layout.TabContainer({
                region: "center",
                style:"width:100%;height:100%;"
            });

            var libraryStore = this.libraryStore = new dojo.data.ItemFileWriteStore({
                url:"scheduleOfRate/scheduleOfRateList"
            });

            var treeModel = new dijit.tree.ForestStoreModel({
                store: libraryStore,
                rootId: "root",
                rootLabel: nls.scheduleOfRates,
                childrenAttrs: ["__children"]
            });

            //TODO: move files using DnD?
            var left = this.left = new dijit.EditableTree({
                model: treeModel,
                splitter: true,
                region: "left",
                openOnClick:true,
                labelAttr: 'name',
                style: "background-color:#ecede9;width:170px;height:100%;",
                getIconClass: dojo.hitch(libraryStore, function(item, opened){
                    if(item.root){
                      return opened ? 'icon-16-container icon-16-file' : 'icon-16-container icon-16-folder';
                    }else{
                      return 'icon-16-container icon-16-list'
                    }
                }),
                onMouseDown: function(e){
                    var node=dijit.getEnclosingWidget(e.target);
                    if(dojo.mouseButtons.isRight(e)) {
                        try{
                            this.set('selectedNode',node);
                        }catch(err){
                            evt.stop(e);
                        }
                    }
                    evt.stop(e);
                }
            });

            on(left.domNode, on.selector(".dijitTree", "contextmenu"), function(e){
                evt.stop(e);
            });

            dojo.connect(left, "onDblClick", this, "onItem");

            borderContainer.addChild(toolbar);
            borderContainer.addChild(left);
            borderContainer.addChild(right);

            this.win.addChild(borderContainer);
            this.win.show();
            this.win.startup();
        },
        toggleSidebar: function() {
            var panelIndex = this.borderContainer.getIndexOfChild(this.left);
            if(panelIndex >= 0){
                this.borderContainer.removeChild(this.left);
            }else{
                this.borderContainer.addChild(this.left);
            }
        },
        makeTab: function(id, name, content){
            var stackContainer = dijit.byId('scheduleOfRate_report_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('scheduleOfRate_report_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'scheduleOfRate_report_'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: nls.trade,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'scheduleOfRate_report_'+id+'-stackContainer'
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(new dijit.layout.ContentPane({
                id: 'scheduleOfRate_report_'+id+'-controllerPane',
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            }));

            var pane = new dijit.layout.ContentPane({
                closable: true,
                style: "padding: 0px; overflow: hidden;border:0px;",
                title: buildspace.truncateString(name, 25),
                content: borderContainer
            });

            this.tabArea.addChild(pane);
            this.tabArea.selectChild(pane);

            dojo.subscribe('scheduleOfRate_report_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('scheduleOfRate_report_'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){
                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyRecursive(true);
                            index = index + 1;
                        }
                    }
                }
            });

            pane.lib_info = {
                name: name,
                id: id
            }
        },
        onItem: function(item){
            var store = this.libraryStore;
            if(!store.isItem(item)) return;
            var id = store.getValue(item, "id");
            var name = store.getValue(item, "name");
            if(id == 'root') return;

            var tac = this.tabArea.getChildren();

            for(var i in tac){
                if(typeof tac[i].lib_info != "object") continue;
                if(tac[i].lib_info.id == id){
                    return this.tabArea.selectChild(tac[i]);
                }
            }

            store = dojo.data.ItemFileWriteStore({
                url: "scheduleOfRate/getTradeList/id/"+id,
                clearOnClose: true
            });

            var self = this,
                formatter = new GridFormatter();

            this.selectedElementStore[id] = new Memory({ idProperty: 'id' });
            this.selectedItemStore[id]    = new Memory({ idProperty: 'id' });
            this.elementItemStore[id]     = [];

            var grid = self.grid = buildspace.apps.ScheduleOfRateLibraryReport.grid({
                id: 'trade-reportpage-container-' + id,
                stackContainerTitle: name,
                pageId: 'trade-reportpage-'+id,
                libraryId: id,
                gridOpts: {
                    gridContainer: self,
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', cellType:'buildspace.widget.grid.cells.Textarea' },
                        {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'},
                        {name: nls.recalculateResourceStatus, field: 'recalculate_resource_status', width:'100px', styles:'text-align:center;', formatter: formatter.recalculateSorCellFormatter }
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createItemGrid(id, _item);
                        }
                    },
                    singleCheckBoxSelection: function(e) {
                        var self = this,
                            rowIndex = e.rowIndex,
                            checked = this.selection.selected[rowIndex],
                            item = this.getItem(rowIndex);

                        // used to store removeable selection
                        self.removedIds = [];

                        if ( checked ) {
                            self.gridContainer.selectedElementStore[self.libraryId].put({ id: item.id[0] });

                            return self.getAffectedItemsByTrades(item, 'add');
                        } else {
                            self.gridContainer.selectedElementStore[self.libraryId].remove(item.id[0]);

                            self.removedIds.push(item.id[0]);

                            return self.getAffectedItemsByTrades(item, 'remove');
                        }
                    },
                    toggleAllSelection: function(checked) {
                        var self = this, selection = this.selection, storeName;

                        // used to store removeable selection
                        self.removedIds = [];

                        if (checked) {
                            selection.selectRange(0, self.rowCount-1);
                            self.store.fetch({
                                onComplete: function (items) {
                                    dojo.forEach(items, function (item, index) {
                                        if(item.id > 0) {
                                            self.gridContainer.selectedElementStore[self.libraryId].put({ id: item.id[0] });
                                        }
                                    });
                                }
                            });

                            return self.getAffectedItemsByTrades(null , 'add');
                        } else {
                            selection.deselectAll();

                            self.store.fetch({
                                onComplete: function (items) {
                                    dojo.forEach(items, function (item, index) {
                                        if(item.id > 0) {
                                            self.gridContainer.selectedElementStore[self.libraryId].remove(item.id[0]);

                                            self.removedIds.push(item.id[0]);
                                        }
                                    });
                                }
                            });

                            return self.getAffectedItemsByTrades(null, 'remove');
                        }
                    },
                    getAffectedItemsByTrades: function(element, type) {
                        var self = this,
                            elements = [];

                        var pb = buildspace.dialog.indeterminateProgressBar({
                            title: nls.pleaseWait+'...'
                        });

                        pb.show();

                        if (type === 'add') {
                            // if single element, then only push affected element only
                            if (element) {
                                elements.push(element.id[0]);
                            } else {
                                self.gridContainer.selectedElementStore[self.libraryId].query().forEach(function(element) {
                                    elements.push(element.id);
                                });
                            }
                        } else {
                            for (var itemKeyIndex in self.removedIds) {
                                elements.push(self.removedIds[itemKeyIndex]);
                            }
                        }

                        request.post('scheduleOfRateReporting/getAffectedItemsBySelectedTrades', {
                            handleAs: 'json',
                            data: {
                                libraryId: self.libraryId,
                                trade_ids: JSON.stringify(self.gridContainer.arrayUnique(elements))
                            }
                        }).then(function(data) {
                            // create default placeholder for storing item(s) associated with element
                            for (var elementId in data) {
                                if ( ! self.gridContainer.elementItemStore[elementId] ) {
                                    self.gridContainer.elementItemStore[elementId] = new Memory({ idProperty: 'id' });
                                }
                            }

                            if ( type === 'add' ) {
                                for (var elementId in data) {
                                    for (var itemIdIndex in data[elementId]) {
                                        self.gridContainer.elementItemStore[elementId].put({ id: data[elementId][itemIdIndex] });
                                        self.gridContainer.selectedItemStore[self.libraryId].put({ id: data[elementId][itemIdIndex] });
                                    }
                                }
                            } else {
                                for (var elementId in data) {
                                    self.gridContainer.selectedElementStore[self.libraryId].remove(elementId);

                                    for (var itemIdIndex in data[elementId]) {
                                        self.gridContainer.elementItemStore[elementId].remove(data[elementId][itemIdIndex]);
                                        self.gridContainer.selectedItemStore[self.libraryId].remove(data[elementId][itemIdIndex]);
                                    }
                                }
                            }

                            pb.hide();
                        }, function(error) {
                            pb.hide();
                            console.log(error);
                        });
                    }
                }
            });
            self.makeTab(id, name, grid);
        },
        createItemGrid: function(libraryId, tradeObj){
            var self = this, formatter = GridFormatter();
            var hierarchyTypes = {
                options: [
                    buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT,
                    buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT,
                    buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID_TEXT
                ],
                values: [
                    buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER,
                    buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM,
                    buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID
                ]
            };

            var store = new dojo.data.ItemFileWriteStore({
                url:"scheduleOfRate/getItemList/id/"+tradeObj.id,
                clearOnClose: true
            });

            var unitQuery = dojo.xhrGet({
                url: "scheduleOfRate/getUnits",
                handleAs: "json"
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            pb.show();

            return when(unitQuery, function(uom){
                pb.hide();
                var grid = buildspace.apps.ScheduleOfRateLibraryReport.grid({
                    id: 'item-reportpage-container-' + libraryId,
                    stackContainerTitle: tradeObj.description,
                    pageId: 'item-reportpage-'+tradeObj.id,
                    libraryId: libraryId,
                    tradeId: tradeObj.id,
                    type: 'tree',
                    gridOpts: {
                        gridContainer: self,
                        tradeGrid: self.grid,
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.description, field: 'description', width:'auto', cellType:'buildspace.widget.grid.cells.Textarea', formatter: formatter.treeCellFormatter },
                            {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', cellType:'dojox.grid.cells.Select', options: hierarchyTypes.options, values:hierarchyTypes.values, formatter: formatter.typeCellFormatter },
                            {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', cellType:'dojox.grid.cells.Select', options: uom.options, values:uom.values, formatter: formatter.unitIdCellFormatter},
                            {name: nls.rate, field: 'rate-value', field_name: 'rate-value', width:'150px', styles:'text-align:right;', cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                            {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'},
                            {name: nls.recalculateResourceStatus, field: 'recalculate_resource_status', width:'100px', styles:'text-align:center;', formatter: formatter.recalculateSorCellFormatter }
                        ],
                        onRowDblClick: function(e){
                            var item = this.getItem(e.rowIndex);
                            if(item.id > 0 && item.description[0] !== null && item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM){
                                self.createBuildUpRateContainer(libraryId, item, store);
                            }
                        },
                        singleCheckBoxSelection: function(e) {
                            var self = this,
                                rowIndex = e.rowIndex,
                                checked = this.selection.selected[rowIndex],
                                item = this.getItem(rowIndex);

                            // used to store removeable selection
                            self.removedIds = [];

                            if ( checked ) {
                                self.gridContainer.selectedItemStore[self.libraryId].put({ id: item.id[0] });

                                return self.getAffectedTradesByItems(item, 'add');
                            } else {
                                self.gridContainer.selectedItemStore[self.libraryId].remove(item.id[0]);

                                self.removedIds.push(item.id[0]);

                                return self.getAffectedTradesByItems(item, 'remove');
                            }
                        },
                        toggleAllSelection: function(checked) {
                            var self = this, selection = this.selection, storeName;

                            // used to store removeable selection
                            self.removedIds = [];

                            if (checked) {
                                selection.selectRange(0, self.rowCount-1);
                                self.store.fetch({
                                    onComplete: function (items) {
                                        dojo.forEach(items, function (item, index) {
                                            if(item.id > 0) {
                                                self.gridContainer.selectedItemStore[self.libraryId].put({ id: item.id[0] });
                                            }
                                        });
                                    }
                                });

                                return self.getAffectedTradesByItems(null , 'add');
                            } else {
                                selection.deselectAll();

                                self.store.fetch({
                                    onComplete: function (items) {
                                        dojo.forEach(items, function (item, index) {
                                            if(item.id > 0) {
                                                self.gridContainer.selectedItemStore[self.libraryId].remove(item.id[0]);

                                                self.removedIds.push(item.id[0]);
                                            }
                                        });
                                    }
                                });

                                return self.getAffectedTradesByItems(null, 'remove');
                            }
                        },
                        getAffectedTradesByItems: function(item, type) {
                            var self = this,
                                items = [];

                            var pb = buildspace.dialog.indeterminateProgressBar({
                                title: nls.pleaseWait+'...'
                            });

                            pb.show();

                            if (type === 'add') {
                                self.gridContainer.selectedItemStore[self.libraryId].query().forEach(function(item) {
                                    items.push(item.id);
                                });
                            } else {
                                for (var itemKeyIndex in self.removedIds) {
                                    items.push(self.removedIds[itemKeyIndex]);
                                }
                            }

                            request.post('scheduleOfRateReporting/getAffectedTradesBySelectedItems', {
                                handleAs: 'json',
                                data: {
                                    libraryId: self.libraryId,
                                    item_ids: JSON.stringify(self.gridContainer.arrayUnique(items))
                                }
                            }).then(function(data) {
                                // create default placeholder for storing item(s) associated with element
                                for (var elementId in data) {
                                    if ( ! self.gridContainer.elementItemStore[elementId] ) {
                                        self.gridContainer.elementItemStore[elementId] = new Memory({ idProperty: 'id' });
                                    }
                                }

                                var elementGrid = dijit.byId('trade-reportpage-container-' + self.libraryId);

                                if ( type === 'add' ) {
                                    for (var elementId in data) {
                                        for (var itemIdIndex in data[elementId]) {
                                            self.gridContainer.elementItemStore[elementId].put({ id: data[elementId][itemIdIndex] });
                                            self.gridContainer.selectedItemStore[self.libraryId].put({ id: data[elementId][itemIdIndex] });
                                        }

                                        // checked element selection if there is item(s) selected in the current element
                                        elementGrid.grid.store.fetchItemByIdentity({
                                            identity: elementId,
                                            onItem: function(node) {
                                                if ( ! node ) {
                                                    return;
                                                }

                                                self.gridContainer.selectedElementStore[self.libraryId].put({ id: elementId });

                                                return elementGrid.grid.rowSelectCell.toggleRow(node._0, true);
                                            }
                                        });
                                    }
                                } else {
                                    for (var elementId in data) {
                                        self.gridContainer.selectedElementStore[self.libraryId].remove(elementId);

                                        for (var itemIdIndex in data[elementId]) {
                                            self.gridContainer.elementItemStore[elementId].remove(data[elementId][itemIdIndex]);
                                            self.gridContainer.selectedItemStore[self.libraryId].remove(data[elementId][itemIdIndex]);
                                        }

                                        // remove checked element selection if there is no item(s) in the current element
                                        elementGrid.grid.store.fetchItemByIdentity({
                                            identity: elementId,
                                            onItem: function(node) {
                                                if ( ! node ) {
                                                    return;
                                                }

                                                if ( self.gridContainer.elementItemStore[elementId].data.length === 0 ) {
                                                    self.gridContainer.selectedElementStore[self.libraryId].remove(elementId);
                                                    return elementGrid.grid.rowSelectCell.toggleRow(node._0, false);
                                                }
                                            }
                                        });
                                    }
                                }

                                pb.hide();
                            }, function(error) {
                                pb.hide();
                                console.log(error);
                            });
                        }
                    }
                });
            },function(error){
                /* got fucked */
            });
        },
        createBuildUpRateContainer: function(libraryId, item, itemGridStore){
            var currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation,
                baseContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:100%;height:100%;border:0px;outline:none;",
                    gutters: false
                }),
                aContainer = new dijit.layout.AccordionContainer({
                    id: "accordian_"+libraryId+"_"+item.id+"-container",
                    region: "center",
                    style:"padding:0px;width:100%;height:100%;border:0px;outline:none;"
                }),
                resourceQuery = dojo.xhrGet({
                    url: "scheduleOfRate/resourceList/item_id/"+item.id,
                    handleAs: "json"
                });

            var formatter = new GridFormatter();

            var unitQuery = dojo.xhrGet({
                url: "scheduleOfRate/getUnits",
                handleAs: "json"
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            pb.show();

            unitQuery.then(function(uom){
                when(resourceQuery, function(resources){
                    if(resources.length == 0){
                        aContainer.addChild(new dijit.layout.ContentPane({
                            title: nls.emptyResourceCategoryTitle,
                            style: "padding:0px;border:0px;",
                            doLayout: false,
                            id: 'accPane-empty_resource-'+item.id,
                            content: '<div style="text-align:center;"><p><h1>'+nls.emptyResourceCategory+'</h1></p></div> '
                        }));
                    }else{
                        var buildUpSummaryWidget = BuildUpSummary({
                            id: 'buildUpRateSummary-'+item.id,
                            itemId: item.id,
                            container: baseContainer,
                            _csrf_token: item._csrf_token
                        });

                        dojo.forEach(resources, function(resource){
                            var store = new dojo.data.ItemFileWriteStore({
                                url:"scheduleOfRate/getBuildUpRateItemList/sor_item_id/"+item.id+"/resource_id/"+resource.id,
                                clearOnClose: true
                            });
                            var grid = BuildUpGrid({
                                resource: resource,
                                scheduleOfRateItemId: item.id,
                                gridOpts: {
                                    itemId: item.id,
                                    store: store,
                                    buildUpSummaryWidget: buildUpSummaryWidget,
                                    structure: [
                                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                        {name: nls.description, field: 'description', width:'auto', cellType:'buildspace.widget.grid.cells.Textarea', formatter: formatter.linkedCellFormatter },
                                        {name: nls.number, field: 'number-value', width:'100px', styles:'text-align:right;', cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                        {name: nls.constant, field: 'constant-value', width:'100px', styles:'text-align:right;', cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                        {name: nls.qty, field: 'quantity-value', width:'70px', styles:'text-align:right;', cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaNumberCellFormatter},
                                        {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', cellType:'dojox.grid.cells.Select', options: uom.options, values:uom.values, formatter: formatter.linkedUnitIdCellFormatter},
                                        {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                        {name: nls.total, field: 'total', width:'120px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter},
                                        {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                        {name: nls.lineTotal, field: 'line_total', width:'120px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter}
                                    ]
                                }
                            });
                            aContainer.addChild(new dijit.layout.ContentPane({
                                title: resource.name+'<span style="color:blue;float:right;">'+buildspace.currencyAbbreviation+' '+currency.format(resource.total_build_up)+'</span>',
                                style: "padding:0px;border:0px;",
                                doLayout: false,
                                id: 'accPane-'+resource.id+'-'+item.id,
                                content: grid
                            }));
                        });
                        baseContainer.addChild(buildUpSummaryWidget);
                    }

                    baseContainer.addChild(aContainer);

                    var container = dijit.byId('scheduleOfRate_report_'+libraryId+'-stackContainer');

                    if(container){
                        var node = document.createElement("div");
                        var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(item.description, 60)+' ('+nls.buildUpRate+')', id: 'buildUpRatePage-'+item.id, executeScripts: true },node );
                        container.addChild(child);
                        child.set('content', baseContainer);
                        container.selectChild('buildUpRatePage-'+item.id);
                    }

                    pb.hide();
                });
            });
        },
        kill: function(){
            var tooltip = dijit.byId('TreeInlineEditBox_error-tooltip');
            if(tooltip){
                tooltip.destroyRecursive();
            }
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
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
        arrayUnique: function(array) {
            return array.reverse().filter(function (e, i, arr) {
                return arr.indexOf(e, i+1) === -1;
            }).reverse();
        }
    });
});