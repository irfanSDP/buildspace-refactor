define("buildspace/apps/StockOut/StockOutDialog/StockOutListingDialog", [
"dojo/_base/declare",
"dojo/aspect",
"dojo/_base/lang",
"dojo/_base/connect",
"dojo/when",
"dojo/html",
"dojo/dom",
"dojo/keys",
"dijit/focus",
"dojo/dom-style",
"dojo/request",
"dojox/layout/ContentPane",
"dijit/form/Button",
"dojox/grid/enhanced/plugins/Rearrange",
"buildspace/widget/grid/plugins/FormulatedColumn",
"buildspace/widget/grid/cells/Formatter",
'buildspace/widget/grid/cells/FormulaTextBox',
'./DeliveryOrderDialog',
'./StockOutFormDialog',
'dojo/i18n!../../../nls/StockOut'],
function(declare, aspect, lang, connect, when_, html, dom, keys, focusUtil, domStyle, request, ContentPane, Button, Rearrange, FormulatedColumn, GridFormatter, FormulaTextBox, DeliveryOrderDialog, StockOutFormDialog, nls) {
    var stockOutUsedQuantityItemGridContainer;
    var stockOutUsedQuantityItemsGrid = declare("buildspace.apps.StockOut.StockOutUsedQuantityItemsGrid", dojox.grid.EnhancedGrid, {
        stockOutUsedQuantity: null,
        type: null,
        dialogWidget: null,
        style: "border-top:none;",
        constructor: function() {
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this, {});
        },
        canSort: function() {
            return false;
        },
        postCreate: function() {
            var self;
            self = this;
            self.inherited(arguments);
            this.on("RowClick", function(e) {
                var item;
                item = self.getItem(e.rowIndex);
                if (item && item.id > 0) {
                    self.disableToolbarButtons(false);
                } else {
                    self.disableToolbarButtons(true);
                }
            });
        },
        canEdit: function(inCell, inRowIndex) {
            var item, self;
            self = this;
            if (inCell !== void 0) {
                item = this.getItem(inRowIndex);
                if (item && parseInt(item.id) > 0 && item.hasOwnProperty('type')) {
                    if (item.type[0] === buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type[0] === buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N) {
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);

                        return false;
                    }
                } else {
                    window.setTimeout(function() {
                        self.edit.cancel();
                        self.focus.setFocusIndex(inRowIndex, inCell.index);
                    }, 10);

                    return false;
                }
            }
            return this._canEdit;
        },
        dodblclick: function(e) {
            this.onRowDblClick(e);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName) {
            var item, params, pb, self;
            self = this;
            item = this.getItem(rowIdx);
            if (val !== item[inAttrName][0]) {
                params = {
                    field_name: inAttrName,
                    stockOutUsedQuantityId: parseInt(self.stockOutUsedQuantity.id),
                    resourceItemId: parseInt(item.id),
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                };
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.savingData + '. ' + nls.pleaseWait + '...'
                });

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'stockOut/updateStockOutQuantityItemInformation',
                        content: params,
                        handleAs: 'json',
                        load: function(resp) {
                            if (resp.success) {
                                dojo.forEach(resp.items, function(node){
                                    self.store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(_itm){
                                        for(var property in node){
                                            if(_itm.hasOwnProperty(property) && property != self.store._getIdentifierAttribute()){
                                                self.store.setValue(_itm, property, node[property]);
                                            }
                                        }
                                    }});
                                });
                                self.store.save();
                            }
                            

                            pb.hide().then(function(){
                                var cell = self.getCellByField(inAttrName);
                                window.setTimeout(function() {
                                    if(focusUtil.curNode){
                                        focusUtil.curNode.blur();//unfocus clicked button
                                        focusUtil.curNode = null;
                                    }
                                    self.focus.setFocusIndex(rowIdx, cell.index);
                                }, 10);
                            });
                        },
                        error: function() {
                            pb.hide();
                        }
                    });
                });
            }
            self.inherited(arguments);
        },
        deleteStockOutRow: function(rowIndex) {
            var item, msg, pb, self, title;
            self = this;
            item = this.getItem(rowIndex);
            pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.deleting + '. ' + nls.pleaseWait + '...'
            });

            focusUtil.curNode.blur();
            focusUtil.curNode = null;

            title = nls.deleteItemDialogBoxTitle;
            msg = nls.deleteItemDialogBoxMsg;

            new buildspace.dialog.confirm(title, msg, 80, 320, function() {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'stockOut/deleteStockOutUsedQuantity',
                        content: {
                            stockOutUsedQuantityId: parseInt(item.id),
                            _csrf_token: item._csrf_token
                        },
                        handleAs: 'json',
                        load: function(data) {
                            if (data.success) {
                                self.refreshGrid();
                                pb.hide();
                                self.selection.clear();
                                self.disableToolbarButtons(true);
    
                                window.setTimeout(function() {
                                    self.focus.setFocusIndex(rowIndex, 0);
                                }, 10);
                            }
                        },
                        error: function() {
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                            pb.hide();
                        }
                    });
                });
            });
        },
        deleteItemsRow: function(rowIndex) {
            var item, msg, pb, self, store, title;
            self = this;
            item = this.getItem(rowIndex);
            store = self.store;
            pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.deleting + '. ' + nls.pleaseWait + '...'
            });
            focusUtil.curNode.blur();
            focusUtil.curNode = null;

            if (item.type[0] === buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type[0] === buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N) {
                title = nls.deleteHeadDialogBoxTitle;
                msg = nls.deleteHeadDialogBoxMsg;
            } else {
                title = nls.deleteItemDialogBoxTitle;
                msg = nls.deleteItemDialogBoxMsg;
            }

            new buildspace.dialog.confirm(title, msg, 80, 320, function() {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'stockOut/deleteStockOutQuantityUsedItems',
                        content: {
                            stockOutUsedQuantityId: parseInt(self.stockOutUsedQuantity.id),
                            resourceItemId: parseInt(item.id),
                            _csrf_token: item._csrf_token
                        },
                        handleAs: 'json',
                        load: function(data) {
                            var handle;
                            if (data.success) {
                                store = self.store;
                                store.save();
                                store.close();
                                handle = aspect.after(self, "_onFetchComplete", function() {
                                    handle.remove();
                                    if (rowIndex > 0) {
                                        this.scrollToRow(rowIndex);
                                        this.focus.setFocusIndex(rowIndex, 0);
                                    }
                                });
                                self.sort();
                                pb.hide();
                                self.selection.clear();
                                self.disableToolbarButtons(true);
    
                                window.setTimeout(function() {
                                    self.focus.setFocusIndex(rowIndex, 0);
                                }, 10);
                            }
                        },
                        error: function() {
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                            pb.hide();
                        }
                    });
                });
            });
        },
        disableToolbarButtons: function(isDisable) {
            var deleteBtn;
            if (this.type === 'tree') {
                deleteBtn = dijit.byId("ImportResourceItemIntoStockOutUsedQuantityItemGrid-Delete-button");
            } else {
                deleteBtn = dijit.byId("StockOutUsedQuantityGrid-Delete-button");
            }

            if(deleteBtn)
                deleteBtn._setDisabledAttr(isDisable);
        },
        onStyleRow: function(e) {
            this.inherited(arguments);

            if(e.node.children[0]){
                if(e.node.children[0].children[0].rows.length >= 2){
                    var elemToHide = e.node.children[0].children[0].rows[1],
                        childElement = e.node.children[0].children[0].rows[0].children;

                    elemToHide.parentNode.removeChild(elemToHide);

                    dojo.forEach(childElement, function(child, i){
                        var rowSpan = dojo.attr(child, 'rowSpan');

                        if(!rowSpan || rowSpan < 2)
                            dojo.attr(child, 'rowSpan', 2);
                    });
                }
            }
        },
        destroy: function() {
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        refreshGrid: function() {
            this.store.save();
            this.store.close();
            this.setStore(this.store);
        }
    });

    stockOutUsedQuantityItemGridContainer = declare("buildspace.apps.StockOut.StockOutUsedQuantityItemsGridContainer", dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        stockOutUsedQuantity: null,
        type: null,
        gridOpts: {},
        postCreate: function() {
            var child, container, grid, node, self, toolbar;
            self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, {
                type: self.type,
                stockOutUsedQuantity: self.stockOutUsedQuantity,
                region: "center"
            });
            grid = new stockOutUsedQuantityItemsGrid(self.gridOpts);
            toolbar = new dijit.Toolbar({
                region: "top",
                style: "padding:2px;border:none;width:100%;"
            });

            if (self.type === "tree") {
                toolbar.addChild(new dijit.form.Button({
                    id: "ImportResourceItemIntoStockOutUsedQuantityItemGrid-Import-button",
                    label: nls.copyFromDeliveryOrder,
                    iconClass: "icon-16-container icon-16-import",
                    onClick: function() {
                        var dialog = new DeliveryOrderDialog({
                            title: nls.copyFromDeliveryOrder,
                            project: self.project,
                            stockOutUsedQuantity: self.stockOutUsedQuantity,
                            itemListGrid: grid
                        });

                        dialog.show();
                    }
                }));
                toolbar.addChild(new dijit.ToolbarSeparator);
                toolbar.addChild(new Button({
                    label: nls["delete"],
                    id: 'ImportResourceItemIntoStockOutUsedQuantityItemGrid-Delete-button',
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: true,
                    onClick: function() {
                        grid.deleteItemsRow(grid.selection.selectedIndex);
                    }
                }));
            } else {
                toolbar.addChild(new dijit.form.Button({
                    label: nls.addNewStockOut,
                    iconClass: "icon-16-container icon-16-add",
                    onClick: function() {
                        var pb = buildspace.dialog.indeterminateProgressBar({
                            title: nls.pleaseWait + '...'
                        });

                        pb.show().then(function(){
                            request("stockOut/getStockOutFormInformation/projectId/" + parseInt(self.project.id), {
                                handleAs: 'json'
                            }).then(function(response) {
                                var dialog;
                                dialog = new StockOutFormDialog({
                                    project: self.project,
                                    formInfo: response.form,
                                    stockOutGrid: grid
                                });

                                dialog.show();
                                pb.hide();
                            }, function(error) {
                                //console.log(error);
                                pb.hide();
                            });
                        });
                    }
                }));
                toolbar.addChild(new dijit.ToolbarSeparator);
                toolbar.addChild(new Button({
                    label: nls["delete"],
                    id: 'StockOutUsedQuantityGrid-Delete-button',
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: true,
                    onClick: function() {
                        grid.deleteStockOutRow(grid.selection.selectedIndex);
                    }
                }));
            }
            self.addChild(toolbar);
            self.addChild(grid);
            container = dijit.byId("stockOutUsedQuantityItems-stackContainer");
            if (container) {
                node = document.createElement("div");
                child = new ContentPane({
                    title: buildspace.truncateString(self.title, 60),
                    executeScripts: true
                }, node);
                container.addChild(child);
                child.set("content", self);
                container.selectChild(child);
            }
        }
    });

    return declare("buildspace.apps.StockOut.ManageStockOutDialog", dijit.Dialog, {
        project: null,
        style: "padding:0px;margin:0px;",
        buildRendering: function() {
            var content = this.createContent();
            content.startup();
            this.content = content;
            this.inherited(arguments);
        },
        postCreate: function() {
            domStyle.set(this.containerNode, {
                padding: "0px",
                margin: "0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function(e) {
            var key = e.keyCode;
            if (key === keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createContent: function() {
            var borderContainer, content, formatter, gridContainer, self, store, toolbar;
            self = this;
            borderContainer = new dijit.layout.BorderContainer({
                style: "padding:0px;width:1020px;height:480px;",
                gutters: false
            });
            toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });
            toolbar.addChild(new dijit.form.Button({
                id: "manageStockOutCloseBtn",
                label: nls.close,
                iconClass: "icon-16-container icon-16-close",
                style: "outline:none!important;",
                onClick: dojo.hitch(this, "hide")
            }));

            formatter = new GridFormatter;
            store = dojo.data.ItemFileWriteStore({
                url: "stockOut/getStockOutListings/projectId/" + parseInt(this.project.id),
                urlPreventCache: true,
                clearOnClose: true
            });
            content = new stockOutUsedQuantityItemGridContainer({
                project: self.project,
                gridOpts: {
                    store: store,
                    structure: [{
                        name: "No.",
                        field: "id",
                        width: "30px",
                        styles: "text-align:center;",
                        formatter: formatter.rowCountCellFormatter
                    }, {
                        name: nls.runningNumber,
                        field: "running_number",
                        width: "auto"
                    }, {
                        name: nls.created_by,
                        field: 'created_by',
                        styles: "text-align:center;",
                        width: '140px',
                        noresize: true
                    }, {
                        name: nls.stockOutDate,
                        field: "stock_out_date",
                        styles: "text-align:center;",
                        width: '160px',
                        noresize: true
                    }],
                    onRowDblClick: function(e) {
                        var _item, _this;
                        _this = this;
                        _item = _this.getItem(e.rowIndex);
                        if (parseInt(_item.id) > 0) {
                            self.createItemGrid(_item);
                        }
                    }
                }
            });
            gridContainer = this.makeGridContainer(content);
            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        createItemGrid: function(stockOutUsedQuantity) {
            var formatter, self, store;
            self = this;
            formatter = new GridFormatter();
            store = new dojo.data.ItemFileWriteStore({
                url: "stockOut/getStockOutResourceItemList/stockOutUsedQuantityId/" + parseInt(stockOutUsedQuantity.id),
                urlPreventCache: true,
                clearOnClose: true
            });
            return new stockOutUsedQuantityItemGridContainer({
                title: stockOutUsedQuantity.running_number,
                type: "tree",
                project: self.project,
                stockOutUsedQuantity: stockOutUsedQuantity,
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    structure: [{
                        name: "No.",
                        field: "id",
                        width: "30px",
                        styles: "text-align:center;",
                        formatter: formatter.rowCountCellFormatter
                    },{
                        name: nls.description,
                        field: "description",
                        width: "auto",
                        formatter: formatter.treeCellFormatter
                    },{
                        name: nls.type,
                        field: "type",
                        width: "80px",
                        styles: "text-align:center;",
                        formatter: formatter.typeCellFormatter
                    },{
                        name: nls.unit,
                        field: "uom_id",
                        width: "70px",
                        styles: "text-align:center;",
                        formatter: formatter.unitIdCellFormatter
                    },{
                        name: nls.availableDOQuantity,
                        field: 'available_quantity',
                        styles: "text-align:right;color:blue;",
                        width: '90px',
                        formatter: formatter.unEditableCurrencyCellFormatter,
                        noresize: true
                    },{
                        name: nls.stockOutQuantity,
                        field: "quantity",
                        width: '90px',
                        styles: 'text-align: right;',
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        editable: true,
                        formatter: formatter.rfqQuantityCellFormatter,
                        noresize: true
                    }]
                }
            });
        },
        makeGridContainer: function(content) {
            var borderContainer, controller, controllerPane, stackContainer, stackPane;
            stackContainer = dijit.byId("stockOutUsedQuantityItems-stackContainer");
            if (stackContainer) {
                dijit.byId("stockOutUsedQuantityItems-stackContainer").destroyRecursive();
            }
            stackContainer = new dijit.layout.StackContainer({
                style: "width:100%;height:100%;border:0px;",
                region: "center",
                id: "stockOutUsedQuantityItems-stackContainer"
            });
            stackPane = new dijit.layout.ContentPane({
                title: nls.stockOut,
                content: content
            });
            stackContainer.addChild(stackPane);
            controller = new dijit.layout.StackController({
                region: "top",
                containerId: "stockOutUsedQuantityItems-stackContainer"
            });
            controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                "class": "breadCrumbTrail",
                region: "top",
                content: controller
            });
            borderContainer = new dijit.layout.BorderContainer({
                style: "padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: "center"
            });
            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);
            dojo.subscribe("stockOutUsedQuantityItems-stackContainer-selectChild", "", function(page) {
                var children, index, widget, _results;
                widget = dijit.byId("stockOutUsedQuantityItems-stackContainer");
                if (widget) {
                    children = widget.getChildren();
                    index = dojo.indexOf(children, dijit.byId(page.id));
                    index = index + 1;
                    _results = [];
                    while (children.length > index) {
                        widget.removeChild(children[index]);
                        children[index].destroyRecursive(true);
                        _results.push(index = index + 1);
                    }
                    return _results;
                }
            });
            return borderContainer;
        }
    });
});
