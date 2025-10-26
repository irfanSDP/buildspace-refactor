define('buildspace/apps/ProjectBuilderReport/BillManager/BillGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojo/aspect",
    "dijit/Menu",
    'dojo/number',
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request',
    "dijit/form/Button",
    "dijit/DropDownMenu",
    "dijit/form/DropDownButton",
    "dijit/MenuItem",
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    './PrintPreviewFormDialog',
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    './PrintPreviewDialog/PrintSelectedItemWithBuildUpQtyGridDialog',
    './PrintPreviewDialog/PrintSelectedItemWithBuildUpRatesGridDialog',
    './PrintPreviewDialog/PrintSelectedItemQtyIncludingQty2GridDialog',
    './PrintPreviewDialog/PrintSelectedItemAmountIncludingQty2GridDialog',
    'dojo/i18n!buildspace/nls/ProjectBuilder',
    'dojo/on',
    'dojox/grid/_Grid',
    "dojox/widget/PlaceholderMenuItem",
    'buildspace/widget/grid/cells/TextBox'
], function(declare, lang, connect, array, domAttr, aspect, Menu, number, MenuPlugin, Selector, Rearrange, FormulatedColumn, evt, keys, focusUtil, html, request, Button, DropDownMenu, DropDownButton, MenuItem, PopupMenuItem, MenuSeparator, PrintPreviewFormDialog, GridFormatter, IndirectSelection, PrintSelectedItemWithBuildUpQtyGridDialog, PrintSelectedItemWithBuildUpRatesGridDialog, PrintSelectedItemQtyIncludingQty2GridDialog, PrintSelectedItemAmountIncludingQty2GridDialog, nls, on) {

    var BillGrid = declare('buildspace.apps.ProjectBuilderReport.BillManager.BillGrid', dojox.grid.EnhancedGrid, {
        type: null,
        billId: -1,
        elementId: 0,
        itemId: -1,
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        keepSelection: true,
        rowSelector: '0px',
        headerMenu: null,
        typeColumns: null,
        markupSettings: null,
        parentGrid: null,
        elementGridStore: null,
        currentBillLockedStatus: false,
        currentGridType: 'element',
        currentBillType: null,
        ORIGINAL_BILL_VERSION: 0,
        typeColumsToQtyUsed: [],
        gridContainer: null,
        constructor:function(args){
            this.type                    = args.type;
            this.hierarchyTypes          = args.hierarchyTypes;
            this.hierarchyTypesForHead   = args.hierarchyTypesForHead;
            this.unitOfMeasurements      = args.unitOfMeasurements;
            this.columnGroup             = args.columnGroup;
            this.typeColumns             = args.typeColumns;
            this.markupSettings          = args.markupSettings;
            this.currencySetting         = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            this.currentBillLockedStatus = args.currentBillLockedStatus;
            this.currentGridType         = args.currentGridType;
            this.gridContainer           = args.gridContainer;

            var formatter = this.formatter = new GridFormatter();

            if(this.type != 'tree'){
                this.typeColumnChildren = [
                    {name: '% '+nls.job, field_name: 'total', width: '60px', styles: "text-align:right;", formatter: formatter.elementTypeJobPercentageCellFormatter},
                    {name: nls.costPerMetreSquare, field_name: 'total_cost', width: '100px', styles: "text-align:right;", formatter: formatter.unEditableCurrencyCellFormatter},
                    {name: nls.total+'/'+nls.unit, field_name: 'total_per_unit', width: '100px', styles: "text-align:right;", formatter: formatter.elementTotalPerUnitCellFormatter},
                    {name: nls.estimated+' '+nls.costPerMetreSquare, field_name: 'estimated_cost_per_metre_square', width: '100px', styles: "text-align:right;", formatter: formatter.numberCellFormatter },
                    {name: nls.estimated+' '+nls.totalCost, field_name: 'estimated_cost', width: '100px', styles: "text-align:right;", formatter: formatter.unEditableCurrencyCellFormatter }
                ];
            }else{
                var includeOptions = {
                    options: [nls.yesCapital, nls.noCapital],
                    values: ['true','false']
                };

                this.typeColumnChildren = [
                    {name: nls.include, field_name: 'include', width: '60px', styles: "text-align:center;", formatter: formatter.yesNoCellFormatter},
                    {name: nls.qty+'/'+nls.unit, field_name: 'quantity_per_unit-value', width: '70px', styles: "text-align:right;", formatter: formatter.billQuantityCellFormatter},
                    {name: nls.qty+'/'+nls.unit+' (2)', field_name: 'quantity_per_unit_remeasurement-value', width: '70px', styles: "text-align:right;", formatter: formatter.billQuantityCellFormatter},
                    {name: nls.difference+' (%)', field_name: 'quantity_per_unit_difference', width: '85px', styles: "text-align:right;color:blue;", formatter: formatter.unEditablePercentageCellFormatter},
                    {name: nls.total+'/'+nls.unit, field_name: 'total_per_unit', width: '100px', styles: "text-align:right;", formatter: formatter.itemTotalPerUnitCellFormatter}
                ];
            }

            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});

            this.setColumnStructure();

            this.plugins = {indirectSelection: {headerSelector:true, width:"40px", styles:"text-align: center;"}};
        },
        postCreate: function() {
            var self = this, storeName;
            self.inherited(arguments);

            if ( self.type === 'tree' ) {
                aspect.after(self, "_onFetchComplete", function() {
                    self.gridContainer.markedCheckBoxObject(self, self.gridContainer.selectedItemStore);
                });
            }
        },
        startup: function() {
            var self = this;
            self.inherited(arguments);

            this._connects.push(connect.connect(this, 'onCellClick', function(e) {
                if (e.cell.name !== "") {
                    return;
                }

                self.singleCheckBoxSelection(e);
            }));

            this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue) {
                self.toggleAllSelection(newValue);
            }));
        },
        canSort: function(inSortInfo){
            return false;
        },
        setColumnStructure: function(){
            var formatter = this.formatter;

            // editable mode for grid
            var editableModeElementGrid = true;

            // if current version is not original bill or is locked then disable editable mode
            if (this.currentBillLockedStatus)
            {
                editableModeElementGrid = false;
            }

            var descriptionWidth = 'auto';

            if(this.typeColumns.length > 1 || this.markupSettings.bill_markup_enabled || this.markupSettings.element_markup_enabled || this.markupSettings.item_markup_enabled)
            {
                descriptionWidth = '500px';
            }

            if(this.type == 'tree')
            {
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
                            showInCtxMenu: true,
                            formatter: formatter.billRefCellFormatter
                        },
                        {
                            name: nls.description,
                            field: 'description',
                            width: descriptionWidth,
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },{
                            name: nls.type,
                            field: 'type',
                            width: '70px',
                            styles: 'text-align:center;',
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

                var hideGrandTotalColumn = true;

                if(this.markupSettings.bill_markup_enabled || this.markupSettings.element_markup_enabled || this.markupSettings.item_markup_enabled)
                {
                    hideGrandTotalColumn = false;
                }

                var fixedColumnsAfterTypeColumns = [{
                    name: nls.rate,
                    field: 'rate-value',
                    styles: "text-align:right;",
                    width: '75px',
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

                var hideGrandTotalColumn = true;

                if(this.markupSettings.bill_markup_enabled || this.markupSettings.element_markup_enabled || this.markupSettings.item_markup_enabled)
                {
                    hideGrandTotalColumn = false;
                }

                var descriptionWidth = 'auto';

                if(this.typeColumns.length > 1 || this.markupSettings.bill_markup_enabled || this.markupSettings.element_markup_enabled || this.markupSettings.item_markup_enabled)
                {
                    descriptionWidth = '500px';
                }

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
                    formatter: formatter.formulaPercentageCellFormatter,
                    noresize: true
                },{
                    name: nls.markup+" ("+this.currencySetting+")",
                    field: 'markup_amount-value',
                    width: '100px',
                    styles: "text-align:right;",
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

                var markupSummaryFields = [
                    {
                        name: nls.total+' (%) ' +nls.markup,
                        field: this.type == 'tree' ? 'grand_total' : 'original_grand_total',
                        formatter: this.type == 'tree' ? formatter.itemTotalMarkupPercentageCellFormatter : formatter.elementTotalMarkupPercentageCellFormatter
                    },
                    {
                        name: nls.total+' ('+this.currencySetting+') ' +nls.markup,
                        field: this.type == 'tree' ? 'grand_total' : 'original_grand_total' ,
                        formatter: this.type == 'tree' ? formatter.itemTotalMarkupAmountCellFormatter : formatter.elementTotalMarkupAmountCellFormatter
                    },
                    {
                        name: nls.overallTotalAfterMarkup,
                        field: this.type == 'tree' ? 'grand_total_after_markup' : 'overall_total_after_markup',
                        formatter: this.type == 'tree' ? formatter.itemOverallTotalAfterMarkupCellFormatter : formatter.elementOverallTotalAfterMarkupCellFormatter
                    }
                ];

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
                self.typeColumsToQtyUsed[typeColumn.id] = typeColumn.use_original_quantity;

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
                for(i=0;i<typeColumnChildren.length;i++){
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
                    }
                    lang.mixin(cellStructure, typeColumnChildren[i]);
                    fixedColumns.cells[0].push(cellStructure);
                }
            });

            fixedColumns.cells.push(parentCells);

            return fixedColumns;
        },
        showHideMergedColumn: function(show, index) {
            this.beginUpdate();
            this.layout.setColumnVisibility(index, show);
            this.endUpdate();
        },
        refreshGrid: function(){
            this.beginUpdate();

            this.set('structure', this.structure);
            this.store.close();

            this.pluginMgr = new this._pluginMgrClass(this);
            this.pluginMgr.preInit();
            this.pluginMgr.postInit();
            this.pluginMgr.startup();

            this._refresh();

            this.endUpdate();
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

            if(e.node.children[0])
            {
                if(e.node.children[0].children[0].rows.length >= 2)
                {
                    var elemToHide = e.node.children[0].children[0].rows[1],
                        childElement = e.node.children[0].children[0].rows[0].children;

                    elemToHide.parentNode.removeChild(elemToHide);

                    dojo.forEach(childElement, function(child, i)
                    {
                        var rowSpan = dojo.attr(child, 'rowSpan');

                        if(!rowSpan || rowSpan < 2)
                            dojo.attr(child, 'rowSpan', 2);
                    });
                }
            }
        }
    });

    return declare('buildspace.apps.ProjectBuilderReport.BillManager.BillGridBuilder', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: '',
        rootProject: null,
        billId: -1,
        elementId: 0,
        itemId: -1,
        rowSelector: null,
        gridOpts: {},
        type: null,
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { billId: self.billId, elementId: self.elementId,type:self.type,region:"center", borderContainerWidget: self });

            var grid        = this.grid = new BillGrid(self.gridOpts);
            var toolbar     = new dijit.Toolbar({region:"top", style:"outline:none!important; border-bottom:none; padding:2px; width:100%;"});
            var bqCSRFToken = self.gridOpts.bqCSRFToken;
            var menu        = new DropDownMenu({ style: "display: none;"});

            if ( self.type !== 'tree' ) {
                // element printing button
                var elementMenuItem = new MenuItem({
                    label: nls.projectElementEstimateSummary,
                    onClick: function() {
                        self.openSelectedElementsPrintingFormDialog();
                    }
                });
                menu.addChild(elementMenuItem);
            }

            // build up qty printing button
            var buildupQtyPrintPreview = new MenuItem({
                label: nls.itemsWithBuildUpQtyPrintPreview,
                onClick: function() {
                    self.openSelectedItemsBuildUpQtyPrintPreviewDialog();
                }
            });
            menu.addChild(buildupQtyPrintPreview);

            // build up rate printing button
            var buildupRatePrintPreview = new MenuItem({
                label: nls.itemsWithBuildUpRatePrintPreview,
                onClick: function() {
                    self.openSelectedItemsBuildUpRatesPrintPreviewDialog();
                }
            });
            menu.addChild(buildupRatePrintPreview);

            // build up rate with markup printing button
            var buildupRateWithMarkupPrintPreview = new MenuItem({
                label: nls.itemsWithMarkupBuildUpRatePrintPreview,
                onClick: function() {
                    self.openSelectedItemsWithMarkupBuildUpRatePrintPreview();
                }
            });
            menu.addChild(buildupRateWithMarkupPrintPreview);

            // qty with qty 2 printing button
            var qtyIncludingQty2 = new MenuItem({
                label: nls.itemsQtyIncludingQty2,
                onClick: function() {
                    self.openSelectedItemsQtyIncludingQty2PrintPreviewDialog();
                }
            });
            menu.addChild(qtyIncludingQty2);

            // amount with qty 2 printing button
            var amountIncludingQty2 = new MenuItem({
                label: nls.itemsAmountIncludingQty2,
                onClick: function() {
                    self.openSelectedItemsAmountIncludingQty2PrintPreviewDialog();
                }
            });
            menu.addChild(amountIncludingQty2);

            toolbar.addChild(
                new DropDownButton({
                    label: nls.print,
                    iconClass: "icon-16-container icon-16-print",
                    dropDown: menu
                })
            );

            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var addResourceCatBtn = dijit.byId('add_resource_category_'+this.billId+'-btn');
            if(addResourceCatBtn)
                addResourceCatBtn.destroy();

            var container = dijit.byId('projectBuilderReportBillGrid'+this.billId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(self.stackContainerTitle, 60),
                    id: self.pageId
                }, node);
                container.addChild(child);
                child.set('content', self);
                container.selectChild(self.pageId);
            }
        },
        openSelectedElementsPrintingFormDialog: function() {
            var self = this,
                elements = [];

            self.gridOpts.gridContainer.selectedElementStore.query().forEach(function(element) {
                elements.push(element.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            return request.get('viewTendererReporting/getPrintingInformation', {
                handleAs: 'json'
            }).then(function(response) {
                var dialog = new PrintPreviewFormDialog({
                    title: nls.projectElementEstimateSummary,
                    billId: self.billId,
                    selectedRows: elements,
                    printURL: 'projectBuilderReport/printSelectedElementsEstimateSummaryByTypes',
                    exportURL: 'projectBuilderReport/exportExcelSelectedElementsEstimateSummaryByTypes',
                    _csrf_token: response._csrf_token
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openSelectedItemsBuildUpQtyPrintPreviewDialog: function() {
            var self = this,
                items = [];

            self.gridOpts.gridContainer.selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('projectBuilderReport/getPrintPreviewSelectedItemsWithBuildUpQty', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemWithBuildUpQtyGridDialog({
                    billId: self.billId,
                    title: nls.itemsWithBuildUpQtyPrintPreview,
                    data: data,
                    typeColumns: self.gridOpts.typeColumns,
                    selectedItems: items
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openSelectedItemsBuildUpRatesPrintPreviewDialog: function() {
            var self = this,
                items = [];

            self.gridOpts.gridContainer.selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('projectBuilderReport/getPrintPreviewSelectedItemsWithBuildUpRates', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemWithBuildUpRatesGridDialog({
                    billId: self.billId,
                    title: nls.itemsWithBuildUpRatePrintPreview,
                    data: data,
                    selectedItems: items,
                    printURL: 'projectBuilderBuildUpItemsReports/printSelectedItemsWithBuildUpRate',
                    exportURL: 'projectBuilderBuildUpItemsReports/exportExcelSelectedItemsWithBuildUpRate'
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openSelectedItemsWithMarkupBuildUpRatePrintPreview: function() {
            var self = this,
                items = [];

            self.gridOpts.gridContainer.selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('projectBuilderReport/getPrintPreviewSelectedItemsWithBuildUpRates', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemWithBuildUpRatesGridDialog({
                    billId: self.billId,
                    title: nls.itemsWithMarkupBuildUpRatePrintPreview,
                    data: data,
                    selectedItems: items,
                    rateFieldName: 'rate-after_markup',
                    printURL: 'projectBuilderBuildUpItemsReports/printSelectedItemsWithMarkupBuildUpRate',
                    exportURL: 'projectBuilderBuildUpItemsReports/exportExcelSelectedItemsWithMarkupBuildUpRate'
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openSelectedItemsQtyIncludingQty2PrintPreviewDialog: function() {
            var self = this,
                items = [];

            self.gridOpts.gridContainer.selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('projectBuilderReport/getPrintPreviewSelectedItemsQtyIncludingQty2', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemQtyIncludingQty2GridDialog({
                    billId: self.billId,
                    title: nls.itemsQtyIncludingQty2,
                    data: data,
                    typeColumns: self.gridOpts.typeColumns,
                    selectedItems: items
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openSelectedItemsAmountIncludingQty2PrintPreviewDialog: function() {
            var self = this,
                items = [];

            self.gridOpts.gridContainer.selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('projectBuilderReport/getPrintPreviewSelectedItemsQtyIncludingQty2', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemAmountIncludingQty2GridDialog({
                    billId: self.billId,
                    title: nls.itemsAmountIncludingQty2,
                    data: data,
                    typeColumns: self.gridOpts.typeColumns,
                    selectedItems: items
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        }
    });
});