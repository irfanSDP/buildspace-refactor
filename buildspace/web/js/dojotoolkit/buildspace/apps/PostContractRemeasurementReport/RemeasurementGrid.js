define('buildspace/apps/PostContractRemeasurementReport/RemeasurementGrid', [
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dijit/TooltipDialog",
    "dijit/popup",
    'dojo/aspect',
    'dojo/request',
    'dojo/number',
    'dijit/form/DropDownButton',
    'dijit/DropDownMenu',
    'dijit/MenuItem',
    'dijit/PopupMenuItem',
    'dojox/grid/enhanced/plugins/Rearrange',
    'buildspace/widget/grid/plugins/FormulatedColumn',
    'dojox/grid/enhanced/plugins/IndirectSelection',
    'buildspace/widget/grid/cells/Formatter',
    './PrintSelectedTypesOrElementsDialog',
    './PrintElementsDialog',
    './PrintItemsDialog',
    'dojo/i18n!buildspace/nls/PostContractRemeasurement'
], function (declare, lang, connect, TooltipDialog, popup, aspect, request, number, DropDownButton, DropDownMenu, MenuItem, PopupMenuItem, Rearrange, FormulatedColumn, IndirectSelection, GridFormatter, PrintSelectedTypesOrElementsDialog, PrintElementsDialog, PrintItemsDialog, nls) {

    var ReportFormatter = declare("buildspace.apps.PostContractRemeasurementReport.ResourceEnhancedGridFormatter", null, {
        postContractRemeasurementQuantityCellFormatter: function (cellValue, rowIdx, cell) {
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                fieldConstantName = this.field.replace("-qty_per_unit", ""),
                finalValue = cellValue,
                val = '&nbsp;';

            if (isNaN(finalValue) || finalValue == 0 || finalValue == null) {
                var formattedValue = "&nbsp;";
            } else {
                var formattedValue = number.format(finalValue, {places: 2});
            }

            if (item.has_note != undefined && item.has_note == 'true') {
                cell.customClasses.push('hasNoteTypeItemCell');
            }

            if (item.version != undefined && item.version > 0) {
                cell.customClasses.push('hasAddendumTypeItemCell');
            }

            if (item[fieldConstantName + '-has_build_up'] != undefined && item[fieldConstantName + '-has_build_up'][0]) {
                val = '<span style="color:#0000FF;"><b>' + formattedValue + '</b></span>';
            } else {
                val = finalValue >= 0 ? formattedValue : '<span style="color:#FF0000">' + formattedValue + '</span>';
            }

            if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                cell.customClasses.push('disable-cell');
                val = item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY ? nls.rateOnly : '&nbsp;';
            } else if (fieldConstantName == buildspace.apps.PostContractReport.ProjectStructureConstants.OMISSION) {
                cell.customClasses.push('disable-cell');
            } else if (item && item.id[0] > 0 && !item.include[0]) {
                cell.customClasses.push('disable-cell');
            }

            return val;
        }
    });

    var RemeasurementGrid = declare('buildspace.apps.PostContractRemeasurementReport.ResourceEnhancedGrid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        region: 'center',
        project: null,
        bill: null,
        billTypeId: -1,
        gridContainer: null,
        escapeHTMLInData: false,
        constructor: function (args) {
            this.structure = args.structure;
            this.type = args.type;
            this.columnGroup = args.columnGroup;
            this.billTypeId = args.billTypeId;
            this.bill = args.bill;
            this.formatter = new GridFormatter();

            if (this.type == 'item') {
                this.setColumnStructure();

                this.createHeaderCtxMenu();
            }

            if (args.type === 'billTypes' || args.type === 'element' || args.type === 'item') {
                this.plugins = {
                    indirectSelection: {
                        headerSelector: true,
                        width: "40px",
                        styles: "text-align: center;"
                    }
                };
            }

            this.inherited(arguments);
        },
        canSort: function (inSortInfo) {
            return false;
        },
        postCreate: function () {
            var self = this, store;
            var tooltipDialog = null;

            this.formulatedColumn = FormulatedColumn(this, {});
            self.inherited(arguments);

            if (self.type === 'element') {
                store = self.gridContainer.selectedElementStore[self.billTypeId];
            } else if (self.type === 'item') {
                store = self.gridContainer.selectedItemStore[self.billTypeId];
            }

            if (store) {
                aspect.after(self, "_onFetchComplete", function () {
                    self.gridContainer.markedCheckBoxObject(self, store);
                });
            }

            if ( self.type === 'item' ) {
                this._connects.push(connect.connect(this, 'onCellMouseOver', function (e) {
                    var colField = e.cell.field,
                        rowIndex = e.rowIndex,
                        item = this.getItem(rowIndex);

                    var fieldConstantName = colField.replace("-value", "");

                    // will show tooltip for formula, if available
                    if (typeof item[fieldConstantName + '-has_formula'] === 'undefined' || !item[fieldConstantName + '-has_formula'][0]) {
                        return;
                    }

                    var formulaValue = item[fieldConstantName + '-value'][0];

                    // convert ITEM ID into ROW ID (if available)
                    formulaValue = this.formulatedColumn.convertItemIdToRowIndex(formulaValue, rowIndex);

                    if (tooltipDialog === null) {
                        tooltipDialog = new TooltipDialog({
                            content: formulaValue,
                            onMouseLeave: function () {
                                popup.close(tooltipDialog);
                            }
                        });

                        popup.open({
                            popup: tooltipDialog,
                            around: e.cellNode
                        });
                    }
                }));

                this._connects.push(connect.connect(this, 'onCellMouseOut', function () {
                    if (tooltipDialog !== null) {
                        popup.close(tooltipDialog);
                        tooltipDialog = null;
                    }
                }));

                this._connects.push(connect.connect(this, 'onStartEdit', function () {
                    if (tooltipDialog !== null) {
                        popup.close(tooltipDialog);
                        tooltipDialog = null;
                    }
                }));
            }
        },
        startup: function () {
            var self = this;
            self.inherited(arguments);

            this._connects.push(connect.connect(this, 'onCellClick', function (e) {
                if (e.cell.name !== "") {
                    return;
                }

                self.singleCheckBoxSelection(e);
            }));

            this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function (newValue) {
                self.toggleAllSelection(newValue);
            }));
        },
        setColumnStructure: function () {
            var formatter = this.formatter, self = this;

            if (this.type == 'item') {
                var reportingFormatter = new ReportFormatter();

                this.structure = {
                    noscroll: false,
                    cells: [
                        [{
                            name: 'No.',
                            field: 'id',
                            width: '40px',
                            styles: 'text-align:center;',
                            formatter: formatter.rowCountCellFormatter,
                            rowSpan: 2
                        }, {
                            name: nls.billReference,
                            field: 'bill_ref',
                            styles: "text-align:center; color: red;",
                            width: '80px',
                            noresize: true,
                            hidden: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        }, {
                            name: nls.description,
                            field: 'description',
                            width: 'auto',
                            formatter: formatter.postContractPrintPreviewTreeCellFormatter,
                            rowSpan: 2
                        }, {
                            name: nls.type,
                            field: 'type',
                            width: '70px',
                            styles: 'text-align:center;',
                            formatter: formatter.typeCellFormatter,
                            rowSpan: 2
                        }, {
                            name: nls.unit,
                            field: 'uom_id',
                            width: '70px',
                            styles: 'text-align:center;',
                            formatter: formatter.unitIdCellFormatter,
                            rowSpan: 2
                        }, {
                            name: nls.rate,
                            field: 'rate',
                            width: '120px',
                            styles: 'text-align:right;',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            rowSpan: 2
                        }, {
                            name: nls.qtyPerUnit,
                            field: 'omission-qty_per_unit',
                            width: '90px',
                            headerClasses: "typeHeader1",
                            formatter: reportingFormatter.postContractRemeasurementQuantityCellFormatter,
                            styles: 'text-align: right;',
                            noresize: true
                        }, {
                            name: nls.totalPerUnit,
                            field: 'omission-total_per_unit',
                            width: '90px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            styles: 'text-align: right; color: red;',
                            noresize: true
                        }, {
                            name: nls.qtyPerUnit,
                            field: 'addition-qty_per_unit',
                            width: '90px',
                            headerClasses: "typeHeader1",
                            formatter: reportingFormatter.postContractRemeasurementQuantityCellFormatter,
                            styles: 'text-align: right;',
                            noresize: true
                        }, {
                            name: nls.totalPerUnit,
                            field: 'addition-total_per_unit',
                            width: '90px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            styles: 'text-align: right;',
                            noresize: true
                        }, {
                            name: nls.nettAdditionOmission,
                            field: 'nett_addition_omission',
                            width: '100px',
                            styles: "text-align: right; color: green;",
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            rowSpan: 2
                        }],
                        [{
                            name: nls.omission,
                            styles: 'text-align: center;',
                            headerClasses: "staticHeader typeHeader1",
                            colSpan: 2,
                            noresize: true
                        }, {
                            name: nls.addition,
                            styles: 'text-align: center;',
                            headerClasses: "staticHeader typeHeader2",
                            colSpan: 2,
                            noresize: true
                        }]
                    ]
                };
            }
        },
        onHeaderCellClick: function (e) {
            if (!dojo.hasClass(e.cell.id, "staticHeader")) {
                e.grid.setSortIndex(e.cell.index);
                e.grid.onHeaderClick(e);
            }
        },
        onHeaderCellMouseOver: function (e) {
            if (!dojo.hasClass(e.cell.id, "staticHeader")) {
                dojo.addClass(e.cellNode, this.cellOverClass);
            }
        },
        onStyleRow: function (e) {
            this.inherited(arguments);

            if (e.node.children[0]) {
                if (e.node.children[0].children[0].rows.length >= 2) {
                    var elemToHide = e.node.children[0].children[0].rows[1],
                        childElement = e.node.children[0].children[0].rows[0].children;

                    elemToHide.parentNode.removeChild(elemToHide);

                    dojo.forEach(childElement, function (child, i) {
                        var rowSpan = dojo.attr(child, 'rowSpan');

                        if (!rowSpan || rowSpan < 2)
                            dojo.attr(child, 'rowSpan', 2);
                    });
                }
            }
        },
        createHeaderCtxMenu: function () {
            if (typeof this.structure !== 'undefined') {
                var columnGroup = this.structure.cells[0],
                    self = this,
                    menusObject = {
                        headerMenu: new dijit.Menu()
                    };
                dojo.forEach(columnGroup, function (data, index) {
                    if (data.showInCtxMenu) {
                        menusObject.headerMenu.addChild(new dijit.CheckedMenuItem({
                            label: data.name,
                            checked: (typeof data.hidden === 'undefined' || data.hidden === false) ? true : false,
                            onChange: function (val) {

                                var show = false;

                                if (val) {
                                    show = true;
                                }

                                self.showHideMergedColumn(show, index);
                            }
                        }));
                    }
                });

                this.plugins = {menus: menusObject};
            }
        },
        showHideMergedColumn: function (show, index) {
            this.beginUpdate();
            this.layout.setColumnVisibility(index, show);
            this.endUpdate();
        },
        destroy: function () {
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.PostContractRemeasurementReport.RemeasurementGrid', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        gridOpts: {},
        pageId: 0,
        postCreate: function () {
            var self = this;
            this.inherited(arguments);
            lang.mixin(self.gridOpts, {project: self.project});

            var menu = new DropDownMenu({style: "display: none;"});
            var toolbar = new dijit.Toolbar({region: "top", style: "padding:2px;border-bottom:none;width:100%;"});
            var grid = this.grid = new RemeasurementGrid(self.gridOpts);

            if (self.gridOpts.type === 'billTypes') {
                var menus = ['selectedTypes', 'elementsBySelectedTypes'];

                dojo.forEach(menus, function (opt) {
                    var printPreviewMethod;

                    switch (opt) {
                        case 'selectedTypes':
                            printPreviewMethod = 'openPrintSelectedTypes';
                            break;

                        case 'elementsBySelectedTypes':
                            printPreviewMethod = 'openPrintSelectedElementsByTypes';
                            break;
                    }

                    menu.addChild(new MenuItem({
                        label: nls[opt],
                        onClick: function () {
                            self[printPreviewMethod](opt);
                        }
                    }));
                });
            } else if (self.gridOpts.type === 'element' || self.gridOpts.type === 'item') {
                var menus = ['selectedElements', 'elementsByAdditionOnly', 'selectedItems', 'itemsWithAdditionOnly', 'selectedItemsWithBuildUpQtyOnly'];

                dojo.forEach(menus, function (opt) {
                    var printPreviewMethod;

                    switch (opt) {
                        case 'selectedElements':
                            printPreviewMethod = 'openPrintSelectedElements';
                            break;

                        case 'elementsByAdditionOnly':
                            printPreviewMethod = 'openPrintElementsWithAddition';
                            break;
                        case 'selectedItems':
                            printPreviewMethod = 'openPrintSelectedItems';
                            break;

                        case 'itemsWithAdditionOnly':
                            printPreviewMethod = 'openPrintItemsWithAdditionOnly';
                            break;

                        case 'selectedItemsWithBuildUpQtyOnly':
                            printPreviewMethod = 'openPrintSelectedItemsWithBuildUpQtyOnly';
                            break;
                    }

                    menu.addChild(new MenuItem({
                        label: nls[opt],
                        onClick: function () {
                            self[printPreviewMethod](opt);
                        }
                    }));
                });
            }

            if (self.gridOpts.type === 'billTypes' || self.gridOpts.type === 'element' || self.gridOpts.type === 'item') {
                toolbar.addChild(
                    new DropDownButton({
                        label: nls.printPreview,
                        iconClass: "icon-16-container icon-16-print",
                        dropDown: menu
                    })
                );

                this.addChild(toolbar);
            }

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content: grid, region: 'center'}));

            var container = dijit.byId('postContractReportRemeasurement-grid_' + self.project.id + '-stackContainer');

            if (container) {
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(self.stackContainerTitle, 60),
                    id: self.pageId,
                    executeScripts: true
                }, node);
                container.addChild(child);
                child.set('content', self);
                lang.mixin(child, {grid: grid});
                container.selectChild(self.pageId);
            }
        },
        openPrintSelectedTypes: function () {
            var self = this,
                selectedTypesStore = self.gridOpts.gridContainer.selectedTypeStore,
                types = [];

            selectedTypesStore.query().forEach(function (item) {
                types.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show();

            request.post('postContractRemeasurementReport/getPrintingSelectedTypes', {
                handleAs: 'json',
                data: {
                    bill_id: self.gridOpts.bill.id[0],
                    type_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(types)),
                    opt: self.gridOpts.gridContainer.opt
                }
            }).then(function (data) {
                var dialog = new PrintSelectedTypesOrElementsDialog({
                    bill: self.gridOpts.bill,
                    title: nls.selectedTypes,
                    data: data,
                    selectedItems: types,
                    opt: self.gridOpts.gridContainer.opt,
                    printURL: 'postContractRemeasurementReport/printSelectedTypes',
                    exportURL: 'postContractRemeasurementExportExcel/exportSelectedTypes'
                });

                dialog.show();
                pb.hide();
            }, function (error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintSelectedElementsByTypes: function () {
            var self = this,
                selectedTypesStore = self.gridOpts.gridContainer.selectedTypeStore,
                types = [];

            selectedTypesStore.query().forEach(function (item) {
                types.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show();

            request.post('postContractRemeasurementReport/getPrintingSelectedElementByTypes', {
                handleAs: 'json',
                data: {
                    bill_id: self.gridOpts.bill.id[0],
                    type_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(types)),
                    opt: self.gridOpts.gridContainer.opt
                }
            }).then(function (data) {
                var dialog = new PrintSelectedTypesOrElementsDialog({
                    bill: self.gridOpts.bill,
                    title: nls.elementsBySelectedTypes,
                    data: data,
                    selectedItems: types,
                    opt: self.gridOpts.gridContainer.opt,
                    printURL: 'postContractRemeasurementReport/printingSelectedElementByTypes',
                    exportURL: 'postContractRemeasurementExportExcel/exportExcelSelectedElementByTypes'
                });

                dialog.show();
                pb.hide();
            }, function (error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintSelectedElements: function () {
            var self = this,
                selectedElementsStore = self.gridOpts.gridContainer.selectedElementStore[self.gridOpts.billTypeId],
                elements = [];

            selectedElementsStore.query().forEach(function (item) {
                elements.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show();

            request.post('postContractRemeasurementReport/getPrintingSelectedElements', {
                handleAs: 'json',
                data: {
                    bill_id: self.gridOpts.bill.id[0],
                    bill_type_id: self.gridOpts.billTypeId,
                    element_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(elements)),
                    opt: self.gridOpts.gridContainer.opt
                }
            }).then(function (data) {
                var dialog = new PrintElementsDialog({
                    bill: self.gridOpts.bill,
                    bill_type_id: self.gridOpts.billTypeId,
                    title: nls.selectedElements,
                    data: data,
                    selectedItems: elements,
                    opt: self.gridOpts.gridContainer.opt,
                    printURL: 'postContractRemeasurementReport/printingSelectedElements',
                    exportURL: 'postContractRemeasurementExportExcel/exportExcelSelectedElements'
                });

                dialog.show();
                pb.hide();
            }, function (error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintElementsWithAddition: function () {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show();

            request.post('postContractRemeasurementReport/getPrintingElementsWithAddition', {
                handleAs: 'json',
                data: {
                    bill_id: self.gridOpts.bill.id[0],
                    bill_type_id: self.gridOpts.billTypeId,
                    opt: self.gridOpts.gridContainer.opt
                }
            }).then(function (data) {
                var dialog = new PrintElementsDialog({
                    bill: self.gridOpts.bill,
                    bill_type_id: self.gridOpts.billTypeId,
                    title: nls.elementsByAdditionOnly,
                    data: data,
                    opt: self.gridOpts.gridContainer.opt,
                    printURL: 'postContractRemeasurementReport/printingElementsWithAddition',
                    exportURL: 'postContractRemeasurementExportExcel/exportExcelElementsWithAddition'
                });

                dialog.show();
                pb.hide();
            }, function (error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintSelectedItems: function () {
            var self = this,
                selectedItemsStore = self.gridOpts.gridContainer.selectedItemStore[self.gridOpts.billTypeId],
                items = [];

            selectedItemsStore.query().forEach(function (item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show();

            request.post('postContractRemeasurementReport/getPrintingSelectedItems', {
                handleAs: 'json',
                data: {
                    bill_id: self.gridOpts.bill.id[0],
                    bill_type_id: self.gridOpts.billTypeId,
                    item_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items)),
                    opt: self.gridOpts.gridContainer.opt
                }
            }).then(function (data) {
                var dialog = new PrintItemsDialog({
                    bill: self.gridOpts.bill,
                    bill_type_id: self.gridOpts.billTypeId,
                    title: nls.selectedItems,
                    data: data,
                    selectedItems: items,
                    opt: self.gridOpts.gridContainer.opt,
                    printURL: 'postContractRemeasurementReport/printingSelectedItems',
                    exportURL: 'postContractRemeasurementExportExcel/exportExcelSelectedItems'
                });

                dialog.show();
                pb.hide();
            }, function (error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintItemsWithAdditionOnly: function () {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show();

            request.post('postContractRemeasurementReport/getPrintingItemsWithAdditionOnly', {
                handleAs: 'json',
                data: {
                    bill_id: self.gridOpts.bill.id[0],
                    bill_type_id: self.gridOpts.billTypeId,
                    opt: self.gridOpts.gridContainer.opt
                }
            }).then(function (data) {
                var dialog = new PrintItemsDialog({
                    bill: self.gridOpts.bill,
                    bill_type_id: self.gridOpts.billTypeId,
                    title: nls.itemsWithAdditionOnly,
                    data: data,
                    opt: self.gridOpts.gridContainer.opt,
                    printURL: 'postContractRemeasurementReport/printingItemsWithAdditionOnly',
                    exportURL: 'postContractRemeasurementExportExcel/exportExcelItemsWithAdditionOnly'
                });

                dialog.show();
                pb.hide();
            }, function (error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintSelectedItemsWithBuildUpQtyOnly: function () {
            var self = this,
                selectedItemsStore = self.gridOpts.gridContainer.selectedItemStore[self.gridOpts.billTypeId],
                items = [];

            selectedItemsStore.query().forEach(function (item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show();

            request.post('postContractRemeasurementReport/getPrintingSelectedItemsWithBuildUpQty', {
                handleAs: 'json',
                data: {
                    bill_id: self.gridOpts.bill.id[0],
                    bill_type_id: self.gridOpts.billTypeId,
                    item_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items)),
                    opt: self.gridOpts.gridContainer.opt
                }
            }).then(function (data) {
                var dialog = new PrintItemsDialog({
                    bill: self.gridOpts.bill,
                    bill_type_id: self.gridOpts.billTypeId,
                    title: nls.selectedItemsWithBuildUpQtyOnly,
                    data: data,
                    selectedItems: items,
                    opt: self.gridOpts.gridContainer.opt,
                    printURL: 'postContractRemeasurementReport/printingSelectedItemsWithBuildUpQty',
                    exportURL: 'postContractRemeasurementExportExcel/exportExcelSelectedItemsWithBuildUpQty'
                });

                dialog.show();
                pb.hide();
            }, function (error) {
                console.log(error);
                pb.hide();
            });
        }
    });
});