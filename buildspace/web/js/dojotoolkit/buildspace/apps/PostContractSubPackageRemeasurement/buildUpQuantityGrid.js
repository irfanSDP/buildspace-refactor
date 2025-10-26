define('buildspace/apps/PostContractSubPackageRemeasurement/buildUpQuantityGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    'dojo/currency',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    'buildspace/widget/grid/cells/Select',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    'dojo/i18n!buildspace/nls/BuildUpQuantityGrid'], function(declare, lang, array, domAttr, Menu, Selector, Rearrange, FormulatedColumn, evt, keys, currency, focusUtil, html, xhr, PopupMenuItem, Select, Textarea, FormulaTextBox, nls ){

    var BuildUpQuantityGrid = declare('buildspace.apps.PostContractSubPackageRemeasurement.BuildUpQuantity.grid', dojox.grid.EnhancedGrid, {
        BillItem: null,
        billColumnSettingId: null,
        style: "border-top:none;",
        selectedItem: null,
        keepSelection: true,
        addUrl: null,
        updateUrl: null,
        deleteUrl: null,
        pasteUrl: null,
        buildUpSummaryWidget: null,
        type: null,
        locked: false,
        disableEditingMode: false,
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});

            if (args.locked) {
                this.disableEditingMode = true;
            }
        },
        canSort: function(inSortInfo){
            return false;
        },
        canEdit: function(inCell, inRowIndex) {
            if (this.disableEditingMode) {
                return false;
            }

            return this._canEdit;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            if(!this.locked){
                this.on("RowContextMenu", function(e){
                    self.selection.clear();
                    var item = self.getItem(e.rowIndex);
                    self.selection.setSelected(e.rowIndex, true);
                    self.contextMenu(e);
                    if(item && item.id > 0){
                        self.disableToolbarButtons(false);
                    } else {
                        self.disableToolbarButtons(true, ['Add']);
                    }
                }, true);

                this.on('RowClick', function(e){
                    var item = self.getItem(e.rowIndex);
                    if(item && item.id > 0){
                        self.disableToolbarButtons(false);
                    } else {
                        self.disableToolbarButtons(true, ['Add']);
                    }
                });
            }
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;
            if(val !== item[inAttrName][0]){
                var attrNameParsed = inAttrName.replace("-value","");//for any formulated column

                if(inAttrName.indexOf("-value") !== -1){
                    val = this.formulatedColumn.convertRowIndexToItemId(val, rowIdx);
                }

                var params = {
                    id: item.id,
                    attr_name: attrNameParsed,
                    val: val,
                    type: self.type,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                if(item.id==buildspace.constants.GRID_LAST_ROW){
                    var prevItem = rowIdx > 0 ? self.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0,
                        relation_id: item.relation_id,
                        bill_column_setting_id: self.billColumnSettingId
                    });
                    url = this.addUrl;
                }

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                        dojo.forEach(data.affected_nodes, function(node){
                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem){
                                for(var property in node){
                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(affectedItem, property, node[property]);
                                    }
                                }
                            }});
                        });
                    }
                    store.save();
                };

                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });

                pb.show();

                dojo.xhrPost({
                    url: url,
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            if(item && item.id > 0){
                                updateCell(resp.data, store);
                            }else{
                                store.deleteItem(item);
                                store.save();
                                dojo.forEach(resp.items, function(item){
                                    store.newItem(item);
                                });
                                store.save();
                                self.disableToolbarButtons(true);
                            }

                            self.updateTotalBuildUp(resp.total_build_up);
                            var cell = self.getCellByField(inAttrName);
                            window.setTimeout(function() {
                                self.focus.setFocusIndex(rowIdx, cell.index);
                            }, 10);
                        }

                        pb.hide();
                    },
                    error: function(error) {
                        console.log(error);
                        pb.hide();
                    }
                });
            }
            self.inherited(arguments);
        },
        dodblclick: function(e){
            //this.onRowDblClick(e);
        },
        addRow: function(rowIndex){
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            var self = this,
                content,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.addingRow+'. '+nls.pleaseWait+'...'
                }),
                store = self.store,
                itemBefore = self.getItem(rowIndex);
            if(itemBefore.id > 0){
                var content = { before_id: itemBefore.id, type: self.type, _csrf_token:itemBefore._csrf_token, bill_column_setting_id: self.billColumnSettingId, relation_id: itemBefore.relation_id };
            }else{
                var prevItemId = (rowIndex > 0) ? self.getItem(rowIndex-1).id : 0;
                var content = { id: itemBefore.id, type: self.type, prev_item_id: prevItemId, relation_id: itemBefore.relation_id, bill_column_setting_id: self.billColumnSettingId, _csrf_token:itemBefore._csrf_token }
            }
            pb.show();

            dojo.xhrPost({
                url: this.addUrl,
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
                    }, 30);
                    pb.hide();
                },
                error: function(error) {
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                    pb.hide();
                }
            });
        },
        deleteRow: function(rowIndex){
            var self = this, item = self.getItem(rowIndex),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            pb.show();

            var xhrArgs = {
                url: this.deleteUrl,
                content: { id: item.id, _csrf_token: item._csrf_token },
                handleAs: 'json',
                load: function(data) {
                    if(data.success){
                        var affectedNodes = data.affected_nodes;
                        var store = self.store;
                        store.deleteItem(item);
                        store.save();

                        for(var x=0, len=affectedNodes.length; x<len; ++x){
                            store.fetchItemByIdentity({ 'identity' : affectedNodes[x].id,  onItem : function(node){
                                for(var property in affectedNodes[x]){
                                    if(node.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(node, property, affectedNodes[x][property]);
                                    }
                                }
                                store.save();
                            }});
                        }
                        self.updateTotalBuildUp();
                    }
                    pb.hide();
                    self.selection.clear();
                    window.setTimeout(function() {
                        self.focus.setFocusIndex(rowIndex, 0);
                    }, 10);
                    self.disableToolbarButtons(true);
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
            };

            new buildspace.dialog.confirm(nls.deleteBuildUpQtyItemTitle, nls.deleteBuildUpQtyItemMsg, 80, 320, function() {
                dojo.xhrPost(xhrArgs);
            }, function() {
                pb.hide();
            });
        },
        cutItems: function(){
            this.selectedItem = this.selection.getFirstSelected();
            this.pasteOp = 'cut';
        },
        copyItems: function(){
            this.selectedItem = this.selection.getFirstSelected();
            this.pasteOp = 'copy';
        },
        pasteItem: function(rowIndex){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                }),
                store = self.store,
                targetItem = self.selection.getFirstSelected();
            var prevItemId = (targetItem.id == buildspace.constants.GRID_LAST_ROW && rowIndex > 0) ? self.getItem(rowIndex-1).id : 0;
            pb.show();

            dojo.xhrPost({
                url: this.pasteUrl,
                content: {
                    type: self.pasteOp,
                    target_id: targetItem.id,
                    id: self.selectedItem.id,
                    prev_item_id: prevItemId,
                    _csrf_token: self.selectedItem._csrf_token
                },
                handleAs: 'json',
                load: function(resp) {
                    var rowsToMove = [];
                    if(resp.success){
                        switch (self.pasteOp) {
                            case 'cut':
                                store.fetchItemByIdentity({ 'identity' : resp.data.id,  onItem : function(item){
                                    var firstRowIdx = self.getItemIndex(item);
                                    rowsToMove.push(firstRowIdx);
                                    if(rowsToMove.length > 0){
                                        self.rearranger.moveRows(rowsToMove, rowIndex);
                                        self.scrollToRow(rowIndex);
                                    }
                                }});
                                break;
                            case 'copy':
                                var item = store.newItem(resp.data);
                                store.save();
                                var firstRowIdx = self.getItemIndex(item);
                                rowsToMove.push(firstRowIdx);
                                if(rowsToMove.length > 0){
                                    self.rearranger.moveRows(rowsToMove, rowIndex);
                                }
                                self.updateTotalBuildUp();
                                break;
                            default:
                                break;
                        }
                    }
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                    self.selectedItem = null;
                    self.pasteOp = null;
                    rowsToMove.length = 0;
                    pb.hide();
                },
                error: function(error) {
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                    self.selectedItem = null;
                    self.pasteOp = null;
                    pb.hide();
                }
            });
        },
        contextMenu: function(e){
            if ( this.disableEditingMode ) {
                return false;
            }

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

            if(item && item.id > 0){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.cut,
                    iconClass:"icon-16-container icon-16-cut",
                    onClick: dojo.hitch(this,'cutItems')
                }));
                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.copy,
                    iconClass:"icon-16-container icon-16-copy",
                    onClick: dojo.hitch(this,'copyItems')
                }));
            }
            this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                label: nls.paste,
                iconClass:"icon-16-container icon-16-paste",
                onClick: dojo.hitch(this,'pasteItem',e.rowIndex),
                disabled: this.selectedItem ? false: true
            }));
            this.rowCtxMenu.addChild(new dijit.MenuItem({
                label: nls.addRow,
                iconClass:"icon-16-container icon-16-add",
                onClick: dojo.hitch(this,'addRow', e.rowIndex)
            }));
            if(item && item.id > 0){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(this,'deleteRow', e.rowIndex)
                }));
            }
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            var addRowBtn = dijit.byId('builUpGrid-'+this.billColumnSettingId+'_'+this.BillItem.id+'AddRow-button');
            var deleteRowBtn = dijit.byId('builUpGrid-'+this.billColumnSettingId+'_'+this.BillItem.id+'DeleteRow-button');

            addRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId('builUpGrid-'+_this.billColumnSettingId+'_'+_this.BillItem.id+label+'Row-button');
                    btn._setDisabledAttr(false);
                });
            }
        },
        updateTotalBuildUp: function(){
            this.buildUpSummaryWidget.refreshTotalQuantity();
        }
    });

    return declare('buildspace.apps.PostContractSubPackageRemeasurement.BuildUpQuantityGrid', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        type: null,
        BillItem: null,
        locked: false,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            lang.mixin(this.gridOpts, {
                billColumnSettingId: this.billColumnSettingId,
                BillItem: this.BillItem,
                region:"center",
                type: this.type,
                locked: this.locked
            });

            var grid          = this.grid = new BuildUpQuantityGrid(this.gridOpts),
                disableButton = this.locked;

            if(!disableButton){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'builUpGrid-'+self.billColumnSettingId+'_'+self.BillItem.id+'AddRow-button',
                        label: nls.addRow,
                        iconClass: "icon-16-container icon-16-add",
                        disabled: disableButton ? disableButton : grid.selection.selectedIndex > -1 ? false : true,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                grid.addRow(grid.selection.selectedIndex);
                            }
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'builUpGrid-'+self.billColumnSettingId+'_'+self.BillItem.id+'DeleteRow-button',
                        label: nls.deleteRow,
                        iconClass: "icon-16-container icon-16-delete",
                        disabled: disableButton ? disableButton : grid.selection.selectedIndex > -1 ? false : true,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                grid.deleteRow(grid.selection.selectedIndex);
                            }
                        }
                    })
                );

                self.addChild(toolbar);
            }

            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });
});