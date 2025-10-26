define('buildspace/apps/Location/ProjectStructureLocationCode/LocationCodeTabContainer',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/html',
    "dojo/dom-style",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/Filter',
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/Location'],
function(declare, lang, html, domStyle, evt, keys, focusUtil, EnhancedGrid, FilterToolbar, Rearrange, GridFormatter, nls) {

    var Grid = declare('buildspace.apps.Location.ProjectStructureLocationCode.LocationCodeGrid', EnhancedGrid, {
        rootProject: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        selectedItem: null,
        pasteOp: null,
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
        },
        canSort: function () {
            return false;
        },
        postCreate: function () {
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

            this.on('RowClick', function (e) {
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
                    pid : this.rootProject.id,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = "location/projectStructureLocationCodeUpdate";

                if(item && isNaN(parseInt(item.id[0]))){
                    var prevItem = rowIdx > 0 ? this.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0
                    });
                    url = "location/projectStructureLocationCodeAdd";
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
                    if(field != 'description' && inCell.editable){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return false;
                    }

                } else if ( field !== 'description') {
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
        dodblclick: function (e) {
            this.onRowDblClick(e);
        },
        onRowDblClick: function (e) {
            this.inherited(arguments);
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
        contextMenuItems: function (e) {
            var item = this.getItem(e.rowIndex);
            if(item && !isNaN(parseInt(item.id[0]))){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.cut,
                    iconClass: "icon-16-container icon-16-cut",
                    onClick: dojo.hitch(this, "cutItem")
                }));
                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.copy,
                    iconClass:"icon-16-container icon-16-copy",
                    onClick: dojo.hitch(this,'copyItem')
                }));
            }

            this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                label: nls.paste,
                iconClass:"icon-16-container icon-16-paste",
                disabled: !this.selectedItem,
                onClick: dojo.hitch(this, "pasteItem")
            }));

            if(item && !isNaN(parseInt(item.id[0]))){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.indent,
                    iconClass:"icon-16-container icon-16-indent",
                    onClick: dojo.hitch(this,'indentOutdent','indent')
                }));
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.outdent,
                    iconClass:"icon-16-container icon-16-outdent",
                    onClick: dojo.hitch(this,'indentOutdent','outdent')
                }));
            }

            this.rowCtxMenu.addChild(new dijit.MenuItem({
                label: nls.addRow,
                iconClass:"icon-16-container icon-16-add",
                onClick: dojo.hitch(this,'addRow')
            }));

            if(item && !isNaN(parseInt(item.id[0]))){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(this,'deleteRow')
                }));
            }
        },
        cutItem: function () {
            this.selectedItem = this.selection.getFirstSelected();
            this.pasteOp = 'cut';
        },
        copyItem: function () {
            this.selectedItem = this.selection.getFirstSelected();
            this.pasteOp = 'copy';
        },
        pasteItem: function () {
            if(this.selectedItem && this.pasteOp && this.selection.selectedIndex > -1){
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
                        url: "location/ProjectStructureLocationCodePaste",
                        content: {
                            type: self.pasteOp,
                            target_id: targetItem.id,
                            prev_item_id: prevItemId,
                            id: self.selectedItem.id,
                            _csrf_token: targetItem._csrf_token
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
                        pid: this.rootProject.id,
                        _csrf_token:itemBefore._csrf_token
                    };
                }else{
                    prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                    content = {
                        id: itemBefore.id,
                        pid: this.rootProject.id,
                        prev_item_id: prevItemId,
                        _csrf_token:itemBefore._csrf_token
                    };
                }

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'location/projectStructureLocationCodeAdd',
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
        deleteRow: function () {
            var rowIndex = this.selection.selectedIndex,
                item = this.getItem(rowIndex);

            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            if(this.selection.selectedIndex > -1 && item && !isNaN(parseInt(item.id[0]))){
                var self = this,
                    title = null,
                    msg = null,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.deleting+'. '+nls.pleaseWait+'...'
                    });

                if ((parseInt(item.rgt[0]) - parseInt(item.lft[0])) > 1) {
                    title = nls.deleteHeadDialogBoxTitle;
                    msg   = nls.deleteHeadDialogBoxMsg;
                } else {
                    title = nls.deleteItemDialogBoxTitle;
                    msg   = nls.deleteItemDialogBoxMsg;
                }

                new buildspace.dialog.confirm(title, msg, 90, 320, function() {
                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: "location/projectStructureLocationCodeDelete",
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
                                    buildspace.dialog.alert(nls.cannotDeleteProjectStructureLocationCodeTitle,nls.cannotDeleteProjectStructureLocationCodeMsg,90,320);
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
                        url: type == 'indent' ? 'location/projectStructureLocationCodeIndent' : 'location/projectStructureLocationCodeOutdent',
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
        disableToolbarButtons: function (isDisable, buttonsToEnable) {
            var addRowBtn = dijit.byId(this.rootProject.id+'ProjectStructureLocationCodeAddRow-button'),
                deleteRowBtn = dijit.byId(this.rootProject.id+'ProjectStructureLocationCodeDeleteRow-button'),
                indentBtn = dijit.byId(this.rootProject.id+'ProjectStructureLocationCodeIndentRow-button'),
                outdentBtn = dijit.byId(this.rootProject.id+'ProjectStructureLocationCodeOutdentRow-button');

            addRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);

            if(indentBtn)
                indentBtn._setDisabledAttr(isDisable);
            if(outdentBtn)
                outdentBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(_this.rootProject.id+'ProjectStructureLocationCode'+label+'Row-button');
                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        }
    });

    return declare('buildspace.apps.Location.ProjectStructureLocationCode.LocationCodeTabContainer', dijit.layout.BorderContainer, {
        region: "center",
        rootProject: null,
        style:"border:none;padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        postCreate: function() {
            this.inherited(arguments);

            var CustomFormatter = {
                yesNoCellFormatter: function(cellValue, rowIdx, cell){
                    return cellValue == 'true' ? nls.yesCapital : nls.noCapital;
                },
                treeCellFormatter: function (cellValue, rowIdx) {
                    var item = this.grid.getItem(rowIdx);
                    var level = item.level * 16;
                    cellValue = cellValue == null ? '&nbsp' : cellValue;
                    if ((parseInt(item.rgt[0]) - parseInt(item.lft[0])) > 1) {
                        cellValue = '<b>' + cellValue + '</b>';
                    }
                    return '<div class="treeNode" style="padding-left:' + level + 'px;"><div class="treeContent">' + cellValue + '&nbsp;</div></div>';
                }
            };
            var formatter = new GridFormatter();
            var grid = Grid({
                id: this.rootProject.id-"ProjectStructureLocationCodeGrid",
                rootProject: this.rootProject,
                structure: [{
                    name: 'No.', field: 'count', width: '30px', styles: 'text-align:center;', formatter: formatter.rowCountCellFormatter
                }, {
                    name: nls.location, field: 'description', editable: true, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width: 'auto', formatter: CustomFormatter.treeCellFormatter
                }, {
                    name: nls.updatedAt, field: 'updated_at', styles:'text-align:center;', width:'120px'
                }],
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: "location/getProjectStructureLocationCodes/pid/" + this.rootProject.id
                })
            });

            var toolbar = new dijit.Toolbar({region: "top", style: "outline:none!important;border:none;padding:2px;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.rootProject.id + 'ProjectStructureLocationCodeAddRow-button',
                    label: nls.addRow,
                    iconClass: "icon-16-container icon-16-add",
                    style: "outline:none!important;",
                    disabled: true,
                    onClick: dojo.hitch(grid, "addRow")
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: this.rootProject.id + 'ProjectStructureLocationCodeIndentRow-button',
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
                    id: this.rootProject.id + 'ProjectStructureLocationCodeOutdentRow-button',
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
                    id: this.rootProject.id + 'ProjectStructureLocationCodeDeleteRow-button',
                    label: nls.deleteRow,
                    iconClass: "icon-16-container icon-16-delete",
                    style: "outline:none!important;",
                    disabled: true,
                    onClick: dojo.hitch(grid, "deleteRow")
                })
            );

            var container = new dijit.layout.BorderContainer({
                region: "center",
                rootProject: null,
                style:"border:none;padding:0;margin:0;width:100%;height:100%;",
                gutters: false
            });


            container.addChild(toolbar);
            container.addChild(grid);

            this.addChild(new FilterToolbar({
                region: 'top',
                grid: grid,
                editableGrid: true,
                filterFields:['description']
            }));

            this.addChild(container);
        }
    });
});
