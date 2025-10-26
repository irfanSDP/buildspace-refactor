define("buildspace/apps/StockOut/StockOutDialog/DeliveryOrderDialog",[
"dojo/_base/declare",
"dojo/_base/lang",
"dojo/_base/connect",
"dojo/keys",
"dojo/dom-style",
"dojox/layout/ContentPane",
"buildspace/widget/grid/cells/Formatter",
"dojox/grid/enhanced/plugins/IndirectSelection",
'dojo/i18n!../../../nls/StockOut'],
function(declare, lang, connect, keys, domStyle, ContentPane, GridFormatter, IndirectSelection, nls) {
    var importGridContainer;
    var importResourceGrid = declare("buildspace.apps.StockOut.ImportResourceGrid", dojox.grid.EnhancedGrid, {
        type: null,
        dialogWidget: null,
        _csrf_token: null,
        resource: null,
        itemIds: [],
        style: "border-top:none;",
        stockOutUsedQuantity: null,
        deliveryOrder: null,
        itemListGrid: null,
        constructor: function(args) {
            this.itemIds = [];
            this.connects = [];
            if (args.type === "tree") {
                this.urlGetDescendantIds = "bqLibrary/getResourceDescendantsForImport";
                this.plugins = {
                        indirectSelection: {
                        headerSelector: true,
                        width: "20px",
                        styles: "text-align: center;"
                    }
                };
            }
            this.inherited(arguments);
        },
        canSort: function(inSortInfo) {
            return false;
        },
        postCreate: function() {
            var self;
            self = this;
            self.inherited(arguments);
            this.on("RowClick", function(e) {
                var item;
                item = self.getItem(e.rowIndex);
                if (self.type === "tree") {
                    if (item && parseInt(item.id) > 0) {
                        self.disableToolbarButtons(false);
                    } else {
                        self.disableToolbarButtons(true);
                    }
                }
            });
            if (this.type === "tree") {
                this._connects.push(connect.connect(this, "onCellClick", function(e) {
                    self.selectTree(e);
                }));
                this._connects.push(connect.connect(this.rowSelectCell, "toggleAllSelection", function(newValue) {
                    self.toggleAllSelection(newValue);
                }));
            }
        },
        dodblclick: function(e) {
            this.onRowDblClick(e);
        },
        selectTree: function(e) {
            var item, itemIndex, newValue, pb, rowIndex, self, store, xhrArgs;
            rowIndex = e.rowIndex;
            newValue = this.selection.selected[rowIndex];
            item = this.getItem(rowIndex);
            self = this;
            store = this.store;
            if (item.type[0] === buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER) {
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait + "..."
                });

                itemIndex = -1;
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: self.urlGetDescendantIds,
                        content: {
                            id: item.id
                        },
                        handleAs: "json",
                        load: function(data) {
                            dojo.forEach(data.items, function(itm) {
                                store.fetchItemByIdentity({
                                    identity: itm.id,
                                    onItem: function(node) {
                                        if (node) {
                                            itemIndex = node._0;
                                            self.pushItemIdIntoGridArray(node, newValue);
                                            self.selection[(newValue ? "addToSelection" : "deselect")](itemIndex);
                                        }
                                    }
                                });
                            });

                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            } else {
                if ((item.type != null) && item.type[0] === buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM) {
                    self.pushItemIdIntoGridArray(item, newValue);
                }
            }
        },
        pushItemIdIntoGridArray: function(item, select) {
            var grid, idx;
            grid = this;
            idx = dojo.indexOf(grid.itemIds, parseInt(item.id));
            if (select) {
                if (idx === -1) {
                    grid.itemIds.push(parseInt(item.id));
                }
            } else {
                if (idx !== -1) {
                    grid.itemIds.splice(idx, 1);
                }
            }
        },
        toggleAllSelection: function(checked) {
            var grid, selection, self;
            grid = this;
            self = this;
            selection = grid.selection;
            if (checked) {
                selection.selectRange(0, grid.rowCount - 1);
                grid.itemIds = [];
                return grid.store.fetch({
                    onComplete: function(items) {
                        return dojo.forEach(items, function(item, index) {
                            if (parseInt(item.id) > 0) {
                                grid.itemIds.push(parseInt(item.id));
                                self.disableToolbarButtons(false);
                            }
                        });
                    }
                });
            } else {
                selection.deselectAll();
                grid.itemIds = [];
            }
        },
        disableToolbarButtons: function(isDisable) {
            var importBtn = dijit.byId("ImportResourceGrid-Import-button");
            if(importBtn)
                importBtn._setDisabledAttr(isDisable);
        },
        copy: function() {
            var itemIds, pb, self, xhrArgs;
            self = this;
            itemIds = self.itemIds;
            if (itemIds.length > 0) {
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.importingItems + ". " + nls.pleaseWait + "..."
                });

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: "stockOut/copyDeliveryOrderItems",
                        content: {
                            stockOutUsedQuantityId: parseInt(self.stockOutUsedQuantity.id),
                            deliveryOrderId: parseInt(self.deliveryOrder.id),
                            ids: [itemIds],
                            _csrf_token: self._csrf_token
                        },
                        handleAs: "json",
                        load: function(data) {
                            pb.hide();
                            if (data.success) {
                                self.itemIds = [];
                                self.itemListGrid.refreshGrid();
                                self.dialogWidget.hide();
                            } else {
                                self.itemIds = [];
                                self.dialogWidget.hide();
                            }
                        },
                        error: function(error) {
                            self.itemIds = [];
                            pb.hide();
                            self.dialogWidget.hide();
                        }
                    });
                });
            }
        },
        destroy: function() {
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });
    importGridContainer = declare("buildspace.apps.StockOut.ImportResourceGridContainer", dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        stockOutUsedQuantity: null,
        type: null,
        gridOpts: {},
        postCreate: function() {
            var child, container, grid, node, toolbar;
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {
                type: this.type,
                stockOutUsedQuantity: this.stockOutUsedQuantity,
                region: "center"
            });
            grid = new importResourceGrid(this.gridOpts);
            if (this.type === "tree") {
                toolbar = new dijit.Toolbar({
                    region: "top",
                    style: "padding:2px;border:none;width:100%;"
                });
                toolbar.addChild(new dijit.form.Button({
                    id: "ImportResourceGrid-Import-button",
                    label: nls.copy,
                    iconClass: "icon-16-container icon-16-import",
                    disabled: true,
                    onClick: function() {
                        grid.copy();
                    }
                }));
                this.addChild(toolbar);
            }
            this.addChild(grid);
            container = dijit.byId("stockOutUsedQuantityFromResourceLibrary-stackContainer");
            if (container) {
                node = document.createElement("div");
                child = new ContentPane({
                    title: buildspace.truncateString(this.title, 60),
                    executeScripts: true
                }, node);
                container.addChild(child);
                child.set("content", this);
                container.selectChild(child);
            }
        }
    });

    return declare("buildspace.apps.StockOut.ImportResourceDialog", dijit.Dialog, {
        style: "padding:0px;margin:0px;",
        project: null,
        stockOutUsedQuantity: null,
        itemListGrid: null,
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
                style: "padding:0px;width:900px;height:400px;",
                gutters: false
            });
            toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });
            toolbar.addChild(new dijit.form.Button({
                label: nls.close,
                iconClass: "icon-16-container icon-16-close",
                style: "outline:none!important;",
                onClick: dojo.hitch(this, "hide")
            }));
            formatter = new GridFormatter();
            store = dojo.data.ItemFileWriteStore({
                url: "stockOut/getResourceWithStockInsByProject/projectId/" + parseInt(self.project.id)
            });
            content = new importGridContainer({
                stockOutUsedQuantity: self.stockOutUsedQuantity,
                gridOpts: {
                    store: store,
                    itemListGrid: self.itemListGrid,
                    structure: [{
                        name: "No",
                        field: "id",
                        width: '30px',
                        styles: 'text-align: center;',
                        formatter: formatter.rowCountCellFormatter,
                        noresize: true
                    }, {
                        name: nls.description,
                        field: "name",
                        width: 'auto',
                        formatter: formatter.treeCellFormatter,
                        noresize: true
                    }],
                    onRowDblClick: function(e) {
                        var _item, _this;
                        _this = this;
                        _item = _this.getItem(e.rowIndex);
                        if (parseInt(_item.id) > 0) {
                            self.createResourceTradeGrid(_item);
                        }
                    }
                }
            });
            gridContainer = this.makeGridContainer(content);
            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);
            return borderContainer;
        },
        createResourceTradeGrid: function(resource) {
            var formatter, self, store;
            self = this;
            formatter = new GridFormatter();
            store = new dojo.data.ItemFileWriteStore({
                url: "stockOut/getResourceTradeWithStockInsByProject/projectId/" + parseInt(self.project.id) + "/resourceId/" + parseInt(resource.id)
            });

            return new importGridContainer({
                title: resource.name,
                stockOutUsedQuantity: self.stockOutUsedQuantity,
                gridOpts: {
                    store: store,
                    itemListGrid: self.itemListGrid,
                    dialogWidget: self,
                    structure: [{
                        name: "No",
                        field: "id",
                        width: '30px',
                        styles: 'text-align: center;',
                        formatter: formatter.rowCountCellFormatter,
                        noresize: true
                    }, {
                        name: nls.description,
                        field: "description",
                        width: 'auto',
                        formatter: formatter.treeCellFormatter,
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
        },
        createItemGrid: function(resourceTrade) {
            var formatter, self, store;
            self = this;
            formatter = new GridFormatter();
            store = new dojo.data.ItemFileWriteStore({
                url: "stockOut/getCopyItemListingsWithDeliveryOrder/stockOutUsedQuantityId/" + parseInt(self.stockOutUsedQuantity.id) + "/resourceTradeId/" + parseInt(resourceTrade.id)
            });

            return new importGridContainer({
                title: resourceTrade.description[0],
                stockOutUsedQuantity: self.stockOutUsedQuantity,
                type: "tree",
                gridOpts: {
                    deliveryOrder: resourceTrade,
                    store: store,
                    itemListGrid: self.itemListGrid,
                    dialogWidget: self,
                    _csrf_token: resourceTrade._csrf_token,
                    structure: [{
                        name: "No",
                        field: "id",
                        width: '30px',
                        styles: 'text-align: center;',
                        formatter: formatter.rowCountCellFormatter,
                        noresize: true
                    }, {
                        name: nls.description,
                        field: "description",
                        width: 'auto',
                        formatter: formatter.treeCellFormatter,
                        noresize: true
                    }, {
                        name: nls.unit,
                        field: "uom_id",
                        styles: 'text-align: center;',
                        width: '70px',
                        formatter: formatter.unitIdCellFormatter,
                        noresize: true
                    }, {
                        name: nls.doQuantity,
                        field: 'do_quantity',
                        styles: "text-align:right;color:blue;",
                        width: '90px',
                        formatter: formatter.unEditableCurrencyCellFormatter,
                        noresize: true
                    },{
                        name: nls.stockOutQuantity,
                        field: 'stock_out_quantity',
                        styles: "text-align:right;color:blue;",
                        width: '90px',
                        formatter: formatter.unEditableCurrencyCellFormatter,
                        noresize: true
                    },{
                        name: nls.availableDOQuantity,
                        field: 'balance_quantity',
                        styles: "text-align:right;color:blue;",
                        width: '90px',
                        formatter: formatter.unEditableCurrencyCellFormatter,
                        noresize: true
                    }]
                }
            });
        },
        makeGridContainer: function(content) {
            var borderContainer, controller, controllerPane, stackContainer, stackPane;
            stackContainer = dijit.byId("stockOutUsedQuantityFromResourceLibrary-stackContainer");
            if (stackContainer) {
                dijit.byId("stockOutUsedQuantityFromResourceLibrary-stackContainer").destroyRecursive();
            }
            stackContainer = new dijit.layout.StackContainer({
                style: "width:100%;height:100%;border:0px;",
                region: "center",
                id: "stockOutUsedQuantityFromResourceLibrary-stackContainer"
            });
            stackPane = new dijit.layout.ContentPane({
                title: nls.resource,
                content: content
            });
            stackContainer.addChild(stackPane);
            controller = new dijit.layout.StackController({
                region: "top",
                containerId: "stockOutUsedQuantityFromResourceLibrary-stackContainer"
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
            dojo.subscribe("stockOutUsedQuantityFromResourceLibrary-stackContainer-selectChild", "", function(page) {
                var children, index, widget, _results;
                widget = dijit.byId("stockOutUsedQuantityFromResourceLibrary-stackContainer");
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
