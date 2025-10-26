define('buildspace/apps/PostContract/VariationOrder/VariationOrderItemContainer',[
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
    "./TopManagementVerifier",
    "./BillDialog",
    "buildspace/apps/ProjectBuilder/BillManager/ScheduleOfQuantityGrid",
    "buildspace/apps/Attachment/UploadAttachmentContainer",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, lang, currency, number, when, aspect, TabContainer, ContentPane, VariationOrderGrid, BuildUpQuantityGrid, BuildUpQuantitySummary, VariationOrderClaim, TopManagementVerifier, BillDialog, ScheduleOfQuantityGrid, UploadAttachmentContainer, GridFormatter, nls ){

    var CustomFormatter = {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if (item.is_from_rfv[0]){
                cell.customClasses.push('requestForVariationItemCell');
            }

            return parseInt(rowIdx)+1;
        },
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
        }
    };

    return declare('buildspace.apps.PostContract.VariationOrder.VariationOrderItemContainer', TabContainer, {
        pageId: 'page-00',
        style: "margin:0;padding:0;border:none;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: null,
        gridOpts: {},
        project: null,
        nested: true,
        variationOrder: null,
        borderContainerWidget: null,
        type: null,
        locked: false,
        claimCertificate: null,
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {
                variationOrder: this.variationOrder,
                project: this.project,
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
                });
            var claimQueryContent = {id: this.variationOrder.id};
            if(this.claimCertificate) claimQueryContent['claimRevision'] = this.claimCertificate.post_contract_claim_revision_id;
            var claimQuery = dojo.xhrGet({
                url: "variationOrder/getClaimStatus",
                content: claimQueryContent,
                handleAs: "json"
            });

            when(unitQuery, function(uom){
                pb.show().then(function(){
                    claimQuery.then(function(status){
                        pb.hide();
                        self.createVariationOrderItemGrid(uom, status, true);
                        self.createVariationOrderClaimGrid(status);
                        self.createTopManagementVerifierForm(self.variationOrder._csrf_token);
                    });
                });
            });

            var container = dijit.byId('variationOrder-'+this.project.id+'-stackContainer');

            if(container){
                container.addChild(new dojox.layout.ContentPane({
                    title: buildspace.truncateString(this.stackContainerTitle, 45),
                    id: this.pageId,
                    content: this,
                    executeScripts: true
                }));
                container.selectChild(this.pageId);
            }
        },
        createVariationOrderItemGrid: function(uom, claimStatus, addToTabContainer){
            var stackPane = dijit.byId('variationOrderItems_tab-'+this.project.id+'_'+this.variationOrder.id+'-StackPane');

            if(!stackPane){
                stackPane = new dijit.layout.StackContainer({
                    id: 'variationOrderItems_tab-'+this.project.id+'_'+this.variationOrder.id+'-StackPane',
                    style:'margin:0;padding:0;border:none;width:100%;height:100%;',
                    title: nls.variationOrderItems
                });
            }

            var borderContainer = dijit.byId('variationOrderItems-'+this.project.id+'_'+this.variationOrder.id+'-borderContainer');
            if(borderContainer){
                stackPane.removeChild(borderContainer);
                borderContainer.destroyRecursive();
            }

            var uploadCellFormatter = {
                blueCellFormatter: function (cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx);
                    return item.id >= 0 ? '<span style="color:blue;">'+item.attachment+'</span>' : null;
                }
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
                formatter = new GridFormatter();

            var url = "variationOrder/getVariationOrderItemList/id/"+self.variationOrder.id;
            if(this.claimCertificate) url += '/claimRevision/' + this.claimCertificate.post_contract_claim_revision_id;

            var store = dojo.data.ItemFileWriteStore({
                    url: url,
                    clearOnClose: true
                }),
                structure = {
                    cells: [
                        [{
                            name: 'No',
                            field: 'id',
                            styles: "text-align:center;",
                            width: '30px',
                            formatter: CustomFormatter.rowCountCellFormatter,
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
                            editable: self.variationOrder.can_be_edited[0],
                            cellType: 'buildspace.widget.grid.cells.Textarea',
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.attachment,
                            field: 'attachment',
                            width: '100px',
                            styles: 'text-align:center;',
                            formatter: uploadCellFormatter.blueCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.type,
                            field: 'type',
                            width: '70px',
                            styles: 'text-align:center;',
                            editable: self.variationOrder.can_be_edited[0],
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
                            editable: self.variationOrder.can_be_edited[0],
                            styles: 'text-align:center;',
                            type: 'dojox.grid.cells.Select',
                            options: uom.options,
                            values: uom.values,
                            formatter: formatter.unitIdCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.rate,
                            field: 'reference_rate-value',
                            styles: "text-align:right;",
                            width: '120px',
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: CustomFormatter.currencyCellFormatter,
                            noresize: true,
                        },{
                            name: nls.qty,
                            field: 'reference_quantity-value',
                            styles: "text-align:right;",
                            width: '90px',
                            editable: true,
                            formatter: formatter.numberCellFormatter,
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            noresize: true
                        },{
                            name: nls.total,
                            field: 'reference_amount-value',
                            styles: "text-align:right;",
                            width: '120px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true
                        },{
                            name: nls.rate,
                            field: 'rate-value',
                            styles: "text-align:right;",
                            width: '120px',
                            editable: self.variationOrder.can_be_edited[0],
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: !self.variationOrder.can_be_edited[0] ? formatter.unEditableCurrencyCellFormatter : CustomFormatter.currencyCellFormatter,
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
                            field: 'omission_total',
                            styles: "text-align:right;",
                            width: '120px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true
                        },{
                            name: nls.qty,
                            field: 'addition_quantity-value',
                            styles: "text-align:right;",
                            width: '90px',
                            editable: self.variationOrder.can_be_edited[0],
                            cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: CustomFormatter.additionQtyCellFormatter,
                            noresize: true
                        },{
                            name: nls.total,
                            field: 'addition_total',
                            styles: "text-align:right;",
                            width: '120px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true
                        },{
                            name: nls.nettOmissionAddition,
                            field: 'nett_omission_addition',
                            styles: "text-align:right;",
                            width: '120px',
                            noresize: true,
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            rowSpan: 2
                        },{
                            name: '%',
                            field: 'previous_percentage-value',
                            styles: "text-align:right;",
                            width: '60px',
                            formatter: formatter.unEditablePercentageCellFormatter,
                            noresize: true
                        },{
                            name: nls.qty,
                            field: 'previous_quantity-value',
                            styles: "text-align:right;",
                            width: '90px',
                            formatter: formatter.unEditableNumberCellFormatter,
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
                            name: nls.qty,
                            field: 'current_quantity-value',
                            styles: "text-align:right;",
                            width: '90px',
                            editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? formatter.currencyCellFormatter : formatter.unEditableNumberCellFormatter,
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
                            name: nls.qty,
                            field: 'up_to_date_quantity-value',
                            styles: "text-align:right;",
                            width: '90px',
                            editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? formatter.currencyCellFormatter : formatter.unEditableNumberCellFormatter,
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
                        },{
                            name: nls.remarks,
                            field: 'remarks',
                            width: '500px',
                            editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                            cellType: 'buildspace.widget.grid.cells.Textarea',
                            noresize: true,
                            rowSpan: 2
                        }],
                        [{
                            name: nls.budget,
                            field: 'id',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader",
                            noresize: true,
                            colSpan: 3
                        },{
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
                            colSpan: 3
                        },{
                            name: nls.currentClaim,
                            field: 'id',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader",
                            noresize: true,
                            colSpan: 3
                        },{
                            name: nls.upToDateClaim,
                            field: 'id',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader",
                            noresize: true,
                            colSpan: 3
                        }]
                    ]
                },
                grid = new VariationOrderGrid({
                    project: self.project,
                    variationOrder: self.variationOrder,
                    variationOrderItemContainer: self,
                    type: claimStatus.count > 0 ? 'vo-claims' : 'vo-items',
                    locked: self.locked ? self.locked : (!self.variationOrder.can_be_edited[0]),
                    gridOpts: {
                        store: store,
                        escapeHTMLInData: false,
                        addUrl: 'variationOrder/variationOrderItemAdd',
                        updateUrl: claimStatus.count > 0 ? 'variationOrder/claimItemUpdate' : 'variationOrder/variationOrderItemUpdate',
                        deleteUrl: 'variationOrder/variationOrderItemDelete',
                        indentUrl: 'variationOrder/variationOrderItemIndent',
                        outdentUrl: 'variationOrder/variationOrderItemOutdent',
                        pasteUrl: 'variationOrder/variationOrderItemPaste',
                        structure: structure,
                        onRowDblClick: function(e){
                            var colField = e.cell.field,
                                rowIndex = e.rowIndex,
                                variationOrderItem = this.getItem(rowIndex);
                            if(this.locked || (colField == "omission_quantity-value" && variationOrderItem.has_omission_build_up_quantity[0])){
                                if(((colField == "addition_quantity-value" && variationOrderItem.has_addition_build_up_quantity[0]) || (colField == "omission_quantity-value" && variationOrderItem.has_omission_build_up_quantity[0])) && !isNaN(parseInt(String(variationOrderItem.id))) &&
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

                                        dimensionColumnQuery.then(function(dimensionColumns){
                                            pb.show().then(function(){
                                                self.createBuildUpQuantityContainer(variationOrderItem, dimensionColumns, type, _this.locked);
                                                pb.hide();
                                            });
                                        }, function(err){
                                            //
                                        });
                                    }else{
                                        buildspace.dialog.alert(nls.buildUpQtyAlert, nls.pleaseSetUOM, 90, 380);
                                    }
                                }
                            }

                            if(colField == "total_unit" && !isNaN(parseInt(String(variationOrderItem.id))) &&
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
                                    locked: !self.variationOrder.can_be_edited[0],
                                    variationOrderItemGrid: this
                                }).show();
                            }
                        },
                        editableCellDblClick: function(e) {
                            var colField = e.cell.field,
                                rowIndex = e.rowIndex,
                                variationOrderItem = this.getItem(rowIndex);

                            if(colField == "addition_quantity-value" && !isNaN(parseInt(String(variationOrderItem.id))) && (variationOrderItem.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM ||
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

                                    dimensionColumnQuery.then(function(dimensionColumns){
                                        pb.show().then(function(){
                                            self.createBuildUpQuantityContainer(variationOrderItem, dimensionColumns, 'addition', false);
                                            pb.hide();
                                        });
                                    }, function(err){
                                    });
                                }else{
                                    buildspace.dialog.alert(nls.buildUpQtyAlert, nls.pleaseSetUOM, 90, 380);
                                }
                            }
                        }
                    }
                });

            var gridBorderContainer = this.makeItemGridContainer(grid, nls.variationOrderItems)

            stackPane.addChild(gridBorderContainer);
            this.borderContainerWidget = gridBorderContainer;

            if(addToTabContainer){
                this.addChild(stackPane);
            }
        },
        createBuildUpQuantityContainer: function(item, dimensionColumns, type, locked){
            locked = type == "omission" ? true : locked;
            var self = this, scheduleOfQtyGrid,
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
                    url: "variationOrder/getLinkInfo/id/"+item.id+"/t/"+type,
                    handleAs: "json"
                }),
                store = new dojo.data.ItemFileWriteStore({
                    url:"variationOrder/getBuildUpQuantityItemList/id/"+item.id+"/type/"+type,
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

            scheduleOfQtyQuery.then(function(linkInfo){
                pb.show().then(function(){
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
                            stackContainerId: 'variationOrderItems-'+self.project.id+'_'+self.variationOrder.id+'-stackContainer',
                            gridOpts: {
                                qtyType: type,
                                buildUpSummaryWidget: buildUpSummaryWidget,
                                store: new dojo.data.ItemFileWriteStore({
                                    url:"variationOrder/getScheduleOfQuantities/id/"+item.id+"/t/"+type,
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
                            addUrl: 'variationOrder/buildUpQuantityItemAdd',
                            updateUrl: 'variationOrder/buildUpQuantityItemUpdate',
                            deleteUrl: 'variationOrder/buildUpQuantityItemDelete',
                            pasteUrl: 'variationOrder/buildUpQuantityItemPaste',
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

                    var container = dijit.byId('variationOrderItems-'+self.project.id+'_'+self.variationOrder.id+'-stackContainer');
                    if(container){
                        container.addChild(new dojox.layout.ContentPane( {
                            title: buildspace.truncateString(item.description, 60)+' ('+nls.buildUpQuantity+' - '+item.uom_symbol+')',
                            id: 'variationOrderBuildUpQuantityPage-'+item.id,
                            style: "padding:0px;border:0px;",
                            content: baseContainer,
                            grid: scheduleOfQtyGrid ? scheduleOfQtyGrid.grid : null,
                            executeScripts: true
                        }));
                        container.selectChild('variationOrderBuildUpQuantityPage-'+item.id);
                    }

                    pb.hide();
                });
            });
        },
        createVariationOrderClaimGrid: function(status){
            this.addChild(
                new VariationOrderClaim({
                    title: nls.claimRevisions,
                    project: this.project,
                    variationOrder: this.variationOrder,
                    variationOrderItemContainer: this,
                    locked: this.locked,
                    claimCertificate: this.claimCertificate,
                    claimStatus: status
                })
            );
        },
        createTopManagementVerifierForm: function() {
            this.addChild(
                new TopManagementVerifier({
                    title: nls.topManagementVerifiers,
                    project: this.project,
                    variationOrder: this.variationOrder,
                    locked: this.locked,
                })
            );
        },
        makeItemGridContainer: function(content, title){
            var id = this.project.id+'_'+this.variationOrder.id,
                stackContainer = dijit.byId('variationOrderItems-'+id+'-stackContainer');

            if(stackContainer){
                stackContainer.destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:none;',
                region: "center",
                id: 'variationOrderItems-'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'variationOrderItems-'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = dijit.byId('variationOrderItems-'+id+'-borderContainer');
            if(borderContainer){
                borderContainer.destroyRecursive(true);
            }

            borderContainer = new dijit.layout.BorderContainer({
                id: 'variationOrderItems-'+id+'-borderContainer',
                style:"padding:0;margin:0;width:100%;height:100%;border:none;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('variationOrderItems-'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('variationOrderItems-'+id+'-stackContainer');
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
        },
        addUploadAttachmentContainer: function(item){
            var self = this;
            var id = 'project-'+self.project.id+'-uploadAttachment';
            var container = dijit.byId(id);

            if(container){
                this.borderContainerWidget.removeChild(container);
                container.destroy();
            }

            container = new UploadAttachmentContainer({
                id: id,
                region: 'bottom',
                item: item,
                disableEditing: ! item.can_be_edited[0],
                style:"padding:0;margin:0;border:none;width:100%;height:40%;"
            });

            this.borderContainerWidget.addChild(container);

            return container;
        }
    });
});
