define('buildspace/apps/ScheduleOfQuantity/grid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojox/grid/enhanced/plugins/Menu",
    "dijit/MenuSeparator",
    'dojo/number',
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
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/ScheduleOfQuantity'
], function(declare, lang, array, domAttr, Menu, MenuSeparator, number, Selector, Rearrange, FormulatedColumn, evt, keys, focusUtil, html, xhr, PopupMenuItem, Textarea, FormulaTextBox, DropDownButton, DropDownMenu, MenuItem, IndirectSelection, nls ){

    return declare('buildspace.apps.ScheduleOfQuantity.Grid', dojox.grid.EnhancedGrid, {
        type: null,
        project: null,
        editable: true,
        region: "center",
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        billColumnSetting: null,
        keepSelection: true,
        addUrl: null,
        updateUrl: null,
        deleteUrl: null,
        pasteUrl: null,
        indentUrl: null,
        outdentUrl: null,
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});

            if(args.type == 'linkTo_ItemGrid'){
                this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align:center;"}}
            }

            if(args.type == 'linkTo_BillItemGrid'){
                this.escapeHTMLInData = false
            }
        },
        canSort: function(inSortInfo){
            return false;
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;
            if(!this.editable || (this.type == 'linkTo_Grid' && this.type == 'linkTo_ItemGrid' && this.type == 'linkTo_BillItemGrid')){
                return false;
            }

            if(this.type=='item_grid'){
                if(inCell != undefined){
                    var item = this.getItem(inRowIndex),
                        field = inCell.field;

                    if ( field === 'type' ){
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
                        field == 'quantity-value' || field == 'uom_id'
                        )){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return false;
                    }
                }
            }

            return this._canEdit;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            if(this.editable && this.type != 'linkTo_Grid' && this.type != 'linkTo_ItemGrid' && this.type != 'linkTo_BillItemGrid'){
                this.on("RowContextMenu", function(e){
                    self.selection.clear();
                    var item = self.getItem(e.rowIndex);
                    self.selection.setSelected(e.rowIndex, true);
                    self.contextMenu(e);
                    if(item.id > 0){
                        self.disableToolbarButtons(false);
                    }else{
                        self.disableToolbarButtons(true, ['Add']);
                    }
                }, true);

                this.on('RowClick', function(e){
                    var item = self.getItem(e.rowIndex);
                    if(item && item.id > 0){
                        self.disableToolbarButtons(false);
                    }else{
                        self.disableToolbarButtons(true, ['Add']);
                    }
                });
            }

            if(this.type == 'linkTo_BillItemGrid'){
                this.on("RowClick", function(e){
                    var colField = e.cell.field,
                        rowIndex = e.rowIndex,
                        _item = this.getItem(rowIndex);
                    if((colField == 'quantity_import' || colField == 'quantity_remeasurement_import') && _item.id > 0 && _item.description[0] !== null && _item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER){
                        self.linkScheduleOfQuantities(_item, colField);
                    }
                }, true);
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

                var cell = self.getCellByField(inAttrName);

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
                    buildspace.dialog.confirm(nls.confirmation, '<div>'+nls.detachAllBuildUpAndLink+'</div>', 85, 280, function() {
                        dojo.xhrPost(xhrArgs);
                    }, function() {
                        pb.hide();

                        window.setTimeout(function() {
                            self.focus.setFocusIndex(rowIdx, cell.index);
                        }, 10);
                    });

                    self.doCancelEdit(rowIdx);
                }else{
                    pb.show();
                    dojo.xhrPost(xhrArgs);
                    self.inherited(arguments);
                }
            }else{
                self.inherited(arguments);
            }
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        editableCellDblClick: function(e){
            var event;
            if(this._click.length > 1 && has('ie')){
                event = this._click[1];
            }else if(this._click.length > 1 && this._click[0].rowIndex != this._click[1].rowIndex){
                event = this._click[0];
            }else{
                event = e;
            }
            this.focus.setFocusCell(event.cell, event.rowIndex);
            this.onRowClick(event);
            this.onRowDblClick(e);
        },
        dodblclick: function(e){
            if(e.cellNode){
                if(e.cell.editable){
                    this.editableCellDblClick(e);
                }else{
                    this.onCellDblClick(e);
                    if(this.customCellDblClick){
                        this.customCellDblClick(e);
                    }
                }
            }else{
                this.onRowDblClick(e);
            }
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
            var self = this, finalValue, item = this.getItem(e.rowIndex), cell = e.cell;
            if(item.id > 0){
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

            var disabledPaste = self.selectedItem ? false : true, pasteFunc = 'pasteItem';

            this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                label: nls.paste,
                iconClass:"icon-16-container icon-16-paste",
                onClick: dojo.hitch(this, 'pasteItem', e.rowIndex),
                disabled: disabledPaste
            }));

            if(item.id > 0 && this.type == 'item_grid'){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.indent,
                    iconClass:"icon-16-container icon-16-indent",
                    onClick: dojo.hitch(this, 'indentOutdent', e.rowIndex,'indent')
                }));

                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.outdent,
                    iconClass:"icon-16-container icon-16-outdent",
                    onClick: dojo.hitch(this, 'indentOutdent', e.rowIndex,'outdent')
                }));
            }

            this.rowCtxMenu.addChild(new dijit.MenuItem({
                label: nls.addRow,
                iconClass:"icon-16-container icon-16-add",
                onClick: dojo.hitch(this,'addRow', e.rowIndex)
            }));

            if(item.id > 0){
                this.rowCtxMenu.addChild(new MenuSeparator());

                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(this, 'deleteRow', e.rowIndex)
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
                store = this.store,
                targetItem = this.selection.getFirstSelected();
            var prevItemId = (targetItem.id == buildspace.constants.GRID_LAST_ROW && rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
            pb.show();

            dojo.xhrPost({
                url: this.pasteUrl,
                content: {
                    type: this.pasteOp,
                    target_id: targetItem.id,
                    prev_item_id: prevItemId,
                    id: this.selectedItem.id,
                    _csrf_token: this.selectedItem._csrf_token
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
            });
        },
        selectAfterPaste: function (rowIndex, scroll){
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
            var content,
                self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.addingRow+'. '+nls.pleaseWait+'...'
                }),
                store = this.store,
                itemBefore = this.getItem(rowIndex);
            if(itemBefore.id > 0){
                content = { before_id: itemBefore.id, relation_id: itemBefore.relation_id, _csrf_token: itemBefore._csrf_token };
            }else{
                var prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                content = { id: itemBefore.id, prev_item_id: prevItemId, relation_id: itemBefore.relation_id, _csrf_token:itemBefore._csrf_token }
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
            var self = this, msg = null,
                item = this.getItem(rowIndex),
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

                        for(var x=0, lenX=items.length; x<lenX; ++x){
                            store.fetchItemByIdentity({ 'identity' : items[x].id,  onItem : function(itm){
                                store.deleteItem(itm);
                                store.save();
                            }});
                        }
                        items.length = 0;

                        if(self.type == "main_grid" && self.tabContainer != undefined){
                            var tac = self.tabContainer.getChildren();
                            for(var t in tac){
                                if(typeof tac[t].lib_info != "object") continue;
                                if(tac[t].lib_info.id == self.project.id+"_"+item.id){
                                    self.tabContainer.removeChild(tac[t]);
                                    break;
                                }
                            }
                        }
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

            var boxW, boxH;
            // determine which msg to show in dialogbox when deleting
            switch(this.type){
                case "item_grid":
                    if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER) {
                        msg   = nls.confirmDeleteItemHead;
                    } else {
                        msg   = nls.confirmDeleteItem;
                    }
                    boxW = 320;
                    boxH = 95;
                    break;
                case "trade_grid":
                    msg = nls.confirmDeleteTrade;
                    boxW = 250;
                    boxH = 90;
                    break;
                default:
                    msg = nls.confirmDeleteSOQ;
                    boxW = 320;
                    boxH = 95;
            }

            new buildspace.dialog.confirm(nls.confirmation, msg, boxH, boxW, function() {
                dojo.xhrPost(xhrArgs);
            }, function() {
                pb.hide();
            });
        },
        indentOutdent: function(rowIndex, type){
            var self = this,
                store = this.store;
            if(rowIndex > 0){
                var item = this.getItem(rowIndex);
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.recalculateRows+'. '+nls.pleaseWait+'...'
                });
                pb.show();
                if(item.id > 0){
                    dojo.xhrPost({
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
                    });
                }
            }
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            if(this.type != 'linkTo_Grid' && this.type != 'linkTo_ItemGrid' && this.type != 'linkTo_BillItemGrid'){
                var buttonId;

                if(this.type == "trade_grid" || this.type == "item_grid"){
                    buttonId = this.project.id+"_"+this.scheduleOfQuantity.id+"_"+this.type;
                }else{
                    buttonId = this.project.id+"_"+this.type;
                }

                var addRowBtn = dijit.byId(buttonId+'_AddSOQRow-button');
                var deleteRowBtn = dijit.byId(buttonId+'_DeleteSOQRow-button');
                var indentBtn = dijit.byId(buttonId+'_IndentSOQ-button');
                var outdentBtn = dijit.byId(buttonId+'_OutdentSOQ-button');
                var importBtn = dijit.byId(buttonId+'_ImportDropDownRow-button');

                addRowBtn._setDisabledAttr(isDisable);
                deleteRowBtn._setDisabledAttr(isDisable);

                if ( importBtn ) {
                    importBtn._setDisabledAttr(isDisable);
                }

                if(indentBtn)
                    indentBtn._setDisabledAttr(isDisable);
                if(outdentBtn)
                    outdentBtn._setDisabledAttr(isDisable);

                if(isDisable && buttonsToEnable instanceof Array ){
                    dojo.forEach(buttonsToEnable, function(label){
                        var btn = dijit.byId(buttonId+'_'+label+'SOQRow-button');
                        if(btn)
                            btn._setDisabledAttr(false);
                    });
                }
            }
        },
        linkScheduleOfQuantities: function(BillItem, qtyType){
            var soqItemGrid = dijit.byId('linkToItemGrid_'+this.project.id);
            if(typeof soqItemGrid == 'undefined'){
                buildspace.dialog.alert(nls.noSoqItemAlert, nls.pleaseOpenSoqItem+'.', 90, 300);
            }else{
                var ids = [];
                dojo.forEach(soqItemGrid.selection.getSelected(), function(item){
                    if(item && item.id > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER){
                        ids.push(item.id[0]);
                    }
                });
                if(ids.length > 0 && this.billColumnSetting != null){
                    var grid = this, type = qtyType == 'quantity_remeasurement_import' ? 2 : 1,
                        pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.linkingData+'. '+nls.pleaseWait+'...'
                    });
                    pb.show();
                    dojo.xhrPost({
                        url: 'scheduleOfQuantity/linkToBillItem',
                        content: {bid: BillItem.id, bcid: this.billColumnSetting.id, type: type, _csrf_token: BillItem._csrf_token, 'ids': ids.toString()},
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                grid.store.fetchItemByIdentity({ 'identity' : resp.item.id,  onItem : function(item){
                                    for(var property in resp.item){
                                        if(item.hasOwnProperty(property) && property != grid.store._getIdentifierAttribute()){
                                            grid.store.setValue(item, property, resp.item[property]);
                                        }
                                    }
                                }});
                                grid.store.save();
                            }
                            pb.hide();
                            soqItemGrid.selection.clear();
                        },
                        error: function(error) {
                            pb.hide();
                            soqItemGrid.selection.clear();
                        }
                    });

                }else{
                    buildspace.dialog.alert(nls.noItemSelectedAlert, nls.pleaseSelectItem+'.', 90, 300);
                }
            }
        }
    });
});