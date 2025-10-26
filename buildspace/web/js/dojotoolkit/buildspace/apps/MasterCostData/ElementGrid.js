define('buildspace/apps/MasterCostData/ElementGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/Rearrange",
    'buildspace/widget/grid/cells/Formatter',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dijit/focus",
    "dojo/when",
    'dojo/aspect',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, lang, EnhancedGrid, Rearrange, GridFormatter, DropDownButton, DropDownMenu, MenuItem, focusUtil, when, aspect, nls){

    var Grid = declare('buildspace.apps.MasterCostData.ElementGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        masterCostData: null,
        workCategory: null,
        addUrl: 'masterCostData/addNewElement',
        updateUrl: 'masterCostData/updateItem',
        constructor:function(args){
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
                        var deleteButton = dijit.byId(self.workCategory.id + 'element-delete-button');
                        deleteButton._setDisabledAttr(true);
                    }
                }
            });
        },
        enableToolbarButtons: function(enable){
            var deleteButton = dijit.byId(this.workCategory.id + 'element-delete-button');
            deleteButton._setDisabledAttr(!enable);
            var addButton = dijit.byId(this.workCategory.id + 'element-add-button');
            addButton._setDisabledAttr(!enable);
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    attr_name: inAttrName,
                    val: val,
                    master_cost_data_id: self.masterCostData.id,
                    parent_id: self.workCategory.id,
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

    return declare('buildspace.apps.MasterCostData.ElementsContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        masterCostData: null,
        workCategory: null,
        grid: null,
        stackContainer: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var grid = this.createBreakdownGrid();

            var gridContainer = new dijit.layout.ContentPane( {
                style:"padding:0px;border:none;width:100%;height:100%;",
                region: 'center',
                content: grid,
                grid: grid
            });

            this.addChild(self.createToolbar());
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
                    id: self.workCategory.id + 'element-add-button',
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
                    id: this.workCategory.id + 'element-delete-button',
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

            return toolbar;
        },
        createBreakdownGrid: function(){
            var self = this;
            self.grid = Grid({
                masterCostData: self.masterCostData,
                workCategory: self.workCategory,
                structure:[
                    {
                        name: 'No.',
                        field: 'count',
                        width:'30px',
                        styles:'text-align:center;',
                        formatter: Formatter.rowCountCellFormatter
                    },{
                        name: nls.element,
                        field: 'description',
                        width:'auto',
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        editable: true
                    }
                ],
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"masterCostData/getElementList/masterCostData/"+this.masterCostData.id+"/id/"+this.workCategory.id
                })
            });

            return self.grid;
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
                parent_id: self.workCategory.id,
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

            buildspace.dialog.confirm(nls.confirmation, nls.confirmationMessage + '<br/>' + nls.cannotBeUndone, 80, 300, onYes);
        }
    });
});