define(["dojo/_base/declare",
    "dojo/aspect",
    "dojo/when",
    'dojo/_base/event',
    "dijit/Menu",
    'dojo/keys',
    'dojo/on',
    "dojo/dom-geometry",
    "dijit/Tooltip",
    "./buildUpGrid",
    "./buildUpSummary",
    "buildspace/widget/grid/cells/Formatter",
    "dojo/currency",
    "dijit/layout/AccordionContainer",
    "dijit/layout/AccordionPane",
    "./AddResourceCategoryDialog",
    "dojo/i18n!buildspace/nls/ScheduleOfRateLibrary"], function(declare, aspect, when, evt, Menu, keys, on, geom, Tooltip, BuildUpGrid, BuildUpSummary, GridFormatter, currency, AccordionContainer, AccordionPane, AddResourceCategoryDialog, nls){
    return declare('buildspace.apps.ScheduleOfRateLibrary', buildspace.apps._App, {
        win: null,
        toolbarActions: ['edit','delete'],
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
                    label: nls.newScheduleOfRate,
                    iconClass: "icon-16-container icon-16-add",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(self,'addScheduleOfRate')
                })
            );

            dojo.forEach(this.toolbarActions, function(action){
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id:'schedule_of_rate-toolbar_'+action,
                        label: nls[action],
                        iconClass: "icon-16-container icon-16-"+action,
                        style:"outline:none!important;",
                        disabled: true,
                        onClick: dojo.hitch(self,action+'ScheduleOfRate')
                    })
                );
            });
            toolbar.addChild(new dijit.ToolbarSeparator());
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
                ajaxSubmitUrl: 'scheduleOfRate/scheduleOfRateUpdate',
                onSuccess:  function(itemId){
                    self.updateTabTitle(itemId);
                },
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
                    if(node && node.item && node.item.root){
                        self.disableToolbarButtons(true);
                    }
                    if(dojo.mouseButtons.isRight(e)) {
                        try{
                            this.set('selectedNode',node);
                        }catch(err){
                            evt.stop(e);
                        }
                    }
                    evt.stop(e);
                },
                onClick:function(e,node) {
                    if(node.item && node.item.id !='root'){
                        self.disableToolbarButtons(false);
                    }
                }
            });

            on(left.domNode, on.selector(".dijitTree", "contextmenu"), function(e){
                evt.stop(e);
            });

            on(left.domNode, on.selector(".dijitTreeNode", "contextmenu"), function(e){
                var ctxMenu = new dijit.Menu();
                var info = {target: e.target, coords: e.keyCode !== keys.F10 && "pageX" in e ? {x: e.pageX, y: e.pageY } : null};
                if(ctxMenu && left.selectedItem){
                    var disabled = (left.selectedItem.root) ? true : false;
                    self.disableToolbarButtons(disabled);
                    self.treeContextMenuItems(ctxMenu);
                    ctxMenu._openMyself(info);
                }
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
        disableToolbarButtons: function(isDisable){
            dojo.forEach(this.toolbarActions, function(action){
                var button = dijit.byId('schedule_of_rate-toolbar_'+action);
                button._setDisabledAttr(isDisable);
            });
        },
        updateTabTitle: function(itemId){
            var tabArea = this.tabArea,
                tac = tabArea.getChildren(),
                store = this.left.model.store;
            store.fetchItemByIdentity({ 'identity' : itemId,  onItem : function(item){
                for(var i in tac){
                    if(typeof tac[i].lib_info != "object") continue;
                    if(tac[i].lib_info.id == item.id){
                        tac[i].lib_info.name = item.name;
                        tac[i].set('title', buildspace.truncateString(item.name, 25));
                        tabArea.resize();
                        break;
                    }
                }
            }});
        },
        addScheduleOfRate: function(){
            var self = this, store = this.libraryStore;
            dojo.xhrPost(xhrArgs = {
                url: 'scheduleOfRate/scheduleOfRateAdd',
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        store.newItem({id:resp.item.id, name:resp.item.name, _csrf_token:resp.item._csrf_token});
                        store.save();
                        var panelIndex = self.borderContainer.getIndexOfChild(self.left);
                        if(panelIndex < 0){
                            self.borderContainer.addChild(self.left);
                        }
                    }
                },
                error: function(error) {/*oh fuck!*/}
            });
        },
        editScheduleOfRate: function(){
            var item = this.left.selectedNode.item;
            this.left._itemNodesMap[ item.id ][0].labelWidget.edit();
        },
        deleteScheduleOfRate: function(){
            var self = this, item = this.left.selectedNode.item, store = this.libraryStore;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.deleting+'. '+nls.pleaseWait+'...'
            });
            var tooltip = dijit.byId('TreeInlineEditBox_error-tooltip');
            if(tooltip){
                tooltip.destroyRecursive();
            }
            var xhrArgs = {
                url: 'scheduleOfRate/scheduleOfRateDelete',
                content: {id: item.id, _csrf_token:item._csrf_token},
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        var tac = self.tabArea.getChildren();
                        for(var i in tac){
                            if(typeof tac[i].lib_info != "object") continue;
                            if(tac[i].lib_info.id == item.id){
                                self.tabArea.removeChild(tac[i]);
                                break;
                            }
                        }
                        store.deleteItem(item);
                        store.save();
                        pb.hide();
                    }
                    self.disableToolbarButtons(true);
                },
                error: function(error) {pb.hide();}
            };

            var onYes = function(){
                pb.show();
                dojo.xhrPost(xhrArgs);
            };

            var content = '<div>'+nls.deleteScheduleOfRateAndAllData+'</div>';
            buildspace.dialog.confirm(nls.confirmation,content,60,280, onYes);
        },
        treeContextMenuItems: function(ctxMenu){
            var self = this, tree = this.left, item = this.left.selectedItem;
            if(item.id == 'root'){
                ctxMenu.addChild(new dijit.MenuItem({
                    label: nls.newScheduleOfRate,
                    iconClass:"icon-16-container icon-16-add",
                    onClick: dojo.hitch(self, 'addScheduleOfRate')
                }));
                ctxMenu.addChild(new dijit.MenuItem({
                    label: nls.reload,
                    iconClass:"icon-16-container icon-16-reload",
                    onClick: function(){
                        tree.model._requeryTop();//dummy reload since we only have one item in root's context menu. Just to make sure context menu looks kinda retarded
                        tree.collapseAll();
                        tree.expandAll();
                    }
                }));
            }else{
                ctxMenu.addChild(new dijit.MenuItem({
                    label: nls.edit,
                    iconClass:"icon-16-container icon-16-edit",
                    onClick: dojo.hitch(self,'editScheduleOfRate')
                }));
                ctxMenu.addChild(new dijit.MenuItem({
                    label: nls.delete,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(self, 'deleteScheduleOfRate')
                }));
            }
        },
        makeTab: function(id, name, content){
            var stackContainer = dijit.byId('scheduleOfRate_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('scheduleOfRate_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'scheduleOfRate_'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: nls.trade,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'scheduleOfRate_'+id+'-stackContainer'
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(new dijit.layout.ContentPane({
                id: 'scheduleOfRate_'+id+'-controllerPane',
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

            dojo.subscribe('scheduleOfRate_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('scheduleOfRate_'+id+'-stackContainer');

                if(widget){
                    var children  = widget.getChildren();
                    var index     = dojo.indexOf(children, dijit.byId(page.id));

                    index = index + 1;

                    if(children.length > index){

                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyRecursive(true);
                            index = index + 1;

                            //remove any add-resource button from stack container if any
                            var addResourceCatBtn = dijit.byId('add_resource_category_'+id+'-btn');
                            if(addResourceCatBtn)
                                addResourceCatBtn.destroy();
                        }

                        var selectedIndex = page.grid.selection.selectedIndex;

                        page.grid.store.save();
                        page.grid.store.close();

                        var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                            handle.remove();
                            if(selectedIndex > -1){
                                this.scrollToRow(selectedIndex);
                                this.selection.setSelected(selectedIndex, true);
                            }
                        });

                        page.grid.sort();
                    }
                }
            });
            pane.lib_info = {
                name: name,
                id: id
            }
        },
        onItem: function(item){
            this.disableToolbarButtons(true);
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

            var grid = self.grid = buildspace.apps.ScheduleOfRateLibrary.grid({
                id: 'trade-page-container-' + id,
                stackContainerTitle: name,
                pageId: 'trade-page-'+id,
                libraryId: id,
                gridOpts: {
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', editable:true, cellType:'buildspace.widget.grid.cells.Textarea' },
                        {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'},
                        {name: nls.recalculateResourceStatus, field: 'recalculate_resource_status', width:'100px', styles:'text-align:center;', formatter: formatter.recalculateSorCellFormatter }
                    ],
                    addUrl:'scheduleOfRate/tradeAdd',
                    updateUrl:'scheduleOfRate/tradeUpdate',
                    deleteUrl:'scheduleOfRate/tradeDelete',
                    pasteUrl:'scheduleOfRate/tradePaste',
                    recalculateUrl: 'scheduleOfRate/tradeRecalculate',
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createItemGrid(id, _item);
                        }
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
                var grid = buildspace.apps.ScheduleOfRateLibrary.grid({
                    id: 'item-page-container-' + libraryId,
                    stackContainerTitle: tradeObj.description,
                    pageId: 'item-page-'+tradeObj.id,
                    libraryId: libraryId,
                    tradeId: tradeObj.id,
                    type: 'tree',
                    gridOpts: {
                        tradeGrid: self.grid,
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.description, field: 'description', width:'auto', editable:true, cellType:'buildspace.widget.grid.cells.Textarea', formatter: formatter.treeCellFormatter },
                            {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', editable:true, cellType:'dojox.grid.cells.Select', options: hierarchyTypes.options, values:hierarchyTypes.values, formatter: formatter.typeCellFormatter },
                            {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', editable:true, cellType:'dojox.grid.cells.Select', options: uom.options, values:uom.values, formatter: formatter.unitIdCellFormatter},
                            {name: nls.rate, field: 'rate-value', field_name: 'rate-value', width:'150px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                            {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'},
                            {name: nls.recalculateResourceStatus, field: 'recalculate_resource_status', width:'100px', styles:'text-align:center;', formatter: formatter.recalculateSorCellFormatter }
                        ],
                        addUrl:'scheduleOfRate/itemAdd',
                        updateUrl:'scheduleOfRate/itemUpdate',
                        deleteUrl:'scheduleOfRate/itemDelete',
                        deleteRateUrl:'scheduleOfRate/itemRateDelete',
                        pasteUrl:'scheduleOfRate/itemPaste',
                        indentUrl:'scheduleOfRate/itemIndent',
                        outdentUrl:'scheduleOfRate/itemOutdent',
                        recalculateUrl: 'scheduleOfRate/itemRecalculate',
                        onRowDblClick: function(e){
                            var item = this.getItem(e.rowIndex);
                            if(item.id > 0 && item.description[0] !== null && item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM){
                                self.createBuildUpRateContainer(libraryId, item, store);
                            }
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
                                    addUrl:'scheduleOfRate/buildUpRateItemAdd',
                                    updateUrl:'scheduleOfRate/buildUpRateItemUpdate',
                                    deleteUrl:'scheduleOfRate/buildUpRateItemDelete',
                                    pasteUrl:'scheduleOfRate/buildUpRateItemPaste',
                                    store: store,
                                    buildUpSummaryWidget: buildUpSummaryWidget,
                                    structure: [
                                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                        {name: nls.description, field: 'description', width:'auto', editable:true, cellType:'buildspace.widget.grid.cells.Textarea', formatter: formatter.linkedCellFormatter },
                                        {name: nls.number, field: 'number-value', width:'100px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                        {name: nls.constant, field: 'constant-value', width:'100px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                        {name: nls.qty, field: 'quantity-value', width:'70px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaNumberCellFormatter},
                                        {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', editable:true, cellType:'dojox.grid.cells.Select', options: uom.options, values:uom.values, formatter: formatter.linkedUnitIdCellFormatter},
                                        {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                        {name: nls.total, field: 'total', width:'120px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter},
                                        {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
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
                    var container = dijit.byId('scheduleOfRate_'+libraryId+'-stackContainer');
                    if(container){
                        var controllerPane = dijit.byId('scheduleOfRate_'+libraryId+'-controllerPane'),
                            resourceCatBtn = new dijit.form.Button({
                                id: 'add_resource_category_'+libraryId+'-btn',
                                label: nls.addResourceCategory,
                                style: "float:right;color:#333333!important;",
                                iconClass: "icon-16-container icon-16-add",
                                baseClass: 'buildUpRateImportResourceCategory',
                                onClick: function(e){
                                    var addResourceDiag = AddResourceCategoryDialog({
                                        libraryId: libraryId,
                                        scheduleOfRateItem: item,
                                        currencyAbbr: currencySetting,
                                        baseContainer: baseContainer
                                    });
                                    addResourceDiag.show();
                                }
                            });

                        controllerPane.addChild(resourceCatBtn);

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
            if (this.win && typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});