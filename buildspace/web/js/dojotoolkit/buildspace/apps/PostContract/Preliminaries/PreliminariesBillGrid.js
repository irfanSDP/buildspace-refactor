define('buildspace/apps/PostContract/Preliminaries/PreliminariesBillGrid',[
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
    'dojo/i18n!buildspace/nls/PostContract',
    'dojo/on',
    'dojox/grid/_Grid',
    "dojox/widget/PlaceholderMenuItem",
    'buildspace/widget/grid/cells/TextBox'
], function(declare, lang, array, domAttr, Menu, number, MenuCheckedItem, MenuPlugin, Selector, Rearrange, FormulatedColumn, evt, keys, focusUtil, html, xhr, DropDownButton, DropDownMenu, MenuItem, PopupMenuItem, MenuSeparator, Select, Textarea, FormulaTextBox, GridFormatter, nls, on){

    var BillGrid = declare('buildspace.apps.PostContract.Preliminaries.PreliminariesBillGrid', dojox.grid.EnhancedGrid, {
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
            this.gridEditable          = args.editable;

            var formatter = this.formatter = new GridFormatter();

            if (this.type != 'tree') {
                this.typeColumns = this.recurringElementGridStructure();
            } else {
                this.typeColumns = this.recurringItemGridStructure();
            }

            this.typeColumnChildren = [
                {name: nls.percent, field_name: 'percentage', width: '60px', styles: "text-align:right;", formatter: formatter.prelimPercentFormatter, cellType: 'buildspace.widget.grid.cells.FormulaTextBox'},
                {name: nls.amount, field_name: 'amount', width: '100px', styles: "text-align:right;", formatter: formatter.prelimAmountFormatter, cellType: 'buildspace.widget.grid.cells.FormulaTextBox'}
            ];

            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});

            this.setColumnStructure();

            this.createHeaderCtxMenu();
        },
        canSort: function(inSortInfo){
            return false;
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName) {
            var self = this, item = self.getItem(rowIdx), store = self.store;

            if ( item[inAttrName] && val !== item[inAttrName][0] ) {
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });

                if ( ! val ) {
                    val = 0;
                }

                var params = {
                    id: item.post_contract_bill_item_rate_id,
                    attr_name: inAttrName,
                    val: val,
                    bill_id : self.billId,
                    revision_id: self.currentClaimRevision.id,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = 'postContractPreliminaries/updateItemClaim';

                var updateCell = function(data, store) {
                    for(var property in data) {
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }

                    store.save();
                };

                pb.show();
                dojo.xhrPost({
                    url: url,
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            if(item.id > 0){
                                updateCell(resp.item, store);
                            }

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
                });
            } else {
                self.inherited(arguments);
            }
        },
        canEdit: function(inCell, inRowIndex){
            var self = this, item = this.getItem(inRowIndex);

            if ( ! self.gridEditable ) {
                return false;
            }

            if ( this.type == 'tree' ) {
                if(item != undefined && item.id[0] > 0 && inCell != undefined) {
                    var field = inCell.field,
                        disableQtyEdit = true,
                        qtyColumn = false,
                        splittedFieldName = field.split("-");

                    if (splittedFieldName.length > 1) {
                        if ((item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID) && (splittedFieldName[1] !== 'percentage' || splittedFieldName[1] !== 'amount')) {
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        } else if ( splittedFieldName[0] == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_INITIAL ) {
                            if(item.include_initial[0] == 'true'){
                                window.setTimeout(function() {
                                    self.edit.cancel();
                                    self.focus.setFocusIndex(inRowIndex, inCell.index);
                                }, 10);
                                return;
                            }
                            else if(item['previousClaim-amount'][0] != 0){
                                window.setTimeout(function() {
                                    self.edit.cancel();
                                    self.focus.setFocusIndex(inRowIndex, inCell.index);
                                }, 10);
                                return;
                            }
                        } else if ( splittedFieldName[0] == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_FINAL ) {
                            if(item.include_final[0] == 'true'){
                                window.setTimeout(function() {
                                    self.edit.cancel();
                                    self.focus.setFocusIndex(inRowIndex, inCell.index);
                                }, 10);
                                return;
                            }
                            else if(item['previousClaim-amount'][0] != 0){
                                window.setTimeout(function() {
                                    self.edit.cancel();
                                    self.focus.setFocusIndex(inRowIndex, inCell.index);
                                }, 10);
                                return;
                            }
                        }
                    } else {
                        if ((item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID)) {
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        } else if ( splittedFieldName[0] === 'rate' ) {
                            if ( item.type != buildspace.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE ) {
                                window.setTimeout(function() {
                                    self.edit.cancel();
                                    self.focus.setFocusIndex(inRowIndex, inCell.index);
                                }, 10);
                                return;
                            }
                            else if(item['previousClaim-amount'][0] != 0){
                                window.setTimeout(function() {
                                    self.edit.cancel();
                                    self.focus.setFocusIndex(inRowIndex, inCell.index);
                                }, 10);
                                return;
                            }
                        } else if ( splittedFieldName[0] === 'include_initial' && item.initial_include_at_revision_id[0] != self.currentClaimRevision.id ) {
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        } else if ( splittedFieldName[0] === 'include_final' && item.final_include_at_revision_id[0] != self.currentClaimRevision.id ) {
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }
                    }
                } else {
                    return;
                }
            } else {
                window.setTimeout(function() {
                    self.edit.cancel();
                    self.focus.setFocusIndex(inRowIndex);
                }, 10);
                return;
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

            var descriptionWidth = 'auto';

            if(this.typeColumns.length > 1 || this.markupSettings.bill_markup_enabled || this.markupSettings.element_markup_enabled || this.markupSettings.item_markup_enabled)
            {
                descriptionWidth = '500px';
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
                            showInCtxMenu: true,
                            formatter: formatter.billRefCellFormatter
                        },
                        {
                            name: nls.description,
                            field: 'description',
                            width: descriptionWidth,
                            editable: false,
                            cellType: 'buildspace.widget.grid.cells.Textarea',
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },{
                            name: nls.voOmittedAt,
                            field: 'omitted_at_vo',
                            width:'90px',
                            styles:'text-align: center;',
                            showInCtxMenu: true,
                            noresize: true,
                            rowSpan : 2,
                            editable: false,
                            formatter: formatter.unEditableCellFormatter
                        },{
                            name: nls.quantity,
                            field: 'qty-qty_per_unit',
                            styles: "text-align:right;",
                            width: '90px',
                            formatter: formatter.postContractPreliminariesQuantityCellFormatter,
                            noresize: true,
                            hidden: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },{
                            name: nls.unit,
                            field: 'uom_id',
                            width: '70px',
                            editable: false,
                            styles: 'text-align:center;',
                            type: 'dojox.grid.cells.Select',
                            options: unitOfMeasurements.options,
                            values: unitOfMeasurements.values,
                            formatter: formatter.unitIdCellFormatter,
                            noresize: true,
                            rowSpan: 2,
                            hidden: true,
                            showInCtxMenu: true
                        },{
                            name: nls.rate,
                            field: 'rate',
                            styles: "text-align:right;",
                            width: '75px',
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: formatter.formulaPrelimCurrencyCellFormatter,
                            noresize: true,
                            rowSpan: 2,
                            hidden: true,
                            showInCtxMenu: true
                        },{
                            name: nls.itemTotal,
                            field: 'item_total',
                            styles: "text-align:right;color:blue;",
                            width: '75px',
                            editable: false,
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: formatter.unEditableNumberCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        }]
                    ]
                };

                var hideGrandTotalColumn = true;

                if(this.markupSettings.bill_markup_enabled || this.markupSettings.element_markup_enabled || this.markupSettings.item_markup_enabled){
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
                    type: 'dojox.grid.cells.Select',
                    options: includeOptions.options,
                    values: includeOptions.values,
                    rowSpan: 2,
                    formatter: formatter.yesNoCellFormatter,
                    editable: true
                },{
                    name: nls.includeFinal,
                    field: 'include_final',
                    width: '100px',
                    styles: "text-align:center;",
                    type: 'dojox.grid.cells.Select',
                    options: includeOptions.options,
                    values: includeOptions.values,
                    rowSpan: 2,
                    formatter: formatter.yesNoCellFormatter,
                    editable: true
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
                            editable: editableModeElementGrid,
                            cellType: 'buildspace.widget.grid.cells.Textarea',
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan: 2
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
                            name: nls.grandTotal,
                            field: 'grand_total',
                            styles: "text-align:right;color:blue;",
                            width: '100px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            rowSpan: 2,
                            noresize: true,
                            showInCtxMenu: true
                        }]
                    ]
                },
                fixedColumnsAfterTypeColumns = [];

                var columnToDisplay = this.constructTypeColumnStructure(fixedColumns);
            }

            fixedColumnsAfterTypeColumns.unshift({
                name: nls.importedUpToDateClaim,
                field: 'imported_up_to_date_amount',
                width:'90px',
                styles:'text-align: right;',
                formatter: formatter.unEditableCurrencyCellFormatter,
                showInCtxMenu: true,
                rowSpan : 2
            });

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
                    var fieldName = typeColumn.field_name, editable = true;
                    field = fieldName+'-'+typeColumnChildren[i].field_name;

                    if(self.type == 'tree') {
                        if ( fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_RECURRING || fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED || fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED || fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_PREVIOUSCLAIM || fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_CURRENTCLAIM || fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_UPTODATECLAIM ) {
                            editable = false;
                        }
                    } else {
                        editable = false;
                    }

                    var cellStructure = {
                        field: field,
                        columnType: "prelimClaimColumn",
                        editable: editable,
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
        doEditItemNote: function (item){
            var self = this;

            // var editItemNoteDialog = new EditBillNoteDialog({
            //     item: item,
            //     billGrid: self,
            //     title: nls.editItemNote,
            //     updateUrl: 'billManager/itemNoteUpdate'
            // });

            // editItemNoteDialog.show();
        },
        doEditTradeNote: function (item){
            var self = this;

            // var editItemNoteDialog = new EditBillNoteDialog({
            //     item: item,
            //     billGrid: self,
            //     title: nls.editTradeNote,
            //     updateUrl: 'billManager/elementNoteUpdate'
            // });

            // editItemNoteDialog.show();
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
        recurringElementGridStructure: function() {
            typeColumns = [];

            var recurringFields = [{
                name: nls.previousClaim,
                field_name: buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_PREVIOUSCLAIM
            }, {
                name: nls.currentClaim,
                field_name: buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_CURRENTCLAIM
            }, {
                name: nls.upToDateClaim,
                field_name: buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_UPTODATECLAIM
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
                field_name: buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_INITIAL
            }, {
                name: nls.recurring,
                field_name: buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_RECURRING
            }, {
                name: nls.timeBased,
                field_name: buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED
            }, {
                name: nls.workBased,
                field_name: buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED
            }, {
                name: nls.final,
                field_name: buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_FINAL
            }, {
                name: nls.previousClaim,
                field_name: buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_PREVIOUSCLAIM
            }, {
                name: nls.currentClaim,
                field_name: buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_CURRENTCLAIM
            }, {
                name: nls.upToDateClaim,
                field_name: buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_UPTODATECLAIM
            }], len = recurringFields.length;

            for (var i = 0; i < len; i++) {
                var obj = {
                    name: recurringFields[i].name,
                    field_name: recurringFields[i].field_name
                };

                typeColumns.push(obj);
            }

            return typeColumns;
        }
    });

    return declare('buildspace.apps.PostContract.BillManager.PreliminariesBillGridBuilder', dijit.layout.BorderContainer, {
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
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {
                billId: this.billId,
                elementId: this.elementId,
                type: this.type,
                region:"center",
                borderContainerWidget: this
            });

            var grid = this.grid = new BillGrid(this.gridOpts);

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('billGrid'+this.billId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 45),
                    id: this.pageId,
                    content: this,
                    grid: grid
                }, node);
                container.addChild(child);
                container.selectChild(this.pageId);
            }
        }
    });
});