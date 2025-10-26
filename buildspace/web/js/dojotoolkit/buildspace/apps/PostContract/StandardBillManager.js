define('buildspace/apps/PostContract/StandardBillManager', [
    'dojo/_base/declare',
    "dojo/dom-style",
    "dojo/_base/connect",
    "dijit/TooltipDialog",
    "dijit/popup",
    'buildspace/widget/grid/plugins/FormulatedColumn',
    "dojo/when",
    'dojo/number',
    "dojo/currency",
    "dijit/layout/ContentPane",
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    "buildspace/apps/ProjectBuilder/BillManager/ScheduleOfQuantityGrid",
    "./BillManager/buildUpQuantitySummary",
    'dojo/aspect',
    'dojo/i18n!buildspace/nls/PostContract',
    './BillManager/StandardBillGrid',
    './BillManager/lumpSumPercentDialog',
    'buildspace/widget/grid/cells/Textarea'],
function (declare, domStyle, connect, TooltipDialog, popup, FormulatedColumn, when, number, currency, ContentPane, EnhancedGrid, GridFormatter, ScheduleOfQuantityGrid, BuildUpQuantitySummary, aspect, nls, StandardBillGrid, LumpSumPercentDialog, Textarea) {

    var TypeGrid = declare('buildspace.apps.PostContract.TypeGrid', EnhancedGrid, {
        rootProject: null,
        style: "border:none;",
        region: 'center',
        pageId: null,
        billId: null,
        updateUrl: null,
        keepSelection: true,
        rowSelector: '0px',
        constructor: function (args) {
            var formatter = new GridFormatter();

            this.structure = {
                noscroll: false,
                cells: [
                    [{
                        name: 'No.',
                        field: 'count',
                        width: '30px',
                        styles: 'text-align:center;',
                        formatter: Formatter.rowCountCellFormatter,
                        rowSpan: 2
                    }, {
                        name: nls.description,
                        field: 'description',
                        width: 'auto',
                        formatter: formatter.typeListTreeCellFormatter,
                        rowSpan: 2
                    }, {
                        name: nls.renameDescription,
                        field: 'new_name',
                        width: '180px',
                        editable: 'true',
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        rowSpan: 2,
                        styles: 'text-align:center;',
                        formatter: formatter.typeListLevelFormatter
                    }, {
                        name: nls.omittedItems,
                        field: 'vo_omitted_items',
                        width:'45px',
                        styles:'text-align: center;',
                        editable: false,
                        noresize: true,
                        showInCtxMenu: true,
                        rowSpan : 2,
                        formatter: formatter.unEditableCellFormatter
                    }, {
                        name: nls.amount,
                        field: 'total_per_unit',
                        width: '120px',
                        styles: 'text-align: right;color:blue;',
                        formatter: formatter.unEditableCurrencyCellFormatter,
                        rowSpan: 2
                    }, {
                        name: nls.percent,
                        field: 'up_to_date_percentage',
                        width: '120px',
                        headerClasses: "typeHeader1",
                        formatter: formatter.unEditablePercentageCellFormatter,
                        styles: 'text-align: right;'
                    }, {
                        name: nls.amount,
                        field: 'up_to_date_amount',
                        width: '120px',
                        headerClasses: "typeHeader1",
                        formatter: formatter.unEditableCurrencyCellFormatter,
                        styles: 'text-align: right;'
                    },{
                        name: nls.percent,
                        field: 'imported_up_to_date_percentage',
                        width: '120px',
                        styles:'text-align: right;',
                        formatter: formatter.unEditablePercentageCellFormatter,
                        showInCtxMenu: true,
                        ctxMenuLabel: nls.importedUpToDateClaim,
                        hideColumnGroup: [
                            {field:'imported_up_to_date_amount'},
                            {name: nls.importedUpToDateClaim}
                        ]
                    },{
                        name: nls.amount,
                        field: 'imported_up_to_date_amount',
                        width: '120px',
                        styles:'text-align: right;',
                        formatter: formatter.unEditableCurrencyCellFormatter
                    }],
                    [{
                        name: nls.upToDateClaim,
                        styles: 'text-align:center;',
                        headerClasses: "staticHeader typeHeader1",
                        colSpan: 2
                    },{
                        name: nls.importedUpToDateClaim,
                        styles: 'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan: 2
                    }]
                ]
            };

            buildspace.grid.headerCtxMenu.createMenu(this);
            this.inherited(arguments);
        },
        canSort: function (inSortInfo) {
            return false;
        },
        dodblclick: function (e) {
            this.onRowDblClick(e);
        },
        canEdit: function (inCell, inRowIndex) {
            var self = this;

            if (inCell != undefined) {
                var item = this.getItem(inRowIndex);

                if (item.level[0] === 0) {
                    window.setTimeout(function () {
                        self.edit.cancel();
                        self.focus.setFocusIndex(inRowIndex, inCell.index);
                    }, 10);
                    return;
                }
            }

            return this._canEdit;
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
        doApplyCellEdit: function (val, rowIdx, inAttrName) {
            var self = this, item = this.getItem(rowIdx), store = this.store;

            if (item[inAttrName][0] != undefined || val !== item[inAttrName][0]) {
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait + '...'
                });
                var params = {
                    id: item.id,
                    project_id: self.rootProject.id,
                    type_id: item.relation_id,
                    counter: item.count,
                    attr_name: inAttrName,
                    val: val
                }, url = this.updateUrl;

                var updateCell = function (data, store) {
                    for (var property in data) {
                        if (item.hasOwnProperty(property) && property != store._getIdentifierAttribute()) {
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
                        load: function (resp) {
                            if (resp.success) {

                                updateCell(resp.item, store);

                                var cell = self.getCellByField(inAttrName);
                                window.setTimeout(function () {
                                    self.focus.setFocusIndex(rowIdx, cell.index);
                                }, 10);
                                pb.hide();
                            }
                        },
                        error: function (error) {
                            pb.hide();
                            console.log(error);
                        }
                    });
                });
            }

            this.inherited(arguments);
        },
        reload: function () {
            this.store.close();
            this._refresh();
        }
    });

    var Formatter = {
        rowCountCellFormatter: function (cellValue, rowIdx, cell) {
            var item = this.grid.getItem(rowIdx);

            if (item.type != undefined && item.type < 1) {
                cell.customClasses.push('invalidTypeItemCell');
            }

            return cellValue > 0 ? cellValue : '';
        }
    };

    var BuildUpQtyGrid = declare('buildspace.apps.PostContract.BillManager.BuildUpQtyGrid', dojox.grid.EnhancedGrid, {
        title: nls.manualQtyItems,
        region: 'center',
        store: null,
        structure: null,
        postCreate: function () {
            var tooltipDialog = null;

            this.formulatedColumn = FormulatedColumn(this, {});
            this.inherited(arguments);

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
        },
        destroy: function () {
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.PostContract.StandardBillManager', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0px;border:none;width:100%;height:100%;",
        gutters: false,
        billId: null,
        rootProject: null,
        locked: false,
        postCreate: function () {
            this.inherited(arguments);
            var self = this;

            this.createTypeGrid();

            dojo.subscribe('postContractStandardBill' + self.billId + '-stackContainer-selectChild', "", function (page) {
                var widget = dijit.byId('postContractStandardBill' + self.billId + '-stackContainer');
                if (widget) {
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page));

                    index = index + 1;

                    if (children.length > index) {
                        while (children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();

                            index = index + 1;
                        }

                        if (page.grid) {
                            var selectedIndex = page.grid.selection.selectedIndex;

                            page.grid.store.save();
                            page.grid.store.close();

                            var handle = aspect.after(page.grid, "_onFetchComplete", function () {
                                handle.remove();
                                if (selectedIndex > -1) {
                                    this.scrollToRow(selectedIndex);
                                    this.selection.setSelected(selectedIndex, true);
                                }
                            });

                            page.grid.sort();
                        }
                    }
                }
            });
        },
        createTypeGrid: function () {
            var self = this;

            var stackContainer = dijit.byId('postContractStandardBill' + this.billId + '-stackContainer');

            if (stackContainer) {
                dijit.byId('postContractStandardBill' + this.billId + '-stackContainer').destroyRecursive();
            }

            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: 'postContractStandardBill' + this.billId + '-stackContainer'
            });

            var me = this;

            dojo.xhrGet({
                url: "postContract/getBillInfo",
                handleAs: "json",
                content: {
                    id: this.billId
                }
            }).then(function (billInfo) {
                var store = dojo.data.ItemFileWriteStore({
                    url: 'postContractStandardBillClaim/getTypeList/id/' + self.billId,
                    clearOnClose: true
                });

                var grid = new TypeGrid({
                    billId: me.billId,
                    rootProject: me.rootProject,
                    pageId: 'type-page-' + me.billId,
                    id: 'type-page-container-' + me.billId,
                    updateUrl: 'postContractStandardBillClaim/updateTypeList',
                    store: store,
                    onRowDblClick: function (e) {
                        var self = this,
                            item = self.getItem(e.rowIndex);

                        if (item.level[0] > 0) {
                            me.createElementGrid(item, billInfo, grid);
                        }
                    }
                });

                var controller = new dijit.layout.StackController({
                    region: "top",
                    containerId: 'postContractStandardBill' + me.billId + '-stackContainer'
                });

                stackContainer.addChild(new dijit.layout.ContentPane({
                    title: nls.type + ' / ' + nls.unit,
                    content: grid,
                    grid: grid
                }));

                var controllerPane = new dijit.layout.ContentPane({
                    style: "padding:0px;overflow:hidden;",
                    baseClass: 'breadCrumbTrail',
                    region: 'top',
                    id: 'billGrid' + me.billId + '-controllerPane',
                    content: controller
                });

                me.addChild(stackContainer);
                me.addChild(controllerPane);
            });
        },
        createElementGrid: function (typeItem, billInfo, typeItemGridStore) {
            var self = this;

            //get type Item by With Id
            var typeItemQuery = dojo.xhrGet({
                url: "postContractStandardBillClaim/getTypeItem",
                handleAs: "json",
                content: {
                    type_id: typeItem.relation_id,
                    project_id: self.rootProject.id,
                    counter: typeItem.count
                }
            });

            typeItemQuery.then(function (typeItem) {
                var me = self;

                var grid = new StandardBillGrid({
                    stackContainerTitle: (typeItem.new_name.length > 0) ? typeItem.relation_name + ' :: ' + typeItem.new_name : typeItem.relation_name + ' :: ' + typeItem.description,
                    billId: self.billId,
                    disableEditing: !billInfo.editable,
                    rootProject: self.rootProject,
                    pageId: 'element-page-' + self.billId,
                    id: 'element-page-container-' + self.billId,
                    typeItem: typeItem,
                    locked: self.locked,
                    gridOpts: {
                        store: dojo.data.ItemFileWriteStore({
                            url: 'postContractStandardBillClaim/getBillElementList/id/' + self.billId + "/project_id/" + self.rootProject.id + "/type_ref_id/" + typeItem.id,
                            clearOnClose: true,
                            urlPreventCache: true
                        }),
                        updateUrl: 'postContractStandardBillClaim/standardBillClaimElementUpdate',
                        typeColumns: billInfo.column_settings,
                        bqCSRFToken: billInfo.bqCSRFToken,
                        claimRevision: billInfo.claim_project_revision_status,
                        selectedClaimRevision: billInfo.current_selected_claim_project_revision_status,
                        currentGridType: 'element',
                        onRowDblClick: function (e) {
                            var self = this,
                                item = self.getItem(e.rowIndex);

                            if (item.id[0] > 0 && item.description[0] !== null && item.description[0] !== '') {
                                me.createItemGrid(item, typeItem, billInfo, grid);
                            }
                        }
                    }
                });
            });
        },
        createItemGrid: function (element, typeItem, billInfo, elementGridStore) {
            var billId = this.billId;

            var self = this,
                store = new dojo.data.ItemFileWriteStore({
                    url: "postContractStandardBillClaim/getItemList/id/" + element.id + "/bill_id/" + billId + "/type_ref_id/" + element.claim_type_ref_id + "/project_id/" + self.rootProject.id,
                    clearOnClose: true
                });

            new StandardBillGrid({
                stackContainerTitle: element.description,
                billId: billId,
                disableEditing: !billInfo.editable,
                rootProject: self.rootProject,
                id: 'item-page-container-' + billId,
                elementId: element.id,
                pageId: 'item-page-' + billId,
                type: 'tree',
                typeItem: typeItem,
                gridOpts: {
                    store: store,
                    escapeHTMLInData: false,
                    elementGridStore: elementGridStore,
                    claimRevision: billInfo.claim_project_revision_status,
                    selectedClaimRevision: billInfo.current_selected_claim_project_revision_status,
                    updateUrl: 'postContractStandardBillClaim/standardBillClaimUpdate',
                    currentGridType: 'item',
                    onRowDblClick: function (e) {
                        var colField = e.cell.field,
                            rowIndex = e.rowIndex,
                            item = this.getItem(rowIndex),
                            billGridStore = this.store;

                        if (colField == 'qty_per_unit' && item[colField + '-has_build_up'][0]) {
                            var billColumnSettingId = typeItem.relation_id;

                            var pb = buildspace.dialog.indeterminateProgressBar({
                                title: nls.pleaseWait + '...'
                            });
                            pb.show().then(function() {
                                dojo.xhrPost({
                                    url: "billBuildUpQuantity/getDimensionColumnStructure",
                                    content: {uom_id: item.uom_id[0]},
                                    handleAs: "json"
                                }).then(function (dimensionColumns) {
                                    self.createBuildUpQuantityContainer(item, dimensionColumns, billColumnSettingId, billGridStore, elementGridStore);
                                    pb.hide();
                                });
                            });

                        }
                    },
                    editableCellDblClick: function (e) {
                        var colField = e.cell.field,
                            rowIndex = e.rowIndex,
                            item = this.getItem(rowIndex),
                            billGridStore = this.store,
                            disableEditingMode;

                        if (colField == "rate" && item && !isNaN(parseInt(item.id[0])) && item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT) {

                            if (number.parse(item.up_to_date_percentage) || number.parse(item.current_percentage) || number.parse(item.prev_percentage)) {
                                disableEditingMode = true;
                            }else{
                                disableEditingMode = false;
                            }

                            var lumpSumPercentDialog = new LumpSumPercentDialog({
                                itemObj: item,
                                projectId: self.rootProject.id,
                                billGridStore: billGridStore,
                                elementGridStore: elementGridStore,
                                disableEditingMode: this.disableEditing ? true : disableEditingMode
                            });

                            lumpSumPercentDialog.show();
                        }
                    }
                }
            });
        },
        createBuildUpQuantityContainer: function (item, dimensionColumns, billColumnSettingId, billGridStore, elementGridStore) {
            var self = this,
                scheduleOfQtyGrid,
                baseContainer = new dijit.layout.BorderContainer({
                    style: "padding:0;margin:0;width:100%;height:100%;border:none;outline:none;",
                    gutters: false
                }),
                tabContainer = new dijit.layout.TabContainer({
                    nested: true,
                    style: "padding:0;border:none;margin:0;width:100%;height:100%;",
                    region: 'center'
                }),
                type = buildspace.constants.QUANTITY_PER_UNIT_ORIGINAL,
                formatter = new GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url: "billBuildUpQuantity/getBuildUpQuantityItemList/bill_item_id/" + item.id + "/bill_column_setting_id/" + billColumnSettingId + "/type/" + type,
                    clearOnClose: true
                }),
                hasLinkedQty = false,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait + '...'
                });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "billBuildUpQuantity/getLinkInfo/id/" + item.id + "/bcid/" + billColumnSettingId + "/t/" + type,
                    handleAs: "json"
                }).then(function (linkInfo) {
                    var structure = [{
                        name: 'No.',
                        field: 'id',
                        styles: "text-align:center;",
                        width: '30px',
                        formatter: formatter.rowCountCellFormatter
                    }, {
                        name: nls.description,
                        field: 'description',
                        width: 'auto'
                    }, {
                        name: nls.factor,
                        field: 'factor-value',
                        width: '100px',
                        styles: 'text-align:right;',
                        formatter: formatter.formulaNumberCellFormatter
                    }];

                    dojo.forEach(dimensionColumns, function (dimensionColumn) {
                        var column = {
                            name: dimensionColumn.title,
                            field: dimensionColumn.field_name,
                            width: '100px',
                            styles: 'text-align:right;',
                            formatter: formatter.formulaNumberCellFormatter
                        };
                        structure.push(column);
                    });

                    var totalColumn = {
                        name: nls.total,
                        field: 'total',
                        width: '100px',
                        styles: 'text-align:right;',
                        formatter: formatter.numberCellFormatter
                    };
                    structure.push(totalColumn);

                    var signColumn = {
                        name: nls.sign,
                        field: 'sign',
                        width: '70px',
                        styles: 'text-align:center;',
                        formatter: formatter.signCellFormatter
                    };
                    structure.push(signColumn);

                    var buildUpSummaryWidget = new BuildUpQuantitySummary({
                        itemId: item.id,
                        billColumnSettingId: billColumnSettingId,
                        type: type,
                        hasLinkedQty: linkInfo.has_linked_qty,
                        container: baseContainer,
                        _csrf_token: item._csrf_token,
                        disableEditingMode: true
                    });

                    if (linkInfo.has_linked_qty) {
                        hasLinkedQty = true;
                        scheduleOfQtyGrid = ScheduleOfQuantityGrid({
                            title: nls.scheduleOfQuantities,
                            BillItem: item,
                            billColumnSettingId: billColumnSettingId,
                            disableEditingMode: true,
                            stackContainerId: 'postContractStandardBill' + self.billId + '-stackContainer',
                            gridOpts: {
                                qtyType: type,
                                buildUpSummaryWidget: buildUpSummaryWidget,
                                store: new dojo.data.ItemFileWriteStore({
                                    url: "billBuildUpQuantity/getScheduleOfQuantities/id/" + item.id + "/bcid/" + billColumnSettingId + "/type/" + type,
                                    clearOnClose: true
                                }),
                                structure: [{
                                    name: 'No.',
                                    field: 'id',
                                    width: '30px',
                                    styles: 'text-align:center;',
                                    formatter: formatter.rowCountCellFormatter
                                }, {
                                    name: nls.description,
                                    field: 'description',
                                    width: 'auto',
                                    formatter: formatter.treeCellFormatter
                                }, {
                                    name: nls.type,
                                    field: 'type',
                                    width: '70px',
                                    styles: 'text-align:center;',
                                    formatter: formatter.typeCellFormatter
                                }, {
                                    name: nls.unit,
                                    field: 'uom_id',
                                    width: '70px',
                                    styles: 'text-align:center;',
                                    formatter: formatter.unitIdCellFormatter
                                }, {
                                    name: nls.qty,
                                    field: 'quantity-value',
                                    width: '100px',
                                    styles: 'text-align:right;',
                                    formatter: formatter.formulaNumberCellFormatter
                                }]
                            }
                        });
                    }

                    tabContainer.addChild(new BuildUpQtyGrid({
                        store: store,
                        structure: structure
                    }));

                    if (hasLinkedQty) {
                        tabContainer.addChild(scheduleOfQtyGrid);
                    }

                    baseContainer.addChild(tabContainer);
                    baseContainer.addChild(buildUpSummaryWidget);
                    var container = dijit.byId('postContractStandardBill' + self.billId + '-stackContainer');

                    if (container) {
                        container.addChild(new dojox.layout.ContentPane({
                            title: buildspace.truncateString(item.description, 45) + ' (' + nls.buildUpQuantity + ' - ' + item.uom_symbol + ')',
                            id: 'buildUpQuantityPage-' + item.id,
                            style: "padding:0px;border:0px;",
                            content: baseContainer,
                            grid: hasLinkedQty ? scheduleOfQtyGrid.grid : null,
                            executeScripts: true
                        }));
                        container.selectChild('buildUpQuantityPage-' + item.id);
                    }

                    pb.hide();
                });
            });
        }
    });
});