define('buildspace/apps/PostContractSubPackageReport/Preliminaries/PreliminariesBillGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/aspect',
    'dojo/_base/connect',
    "dojo/_base/array",
    "dojo/request",
    "dojo/store/Memory",
    "dojo/dom-attr",
    "dijit/Menu",
    'dojo/number',
    "dijit/CheckedMenuItem",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    "dijit/form/DropDownButton",
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    'buildspace/widget/grid/cells/Select',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    "dojo/currency",
    './PrintAllSelectedItemDialog',
    './PrintAllSelectedItemWithClaims',
    './PrintAllItemWithCurrentClaimMoreThanZero',
    './PrintAllItemWithClaims',
    'dojo/i18n!buildspace/nls/PostContractSubPackage'
], function(declare, lang, aspect, connect, array, request, Memory, domAttr, Menu, number, MenuCheckedItem, MenuPlugin, Selector, Rearrange, FormulatedColumn, evt, keys, focusUtil, html, xhr, DropDownButton, DropDownMenu, MenuItem, PopupMenuItem, MenuSeparator, Select, Textarea, FormulaTextBox, GridFormatter, IndirectSelection, currency, PrintAllSelectedItemDialog, PrintAllSelectedItemWithClaims, PrintAllItemWithCurrentClaimMoreThanZero, PrintAllItemWithClaims, nls) {

    var Formatter2 = declare("buildspace.widget.grid.cells.Formatter", null, {
        prelimPercentFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx), fieldName = cell.fieldName;

            if ( item && item.grand_total && item.grand_total[0] == 0 ) {
                cellValue = "&nbsp;";
            }
            else if(isNaN(cellValue) || cellValue == 0){
                cellValue = "&nbsp;";
            } else {
                var formattedValue = number.format(cellValue, {places:2})+"%";

                if ( cell.fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED || cell.fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED ) {
                    cellValue = cellValue > 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
                } else {
                    cellValue = cellValue > 0 ? '<span>'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
                }
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            } else if ( fieldName && (
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_RECURRING ||
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED ||
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED
                ) ) {
                cell.customClasses.push('recurring-cell');
            } else if ( fieldName && (
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_PREVIOUSCLAIM ||
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_CURRENTCLAIM ||
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_UPTODATECLAIM
                ) ) {
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        prelimAmountFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                val = '&nbsp;',
                fieldName = cell.fieldName,
                formatterValue;

            if(isNaN(value) || value == 0 || value == null){
                formatterValue = "&nbsp;";
            }else{
                formatterValue = currency.format(value);

                if ( cell.fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED || cell.fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED ) {
                    formatterValue = value > 0 ? '<span style="color:blue;">'+formatterValue+'</span>' : '<span style="color:#FF0000">'+formatterValue+'</span>';
                } else {
                    formatterValue = value > 0 ? '<span>'+formatterValue+'</span>' : '<span style="color:#FF0000">'+formatterValue+'</span>';
                }
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            } else if ( fieldName && (
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_RECURRING ||
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED ||
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED
                ) ) {
                cell.customClasses.push('recurring-cell');
            } else if ( fieldName && (
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_PREVIOUSCLAIM ||
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_CURRENTCLAIM ||
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_UPTODATECLAIM
                ) ) {
                cell.customClasses.push('disable-cell');
            }

            return formatterValue;
        },
        formulaPrelimCurrencyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                val = '&nbsp;';
                fieldName = cell.field.split('-');

            if(isNaN(value) || value == 0 || value == null){
                val = "&nbsp;";
            }else{
                val = currency.format(value);
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            } else if ( fieldName && (
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_RECURRING ||
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED ||
                fieldName == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED
                ) ) {
                cell.customClasses.push('recurring-cell');
            }

            return val;
        }
    });

    var BillGrid = declare('buildspace.apps.PostContractSubPackageReport.Preliminaries.PreliminariesBillGrid', dojox.grid.EnhancedGrid, {
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
        subPackage: null,
        currentGridType: 'element',
        typeColumsToQtyUsed: [],
        currentSelectedClaimRevision: null,
        currentClaimRevision: null,
        constructor:function(args) {
            this.type                  = args.type;
            this.hierarchyTypes        = args.hierarchyTypes;
            this.hierarchyTypesForHead = args.hierarchyTypesForHead;
            this.unitOfMeasurements    = args.unitOfMeasurements;
            this.columnGroup           = args.columnGroup;
            this.typeColumns           = args.typeColumns;
            this.markupSettings        = args.markupSettings;
            this.currencySetting       = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;
            this.currentGridType       = args.currentGridType;

            var formatter  = this.formatter = new GridFormatter();
            var formatter2 = this.formatter2 = new Formatter2();

            if (this.type != 'tree') {
                this.typeColumns = this.recurringElementGridStructure();
            } else {
                this.typeColumns = this.recurringItemGridStructure();
            }

            this.typeColumnChildren = [
                {name: nls.percent, field_name: 'percentage', width: '60px', styles: "text-align:right;", formatter: formatter2.prelimPercentFormatter},
                {name: nls.amount, field_name: 'amount', width: '100px', styles: "text-align:right;", formatter: formatter2.prelimAmountFormatter}
            ];

            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});

            this.setColumnStructure();

            this.createHeaderCtxMenu();

            this.plugins = {indirectSelection: {headerSelector:true, width:"40px", styles:"text-align: center;"}};
        },
        postCreate: function() {
            var self = this, storeName;
            self.inherited(arguments);

            if ( self.type === 'tree' ) {
                storeName = 'previewItemStore';
            } else {
                storeName = 'previewElementStore';
            }

            aspect.after(self, "_onFetchComplete", function() {
                self.gridContainer.markedCheckBoxObject(self, self.gridContainer[storeName]);
            });
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
        singleCheckBoxSelection: function(e) {
            var self = this,
                rowIndex = e.rowIndex,
                checked = this.selection.selected[rowIndex],
                item = this.getItem(rowIndex);

            // used to store removeable selection
            self.removedIds = [];

            if ( checked ) {
                if (self.type === 'tree') {
                    self.gridContainer.previewItemStore.put({ id: item.id[0] });

                    return self.getAffectedElementsByItems(item, 'add');
                } else {
                    self.gridContainer.previewElementStore.put({ id: item.id[0] });

                    return self.getAffectedItemsAndBillsByElement(item, 'add');
                }
            } else {
                if (self.type === 'tree') {
                    self.gridContainer.previewItemStore.remove(item.id[0]);

                    self.removedIds.push(item.id[0]);

                    return self.getAffectedElementsByItems(item, 'remove');
                } else {
                    self.gridContainer.previewElementStore.remove(item.id[0]);

                    self.removedIds.push(item.id[0]);

                    return self.getAffectedItemsAndBillsByElement(item, 'remove');
                }
            }
        },
        toggleAllSelection: function(checked) {
            var self = this, selection = this.selection, storeName;

            // used to store removeable selection
            self.removedIds = [];

            if ( self.type === 'tree' ) {
                storeName = 'previewItemStore';
            } else {
                storeName = 'previewElementStore';
            }

            if (checked) {
                selection.selectRange(0, self.rowCount-1);
                self.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item.id > 0) {
                                self.gridContainer[storeName].put({ id: item.id[0] });
                            }
                        });
                    }
                });

                if (self.type === 'tree') {
                    return self.getAffectedElementsByItems(null , 'add');
                } else {
                    return self.getAffectedItemsAndBillsByElement(null, 'add');
                }
            } else {
                selection.deselectAll();

                self.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item.id > 0) {
                                self.gridContainer[storeName].remove(item.id[0]);

                                self.removedIds.push(item.id[0]);
                            }
                        });
                    }
                });

                if (self.type === 'tree') {
                    return self.getAffectedElementsByItems(null, 'remove');
                } else {
                    return self.getAffectedItemsAndBillsByElement(null, 'remove');
                }
            }
        },
        canSort: function(inSortInfo){
            return false;
        },
        setColumnStructure: function(){
            var formatter  = this.formatter;
            var formatter2 = this.formatter2;

            var descriptionWidth = 'auto';

            if(this.typeColumns.length > 1 || this.markupSettings.bill_markup_enabled || this.markupSettings.element_markup_enabled || this.markupSettings.item_markup_enabled) {
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
                            rowSpan: 2
                        },
                        {
                            name: nls.billReference,
                            field: 'bill_ref',
                            styles: "text-align:center; color: red;",
                            width: '80px',
                            noresize: true,
                            hidden: true,
                            rowSpan: 2,
                            formatter: formatter.billRefCellFormatter
                        },
                        {
                            name: nls.description,
                            field: 'description',
                            width: descriptionWidth,
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.type,
                            field: 'type',
                            width: '70px',
                            styles: 'text-align:center;',
                            formatter: formatter.typeCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.quantity,
                            field: 'qty-qty_per_unit',
                            styles: "text-align:right;",
                            width: '90px',
                            formatter: formatter.postContractPreliminariesQuantityCellFormatter,
                            noresize: true,
                            hidden: true,
                            rowSpan: 2
                        },{
                            name: nls.unit,
                            field: 'uom_id',
                            width: '70px',
                            styles: 'text-align:center;',
                            formatter: formatter.unitIdCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.rate,
                            field: 'rate',
                            styles: "text-align:right;",
                            width: '75px',
                            formatter: formatter2.formulaPrelimCurrencyCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.itemTotal,
                            field: 'item_total',
                            styles: "text-align:right;color:blue;",
                            width: '75px',
                            formatter: formatter.unEditableNumberCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        }]
                    ]
                };

                var hideGrandTotalColumn = true;

                if(this.markupSettings.bill_markup_enabled || this.markupSettings.element_markup_enabled || this.markupSettings.item_markup_enabled)
                {
                    hideGrandTotalColumn = false;
                }

                var includeOptions = {
                    options: [nls.includeCapital, nls.excludeCapital],
                    values: ['true','false']
                };

                var fixedColumnsAfterTypeColumns = [{
                    name: nls.includeInitial,
                    field: 'include_initial',
                    width: '100px',
                    styles: "text-align:center;",
                    rowSpan: 2,
                    formatter: formatter.yesNoCellFormatter
                },{
                    name: nls.includeFinal,
                    field: 'include_final',
                    width: '100px',
                    styles: "text-align:center;",
                    rowSpan: 2,
                    formatter: formatter.yesNoCellFormatter
                }];

                var columnToDisplay = this.constructTypeColumnStructure(fixedColumns);
            }
            else
            {
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
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        }, {
                            name: nls.grandTotal,
                            field: 'grand_total',
                            styles: "text-align:right;color:blue;",
                            width: '100px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            rowSpan: 2,
                            noresize: true
                        }]
                    ]
                },
                fixedColumnsAfterTypeColumns = [];

                var columnToDisplay = this.constructTypeColumnStructure(fixedColumns);
            }

            //Generate After Type Column
            dojo.forEach(fixedColumnsAfterTypeColumns,function(column){
                columnToDisplay.cells[0].push(column);
            });

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

                var colspan = typeColumnChildren.length;
                colCount++;

                parentCells.push({
                    name: typeColumn.name,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader typeHeader"+colCount,
                    colSpan: colspan
                });

                var field = null;

                for(i=0;i<typeColumnChildren.length;i++) {
                    var fieldName = typeColumn.field_name;
                    field = fieldName+'-'+typeColumnChildren[i].field_name;

                    var cellStructure = {
                        field: field,
                        columnType: "prelimClaimColumn",
                        fieldName: typeColumn.field_name
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
                dojo.forEach(columnGroup, function(data, index)
                {
                    if(data.showInCtxMenu)
                    {
                        menusObject.headerMenu.addChild(new dijit.CheckedMenuItem({
                            label: data.name,
                            checked: (typeof data.hidden === 'undefined' || data.hidden === false) ? true : false,
                            onChange: function(val){

                                var show = false;

                                if (val)
                                {
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
        },
        recurringElementGridStructure: function() {
            typeColumns = [];

            var recurringFields = [{
                name: nls.previousClaim,
                field_name: buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_PREVIOUSCLAIM
            }, {
                name: nls.currentClaim,
                field_name: buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_CURRENTCLAIM
            }, {
                name: nls.upToDateClaim,
                field_name: buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_UPTODATECLAIM
            }], len = recurringFields.length;

            for (var i = 0; i < len; i++) {
                var obj = {
                    name: recurringFields[i].name,
                    field_name: recurringFields[i].field_name
                };

                typeColumns.push(obj);
            }

            return typeColumns;
        },
        recurringItemGridStructure: function() {
            typeColumns = [];

            var recurringFields = [{
                name: nls.initial,
                field_name: buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_INITIAL
            }, {
                name: nls.recurring,
                field_name: buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_RECURRING
            }, {
                name: nls.timeBased,
                field_name: buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED
            }, {
                name: nls.workBased,
                field_name: buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED
            }, {
                name: nls.final,
                field_name: buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_FINAL
            }, {
                name: nls.previousClaim,
                field_name: buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_PREVIOUSCLAIM
            }, {
                name: nls.currentClaim,
                field_name: buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_CURRENTCLAIM
            }, {
                name: nls.upToDateClaim,
                field_name: buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.PRELIM_COLUMN_UPTODATECLAIM
            }], len = recurringFields.length;

            for (var i = 0; i < len; i++) {
                var obj = {
                    name: recurringFields[i].name,
                    field_name: recurringFields[i].field_name
                };

                typeColumns.push(obj);
            }

            return typeColumns;
        },
        getAffectedItemsAndBillsByElement: function(element, type) {
            var self = this,
                selectedItemStore = self.gridContainer.previewElementStore,
                elements = [];

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            if (type === 'add') {
                selectedItemStore.query().forEach(function(item) {
                    elements.push(item.id);
                });
            } else {
                for (var typeKeyIndex in self.removedIds) {
                    elements.push(self.removedIds[typeKeyIndex]);
                }
            }

            request.post('postContractPreliminaries/getAffectedItems', {
                handleAs: 'json',
                data: {
                    id: self.billId,
                    element_ids: JSON.stringify(self.borderContainerWidget.arrayUnique(elements))
                }
            }).then(function(data) {
                if ( type === 'add' ) {
                    for (var billId in data) {
                        for (var elementId in data[billId]) {
                            for (var itemId in data[billId][elementId]) {
                                self.gridContainer.previewItemStore.put({ id: data[billId][elementId][itemId] });
                            }
                        }
                    }
                } else {
                    for (var billId in data) {
                        for (var elementId in data[billId]) {
                            self.gridContainer.previewElementStore.remove(elementId);

                            for (var itemId in data[billId][elementId]) {
                                self.gridContainer.previewItemStore.remove(data[billId][elementId][itemId]);
                            }
                        }
                    }
                }

                pb.hide();
            }, function(error) {
                pb.hide();
                console.log(error);
            });
        },
        getAffectedElementsByItems: function(item, type) {
            var self = this,
                items = [];

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            if (type === 'add') {
                self.gridContainer.previewItemStore.query().forEach(function(item) {
                    items.push(item.id);
                });
            } else {
                for (var typeKeyIndex in self.removedIds) {
                    items.push(self.removedIds[typeKeyIndex]);
                }
            }

            request.post('postContractPreliminaries/getAffectedElements', {
                handleAs: 'json',
                data: {
                    id: self.billId,
                    itemIds: JSON.stringify(self.borderContainerWidget.arrayUnique(items))
                }
            }).then(function(data) {
                // remove existing element record
                self.gridContainer.previewElementStore = new Memory({ idProperty: 'id' });

                self.updateElementAndBillGridSelectBox(data);

                pb.hide();
            }, function(error) {
                pb.hide();
                console.log(error);
            });
        },
        updateElementAndBillGridSelectBox: function(data) {
            var self        = this;
            var elementGrid = dijit.byId('sub_package_element-page-container-' + self.billId);

            // clear previous selection
            elementGrid.grid.store.fetch({
                onItem: function(node) {
                    if ( ! node ) {
                        return;
                    }

                    return elementGrid.grid.rowSelectCell.toggleRow(node._0, false);
                }
            });

            for (var billId in data) {
                for (var elementId in data[billId]) {
                    elementGrid.grid.store.fetchItemByIdentity({
                        identity: elementId,
                        onItem: function(node) {
                            if ( ! node ) {
                                return;
                            }

                            self.gridContainer.previewElementStore.put({ id: elementId });

                            return elementGrid.grid.rowSelectCell.toggleRow(node._0, true);
                        }
                    });
                }
            }
        }
    });

    return declare('buildspace.apps.PostContractSubPackageReport.BillManager.PreliminariesBillGridBuilder', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: '',
        rootProject: null,
        subPackage: null,
        billId: -1,
        elementId: 0,
        itemId: -1,
        rowSelector: null,
        gridOpts: {},
        type: null,
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            lang.mixin(self.gridOpts, { subPackage: self.subPackage, billId: self.billId, elementId: self.elementId,type:self.type,region:"center", borderContainerWidget: self });

            var sortOptions = ['allItems', 'itemsWithClaimsSelected', 'itemsWithCurrentClaimMoreThanZero', 'itemsWithClaims'],
                menu = new DropDownMenu({ style: "display: none;"});

            var grid = this.grid = new BillGrid(self.gridOpts);
            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important; border-bottom:none; padding:2px; width:100%;"});

            dojo.forEach(sortOptions, function(opt) {
                var printPreviewMethod;

                switch(opt) {
                    case 'allItems':
                        printPreviewMethod = 'openPrintAllSelectedItemDialog';
                        break;

                    case 'itemsWithClaimsSelected':
                        printPreviewMethod = 'openPrintAllSelectedItemWithClaims';
                        break;

                    case 'itemsWithCurrentClaimMoreThanZero':
                        printPreviewMethod = 'openPrintAllItemWithCurrentClaimMoreThanZero';
                        break;

                    case 'itemsWithClaims':
                        printPreviewMethod = 'openPrintAllItemWithClaims';
                        break;
                }

                var menuItem = new MenuItem({
                    label: nls[opt],
                    onClick: function() {
                        self[printPreviewMethod](opt);
                    }
                });
                menu.addChild(menuItem);
            });

            toolbar.addChild(
                new DropDownButton({
                    label: nls.printPreview,
                    iconClass: "icon-16-container icon-16-print",
                    dropDown: menu
                })
            );

            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var addResourceCatBtn = dijit.byId('add_resource_category_'+this.billId+'-btn');
            if(addResourceCatBtn)
                addResourceCatBtn.destroy();

            var container = dijit.byId('subPackageBillGrid'+this.billId+'-stackContainer');
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
        openPrintAllSelectedItemDialog: function() {
            var self = this,
                selectedItemStore = self.gridOpts.gridContainer.previewItemStore,
                items = [];

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractSubPackagePreliminaries/getPrintingSelectedItem', {
                handleAs: 'json',
                data: {
                    subPackageId: self.subPackage.id,
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintAllSelectedItemDialog({
                    project: self.rootProject,
                    title: nls.printPreview + ' (' + nls.allItems + ')',
                    data: data,
                    subPackageId: self.subPackage.id,
                    billId: self.billId,
                    selectedItems: items
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintAllSelectedItemWithClaims: function() {
            var self = this,
                selectedItemStore = self.gridOpts.gridContainer.previewItemStore,
                items = [];

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractSubPackagePreliminaries/getPrintingSelectedItem', {
                handleAs: 'json',
                data: {
                    subPackageId: self.subPackage.id,
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintAllSelectedItemWithClaims({
                    project: self.rootProject,
                    title: nls.printPreview + ' (' + nls.itemsWithClaimsSelected + ')',
                    data: data,
                    subPackageId: self.subPackage.id,
                    billId: self.billId,
                    selectedItems: items
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintAllItemWithCurrentClaimMoreThanZero: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractSubPackagePreliminaries/getPrintingItemWithCurrentClaimMoreThanZero', {
                handleAs: 'json',
                data: {
                    subPackageId: self.subPackage.id,
                    bill_id: self.billId
                }
            }).then(function(data) {
                var dialog = new PrintAllItemWithCurrentClaimMoreThanZero({
                    project: self.rootProject,
                    title: nls.printPreview + ' (' + nls.itemsWithCurrentClaimMoreThanZero + ')',
                    billId: self.billId,
                    subPackageId: self.subPackage.id,
                    data: data
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintAllItemWithClaims: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractSubPackagePreliminaries/getPrintingAllItemClaims', {
                handleAs: 'json',
                data: {
                    subPackageId: self.subPackage.id,
                    bill_id: self.billId
                }
            }).then(function(data) {
                var dialog = new PrintAllItemWithClaims({
                    project: self.rootProject,
                    title: nls.printPreview + ' (' + nls.itemsWithClaims + ')',
                    subPackageId: self.subPackage.id,
                    billId: self.billId,
                    data: data
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        arrayUnique: function(array) {
            return array.reverse().filter(function (e, i, arr) {
                return arr.indexOf(e, i+1) === -1;
            }).reverse();
        }
    });
});