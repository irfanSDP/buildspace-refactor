define('buildspace/apps/ProjectBuilder/BillManager/buildUpFloorAreaDialog',[
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
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    'buildspace/widget/grid/cells/Select',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    "dojo/dom-style",
    "buildspace/widget/grid/cells/Formatter",
    "./buildUpFloorAreaSummary",
    'dojo/i18n!buildspace/nls/BuildUpFloorArea'
], function(declare, lang, array, domAttr, Menu, Selector, Rearrange, FormulatedColumn, evt, keys, currency, focusUtil, xhr, PopupMenuItem, Select, Textarea, FormulaTextBox, when, html, dom, domStyle, GridFormatter, BuildUpFloorAreaSummary, nls){

    var BuildUpFloorAreaGrid = declare('buildspace.apps.ProjectBuilder.BillManager.BuildUpFloorArea.grid', dojox.grid.EnhancedGrid, {
        billColumnSettingId: null,
        style: "border-top:none;",
        selectedItem: null,
        keepSelection: true,
        rowSelector: '0px',
        addUrl: null,
        updateUrl: null,
        deleteUrl: null,
        pasteUrl: null,
        buildUpSummaryWidget: null,
        type: null,
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
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
                }

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

                            self.updateTotalBuildUp(resp.total_build_up);
                            var cell = self.getCellByField(inAttrName);
                            window.setTimeout(function() {
                                self.focus.setFocusIndex(rowIdx, cell.index);
                            }, 10);
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                };

                dojo.xhrPost(xhrArgs);
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
                        //2 - cellIndex2:Description
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

            dojo.xhrPost(xhrArgs);
        },
        cutItems: function(){
            this.selectedItem = this.selection.getFirstSelected();
            this.pasteOp = 'cut';

            var pasteRowBtn = dijit.byId('buildUpFloorAreaGrid-'+this.billColumnSettingId+'_'+'PasteRow-button');
            if(this.selectedItem){
                pasteRowBtn._setDisabledAttr(false);
            }
        },
        copyItems: function(){
            this.selectedItem = this.selection.getFirstSelected();
            this.pasteOp = 'copy';

            var pasteRowBtn = dijit.byId('buildUpFloorAreaGrid-'+this.billColumnSettingId+'_'+'PasteRow-button');
            if(this.selectedItem){
                pasteRowBtn._setDisabledAttr(false);
            }
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
                                    }
                                    var selectRowIndex  = (firstRowIdx > rowIndex) ? rowIndex : rowIndex - 1;
                                    self.selectAfterPaste(selectRowIndex, true);
                                }});
                                break;
                            case 'copy':
                                var item = store.newItem(resp.data);
                                store.save();
                                var firstRowIdx = self.getItemIndex(item);
                                rowsToMove.push(firstRowIdx);
                                if(rowsToMove.length > 0){
                                    self.rearranger.moveRows(rowsToMove, rowIndex);
                                    self.selectAfterPaste(rowIndex, false);
                                }
                                self.updateTotalBuildUp();
                                break;
                            default:
                                break;
                        }
                    }

                    self.pasteOp = null;
                    self.disableToolbarButtons(false);
                    rowsToMove.length = 0;
                    pb.hide();
                },
                error: function(error) {
                    self.selection.clear();
                    self.selectedItem = null;
                    self.pasteOp = null;
                    self.disableToolbarButtons(true);
                    pb.hide();
                }
            };

            dojo.xhrPost(xhrArgs);
        },
        selectAfterPaste: function (rowIndex, scroll)
        {
            var self = this;

            self.selection.clear();
            self.selectedItem = null;
            self.selection.setSelected(rowIndex, true);

            if(scroll)
            {
                self.scrollToRow(((rowIndex - 3) > 0) ? rowIndex - 3 : rowIndex);
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
            var self = this, item = this.getItem(e.rowIndex);
            if(item.id > 0){
                self.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.cut,
                    iconClass:"icon-16-container icon-16-cut",
                    onClick: dojo.hitch(self,'cutItems')
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
            self.rowCtxMenu.addChild(new dijit.MenuItem({
                label: nls.addRow,
                iconClass:"icon-16-container icon-16-add",
                onClick: dojo.hitch(self,'addRow', e.rowIndex)
            }));
            if(item.id > 0){
                self.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(self,'deleteRow', e.rowIndex)
                }));
            }
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            var addRowBtn = dijit.byId('buildUpFloorAreaGrid-'+this.billColumnSettingId+'_'+'AddRow-button');
            var deleteRowBtn = dijit.byId('buildUpFloorAreaGrid-'+this.billColumnSettingId+'_'+'DeleteRow-button');
            var copyRowBtn = dijit.byId('buildUpFloorAreaGrid-'+this.billColumnSettingId+'_'+'CopyRow-button');
            var cutRowBtn = dijit.byId('buildUpFloorAreaGrid-'+this.billColumnSettingId+'_'+'CutRow-button');
            var pasteRowBtn = dijit.byId('buildUpFloorAreaGrid-'+this.billColumnSettingId+'_'+'PasteRow-button');

            addRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);
            copyRowBtn._setDisabledAttr(isDisable);
            cutRowBtn._setDisabledAttr(isDisable);
            if(this.selectedItem && this.selection.selectedIndex > -1){
                pasteRowBtn._setDisabledAttr(false);
            }else{
                pasteRowBtn._setDisabledAttr(true);
            }

            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId('buildUpFloorAreaGrid-'+_this.billColumnSettingId+'_'+label+'Row-button');
                    btn._setDisabledAttr(false);
                })
            }
        },
        updateTotalBuildUp: function(){
            this.buildUpSummaryWidget.refreshTotalFloorArea();
        }
    });

    var BuildUpFloorAreaGridContainer = declare('buildspace.apps.ProjectBuilder.BillManager.BuildUpFloorAreaGrid', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        billColumnSettingId: null,
        type: null,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            lang.mixin(self.gridOpts, { billColumnSettingId: self.billColumnSettingId, region:"center", type: self.type });
            var grid = this.grid = new BuildUpFloorAreaGrid(self.gridOpts);
            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    id: 'buildUpFloorAreaGrid-'+self.billColumnSettingId+'_'+'AddRow-button',
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

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'buildUpFloorAreaGrid-'+self.billColumnSettingId+'_'+'DeleteRow-button',
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

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'buildUpFloorAreaGrid-'+self.billColumnSettingId+'_'+'CutRow-button',
                    label: nls.cut,
                    iconClass: "icon-16-container icon-16-cut",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(){
                        if(grid.selection.selectedIndex > -1){
                             grid.cutItems();
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'buildUpFloorAreaGrid-'+self.billColumnSettingId+'_'+'CopyRow-button',
                    label: nls.copy,
                    iconClass: "icon-16-container icon-16-copy",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(){
                        if(grid.selection.selectedIndex > -1){
                             grid.copyItems();
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'buildUpFloorAreaGrid-'+self.billColumnSettingId+'_'+'PasteRow-button',
                    label: nls.paste,
                    iconClass: "icon-16-container icon-16-paste",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(){
                        if(grid.selection.selectedIndex > -1){
                             grid.pasteItem(grid.selection.selectedIndex);
                        }
                    }
                })
            );

            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });

    return declare('buildspace.apps.ProjectBuilder.BillManager.BuildUpFloorAreaDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.appName,
        billId: 0,
        billColumnSettingId: 0,
        columnSettingForm: null,
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;

            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function(e){
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.columnSettingForm.refreshColumn();
            this.destroyRecursive();
        },
        createContent: function(){
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:900px;height:450px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            var formatter = new GridFormatter(),
                sign = {
                    options: [
                        buildspace.constants.SIGN_POSITIVE_TEXT,
                        buildspace.constants.SIGN_NEGATIVE_TEXT
                    ],
                    values: [
                        buildspace.constants.SIGN_POSITIVE,
                        buildspace.constants.SIGN_NEGATIVE
                    ]
                };

            var structure = [{
                name: 'No',
                field: 'id',
                styles: "text-align:center;",
                width: '40px',
                formatter: formatter.rowCountCellFormatter
            }, {
                name: nls.description,
                field: 'description',
                width: 'auto',
                editable: true,
                cellType: 'buildspace.widget.grid.cells.Textarea'
            },{
                name: nls.factor,
                field: 'factor-value',
                width:'100px',
                styles:'text-align:right;',
                editable:true,
                cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                formatter: formatter.formulaNumberCellFormatter
            },{
                name: nls.length,
                field: 'length-value',
                width:'100px',
                styles:'text-align:right;',
                editable:true,
                cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                formatter: formatter.formulaNumberCellFormatter
            },{
                name: nls.width,
                field: 'width-value',
                width:'100px',
                styles:'text-align:right;',
                editable:true,
                cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                formatter: formatter.formulaNumberCellFormatter
            },{
                name: nls.total,
                field: 'total',
                width:'100px',
                styles:'text-align:right;',
                formatter: formatter.numberCellFormatter
            },{
                name: nls.sign,
                field: 'sign',
                width: '70px',
                styles: 'text-align:center;',
                editable: true,
                cellType: 'dojox.grid.cells.Select',
                options: sign.options,
                values: sign.values,
                formatter: formatter.signCellFormatter
            }];

            var buildUpFloorAreaStore = dojo.data.ItemFileWriteStore({
                url:"billBuildUpFloorArea/getBuildUpFloorAreaItemList/bill_column_setting_id/"+self.billColumnSettingId,
                clearOnClose: true
            });

            var buildUpSummaryWidget = BuildUpFloorAreaSummary({
                billId: self.billId,
                billColumnSettingId: self.billColumnSettingId,
                container: borderContainer,
                columnSettingForm: self.columnSettingForm,
                buildUpGridStore: buildUpFloorAreaStore
            });

            var content = BuildUpFloorAreaGridContainer({
                region: 'center',
                billColumnSettingId: self.billColumnSettingId,
                gridOpts: {
                    addUrl: 'billBuildUpFloorArea/buildUpFloorAreaItemAdd',
                    updateUrl: 'billBuildUpFloorArea/buildUpFloorAreaItemUpdate',
                    deleteUrl: 'billBuildUpFloorArea/buildUpFloorAreaItemDelete',
                    pasteUrl: 'billBuildUpFloorArea/buildUpFloorAreaItemPaste',
                    store: buildUpFloorAreaStore,
                    structure: structure,
                    buildUpSummaryWidget: buildUpSummaryWidget
                }
            });

            var gridContainer = this.makeGridContainer(content,nls.appName);
            borderContainer.addChild(toolbar);
            borderContainer.addChild(buildUpSummaryWidget);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        makeGridContainer: function(content, title){
            var id = this.billId;
            var stackContainer = dijit.byId('billManager-buildUpFloorArea_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('billManager-buildUpFloorArea_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'billManager-buildUpFloorArea_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'billManager-buildUpFloorArea_'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('billManager-buildUpFloorArea_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('billManager-buildUpFloorArea_'+id+'-stackContainer');
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

            return borderContainer;
        }
    });
});