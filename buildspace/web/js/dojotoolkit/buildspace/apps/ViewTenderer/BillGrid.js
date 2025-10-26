define('buildspace/apps/ViewTenderer/BillGrid',[
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
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    "./HistoricalRateSearchDialog",
    "./TendererDetails",
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/Tendering',
    'dojo/on',
    'dojox/grid/_Grid',
    "dojox/widget/PlaceholderMenuItem",
    'buildspace/widget/grid/cells/TextBox'
], function(declare, lang, array, domAttr, Menu, number, MenuCheckedItem, MenuPlugin, Selector, Rearrange, FormulatedColumn, evt, keys, focusUtil, html, xhr, PopupMenuItem, MenuSeparator, HistoricalRateSearchDialog, TendererDetails, Textarea, FormulaTextBox, GridFormatter, nls, on){

    var BillGrid = declare('buildspace.apps.ViewTenderer.BillGrid', dojox.grid.EnhancedGrid, {
        type: null,
        billId: -1,
        elementId: 0,
        itemId: -1,
        disableEditing: false,
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
        updateUrl: null,
        project: null,
        typeColumsToQtyUsed: [],
        constructor:function(args){
            this.type                    = args.type;
            this.hierarchyTypes          = args.hierarchyTypes;
            this.hierarchyTypesForHead   = args.hierarchyTypesForHead;
            this.unitOfMeasurements      = args.unitOfMeasurements;
            this.columnGroup             = args.columnGroup;
            this.typeColumns             = args.typeColumns;
            this.tender_setting          = args.tender_setting;
            this.tender_companies        = args.tender_companies;
            this.currencySetting         = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            var formatter = this.formatter = new GridFormatter();

            this.companyColumnChildren = [
                {name: nls.rate, field_name: 'rate-value', width: '85px', cellType:'buildspace.widget.grid.cells.TextBox', styles: "text-align:right;", editable: true, formatter: formatter.companyRateCurrencyCellFormatter },
                {name: nls.grandTotal, field_name: 'grand_total_after_markup', width: '100px', styles: "text-align:right;", editable: false, formatter: formatter.unEditableCurrencyCellFormatter}
            ];

            this.setColumnStructure();
            this.inherited(arguments);
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
        setColumnStructure: function(){
            var formatter = this.formatter;

            var descriptionWidth = 'auto';
            var fixedColumns, fixedColumnsAfterTypeColumns, columnToDisplay;

            if(this.type == 'tree'){
                if(this.tender_companies.length > 0){
                    descriptionWidth = '500px';
                }

                var unitOfMeasurements = this.unitOfMeasurements,
                    hierarchyTypes = this.hierarchyTypes;

                fixedColumns = this.fixedColumns = {
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
                            showInCtxMenu: true,
                            rowSpan:2
                        },
                        {
                            name: nls.billReference,
                            field: 'bill_ref',
                            styles: "text-align:center; color: red;",
                            width: '80px',
                            noresize: true,
                            showInCtxMenu: true,
                            formatter: formatter.billRefCellFormatter,
                            rowSpan:2,
                            hidden: false
                        },
                        {
                            name: nls.description,
                            field: 'description',
                            width: descriptionWidth,
                            editable: false,
                            cellType: 'buildspace.widget.grid.cells.Textarea',
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
                        },{
                            name: nls.type,
                            field: 'type',
                            width: '70px',
                            styles: 'text-align:center;',
                            editable: false,
                            type: 'dojox.grid.cells.Select',
                            options: hierarchyTypes.options,
                            values: hierarchyTypes.values,
                            formatter: formatter.typeCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
                        },{
                            name: nls.unit,
                            field: 'uom_id',
                            width: '50px',
                            editable: false,
                            styles: 'text-align:center;',
                            type: 'dojox.grid.cells.Select',
                            options: unitOfMeasurements.options,
                            values: unitOfMeasurements.values,
                            formatter: formatter.unitIdCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
                        },{
                            name: nls.grandTotalQty,
                            field: 'grand_total_quantity',
                            styles: "text-align:right;color:blue;",
                            width: '90px',
                            formatter: formatter.unEditableNumberCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
                        },{
                            name: nls.rate,
                            field: 'rate_after_markup',
                            styles: "text-align:right;",
                            width: '75px',
                            editable: false,
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: formatter.rateAfterMarkupCellFormatter,
                            noresize: true,
                            rowSpan:2
                        },{
                            name: nls.grandTotal,
                            field: 'grand_total_after_markup',
                            styles: "text-align:right;color:blue;",
                            width: '100px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
                        },{
                            name: nls.qty,
                            field: 'quantity_per_unit_remeasurement',
                            styles: "text-align:right;color:blue;",
                            width: '100px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:1
                        },{
                            name: nls.amount,
                            field: 'remeasurement_amount',
                            styles: "text-align:right;color:blue;",
                            width: '100px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:1
                        },{
                            name: nls.historicalRate,
                            field: 'historical_rate',
                            styles: "text-align:right;color:blue;",
                            width: '100px',
                            editable: true,
                            formatter: formatter.currencyCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
                        }],
                        [{
                            name: nls.remeasurement,
                            field: 'remeasurement_fields',
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader"+2,
                            colSpan: 2,
                            hidden: false
                        }]
                    ]
                };

                fixedColumnsAfterTypeColumns = [];

                columnToDisplay = this.generateContractorRateColumn(fixedColumns);
            }else{
                if(this.tender_companies.length > 5){
                    descriptionWidth = '480px';
                }

                fixedColumns = this.fixedColumns = {
                    noscroll: false,
                    width: '50',
                    cells: [
                        [{
                            name: 'No',
                            field: 'id',
                            styles: "text-align:center;",
                            width: '30px',
                            formatter: formatter.rowCountCellFormatter,
                            noresize: true
                        }, {
                            name: nls.description,
                            field: 'description',
                            width: descriptionWidth,
                            cellType: 'buildspace.widget.grid.cells.Textarea',
                            formatter: formatter.treeCellFormatter,
                            noresize: true
                        },{
                            name: nls.grandTotal,
                            field: 'overall_total_after_markup',
                            styles: "text-align:right;color:blue;",
                            width: '100px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true,
                            showInCtxMenu: true
                        }]
                    ]
                };

                fixedColumnsAfterTypeColumns = this.generateContractorGrandTotalColumn();

                columnToDisplay = fixedColumns;
            }

            dojo.forEach(fixedColumnsAfterTypeColumns,function(column){
                columnToDisplay.cells[0].push(column);
            });

            this.structure = columnToDisplay;
        },
        generateContractorRateColumn: function(fixedColumns){
            var companies = this.tender_companies,
                companyColumnChildren = this.companyColumnChildren,
                parentCells = [],
                childCells = [];
                var colCount = 0;

            dojo.forEach(companies, function(company){

                var colspan = companyColumnChildren.length;

                colCount++;

                var companyName = null;

                if(company.awarded){
                    companyName = '<span style="color:blue;">'+buildspace.truncateString(company.name, 28)+'</span>';
                }else{
                    companyName = buildspace.truncateString(company.name, 28);
                }

                parentCells.push({
                    name: companyName,
                    field: company.id + '-company_fields',
                    styles:'text-align:center;',
                    headerClasses: "staticHeader typeHeader"+colCount,
                    colSpan: colspan,
                    headerId: company.id,
                    hidden: false
                });

                var field = null;

                for(i=0;i<companyColumnChildren.length;i++){
                    field = company.id+'-'+companyColumnChildren[i].field_name;

                    var cellStructure = {
                        field: field,
                        columnType: "contractorColumn",
                        companyId: company.id,
                        headerClasses: "typeHeader"+colCount
                    };

                    lang.mixin(cellStructure, companyColumnChildren[i]);

                    fixedColumns.cells[0].push(cellStructure);
                }
            });

            parentCells = fixedColumns.cells[1].concat(parentCells);
            fixedColumns.cells[1] = parentCells;

            return fixedColumns;
        },
        generateContractorGrandTotalColumn: function(){
            var columns = [],
                companies = this.tender_companies,
                formatter = this.formatter,
                colCount = 0;

            dojo.forEach(companies,function(company){
                colCount++;

                var companyName = null;

                if(company.awarded){
                    companyName = '<span style="color:blue;">'+buildspace.truncateString(company.name, 28)+'</span>';
                }else{
                    companyName = buildspace.truncateString(company.name, 28);
                }

                var structure = {
                    name: companyName,
                    field: company.id+'-overall_total_after_markup',
                    styles: "text-align:right;",
                    width: '120px',
                    formatter: formatter.unEditableCurrencyCellFormatter,
                    headerClasses: "typeHeader"+colCount,
                    noresize: true
                };
                columns.push(structure);
            });

            return columns;
        },
        addTendererDetailsContainer: function(tendererId){
            var self = this;
            var id = 'project-'+self.project.id+'-viewTendererDetails';
            var container = dijit.byId(id);
            if(container)
            {
                this.borderContainerWidget.removeChild(container);
                container.destroy();
            }

            container = new TendererDetails({
                id: id,
                region: 'bottom',
                project: self.project,
                company_id: tendererId,
                tender_companies: self.tender_companies,
                style:"padding:0px;margin:0px;width:100%;height:40%;",
                gutters: true
            });

            this.borderContainerWidget.addChild(container);

            return container;
        },
        onHeaderCellDblClick: function(e) {
            var slug;
            if(this.type == 'tree'){
                slug = '-company_fields';
            }
            else{
                slug = '-overall_total_after_markup';
            }
            if(e.cell.field.endsWith(slug))
            {
                var companyId = e.cell.field.replace(slug, '');
                this.addTendererDetailsContainer(companyId);
            }
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;
            var attrNameParsed = inAttrName.replace("-value","");

            if(item[inAttrName][0] != undefined || val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    project_id: self.project.id,
                    unsorted: self.unsorted,
                    attr_name: attrNameParsed,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                        dojo.forEach(data.affected_nodes, function(node){
                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem){
                                for(var property in node){
                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(affectedItem, property, node[property]);
                                    }
                                }
                            }});
                        });
                    }
                    store.save();
                };

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: url,
                        content: params,
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                if(item.id > 0){
                                    updateCell(resp.data, store);
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
                            console.log(error);
                        }
                    });
                });
            }
            self.inherited(arguments);
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
        canEdit: function(inCell, inRowIndex){
            var self = this;

            if(this.type=='tree'){
                if(inCell != undefined){
                    var item = this.getItem(inRowIndex),
                        field = inCell.field,
                        splittedFieldName = field.split("-");

                    if(self.disableEditing){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);

                        return false;
                    }

                    var secondFieldName = null;

                    if(splittedFieldName.length == 3){
                        secondFieldName = splittedFieldName[1];
                    }

                    var excludedItem = [
                        buildspace.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT,
                        buildspace.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE,
                        buildspace.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM,
                        buildspace.constants.HIERARCHY_TYPE_ITEM_PC_RATE
                    ];

                    if(item.id[0] > 0 && !item.project_revision_deleted_at[0]){
                        if((item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N) && field != 'description' && field != 'type' && inCell.editable){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return false;
                        } else if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && field != 'description' && field != 'type' && inCell.editable){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return false;
                        }else if((item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID) && field == 'description'){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return false;
                        }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID && (field != 'type' && field != 'uom_id')){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return false;
                        }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY && field != 'description' && secondFieldName != 'rate'){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return false;
                        }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE && (field == 'rate-value' || secondFieldName == 'rate')){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return false;
                        }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT && field != 'description' && field != 'type' && field != 'markup_percentage-value' && field != 'markup_amount-value' && field != 'uom_id' && inCell.editable){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return false;
                        }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return false;
                        }else if((item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID) && field == 'historical_rate' && inCell.editable){
                            var dialog = new HistoricalRateSearchDialog({
                                targetBillItem: item
                            });

                            dialog.show();

                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return false;
                        }
                    }else {
                        return false;
                    }
                }
            }
            return this._canEdit;
        }
    });

    return declare('buildspace.apps.ViewTenderer.BillGridBuilder', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: '',
        rootProject: null,
        billId: -1,
        elementId: 0,
        disableEditing: false,
        itemId: -1,
        rowSelector: null,
        gridOpts: {},
        type: null,
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {
                billId: this.billId,
                project:this.rootProject,
                elementId: this.elementId,
                type: this.type,
                region:"center",
                disableEditing: this.disableEditing,
                borderContainerWidget: this
            });

            var grid = this.grid = new BillGrid(this.gridOpts);

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('viewTenderBreakdown'+this.rootProject.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                container.addChild(new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    id: this.pageId,
                    content: this,
                    grid: grid
                }, node));
                container.selectChild(this.pageId);
            }
        }
    });
});