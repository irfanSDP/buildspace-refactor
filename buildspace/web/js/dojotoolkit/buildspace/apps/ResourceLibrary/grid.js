define('buildspace/apps/ResourceLibrary/grid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    'dojo/_base/connect',
    "dijit/TooltipDialog",
    "dijit/popup",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    './fileImportDialog',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    './importResourceItemDialog',
    './importSORItemDialog',
    './importBQItemDialog',
    'dojo/i18n!buildspace/nls/ResourceLibrary',
    'buildspace/widget/grid/Filter'
], function(declare, lang, array, domAttr, connect, TooltipDialog, popup, Menu, Selector, Rearrange, FormulatedColumn, evt, keys, focusUtil, html, xhr, PopupMenuItem, Textarea, FormulaTextBox, FileImportDialog, DropDownButton, DropDownMenu, MenuItem, ImportResourceItemDialog, ImportSORItemDialog, ImportBQItemDialog, nls, FilterToolbar){

    var ResourceLibraryGrid = declare('buildspace.apps.ResourceLibrary.Grid', dojox.grid.EnhancedGrid, {
        type: null,
        libraryId: 0,
        tradeId: 0,
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        keepSelection: true,
        rowSelector: '0px',
        addUrl: null,
        updateUrl: null,
        checkBeforeDeleteUrl: null,
        deleteUrl: null,
        pasteUrl: null,
        indentUrl: null,
        outdentUrl: null,
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});
        },
        canSort: function(inSortInfo){
            return false;
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;
            if(this.type=='tree'){
                if(inCell !== undefined){
                    var item = this.getItem(inRowIndex),
                        field = inCell.field;

                    if ( field === 'type' )
                    {
                        var nextItem = this.getItem(inRowIndex+1);

                        if(item.id[0] > 0 && item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && nextItem !== undefined && item.level[0] < nextItem.level[0]) {
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }
                    }

                    if(item.id[0] > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM && (
                        field == 'rate-value' || field == 'constant-value' || field == 'wastage-value' || field == 'uom_id'
                        )){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }
                }
            }
            return this._canEdit;
        },
        postCreate: function(){
            var self = this;
            var tooltipDialog = null;

            this.inherited(arguments);

            this.on("RowContextMenu", function(e){
                self.selection.clear();
                var item = self.getItem(e.rowIndex);
                self.selection.setSelected(e.rowIndex, true);
                self.contextMenu(e);
                if(item.id > 0){
                    self.disableToolbarButtons(false);
                }else{
                    self.disableToolbarButtons(true, ['Add', 'ImportFromLibrary']);
                }
            }, true);

            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex);
                if(item && item.id > 0){
                    self.disableToolbarButtons(false);
                }else{
                    self.disableToolbarButtons(true, ['Add', 'ImportFromLibrary']);
                }
            });

            if ( self.type === 'tree' ) {
                this._connects.push(connect.connect(this, 'onCellMouseOver', function(e) {
                    var colField = e.cell.field,
                        rowIndex = e.rowIndex,
                        item = this.getItem(rowIndex);

                    var fieldConstantName = colField.replace("-value", "");

                    // will show tooltip for formula, if available
                    if (typeof item[fieldConstantName+'-has_formula'] === 'undefined' || ! item[fieldConstantName+'-has_formula'][0] ) {
                        return;
                    }

                    var formulaValue = item[fieldConstantName+'-value'][0];

                    // convert ITEM ID into ROW ID (if available)
                    formulaValue = this.formulatedColumn.convertItemIdToRowIndex(formulaValue, rowIndex);

                    if(tooltipDialog === null) {
                        tooltipDialog = new TooltipDialog({
                            content: formulaValue,
                            onMouseLeave: function() {
                                popup.close(tooltipDialog);
                            }
                        });

                        popup.open({
                            popup: tooltipDialog,
                            around: e.cellNode
                        });
                    }
                }));

                this._connects.push(connect.connect(this, 'onCellMouseOut', function() {
                    if(tooltipDialog !== null){
                        popup.close(tooltipDialog);
                        tooltipDialog = null;
                    }
                }));

                this._connects.push(connect.connect(this, 'onStartEdit', function() {
                    if(tooltipDialog !== null){
                        popup.close(tooltipDialog);
                        tooltipDialog = null;
                    }
                }));
            }
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this,
                item = self.getItem(rowIdx),
                store = self.store,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
            var attrNameParsed = inAttrName.replace("-value","");//for any formulated column
            if(inAttrName.indexOf("-value") !== -1){
                val = this.formulatedColumn.convertRowIndexToItemId(val, rowIdx);
            }

            if(val !== item[inAttrName][0]){
                var params = {
                    id: item.id,
                    attr_name: attrNameParsed,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                if(item.id==buildspace.constants.GRID_LAST_ROW){
                    var prevItem = rowIdx > 0 ? self.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0,
                        relation_id: item.relation_id
                    });
                    url = this.addUrl;
                }

                var updateCell = function(data, gridStore){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != gridStore._getIdentifierAttribute()){
                            gridStore.setValue(item, property, data[property]);
                        }
                        dojo.forEach(data.affected_nodes, function(node){
                            gridStore.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem){
                                for(var property in node){
                                    if(item.hasOwnProperty(property) && property != gridStore._getIdentifierAttribute()){
                                        gridStore.setValue(affectedItem, property, node[property]);
                                    }
                                }
                            }});
                        });
                    }
                    gridStore.save();
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
                };

                if(item[attrNameParsed+'-has_build_up'] != undefined && item[attrNameParsed+'-has_build_up'][0]){
                    var onYes = function(){
                        pb.show();
                        dojo.xhrPost(xhrArgs);
                    };

                    var content = '<div>'+nls.detachAllSelectLink+'</div>';
                    buildspace.dialog.confirm(nls.confirmation,content,60,280, onYes);
                    self.doCancelEdit(rowIdx);
                }else{
                    pb.show();
                    dojo.xhrPost(xhrArgs);
                    self.inherited(arguments);
                }
            } else {
                self.inherited(arguments);
            }
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
            var self = this, item = this.getItem(e.rowIndex);
            if(item.id > 0){
                self.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.cut,
                    iconClass:"icon-16-container icon-16-cut",
                    onClick: function(){
                        self.pasteType = 'structure';
                        self.cutItems();
                    }
                }));
                self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.copy,
                    iconClass:"icon-16-container icon-16-copy",
                    onClick: dojo.hitch(self,'copyItems')
                }));
            }
            self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                label: nls.paste,
                iconClass:"icon-16-container icon-16-paste",
                onClick: dojo.hitch(self,'pasteItem',e.rowIndex),
                disabled: self.selectedItem ? false: true
            }));
            if(item.id > 0 && self.type == 'tree'){
                self.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.indent,
                    iconClass:"icon-16-container icon-16-indent",
                    onClick: dojo.hitch(self,'indentOutdent', e.rowIndex,'indent')
                }));
                self.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.outdent,
                    iconClass:"icon-16-container icon-16-outdent",
                    onClick: dojo.hitch(self,'indentOutdent', e.rowIndex,'outdent')
                }));
            }
            self.rowCtxMenu.addChild(new dijit.MenuItem({
                label: nls.addRow,
                iconClass:"icon-16-container icon-16-add",
                onClick: dojo.hitch(self,'addRow', e.rowIndex, 'lala')
            }));
            if(item.id > 0){
                self.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(self,'deleteRow', e.rowIndex)
                }));
            }
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

            var xhrArgs = {
                url: this.pasteUrl,
                content: {
                    type: self.pasteOp,
                    target_id: targetItem.id,
                    prev_item_id: prevItemId,
                    id: self.selectedItem.id,
                    _csrf_token: self.selectedItem._csrf_token
                },
                handleAs: 'json',
                load: function(resp) {
                    var rowsToMove = [];
                    if(resp.success){
                        var children = resp.c;
                        switch (self.pasteOp) {
                            case 'cut':
                                store.fetchItemByIdentity({ 'identity' : resp.data.id,  onItem : function(item){
                                    var firstRowIdx = self.getItemIndex(item);
                                    rowsToMove.push(firstRowIdx);
                                    for(var x=0, len=children.length; x<len; ++x){
                                        store.fetchItemByIdentity({ 'identity' : children[x].id,  onItem : function(child){
                                            var itemIdx = self.getItemIndex(child);
                                            rowsToMove.push(itemIdx);
                                        }});
                                    }
                                    if(rowsToMove.length > 0){
                                        self.rearranger.moveRows(rowsToMove, rowIndex);
                                    }
                                    var selectRowIndex  = (firstRowIdx > rowIndex) ? rowIndex : rowIndex - 1;
                                    self.selectAfterPaste(selectRowIndex, true);
                                }});
                                store.fetchItemByIdentity({ 'identity' : resp.data.id,  onItem : function(item){
                                    for(var property in resp.data){
                                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                            store.setValue(item, property, resp.data[property]);
                                        }
                                    }
                                    store.save();
                                    for(var x=0, len=children.length; x<len; ++x){
                                        store.fetchItemByIdentity({ 'identity' : children[x].id,  onItem : function(child){
                                            for(var property in children[x]){
                                                if(child.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                                    store.setValue(child, property, children[x][property]);
                                                }
                                            }
                                            store.save();
                                        }});
                                    }
                                }});
                                break;
                            case 'copy':
                                var item = store.newItem(resp.data);
                                store.save();
                                var firstRowIdx = self.getItemIndex(item);
                                rowsToMove.push(firstRowIdx);
                                for(var x=0, len=children.length; x<len; ++x){
                                    var child = store.newItem(children[x]);
                                    store.save();
                                    var itemIdx = self.getItemIndex(child);
                                    rowsToMove.push(itemIdx);
                                }
                                if(rowsToMove.length > 0){
                                    self.rearranger.moveRows(rowsToMove, rowIndex);
                                    self.selectAfterPaste(rowIndex, false);
                                }
                                break;
                            default:
                                break;
                        }
                    }
                    self.disableToolbarButtons(false);
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
            };
            dojo.xhrPost(xhrArgs);
        },
        selectAfterPaste: function(rowIndex, scroll)
        {
            this.selection.clear();
            this.selectedItem = null;
            this.selection.setSelected(rowIndex, true);

            if(scroll){
                this.scrollToRow(((rowIndex - 3) > 0) ? rowIndex - 3 : rowIndex);
            }
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
                content = { before_id: itemBefore.id, _csrf_token:itemBefore._csrf_token };
            }else{
                var prevItemId = (rowIndex > 0) ? self.getItem(rowIndex-1).id : 0;
                content = { id: itemBefore.id, prev_item_id: prevItemId, relation_id: itemBefore.relation_id, _csrf_token:itemBefore._csrf_token }
            }
            pb.show();
            var xhrArgs = {
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
            };
            dojo.xhrPost(xhrArgs);
        },
        deleteRow: function(rowIndex){
            var self = this, item = self.getItem(rowIndex),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            var xhrArgs = {
                url: this.deleteUrl,
                content: { id: item.id, _csrf_token: item._csrf_token },
                handleAs: 'json',
                load: function(data) {
                    if(data.success){
                        var items = data.items;
                        var store = self.store;

                        if(data.affected_nodes != undefined){
                            var affectedNodesList = data.affected_nodes;
                            for(var i=0, len=affectedNodesList.length; i<len; ++i){
                                dojo.forEach(affectedNodesList[i], function(node){
                                    store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem){
                                        for(var property in node){
                                            if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                                store.setValue(affectedItem, property, node[property]);
                                            }
                                        }
                                    }});
                                });
                            }
                        }

                        for(var i=0, len=items.length; i<len; ++i){
                            store.fetchItemByIdentity({ 'identity' : items[i].id,  onItem : function(itm){
                                store.deleteItem(itm);
                                store.save();
                            }});
                        }
                        items.length = 0;
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

            var xhrArgs2 = {
                url: this.checkBeforeDeleteUrl,
                content: { id: item.id },
                handleAs: 'json',
                load: function(data) {
                    if (data.success) {
                        if (data.sorLinkedItems || data.linkedItems) {
                            new buildspace.dialog.alert(self.deleteLinkNotificationTitle, self.deleteLinkNotificationMsg, 60, 300);
                            pb.hide();
                        } else if (data.hasRowLinking) {
                            new buildspace.dialog.alert(nls.deleteRowLinkingNotificationTitle, nls.deleteRowLinkingNotificationMsg, 60, 300);
                            pb.hide();
                        } else {
                            new buildspace.dialog.confirm(self.deleteLinkNormalTitle, self.deleteLinkNormalMsg, 80, 320, function() {
                                dojo.xhrPost(xhrArgs);
                            }, function() {
                                pb.hide();
                            });
                        }
                    }
                    else {
                        /* got fucked */
                    }
                },
                error: function(error) {
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                    self.selectedItem = null;
                    self.pasteOp = null;
                    pb.hide();
                }
            };

            pb.show();
            dojo.xhrGet(xhrArgs2);
        },
        indentOutdent: function(rowIndex, type){
            var self = this,
                store = self.store;
            if(rowIndex > 0){
                var item = self.getItem(rowIndex);
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.recalculateRows+'. '+nls.pleaseWait+'...'
                });
                pb.show();
                if(item.id > 0){
                    var xhrArgs = {
                        url: this[type+'Url'],
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
                    };
                    dojo.xhrPost(xhrArgs);
                }
            }
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            var addRowBtn = dijit.byId(this.libraryId+'_'+this.tradeId+'AddRow-button');
            var deleteRowBtn = dijit.byId(this.libraryId+'_'+this.tradeId+'DeleteRow-button');
            var indentBtn = dijit.byId(this.libraryId+'_'+this.tradeId+'Indent-button');
            var outdentBtn = dijit.byId(this.libraryId+'_'+this.tradeId+'Outdent-button');
            var importFromResourceBtn = dijit.byId(this.libraryId+'_'+this.tradeId+'ImportFromLibraryRow-button');

            addRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);

            if(indentBtn)
                indentBtn._setDisabledAttr(isDisable);
            if(outdentBtn)
                outdentBtn._setDisabledAttr(isDisable);
            if(importFromResourceBtn)
                importFromResourceBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(_this.libraryId+'_'+_this.tradeId+label+'Row-button');

                    if (btn) {
                        btn._setDisabledAttr(false);
                    }
                });
            }
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.ResourceLibrary.grid', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;border:0px;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: null,
        libraryId: 0,
        tradeId: 0,
        gridOpts: {},
        type: null,
        postCreate: function(){
            var self = this,
                filterFields;
            this.inherited(arguments);
            lang.mixin(self.gridOpts, { libraryId: self.libraryId, tradeId: self.tradeId, type:self.type, region:"center", borderContainerWidget: self });
            var grid = this.grid = new ResourceLibraryGrid(self.gridOpts);
            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.libraryId+'_'+self.tradeId+'AddRow-button',
                    label: nls.addRow,
                    iconClass: "icon-16-container icon-16-add",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(){
                        if(grid.selection.selectedIndex > -1){
                            grid.addRow(grid.selection.selectedIndex);
                        }
                    }
                })
            );
            if(self.type == 'tree'){
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: self.libraryId+'_'+self.tradeId+'Indent-button',
                        label: nls.indent,
                        iconClass: "icon-16-container icon-16-indent",
                        disabled: grid.selection.selectedIndex > -1 ? false : true,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                grid.indentOutdent(grid.selection.selectedIndex, 'indent');
                            }
                        }
                    })
                );
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: self.libraryId+'_'+self.tradeId+'Outdent-button',
                        label: nls.outdent,
                        iconClass: "icon-16-container icon-16-outdent",
                        disabled: grid.selection.selectedIndex > -1 ? false : true,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                grid.indentOutdent(grid.selection.selectedIndex, 'outdent');
                            }
                        }
                    })
                );

                filterFields = [
                    {'description': nls.description},
                    {'constant-final_value': nls.constant},
                    {'uom_symbol': nls.unit},
                    {'rate-final_value': nls.rate},
                    {'wastage-final_value': nls.wastage+" (%)"}
                ];
            }
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.libraryId+'_'+self.tradeId+'DeleteRow-button',
                    label: nls.deleteRow,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(){
                        if(grid.selection.selectedIndex > -1){
                            grid.deleteRow(grid.selection.selectedIndex);
                        }
                    }
                })
            );

            if(self.type != 'tree')
            {
                var importDropDownMenu = new DropDownMenu({ style: "display: none;"});

                importDropDownMenu.addChild(new MenuItem({
                    label: nls.importFromBuildsoft,
                    onClick: function(e){
                        var fileImportDialog = new FileImportDialog({
                            resourceId: self.libraryId,
                            importType: buildspace.constants.FILE_IMPORT_TYPE_BUILDSOFT,
                            uploadUrl: "resourceLibrary/importBuildsoftExcel",
                            title: nls.importFromBuildsoft,
                            resourceGrid: grid
                        });
                        fileImportDialog.show();
                    }
                }));

                importDropDownMenu.addChild(new MenuItem({
                    label: nls.importFromExcel,
                    onClick: function(e){
                        var fileImportDialog = new FileImportDialog({
                            resourceId: self.libraryId,
                            importType: buildspace.constants.FILE_IMPORT_TYPE_EXCEL,
                            uploadUrl: "resourceLibrary/previewImportedFile",
                            title: nls.importFromExcel,
                            resourceGrid: grid
                        });

                        fileImportDialog.show();
                    }
                }));

                importDropDownMenu.addChild(new MenuItem({
                    label: nls.importFromPricelist,
                    onClick: function(e){
                        var fileImportDialog = new FileImportDialog({
                            resourceId: self.libraryId,
                            importType: buildspace.constants.FILE_IMPORT_TYPE_PRICELIST,
                            uploadUrl: "resourceLibrary/importPricelist",
                            title: nls.importFromPricelist,
                            resourceGrid: grid
                        });

                        fileImportDialog.show();
                    }
                }));

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(new DropDownButton({
                    label: nls.importFromFiles,
                    iconClass: "icon-16-container icon-16-import",
                    dropDown: importDropDownMenu
                }));

                filterFields = [
                    {'description':nls.description}
                ];
            } else {
                var importFromLibraryMenus = new DropDownMenu({ style: "display: none;"});

                importFromLibraryMenus.addChild(new MenuItem({
                    label: nls.importFromResource,
                    onClick: function(e){
                        if(grid.selection.selectedIndex > -1){
                            new ImportResourceItemDialog({
                                title: nls.importFromResource,
                                libraryId: self.libraryId,
                                tradeId: self.tradeId,
                                selectedItem: grid.selection.getFirstSelected(),
                                resourceGrid: grid
                            }).show();
                        }
                    }
                }));

                importFromLibraryMenus.addChild(new MenuItem({
                    label: nls.importFromScheduleOfRate,
                    onClick: function(e){
                        if(grid.selection.selectedIndex > -1){
                            new ImportSORItemDialog({
                                title: nls.importFromScheduleOfRate,
                                libraryId: self.libraryId,
                                tradeId: self.tradeId,
                                selectedItem: grid.selection.getFirstSelected(),
                                resourceGrid: grid
                            }).show();
                        }
                    }
                }));

                importFromLibraryMenus.addChild(new MenuItem({
                    label: nls.importFromBQLibrary,
                    onClick: function(e){
                        if(grid.selection.selectedIndex > -1){
                            new ImportBQItemDialog({
                                title: nls.importFromBQLibrary,
                                libraryId: self.libraryId,
                                tradeId: self.tradeId,
                                selectedItem: grid.selection.getFirstSelected(),
                                resourceGrid: grid
                            }).show();
                        }
                    }
                }));

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(new DropDownButton({
                    id: self.libraryId+'_'+self.tradeId+'ImportFromLibraryRow-button',
                    label: nls.importLibrary,
                    iconClass: "icon-16-container icon-16-import",
                    dropDown: importFromLibraryMenus,
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                }));
            }

            self.addChild(
                new FilterToolbar({
                    grid:self.grid,
                    region:"top",
                    editableGrid: true,
                    filterFields: filterFields
                })
            );
            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('resourceLibraryGrid'+this.libraryId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                lang.mixin(child, {grid: grid});
                container.selectChild(self.pageId);
            }
        }
    });
});