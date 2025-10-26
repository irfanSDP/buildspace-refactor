define('buildspace/apps/PostContractReport/MaterialOnSiteReport', [
        'dojo/_base/declare',
        'dojo/aspect',
        'dojo/currency',
        'dojo/number',
        "dojo/when",
        'dojo/request',
        'dojo/store/Memory',
        "dijit/layout/ContentPane",
        "dijit/layout/TabContainer",
        "dijit/layout/BorderContainer",
        "./MaterialOnSiteReport/MaterialOnSiteGrid",
        "./MaterialOnSiteReport/PrintSettingForm",
        "buildspace/widget/grid/cells/Formatter",
        'dojo/i18n!buildspace/nls/PostContract'],
    function (declare, aspect, currency, number, when, request, Memory, ContentPane, TabContainer, BorderContainer, MaterialOnSiteGrid, PrintSettingForm, GridFormatter, nls) {

        var CustomFormatter = {
            statusCellFormatter: function (cellValue, rowIdx, cell) {
                var item = this.grid.getItem(rowIdx);

                if (item.id == buildspace.constants.GRID_LAST_ROW) {
                    cell.customClasses.push('disable-cell');
                    return "&nbsp;";
                }

                if (cellValue == buildspace.apps.PostContractReport.ProjectStructureConstants.MATERIAL_ON_SITE_CLAIMED) {
                    cell.customClasses.push('green-cell');
                    return nls.claimed.toUpperCase();
                } else {
                    cell.customClasses.push('yellow-cell');
                    return nls.thisClaim.toUpperCase();
                }
            },
            mosTotalCellFormatter: function (cellValue, rowIdx, cell) {
                var item = this.grid.getItem(rowIdx);

                if (item.id == buildspace.constants.GRID_LAST_ROW) {
                    cell.customClasses.push('disable-cell');
                    return "&nbsp;";
                }

                cell.customClasses.push('disable-cell');
                var value = number.parse(cellValue);

                if (value < 0) {
                    return '<span style="color:#FF0000">' + currency.format(value) + '</span>';
                } else {
                    return value == 0 ? "&nbsp;" : '<span style="color:#42b449;">' + currency.format(value) + '</span>';
                }
            },
            currencyCellFormatter: function (cellValue, rowIdx, cell) {
                var value = number.parse(cellValue),
                    item = this.grid.getItem(rowIdx);

                if (isNaN(value) || value == 0 || value == null) {
                    cellValue = "&nbsp;";
                } else {
                    cellValue = currency.format(value);
                    cellValue = value >= 0 ? cellValue : '<span style="color:#FF0000">' + cellValue + '</span>';
                }

                if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                    cell.customClasses.push('disable-cell');
                }

                return cellValue;
            },
            qtyCellFormatter: function (cellValue, rowIdx, cell) {
                var item = this.grid.getItem(rowIdx),
                    value = number.parse(cellValue),
                    formattedValue = "&nbsp;";

                if (!isNaN(value) && value != 0 && value != null) {
                    formattedValue = number.format(value, {places: 2});
                }

                if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                    cell.customClasses.push('disable-cell');
                }

                return value >= 0 ? formattedValue : '<span style="color:#FF0000">' + formattedValue + '</span>';
            }
        };

        var materialOnSiteContainer = declare('buildspace.apps.PostContractReport.MaterialOnSiteReport', BorderContainer, {
            style: "padding:0;margin:0;border:none;width:100%;height:100%;",
            region: "center",
            gutters: false,
            rootProject: null,
            mosSelectedStore: [],
            mosItemSelectedStore: [],
            postCreate: function () {
                this.inherited(arguments);

                this.mosSelectedStore = new Memory({idProperty: 'id'});
                this.mosItemSelectedStore = new Memory({idProperty: 'id'});

                var self = this,
                    formatter = new GridFormatter(),
                    store = dojo.data.ItemFileWriteStore({
                        url: "materialOnSite/getMaterialOnSiteList/pid/" + self.rootProject.id,
                        clearOnClose: true
                    }),
                    grid = new MaterialOnSiteGrid({
                        id: 'material_on_site-' + self.rootProject.id,
                        stackContainerTitle: nls.scheduleOfRates,
                        pageId: 'material_on_site-' + self.rootProject.id,
                        project: self.rootProject,
                        type: 'vo',
                        gridOpts: {
                            gridContainer: self,
                            store: store,
                            structure: [
                                {
                                    name: 'No.',
                                    field: 'id',
                                    width: '30px',
                                    styles: 'text-align:center;',
                                    formatter: formatter.rowCountCellFormatter
                                },
                                {
                                    name: nls.description,
                                    field: 'description',
                                    width: 'auto',
                                    cellType: 'buildspace.widget.grid.cells.Textarea'
                                },
                                {
                                    name: nls.reductionPercentage,
                                    cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                                    field: 'reduction_percentage',
                                    width: '150px',
                                    styles: 'text-align:right;',
                                    formatter: formatter.editableClaimPercentageCellFormatter
                                },
                                {
                                    name: nls.totalMos,
                                    field: 'total',
                                    width: '150px',
                                    styles: 'text-align:right;',
                                    formatter: CustomFormatter.mosTotalCellFormatter
                                },
                                {
                                    name: nls.totalMosAfterReduction,
                                    field: 'total_after_reduction',
                                    width: '150px',
                                    styles: 'text-align:right;',
                                    formatter: CustomFormatter.mosTotalCellFormatter
                                },
                                {
                                    name: nls.status,
                                    field: 'status',
                                    width: '80px',
                                    styles: 'text-align:center;',
                                    formatter: CustomFormatter.statusCellFormatter
                                },
                                {
                                    name: nls.lastUpdated,
                                    field: 'updated_at',
                                    width: '120px',
                                    styles: 'text-align: center;'
                                }
                            ],
                            onRowDblClick: function (e) {
                                var _this = this, _item = _this.getItem(e.rowIndex);

                                if (_item.id > 0 && _item.description[0] !== null && _item.description[0].length > 0) {
                                    self.makeItemGrid(_item);
                                }
                            },
                            singleCheckBoxSelection: function (e) {
                                var self = this,
                                    rowIndex = e.rowIndex,
                                    checked = this.selection.selected[rowIndex],
                                    item = this.getItem(rowIndex);

                                // used to store removeable selection
                                self.removedIds = [];

                                if (checked) {
                                    self.gridContainer.mosSelectedStore.put({id: item.id[0]});

                                    return self.getAffectedItemsByVO(item, 'add');
                                } else {
                                    self.gridContainer.mosSelectedStore.remove(item.id[0]);

                                    self.removedIds.push(item.id[0]);

                                    return self.getAffectedItemsByVO(item, 'remove');
                                }
                            },
                            toggleAllSelection: function (checked) {
                                var self = this, selection = this.selection;

                                // used to store removeable selection
                                self.removedIds = [];

                                if (checked) {
                                    selection.selectRange(0, self.rowCount - 1);
                                    self.store.fetch({
                                        onComplete: function (items) {
                                            dojo.forEach(items, function (item) {
                                                if (item.id > 0) {
                                                    self.gridContainer.mosSelectedStore.put({id: item.id[0]});
                                                }
                                            });
                                        }
                                    });

                                    return self.getAffectedItemsByVO(null, 'add');
                                } else {
                                    selection.deselectAll();

                                    self.store.fetch({
                                        onComplete: function (items) {
                                            dojo.forEach(items, function (item, index) {
                                                if (item.id > 0) {
                                                    self.gridContainer.mosSelectedStore.remove(item.id[0]);

                                                    self.removedIds.push(item.id[0]);
                                                }
                                            });
                                        }
                                    });

                                    return self.getAffectedItemsByVO(null, 'remove');
                                }
                            },
                            getAffectedItemsByVO: function (item, type) {
                                var self = this,
                                    vos = [];

                                var pb = buildspace.dialog.indeterminateProgressBar({
                                    title: nls.pleaseWait + '...'
                                });

                                pb.show();

                                if (type === 'add') {
                                    self.gridContainer.mosSelectedStore.query().forEach(function (item) {
                                        vos.push(item.id);
                                    });
                                } else {
                                    for (var voKeyIndex in self.removedIds) {
                                        vos.push(self.removedIds[voKeyIndex]);
                                    }
                                }

                                request.post('materialOnSiteReporting/getAffectedItems', {
                                    handleAs: 'json',
                                    data: {
                                        pid: self.project.id,
                                        mos_ids: JSON.stringify(self.gridContainer.arrayUnique(vos))
                                    }
                                }).then(function (data) {
                                    if (type === 'add') {
                                        for (var mosId in data) {
                                            for (var itemIdIndex in data[mosId]) {
                                                self.gridContainer.mosItemSelectedStore.put({id: data[mosId][itemIdIndex]});
                                            }
                                        }
                                    } else {
                                        for (var mosId in data) {
                                            for (var itemIdIndex in data[mosId]) {
                                                self.gridContainer.mosItemSelectedStore.remove(data[mosId][itemIdIndex]);
                                            }

                                            // remove checked type selection if no item is selected in the current VO
                                            self.store.fetchItemByIdentity({
                                                identity: mosId,
                                                onItem: function (node) {
                                                    if (!node) {
                                                        return;
                                                    }

                                                    self.gridContainer.mosSelectedStore.remove(mosId);

                                                    return self.rowSelectCell.toggleRow(node._0, false);
                                                }
                                            });
                                        }
                                    }

                                    pb.hide();
                                }, function (error) {
                                    pb.hide();
                                    console.log(error);
                                });
                            }
                        }
                    });

                var gridContainer = this.makeGridContainer(grid, nls.materialOnSite);

                this.addChild(gridContainer);
            },
            makeItemGrid: function (materialOnSite) {
                var self = this;

                var formatter = new GridFormatter(),
                    store = dojo.data.ItemFileWriteStore({
                        url: "materialOnSite/getMaterialOnSiteItemList/id/" + materialOnSite.id,
                        clearOnClose: true
                    }),
                    structure = [{
                        name: 'No',
                        field: 'id',
                        styles: "text-align:center;",
                        width: '30px',
                        formatter: formatter.rowCountCellFormatter,
                        noresize: true
                    }, {
                        name: nls.description,
                        field: 'description',
                        width: 'auto',
                        formatter: formatter.treeCellFormatter,
                        noresize: true
                    }, {
                        name: nls.type,
                        field: 'type',
                        width: '70px',
                        styles: 'text-align:center;',
                        formatter: formatter.typeCellFormatter,
                        noresize: true
                    }, {
                        name: nls.unit,
                        field: 'uom_id',
                        width: '70px',
                        styles: 'text-align:center;',
                        formatter: formatter.unitIdCellFormatter,
                        noresize: true
                    }, {
                        name: nls.deliveredQty,
                        field: 'delivered_qty',
                        width: '90px',
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        styles: "text-align:right;",
                        formatter: CustomFormatter.qtyCellFormatter,
                        noresize: true
                    }, {
                        name: nls.usedQty,
                        field: 'used_qty',
                        width: '90px',
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        styles: "text-align:right;",
                        formatter: CustomFormatter.qtyCellFormatter,
                        noresize: true
                    }, {
                        name: nls.balanceQty,
                        field: 'balance_qty',
                        width: '90px',
                        styles: "text-align:right;",
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: formatter.unEditableCurrencyCellFormatter,
                        noresize: true
                    }, {
                        name: nls.rate,
                        field: 'rate-value',
                        styles: "text-align:right;",
                        width: '120px',
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: materialOnSite.status[0] == buildspace.apps.PostContractReport.ProjectStructureConstants.MATERIAL_ON_SITE_THIS_CLAIM ? CustomFormatter.currencyCellFormatter : formatter.unEditableCurrencyCellFormatter,
                        noresize: true
                    }, {
                        name: nls.amount,
                        field: 'amount',
                        styles: "text-align:right;",
                        width: '120px',
                        formatter: formatter.unEditableCurrencyCellFormatter,
                        noresize: true
                    }],
                    grid = new MaterialOnSiteGrid({
                        project: self.rootProject,
                        materialOnSite: materialOnSite,
                        type: 'vo-items',
                        gridOpts: {
                            gridContainer: self,
                            store: store,
                            escapeHTMLInData: false,
                            structure: structure,
                            singleCheckBoxSelection: function (e) {
                                var self = this,
                                    rowIndex = e.rowIndex,
                                    checked = this.selection.selected[rowIndex],
                                    item = this.getItem(rowIndex);

                                // used to store removeable selection
                                self.removedIds = [];

                                if (checked) {
                                    self.gridContainer.mosItemSelectedStore.put({id: item.id[0]});

                                    return self.getAffectedVOByItems(item, 'add');
                                } else {
                                    self.gridContainer.mosItemSelectedStore.remove(item.id[0]);

                                    self.removedIds.push(item.id[0]);

                                    return self.getAffectedVOByItems(item, 'remove');
                                }
                            },
                            toggleAllSelection: function (checked) {
                                var self = this, selection = this.selection;

                                // used to store removeable selection
                                self.removedIds = [];

                                if (checked) {
                                    selection.selectRange(0, self.rowCount - 1);
                                    self.store.fetch({
                                        onComplete: function (items) {
                                            dojo.forEach(items, function (item) {
                                                if (item.id > 0) {
                                                    self.gridContainer.mosItemSelectedStore.put({id: item.id[0]});
                                                }
                                            });
                                        }
                                    });

                                    return self.getAffectedVOByItems(null, 'add');
                                } else {
                                    selection.deselectAll();

                                    self.store.fetch({
                                        onComplete: function (items) {
                                            dojo.forEach(items, function (item) {
                                                if (item.id > 0) {
                                                    self.gridContainer.mosItemSelectedStore.remove(item.id[0]);

                                                    self.removedIds.push(item.id[0]);
                                                }
                                            });
                                        }
                                    });

                                    return self.getAffectedVOByItems(null, 'remove');
                                }
                            },
                            getAffectedVOByItems: function (item, type) {
                                var self = this,
                                    items = [];

                                var pb = buildspace.dialog.indeterminateProgressBar({
                                    title: nls.pleaseWait + '...'
                                });

                                pb.show();

                                if (type === 'add') {
                                    self.gridContainer.mosItemSelectedStore.query().forEach(function (item) {
                                        items.push(item.id);
                                    });
                                } else {
                                    for (var itemKeyIndex in self.removedIds) {
                                        items.push(self.removedIds[itemKeyIndex]);
                                    }
                                }

                                request.post('materialOnSiteReporting/getAffectedMOS', {
                                    handleAs: 'json',
                                    data: {
                                        pid: self.project.id,
                                        item_ids: JSON.stringify(self.gridContainer.arrayUnique(items))
                                    }
                                }).then(function (data) {
                                    var vosGrid = dijit.byId('material_on_site-' + self.project.id);

                                    if (type === 'add') {
                                        for (var mosId in data) {
                                            for (var itemIdIndex in data[mosId]) {
                                                self.gridContainer.mosItemSelectedStore.put({id: data[mosId][itemIdIndex]});
                                            }

                                            // remove checked type selection if no item is selected in the current VO
                                            vosGrid.grid.store.fetchItemByIdentity({
                                                identity: mosId,
                                                onItem: function (node) {
                                                    if (!node) {
                                                        return;
                                                    }

                                                    if (self.gridContainer.mosItemSelectedStore.data.length > 0) {
                                                        self.gridContainer.mosSelectedStore.put({id: mosId});

                                                        return vosGrid.grid.rowSelectCell.toggleRow(node._0, true);
                                                    }
                                                }
                                            });
                                        }
                                    } else {
                                        for (var mosId in data) {
                                            for (var itemIdIndex in data[mosId]) {
                                                self.gridContainer.mosItemSelectedStore.remove(data[mosId][itemIdIndex]);
                                            }

                                            // remove checked type selection if no item is selected in the current VO
                                            vosGrid.grid.store.fetchItemByIdentity({
                                                identity: mosId,
                                                onItem: function (node) {
                                                    if (!node) {
                                                        return;
                                                    }

                                                    if (self.gridContainer.mosItemSelectedStore.data.length === 0) {
                                                        self.gridContainer.mosSelectedStore.remove(mosId);

                                                        return vosGrid.grid.rowSelectCell.toggleRow(node._0, false);
                                                    }
                                                }
                                            });
                                        }
                                    }

                                    pb.hide();
                                }, function (error) {
                                    pb.hide();
                                    console.log(error);
                                });
                            }
                        }
                    });

                var title = materialOnSite.description;
                var pageId = 'material_on_site_items_report-' + materialOnSite.id + '-' + self.rootProject.id + '-page';

                self.appendNewStack(grid, pageId, title);
            },
            makeGridContainer: function (content, title) {
                var id = this.rootProject.id;
                var stackContainer = dijit.byId('materialOnSiteReport-' + id + '-stackContainer');

                if (stackContainer) {
                    dijit.byId('materialOnSiteReport-' + id + '-stackContainer').destroyRecursive();
                }

                stackContainer = new dijit.layout.StackContainer({
                    style: 'width:100%;height:100%;border:none;',
                    region: "center",
                    id: 'materialOnSiteReport-' + id + '-stackContainer'
                });

                stackContainer.addChild(new dijit.layout.ContentPane({
                    title: title,
                    content: content,
                    grid: content.grid
                }));

                var controller = new dijit.layout.StackController({
                    region: "top",
                    containerId: 'materialOnSiteReport-' + id + '-stackContainer'
                });

                var controllerPane = new dijit.layout.ContentPane({
                    style: "padding:0;overflow:hidden;",
                    class: 'breadCrumbTrail',
                    region: 'top',
                    content: controller
                });

                var borderContainer = new dijit.layout.BorderContainer({
                    style: "padding:0;margin:0;width:100%;height:100%;border:none;",
                    gutters: false,
                    region: 'center'
                });

                borderContainer.addChild(stackContainer);
                borderContainer.addChild(controllerPane);

                dojo.subscribe('materialOnSiteReport-' + id + '-stackContainer-selectChild', "", function (page) {
                    var widget = dijit.byId('materialOnSiteReport-' + id + '-stackContainer');
                    if (widget) {
                        var children = widget.getChildren(),
                            index = dojo.indexOf(children, page);

                        index = index + 1;

                        if (children.length > index) {

                            while (children.length > index) {
                                widget.removeChild(children[index]);
                                children[index].destroyDescendants();
                                children[index].destroyRecursive();
                                index = index + 1;
                            }

                            var selectedIndex = page.grid.selection.selectedIndex;

                            page.grid.store.save();
                            page.grid.store.close();

                            var handle = aspect.after(page.grid, "_onFetchComplete", function () {
                                handle.remove();

                                if (selectedIndex > -1) {
                                    this.scrollToRow(selectedIndex, true);
                                    this.selection.setSelected(selectedIndex, true);
                                }
                            });

                            page.grid.sort();
                        }
                    }
                });

                return borderContainer;
            },
            appendNewStack: function (content, pageId, title) {
                var self = this;

                var container = dijit.byId('materialOnSiteReport-' + self.rootProject.id + '-stackContainer');

                if (!container) {
                    return;
                }

                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(title, 45),
                    id: pageId,
                    content: content,
                    executeScripts: true
                }, node);
                container.addChild(child);
                container.selectChild(pageId);
            },
            markedCheckBoxObject: function (grid, selectedRowStore) {
                var store = grid.store;

                selectedRowStore.query().forEach(function (item) {
                    if (item.id == buildspace.constants.GRID_LAST_ROW) {
                        return;
                    }

                    store.fetchItemByIdentity({
                        identity: item.id,
                        onItem: function (node) {
                            if (!node) {
                                return;
                            }

                            return grid.rowSelectCell.toggleRow(node._0, true);
                        }
                    });
                });
            },
            arrayUnique: function (array) {
                return array.reverse().filter(function (e, i, arr) {
                    return arr.indexOf(e, i + 1) === -1;
                }).reverse();
            }
        });

        return declare('buildspace.apps.PostContractReport.MaterialOnSiteTabContainer', TabContainer, {
            style: "margin:0;padding:0;border:none;width:100%;height:100%;",
            gutters: false,
            rootProject: null,
            nested: true,
            postCreate: function () {
                var self = this;
                this.inherited(arguments);

                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait + "..."
                });

                pb.show();

                request.get('materialOnSite/getPrintSettingForm', {
                    handleAs: 'json',
                    query: {
                        pid: self.rootProject.id[0]
                    }
                }).then(function (data) {
                    self.createMaterialOnSiteTab();
                    self.createPrintSettingTab(data);

                    pb.hide();
                }, function (error) {
                    pb.hide();
                    console.log(error);
                });
            },
            createMaterialOnSiteTab: function () {
                var self = this;

                this.addChild(new materialOnSiteContainer({
                    title: buildspace.truncateString(nls.materialOnSite, 45),
                    rootProject: self.rootProject
                }));
            },
            createPrintSettingTab: function (data) {
                var self = this;

                this.addChild(new PrintSettingForm({
                    title: buildspace.truncateString(nls.printSettingForm, 45),
                    rootProject: self.rootProject,
                    data: data
                }));
            }
        });
    });