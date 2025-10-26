define('buildspace/apps/PostContract/VariationOrder/VariationOrderGrid',[
    'dojo/_base/declare',
    "dojo/_base/connect",
    'dojo/_base/lang',
    'dojo/_base/html',
    "dojo/dom-style",
    "dojo/number",
    "dijit/focus",
    'dojo/_base/event',
    'dojo/keys',
    "dojo/has",
    "dijit/TooltipDialog",
    "dijit/popup",
    'dojox/grid/EnhancedGrid',
    "./BillDialog",
    "./ImportedVariationOrderContainer",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, connect, lang, html, domStyle, number, focusUtil, evt, keys, has, TooltipDialog, popup, EnhancedGrid, BillDialog, ImportedVariationOrderContainer, Rearrange, FormulatedColumn, GridFormatter, nls) {

    var VariationOrderGrid = declare('buildspace.apps.PostContract.VariationOrder.VariationOrderEnhancedGrid', EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        selectedItem: null,
        region: 'center',
        project: null,
        locked: false,
        keepSelection: true,
        addUrl: null,
        updateUrl: null,
        deleteUrl: null,
        pasteUrl: null,
        indentUrl: null,
        outdentUrl: null,
        variationOrderFirstLevelContainer: null,
        variationOrderItemContainer: null,
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
            var myTooltipDialog = null;

            this._connects.push(connect.connect(this, 'onCellMouseOver', function(e){
                var item = self.getItem(e.rowIndex),
                    colField = e.cell.field,
                    rowIndex = e.rowIndex;

                var fieldConstantName = colField.replace("-value", "");

                // will show tooltip for formula, if available
                if (!item || !item.hasOwnProperty(fieldConstantName+'-has_formula') || typeof item[fieldConstantName+'-has_formula'] === 'undefined' || ! item[fieldConstantName+'-has_formula'][0] ) {
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
                if(myTooltipDialog !== null){
                    popup.close(myTooltipDialog);
                    myTooltipDialog = null;
                }
            }));

            if(!this.locked){
                this.on("RowContextMenu", function(e){
                    self.selection.clear();
                    var item = self.getItem(e.rowIndex);
                    self.selection.setSelected(e.rowIndex, true);

                    if(self.type == "vo" || (self.type == "vo-items" && self.variationOrder.is_approved[0] == "false")){
                        var buttonsToEnable = [];
                        self.contextMenu(e);
                        if(item && !isNaN(parseInt(String(item.id))) && !item.is_from_rfv[0] && item.can_be_edited[0]){
                            var prevItem = (e.rowIndex > 0) ? self.getItem(e.rowIndex-1) : null;
                            var disableAll = false;
                            if(self.type == "vo-items" && (!prevItem || prevItem.is_from_rfv[0])){
                                disableAll = true;
                                buttonsToEnable  = ["Add", "Delete", "OmitFromBill"];
                            }
                            self.disableToolbarButtons(disableAll, buttonsToEnable);
                        }else{
                            buttonsToEnable = (self.type == "vo-items" && item.is_from_rfv[0]) ? ["OmitFromBill"] : ["Add", "OmitFromBill"];
                            self.disableToolbarButtons(true, buttonsToEnable);
                        }
                    }
                }, true);

                this.on('RowClick', function(e){
                    var item = self.getItem(e.rowIndex);
                    var buttonsToEnable = [];
                    if(item && !isNaN(parseInt(String(item.id))) && item.can_be_edited[0] && !item.is_from_rfv[0]){
                        var prevItem = (e.rowIndex > 0) ? self.getItem(e.rowIndex-1) : null;
                        var disableAll = false;
                        if(self.type == "vo-items" && (!prevItem || prevItem.is_from_rfv[0])){
                            disableAll = true;
                            buttonsToEnable  = ["Add", "Delete", "OmitFromBill"];
                        }
                        self.disableToolbarButtons(disableAll, buttonsToEnable);
                    }else{
                        buttonsToEnable = (self.type == "vo-items" && item.is_from_rfv[0]) ? ["OmitFromBill"] : ["Add", "OmitFromBill"];
                        self.disableToolbarButtons(true, buttonsToEnable);
                    }
                });
            }

            this.on('CellClick', function(e) {
                var colField = e.cell.field,
                    item = this.getItem(e.rowIndex);

                if (colField == 'attachment' && item.class == 'VariationOrder' ){
                    this.variationOrderFirstLevelContainer.addUploadAttachmentContainer(item);
                }else if(colField == 'attachment' && item.class == 'VariationOrderItem'){
                    this.variationOrderItemContainer.addUploadAttachmentContainer(item);
                }
            });
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;
            if(inCell != undefined){
                var item = this.getItem(inRowIndex),
                    field = inCell.field;
                if(this.type=="vo"){
                    if(!item.can_be_edited[0] || self.locked) return false;
                    if ( field === 'is_approved' && String(item.id) == buildspace.constants.GRID_LAST_ROW ){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return false;
                    }

                    if (!isNaN(parseInt(String(item.id))) && !item.can_be_edited[0] ){
                        window.setTimeout(function() {
                            self.disableToolbarButtons(true);
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return false;
                    }
                }else if(this.type == "vo-items"){
                    if(!item.can_be_edited[0]) return false;
                    /*
                     for items omitted from bills, all columns cannot be editable except for addition qty column
                     */
                    if ( field != 'addition_quantity-value' && field != 'current_percentage-value' && field != 'current_amount-value' && field != 'up_to_date_percentage-value' && field != 'up_to_date_amount-value' && !isNaN(parseInt(item.id[0])) && item.bill_item_id[0] > 0 ){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return false;
                    }

                    //disable editing for type, unit and budget columns for rfv vo items
                    if(!isNaN(parseInt(String(item.id))) && item.is_from_rfv[0] && (field == "type" || field=="uom_id" || field=="reference_rate-value" || field=="reference_quantity-value") ){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return false;
                    }

                    if(field != "description" && field != "type" && !isNaN(parseInt(String(item.id))) && item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return false;
                    }

                    if((String(item.id) == buildspace.constants.GRID_LAST_ROW || item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER) && (field == "rate-value" || field == "addition_quantity-value" || field == "reference_rate-value" || field == "reference_quantity-value")){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return false;
                    }
                }else if(this.type == "vo-claims"){
                    if(!item.can_claim[0]) return false;
                    if(String(item.id) == buildspace.constants.GRID_LAST_ROW || item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER){
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
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = this.getItem(rowIdx), store = this.store;

            var attrNameParsed = inAttrName.replace("-value","");//for any formulated column
            if(inAttrName.indexOf("-value") !== -1){
                val = this.formulatedColumn.convertRowIndexToItemId(val, rowIdx);
            }

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    attr_name: attrNameParsed,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                if(item && isNaN(parseInt(String(item.id)))){
                    var prevItem = rowIdx > 0 ? this.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0,
                        relation_id: item.relation_id
                    });
                    url = this.addUrl;
                }

                var postFunc = function(){
                    var postFuncPb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.pleaseWait+'...'
                    });

                    postFuncPb.show().then(function(){
                        dojo.xhrPost({
                            url: url,
                            content: params,
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success){
                                    if(!isNaN(parseInt(String(item.id)))){
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

                                    if(!item.can_be_edited[0]) self.disableToolbarButtons(true, ["Add", "OmitFromBill"]);

                                    var cell = self.getCellByField(inAttrName);
                                    window.setTimeout(function() {
                                        self.focus.setFocusIndex(rowIdx, cell.index);
                                    }, 10);
                                    postFuncPb.hide();
                                }
                            },
                            error: function(error) {
                                postFuncPb.hide();
                            }
                        });
                    });
                };

                if(this.type == "vo-items" && (attrNameParsed == "type" || attrNameParsed == "uom_id" || attrNameParsed == "addition_quantity") && item.has_addition_build_up_quantity[0]){
                    var content = '<div>'+nls.detachAllBuildUpAndLink+'</div>';
                    buildspace.dialog.confirm(nls.confirmation,content,90,380, postFunc);
                    this.doCancelEdit(rowIdx);
                }else{
                    if((attrNameParsed == 'status') && (val == buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PENDING)){
                        pb.show().then(function(){
                            dojo.xhrGet({
                                url: "variationOrder/getVerifierList",
                                content: {
                                    pid: self.project.id,
                                    variationOrderId: item.id,
                                },
                                handleAs: 'json',
                                load: function(resp) {
                                    var count = 0;
                                    count += Object.keys(resp.verifiers).length;
                                    count += resp.topManagementVerifiers.length;

                                    if(resp.success){
                                        buildspace.dialog.confirm(nls.confirmSubmit, '<div>'+nls.numberOfReviewers+': ' + count + '</div>', 60, 200, function(){
                                            postFunc();
                                        }, function(){
                                            store.setValue(item, 'status', buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PREPARING);
                                        });
                                    } else {
                                        self.doCancelEdit(rowIdx);
                                        store.setValue(item, 'status', buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PREPARING);

                                        buildspace.dialog.alert('Warning', resp.errorMsg, 100, 300);
                                    }
                                    pb.hide();
                                },
                                error: function(error) {
                                    pb.hide();
                                }
                            });
                        });
                    }else{
                        postFunc();
                    }
                }
            }

            this.inherited(arguments);
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
            var item = this.getItem(e.rowIndex);

            if(item && !isNaN(parseInt(String(item.id))) && this.type == "vo-items" && !item.is_from_rfv[0]){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.cut,
                    iconClass:"icon-16-container icon-16-cut",
                    onClick: dojo.hitch(this,'cutItems')
                }));
            }

            if(this.type == "vo-items" && !item.is_from_rfv[0]){
                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.paste,
                    iconClass:"icon-16-container icon-16-paste",
                    onClick: dojo.hitch(this,'pasteItem'),
                    disabled: this.selectedItem ? false: true
                }));
            }

            if(item && !isNaN(parseInt(String(item.id))) && this.type == "vo-items" && !item.is_from_rfv[0]){
                var prevItem = (e.rowIndex > 0) ? this.getItem(e.rowIndex-1) : null;
                if(prevItem && !prevItem.is_from_rfv[0]){
                    this.rowCtxMenu.addChild(new dijit.MenuItem({
                        label: nls.indent,
                        iconClass:"icon-16-container icon-16-indent",
                        onClick: dojo.hitch(this,'indentOutdent', 'indent')
                    }));

                    this.rowCtxMenu.addChild(new dijit.MenuItem({
                        label: nls.outdent,
                        iconClass:"icon-16-container icon-16-outdent",
                        onClick: dojo.hitch(this,'indentOutdent', 'outdent')
                    }));
                }
            }

            if(item && (this.type == "vo" || (this.type == "vo-items" && !item.is_from_rfv[0]))){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.addRow,
                    iconClass:"icon-16-container icon-16-add",
                    onClick: dojo.hitch(this,'addRow')
                }));
            }

            if(item && !isNaN(parseInt(String(item.id))) && !item.is_from_rfv[0] && item.can_be_edited[0]){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(this,'deleteRow')
                }));
            }
        },
        cutItems: function(){
            this.selectedItem = this.selection.getFirstSelected();
        },
        pasteItem: function(){
            if(this.selectedItem && this.selection.selectedIndex > -1) {
                var self = this,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.pleaseWait+'...'
                    }),
                    store = this.store,
                    rowIndex = this.selection.selectedIndex,
                    targetItem = this.selection.getFirstSelected(),
                    prevItemId = (isNaN(parseInt(targetItem.id[0])) && rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: self.pasteUrl,
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

                                    var selectRowIndex = (firstRowIdx > rowIndex) ? rowIndex : rowIndex - 1;
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
                            }
                            self.disableToolbarButtons(false);
                            rowsToMove.length = 0;
                            pb.hide();
                        },
                        error: function(error) {
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                            self.selectedItem = null;
                            pb.hide();
                        }
                    });
                });
            }
        },
        selectAfterPaste: function (rowIndex, scroll){
            this.selection.clear();
            this.selectedItem = null;
            this.selection.setSelected(rowIndex, true);

            if(scroll){
                this.scrollToRow(((rowIndex - 3) > 0) ? rowIndex - 3 : rowIndex);
            }
        },
        addRow: function(){
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            if(this.selection.selectedIndex > -1){
                var rowIndex = this.selection.selectedIndex;
                var self = this,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.addingRow+'. '+nls.pleaseWait+'...'
                    }),
                    store = this.store,
                    itemBefore = this.getItem(rowIndex),
                    content;

                if(!isNaN(parseInt(String(itemBefore.id)))){
                    content = { before_id: itemBefore.id, _csrf_token:itemBefore._csrf_token };
                }else{
                    var prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                    content = { id: itemBefore.id, prev_item_id: prevItemId, relation_id: itemBefore.relation_id, _csrf_token:itemBefore._csrf_token }
                }

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: self.addUrl,
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
                    });
                });
            }
        },
        deleteRow: function(){
            var rowIndex = this.selection.selectedIndex,
                item = this.getItem(rowIndex);

            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            if(this.selection.selectedIndex > -1 && item && !isNaN(parseInt(String(item.id))) && !item.is_from_rfv[0]) {
                var self = this, title = null, msg = null,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.deleting+'. '+nls.pleaseWait+'...'
                    });

                // determine which msg to show in dialogbox when deleting
                if(this.type == 'vo') {
                    // for element level
                    title = nls.deleteVariationOrderDialogBoxTitle;
                    msg   = nls.deleteVariationOrderDialogBoxMsg;
                }else{
                    title = nls.deleteVariationOrderItemDialogBoxTitle;
                    msg   = nls.deleteVariationOrderItemDialogBoxMsg;
                }

                new buildspace.dialog.confirm(title, msg, 90, 380, function() {

                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: self.deleteUrl,
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
                        });
                    });

                }, function() {
                });
            }
        },
        indentOutdent: function(type){
            var rowIndex = this.selection.selectedIndex,
                item = this.getItem(rowIndex);

            if(this.selection.selectedIndex > 0 && item && !isNaN(parseInt(String(item.id)))) {
                var self = this,
                    store = this.store,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.recalculateRows+'. '+nls.pleaseWait+'...'
                    });

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: self[type+'Url'],
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
                });
            }
        },
        openBillDialog: function(){
            var item = this.getItem(this.selection.selectedIndex);

            if(this.selection.selectedIndex > -1 && item) {
                var isLastItem = item.id[0] == buildspace.constants.GRID_LAST_ROW;

                BillDialog({
                    variationOrderItem: item,
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
                    id = 'variationOrder-'+this.project.id;
                    break;
                case 'vo-items':
                    id = 'variationOrder-'+this.project.id+'_'+this.variationOrder.id+'-items';
                    break;
                default:
                    return;// no need to search for elements to be disabled
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

    return declare('buildspace.apps.PostContract.VariationOrder.VariationOrderGrid', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        project: null,
        variationOrder: null,
        variationOrderFirstLevelContainer: null,
        variationOrderItemContainer: null,
        gridOpts: {},
        locked: false,
        type: null,
        pageId: 0,
        claimCertificate: null,
        postCreate: function(){
            var id, stackContainerId;
            var self = this;
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                type: this.type,
                project: this.project,
                variationOrder: this.variationOrder,
                locked: this.locked,
                variationOrderFirstLevelContainer: this.variationOrderFirstLevelContainer,
                variationOrderItemContainer: this.variationOrderItemContainer,
            });

            var grid = this.grid = new VariationOrderGrid(this.gridOpts);

            switch(this.type){
                case 'vo':
                    id = 'variationOrder-'+this.project.id;
                    stackContainerId = 'variationOrder-'+this.project.id;
                    break;
                case 'vo-items':
                    id = 'variationOrder-'+this.project.id+'_'+this.variationOrder.id+'-items';
                    stackContainerId = 'variationOrderItems-'+this.project.id+'_'+this.variationOrder.id;
                    break;
                case 'vo-claims':
                    id = 'variationOrder-'+this.project.id+'_'+this.variationOrder.id+'-claims';
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
                        disabled: grid.selection.selectedIndex < 0,
                        onClick: dojo.hitch(grid, "addRow")
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                if(this.type == "vo-items"){
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: id+'IndentRow-button',
                            label: nls.indent,
                            iconClass: "icon-16-container icon-16-indent",
                            disabled: grid.selection.selectedIndex < 0,
                            onClick: dojo.hitch(grid, "indentOutdent", 'indent')
                        })
                    );
                    toolbar.addChild(new dijit.ToolbarSeparator());
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: id+'OutdentRow-button',
                            label: nls.outdent,
                            iconClass: "icon-16-container icon-16-outdent",
                            disabled: grid.selection.selectedIndex < 0,
                            onClick: dojo.hitch(grid, "indentOutdent", 'outdent')
                        })
                    );
                    toolbar.addChild(new dijit.ToolbarSeparator());
                }

                toolbar.addChild(
                    new dijit.form.Button({
                        id: id+'DeleteRow-button',
                        label: nls.deleteRow,
                        iconClass: "icon-16-container icon-16-delete",
                        disabled: grid.selection.selectedIndex < 0,
                        onClick: dojo.hitch(grid, "deleteRow")
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.importedVariationOrders,
                        iconClass: "icon-16-container icon-16-contrast",
                        onClick: dojo.hitch(self, "createImportedVariationOrdersContainer")
                    })
                );

                if(this.type == "vo-items"){
                    toolbar.addChild(new dijit.ToolbarSeparator());

                    toolbar.addChild(
                        new dijit.form.Button({
                            id: id+'OmitFromBillRow-button',
                            label: nls.omitFromBills,
                            iconClass: "icon-16-container icon-16-eject",
                            disabled: grid.selection.selectedIndex < 0,
                            onClick: dojo.hitch(grid, "openBillDialog")
                        })
                    );
                }

                this.addChild(toolbar);
            }

            this.addChild(grid);

            if(this.type !== 'vo-claims'){
                var container = dijit.byId(stackContainerId+'-stackContainer');
                if(container){
                    container.addChild(new dojox.layout.ContentPane( {
                        title: buildspace.truncateString(this.stackContainerTitle, 60),
                        content: this,
                        grid: grid,
                        id: this.pageId,
                        executeScripts: true
                    }));
                    container.selectChild(this.pageId);
                }
            }
        },
        createImportedVariationOrdersContainer: function(){
            var widget = dijit.byId('variationOrderContainer');

            var importedVoContainerId = 'importedVariationOrdersContainer-variationOrderListingGrid';
            var importedVoContainer = dijit.byId(importedVoContainerId);
            if(importedVoContainer){
                widget.removeChild(importedVoContainer);
                importedVoContainer.destroy();
            }

            var importedVariationOrdersContainer = new ImportedVariationOrderContainer({
                id: importedVoContainerId,
                title: nls.importedVariationOrders,
                region: 'bottom',
                parentContainer: widget,
                project: this.project,
                claimCertificate: this.claimCertificate
            });

            widget.addChild(importedVariationOrdersContainer);
        }
    });
});
