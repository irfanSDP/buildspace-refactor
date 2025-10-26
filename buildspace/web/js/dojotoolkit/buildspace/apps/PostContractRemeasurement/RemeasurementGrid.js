define('buildspace/apps/PostContractRemeasurement/RemeasurementGrid', [
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dijit/TooltipDialog",
    "dijit/popup",
    'dojox/grid/enhanced/plugins/Rearrange',
    'buildspace/widget/grid/plugins/FormulatedColumn',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/PostContractRemeasurement'
], function (declare, lang, connect, TooltipDialog, popup, Rearrange, FormulatedColumn, GridFormatter, nls) {

    var RemeasurementGrid = declare('buildspace.apps.PostContractRemeasurement.ResourceEnhancedGrid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        region: 'center',
        updateUrl: null,
        project: null,
        billTypeId: -1,
        escapeHTMLInData: false,
        constructor: function (args) {
            this.structure = args.structure;
            this.type = args.type;
            this.updateUrl = args.updateUrl;
            this.columnGroup = args.columnGroup;
            this.billTypeId = args.billTypeId;
            this.formatter = new GridFormatter();

            if (this.type == 'item') {
                this.setColumnStructure();

                this.createHeaderCtxMenu();
            }

            this.inherited(arguments);
        },
        canSort: function (inSortInfo) {
            return false;
        },
        postCreate: function () {
            var tooltipDialog = null;

            this.formulatedColumn = FormulatedColumn(this, {});
            this.inherited(arguments);

            if ( self.type === 'item' ) {
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
            }
        },
        canEdit: function (inCell, inRowIndex) {
            var self = this, item = this.getItem(inRowIndex);

            if (self.type === 'item') {
                if (inCell) {
                    if (item && item.id[0] > 0) {
                        if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                            window.setTimeout(function () {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        } else if (!item.include[0]) {
                            window.setTimeout(function () {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }
                    } else {
                        return;
                    }
                }
            } else {
                window.setTimeout(function () {
                    self.edit.cancel();
                    self.focus.setFocusIndex(inRowIndex);
                }, 10);
                return;
            }

            return this._canEdit;
        },
        doApplyCellEdit: function (val, rowIdx, inAttrName) {
            var self = this,
                item = self.getItem(rowIdx),
                store = self.store,
                attrNameParsed = inAttrName.replace("-qty_per_unit", "");

            if (item[inAttrName] && val !== item[inAttrName][0]) {
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.savingData + '. ' + nls.pleaseWait + '...'
                });

                if (!val) {
                    val = 0;
                }

                var params = {
                    id: item.post_contract_bill_item_rate_id,
                    attr_name: inAttrName,
                    val: val,
                    btId: self.billTypeId,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = self.updateUrl;

                var updateCell = function (data, store) {
                    for (var property in data) {
                        if (item.hasOwnProperty(property) && property != store._getIdentifierAttribute()) {
                            store.setValue(item, property, data[property]);
                        }
                    }

                    store.save();
                };

                var xhrArgs = {
                    url: url,
                    content: params,
                    handleAs: 'json',
                    load: function (resp) {
                        if (resp.success) {
                            if (item.id > 0) {
                                updateCell(resp.item, store);
                            }

                            var cell = self.getCellByField(inAttrName);

                            window.setTimeout(function () {
                                self.focus.setFocusIndex(rowIdx, cell.index);
                            }, 10);

                            pb.hide();
                        }
                    },
                    error: function (error) {
                        pb.hide();
                    }
                };

                if (item[attrNameParsed + '-has_build_up'] != undefined && item[attrNameParsed + '-has_build_up'][0]) {
                    var onYes = function () {
                        pb.show();
                        dojo.xhrPost(xhrArgs);
                    };

                    var content = '<div>' + nls.detachAllBuildUpAndLink + '</div>';
                    buildspace.dialog.confirm(nls.confirmation, content, 60, 280, onYes);
                    self.doCancelEdit(rowIdx);
                } else {
                    pb.show();
                    dojo.xhrPost(xhrArgs);
                    self.inherited(arguments);
                }
            } else {
                self.inherited(arguments);
            }
        },
        setColumnStructure: function () {
            var formatter = this.formatter, self = this;

            if (this.type == 'item') {
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
                            formatter: formatter.treeCellFormatter,
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
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            rowSpan: 2
                        }, {
                            name: nls.qtyPerUnit,
                            field: 'omission-qty_per_unit',
                            width: '90px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.postContractRemeasurementQuantityCellFormatter,
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
                            formatter: formatter.postContractRemeasurementQuantityCellFormatter,
                            styles: 'text-align: right;',
                            noresize: true,
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox'
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
        doCancelEdit: function (inRowIndex) {
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        editableCellDblClick: function (e) {
            var event;
            if (this._click.length > 1 && has('ie')) {
                event = this._click[1];
            } else if (this._click.length > 1 && this._click[0].rowIndex != this._click[1].rowIndex) {
                event = this._click[0];
            } else {
                event = e;
            }
            this.focus.setFocusCell(event.cell, event.rowIndex);
            this.onRowClick(event);
            this.onRowDblClick(e);
        },
        dodblclick: function (e) {
            if (e.cellNode) {
                if (e.cell.editable) {
                    this.editableCellDblClick(e);
                } else {
                    this.onCellDblClick(e);
                }
            } else {
                this.onRowDblClick(e);
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
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var RemeasurementGridContainer = declare('buildspace.apps.PostContractRemeasurement.RemeasurementGrid', dijit.layout.BorderContainer, {
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

            var grid = this.grid = new RemeasurementGrid(self.gridOpts);
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content: grid, region: 'center'}));

            var container = dijit.byId('postContractRemeasurement-grid_' + self.project.id + '-stackContainer');

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
        }
    });

    return RemeasurementGridContainer;
});