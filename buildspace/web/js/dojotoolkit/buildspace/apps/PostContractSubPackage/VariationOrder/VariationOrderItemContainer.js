define('buildspace/apps/PostContractSubPackage/VariationOrder/VariationOrderItemContainer',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/currency',
    'dojo/number',
    "dojo/when",
    'dojo/aspect',
    "dijit/layout/TabContainer",
    "dijit/layout/ContentPane",
    "./VariationOrderGrid",
    "./buildUpQuantityGrid",
    "./buildUpQuantitySummary",
    "./VariationOrderClaim",
    "./BillDialog",
    "buildspace/apps/ProjectBuilder/BillManager/ScheduleOfQuantityGrid",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, lang, currency, number, when, aspect, TabContainer, ContentPane, VariationOrderGrid, BuildUpQuantityGrid, BuildUpQuantitySummary, VariationOrderClaim, BillDialog, ScheduleOfQuantityGrid, GridFormatter, nls ){

    var CustomFormatter = {
        totalUnitCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                cell.customClasses.push('disable-cell');

                cellValue = '&nbsp;';
            } else if(item.bill_item_id[0] > 0 || !item.can_be_edited[0]){
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        currencyCellFormatter: function(cellValue, rowIdx, cell){
            var value = number.parse(cellValue),
                item = this.grid.getItem(rowIdx);

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = currency.format(value);
                cellValue = value >= 0 ? cellValue : '<span style="color:#FF0000">'+cellValue+'</span>';
            }

            if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                cell.customClasses.push('disable-cell');
            } else if(item.bill_item_id[0] > 0){
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        additionQtyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                formattedValue = "&nbsp;";

            if(!item.can_be_edited[0]){
                cell.customClasses.push('disable-cell');
            }

            if(!isNaN(value) && value != 0 && value != null){
                formattedValue = number.format(value, {places: 2});
            }

            if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                cell.customClasses.push('disable-cell');
            }

            if(item['has_addition_build_up_quantity'] != undefined && item['has_addition_build_up_quantity'][0]){
                return '<span style="color:#0000FF;"><b>'+formattedValue+'</b></span>';
            }else{
                return value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }
        },
        additionTotalCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                formattedValue = "&nbsp;";

            var total = item.id > 0 ? value * number.parse(item['rate-value'][0]) * number.parse(item.total_unit[0]) : 0;

            if(!isNaN(total) && total != 0){
                formattedValue = currency.format(total);
            }

            cell.customClasses.push('disable-cell');

            return total >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
        },
        omissionQtyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                formattedValue = "&nbsp;";

            cell.customClasses.push('disable-cell');

            if(!isNaN(value) && value != 0 && value != null){
                formattedValue = number.format(value, {places: 2});
            }

            if(item['has_omission_build_up_quantity'] != undefined && item['has_omission_build_up_quantity'][0]){
                return '<span style="color:#0000FF;"><b>'+formattedValue+'</b></span>';
            }else{
                return value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }
        },
        omissionTotalCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                formattedValue = "&nbsp;";

            var total = item.id > 0 ? value * number.parse(item['rate-value'][0]) * number.parse(item.total_unit[0]) : 0;

            if(!isNaN(total) && total != 0){
                formattedValue = currency.format(total);
            }

            cell.customClasses.push('disable-cell');

            return '<span style="color:#FF0000">'+formattedValue+'</span>';
        },
        nettOmissionAdditionCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx), value;

            var omissionTotal = item.id > 0 ? number.parse(item['omission_quantity-value'][0]) * number.parse(item['rate-value'][0]) * number.parse(item.total_unit[0]) : 0;
            var additionTotal = item.id > 0 ? number.parse(item['addition_quantity-value'][0]) * number.parse(item['rate-value'][0]) * number.parse(item.total_unit[0]) : 0;

            cell.customClasses.push('disable-cell');
            value = additionTotal - omissionTotal;

            if(value < 0){
                return '<span style="color:#FF0000">'+currency.format(value)+'</span>';
            }else{
                return value == 0 ? "&nbsp;" : '<span style="color:#42b449;">'+currency.format(value)+'</span>';
            }
        }
    };

    return declare('buildspace.apps.PostContractSubPackage.VariationOrder.VariationOrderItemContainer', TabContainer, {
        pageId: 'page-00',
        style: "margin:0;padding:0;border:none;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: null,
        gridOpts: {},
        subPackage: null,
        nested: true,
        variationOrder: null,
        type: null,
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {
                variationOrder: this.variationOrder,
                subPackage: this.subPackage,
                type: this.type,
                region: "center",
                tabContainerWidget: this
            });

            var unitQuery = dojo.xhrGet({
                    url: "variationOrder/getUnits",
                    handleAs: "json"
                }),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                }),
                claimQuery = dojo.xhrGet({
                    url: "variationOrder/getSpClaimStatus",
                    content: {id: this.variationOrder.id},
                    handleAs: "json"
                });

            pb.show();

            when(unitQuery, function(uom){
                claimQuery.then(function(status){
                    pb.hide();
                    self.createVariationOrderItemGrid(uom, status, true);
                    self.createVariationOrderClaimGrid(status);
                });
            });

            var container = dijit.byId('SP_variationOrder-'+self.subPackage.id+'-stackContainer');

            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(self.stackContainerTitle, 45),
                    id: this.pageId,
                    content: this,
                    executeScripts: true
                },node );
                container.addChild(child);
                container.selectChild(this.pageId);
            }
        },
        createVariationOrderItemGrid: function(uom, claimStatus, addToTabContainer){
            var stackPane = dijit.byId('SP_variationOrderItems_tab-'+this.subPackage.id+'_'+this.variationOrder.id+'-StackPane');

            if(!stackPane){
                stackPane = new dijit.layout.StackContainer({
                    id: 'SP_variationOrderItems_tab-'+this.subPackage.id+'_'+this.variationOrder.id+'-StackPane',
                    style:'margin:0;padding:0;border:none;width:100%;height:100%;',
                    title: nls.variationOrderItems
                });
            }

            var borderContainer = dijit.byId('SP_variationOrderItems-'+this.subPackage.id+'_'+this.variationOrder.id+'-borderContainer');
            if(borderContainer){
                stackPane.removeChild(borderContainer);
                borderContainer.destroyRecursive();
            }

            var self = this,
                hierarchyTypes = {
                    options: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT
                    ],
                    values: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM
                    ]
                },
                formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "variationOrder/getSpVariationOrderItemList/id/"+self.variationOrder.id,
                    clearOnClose: true
                }),
                structure = {
                    cells: [
                        [{
                            name: 'No',
                            field: 'id',
                            styles: "text-align:center;",
                            width: '30px',
                            formatter: formatter.rowCountCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.billReference,
                            field: 'bill_ref',
                            styles: "text-align:center; color:red;",
                            width: '80px',
                            formatter: formatter.unEditableCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.totalUnit,
                            field: 'total_unit',
                            styles: "text-align:center;",
                            width: '70px',
                            formatter: CustomFormatter.totalUnitCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.description,
                            field: 'description',
                            width: '500px',
                            editable: self.variationOrder.is_approved[0] != "true",
                            cellType: 'buildspace.widget.grid.cells.Textarea',
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.type,
                            field: 'type',
                            width: '70px',
                            styles: 'text-align:center;',
                            editable: self.variationOrder.is_approved[0] != "true",
                            type: 'dojox.grid.cells.Select',
                            options: hierarchyTypes.options,
                            values: hierarchyTypes.values,
                            formatter: formatter.typeCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.unit,
                            field: 'uom_id',
                            width: '70px',
                            editable: self.variationOrder.is_approved[0] != "true",
                            styles: 'text-align:center;',
                            type: 'dojox.grid.cells.Select',
                            options: uom.options,
                            values: uom.values,
                            formatter: formatter.unitIdCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.rate,
                            field: 'rate-value',
                            styles: "text-align:right;",
                            width: '120px',
                            editable: self.variationOrder.is_approved[0] != "true",
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: self.variationOrder.is_approved[0] == "true" ? formatter.unEditableCurrencyCellFormatter : CustomFormatter.currencyCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.qty,
                            field: 'omission_quantity-value',
                            styles: "text-align:right;",
                            width: '90px',
                            formatter: CustomFormatter.omissionQtyCellFormatter,
                            noresize: true
                        },{
                            name: nls.total,
                            field: 'omission_quantity-value',
                            styles: "text-align:right;",
                            width: '120px',
                            formatter: CustomFormatter.omissionTotalCellFormatter,
                            noresize: true
                        },{
                            name: nls.qty,
                            field: 'addition_quantity-value',
                            styles: "text-align:right;",
                            width: '90px',
                            editable: self.variationOrder.is_approved[0] != "true",
                            cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: CustomFormatter.additionQtyCellFormatter,
                            noresize: true
                        },{
                            name: nls.total,
                            field: 'addition_quantity-value',
                            styles: "text-align:right;",
                            width: '120px',
                            formatter: CustomFormatter.additionTotalCellFormatter,
                            noresize: true
                        },{
                            name: nls.nettOmissionAddition,
                            field: 'id',
                            styles: "text-align:right;",
                            width: '120px',
                            noresize: true,
                            formatter: CustomFormatter.nettOmissionAdditionCellFormatter,
                            rowSpan: 2
                        },{
                            name: '%',
                            field: 'previous_percentage-value',
                            styles: "text-align:right;",
                            width: '60px',
                            formatter: formatter.unEditablePercentageCellFormatter,
                            noresize: true
                        },{
                            name: nls.amount,
                            field: 'previous_amount-value',
                            styles: "text-align:right;",
                            width: '120px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true
                        },{
                            name: '%',
                            field: 'current_percentage-value',
                            styles: "text-align:right;",
                            width: '60px',
                            editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? formatter.editablePercentageCellFormatter : formatter.unEditablePercentageCellFormatter,
                            noresize: true
                        },{
                            name: nls.amount,
                            field: 'current_amount-value',
                            styles: "text-align:right;",
                            width: '120px',
                            editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? formatter.currencyCellFormatter : formatter.unEditableCurrencyCellFormatter,
                            noresize: true
                        },{
                            name: '%',
                            field: 'up_to_date_percentage-value',
                            styles: "text-align:right;",
                            width: '60px',
                            editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? formatter.editablePercentageCellFormatter : formatter.unEditablePercentageCellFormatter,
                            noresize: true
                        },{
                            name: nls.amount,
                            field: 'up_to_date_amount-value',
                            styles: "text-align:right;",
                            width: '120px',
                            editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? formatter.currencyCellFormatter : formatter.unEditableCurrencyCellFormatter,
                            noresize: true
                        }],
                        [{
                            name: nls.omission,
                            field: 'id',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader",
                            noresize: true,
                            colSpan: 2
                        },{
                            name: nls.addition,
                            field: 'id',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader",
                            noresize: true,
                            colSpan: 2
                        },{
                            name: nls.previousClaim,
                            field: 'id',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader",
                            noresize: true,
                            colSpan: 2
                        },{
                            name: nls.currentClaim,
                            field: 'id',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader",
                            noresize: true,
                            colSpan: 2
                        },{
                            name: nls.upToDateClaim,
                            field: 'id',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader",
                            noresize: true,
                            colSpan: 2
                        }]
                    ]
                },
                grid = new VariationOrderGrid({
                    subPackage: self.subPackage,
                    variationOrder: self.variationOrder,
                    type: claimStatus.count > 0 ? 'vo-claims' : 'vo-items',
                    locked: self.variationOrder.is_approved[0] == "true",
                    gridOpts: {
                        store: store,
                        escapeHTMLInData: false,
                        addUrl: 'variationOrder/spVariationOrderItemAdd',
                        updateUrl: claimStatus.count > 0 ? 'variationOrder/spClaimItemUpdate' : 'variationOrder/spVariationOrderItemUpdate',
                        deleteUrl: 'variationOrder/spVariationOrderItemDelete',
                        indentUrl: 'variationOrder/spVariationOrderItemIndent',
                        outdentUrl: 'variationOrder/spVariationOrderItemOutdent',
                        pasteUrl: 'variationOrder/spVariationOrderItemPaste',
                        structure: structure,
                        onRowDblClick: function(e){
                            var colField = e.cell.field,
                                rowIndex = e.rowIndex,
                                variationOrderItem = this.getItem(rowIndex);
                            if(this.locked || (colField == "omission_quantity-value" && variationOrderItem.has_omission_build_up_quantity[0])){
                                if(((colField == "addition_quantity-value" && variationOrderItem.has_addition_build_up_quantity[0]) || (colField == "omission_quantity-value" && variationOrderItem.has_omission_build_up_quantity[0])) && variationOrderItem.id > 0 &&
                                    (variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM ||
                                        variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR ||
                                        variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL ||
                                        variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED)){
                                    if(variationOrderItem.uom_id[0] > 0){
                                        var _this = this,
                                            dimensionColumnQuery = dojo.xhrGet({
                                                url: "billBuildUpQuantity/getDimensionColumnStructure",
                                                content: {uom_id: variationOrderItem.uom_id[0]},
                                                handleAs: "json"
                                            }),
                                            pb = buildspace.dialog.indeterminateProgressBar({
                                                title:nls.pleaseWait+'...'
                                            }),
                                            type = colField == "addition_quantity-value" ? "addition" : "omission";
                                        pb.show();
                                        dimensionColumnQuery.then(function(dimensionColumns){
                                            self.createBuildUpQuantityContainer(variationOrderItem, dimensionColumns, type, _this.locked);
                                            pb.hide();
                                        }, function(err){
                                            pb.hide();
                                        });
                                    }else{
                                        buildspace.dialog.alert(nls.buildUpQtyAlert, nls.pleaseSetUOM, 60, 300);
                                    }
                                }
                            }

                            if(colField == "total_unit" && variationOrderItem.id > 0 &&
                                (variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM ||
                                    variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR ||
                                    variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL ||
                                    variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED ||
                                    variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY ||
                                    variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM ||
                                    variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT ||
                                    variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE)){
                                BillDialog({
                                    variationOrderItem: variationOrderItem,
                                    variationOrder: self.variationOrder,
                                    type: 'update_total_unit',
                                    locked: self.variationOrder.is_approved[0] == "true",
                                    variationOrderItemGrid: this
                                }).show();
                            }
                        },
                        editableCellDblClick: function(e) {
                            var colField = e.cell.field,
                                rowIndex = e.rowIndex,
                                variationOrderItem = this.getItem(rowIndex);

                            if(colField == "addition_quantity-value" && variationOrderItem.id > 0 && (variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM ||
                                variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR ||
                                variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL ||
                                variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED)){
                                if(variationOrderItem.uom_id[0] > 0){
                                    var dimensionColumnQuery = dojo.xhrGet({
                                        url: "billBuildUpQuantity/getDimensionColumnStructure",
                                        content: {uom_id: variationOrderItem.uom_id[0]},
                                        handleAs: "json"
                                    });
                                    var pb = buildspace.dialog.indeterminateProgressBar({
                                        title:nls.pleaseWait+'...'
                                    });
                                    pb.show();
                                    dimensionColumnQuery.then(function(dimensionColumns){
                                        self.createBuildUpQuantityContainer(variationOrderItem, dimensionColumns, 'addition', false);
                                        pb.hide();
                                    }, function(err){
                                        pb.hide();
                                    });
                                }else{
                                    buildspace.dialog.alert(nls.buildUpQtyAlert, nls.pleaseSetUOM, 60, 300);
                                }
                            }
                        }
                    }
                });

            stackPane.addChild(this.makeItemGridContainer(grid, nls.variationOrderItems));

            if(addToTabContainer){
                this.addChild(stackPane);
            }
        },
        createBuildUpQuantityContainer: function(item, dimensionColumns, type, locked){
            locked = type == "omission" ? true : locked;
            var self = this,
                scheduleOfQtyGrid,
                baseContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:100%;height:100%;border:0px;outline:none;",
                    gutters: false
                }),
                tabContainer = new dijit.layout.TabContainer({
                    nested: true,
                    style: "padding:0;border:none;margin:0;width:100%;height:100%;",
                    region: 'center'
                }),
                formatter = new GridFormatter(),
                scheduleOfQtyQuery = dojo.xhrGet({
                    url: "variationOrder/getSpLinkInfo/id/"+item.id+"/t/"+type,
                    handleAs: "json"
                }),
                store = new dojo.data.ItemFileWriteStore({
                    url:"variationOrder/getSpBuildUpQuantityItemList/id/"+item.id+"/type/"+type,
                    clearOnClose: true
                }),
                sign = {
                    options: [
                        buildspace.constants.SIGN_POSITIVE_TEXT,
                        buildspace.constants.SIGN_NEGATIVE_TEXT
                    ],
                    values: [
                        buildspace.constants.SIGN_POSITIVE,
                        buildspace.constants.SIGN_NEGATIVE
                    ]
                },
                hasLinkedQty = false,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show();

            scheduleOfQtyQuery.then(function(linkInfo){
                var structure = [{
                    name: 'No',
                    field: 'id',
                    styles: "text-align:center;",
                    width: '30px',
                    formatter: formatter.rowCountCellFormatter
                }, {
                    name: nls.description,
                    field: 'description',
                    width: 'auto',
                    editable: !locked,
                    cellType: 'buildspace.widget.grid.cells.Textarea'
                },{
                    name: nls.factor,
                    field: 'factor-value',
                    width:'100px',
                    styles:'text-align:right;',
                    editable: !locked,
                    cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                    formatter: formatter.formulaNumberCellFormatter
                }];

                dojo.forEach(dimensionColumns, function(dimensionColumn){
                    var column = {
                        name: dimensionColumn.title,
                        field: dimensionColumn.field_name,
                        width:'100px',
                        styles:'text-align:right;',
                        editable: !locked,
                        cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: formatter.formulaNumberCellFormatter
                    };
                    structure.push(column);
                });

                structure.push({ // total column
                    name: nls.total,
                    field: 'total',
                    width:'100px',
                    styles:'text-align:right;',
                    formatter: formatter.numberCellFormatter
                });

                structure.push({
                    name: nls.sign,
                    field: 'sign',
                    width: '70px',
                    styles: 'text-align:center;',
                    editable: true,
                    cellType: 'dojox.grid.cells.Select',
                    options: sign.options,
                    values: sign.values,
                    formatter: formatter.signCellFormatter
                });

                var buildUpSummaryWidget = new BuildUpQuantitySummary({
                    itemId: item.id,
                    type: type,
                    container: baseContainer,
                    hasLinkedQty: linkInfo.has_linked_qty,
                    _csrf_token: item._csrf_token,
                    locked: locked
                });

                if(linkInfo.has_linked_qty){
                    hasLinkedQty = true;
                    scheduleOfQtyGrid = ScheduleOfQuantityGrid({
                        title: nls.scheduleOfQuantities,
                        BillItem: item,
                        disableEditingMode: true,
                        stackContainerId: 'SP_variationOrderItems-'+self.subPackage.id+'_'+self.variationOrder.id+'-stackContainer',
                        gridOpts: {
                            qtyType: type,
                            buildUpSummaryWidget: buildUpSummaryWidget,
                            store: new dojo.data.ItemFileWriteStore({
                                url:"variationOrder/getSpScheduleOfQuantities/id/"+item.id+"/t/"+type,
                                clearOnClose: true
                            }),
                            structure: [
                                {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                                {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                                {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                                {name: nls.qty, field: 'quantity-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaNumberCellFormatter}
                            ]
                        }
                    });
                }

                tabContainer.addChild(new BuildUpQuantityGrid({
                    title: nls.manualQtyItems,
                    region: 'center',
                    VariationOrderItem: item,
                    type: type,
                    locked: locked,
                    gridOpts: {
                        addUrl: 'variationOrder/spBuildUpQuantityItemAdd',
                        updateUrl: 'variationOrder/spBuildUpQuantityItemUpdate',
                        deleteUrl: 'variationOrder/spBuildUpQuantityItemDelete',
                        pasteUrl: 'variationOrder/spBuildUpQuantityItemPaste',
                        store: store,
                        structure: structure,
                        buildUpSummaryWidget: buildUpSummaryWidget
                    }
                }));

                if(hasLinkedQty){
                    tabContainer.addChild(scheduleOfQtyGrid);
                }

                baseContainer.addChild(tabContainer);
                baseContainer.addChild(buildUpSummaryWidget);

                var container = dijit.byId('SP_variationOrderItems-'+self.subPackage.id+'_'+self.variationOrder.id+'-stackContainer');
                if(container){
                    var node = document.createElement("div");
                    var child = new dojox.layout.ContentPane( {
                            title: buildspace.truncateString(item.description, 45)+' ('+nls.buildUpQuantity+' - '+item.uom_symbol+')',
                            id: 'SP_variationOrderBuildUpQuantityPage-'+item.id,
                            style: "padding:0px;border:0px;",
                            content: baseContainer,
                            executeScripts: true },
                        node );
                    container.addChild(child);
                    container.selectChild('SP_variationOrderBuildUpQuantityPage-'+item.id);
                }

                pb.hide();
            });
        },
        createVariationOrderClaimGrid: function(status){
            this.addChild(new ContentPane({
                style: "margin:0;padding:0;border:none;width:100%;height:100%;",
                title: nls.claimRevisions,
                content: new VariationOrderClaim({
                    variationOrder: this.variationOrder,
                    variationOrderItemContainer: this,
                    claimStatus: status
                })
            }));
        },
        makeItemGridContainer: function(content, title){
            var id = this.subPackage.id+'_'+this.variationOrder.id,
                stackContainer = dijit.byId('SP_variationOrderItems-'+id+'-stackContainer');

            if(stackContainer){
                stackContainer.destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:none;',
                region: "center",
                id: 'SP_variationOrderItems-'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'SP_variationOrderItems-'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = dijit.byId('SP_variationOrderItems-'+id+'-borderContainer');
            if(borderContainer){
                borderContainer.destroyRecursive(true);
            }

            borderContainer = new dijit.layout.BorderContainer({
                id: 'SP_variationOrderItems-'+id+'-borderContainer',
                style:"padding:0;margin:0;width:100%;height:100%;border:none;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('SP_variationOrderItems-'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('SP_variationOrderItems-'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){

                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();
                            index = index + 1;
                        }

                        if(page.grid){
                            var selectedIndex = page.grid.selection.selectedIndex;

                            page.grid.store.save();
                            page.grid.store.close();

                            var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                                handle.remove();

                                if(selectedIndex > -1){
                                    this.scrollToRow(selectedIndex);
                                    this.selection.setSelected(selectedIndex, true);
                                }
                            });

                            page.grid.sort();
                        }
                    }
                }
            });

            return borderContainer;
        }
    });
});