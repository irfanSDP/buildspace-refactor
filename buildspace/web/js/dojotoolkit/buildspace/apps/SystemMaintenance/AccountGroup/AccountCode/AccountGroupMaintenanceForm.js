define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/enhanced/plugins/Rearrange',
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/Filter',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/AccountGroup',
    './AccountCodeMaintenanceForm',
],
function(declare, lang, Rearrange, evt, keys, focusUtil, Textarea, FilterToolbar, GridFormatter, nls, AccountCodeMaintenanceForm) {
    var AccountGroupMaintenenceGrid = declare('buildspace.apps.AccountGroupMaintenance.AccountGroupMaintenenceGrid', dojox.grid.EnhancedGrid, {
        region: "center",
        style: "border-top:none;",
        selectedItem: null,
        keepSelection: true,
        rowSelector: '0px',
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);

            this.on("RowContextMenu", function(e){
                this.selection.clear();
                var item = this.getItem(e.rowIndex);
                this.selection.setSelected(e.rowIndex, true);
                this.contextMenu(e);
                if(item && !isNaN(parseInt(item.id[0]))){
                    this.disableToolbarButtons(false);
                }else{
                    this.disableToolbarButtons(true, ['Add']);
                }
            }, true);

            this.on('RowClick', function(e){
                var item = this.getItem(e.rowIndex);
                if(item && !isNaN(parseInt(item.id[0]))){
                    this.disableToolbarButtons(false);
                }else{
                    this.disableToolbarButtons(true, ['Add']);
                }
            });
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this;
            var item = this.getItem(rowIdx);
            var store = this.store;

            if(val !== item[inAttrName][0]) {
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                
                var params = {
                    id: item.id,
                    attr_name: inAttrName,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                };

                var url = "systemMaintenance/accountGroupUpdate";

                if(item && isNaN(parseInt(item.id[0]))){
                    var prevItem = rowIdx > 0 ? this.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0
                    });
                    url = "systemMaintenance/accountGroupAdd";
                }

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }

                    store.save();
                };

                pb.show().then(function() {
                    dojo.xhrPost({
                        url: url,
                        content: params,
                        handleAs: 'json',
                        load:function(resp) {
                            if(resp.success) {
                                if(!isNaN(parseInt(item.id[0]))) {
                                    updateCell(resp.data, store);
                                } else {
                                    store.deleteItem(item);
                                    store.save();
                                    dojo.forEach(resp.items, function(item){
                                        store.newItem(item);
                                    });
                                    store.save();
                                }

                                var cell = self.getCellByField(inAttrName);
                                window.setTimeout(function() {
                                    self.focus.setFocusIndex(rowIdx, cell.index);
                                }, 10);
                                pb.hide();
                            }
                        },
                        error:function(error) {
                            pb.hide();
                        }
                    });
                });
            }
            this.inherited(arguments);
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        contextMenu: function(e){
            var rowCtxMenu = this.rowCtxMenu = new dijit.Menu();
            this.contextMenuItems(e);
            var info = {target: e.target, coords: e.keyCode !== keys.F10 && "pageX" in e ? {x: e.pageX, y: e.pageY } : null};
            var item = this.getItem(e.rowIndex);
            if(rowCtxMenu && item && (this.selection.isSelected(e.rowIndex) || e.rowNode && html.hasClass(e.rowNode, 'dojoxGridRowbar'))){
                rowCtxMenu._openMyself(info);
                evt.stop(e);
                return;
            }
        },
        contextMenuItems: function(e){
            var item = this.getItem(e.rowIndex);

            console.log(item);

            if(item && !isNaN(parseInt(item.id[0]))){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.addRow,
                    iconClass:"icon-16-container icon-16-add",
                    onClick: dojo.hitch(this, "addRow")
                }));
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(this, "deleteRow")
                }));

                if(item.disable == 'Yes'){
                    this.rowCtxMenu.addChild(new dijit.MenuItem({
                        label: nls.enable,
                        iconClass:"icon-16-container icon-16-enable",
                        onClick: dojo.hitch(this, "enable")
                    }));
                }
                else{
                    this.rowCtxMenu.addChild(new dijit.MenuItem({
                        label: nls.disable,
                        iconClass:"icon-16-container icon-16-disable",
                        onClick: dojo.hitch(this, "disable")
                    }));
                }
            }
        },
        addRow: function() {
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            if(this.selection.selectedIndex > -1) {
                var rowIndex = this.selection.selectedIndex;
                var self = this;
                var prevItemId;
                var content;
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var store = this.store;
                var itemBefore = this.getItem(rowIndex);

                if(!isNaN(parseInt(itemBefore.id[0]))) {
                    prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                    content = {
                        prev_item_id: prevItemId,
                        before_id: itemBefore.id,
                        _csrf_token:itemBefore._csrf_token
                    };
                } else {
                    prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                    content = {
                        id: itemBefore.id,
                        prev_item_id: prevItemId,
                        _csrf_token:itemBefore._csrf_token
                    };
                }

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'systemMaintenance/accountGroupAdd',
                        content: content,
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                dojo.forEach(resp.items,function(data){
                                    if(data.id > 0){
                                        var item = store.newItem(data);
                                        store.save();
                                        var itemIdx = self.getItemIndex(item);
                                        self.rearranger.moveRows([itemIdx], rowIndex);
                                        self.selection.clear();
                                    }
                                });
                            }
                            window.setTimeout(function() {
                                self.selection.setSelected(rowIndex, true);
                                self.focus.setFocusIndex(rowIndex, 1);
                            },30);
                            pb.hide();
                        },
                        error: function(error) {
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                            pb.hide();
                        }
                    });
                });
            }
        },
        deleteRow: function() {
            var rowIndex = this.selection.selectedIndex;
            var item     = this.getItem(rowIndex);

            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            
            if(this.selection.selectedIndex > -1 && item && !isNaN(parseInt(item.id[0]))) {
                var self = this;
                var pb   = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });

                buildspace.dialog.confirm(nls.deleteAccountGroupConfirmation, '<div>'+nls.doYouReallywantToDeleteAccountGroup+' ?</div>', 90, 320, function() {
                    pb.show().then(function() {
                        dojo.xhrPost({
                            url: "systemMaintenance/accountGroupDelete",
                            content: { id: item.id, _csrf_token: item._csrf_token },
                            handleAs: 'json',
                            load: function(data) {
                                if(data.success){
                                    var items = data.items;
                                    var store = self.store;

                                    for(var i=0, len=items.length; i<len; ++i){
                                        store.fetchItemByIdentity({ 'identity' : items[i].id,  onItem : function(itm){
                                            store.deleteItem(itm);
                                            store.save();
                                        }});
                                    }

                                    items.length = 0;
                                    
                                    self.selection.clear();
                                    window.setTimeout(function() {
                                        self.focus.setFocusIndex(rowIndex, 0);
                                    }, 10);
                                    self.disableToolbarButtons(true);
                                }else{
                                    buildspace.dialog.alert(nls.cannotDeleteAccountGroupTitle,nls.cannotDeleteAccountGroupMsg,90,320);
                                }

                                pb.hide();

                                self.selectedItem = null;
                                self.pasteOp = null;
                            },
                            error: function(error) {
                                self.selection.clear();
                                self.disableToolbarButtons(true);
                                self.selectedItem = null;
                                self.pasteOp = null;
                                pb.hide();
                            }
                        });
                    });
                });
            }
        },
        disable: function() {
            var rowIndex = this.selection.selectedIndex;
            var item     = this.getItem(rowIndex);

            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            
            if(this.selection.selectedIndex > -1 && item && !isNaN(parseInt(item.id[0]))) {
                var self = this;
                var pb   = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

                buildspace.dialog.confirm(nls.disableAccountGroupConfirmation, '<div>'+nls.doYouReallywantToDisableAccountGroup+' ?</div>', 90, 320, function() {
                    pb.show().then(function() {
                        dojo.xhrPost({
                            url: "systemMaintenance/accountGroupDisable",
                            content: { id: item.id, _csrf_token: item._csrf_token },
                            handleAs: 'json',
                            load: function(data) {
                                if(data.success){

                                    self.reload();
                                    
                                    self.selection.clear();
                                    window.setTimeout(function() {
                                        self.focus.setFocusIndex(rowIndex, 0);
                                    }, 10);
                                    self.disableToolbarButtons(true);
                                }

                                pb.hide();

                                self.selectedItem = null;
                                self.pasteOp = null;
                            },
                            error: function(error) {
                                self.selection.clear();
                                self.disableToolbarButtons(true);
                                self.selectedItem = null;
                                self.pasteOp = null;
                                pb.hide();
                            }
                        });
                    });
                });
            }
        },
        enable: function() {
            var rowIndex = this.selection.selectedIndex;
            var item     = this.getItem(rowIndex);

            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            
            if(this.selection.selectedIndex > -1 && item && !isNaN(parseInt(item.id[0]))) {
                var self = this;
                var pb   = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

                buildspace.dialog.confirm(nls.enableAccountGroupConfirmation, '<div>'+nls.doYouReallywantToEnableAccountGroup+' ?</div>', 90, 320, function() {
                    pb.show().then(function() {
                        dojo.xhrPost({
                            url: "systemMaintenance/accountGroupEnable",
                            content: { id: item.id, _csrf_token: item._csrf_token },
                            handleAs: 'json',
                            load: function(data) {
                                if(data.success){
                                    self.reload();
                                    
                                    self.selection.clear();
                                    window.setTimeout(function() {
                                        self.focus.setFocusIndex(rowIndex, 0);
                                    }, 10);
                                    self.disableToolbarButtons(true);
                                }

                                pb.hide();

                                self.selectedItem = null;
                                self.pasteOp = null;
                            },
                            error: function(error) {
                                self.selection.clear();
                                self.disableToolbarButtons(true);
                                self.selectedItem = null;
                                self.pasteOp = null;
                                pb.hide();
                            }
                        });
                    });
                });
            }
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            var addRowBtn = dijit.byId('AddAccountGroupRow-button');
            var deleteRowBtn = dijit.byId('DeleteAccountGroupRow-button');

            addRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(label+'AccountGroupRow-button');
                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    return declare('buildspace.apps.AccountGroupMaintenance.AccountGroupMaintenanceFormContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        postCreate: function() {
            this.inherited(arguments);

            var formatter = new GridFormatter();

            var stackContainer = dijit.byId('accountCodeMaintenance-stackContainer');

            if(stackContainer){
                dijit.byId('accountCodeMaintenance-stackContainer').destroyRecursive();
            }

            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;',
                region: "center",
                id: 'accountCodeMaintenance-stackContainer'
            });

            var grid = this.AccountGroupMaintenenceGrid = new AccountGroupMaintenenceGrid({
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: "systemMaintenance/getAccountGroups"
                }),
                structure: [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.name, field: 'name', editable: true, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'auto', formatter:formatter.disabledCellFormatter},
                    {name: nls.disable, field: 'disable', styles:'text-align:center;', width:'120px'},
                    {name: nls.updatedAt, field: 'updated_at', styles:'text-align:center;', width:'120px'},
                ],
                onRowDblClick: dojo.hitch(this, 'maintainAccountCodes')
            });

            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'AddAccountGroupRow-button',
                    label: nls.addRow,
                    iconClass: "icon-16-container icon-16-add",
                    disabled: true,
                    onClick: dojo.hitch(grid, 'addRow')
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'DeleteAccountGroupRow-button',
                    label: nls.deleteRow,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: true,
                    onClick: dojo.hitch(grid, 'deleteRow')
                })
            );

            var filter  = new FilterToolbar({
                grid: grid,
                region:"top",
                filterFields: [ {name : nls.name}]
            });

            var gridContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                baseClass: 'form',
                gutters: false,
                region: "center"
            });

            gridContainer.addChild(filter);
            gridContainer.addChild(toolbar);
            gridContainer.addChild(grid);

            var stackPane = new dijit.layout.ContentPane({
                title: nls.accountGroup,
                content: gridContainer
            });

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'accountCodeMaintenance-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding: 0px; overflow: hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            stackContainer.addChild(stackPane);

            this.addChild(stackContainer);
            this.addChild(controllerPane);
            
            dojo.subscribe('accountCodeMaintenance-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('accountCodeMaintenance-stackContainer');
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
        },
        maintainAccountCodes: function(e) {
            var self = this;
            var item = self.AccountGroupMaintenenceGrid.getItem(e.rowIndex);

            if(item.disable == 'No'){
                if(item && !isNaN(parseInt(item.id[0]))) {
                    this.createAccountCodeMaintenanceWindow(item);
                }
            }
        },
        createAccountCodeMaintenanceWindow: function(item) {
            var self = this;
            var stackPane = new dijit.layout.ContentPane({
                title: nls.accountCode,
                content: new AccountCodeMaintenanceForm({
                    accountGroupId: item.id[0]
                }),
            });

            self.stackContainer.addChild(stackPane);
            self.stackContainer.selectChild(stackPane);
        },
    });
});