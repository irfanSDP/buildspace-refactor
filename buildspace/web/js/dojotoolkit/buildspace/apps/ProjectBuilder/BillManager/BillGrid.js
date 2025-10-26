define('buildspace/apps/ProjectBuilder/BillManager/BillGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dijit/Menu",
    'dojo/number',
    "dijit/CheckedMenuItem",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    './importItemDialog',
    './ImportQtyDialog',
    './importProjectItemDialog',
    './importElementDialog',
    './editBillNoteDialog',
    './PrintBillDialog',
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    'buildspace/widget/grid/cells/Select',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ProjectBuilder',
    'dojo/on',
    'dojox/grid/_Grid',
    "dojox/widget/PlaceholderMenuItem",
    'buildspace/widget/grid/cells/TextBox'
], function(declare, lang, array, domAttr, Menu, number, MenuCheckedItem, MenuPlugin, Selector, Rearrange, FormulatedColumn, ImportItemDialog, ImportQtyDialog, ImportProjectItemDialog, ImportElementDialog, EditBillNoteDialog, PrintBillDialog, evt, keys, focusUtil, html, xhr, PopupMenuItem, MenuSeparator, Select, Textarea, FormulaTextBox, GridFormatter, nls, on){

    var BillGrid = declare('buildspace.apps.ProjectBuilder.BillManager.BillGrid', dojox.grid.EnhancedGrid, {
        type: null,
        billId: -1,
        elementId: 0,
        itemId: -1,
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        keepSelection: true,
        rowSelector: '0px',
        addUrl: null,
        updateUrl: null,
        rowUpdateUrl: null,
        deleteUrl: null,
        deleteRateUrl: null,
        deleteQuantityUrl: null,
        pasteUrl: null,
        indentUrl: null,
        outdentUrl: null,
        headerMenu: null,
        typeColumns: null,
        markupSettings: null,
        parentGrid: null,
        elementGridStore: null,
        currentBQAddendumId: -1,
        currentBillLockedStatus: false,
        currentGridType: 'element',
        ORIGINAL_BILL_VERSION: 0,
        constructor:function(args){
            this.type                    = args.type;
            this.hierarchyTypes          = args.hierarchyTypes;
            this.hierarchyTypesForHead   = args.hierarchyTypesForHead;
            this.unitOfMeasurements      = args.unitOfMeasurements;
            this.columnGroup             = args.columnGroup;
            this.typeColumns             = args.typeColumns;
            this.markupSettings          = args.markupSettings;
            this.currencySetting         = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            this.currentBQAddendumId     = args.currentBQAddendumId;
            this.currentBillLockedStatus = args.currentBillLockedStatus;
            this.currentGridType         = args.currentGridType;

            var formatter = this.formatter = new GridFormatter();

            if(this.type != 'tree'){
                this.typeColumnChildren = [
                    {name: '% '+nls.job, field_name: 'total', width: '60px', styles: "text-align:right;", editable: false, formatter: formatter.elementTypeJobPercentageCellFormatter},
                    {name: nls.costPerMetreSquare, field_name: 'total_cost', width: '100px', styles: "text-align:right;", editable: false, formatter: formatter.unEditableCurrencyCellFormatter},
                    {name: nls.total+'/'+nls.unit, field_name: 'total_per_unit', width: '100px', styles: "text-align:right;", editable: false, formatter: formatter.elementTotalPerUnitCellFormatter},
                    {name: nls.estimated+' '+nls.costPerMetreSquare, field_name: 'estimated_cost_per_metre_square', width: '100px', styles: "text-align:right;", cellType:'buildspace.widget.grid.cells.TextBox', editable: true, formatter: formatter.numberCellFormatter },
                    {name: nls.estimated+' '+nls.totalCost, field_name: 'estimated_cost', width: '100px', styles: "text-align:right;", formatter: formatter.unEditableCurrencyCellFormatter }
                ];

            }else{
                var includeOptions = {
                    options: [nls.yesCapital, nls.noCapital],
                    values: ['true','false']
                };

                this.typeColumnChildren = [
                    {name: nls.include, field_name: 'include', width: '60px', styles: "text-align:center;", editable: true, cellType: 'dojox.grid.cells.Select', options: includeOptions.options, values: includeOptions.values, formatter: formatter.yesNoCellFormatter},
                    {name: nls.qty+'/'+nls.unit, field_name: 'quantity_per_unit-value', width: '70px', styles: "text-align:right;", editable: true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.billQuantityCellFormatter},
                    {name: nls.qty+'/'+nls.unit+' (2)', field_name: 'quantity_per_unit_remeasurement-value', width: '70px', styles: "text-align:right;", editable: true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.billQuantityCellFormatter},
                    {name: nls.difference+' (%)', field_name: 'quantity_per_unit_difference', width: '85px', styles: "text-align:right;color:blue;", editable: false, formatter: formatter.unEditablePercentageCellFormatter},
                    {name: nls.total+'/'+nls.unit, field_name: 'total_per_unit', width: '100px', styles: "text-align:right;", editable: false, formatter: formatter.itemTotalPerUnitCellFormatter}
                ];
            }

            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});

            this.setColumnStructure();

            this.createHeaderCtxMenu();
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            this.on("RowContextMenu", function(e){
                self.selection.clear();
                self.selection.setSelected(e.rowIndex, true);
                self.setupContextMenuAndToolbarButtonRestriction(e);
                self.contextMenu(e);
            }, true);

            this.on('RowClick', function(e){
                self.setupContextMenuAndToolbarButtonRestriction(e);
            });
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;
            var attrNameParsed = inAttrName.replace("-value","");//for any formulated column
            if(inAttrName.indexOf("-value") !== -1){
                val = this.formulatedColumn.convertRowIndexToItemId(val, rowIdx);
            }

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    attr_name: attrNameParsed,
                    val: val,
                    bill_id : self.billId,
                    _csrf_token: item._csrf_token ? item._csrf_token : null,
                    currentBQAddendumId: this.currentBQAddendumId
                }, url = this.updateUrl;

                if(item.id==buildspace.constants.GRID_LAST_ROW){
                    var prevItem = rowIdx > 0 ? self.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0,
                        relation_id: item.relation_id
                    });
                    url = this.addUrl;
                }

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }

                    if(data.affected_nodes.hasOwnProperty('affected_bill_items')){
                        dojo.forEach(data.affected_nodes['affected_bill_items'], function(node){
                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem){
                                for(var property in node){
                                    if(affectedItem.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(affectedItem, property, node[property]);
                                    }
                                }
                            }});
                        });
                    }

                    if(data.affected_nodes.hasOwnProperty('affected_bill_item_type_references')){
                        dojo.forEach(data.affected_nodes['affected_bill_item_type_references'], function(node){
                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem){
                                for(var property in node){
                                    if(affectedItem.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(affectedItem, property, node[property]);
                                    }
                                }
                            }});
                        });
                    }

                    dojo.forEach(data.affected_nodes, function(node){
                        store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem){
                            for(var property in node){
                                if(affectedItem.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                    store.setValue(affectedItem, property, node[property]);
                                }
                            }
                        }});
                    });
                    if(self.type != 'tree'){
                        dojo.forEach(data.other_elements, function(node){
                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(element){
                                for(var property in node){
                                    if(element.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(element, property, node[property]);
                                    }
                                }
                            }});
                        });
                    }
                    store.save();
                };

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
                            if(inAttrName == 'type' && buildspace.apps.ProjectBuilder.NoMarkupItemTypes.includes(val)) self.refreshGrid();
                            var cell = self.getCellByField(inAttrName);
                            window.setTimeout(function() {
                                self.focus.setFocusIndex(rowIdx, cell.index);
                            }, 10);
                            pb.hide();
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                };

                if(item[attrNameParsed+'-has_build_up'] != undefined && item[attrNameParsed+'-has_build_up'][0]){
                    var onYes = function(){
                        pb.show().then(function(){
                            dojo.xhrPost(xhrArgs);
                        });
                    };

                    var content = '<div>'+nls.detachAllBuildUpAndLink+'</div>';
                    buildspace.dialog.confirm(nls.confirmation,content,60,280, onYes);
                    self.doCancelEdit(rowIdx);
                }
                else if(inAttrName == 'type' && buildspace.apps.ProjectBuilder.NoMarkupItemTypes.includes(val)){
                    var onYes = function(){
                        pb.show().then(function(){
                            dojo.xhrPost(xhrArgs);
                        });
                    };

                    var content = '<div>'+nls.changeItemTypeRemoveMarkup+'</div>';
                    buildspace.dialog.confirm(nls.confirmation,content,60,280, onYes);
                    self.doCancelEdit(rowIdx);
                }else{
                    pb.show().then(function(){
                        dojo.xhrPost(xhrArgs);
                    });
                    self.inherited(arguments);
                }
            }else{
                self.inherited(arguments);
            }
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;

            if(inCell != undefined){
                var item = this.getItem(inRowIndex),
                    field = inCell.field;
                if(this.type=='tree'){
                    // if current bill has been set to locked status, don't allow user input
                    // into selected column
                    if (this.currentBillLockedStatus) {
                        return false;
                    }

                    if(item.id[0] > 0){
                        var str;
                        if((item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N) && field != 'description' && field != 'type' && inCell.editable){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        } else if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && field != 'description' && field != 'type' && inCell.editable){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }else if((item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID) && field == 'description'){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID && (field != 'type' && field != 'uom_id')){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY && field != 'description' && field != 'type' && field != 'rate-value' && field != 'markup_percentage-value' && field != 'markup_amount-value' && field != 'uom_id' && inCell.editable){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE && field == 'rate-value'){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }else if((item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE) && field != 'description' && field != 'type' && field != 'rate-value' && field != 'markup_percentage-value' && field != 'markup_amount-value' && field != 'uom_id' && inCell.editable){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT && field != 'description' && field != 'type' && field != 'markup_percentage-value' && field != 'markup_amount-value' && field != 'uom_id' && inCell.editable){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }

                        if(field.indexOf('-quantity_per_unit-value') > 0){
                            str = field.split('-');
                            if(item[str[0]+'-include'][0]=="false"){
                                window.setTimeout(function() {
                                    self.edit.cancel();
                                    self.focus.setFocusIndex(inRowIndex, inCell.index);
                                }, 10);
                                return;
                            }
                        }
                        if(field.indexOf('-quantity_per_unit_remeasurement-value') > 0){
                            str = field.split('-');
                            if(item[str[0]+'-include'][0]=="false"){
                                window.setTimeout(function() {
                                    self.edit.cancel();
                                    self.focus.setFocusIndex(inRowIndex, inCell.index);
                                }, 10);
                                return;
                            }
                        }

                        if ( field === 'type' ) {
                            var nextItem = self.getItem(inRowIndex+1);

                            if((item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N) && inCell.editable && nextItem !== undefined && item.level[0] < nextItem.level[0]) {
                                inCell.options = self.hierarchyTypesForHead.options;
                                inCell.values  = self.hierarchyTypesForHead.values;
                            } else {
                                inCell.options = self.hierarchyTypes.options;
                                inCell.values  = self.hierarchyTypes.values;
                            }
                        }

                        if(field == 'markup_percentage-value' || field == 'markup_amount-value'){
                            var markupDisabledItemType = [
                                buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE,
                                buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT,
                                buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE,
                                buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID,
                                buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER,
                                buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                            ];

                            if(markupDisabledItemType.indexOf(item.type[0]) >= 0){
                                window.setTimeout(function() {
                                    self.edit.cancel();
                                    self.focus.setFocusIndex(inRowIndex, inCell.index);
                                }, 10);
                                return;
                            }
                        }
                    } else if ( field !== 'description' && field !== 'type' ) {
                        return;
                    }
                }
                else{
                    if(item.id[0] > 0){
                        if((field == 'markup_percentage-value' || field == 'markup_amount-value') && item.markup_disabled[0]){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }
                    }
                }
            }
            return this._canEdit;
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        setColumnStructure: function(){
            var formatter = this.formatter;

            // editable mode for grid
            var editableModeElementGrid = true;

            // if current version is not original bill or is locked then disable editable mode
            if (this.currentBillLockedStatus){
                editableModeElementGrid = false;
            }

            var descriptionWidth = 'auto';

            if(this.typeColumns.length > 1 || this.markupSettings.bill_markup_enabled || this.markupSettings.element_markup_enabled || this.markupSettings.item_markup_enabled){
                descriptionWidth = '580px';
            }

            var hideGrandTotalColumn = true;
            if(this.markupSettings.bill_markup_enabled || this.markupSettings.element_markup_enabled || this.markupSettings.item_markup_enabled){
                hideGrandTotalColumn = false;
            }

            if(this.type == 'tree'){
                var unitOfMeasurements = this.unitOfMeasurements,
                    hierarchyTypes = this.hierarchyTypes;
                var fixedColumns = this.fixedColumns = {
                    noscroll: false,
                    width: 57.8,
                    cells: [
                        [{
                            name: 'No',
                            field: 'id',
                            styles: "text-align:center;",
                            width: '30px',
                            formatter: formatter.rowCountCellFormatter,
                            noresize: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },
                        {
                            name: nls.billReference,
                            field: 'bill_ref',
                            styles: "text-align:center; color: red;",
                            width: '80px',
                            noresize: true,
                            hidden: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },
                        {
                            name: nls.description,
                            field: 'description',
                            width: descriptionWidth,
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.Textarea',
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },{
                            name: nls.type,
                            field: 'type',
                            width: '70px',
                            styles: 'text-align:center;',
                            editable: true,
                            type: 'dojox.grid.cells.Select',
                            options: hierarchyTypes.options,
                            values: hierarchyTypes.values,
                            formatter: formatter.typeCellFormatter,
                            noresize: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },{
                            name: nls.unit,
                            field: 'uom_id',
                            width: '70px',
                            editable: true,
                            styles: 'text-align:center;',
                            type: 'dojox.grid.cells.Select',
                            options: unitOfMeasurements.options,
                            values: unitOfMeasurements.values,
                            formatter: formatter.unitIdCellFormatter,
                            noresize: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },{
                            name: nls.grandTotalQty,
                            field: 'grand_total_quantity',
                            styles: "text-align:right;color:blue;",
                            width: '90px',
                            formatter: formatter.unEditableNumberCellFormatter,
                            noresize: true,
                            hidden: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        }]
                    ]
                };

                var fixedColumnsAfterTypeColumns = [{
                    name: nls.rate,
                    field: 'rate-value',
                    styles: "text-align:right;",
                    width: '75px',
                    editable: true,
                    cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                    formatter: formatter.formulaCurrencyCellFormatter,
                    noresize: true,
                    rowSpan: 2
                },{
                    name: nls.originalGrandTotal,
                    field: 'grand_total',
                    styles: "text-align:right;color:blue;",
                    width: '100px',
                    formatter: formatter.unEditableCurrencyCellFormatter,
                    noresize: true,
                    hidden: hideGrandTotalColumn,
                    rowSpan: 2,
                    showInCtxMenu: true
                }];
                var columnToDisplay = this.constructTypeColumnStructure(fixedColumns);
            }
            else{
                
                var fixedColumns = this.fixedColumns = {
                    noscroll: false,
                    width: '50',
                    cells: [
                        [{
                            name: 'No',
                            field: 'id',
                            styles: "text-align:center;",
                            width: '30px',
                            formatter: formatter.rowCountCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        }, {
                            name: nls.description,
                            field: 'description',
                            width: descriptionWidth,
                            editable: editableModeElementGrid,
                            cellType: 'buildspace.widget.grid.cells.Textarea',
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        }]
                    ]
                },
                fixedColumnsAfterTypeColumns = [{
                    name: nls.grandTotal,
                    field: 'grand_total',
                    styles: "text-align:right;color:blue;",
                    width: '100px',
                    formatter: formatter.unEditableCurrencyCellFormatter,
                    hidden: hideGrandTotalColumn,
                    rowSpan: 2,
                    noresize: true,
                    showInCtxMenu: true
                }];

                var columnToDisplay = this.constructTypeColumnStructure(fixedColumns);
            }

            //Generate After Type Column
            dojo.forEach(fixedColumnsAfterTypeColumns,function(column){
                columnToDisplay.cells[0].push(column);
            });

            if((this.type == "tree" && this.markupSettings.item_markup_enabled) ||
                (this.type != "tree" && this.markupSettings.element_markup_enabled)){
                var markupColumns = [{
                    name: nls.markup+" (%)",
                    field: 'markup_percentage-value',
                    width: '80px',
                    styles: "text-align:right;",
                    editable: true,
                    cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                    formatter: formatter.formulaPercentageCellFormatter,
                    noresize: true
                },{
                    name: nls.markup+" ("+this.currencySetting+")",
                    field: 'markup_amount-value',
                    width: '100px',
                    styles: "text-align:right;",
                    editable: true,
                    cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                    formatter: formatter.formulaCurrencyCellFormatter,
                    noresize: true
                }];

                dojo.forEach(markupColumns,function(column){
                    columnToDisplay.cells[0].push(column);
                });

                var markupColumnName = this.type == 'tree' ? nls.item+" "+nls.markup : nls.element+" "+nls.markup;

                columnToDisplay.cells[1].push({
                    name: markupColumnName,
                    styles: "text-align:center;",
                    headerClasses: "staticHeader",
                    colSpan: markupColumns.length
                });
            }

            if(this.markupSettings.bill_markup_enabled || this.markupSettings.element_markup_enabled || this.markupSettings.item_markup_enabled){
                var markupSummaryColumns = [];

                if(this.type == 'tree'){
                    markupSummaryColumns.push({
                        name: nls.rateAfterMarkup,
                        field: 'rate_after_markup',
                        width: '100px',
                        styles: "text-align:right;",
                        headerClasses: "typeHeader1",
                        formatter: formatter.rateAfterMarkupCellFormatter,
                        noresize: true
                    });
                }

                var markupSummaryFields = [{
                        name: nls.total+' (%) ' +nls.markup,
                        field: this.type == 'tree' ? 'grand_total' : 'original_grand_total',
                        formatter: this.type == 'tree' ? formatter.itemTotalMarkupPercentageCellFormatter : formatter.elementTotalMarkupPercentageCellFormatter
                    },{
                        name: nls.total+' ('+this.currencySetting+') ' +nls.markup,
                        field: this.type == 'tree' ? 'grand_total' : 'original_grand_total' ,
                        formatter: this.type == 'tree' ? formatter.itemTotalMarkupAmountCellFormatter : formatter.elementTotalMarkupAmountCellFormatter
                    },{
                        name: nls.overallTotalAfterMarkup,
                        field: this.type == 'tree' ? 'grand_total_after_markup' : 'overall_total_after_markup',
                        formatter: this.type == 'tree' ? formatter.itemOverallTotalAfterMarkupCellFormatter : formatter.elementOverallTotalAfterMarkupCellFormatter
                    }];

                dojo.forEach(markupSummaryFields, function(field){
                    markupSummaryColumns.push({
                        name: field.name,
                        field: field.field,
                        width: '100px',
                        styles: "text-align:right;",
                        headerClasses: "typeHeader1",
                        formatter: field.formatter,
                        noresize: true
                    });
                });

                if(this.type != 'tree'){
                    markupSummaryColumns.push({
                        name: '% '+nls.job,
                        field: 'overall_total_after_markup',
                        styles: "text-align:right;",
                        headerClasses: "typeHeader1",
                        width: '70px',
                        formatter: formatter.elementJobPercentageCellFormatter,
                        noresize: true
                    });
                }

                dojo.forEach(markupSummaryColumns,function(column){
                    columnToDisplay.cells[0].push(column);
                });

                var markupStr = [];
                if(this.markupSettings.item_markup_enabled){
                    markupStr.push(nls.item);
                }
                if(this.markupSettings.element_markup_enabled){
                    markupStr.push(nls.element);
                }
                if(this.markupSettings.bill_markup_enabled){
                    markupStr.push(nls.bill);
                }

                columnToDisplay.cells[1].push({
                    name: '<div style="font-weight:normal;">('+markupStr.toString()+')</div>'+nls.markupSummary,
                    styles: "text-align:center;",
                    headerClasses: "staticHeader typeHeader1",
                    colSpan: markupSummaryColumns.length
                });
            }

            this.structure = columnToDisplay;
        },
        constructTypeColumnStructure: function(fixedColumns){
            var self = this,
                typeColumns = this.typeColumns,
                typeColumnChildren = this.typeColumnChildren,
                parentCells = [],
                childCells = [];
                var colCount = 0;

            dojo.forEach(typeColumns, function(typeColumn){
                var colspan = (!typeColumn.remeasurement_quantity_enabled && self.type =='tree') ? typeColumnChildren.length-2 : typeColumnChildren.length;
                colCount++;
                //rename Total Cost Name
                if(self.type != 'tree'){

                    if(typeColumn.floor_area_display_metric){
                        typeColumnChildren[1].name = nls.costPerMetreSquare;
                    }else{
                        typeColumnChildren[1].name = nls.costPerSquareFeet;
                    }

                    var hideEstimatedColumn;

                    if(typeColumn.show_estimated_total_cost){
                        hideEstimatedColumn = false;
                    }else{
                        hideEstimatedColumn = true;
                        colspan-= 2;
                    }

                    typeColumnChildren[3].hidden = hideEstimatedColumn;
                    typeColumnChildren[4].hidden = hideEstimatedColumn;
                }

                parentCells.push({
                    name: typeColumn.name + "<br>"+nls.totalUnit+":" + typeColumn.quantity,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader typeHeader"+colCount,
                    colSpan: colspan,
                    headerId: typeColumn.id,
                    hidden: typeColumn.is_hidden
                });

                var field = null;
                for(var i=0;i<typeColumnChildren.length;i++){
                    field = typeColumn.id+'-'+typeColumnChildren[i].field_name;
                    if((!typeColumn.remeasurement_quantity_enabled && self.type =='tree')&&(typeColumnChildren[i].field_name == 'quantity_per_unit_remeasurement-value' || typeColumnChildren[i].field_name=='quantity_per_unit_difference')){
                        continue;
                    }
                    var cellStructure = {
                        field: field,
                        columnType: "typeColumn",
                        billColumnSettingId: typeColumn.id,
                        hidden: typeColumn.is_hidden,
                        headerClasses: "typeHeader"+colCount
                    };
                    lang.mixin(cellStructure, typeColumnChildren[i]);
                    fixedColumns.cells[0].push(cellStructure);
                }
            });

            fixedColumns.cells.push(parentCells);

            return fixedColumns;
        },
        createHeaderCtxMenu: function(){
            if (typeof this.fixedColumns !== 'undefined') {
                var columnGroup = this.fixedColumns.cells[0],
                    self = this,
                    menusObject = {
                        headerMenu: new dijit.Menu()
                    };
                dojo.forEach(columnGroup, function(data, index){
                    if(data.showInCtxMenu){
                        menusObject.headerMenu.addChild(new dijit.CheckedMenuItem({
                            label: data.name,
                            checked: (typeof data.hidden === 'undefined' || data.hidden === false) ? true : false,
                            onChange: function(val){

                                var show = false;

                                if (val){
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
        showHideMergedColumn: function(show, index) {
            this.beginUpdate();
            this.layout.setColumnVisibility(index, show);
            this.endUpdate();
        },
        cutItems: function(){
            this.selectedItem = this.selection.getFirstSelected();
            this.pasteOp = 'cut';
            this.copyCellType = null;
            this.selectedBillColumnSettingId = null;
        },
        copyItems: function(){
            this.selectedItem = this.selection.getFirstSelected();
            this.pasteOp = 'copy';
            this.copyCellType = null;
            this.selectedBillColumnSettingId = null;
        },
        copyCell: function(type, billColumnSettingId){
            this.selectedItem = this.selection.getFirstSelected();
            this.selectedBillColumnSettingId = billColumnSettingId;
            this.copyCellType = type;
            this.pasteOp = null;
        },
        importQty: function(type, billColumnSettingId){
            new ImportQtyDialog({
                billId: this.billId,
                billItem: this.selection.getFirstSelected(),
                billGrid: this,
                qtyType: type,
                targetBillColumnSettingId: billColumnSettingId
            }).show();
        },
        pasteItem: function(rowIndex){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                }),
                store = self.store,
                targetItem = self.selection.getFirstSelected();
            var prevItemId = (targetItem.id == buildspace.constants.GRID_LAST_ROW && rowIndex > 0) ? self.getItem(rowIndex-1).id : 0;

            var xhrArgs = {
                url: this.pasteUrl,
                content: {
                    type: self.pasteOp,
                    target_id: targetItem.id,
                    prev_item_id: prevItemId,
                    id: self.selectedItem.id,
                    currentBQAddendumId: self.currentBQAddendumId,
                    _csrf_token: self.selectedItem._csrf_token
                },
                handleAs: 'json',
                load: function(resp) {
                    var rowsToMove = [];
                    if(resp.success){
                        var children = resp.c;
                        switch (self.pasteOp) {
                            case 'cut':
                                store.fetchItemByIdentity({ 'identity' : resp.data.id,  onItem : function(item){
                                    var firstRowIdx = self.getItemIndex(item);
                                    rowsToMove.push(firstRowIdx);
                                    for(var x=0, len=children.length; x<len; ++x){
                                        store.fetchItemByIdentity({ 'identity' : children[x].id,  onItem : function(child){
                                            var itemIdx = self.getItemIndex(child);
                                            rowsToMove.push(itemIdx);
                                        }});
                                    }
                                    if(rowsToMove.length > 0){
                                        self.rearranger.moveRows(rowsToMove, rowIndex);
                                    }
                                    var selectRowIndex  = (firstRowIdx > rowIndex) ? rowIndex : rowIndex - 1;
                                    self.selectAfterPaste(selectRowIndex, true);
                                }});
                                store.fetchItemByIdentity({ 'identity' : resp.data.id,  onItem : function(item){
                                    for(var property in resp.data){
                                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                            store.setValue(item, property, resp.data[property]);
                                        }
                                    }
                                    store.save();
                                    for(var x=0, len=children.length; x<len; ++x){
                                        store.fetchItemByIdentity({ 'identity' : children[x].id,  onItem : function(child){
                                            for(var property in children[x]){
                                                if(child.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                                    store.setValue(child, property, children[x][property]);
                                                }
                                            }
                                            store.save();
                                        }});
                                    }
                                }});
                                break;
                            case 'copy':

                                var item = store.newItem(resp.data);
                                store.save();
                                var firstRowIdx = self.getItemIndex(item);
                                rowsToMove.push(firstRowIdx);
                                for(var x=0, len=children.length; x<len; ++x){
                                    var child = store.newItem(children[x]);
                                    store.save();
                                    var itemIdx = self.getItemIndex(child);
                                    rowsToMove.push(itemIdx);
                                }
                                if(rowsToMove.length > 0){
                                    self.rearranger.moveRows(rowsToMove, rowIndex);
                                    self.selectAfterPaste(rowIndex, false);
                                }

                                break;
                            default:
                                break;
                        }
                    }
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

            pb.show().then(function(){
                dojo.xhrPost(xhrArgs);
            });
        },
        selectAfterPaste: function (rowIndex, scroll){
            this.selection.clear();
            this.selectedItem = null;
            this.selection.setSelected(rowIndex, true);

            if(scroll){
                this.scrollToRow(((rowIndex - 3) > 0) ? rowIndex - 3 : rowIndex);
            }
        },
        pasteCell: function(rowIndex, cell){
            var self = this, content,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                }),
                store = self.store,
                targetItem = self.selection.getFirstSelected();

            if(self.copyCellType != 'rate'){
                var fieldName = cell.field.split('-');
                content = {
                    type: self.copyCellType,
                    target_id: targetItem.id,
                    target_bill_column_setting_id: fieldName[0],
                    id: self.selectedItem.id,
                    bill_column_setting_id: self.selectedBillColumnSettingId,
                    currentBQAddendumId: self.currentBQAddendumId,
                    _csrf_token: self.selectedItem._csrf_token
                }
            }else{
                content = {
                    type: self.copyCellType,
                    target_id: targetItem.id,
                    id: self.selectedItem.id,
                    currentBQAddendumId: self.currentBQAddendumId,
                    _csrf_token: self.selectedItem._csrf_token
                }
            }
            var xhrArgs = {
                url: this.pasteUrl,
                content: content,
                handleAs: 'json',
                load: function(resp) {
                    var rowsToMove = [];
                    if(resp.success){
                        var children = resp.c;
                        store.fetchItemByIdentity({ 'identity' : resp.data.id,  onItem : function(item){
                            for(var property in resp.data){
                                if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                    store.setValue(item, property, resp.data[property]);
                                }
                            }
                            store.save();
                            for(var x=0, len=children.length; x<len; ++x){
                                store.fetchItemByIdentity({ 'identity' : children[x].id,  onItem : function(child){
                                    for(var property in children[x]){
                                        if(child.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                            store.setValue(child, property, children[x][property]);
                                        }
                                    }
                                    store.save();
                                }});
                            }
                        }});
                    }
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                    self.selectedItem = null;
                    self.copyCellType = null;
                    self.selectedBillColumnSettingId = null;
                    rowsToMove.length = 0;
                    pb.hide();
                },
                error: function(error) {
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                    self.selectedItem = null;
                    self.copyCellType = null;
                    self.selectedBillColumnSettingId = null;
                    pb.hide();
                }
            };

            pb.show().then(function(){
                dojo.xhrPost(xhrArgs);
            });
        },
        addRow: function(rowIndex){
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            var self = this,
                prevItemId,
                content,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.addingRow+'. '+nls.pleaseWait+'...'
                }),
                store = self.store,
                itemBefore = self.getItem(rowIndex),
                billId = self.billId;
            if(itemBefore.id > 0){
                prevItemId = (rowIndex > 0) ? self.getItem(rowIndex-1).id : 0;
                content = { prev_item_id: prevItemId, before_id: itemBefore.id, bill_id: billId, _csrf_token:itemBefore._csrf_token, currentBQAddendumId: this.currentBQAddendumId };
            }else{
                prevItemId = (rowIndex > 0) ? self.getItem(rowIndex-1).id : 0;
                content = { id: itemBefore.id, bill_id: billId, prev_item_id: prevItemId, relation_id: itemBefore.relation_id, _csrf_token:itemBefore._csrf_token, currentBQAddendumId: this.currentBQAddendumId }
            }

            pb.show().then(function(){
                dojo.xhrPost({
                    url: self.addUrl,
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
                            var colIndex = (self.type == 'tree') ? 2 : 1;
                            self.selection.setSelected(rowIndex, true);
                            self.focus.setFocusIndex(rowIndex, colIndex);
                        },30);
                        pb.hide();
                    },
                    error: function(error) {
                        self.selection.clear();
                        self.disableToolbarButtons(true);
                        pb.hide();
                    }
                });
            });
        },
        deleteRow: function(rowIndex){
            var self = this, title = null, msg = null,
                item = self.getItem(rowIndex),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                }),
                deleteType = 'normal';

            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            var xhrArgs = {
                url: this.deleteUrl,
                content: { id: item.id, method: deleteType, addendum_id: self.currentBQAddendumId, _csrf_token: item._csrf_token },
                handleAs: 'json',
                load: function(data) {
                    if(data.success){
                        var items = data.items;
                        var store = self.store;

                        if(data.affected_nodes != undefined){
                            var affectedList = data.affected_nodes;
                            for(var type in affectedList){
                                dojo.forEach(affectedList[type], function(node){
                                    store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem){
                                        for(var property in node){
                                            if(affectedItem.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                                store.setValue(affectedItem, property, node[property]);
                                                store.save();
                                            }
                                        }
                                    }});
                                });
                            }
                        }

                        for(var i=0, len=items.length; i<len; ++i){
                            store.fetchItemByIdentity({ 'identity' : items[i].id,  onItem : function(itm){
                                if (deleteType === 'normal') {
                                    store.deleteItem(itm);
                                } else {
                                    for(var property in items[i]) {
                                        if(itm.hasOwnProperty(property) && property != store._getIdentifierAttribute()) {
                                            store.setValue(itm, property, items[i][property]);
                                        }
                                    }
                                }

                                store.save();
                            }});
                        }
                        items.length = 0;
                    }
                    pb.hide();
                    self.selection.clear();
                    window.setTimeout(function() {
                        self.focus.setFocusIndex(rowIndex, 0);
                    }, 10);
                    self.disableToolbarButtons(true);
                    self.selectedItem = null;
                    self.pasteOp = null;
                    self.copyCellType = null;
                },
                error: function(error) {
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                    self.selectedItem = null;
                    self.pasteOp = null;
                    self.copyCellType = null;
                    pb.hide();
                }
            };

            // determine which msg to show in dialogbox when deleting
            if (this.type != 'tree') {
                // for element level
                title = nls.deleteElementDialogBoxTitle;
                msg   = nls.deleteElementDialogBoxMsg;
            } else {
                // for head/item level
                if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N) {
                    title = nls.deleteHeadDialogBoxTitle;
                    msg   = nls.deleteHeadDialogBoxMsg;
                } else {
                    title = nls.deleteItemDialogBoxTitle;
                    msg   = nls.deleteItemDialogBoxMsg;
                }
            }

            new buildspace.dialog.confirm(title, msg, 80, 320, function() {
                pb.show().then(function(){
                    dojo.xhrPost(xhrArgs);
                });
            }, function() {
                //on cancel
            });
        },
        deleteQuantity: function(rowIndex, cell, type){
            var self = this, item = self.getItem(rowIndex),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            var fieldName = cell.field.split('-');
            var content = {
                type: type,
                bill_column_setting_id: fieldName[0],
                id: item.id,
                _csrf_token: item._csrf_token
            };

            pb.show().then(function(){
                dojo.xhrPost({
                    url: self.deleteQuantityUrl,
                    content: content,
                    handleAs: 'json',
                    load: function(data) {
                        if(data.success){
                            var items = data.items;
                            var store = self.store;
    
                            dojo.forEach(items, function(node){
                                store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(item){
                                    for(var property in node){
                                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                            store.setValue(item, property, node[property]);
                                            store.save();
                                        }
                                    }
                                }});
                            });
                        }
                        pb.hide();
                        self.selection.clear();
                        window.setTimeout(function() {
                            self.focus.setFocusIndex(rowIndex, 0);
                        }, 10);
                        self.disableToolbarButtons(true);
                        self.selectedItem = null;
                        self.pasteOp = null;
                        self.copyCellType = null;
                    },
                    error: function(error) {
                        self.selection.clear();
                        self.disableToolbarButtons(true);
                        self.selectedItem = null;
                        self.pasteOp = null;
                        self.copyCellType = null;
                        pb.hide();
                    }
                });
            });
        },
        deleteRate: function(rowIndex){
            var self = this, item = self.getItem(rowIndex),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            
            pb.show().then(function(){
                dojo.xhrPost({
                    url: self.deleteRateUrl,
                    content: { id: item.id, _csrf_token: item._csrf_token },
                    handleAs: 'json',
                    load: function(data) {
                        if(data.success){
                            var items = data.items;
                            var store = self.store;
    
                            dojo.forEach(items, function(node){
                                store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(item){
                                    for(var property in node){
                                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                            store.setValue(item, property, node[property]);
                                            store.save();
                                        }
                                    }
                                }});
                            });
                        }
                        pb.hide();
                        self.selection.clear();
                        window.setTimeout(function() {
                            self.focus.setFocusIndex(rowIndex, 0);
                        }, 10);
                        self.disableToolbarButtons(true);
                        self.selectedItem = null;
                        self.pasteOp = null;
                        self.copyCellType = null;
                    },
                    error: function(error) {
                        self.selection.clear();
                        self.disableToolbarButtons(true);
                        self.selectedItem = null;
                        self.pasteOp = null;
                        self.copyCellType = null;
                        pb.hide();
                    }
                });
            });
        },
        indentOutdent: function(rowIndex, type){
            var self = this,
                store = self.store;
            if(rowIndex > 0){
                var item = self.getItem(rowIndex);
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.recalculateRows+'. '+nls.pleaseWait+'...'
                });
                
                if(item.id > 0){
                    var xhrArgs = {
                        url: this[type+'Url'],
                        content: { id: item.id, _csrf_token: item._csrf_token },
                        handleAs: 'json',
                        load: function(data) {
                            if(data.success){
                                var nextItems = data.c;
                                for(var property in data.item){
                                    if(data.item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(item, property, data.item[property]);
                                    }
                                }
                                for(var x=0, len=nextItems.length; x<len; ++x){
                                    store.fetchItemByIdentity({ 'identity' : nextItems[x].id,  onItem :  function (nextItem) {
                                        for(var property in nextItems[x]){
                                            if(nextItem.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                                store.setValue(nextItem, property, nextItems[x][property]);
                                            }
                                        }
                                    }});
                                }
                                store.save();
                            }
                            pb.hide();
                        },
                        error: function(error) {
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                            pb.hide();
                        }
                    };

                    pb.show().then(function(){
                        dojo.xhrPost(xhrArgs);
                    });
                }
            }
        },
        refreshGrid: function(){
            this.beginUpdate();

            this.set('structure', this.structure);
            this.set('headerMenu', this.createHeaderCtxMenu());
            this.store.close();

            this.pluginMgr = new this._pluginMgrClass(this);
            this.pluginMgr.preInit();
            this.pluginMgr.postInit();
            this.pluginMgr.startup();

            this._refresh();

            this.endUpdate();
        },
        editableCellDblClick: function(e){
            var event;
            if(this._click.length > 1 && has('ie')){
                event = this._click[1];
            }else if(this._click.length > 1 && this._click[0].rowIndex != this._click[1].rowIndex){
                event = this._click[0];
            }else{
                event = e;
            }
            this.focus.setFocusCell(event.cell, event.rowIndex);
            this.onRowClick(event);
            this.onRowDblClick(e);
        },
        dodblclick: function(e){
            if(e.cellNode){
                if(e.cell.editable){
                    this.editableCellDblClick(e);
                }else{
                    this.onCellDblClick(e);
                }
            }else{
                this.onRowDblClick(e);
            }
        },
        contextMenu: function(e){
            var rowCtxMenu = this.rowCtxMenu = new dijit.Menu();
            this.contextMenuItems(e);
            var info       = {target: e.target, coords: e.keyCode !== keys.F10 && "pageX" in e ? {x: e.pageX, y: e.pageY } : null};
            var item       = this.getItem(e.rowIndex);

            // if current bq addendum is locked, then don't generate context menu on right click
            if ( this.currentBillLockedStatus ){
                return false;
            }

            if(rowCtxMenu && item && (this.selection.isSelected(e.rowIndex) || e.rowNode && html.hasClass(e.rowNode, 'dojoxGridRowbar'))){
                rowCtxMenu._openMyself(info);
                evt.stop(e);
                return;
            }
        },
        contextMenuItems: function(e){
            var self = this,
                str, finalValue,
                topRowItem = this.getItem(e.rowIndex - 1),
                item = this.getItem(e.rowIndex),
                cell = e.cell, disableButton = false,
                disableIndentButton = false,
                disableDeleteButton = false,
                disableAddButton = false;

            if ( this.type == 'tree' ){
                if ( topRowItem === undefined ){
                    disableAddButton = true;
                }

                if ( topRowItem && topRowItem.type[0] === buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER ){
                    disableAddButton = true;
                }
            }

            if(item.id > 0){
                self.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.cut,
                    iconClass:"icon-16-container icon-16-cut",
                    onClick: function(){
                        self.pasteType = 'structure';
                        self.cutItems();
                    },
                    disabled: disableButton
                }));

                self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.copy,
                    iconClass:"icon-16-container icon-16-copy",
                    onClick: dojo.hitch(self,'copyItems'),
                    disabled: disableButton
                }));

                if(self.type == 'tree'){
                    if(cell.field.indexOf('-quantity_per_unit-value') > 0){
                        if(item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                            && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE
                            && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){

                            str = cell.field.split('-');
                            finalValue = number.parse(item[str[0]+'-quantity_per_unit-final_value']);

                            self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                                label: nls.copyQty,
                                iconClass:"icon-16-container icon-16-copy",
                                disabled: disableButton ? disableButton : (!isNaN(finalValue) && finalValue != 0 && finalValue != null) ? false : true,
                                onClick: dojo.hitch(self,'copyCell', 'qty', str[0])
                            }));

                            self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                                label: nls.importQtyFromProjects,
                                iconClass:"icon-16-container icon-16-import",
                                onClick: dojo.hitch(self,'importQty', 'qty', str[0])
                            }));
                        }

                    }else if(cell.field.indexOf('-quantity_per_unit_remeasurement-value') > 0){
                        if(item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                            && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE
                            && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){

                            str = cell.field.split('-');
                            finalValue = number.parse(item[str[0]+'-quantity_per_unit_remeasurement-final_value']);

                            self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                                label: nls.copyQty,
                                iconClass:"icon-16-container icon-16-copy",
                                disabled: (!isNaN(finalValue) && finalValue != 0 && finalValue != null) ? false : true,
                                onClick: dojo.hitch(self,'copyCell', 'qty_remeasurement', str[0])
                            }));

                            self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                                label: nls.importQtyFromProjects,
                                iconClass:"icon-16-container icon-16-import",
                                onClick: dojo.hitch(self,'importQty', 'qty_remeasurement', str[0])
                            }));
                        }

                    }else if(cell.field == 'rate-value'){
                        if(item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                            && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                            finalValue = number.parse(item['rate-final_value']);
                            self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                                label: nls.copyRate,
                                iconClass:"icon-16-container icon-16-copy",
                                disabled: (!isNaN(finalValue) && finalValue != 0 && finalValue != null) ? false : true,
                                onClick: dojo.hitch(self,'copyCell', 'rate')
                            }));
                        }
                    }
                }
            }
            var disabledPaste = true, pasteFunc = 'pasteItem';
            if(self.type == 'tree' && self.selectedItem){
                if(self.copyCellType == 'qty' && item.id > 0 && cell.field.indexOf('-quantity_per_unit-value') > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                    && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE
                    && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                    disabledPaste = false;
                    pasteFunc = 'pasteCell';
                }else if(self.copyCellType == 'qty_remeasurement' && item.id > 0 && cell.field.indexOf('-quantity_per_unit_remeasurement-value') > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                    && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE
                    && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                    disabledPaste = false;
                    pasteFunc = 'pasteCell';
                }else if(self.copyCellType == 'rate' && item.id > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                    && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                    disabledPaste = false;
                    pasteFunc = 'pasteCell';
                }else if(self.copyCellType == null){
                    disabledPaste = false;
                }
            }else{
                disabledPaste = self.selectedItem ? false : true;
            }
            self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                label: nls.paste,
                iconClass:"icon-16-container icon-16-paste",
                onClick: dojo.hitch(self, pasteFunc, e.rowIndex, cell),
                disabled: disableButton ? disableButton : disabledPaste
            }));
            if(item.id > 0 && self.type == 'tree'){
                self.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.indent,
                    iconClass:"icon-16-container icon-16-indent",
                    onClick: dojo.hitch(self,'indentOutdent', e.rowIndex,'indent'),
                    disabled: disableIndentButton
                }));
                self.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.outdent,
                    iconClass:"icon-16-container icon-16-outdent",
                    onClick: dojo.hitch(self,'indentOutdent', e.rowIndex,'outdent'),
                    disabled: disableIndentButton
                }));

                self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.editItemNote,
                    iconClass:"icon-16-container icon-16-note",
                    onClick: dojo.hitch(self,'doEditItemNote', item)
                }));
            }else if(item.id > 0 && self.type != 'tree'){
                self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.editTradeNote,
                    iconClass:"icon-16-container icon-16-note",
                    onClick: dojo.hitch(self,'doEditTradeNote', item)
                }));
            }

            self.rowCtxMenu.addChild(new dijit.MenuItem({
                label: nls.addRow,
                iconClass:"icon-16-container icon-16-add",
                onClick: dojo.hitch(self,'addRow', e.rowIndex),
                disabled: disableAddButton
            }));

            if(item.id > 0){
                self.rowCtxMenu.addChild(new MenuSeparator());
                self.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(self,'deleteRow', e.rowIndex),
                    disabled: disableDeleteButton
                }));
                if(self.type == 'tree'){
                    if(cell.field.indexOf('-quantity_per_unit-value') > 0){
                        if(item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                            && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE
                            && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                            str = cell.field.split('-');
                            finalValue = number.parse(item[str[0]+'-quantity_per_unit-final_value']);
                            self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                                label: nls.deleteQty,
                                iconClass:"icon-16-container icon-16-delete",
                                disabled: disableButton ? disableButton : (!isNaN(finalValue) && finalValue != 0 && finalValue != null) ? false : true,
                                onClick: dojo.hitch(self,'deleteQuantity', e.rowIndex, cell, 'qty')
                            }));
                        }
                    }else if(cell.field.indexOf('-quantity_per_unit_remeasurement-value') > 0){
                        if(item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                            && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE
                            && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                            str = cell.field.split('-');
                            finalValue = number.parse(item[str[0]+'-quantity_per_unit_remeasurement-final_value']);
                            self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                                label: nls.deleteQty,
                                iconClass:"icon-16-container icon-16-delete",
                                disabled: (!isNaN(finalValue) && finalValue != 0 && finalValue != null) ? false : true,
                                onClick: dojo.hitch(self,'deleteQuantity', e.rowIndex, cell, 'qty_remeasurement')
                            }));
                        }
                    }else if(cell.field == 'rate-value'){
                        if(item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                            && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                            finalValue = number.parse(item['rate-final_value']);
                            self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                                label: nls.deleteRate,
                                iconClass:"icon-16-container icon-16-delete",
                                disabled: (!isNaN(finalValue) && finalValue != 0 && finalValue != null) ? false : true,
                                onClick: dojo.hitch(self,'deleteRate', e.rowIndex)
                            }));
                        }
                    }
                }
            }
        },
        doEditItemNote: function (item){
            new EditBillNoteDialog({
                item: item,
                billGrid: this,
                title: nls.editItemNote,
                updateUrl: 'billManager/itemNoteUpdate'
            }).show();
        },
        doEditTradeNote: function (item){
            new EditBillNoteDialog({
                item: item,
                billGrid: this,
                title: nls.editTradeNote,
                updateUrl: 'billManager/elementNoteUpdate'
            }).show();
        },
        onHeaderCellClick: function(e) {
           if (!dojo.hasClass(e.cell.id, "staticHeader")) {
               e.grid.setSortIndex(e.cell.index);
               e.grid.onHeaderClick(e);
           }
        },
        onHeaderCellMouseOver: function(e) {
           if (!dojo.hasClass(e.cell.id, "staticHeader")) {
               dojo.addClass(e.cellNode, this.cellOverClass);
           }
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
        disableToolbarButtons: function(isDisable, buttonsToEnable) {
            var addRowBtn = dijit.byId(this.billId+this.elementId+'AddRow-button'),
                deleteRowBtn = dijit.byId(this.billId+this.elementId+'DeleteRow-button'),
                indentBtn = dijit.byId(this.billId+this.elementId+'IndentRow-button'),
                outdentBtn = dijit.byId(this.billId+this.elementId+'OutdentRow-button'),
                importElementLibraryBtn = dijit.byId(this.billId+this.elementId+'ImportElementFromLibraryRow-button'),
                importItemLibraryBtn = dijit.byId(this.billId+this.elementId+'ImportItemFromLibraryRow-button'),
                importItemProjectBtn = dijit.byId(this.billId+this.elementId+'ImportItemFromProjectRow-button');

            addRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);

            if(importElementLibraryBtn)
                importElementLibraryBtn._setDisabledAttr(isDisable);
            if(importItemLibraryBtn)
                importItemLibraryBtn._setDisabledAttr(isDisable);
            if(importItemProjectBtn)
                importItemProjectBtn._setDisabledAttr(isDisable);
            if(indentBtn)
                indentBtn._setDisabledAttr(isDisable);
            if(outdentBtn)
                outdentBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(_this.billId+_this.elementId+label+'Row-button');
                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        },
        disableAllButtonWhenCurrentBillIsLocked: function() {
            if (! this.currentBillLockedStatus) {
                if (this.type == "tree") {
                    this.disableToolbarButtons(true, ['Add', 'ImportItemFromLibrary', 'ImportItemFromProject']);
                } else {
                    this.disableToolbarButtons(true, ['Add', 'ImportElementFromLibrary']);
                }
            }
        },
        setupContextMenuAndToolbarButtonRestriction: function(e) {
            var item = this.getItem(e.rowIndex);

            if(item && item.id > 0 && ! this.currentBillLockedStatus) {
                this.disableToolbarButtons(false);
            } else {
                this.disableAllButtonWhenCurrentBillIsLocked();
            }
        }
    });

    return declare('buildspace.apps.ProjectBuilder.BillManager.BillGridBuilder', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;border:0px!important;",
        gutters: false,
        stackContainerTitle: '',
        billId: -1,
        elementId: 0,
        itemId: -1,
        rowSelector: null,
        gridOpts: {},
        type: null,
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            lang.mixin(this.gridOpts, { billId: this.billId, elementId: this.elementId, type: this.type, region: "center", borderContainerWidget: this });
            var grid = this.grid = new BillGrid(this.gridOpts);

            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.billId+this.elementId+'AddRow-button',
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

            if(this.type == 'tree'){
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: this.billId+this.elementId+'IndentRow-button',
                        label: nls.indent,
                        iconClass: "icon-16-container icon-16-indent",
                        disabled: grid.selection.selectedIndex > -1 ? false : true,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                grid.indentOutdent(grid.selection.selectedIndex, 'indent');
                            }
                        }
                    })
                );
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: this.billId+this.elementId+'OutdentRow-button',
                        label: nls.outdent,
                        iconClass: "icon-16-container icon-16-outdent",
                        disabled: grid.selection.selectedIndex > -1 ? false : true,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                grid.indentOutdent(grid.selection.selectedIndex, 'outdent');
                            }
                        }
                    })
                );
            }
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: this.billId+this.elementId+'DeleteRow-button',
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
            if(this.type == 'tree'){
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: this.billId+this.elementId+'ImportItemFromLibraryRow-button',
                        label: nls.importItemsFromLibrary,
                        iconClass: "icon-16-container icon-16-import",
                        disabled: grid.selection.selectedIndex > -1 ? false : true,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                new ImportItemDialog({
                                    billId: self.billId,
                                    selectedItem: grid.selection.getFirstSelected(),
                                    elementId: self.elementId,
                                    billGrid: grid,
                                    currentBQAddendumId: self.gridOpts.currentBQAddendumId
                                }).show();
                            }
                        }
                    })
                );
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: this.billId+this.elementId+'ImportItemFromProjectRow-button',
                        label: nls.importItemsFromProject,
                        iconClass: "icon-16-container icon-16-import",
                        disabled: grid.selection.selectedIndex > -1 ? false : true,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                new ImportProjectItemDialog({
                                    billId: self.billId,
                                    selectedItem: grid.selection.getFirstSelected(),
                                    elementId: self.elementId,
                                    billGrid: grid,
                                    currentBQAddendumId: self.gridOpts.currentBQAddendumId
                                }).show();
                            }
                        }
                    })
                );
            }else{
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: this.billId+this.elementId+'ImportElementFromLibraryRow-button',
                        label: nls.importElementsFromLibrary,
                        iconClass: "icon-16-container icon-16-import",
                        disabled: grid.selection.selectedIndex > -1 ? false : true,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                new ImportElementDialog({
                                    billId: self.billId,
                                    selectedItem: grid.selection.getFirstSelected(),
                                    elementId: self.elementId,
                                    billGrid: grid
                                }).show();
                            }
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.printBQ,
                        iconClass: "icon-16-container icon-16-print",
                        onClick: function(){
                            new PrintBillDialog({
                                billId: self.billId,
                                bqCSRFToken: self.gridOpts.bqCSRFToken
                            }).show();
                        }
                    })
                );
            }

            this.addChild(toolbar);
            this.addChild(grid);

            var addResourceCatBtn = dijit.byId('add_resource_category_'+this.billId+'-btn');
            if(addResourceCatBtn)
                addResourceCatBtn.destroy();

            var container = dijit.byId('billGrid'+this.billId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                container.addChild(new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    content: this,
                    id: this.pageId,
                    grid: grid
                }, node));
                container.selectChild(this.pageId);
            }
        }
    });
});