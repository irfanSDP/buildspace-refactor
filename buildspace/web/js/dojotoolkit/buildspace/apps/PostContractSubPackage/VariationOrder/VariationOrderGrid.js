define('buildspace/apps/PostContractSubPackage/VariationOrder/VariationOrderGrid',[
    'dojo/_base/declare',
    "dojo/_base/connect",
    'dojo/_base/lang',
    'dojo/_base/html',
    "dojo/dom-style",
    "dojo/number",
    "dijit/focus",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/TooltipDialog",
    "dijit/popup",
    'dojox/grid/EnhancedGrid',
    "./BillDialog",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, connect, lang, html, domStyle, number, focusUtil, evt, keys, TooltipDialog, popup, EnhancedGrid, BillDialog, Rearrange, FormulatedColumn, GridFormatter, nls) {

    var VariationOrderGrid = declare('buildspace.apps.PostContractSubPackage.VariationOrder.VariationOrderEnhancedGrid', EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        selectedItem: null,
        region: 'center',
        subPackage: null,
        locked: false,
        keepSelection: true,
        addUrl: null,
        updateUrl: null,
        deleteUrl: null,
        pasteUrl: null,
        indentUrl: null,
        outdentUrl: null,
        constructor:function(args){
            this.connects = [];
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            var myTooltipDialog;

            this._connects.push(connect.connect(this, 'onCellMouseOver', function(e){
                var item = self.getItem(e.rowIndex),
                    colField = e.cell.field,
                    rowIndex = e.rowIndex;

                var fieldConstantName = colField.replace("-value", "");

                // will show tooltip for formula, if available
                if (typeof item[fieldConstantName+'-has_formula'] === 'undefined' || ! item[fieldConstantName+'-has_formula'][0] ) {
                    return;
                }

                var formulaValue = item[fieldConstantName+'-value'][0];

                // convert ITEM ID into ROW ID (if available)
                formulaValue = this.formulatedColumn.convertItemIdToRowIndex(formulaValue, rowIndex);

                if(myTooltipDialog === null) {
                    myTooltipDialog = new TooltipDialog({
                        content: formulaValue,
                        onMouseLeave: function() {
                            popup.close(myTooltipDialog);
                        }
                    });

                    popup.open({
                        popup: myTooltipDialog,
                        around: e.cellNode
                    });
                }
            }));

            this._connects.push(connect.connect(this, 'onCellMouseOut', function(e){
                if(myTooltipDialog !== undefined){
                    popup.close(myTooltipDialog);
                }
            }));

            if(!this.locked){
                this.on("RowContextMenu", function(e){
                    self.selection.clear();
                    var item = self.getItem(e.rowIndex);
                    self.selection.setSelected(e.rowIndex, true);

                    if(self.type == "vo" && item.can_be_edited[0] || self.type == "vo-items" && self.variationOrder.is_approved[0] == "false"){
                        self.contextMenu(e);
                        if(item.id > 0){
                            self.disableToolbarButtons(false);
                        }else{
                            self.disableToolbarButtons(true, ["Add", "OmitFromBill"]);
                        }
                    }
                }, true);

                this.on('RowClick', function(e){
                    var item = self.getItem(e.rowIndex);
                    if(item && item.id > 0){
                        self.disableToolbarButtons(false);
                    }else{
                        self.disableToolbarButtons(true, ["Add", "OmitFromBill"]);
                    }
                });
            }
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;
            if(inCell != undefined){
                var item = this.getItem(inRowIndex),
                    field = inCell.field;
                if(this.type=="vo"){
                    if ( field === 'is_approved' && item.id == buildspace.constants.GRID_LAST_ROW ){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }

                    if (item.id > 0 && !item.can_be_edited[0] ){
                        window.setTimeout(function() {
                            self.disableToolbarButtons(true);
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }
                }else if(this.type == "vo-items"){
                    /*
                     for items omitted from bills, all columns cannot be editable except for addition qty column
                     */
                    if ( field != 'addition_quantity-value' && field != 'current_percentage-value' && field != 'current_amount-value' && field != 'up_to_date_percentage-value' && field != 'up_to_date_amount-value' && item.id > 0 && item.bill_item_id[0] > 0 ){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }

                    if(field != "description" && field != "type" && item.id > 0 && item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }

                    if((item.id == buildspace.constants.GRID_LAST_ROW || item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER) && (field == "rate-value" || field == "addition_quantity-value")){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }
                }else if(this.type == "vo-claims"){
                    if(item.id == buildspace.constants.GRID_LAST_ROW || item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }
                    //disable editing for claims when nett omission is negative (omission is more than addition)
                    var omissionTotal = item.id > 0 ? number.parse(item['omission_quantity-value'][0]) * number.parse(item['rate-value'][0]) * number.parse(item.total_unit[0]) : 0;
                    var additionTotal = item.id > 0 ? number.parse(item['addition_quantity-value'][0]) * number.parse(item['rate-value'][0]) * number.parse(item.total_unit[0]) : 0;

                    if(omissionTotal > additionTotal){
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

                var xhrArgs = {
                    url: url,
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            if(item.id > 0){
                                for(var property in resp.data){
                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(item, property, resp.data[property]);
                                    }
                                }
                                store.save();
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

                if(self.type == "vo-items" && (attrNameParsed == "type" || attrNameParsed == "uom_id" || attrNameParsed == "addition_quantity") && item.has_addition_build_up_quantity[0]){
                    var onYes = function(){
                        pb.show();
                        dojo.xhrPost(xhrArgs);
                    };

                    var content = '<div>'+nls.detachAllBuildUpAndLink+'</div>';
                    buildspace.dialog.confirm(nls.confirmation,content,60,280, onYes);
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
        onStyleRow: function(e) {
            this.inherited(arguments);

            if(e.node.children[0] && e.node.children[0].children[0].rows.length >= 2){
                var elemToHide = e.node.children[0].children[0].rows[1],
                    childElement = e.node.children[0].children[0].rows[0].children;

                elemToHide.parentNode.removeChild(elemToHide);

                dojo.forEach(childElement, function(child, i){
                    var rowSpan = dojo.attr(child, 'rowSpan');

                    if(!rowSpan || rowSpan < 2)
                        dojo.attr(child, 'rowSpan', 2);
                });
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
            var self = this, item = this.getItem(e.rowIndex), cell = e.cell;
            if(item.id > 0 && this.type == "vo-items"){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.cut,
                    iconClass:"icon-16-container icon-16-cut",
                    onClick: dojo.hitch(this,'cutItems')
                }));
            }

            if(this.type == "vo-items"){
                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.paste,
                    iconClass:"icon-16-container icon-16-paste",
                    onClick: dojo.hitch(this,'pasteItem', e.rowIndex),
                    disabled: this.selectedItem ? false: true
                }));
            }

            if(item.id > 0 && this.type == "vo-items"){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.indent,
                    iconClass:"icon-16-container icon-16-indent",
                    onClick: dojo.hitch(this,'indentOutdent', e.rowIndex,'indent')
                }));

                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.outdent,
                    iconClass:"icon-16-container icon-16-outdent",
                    onClick: dojo.hitch(this,'indentOutdent', e.rowIndex,'outdent')
                }));
            }

            this.rowCtxMenu.addChild(new dijit.MenuItem({
                label: nls.addRow,
                iconClass:"icon-16-container icon-16-add",
                onClick: dojo.hitch(this,'addRow', e.rowIndex)
            }));

            if(item.id > 0){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(this,'deleteRow', e.rowIndex)
                }));
            }
        },
        cutItems: function(){
            this.selectedItem = this.selection.getFirstSelected();
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
                    type: "cut",
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
                            var selectedRowIdx = (firstRowIdx > rowIndex) ? rowIndex : rowIndex-1;
                            self.selection.clear();
                            self.selectedItem = null;
                            self.selection.setSelected(selectedRowIdx, true);

                            selectedRowIdx = (selectedRowIdx - 3) > 0 ? selectedRowIdx-3 : selectedRowIdx;
                            self.scrollToRow(selectedRowIdx);

                            self.disableToolbarButtons(false);
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
                    }
                    rowsToMove.length = 0;
                    pb.hide();
                },
                error: function(error) {
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                    self.selectedItem = null;
                    pb.hide();
                }
            };
            dojo.xhrPost(xhrArgs);
        },
        addRow: function(rowIndex){
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.addingRow+'. '+nls.pleaseWait+'...'
                }),
                store = self.store,
                itemBefore = self.getItem(rowIndex),
                content;
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
                        var colIndex = (self.type == 'vo-items') ? 3 : 1;
                        self.focus.setFocusIndex(rowIndex, colIndex);
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
            var self = this, title = null, msg = null,
                item = self.getItem(rowIndex),
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
                    self.disableToolbarButtons(true);

                    window.setTimeout(function() {
                        self.focus.setFocusIndex(rowIndex, 0);
                    }, 10);
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

            // determine which msg to show in dialogbox when deleting
            if(this.type == 'vo') {
                // for element level
                title = nls.deleteVariationOrderDialogBoxTitle;
                msg   = nls.deleteVariationOrderDialogBoxMsg;
            }else{
                title = nls.deleteVariationOrderItemDialogBoxTitle;
                msg   = nls.deleteVariationOrderItemDialogBoxMsg;
            }

            new buildspace.dialog.confirm(title, msg, 80, 320, function() {
                dojo.xhrPost(xhrArgs);
            }, function() {
                pb.hide();
            });
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
        openBillDialog: function(rowIdx){
            if(rowIdx > -1){
                var variationOrderItem = this.getItem(rowIdx),
                    isLastItem = variationOrderItem.id[0] == buildspace.constants.GRID_LAST_ROW ? true : false;

                BillDialog({
                    variationOrderItem: variationOrderItem,
                    variationOrder: this.variationOrder,
                    isLastItem: isLastItem,
                    type: 'omit_from_bill',
                    locked: this.locked,
                    variationOrderItemGrid: this
                }).show();
            }
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            isDisable = this.locked ? true : isDisable;
            var id;
            switch(this.type){
                case 'vo':
                    id = 'variationOrder-'+this.subPackage.id;
                    break;
                case 'vo-items':
                    id = 'variationOrder-'+this.subPackage.id+'_'+this.variationOrder.id+'-items';
                    break;
                default:
                    throw new Error("type must be set!");
                    break;
            }

            var addRowBtn = dijit.byId(id+'AddRow-button'),
                deleteRowBtn = dijit.byId(id+'DeleteRow-button'),
                indentRowBtn = dijit.byId(id+'IndentRow-button'),
                outdentRowBtn = dijit.byId(id+'OutdentRow-button'),
                omitFromBillBtn = dijit.byId(id+'OmitFromBillRow-button');

            if(indentRowBtn){
                indentRowBtn._setDisabledAttr(isDisable);
            }

            if(outdentRowBtn){
                outdentRowBtn._setDisabledAttr(isDisable);
            }

            if(omitFromBillBtn){
                omitFromBillBtn._setDisabledAttr(isDisable);
            }

            if(addRowBtn){
                addRowBtn._setDisabledAttr(isDisable);
            }

            if(deleteRowBtn){
                deleteRowBtn._setDisabledAttr(isDisable);
            }

            if(isDisable && buttonsToEnable instanceof Array && !this.locked){
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(id+label+'Row-button');
                    if(btn){
                        btn._setDisabledAttr(false);
                    }
                })
            }
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.PostContractSubPackage.VariationOrder.VariationOrderGrid', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        subPackage: null,
        variationOrder: null,
        gridOpts: {},
        locked: false,
        type: null,
        pageId: 0,
        postCreate: function(){
            var id, stackContainerId;
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                type: this.type,
                subPackage: this.subPackage,
                variationOrder: this.variationOrder,
                locked: this.locked
            });

            var grid = this.grid = new VariationOrderGrid(this.gridOpts);

            switch(this.type){
                case 'vo':
                    id = 'variationOrder-'+this.subPackage.id;
                    stackContainerId = 'SP_variationOrder-'+this.subPackage.id;
                    break;
                case 'vo-items':
                    id = 'variationOrder-'+this.subPackage.id+'_'+this.variationOrder.id+'-items';
                    stackContainerId = 'SP_variationOrderItems-'+this.subPackage.id+'_'+this.variationOrder.id;
                    break;
                case 'vo-claims':
                    id = 'variationOrder-'+this.subPackage.id+'_'+this.variationOrder.id+'-claims';
                    break;
                default:
                    throw new Error("type must be set!");
                    break;
            }

            if(!this.locked){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        id: id+'AddRow-button',
                        label: nls.addRow,
                        iconClass: "icon-16-container icon-16-add",
                        disabled: grid.selection.selectedIndex < 0 ? true : false,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                grid.addRow(grid.selection.selectedIndex);
                            }
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                if(this.type == "vo-items"){
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: id+'IndentRow-button',
                            label: nls.indent,
                            iconClass: "icon-16-container icon-16-indent",
                            disabled: grid.selection.selectedIndex < 0 ? true : false,
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
                            id: id+'OutdentRow-button',
                            label: nls.outdent,
                            iconClass: "icon-16-container icon-16-outdent",
                            disabled: grid.selection.selectedIndex < 0 ? true : false,
                            onClick: function(){
                                if(grid.selection.selectedIndex > -1){
                                    grid.indentOutdent(grid.selection.selectedIndex, 'outdent');
                                }
                            }
                        })
                    );
                    toolbar.addChild(new dijit.ToolbarSeparator());
                }

                toolbar.addChild(
                    new dijit.form.Button({
                        id: id+'DeleteRow-button',
                        label: nls.deleteRow,
                        iconClass: "icon-16-container icon-16-delete",
                        disabled: grid.selection.selectedIndex < 0 ? true : false,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                grid.deleteRow(grid.selection.selectedIndex);
                            }
                        }
                    })
                );

                if(this.type == "vo-items"){
                    toolbar.addChild(new dijit.ToolbarSeparator());

                    toolbar.addChild(
                        new dijit.form.Button({
                            id: id+'OmitFromBillRow-button',
                            label: nls.omitFromBills,
                            iconClass: "icon-16-container icon-16-eject",
                            disabled: grid.selection.selectedIndex < 0 ? true : false,
                            onClick: function(){
                                if(grid.selection.selectedIndex > -1){
                                    var rowIndex = grid.selection.selectedIndex;
                                    grid.openBillDialog(rowIndex);
                                }
                            }
                        })
                    );
                }

                this.addChild(toolbar);
            }

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            if(this.type !== 'vo-claims'){
                var container = dijit.byId(stackContainerId+'-stackContainer');
                if(container){
                    var node = document.createElement("div");
                    var child = new dojox.layout.ContentPane( {
                        title: buildspace.truncateString(this.stackContainerTitle, 45),
                        id: this.pageId,
                        content: this,
                        grid: grid,
                        executeScripts: true
                    },node );
                    container.addChild(child);
                    container.selectChild(this.pageId);
                }
            }
        }
    });
});