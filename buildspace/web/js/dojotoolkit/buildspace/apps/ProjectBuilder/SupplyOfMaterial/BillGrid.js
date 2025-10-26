define('buildspace/apps/ProjectBuilder/SupplyOfMaterial/BillGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dijit/Menu",
    'dojo/number',
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
    "dijit/MenuSeparator",
    'buildspace/widget/grid/cells/Select',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function(declare, lang, array, domAttr, Menu, number, MenuPlugin, Selector, Rearrange, FormulatedColumn, evt, keys, focusUtil, html, xhr, PopupMenuItem, MenuSeparator, Select, Textarea, FormulaTextBox, GridFormatter, nls){

    var BillGrid = declare('buildspace.apps.ProjectBuilder.SupplyOfMaterial.BillGrid', dojox.grid.EnhancedGrid, {
        type: null,
        billId: -1,
        elementId: 0,
        itemId: -1,
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        keepSelection: true,
        rowSelector: '0px',
        addUrl: null,
        updateUrl: null,
        rowUpdateUrl: null,
        deleteUrl: null,
        deleteRateUrl: null,
        deleteQuantityUrl: null,
        pasteUrl: null,
        indentUrl: null,
        outdentUrl: null,
        headerMenu: null,
        parentGrid: null,
        elementGridStore: null,
        currentBillLockedStatus: false,
        currentGridType: 'element',
        constructor:function(args){
            this.type                    = args.type;
            this.hierarchyTypes          = args.hierarchyTypes;
            this.hierarchyTypesForHead   = args.hierarchyTypesForHead;
            this.unitOfMeasurements      = args.unitOfMeasurements;
            this.currencySetting         = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            this.currentBillLockedStatus = args.currentBillLockedStatus;
            this.currentGridType         = args.currentGridType;

            this.formatter = new GridFormatter();

            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});

            this.setColumnStructure();
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            this.on("RowContextMenu", function(e){
                self.selection.clear();
                self.selection.setSelected(e.rowIndex, true);
                self.setupContextMenuAndToolbarButtonRestriction(e);
                self.contextMenu(e);
            }, true);

            this.on('RowClick', function(e){
                self.setupContextMenuAndToolbarButtonRestriction(e);
            });
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;
            var attrNameParsed = inAttrName.replace("-value","");//for any formulated column
            if(inAttrName.indexOf("-value") !== -1){
                val = this.formulatedColumn.convertRowIndexToItemId(val, rowIdx);
            }

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    attr_name: attrNameParsed,
                    val: val,
                    bill_id : self.billId,
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

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }

                    if(self.type != 'tree'){
                        dojo.forEach(data.other_elements, function(node){
                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(element){
                                for(var property in node){
                                    if(element.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(element, property, node[property]);
                                    }
                                }
                            }});
                        });
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
                    });
                });
            }

            self.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;

            if(this.type=='tree'){
                if(inCell != undefined){
                    var item = this.getItem(inRowIndex),
                        field = inCell.field;

                    // if current bill has been set to locked status, don't allow user input
                    // into selected column
                    if (this.currentBillLockedStatus) {
                        return false;
                    }

                    if(parseInt(String(item.id)) > 0){
                        if((item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N) && field != 'description' && field != 'type' && inCell.editable){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        } else if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && field != 'description' && field != 'type' && inCell.editable){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }

                        if(self.type == 'tree' && field === 'type'){
                            var nextItem = self.getItem(inRowIndex+1);

                            if((item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N) && inCell.editable && nextItem !== undefined && parseInt(String(item.level)) < parseInt(String(nextItem.level))) {
                                inCell.options = self.hierarchyTypesForHead.options;
                                inCell.values  = self.hierarchyTypesForHead.values;
                            } else {
                                inCell.options = self.hierarchyTypes.options;
                                inCell.values  = self.hierarchyTypes.values;
                            }
                        }
                    } else if ( field !== 'description' && field !== 'type' ) {
                        return;
                    }
                }
            }
            return this._canEdit;
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        setColumnStructure: function(){
            var formatter = this.formatter;

            // editable mode for grid
            var editable = true;

            // if current version is not original bill or is locked then disable editable mode
            if (this.currentBillLockedStatus){
                editable = false;
            }

            if(this.type == 'tree'){
                var unitOfMeasurements = this.unitOfMeasurements,
                    hierarchyTypes = this.hierarchyTypes;
                this.structure = [{
                    name: 'No',
                    field: 'id',
                    styles: "text-align:center;",
                    width: '30px',
                    formatter: formatter.rowCountCellFormatter,
                    noresize: true
                },{
                    name: nls.description,
                    field: 'description',
                    width: 'auto',
                    editable: editable,
                    cellType: 'buildspace.widget.grid.cells.Textarea',
                    formatter: formatter.treeCellFormatter,
                    noresize: true
                },{
                    name: nls.type,
                    field: 'type',
                    width: '70px',
                    styles: 'text-align:center;',
                    editable: editable,
                    type: 'dojox.grid.cells.Select',
                    options: hierarchyTypes.options,
                    values: hierarchyTypes.values,
                    formatter: formatter.typeCellFormatter,
                    noresize: true
                },{
                    name: nls.unit,
                    field: 'uom_id',
                    width: '70px',
                    editable: editable,
                    styles: 'text-align:center;',
                    type: 'dojox.grid.cells.Select',
                    options: unitOfMeasurements.options,
                    values: unitOfMeasurements.values,
                    formatter: formatter.unitIdCellFormatter,
                    noresize: true
                },{
                    name: nls.supplyRate + " ("+this.currencySetting+")",
                    field: 'supply_rate',
                    styles: "text-align:right;",
                    width: '120px',
                    editable: editable,
                    cellType: 'buildspace.widget.grid.cells.Textarea',
                    formatter: formatter.currencyCellFormatter,
                    noresize: true
                }];
            }else{
                this.structure = [{
                    name: 'No',
                    field: 'id',
                    styles: "text-align:center;",
                    width: '30px',
                    formatter: formatter.rowCountCellFormatter,
                    noresize: true
                },{
                    name: nls.description,
                    field: 'description',
                    width: 'auto',
                    editable: editable,
                    cellType: 'buildspace.widget.grid.cells.Textarea',
                    noresize: true
                },{
                    name: nls.total,
                    field: 'total',
                    styles: "text-align:right;",
                    width: '120px',
                    formatter: formatter.unEditableCurrencyCellFormatter,
                    noresize: true
                }];
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
            
            pb.show().then(function(){
                dojo.xhrPost({
                    url: self.pasteUrl,
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
        pasteCell: function(rowIndex, cell){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                }),
                store = this.store,
                targetItem = this.selection.getFirstSelected();

            pb.show().then(function(){
                dojo.xhrPost({
                    url: self.pasteUrl,
                    content: {
                        target_id: targetItem.id,
                        id: self.selectedItem.id,
                        _csrf_token: self.selectedItem._csrf_token
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        var rowsToMove = [];
                        if(resp.success){
                            var children = resp.c;
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
                        self.selection.clear();
                        self.disableToolbarButtons(true);
                        self.selectedItem = null;
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
        },
        addRow: function(rowIndex){
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            var self = this,
                prevItemId,
                content,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.addingRow+'. '+nls.pleaseWait+'...'
                }),
                store = self.store,
                itemBefore = self.getItem(rowIndex),
                billId = self.billId;
            if(itemBefore.id > 0){
                prevItemId = (rowIndex > 0) ? self.getItem(rowIndex-1).id : 0;
                content = { prev_item_id: prevItemId, before_id: itemBefore.id, bill_id: billId, _csrf_token:itemBefore._csrf_token};
            }else{
                prevItemId = (rowIndex > 0) ? self.getItem(rowIndex-1).id : 0;
                content = { id: itemBefore.id, bill_id: billId, prev_item_id: prevItemId, relation_id: itemBefore.relation_id, _csrf_token:itemBefore._csrf_token}
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
                            var colIndex = (self.type == 'tree') ? 2 : 1;
                            self.selection.setSelected(rowIndex, true);
                            self.focus.setFocusIndex(rowIndex, colIndex);
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
        },
        deleteRow: function(rowIndex){
            var self = this, title = null, msg = null,
                item = self.getItem(rowIndex),
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

            // determine which msg to show in dialogbox when deleting
            if (this.type != 'tree') {
                // for element level
                title = nls.deleteElementDialogBoxTitle;
                msg   = nls.deleteElementDialogBoxMsg;
            } else {
                // for head/item level
                if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N) {
                    title = nls.deleteHeadDialogBoxTitle;
                    msg   = nls.deleteHeadDialogBoxMsg;
                } else {
                    title = nls.deleteItemDialogBoxTitle;
                    msg   = nls.deleteItemMsg;
                }
            }

            new buildspace.dialog.confirm(title, msg, 80, 320, function() {
                pb.show().then(function(){
                    dojo.xhrPost(xhrArgs);
                });
            }, function() {
                //pb.hide();
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

                if(parseInt(String(item.id)) > 0){
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
            }
        },
        refreshGrid: function(){
            this.beginUpdate();

            this.set('structure', this.structure);
            this.store.close();

            this.pluginMgr = new this._pluginMgrClass(this);
            this.pluginMgr.preInit();
            this.pluginMgr.postInit();
            this.pluginMgr.startup();

            this._refresh();

            this.endUpdate();
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
            var info       = {target: e.target, coords: e.keyCode !== keys.F10 && "pageX" in e ? {x: e.pageX, y: e.pageY } : null};
            var item       = this.getItem(e.rowIndex);

            // if current bq addendum is locked, then don't generate context menu on right click
            if ( this.currentBillLockedStatus ){
                return false;
            }

            if(rowCtxMenu && item && (this.selection.isSelected(e.rowIndex) || e.rowNode && html.hasClass(e.rowNode, 'dojoxGridRowbar'))){
                rowCtxMenu._openMyself(info);
                evt.stop(e);
                return;
            }
        },
        contextMenuItems: function(e){
            var topRowItem = this.getItem(e.rowIndex - 1),
                item = this.getItem(e.rowIndex),
                cell = e.cell, disableButton = false,
                disableIndentButton = false,
                disableDeleteButton = false,
                disableAddButton = false;

            if ( this.type == 'tree' ){
                if ( topRowItem === undefined ){
                    disableAddButton = true;
                }

                if ( topRowItem && topRowItem.type[0] === buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER ){
                    disableAddButton = true;
                }
            }

            if(parseInt(String(item.id)) > 0){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.cut,
                    iconClass:"icon-16-container icon-16-cut",
                    onClick: dojo.hitch(this,'cutItems'),
                    disabled: disableButton
                }));

                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.copy,
                    iconClass:"icon-16-container icon-16-copy",
                    onClick: dojo.hitch(this,'copyItems'),
                    disabled: disableButton
                }));
            }

            var disabledPaste = this.selectedItem ? false : true;

            this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                label: nls.paste,
                iconClass:"icon-16-container icon-16-paste",
                onClick: dojo.hitch(this, 'pasteItem', e.rowIndex, cell),
                disabled: disableButton ? disableButton : disabledPaste
            }));

            if(parseInt(String(item.id)) > 0 && this.type == 'tree'){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.indent,
                    iconClass:"icon-16-container icon-16-indent",
                    onClick: dojo.hitch(this,'indentOutdent', e.rowIndex,'indent'),
                    disabled: disableIndentButton
                }));
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.outdent,
                    iconClass:"icon-16-container icon-16-outdent",
                    onClick: dojo.hitch(this,'indentOutdent', e.rowIndex,'outdent'),
                    disabled: disableIndentButton
                }));
            }

            this.rowCtxMenu.addChild(new dijit.MenuItem({
                label: nls.addRow,
                iconClass:"icon-16-container icon-16-add",
                onClick: dojo.hitch(this,'addRow', e.rowIndex),
                disabled: disableAddButton
            }));

            if(parseInt(String(item.id)) > 0){
                this.rowCtxMenu.addChild(new MenuSeparator());
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(this,'deleteRow', e.rowIndex),
                    disabled: disableDeleteButton
                }));
            }
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable) {
            var addRowBtn = dijit.byId(this.billId+this.elementId+'AddRow-button'),
                deleteRowBtn = dijit.byId(this.billId+this.elementId+'DeleteRow-button'),
                indentBtn = dijit.byId(this.billId+this.elementId+'IndentRow-button'),
                outdentBtn = dijit.byId(this.billId+this.elementId+'OutdentRow-button');

            addRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);

            if(indentBtn)
                indentBtn._setDisabledAttr(isDisable);
            if(outdentBtn)
                outdentBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(_this.billId+_this.elementId+label+'Row-button');
                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        },
        disableAllButtonWhenCurrentBillIsLocked: function() {
            if (! this.currentBillLockedStatus) {
                this.disableToolbarButtons(true, ['Add']);
            }
        },
        setupContextMenuAndToolbarButtonRestriction: function(e) {
            var item = this.getItem(e.rowIndex);

            if(item && item.id > 0 && ! this.currentBillLockedStatus) {
                this.disableToolbarButtons(false);
            } else {
                this.disableAllButtonWhenCurrentBillIsLocked();
            }
        }
    });

    return declare('buildspace.apps.ProjectBuilder.SupplyOfMaterial.BillGridBuilder', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;border:0px!important;",
        gutters: false,
        stackContainerTitle: '',
        billId: -1,
        elementId: 0,
        itemId: -1,
        rowSelector: null,
        gridOpts: {},
        type: null,
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, { billId: this.billId, elementId: this.elementId, type: this.type, region: "center", borderContainerWidget: this });
            var grid = this.grid = new BillGrid(this.gridOpts);

            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.billId+this.elementId+'AddRow-button',
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

            if(this.type == 'tree'){
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: this.billId+this.elementId+'IndentRow-button',
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
                        id: this.billId+this.elementId+'OutdentRow-button',
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
            }
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: this.billId+this.elementId+'DeleteRow-button',
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

            if(this.type != 'tree'){
                var self = this;

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.printBill,
                        iconClass: "icon-16-container icon-16-print",
                        onClick: function(e) {
                            window.open('supplyOfMaterialBill/printBill/id/' + self.billId, '_blank');
                        }
                    })
                );
            }

            this.addChild(toolbar);
            this.addChild(grid);

            var container = dijit.byId('supplyOfMaterialGrid'+this.billId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                container.addChild(new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    content: this,
                    id: this.pageId,
                    grid: grid
                }, node));
                container.selectChild(this.pageId);
            }
        }
    });
});