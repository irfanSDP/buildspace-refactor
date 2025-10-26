define(['dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/Filter',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PredefinedLocationCodeMaintenance',
    "dojo/html"],
function(declare, lang, array, domAttr, Menu, Selector, Rearrange, evt, keys, focusUtil, Textarea, FilterToolbar, GridFormatter, nls, html){

    var PredefinedLocationCodeGrid = declare('buildspace.apps.PredefinedLocationCodeMaintenance.PredefinedLocationCodeGrid', dojox.grid.EnhancedGrid, {
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
            var self = this, item = this.getItem(rowIdx), store = this.store;

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    attr_name: inAttrName,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = "systemMaintenance/predefinedLocationCodeUpdate";

                if(item && isNaN(parseInt(item.id[0]))){
                    var prevItem = rowIdx > 0 ? this.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0
                    });
                    url = "systemMaintenance/predefinedLocationCodeAdd";
                }

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }

                    store.save();
                };

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: url,
                        content: params,
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                if(!isNaN(parseInt(item.id[0]))){
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
                    });
                });
            }

            this.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;

            if(inCell != undefined){
                var item = this.getItem(inRowIndex),
                    field = inCell.field;

                if(item && !isNaN(parseInt(item.id[0]))){
                    if(field != 'name' && inCell.editable){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return false;
                    }

                } else if ( field !== 'name' ) {
                    return false;
                }
            }

            return this._canEdit;
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
            if(item && !isNaN(parseInt(item.id[0]))){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.addRow,
                    iconClass:"icon-16-container icon-16-add",
                    onClick: dojo.hitch(this, "addRow")
                }));
                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.indent,
                    iconClass:"icon-16-container icon-16-indent",
                    onClick: dojo.hitch(this, "indentOutdent", 'indent')
                }));
                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.outdent,
                    iconClass:"icon-16-container icon-16-outdent",
                    onClick: dojo.hitch(this, "indentOutdent", 'outdent')
                }));
                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(this, "deleteRow")
                }));
            }
        },
        addRow: function(){
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            if(this.selection.selectedIndex > -1){
                var rowIndex = this.selection.selectedIndex;
                var self = this,
                    prevItemId,
                    content,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.savingData+'. '+nls.pleaseWait+'...'
                    }),
                    store = this.store,
                    itemBefore = this.getItem(rowIndex);

                if(!isNaN(parseInt(itemBefore.id[0]))){
                    prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                    content = {
                        prev_item_id: prevItemId,
                        before_id: itemBefore.id,
                        _csrf_token:itemBefore._csrf_token
                    };
                }else{
                    prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                    content = {
                        id: itemBefore.id,
                        prev_item_id: prevItemId,
                        _csrf_token:itemBefore._csrf_token
                    };
                }

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'systemMaintenance/predefinedLocationCodeAdd',
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
        indentOutdent: function (type) {
            var rowIndex = this.selection.selectedIndex,
                item = this.getItem(rowIndex);

            if(this.selection.selectedIndex > 0 && item && !isNaN(parseInt(item.id[0]))) {
                var self = this,
                    store = self.store,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.recalculateRows+'. '+nls.pleaseWait+'...'
                    });

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: type == 'indent' ? 'systemMaintenance/predefinedLocationCodeIndent' : 'systemMaintenance/predefinedLocationCodeOutdent',
                        content: { id: item.id, _csrf_token: item._csrf_token },
                        handleAs: 'json',
                        load: function(data) {
                            if(data.success){
                                var nextItems = data.c;
                                for(var property in data.item){
                                    if(data.item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(item, property, data.item[property]);
                                    }
                                }
                                for(var x=0, len=nextItems.length; x<len; ++x){
                                    store.fetchItemByIdentity({ 'identity' : nextItems[x].id,  onItem :  function (nextItem) {
                                        for(var property in nextItems[x]){
                                            if(nextItem.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                                store.setValue(nextItem, property, nextItems[x][property]);
                                            }
                                        }
                                    }});
                                }
                                store.save();
                            }
                            pb.hide();
                        },
                        error: function(error) {
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                            pb.hide();
                        }
                    })
                });
            }
        },
        deleteRow: function () {
            var rowIndex = this.selection.selectedIndex,
                item = this.getItem(rowIndex);

            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            if(this.selection.selectedIndex > -1 && item && !isNaN(parseInt(item.id[0]))){
                var self = this,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.deleting+'. '+nls.pleaseWait+'...'
                    });

                buildspace.dialog.confirm(nls.deletePredefinedLocationCodeConfirmation, '<div>'+nls.doYouReallywantToDelete+' ?</div>', 90, 320, function() {
                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: "systemMaintenance/predefinedLocationCodeDelete",
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
                                    buildspace.dialog.alert(nls.cannotDeleteTitle,nls.cannotDeleteMsg,90,320);
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
            var addRowBtn = dijit.byId('AddLocationCodeRow-button'),
                deleteRowBtn = dijit.byId('DeleteLocationCodeRow-button'),
                indentBtn = dijit.byId('IndentLocationCodeRow-button'),
                outdentBtn = dijit.byId('OutdentLocationCodeRow-button');

            addRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);

            if(indentBtn)
                indentBtn._setDisabledAttr(isDisable);
            if(outdentBtn)
                outdentBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(label+'LocationCodeRow-button');
                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        }
    });

    return declare('buildspace.apps.PredefinedLocationCodeMaintenance.PredefinedLocationCodeMaintenanceFormContainer', dijit.layout.BorderContainer, {
        style:"padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        postCreate: function(){
            this.inherited(arguments);
            var CustomFormatter = {
                treeCellFormatter: function (cellValue, rowIdx) {
                    var item = this.grid.getItem(rowIdx);
                    var level = item.level * 16;
                    cellValue = cellValue == null ? '&nbsp' : cellValue;
                    if (parseInt(item.level[0]) < buildspace.constants.PREDEFINED_LOCATION_CODE_SUB_ELEMENT_LEVEL) {
                        cellValue = '<b>' + cellValue + '</b>';
                    }
                    return '<div class="treeNode" style="padding-left:' + level + 'px;"><div class="treeContent">' + cellValue + '&nbsp;</div></div>';
                }
            };
            var formatter = new GridFormatter();
            var grid = this.predefinedLocationCodeGrid = new PredefinedLocationCodeGrid({
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: "systemMaintenance/getPredefinedLocationCodes"
                }),
                structure: [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.name, field: 'name', editable: true, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'auto', formatter: CustomFormatter.treeCellFormatter},
                    {name: nls.type, field: 'type_txt', styles:'text-align:center;', width:'140px'},
                    {name: nls.updatedAt, field: 'updated_at', styles:'text-align:center;', width:'120px'}
                ]
            });

            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'AddLocationCodeRow-button',
                    label: nls.addRow,
                    iconClass: "icon-16-container icon-16-add",
                    disabled: true,
                    onClick: dojo.hitch(grid, "addRow")
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'IndentLocationCodeRow-button',
                    label: nls.indent,
                    iconClass: "icon-16-container icon-16-indent",
                    style: "outline:none!important;",
                    disabled: true,
                    onClick: dojo.hitch(grid, "indentOutdent", 'indent')
                })
            );
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'OutdentLocationCodeRow-button',
                    label: nls.outdent,
                    iconClass: "icon-16-container icon-16-outdent",
                    style: "outline:none!important;",
                    disabled: true,
                    onClick: dojo.hitch(grid, "indentOutdent", 'outdent')
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'DeleteLocationCodeRow-button',
                    label: nls.deleteRow,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: true,
                    onClick: dojo.hitch(grid, "deleteRow")
                })
            );
            var filter  = new FilterToolbar({
                grid: grid,
                region:"top",
                filterFields: [ {name : nls.name, type_txt: nls.type}]
            });

            this.addChild(filter);
            this.addChild(toolbar);
            this.addChild(grid);
        }
    });
});