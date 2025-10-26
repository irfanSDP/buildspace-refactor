define(["dojo/_base/declare",
    "dojo/aspect",
    'dojo/_base/event',
    'dojo/when',
    "dijit/Menu",
    'dojo/keys',
    'dojo/on',
    "buildspace/widget/grid/cells/Formatter",
    "dijit/Tooltip",
    "./rfqRateSelectionGrid",
    "dojo/i18n!buildspace/nls/ResourceLibrary"], function(declare, aspect, evt, when, Menu, keys, on, GridFormatter, Tooltip, rfqRateSelectionGrid, nls){

    return declare('buildspace.apps.ResourceLibrary', buildspace.apps._App, {
        win: null,
        toolbarActions: ['edit','delete'],
        init: function(args){
            var self = this;
            this.win = new buildspace.widget.Window({
                title: nls.appName,
                onClose: dojo.hitch(this, "kill")
            });

            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;width:100%;"});
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.newResource,
                    iconClass: "icon-16-container icon-16-add",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(self,'addResource')
                })
            );

            dojo.forEach(this.toolbarActions, function(action){
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id:'resourceLibrary-toolbar_'+action,
                        label: nls[action],
                        iconClass: "icon-16-container icon-16-"+action,
                        style:"outline:none!important;",
                        disabled: true,
                        onClick: dojo.hitch(self,action+'Resource')
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
                url:"resourceLibrary/resourceList"
            });

            var treeModel = new dijit.tree.ForestStoreModel({
                store: libraryStore,
                rootId: "root",
                rootLabel: nls.resources
            });

            var left = this.left = new dijit.EditableTree({
                model: treeModel,
                splitter: true,
                region: "left",
                openOnClick:true,
                ajaxSubmitUrl: 'resourceLibrary/resourceUpdate',
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

            var borderContainer = this.borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                gutters: false
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
                var button = dijit.byId('resourceLibrary-toolbar_'+action);
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
                };
            }});
        },
        addResource: function(){
            var self = this,
                store = this.libraryStore;

            dojo.xhrPost({
                url: 'resourceLibrary/resourceAdd',
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        store.newItem({id:resp.item.id, name:resp.item.name, can_be_deleted:resp.item.can_be_deleted, _csrf_token:resp.item._csrf_token});
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
        editResource: function(){
            var item = this.left.selectedNode.item;
            this.left._itemNodesMap[ item.id ][0].labelWidget.edit();
        },
        deleteResource: function(){
            var self = this, item = this.left.selectedNode.item, store = this.libraryStore;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.deleting+'. '+nls.pleaseWait+'...'
            });

            var tooltip = dijit.byId('TreeInlineEditBox_error-tooltip');

            if(tooltip){
                tooltip.destroyRecursive();
            }

            if (item.can_be_deleted[0]) {
                var onYes = function() {
                    pb.show();
                    dojo.xhrPost({
                        url: 'resourceLibrary/resourceDelete',
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
                    });
                };

                var content = '<div>'+nls.deleteResourceAndAllData+'</div>';
                buildspace.dialog.confirm(nls.confirmation,content,60,280, onYes);
            } else {
                new buildspace.dialog.alert(nls.deleteLinkResourceTitle, nls.deleteLinkResourceMsg, 60, 300);
            }
        },
        treeContextMenuItems: function(ctxMenu){
            var self = this, tree = this.left, item = this.left.selectedItem;
            if(item.id == 'root'){
                ctxMenu.addChild(new dijit.MenuItem({
                    label: nls.newResource,
                    iconClass:"icon-16-container icon-16-add",
                    onClick: dojo.hitch(self, 'addResource')
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
                    onClick: dojo.hitch(self,'editResource')
                }));
                ctxMenu.addChild(new dijit.MenuItem({
                    label: nls.delete,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(self, 'deleteResource')
                }));
            }
        },
        makeTab: function(id, name, content){
            var stackContainer = dijit.byId('resourceLibraryGrid'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('resourceLibraryGrid'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;',
                region: "center",
                id: 'resourceLibraryGrid'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: nls.elements,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'resourceLibraryGrid'+id+'-stackContainer'
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                gutters: false
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(new dijit.layout.ContentPane({
                style: "padding: 0px; overflow: hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            }));

            var pane = new dijit.layout.ContentPane({
                closable: true,
                style: "padding: 0px; overflow: hidden;",
                title: buildspace.truncateString(name, 25),
                content: borderContainer
            });

            this.tabArea.addChild(pane);
            this.tabArea.selectChild(pane);

            dojo.subscribe('resourceLibraryGrid'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('resourceLibraryGrid'+id+'-stackContainer');

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
                url: "resourceLibrary/getTradeList/id/"+id,
                clearOnClose: true,
                urlPreventCache: true
            });

            var self = this,
                formatter = new GridFormatter();

            var grid = self.grid = buildspace.apps.ResourceLibrary.grid({
                id: 'trade-page-container-'+id,
                stackContainerTitle: name,
                pageId: 'page-'+id,
                libraryId: id,
                gridOpts: {
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', editable:true, cellType:'buildspace.widget.grid.cells.Textarea' },
                        {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
                    ],
                    addUrl:'resourceLibrary/tradeAdd',
                    updateUrl:'resourceLibrary/tradeUpdate',
                    checkBeforeDeleteUrl: 'resourceLibrary/tradeLinkCheckBeforeDelete',
                    deleteUrl:'resourceLibrary/tradeDelete',
                    pasteUrl:'resourceLibrary/tradePaste',
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createItemGrid(id, _item);
                        }
                    },
                    deleteLinkNotificationTitle: nls.deleteLinkedTradeNoticationTitle,
                    deleteLinkNotificationMsg: nls.deleteLinkedTradeNoticationMsg,
                    deleteLinkNormalTitle: nls.deleteTradeNormalTitle,
                    deleteLinkNormalMsg: nls.deleteTradeNormalMsg
                }
            });
            self.makeTab(id, name, grid);
        },
        createItemGrid: function(libraryId, tradeObj){
            var self = this, formatter = GridFormatter(),
                hierarchyTypes = {
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
                },
                store = new dojo.data.ItemFileWriteStore({
                    url:"resourceLibrary/getItemList/id/"+tradeObj.id,
                    clearOnClose: true,
                    urlPreventCache:true
                }),
                unitQuery = dojo.xhrGet({
                    url: "resourceLibrary/getUnits",
                    handleAs: "json"
                }),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show();

            return when(unitQuery, function(uom){
                pb.hide();
                var grid = buildspace.apps.ResourceLibrary.grid({
                    id: "item-page-container-"+libraryId,
                    stackContainerTitle: tradeObj.description,
                    pageId: 'item-page-'+tradeObj.id,
                    libraryId: libraryId,
                    tradeId: tradeObj.id,
                    type: 'tree',
                    gridOpts: {
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.description, field: 'description', width:'auto', editable:true, cellType:'buildspace.widget.grid.cells.Textarea', formatter: formatter.treeCellFormatter },
                            {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', editable:true, cellType:'dojox.grid.cells.Select', options: hierarchyTypes.options, values:hierarchyTypes.values, formatter: formatter.typeCellFormatter },
                            {name: nls.constant, field: 'constant-value', width:'160px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                            {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', editable:true, cellType:'dojox.grid.cells.Select', options: uom.options, values:uom.values, formatter: formatter.unitIdCellFormatter},
                            {name: nls.rate, field: 'rate-value', width:'100px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                            {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                            {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
                        ],
                        addUrl:'resourceLibrary/itemAdd',
                        updateUrl:'resourceLibrary/itemUpdate',
                        checkBeforeDeleteUrl: 'resourceLibrary/itemLinkCheckBeforeDelete',
                        deleteUrl:'resourceLibrary/itemDelete',
                        pasteUrl:'resourceLibrary/itemPaste',
                        indentUrl:'resourceLibrary/itemIndent',
                        outdentUrl:'resourceLibrary/itemOutdent',
                        deleteLinkNotificationTitle: nls.deleteLinkedItemNoticationTitle,
                        deleteLinkNotificationMsg: nls.deleteLinkedItemNoticationMsg,
                        deleteLinkNormalTitle: nls.deleteItemNormalTitle,
                        deleteLinkNormalMsg: nls.deleteItemNormalMsg,
                        onRowDblClick: function(e) {
                            var _this = this,
                                _item = _this.getItem(e.rowIndex)
                                colField = e.cell.field;

                            if ( _item.id > 0 && _item.description[0] !== null && _item.type[0] === buildspace.constants.HIERARCHY_TYPE_WORK_ITEM && colField === 'rate-value') {
                                self.createItemSupplierRateGrid(libraryId, _item);
                            }
                        }
                    }
                });
            },function(error){
                /* got fucked */
            });
        },
        createItemSupplierRateGrid: function(libraryId, item){
            var formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"resourceLibrary/getItemSupplierRateList/resourceItemId/"+item.id,
                    clearOnClose: true
                }),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show();

            dojo.xhrGet({
                url: 'resourceLibrary/getSelectedRatesFromRFQ/resourceItemId/'+item.id,
                handleAs: 'json',
                load: function(resp) {
                    var grid = new rfqRateSelectionGrid({
                        stackContainerTitle: item.description,
                        pageId: 'item-supplier-rate-page-'+item.id,
                        type: 'rateSelection',
                        libraryId: libraryId,
                        gridOpts: {
                            formInfo: resp.formInformation,
                            itemId: item.id,
                            store: store,
                            structure: [
                                {name: nls.supplierName, field: 'company_name', width:'250px'},
                                {name: nls.projectName, field: 'project_title', width:'250px'},
                                {name: nls.country, field: 'country', width:'100px', styles:'text-align: center;'},
                                {name: nls.state, field: 'state', width:'100px', styles:'text-align: center;'},
                                {name: nls.rate, field: 'rate', width:'120px', styles:'text-align:center;', cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.rfqQuantityCellFormatter},
                                {name: nls.remarks, field: 'remarks', width:'auto'},
                                {name: nls.lastUpdated, field: 'rate_last_updated_at', width:'120px', styles:'text-align: center;'}
                            ],
                            addUrl:'resourceLibrary/itemAdd',
                            updateUrl:'resourceLibrary/itemUpdate',
                            checkBeforeDeleteUrl: 'resourceLibrary/itemLinkCheckBeforeDelete',
                            deleteUrl:'resourceLibrary/itemDelete',
                            pasteUrl:'resourceLibrary/itemPaste',
                            indentUrl:'resourceLibrary/itemIndent',
                            outdentUrl:'resourceLibrary/itemOutdent',
                            deleteLinkNotificationTitle: nls.deleteLinkedItemNoticationTitle,
                            deleteLinkNotificationMsg: nls.deleteLinkedItemNoticationMsg,
                            deleteLinkNormalTitle: nls.deleteItemNormalTitle,
                            deleteLinkNormalMsg: nls.deleteItemNormalMsg
                        }
                    });

                    pb.hide();
                },
                error: function(error) {
                    /*oh fuck!*/
                    pb.hide();
                }
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