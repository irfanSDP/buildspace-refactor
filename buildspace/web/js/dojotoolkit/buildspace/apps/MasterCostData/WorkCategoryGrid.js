define('buildspace/apps/MasterCostData/WorkCategoryGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/Rearrange",
    'buildspace/widget/grid/cells/Formatter',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dijit/Menu",
    "dijit/CheckedMenuItem",
    "dijit/focus",
    "dojox/grid/enhanced/plugins/Menu",
    "dojo/when",
    'dojo/aspect',
    './ElementGrid',
    './LinkProjectParticularsDialog',
    './WorkCategoryParticularSelectionDialog',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, lang, array, EnhancedGrid, Rearrange, GridFormatter, DropDownButton, DropDownMenu, MenuItem, Menu, CheckedMenuItem, focusUtil, MenuPlugin, when, aspect, ElementGrid, LinkProjectParticularsDialog, WorkCategoryParticularSelectionDialog, nls){

    var Grid = declare('buildspace.apps.MasterCostData.WorkCategoryGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        masterCostData: null,
        projectCostingItem: null,
        addUrl: 'masterCostData/addNewWorkCategory',
        updateUrl: 'masterCostData/updateItem',
        constructor:function(args){
            this.gridFormatter = new GridFormatter();
            this.structure = this.getGridStructure();
            this.inherited(arguments);
            this.rearranger = Rearrange(this, {});
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on('RowClick', function (e) {
                if (e.cell) {
                    var item = this.getItem(e.rowIndex);
                    if(item.id[0] > 0) {
                        self.enableToolbarButtons(true);
                    }
                    else if(item.id[0] === buildspace.constants.GRID_LAST_ROW) {
                        self.enableToolbarButtons(true);
                        var deleteButton = dijit.byId(self.projectCostingItem.id + 'work-category-delete-button');
                        deleteButton._setDisabledAttr(true);
                    }
                }
            });
        },
        enableToolbarButtons: function(enable){
            var deleteButton = dijit.byId(this.projectCostingItem.id + 'work-category-delete-button');
            deleteButton._setDisabledAttr(!enable);
            var addButton = dijit.byId(this.projectCostingItem.id + 'work-category-add-button');
            addButton._setDisabledAttr(!enable);
        },
        getGridStructure: function(){
            return [
                {
                    name: 'No.',
                    field: 'count',
                    width:'30px',
                    styles:'text-align:center;',
                    formatter: Formatter.rowCountCellFormatter
                },{
                    name: nls.description,
                    field: 'description',
                    width:'auto',
                    cellType: 'buildspace.widget.grid.cells.Textarea',
                    editable: true
                }
            ];
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            if(inAttrName != 'description') return;

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    attr_name: inAttrName,
                    val: val,
                    master_cost_data_id: self.masterCostData.id,
                    parent_id: self.projectCostingItem.id,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                if(item.id==buildspace.constants.GRID_LAST_ROW){
                    var prevItem = rowIdx > 0 ? self.getItem(rowIdx-1):false;
                    var currentItem = self.getItem(rowIdx);
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0,
                        current_id: currentItem.id
                    });
                    url = this.addUrl;
                }

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }
                    store.save();
                };

                var xhrArgs = {
                    url: url,
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            if(item.id > 0){
                                updateCell(resp.data, store);
                            }else{
                                store.deleteItem(item);
                                store.save();
                                dojo.forEach(resp.items, function(item){
                                    store.newItem(item);
                                });
                                store.save();
                                self.enableToolbarButtons(false);
                            }
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
                };

                pb.show();
                dojo.xhrPost(xhrArgs);
            }
            self.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            return true;
        },
        reload: function(){
            this.store.save();
            this.store.close();
            this.sort();
            this._refresh();
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return parseInt(rowIdx)+1;
        }
    };

    return declare('buildspace.apps.MasterCostData.WorkCategoriesContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        masterCostData: null,
        projectCostingItem: null,
        grid: null,
        stackContainer: null,
        postCreate: function(){
            this.inherited(arguments);
            var grid = this.createBreakdownGrid();

            var gridContainer = new dijit.layout.ContentPane( {
                style:"padding:0px;border:none;width:100%;height:100%;",
                region: 'center',
                content: grid,
                grid: grid
            });

            this.addChild(this.createToolbar());
            this.addChild(gridContainer);
        },
        createToolbar: function(){
            var self = this;

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    id: self.projectCostingItem.id + 'work-category-add-button',
                    label: nls.addRow,
                    iconClass: "icon-16-container icon-16-add",
                    disabled: self.grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(){
                        if (self.grid.selection.selectedIndex > -1) {
                            self.addRow(self.grid.selection.selectedIndex);
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    id: self.projectCostingItem.id + 'work-category-delete-button',
                    label: nls.deleteRow,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: true,
                    style: "outline:none!important;",
                    onClick: function () {
                        if (self.grid.selection.selectedIndex > -1) {
                            var item = self.grid.getItem(self.grid.selection.selectedIndex);
                            if (item.id[0] > 0) {
                                self.deleteRow(item);
                            }
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.showHideColumns,
                    iconClass: "icon-16-container icon-16-dial",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.createWorkCategoryParticularSelectionDialog();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.refresh,
                    iconClass: "icon-16-container icon-16-reload",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.grid.reload();
                    }
                })
            );

            return toolbar;
        },
        createBreakdownGrid: function(){
            var self = this;
            self.grid = Grid({
                masterCostData: self.masterCostData,
                projectCostingItem: self.projectCostingItem,
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    var field = e.cell.field;

                    if(item.id == buildspace.constants.GRID_LAST_ROW) return;

                    self.createElementGrid(item);
                },
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"masterCostData/getWorkCategoryList/masterCostData/"+this.masterCostData.id+"/id/"+this.projectCostingItem.id
                })
            });

            return self.grid;
        },
        createElementGrid: function(item){
            var elementGrid = new ElementGrid({
                title: buildspace.truncateString(item.description, 60),
                stackContainer: this.stackContainer,
                masterCostData: this.masterCostData,
                workCategory: item,
            });

            this.stackContainer.addChild(elementGrid);
            this.stackContainer.selectChild(elementGrid);
        },
        activateLinkedProjectParticularsDialog: function(item, field) {
            var linkDialog = new LinkProjectParticularsDialog({
                masterCostData: this.masterCostData,
                item: item,
                field: field
            });
            linkDialog.show();
        },
        addRow: function(rowIndex){
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.addingRow+'. '+nls.pleaseWait+'...'
                }),
                store = self.grid.store,
                currentItem = self.grid.getItem(rowIndex);

            var prevItemId = (rowIndex > 0) ? self.grid.getItem(rowIndex-1).id : 0;

            var content = {
                master_cost_data_id: self.masterCostData.id,
                parent_id: self.projectCostingItem.id,
                prev_item_id: prevItemId,
                current_id: currentItem.id,
                _csrf_token:currentItem._csrf_token
            };

            pb.show().then(function(){
                dojo.xhrPost({
                    url: self.grid.addUrl,
                    content: content,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            dojo.forEach(resp.items,function(data){
                                if(data.id > 0){
                                    var item = store.newItem(data);
                                    store.save();
                                    var itemIdx = self.grid.getItemIndex(item);
                                    self.grid.rearranger.moveRows([itemIdx], rowIndex);
                                    self.grid.selection.clear();
                                }
                            });
                        }
                        window.setTimeout(function() {
                            self.grid.selection.setSelected(rowIndex, true);
                            self.grid.focus.setFocusIndex(rowIndex, 1);
                        },30);
                        pb.hide();
                    },
                    error: function(error) {
                        self.grid.selection.clear();
                        self.grid.enableToolbarButtons(true);
                        pb.hide();
                    }
                });
            });
        },
        deleteRow: function (item) {
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.deleting + '. ' + nls.pleaseWait + '...'
                });

            var onYes = function () {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'masterCostData/deleteItem',
                        content: {id: item.id, _csrf_token: item._csrf_token},
                        handleAs: 'json',
                        load: function (resp) {
                            if (resp.success) {
                                self.grid.store.deleteItem(item)
                                self.grid.store.save();
                                self.grid.reload();
                            }
                            else{
                                buildspace.dialog.alert(nls.cannotBeDeleted, resp.errorMsg, 80, 300);
                            }
                            pb.hide();
                            self.grid.selection.clear();
                            self.grid.enableToolbarButtons(false);
                        },
                        error: function (error) {
                            pb.hide();
                            self.grid.selection.clear();
                            self.grid.enableToolbarButtons(false);
                        }
                    });
                });
            };

            buildspace.dialog.confirm(nls.confirmation, nls.confirmationMessage + '<br/>' + nls.willDeleteAllChildItems + '<br/>' + nls.cannotBeUndone, 80, 300, onYes);
        },
        createWorkCategoryParticularSelectionDialog: function(){
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            var params = {
                _csrf_token: self.projectCostingItem._csrf_token ? self.projectCostingItem._csrf_token : null
            };

            var xhrArgs = {
                url: "masterCostData/getWorkCategoryParticulars/id/"+self.projectCostingItem.id,
                content: params,
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        var workCategoryParticularDialog = new WorkCategoryParticularSelectionDialog({
                            gridUrl: "masterCostData/getProjectParticularList/master_cost_data_id/"+self.masterCostData.id,
                            masterCostData: self.masterCostData,
                            projectCostingItem: self.projectCostingItem,
                            selectedItemIds: resp.selected_ids
                        });

                        workCategoryParticularDialog.show();
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            };

            pb.show();
            dojo.xhrPost(xhrArgs);
        }
    });
});