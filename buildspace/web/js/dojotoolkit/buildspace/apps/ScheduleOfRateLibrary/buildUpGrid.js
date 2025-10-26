define('buildspace/apps/ScheduleOfRateLibrary/buildUpGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    'dojo/_base/connect',
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
    'dijit/popup',
    'dijit/TooltipDialog',
    './importResourceDialog',
    './importScheduleOfRateDialog',
    'dojo/i18n!buildspace/nls/BuildUpGrid'
], function(declare, lang, array, domAttr, connect, Menu, Selector, Rearrange, FormulatedColumn, evt, keys, currency, focusUtil, html, xhr, PopupMenuItem, Select, Textarea, FormulaTextBox, popup, TooltipDialog, ImportResourceDialog, ImportScheduleOfRateDialog, nls ){

    var BuildUpGrid = declare('buildspace.apps.ScheduleOfRateLibrary.BuildUp.grid', dojox.grid.EnhancedGrid, {
        itemId: -1,
        resource: null,
        scheduleOfRateItemId: 0,
        style: "border-top:none;",
        selectedItem: null,
        unitObj: null,
        keepSelection: true,
        rowSelector: '0px',
        addUrl: null,
        updateUrl: null,
        deleteUrl: null,
        pasteUrl: null,
        buildUpSummaryWidget: null,
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            var tooltipDialog = null;

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
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;
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
                        relation_id: item.relation_id,
                        resource_id: self.resource.id
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

                        pb.hide();
                    },
                    error: function(error) {
                        console.log(error);
                        pb.hide();
                    }
                };

                dojo.xhrPost(xhrArgs);
            }
            self.inherited(arguments);
        },
        dodblclick: function(e){
            //this.onRowDblClick(e);
        },
        onCellFocus: function(inCell, inRowIndex) {
            var self = this, item = self.getItem(inRowIndex), fieldName = inCell.field;

            if ( fieldName !== 'description' || item.id[0] < 0 || ! item.linked[0] ) {
                self.closeToolTipDialogIfAvailable();
                return;
            }

            // will call the api to get current's item parent root information
            var xhrArgs = {
                url: 'resourceLibrary/getBuildUpRatesToolTipInformation',
                content: {
                    id: item.id,
                    type: 'sor'
                },
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success) {
                        self.closeToolTipDialogIfAvailable();

                        var needTable = false,
                            toolTipContent = '<h1 style="font-size: 12px; text-shadow: 1px 1px 1px #000;background-color: rgba(35, 35, 35, .85); color: #fff; padding: 4px 10px;">'+resp.items[0].description+'</h1>';

                        if (Object.keys(resp.items).length > 1) {
                            needTable = true;

                            toolTipContent += '<table style="width: 100%; border-collapse: collapse;">';
                        }

                        dojo.forEach(resp.items, function(item, i){
                            if (i !== 0) {
                                var paddingLeftAmt = item.level * 16;

                                toolTipContent += '<tr><td style="border: 1px dotted #D5CDB5; font-weight: bold; font-size: 11px; padding-left:'+paddingLeftAmt+'px;">'+item.description+'</td></td>';
                            }
                        });

                        if (needTable) {
                            toolTipContent += '</table>';
                        }

                        // create a new tooltip based on the information returned from the api
                        self.myTooltipDialog = new TooltipDialog({
                            id: 'buildUpRatesTooltipDialog-'+self.itemId[0],
                            style: "width: 300px;",
                            content: toolTipContent
                        });

                        popup.open({
                            popup: self.myTooltipDialog,
                            around: inCell.view.rowNodes[inRowIndex],
                            orient: ['below']
                        });
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            };

            dojo.xhrGet(xhrArgs);
        },
        onRowMouseOut: function() {
            this.closeToolTipDialogIfAvailable();
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
                var content = { before_id: itemBefore.id, _csrf_token:itemBefore._csrf_token };
            }else{
                var prevItemId = (rowIndex > 0) ? self.getItem(rowIndex-1).id : 0;
                var content = { id: itemBefore.id, prev_item_id: prevItemId, relation_id: itemBefore.relation_id, resource_id: self.resource.id, _csrf_token:itemBefore._csrf_token }
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
                        self.updateTotalBuildUp(data.total_build_up);
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

            new buildspace.dialog.confirm(nls.deleteBuildUpRateItemTitle, nls.deleteBuildUpRateItemMsg, 80, 320, function() {
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
                                self.updateTotalBuildUp(resp.total_build_up);
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
            var addRowBtn = dijit.byId('builUpGrid-'+this.resource.id+'_'+this.scheduleOfRateItemId+'AddRow-button');
            var deleteRowBtn = dijit.byId('builUpGrid-'+this.resource.id+'_'+this.scheduleOfRateItemId+'DeleteRow-button');

            addRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId('builUpGrid-'+_this.resource.id+'_'+_this.scheduleOfRateItemId+label+'Row-button');
                    btn._setDisabledAttr(false);
                })
            }
        },
        updateTotalBuildUp: function(totalBuildUp){
            var accContainer = dijit.byId('accPane-'+this.resource.id+'-'+this.scheduleOfRateItemId);
            accContainer.set('title', this.resource.name+'<span style="color:blue;float:right;">'+buildspace.currencyAbbreviation+'&nbsp;'+currency.format(totalBuildUp)+'</span>');
            this.buildUpSummaryWidget.refreshTotalCost();
        },
        closeToolTipDialogIfAvailable: function() {
            var myTooltipDialogContainer = dijit.byId('buildUpRatesTooltipDialog-'+this.itemId[0]);

            // close the opened dialog box, if any
            if (this.myTooltipDialog !== undefined && myTooltipDialogContainer !== undefined) {
                popup.close();

                myTooltipDialogContainer.destroy();
            }
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.ScheduleOfRateLibrary.BuildUpGrid', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        resource: null,
        scheduleOfRateItemId: 0,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { resource: self.resource, scheduleOfRateItemId: self.scheduleOfRateItemId, region:"center" });
            var grid = this.grid = new BuildUpGrid(self.gridOpts);
            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'builUpGrid-'+self.resource.id+'_'+self.scheduleOfRateItemId+'AddRow-button',
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
                    id: 'builUpGrid-'+self.resource.id+'_'+self.scheduleOfRateItemId+'DeleteRow-button',
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
                    label: nls.importFromResourceLibrary,
                    iconClass: "icon-16-container icon-16-import",
                    onClick: function(){
                        var importDialog = ImportResourceDialog({
                            itemId: self.scheduleOfRateItemId,
                            resource: self.resource,
                            buildUpGridStore: grid.store
                        });
                        importDialog.show();
                    }
                })
            );
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.importFromScheduleOfRate,
                    iconClass: "icon-16-container icon-16-import",
                    onClick: function(){
                        var importDialog = new ImportScheduleOfRateDialog({
                            itemId: self.scheduleOfRateItemId,
                            resource: self.resource,
                            buildUpGridStore: grid.store,
                            buildUpSummaryWidget: self.gridOpts.buildUpSummaryWidget
                        });
                        importDialog.show();
                    }
                })
            );

            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });
});