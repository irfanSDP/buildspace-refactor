define('buildspace/apps/ViewTendererReport/BillGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojo/json",
    "dojo/request",
    "dojo/aspect",
    "dijit/Menu",
    "dojox/grid/enhanced/plugins/Rearrange",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/store/Memory',
    'dijit/DropDownMenu',
    'dijit/form/DropDownButton',
    'dijit/MenuItem',
    'dijit/PopupMenuItem',
    "buildspace/widget/grid/cells/Formatter",
    './PrintPreviewDialog/PrintSelectedElementGridDialog',
    './PrintPreviewDialog/PrintSelectedElementRevisionsGridDialog',
    './PrintPreviewDialog/PrintSelectedItemRateGridDialog',
    './PrintPreviewDialog/PrintSelectedItemTotalGridDialog',
    './PrintPreviewDialog/PrintSelectedItemRateAndTotalGridDialog',
    './PrintPreviewDialog/PrintSelectedItemRateAndTotalRevisionsGridDialog',
    './PrintPreviewDialog/PrintSelectedItemRateAndTotalPerUnitGridDialog',
    './PrintPreviewDialog/PrintSelectedElementByTypeAndTendererGridDialog',
    'dojo/i18n!buildspace/nls/TenderingReport'
], function(declare, lang, connect, array, domAttr, JSON, request, aspect, Menu, Rearrange, IndirectSelection, FormulatedColumn, evt, keys, focusUtil, Memory, DropDownMenu, DropDownButton, MenuItem, PopupMenuItem, GridFormatter, PrintSelectedElementGridDialog, PrintSelectedElementRevisionsGridDialog, PrintSelectedItemRateGridDialog, PrintSelectedItemTotalGridDialog, PrintSelectedItemRateAndTotalGridDialog, PrintSelectedItemRateAndTotalRevisionsGridDialog, PrintSelectedItemRateAndTotalPerUnitGridDialog, PrintSelectedElementByTypeAndTendererGridDialog, nls) {

    var BillGrid = declare('buildspace.apps.ViewTendererReport.BillGrid', dojox.grid.EnhancedGrid, {
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
        project: null,
        gridContainer: null,
        constructor:function(args) {
            this.type                  = args.type;
            this.billId                = args.billId;
            this.elementId             = args.elementId;
            this.itemId                = args.itemId;
            this.hierarchyTypes        = args.hierarchyTypes;
            this.hierarchyTypesForHead = args.hierarchyTypesForHead;
            this.unitOfMeasurements    = args.unitOfMeasurements;
            this.columnGroup           = args.columnGroup;
            this.typeColumns           = args.typeColumns;
            this.tender_setting        = args.tender_setting;
            this.tender_companies      = args.tender_companies;
            this.currencySetting       = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;
            this.gridContainer         = args.gridContainer;

            var formatter = this.formatter = new GridFormatter();

            this.companyColumnChildren = [
                {name: nls.rate, field_name: 'rate-value', width: '85px', styles: "text-align:right;", formatter: formatter.companyRateCurrencyCellFormatter },
                {name: nls.grandTotal, field_name: 'grand_total_after_markup', width: '100px', styles: "text-align:right;", formatter: formatter.unEditableCurrencyCellFormatter}
            ];

            this.setColumnStructure();

            this.plugins = {indirectSelection: {headerSelector:true, width:"40px", styles:"text-align: center;"}};

            this.inherited(arguments);
        },
        postCreate: function() {
            var self = this, storeName;
            self.inherited(arguments);

            if ( self.type === 'tree' ) {
                storeName = 'viewTendererItemPreviewStore';
            } else {
                storeName = 'viewTendererElementPreviewStore';
            }

            aspect.after(self, "_onFetchComplete", function() {
                self.gridContainer.markedCheckBoxObject(self, self.gridContainer[storeName][self.billId]);
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

            if ( checked ) {
                if (self.type === 'tree') {
                    return self.getAffectedElementAndBillsByItems(item, 'add');
                } else {
                    return self.getAffectedItemsAndBillsByElement(item, 'add');
                }
            } else {
                if (self.type === 'tree') {
                    return self.getAffectedElementAndBillsByItems(item, 'remove');
                } else {
                    return self.getAffectedItemsAndBillsByElement(item, 'remove');
                }
            }
        },
        toggleAllSelection: function(checked) {
            var self = this, selection = this.selection, storeName;

            if ( self.type === 'tree' ) {
                storeName = 'viewTendererItemPreviewStore';
            } else {
                storeName = 'viewTendererElementPreviewStore';
            }

            if (checked) {
                selection.selectRange(0, self.rowCount-1);
                self.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item.id > 0) {
                                self.gridContainer[storeName][self.billId].put({ id: item.id[0] });
                            }
                        });
                    }
                });

                if (self.type === 'tree') {
                    return self.getAffectedElementAndBillsByItems(null , 'add');
                } else {
                    return self.getAffectedItemsAndBillsByElement(null, 'add');
                }
            } else {
                selection.deselectAll();

                if (self.type === 'tree') {
                    self.store.fetch({
                        onComplete: function (items) {
                            dojo.forEach(items, function (item, index) {
                                if(item.id > 0) {
                                    self.gridContainer[storeName][self.billId].remove(item.id[0]);
                                }
                            });
                        }
                    });

                    return self.getAffectedElementAndBillsByItems(null, 'remove');
                } else {
                    return self.getAffectedItemsAndBillsByElement(null, 'remove');
                }
            }
        },
        getAffectedItemsAndBillsByElement: function(element, type) {
            var self = this,
                selectedItemStore = self.gridContainer.viewTendererElementPreviewStore[self.billId],
                elements = [];

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            if ( element ) {
                self.gridContainer.viewTendererElementPreviewStore[self.billId].put({ id: element.id[0] });
                elements.push(element.id[0]);
            } else {
                selectedItemStore.query().forEach(function(item) {
                    elements.push(item.id);
                });
            }

            request.post('viewTendererReporting/getAffectedBillsAndItems', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    element_ids: JSON.stringify(self.borderContainerWidget.arrayUnique(elements))
                }
            }).then(function(data) {
                var billGrid = dijit.byId('viewTenderer-bill-page-container-' + self.project.id);

                if ( type === 'add' ) {
                    for (var billId in data) {
                        billGrid.store.fetchItemByIdentity({
                            identity: billId,
                            onItem: function(node) {
                                if ( ! node ) {
                                    return;
                                }

                                self.gridContainer.viewTendererBillPreviewStore.put({ id: billId });

                                return billGrid.rowSelectCell.toggleRow(node._0, true);
                            }
                        });

                        for (var elementId in data[billId]) {
                            for (var itemId in data[billId][elementId]) {
                                self.gridContainer.viewTendererItemPreviewStore[self.billId].put({ id: data[billId][elementId][itemId] });
                            }
                        }
                    }
                } else {
                    for (var billId in data) {
                        for (var elementId in data[billId]) {
                            self.gridContainer.viewTendererElementPreviewStore[self.billId].remove(elementId);

                            for (var itemId in data[billId][elementId]) {
                                self.gridContainer.viewTendererItemPreviewStore[self.billId].remove(data[billId][elementId][itemId]);
                            }
                        }

                        // remove checked bill selection if no element is selected
                        billGrid.store.fetchItemByIdentity({
                            identity: billId,
                            onItem: function(node) {
                                if ( ! node ) {
                                    return;
                                }

                                if ( self.gridContainer.viewTendererElementPreviewStore[self.billId].data.length === 0 ) {
                                    self.gridContainer.viewTendererBillPreviewStore.remove(billId);

                                    return billGrid.rowSelectCell.toggleRow(node._0, false);
                                }
                            }
                        });
                    }
                }

                pb.hide();
            }, function(error) {
                pb.hide();
                console.log(error);
            });
        },
        getAffectedElementAndBillsByItems: function(item, type) {
            var self = this,
                items = [];

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            if ( item ) {
                if ( type === 'remove' ) {
                    self.gridContainer.viewTendererItemPreviewStore[self.billId].remove(item.id[0]);
                } else {
                    self.gridContainer.viewTendererItemPreviewStore[self.billId].put({ id: item.id[0] });
                }
            }

            self.gridContainer.viewTendererItemPreviewStore[self.billId].query().forEach(function(item) {
                items.push(item.id);
            });

            request.post('viewTendererReporting/getAffectedBillsAndElements', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.borderContainerWidget.arrayUnique(items))
                }
            }).then(function(data) {
                // remove existing bill and element record
                self.gridContainer.viewTendererBillPreviewStore                 = new Memory({ idProperty: 'id' });
                self.gridContainer.viewTendererElementPreviewStore[self.billId] = new Memory({ idProperty: 'id' });

                self.updateElementAndBillGridSelectBox(data);

                pb.hide();
            }, function(error) {
                pb.hide();
                console.log(error);
            });
        },
        updateElementAndBillGridSelectBox: function(data) {
            var self        = this;
            var billGrid    = dijit.byId('viewTenderer-bill-page-container-' + self.project.id);
            var elementGrid = dijit.byId('viewTenderer-element-page-container-' + self.billId);

            // clear previous selection
            billGrid.store.fetch({
                onItem: function(node) {
                    if ( ! node ) {
                        return;
                    }

                    return billGrid.rowSelectCell.toggleRow(node._0, false);
                }
            });

            elementGrid.grid.store.fetch({
                onItem: function(node) {
                    if ( ! node ) {
                        return;
                    }

                    return elementGrid.grid.rowSelectCell.toggleRow(node._0, false);
                }
            });

            for (var billId in data) {
                billGrid.store.fetchItemByIdentity({
                    identity: billId,
                    onItem: function(node) {
                        if ( ! node ) {
                            return;
                        }

                        self.gridContainer.viewTendererBillPreviewStore.put({ id: billId });

                        return billGrid.rowSelectCell.toggleRow(node._0, true);
                    }
                });

                for (var elementId in data[billId]) {
                    elementGrid.grid.store.fetchItemByIdentity({
                        identity: elementId,
                        onItem: function(node) {
                            if ( ! node ) {
                                return;
                            }

                            self.gridContainer.viewTendererElementPreviewStore[self.billId].put({ id: elementId });

                            return elementGrid.grid.rowSelectCell.toggleRow(node._0, true);
                        }
                    });
                }
            }
        },
        pushIntoStore: function(item, store) {
            if ( item && item.id ) {
                store.put({ id: item.id[0] });
            }

            return store;
        },
        removeFromStore: function(item, store) {
            if ( item && item.id ) {
                store.remove(item.id[0]);
            }

            return store;
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

            if(this.type == 'tree')
            {
                if(this.tender_companies.length > 0)
                {
                    descriptionWidth = '500px';
                }

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
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
                        },{
                            name: nls.type,
                            field: 'type',
                            width: '70px',
                            styles: 'text-align:center;',
                            formatter: formatter.typeCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
                        },{
                            name: nls.unit,
                            field: 'uom_id',
                            width: '50px',
                            styles: 'text-align:center;',
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
                        }]
                    ]
                },
                fixedColumnsAfterTypeColumns = [];

                var columnToDisplay = this.generateContractorRateColumn(fixedColumns);
            }
            else
            {
                if(this.tender_companies.length > 5)
                {
                    descriptionWidth = '480px';
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
                            noresize: true
                        }, {
                            name: nls.description,
                            field: 'description',
                            width: descriptionWidth,
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
                },
                fixedColumnsAfterTypeColumns = this.generateContractorGrandTotalColumn();

                var columnToDisplay = fixedColumns;
            }

            dojo.forEach(fixedColumnsAfterTypeColumns,function(column){
                columnToDisplay.cells[0].push(column);
            });

            this.structure = columnToDisplay;
        },
        generateContractorRateColumn: function(fixedColumns){
            var self = this,
                companies = this.tender_companies,
                companyColumnChildren = this.companyColumnChildren,
                parentCells = [],
                childCells = [];
                var colCount = 0;

            dojo.forEach(companies, function(company){

                var colspan = companyColumnChildren.length;

                colCount++;

                var companyName = null;

                if(company.awarded)
                {
                    companyName = '<span style="color:blue;">'+buildspace.truncateString(company.name, 28)+'</span>';
                }
                else
                {
                    companyName = buildspace.truncateString(company.name, 28);
                }

                parentCells.push({
                    name: companyName,
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

            fixedColumns.cells.push(parentCells);

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

                if(company.awarded)
                {
                    companyName = '<span style="color:blue;">'+buildspace.truncateString(company.name, 28)+'</span>';
                }
                else
                {
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
        canSort: function(inSortInfo){
            return false;
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
        destroy: function() {
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.ViewTendererReport.BillGridBuilder', dijit.layout.BorderContainer, {
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
            lang.mixin(self.gridOpts, { billId: self.billId, project:self.rootProject, elementId: self.elementId, type:self.type, region:"center", borderContainerWidget: self });

            var grid    = this.grid = new BillGrid(self.gridOpts);
            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important; border-bottom:none; padding:2px; width:100%;"});

            var sortOptions = ['summaryAllTenderersLowToHighest', 'summaryAllTenderersHighToLowest'];

            // disable print by selected tenderer if there is no tenderer selected
            var disabledPrintSelectedTendererReport = false;

            if ( self.gridOpts.tender_companies.length === 0 ) {
                disabledPrintSelectedTendererReport = true;
            }

            if ( self.gridOpts.tender_companies.length > 0 && self.gridOpts.tender_companies[0].awarded === false ) {
                disabledPrintSelectedTendererReport = true;
            }

            if ( this.type === 'element' ) {
                var menuElement = new DropDownMenu({ style: "display: none;"});
                var pSubMenu    = new Menu();
                var summaryRevisionsSubMenu    = new Menu();

                dojo.forEach(sortOptions, function(opt) {
                    var menuItem = new MenuItem({
                        id: opt+"-"+self.rootProject.id+'-'+self.type+"-menuElement",
                        label: nls[opt],
                        onClick: function() {
                            self.openElementPreviewDialogForAllTenderers(opt);
                        }
                    });
                    pSubMenu.addChild(menuItem);

                    var summaryRevisionMenuItem = new MenuItem({
                        id: opt+"-"+self.rootProject.id+'-'+self.type+"-revisionMenuElement",
                        label: nls[opt],
                        onClick: function() {
                            self.openElementRevisionsPreviewDialogForAllTenderers(opt);
                        }
                    });
                    summaryRevisionsSubMenu.addChild(summaryRevisionMenuItem);
                });

                menuElement.addChild(
                    new MenuItem({
                        id: self.rootProject.id+'-'+this.type+'-ProjectElementSummarySelectedTenderer-button',
                        label: nls.projectSummarySelectedTenderers,
                        iconClass: "icon-16-container icon-16-print",
                        disabled: disabledPrintSelectedTendererReport,
                        onClick: function() {
                            self.openElementPreviewDialogForSelectedTender();
                        }
                    })
                );

                menuElement.addChild(
                    new MenuItem({
                        id: self.rootProject.id+'-'+this.type+'-ProjectElementSummaryPerUnitTypeByTenderers-button',
                        label: nls.summaryPerUnit,
                        iconClass: "icon-16-container icon-16-print",
                        onClick: function() {
                            self.openElementPreviewDialogAmountPerUnitTypeForSelectedTenderer();
                        }
                    })
                );

                var elementSummaryMenuItem = new PopupMenuItem({
                    label: nls.projectSummaryAllTenderers,
                    iconClass: "icon-16-container icon-16-print",
                    popup: pSubMenu
                });
                menuElement.addChild(elementSummaryMenuItem);

                var elementSummaryRevisionsMenuItem = new PopupMenuItem({
                    label: nls.projectSummaryRevisionsAllTenderers,
                    iconClass: "icon-16-container icon-16-print",
                    popup: summaryRevisionsSubMenu
                });
                menuElement.addChild(elementSummaryRevisionsMenuItem);

                toolbar.addChild(
                    new DropDownButton({
                        label: nls.summary,
                        iconClass: "icon-16-container icon-16-print",
                        dropDown: menuElement
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
            }

            // ====================================================================================================================================
            // create tool bar item for menu Item Rate
            // ====================================================================================================================================
            var menuItemRate    = new DropDownMenu({ style: "display: none;"});
            var itemRateSubMenu = new Menu();

            dojo.forEach(sortOptions, function(opt) {
                var menuChild = new MenuItem({
                    id: opt+"-"+self.rootProject.id+'-'+self.type+"-menuItemRate",
                    label: nls[opt],
                    onClick: function() {
                        self.openItemRatePreviewDialogForAllTenderers(opt);
                    }
                });
                itemRateSubMenu.addChild(menuChild);
            });

            menuItemRate.addChild(
                new MenuItem({
                    id: self.rootProject.id+'-'+this.type+'-ProjectItemRateSelectedTenderer-button',
                    label: nls.itemRateSelectedTenderers,
                    iconClass: "icon-16-container icon-16-print",
                    disabled: disabledPrintSelectedTendererReport,
                    onClick: function() {
                        self.openItemRatePreviewDialogForSelectedTender();
                    }
                })
            );

            var itemRateSummaryMenuItem = new PopupMenuItem({
                label: nls.itemRateAllTenderers,
                iconClass: "icon-16-container icon-16-print",
                popup: itemRateSubMenu
            });
            menuItemRate.addChild(itemRateSummaryMenuItem);

            toolbar.addChild(
                new DropDownButton({
                    label: nls.itemRate,
                    id: self.rootProject.id+'-'+this.type+'-ProjectItemRateAllTenderersPrint-button',
                    iconClass: "icon-16-container icon-16-print",
                    dropDown: menuItemRate
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            // ====================================================================================================================================

            // ====================================================================================================================================
            // create tool bar item for menu Item Total
            // ====================================================================================================================================
            var menuItemTotal    = new DropDownMenu({ style: "display: none;"});
            var itemTotalSubMenu = new Menu();

            dojo.forEach(sortOptions, function(opt) {
                var menuChild = new MenuItem({
                    id: opt+"-"+self.rootProject.id+'-'+self.type+"-menuItemTotal",
                    label: nls[opt],
                    onClick: function() {
                        self.openItemTotalPreviewDialogForAllTenderers(opt);
                    }
                });
                itemTotalSubMenu.addChild(menuChild);
            });

            menuItemTotal.addChild(
                new MenuItem({
                    id: self.rootProject.id+'-'+this.type+'-ProjectItemTotalSelectedTenderer-button',
                    label: nls.itemTotalSelectedTenderers,
                    iconClass: "icon-16-container icon-16-print",
                    disabled: disabledPrintSelectedTendererReport,
                    onClick: function() {
                        self.openItemTotalPreviewDialogForSelectedTender();
                    }
                })
            );

            var itemTotalSummaryMenuItem = new PopupMenuItem({
                label: nls.itemTotalAllTenderers,
                iconClass: "icon-16-container icon-16-print",
                popup: itemTotalSubMenu
            });
            menuItemTotal.addChild(itemTotalSummaryMenuItem);

            toolbar.addChild(
                new DropDownButton({
                    label: nls.itemTotal,
                    id: self.rootProject.id+'-'+this.type+'-ProjectItemTotalAllTenderersPrint-button',
                    iconClass: "icon-16-container icon-16-print",
                    dropDown: menuItemTotal
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            // ====================================================================================================================================

            var itemRateAndTotalMenu    = new DropDownMenu({ style: "display: none;"});

            // ====================================================================================================================================
            // create tool bar item for menu Item Rate and Item Total Per Unit.
            // ====================================================================================================================================
            var itemRateAndTotalPerUnitSubMenu = new Menu();

            dojo.forEach(sortOptions, function(opt) {
                var menuChild = new MenuItem({
                    id: opt+"-"+self.rootProject.id+'-'+self.type+"-itemRateAndTotalPerUnitMenu",
                    label: nls[opt],
                    onClick: function() {
                        self.openItemRateAndTotalPerUnitPreviewDialogForAllTenderers(opt);
                    }
                });
                itemRateAndTotalPerUnitSubMenu.addChild(menuChild);
            });

            var itemRateAndTotalPerUnitMenuItem = new PopupMenuItem({
                label: nls.itemRateAndTotalPerUnitAllTenderers,
                iconClass: "icon-16-container icon-16-print",
                popup: itemRateAndTotalPerUnitSubMenu
            });
            itemRateAndTotalMenu.addChild(itemRateAndTotalPerUnitMenuItem);
            // ====================================================================================================================================

            // ====================================================================================================================================
            // create tool bar item for menu Item Rate and Item Total
            // ====================================================================================================================================
            var itemRateAndTotalSubMenu = new Menu();

            dojo.forEach(sortOptions, function(opt) {
                var menuChild = new MenuItem({
                    id: opt+"-"+self.rootProject.id+'-'+self.type+"-itemRateAndTotalMenu",
                    label: nls[opt],
                    onClick: function() {
                        self.openItemRateAndTotalPreviewDialogForAllTenderers(opt);
                    }
                });
                itemRateAndTotalSubMenu.addChild(menuChild);
            });

            var itemRateAndTotalSummaryMenuItem = new PopupMenuItem({
                label: nls.itemRateAndTotalAllTenderers,
                iconClass: "icon-16-container icon-16-print",
                popup: itemRateAndTotalSubMenu
            });
            itemRateAndTotalMenu.addChild(itemRateAndTotalSummaryMenuItem);

            toolbar.addChild(
                new DropDownButton({
                    label: nls.itemRateAndTotal,
                    id: self.rootProject.id+'-'+this.type+'-ProjectItemRateAndTotalAllTenderersPrint-button',
                    iconClass: "icon-16-container icon-16-print",
                    dropDown: itemRateAndTotalMenu
                })
            );
            // ====================================================================================================================================

            // ====================================================================================================================================
            // create tool bar item for menu Item Rate and Item Total of all Revisions.
            // ====================================================================================================================================
            var itemRateAndTotalRevisionsMenu    = new DropDownMenu({ style: "display: none;"});
            var itemRateAndTotalRevisionsSubMenu = new Menu();

            dojo.forEach(sortOptions, function(opt) {
                var menuChild = new MenuItem({
                    id: opt+"-"+self.rootProject.id+'-'+self.type+"-itemRateAndTotalRevisionsMenu",
                    label: nls[opt],
                    onClick: function() {
                        self.openItemRateAndTotalRevisionsPreviewDialogForAllTenderers(opt);
                    }
                });
                itemRateAndTotalRevisionsSubMenu.addChild(menuChild);
            });

            var itemRateAndTotalRevisionsSummaryMenuItem = new PopupMenuItem({
                label: nls.itemRateAndTotalRevisionsAllTenderers,
                iconClass: "icon-16-container icon-16-print",
                popup: itemRateAndTotalRevisionsSubMenu
            });
            itemRateAndTotalMenu.addChild(itemRateAndTotalRevisionsSummaryMenuItem);
            // ====================================================================================================================================

            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('viewTendererReportBreakdown'+this.rootProject.id+'-stackContainer');
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
        openElementPreviewDialogForSelectedTender: function() {
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedElementStore = self.gridOpts.gridContainer.viewTendererElementPreviewStore[self.billId],
                elements = [];

            selectedElementStore.query().forEach(function(item) {
                elements.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getPrintingSelectedElementByTenderer', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    elementIds: JSON.stringify(self.arrayUnique(elements))
                }
            }).then(function(data) {
                var companyId, companyName;

                dojo.forEach(companies, function(company) {
                    if (company.awarded)
                    {
                        companyId   = company.id;
                        companyName = buildspace.truncateString(company.name, 28);
                    }
                });

                var dialog = new PrintSelectedElementGridDialog({
                    title: nls.projectSummarySelectedTenderers,
                    companyId: companyId,
                    companyName: companyName,
                    data: data,
                    billId: self.billId,
                    selectedElements: elements
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openElementPreviewDialogForAllTenderers: function(type) {
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedElementStore = self.gridOpts.gridContainer.viewTendererElementPreviewStore[self.billId],
                seletedTenderStore = self.gridOpts.builderContainer.selectedTenderers,
                tenderers = [],
                elements = [];

            seletedTenderStore.query().forEach(function(tenderer) {
                tenderers.push(tenderer.id);
            });

            selectedElementStore.query().forEach(function(element) {
                elements.push(element.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getPrintingSelectedElementByAllTenderer', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    tendererIds: JSON.stringify(self.arrayUnique(tenderers)),
                    elementIds: JSON.stringify(self.arrayUnique(elements)),
                    type: type
                }
            }).then(function(data) {
                var dialog = new PrintSelectedElementGridDialog({
                    project: self.rootProject,
                    title: nls.projectSummaryAllTenderers + " (" + nls[type] + ")",
                    companies: companies,
                    data: data,
                    selectedTenderers: tenderers,
                    selectedElements: elements,
                    billId: self.billId,
                    type: type
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openItemRatePreviewDialogForSelectedTender: function() {
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedItemStore = self.gridOpts.gridContainer.viewTendererItemPreviewStore[self.billId],
                items = [];

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getPrintingSelectedItemRateByTenderer', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.arrayUnique(items))
                }
            }).then(function(data) {
                var companyId, companyName;

                dojo.forEach(companies, function(company) {
                    if (company.awarded)
                    {
                        companyId   = company.id;
                        companyName = buildspace.truncateString(company.name, 28);
                    }
                });

                var dialog = new PrintSelectedItemRateGridDialog({
                    project: self.rootProject,
                    title: nls.itemRateSelectedTenderers,
                    companyId: companyId,
                    companyName: companyName,
                    data: data,
                    billId: self.billId,
                    elementId: self.elementId,
                    selectedItems: items
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openItemRatePreviewDialogForAllTenderers: function(type) {
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedItemStore = self.gridOpts.gridContainer.viewTendererItemPreviewStore[self.billId],
                seletedTenderStore = self.gridOpts.builderContainer.selectedTenderers,
                tenderers = [],
                items = [];

            seletedTenderStore.query().forEach(function(tenderer) {
                tenderers.push(tenderer.id);
            });

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getPrintingSelectedItemRateByAllTenderer', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    tendererIds: JSON.stringify(self.arrayUnique(tenderers)),
                    itemIds: JSON.stringify(self.arrayUnique(items)),
                    type: type
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemRateGridDialog({
                    project: self.rootProject,
                    title: nls.itemRateAllTenderers + " (" + nls[type] + ")",
                    companies: companies,
                    data: data,
                    selectedTenderers: tenderers,
                    selectedItems: items,
                    billId: self.billId,
                    elementId: self.elementId,
                    type: type
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openItemTotalPreviewDialogForSelectedTender: function() {
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedItemStore = self.gridOpts.gridContainer.viewTendererItemPreviewStore[self.billId],
                items = [];

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getPrintingSelectedItemTotalByTenderer', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.arrayUnique(items))
                }
            }).then(function(data) {
                var companyId, companyName;

                dojo.forEach(companies, function(company) {
                    if (company.awarded)
                    {
                        companyId   = company.id;
                        companyName = buildspace.truncateString(company.name, 28);
                    }
                });

                var dialog = new PrintSelectedItemTotalGridDialog({
                    project: self.rootProject,
                    title: nls.itemTotalSelectedTenderers,
                    companyId: companyId,
                    companyName: companyName,
                    data: data,
                    selectedItems: items,
                    billId: self.billId,
                    elementId: self.elementId
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openItemTotalPreviewDialogForAllTenderers: function(type) {
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedItemStore = self.gridOpts.gridContainer.viewTendererItemPreviewStore[self.billId],
                seletedTenderStore = self.gridOpts.builderContainer.selectedTenderers,
                tenderers = [],
                items = [];

            seletedTenderStore.query().forEach(function(tenderer) {
                tenderers.push(tenderer.id);
            });

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getPrintingSelectedItemTotalByAllTenderer', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    tendererIds: JSON.stringify(self.arrayUnique(tenderers)),
                    itemIds: JSON.stringify(self.arrayUnique(items)),
                    type: type
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemTotalGridDialog({
                    project: self.rootProject,
                    title: nls.itemTotalAllTenderers + " (" + nls[type] + ")",
                    companies: companies,
                    data: data,
                    selectedTenderers: tenderers,
                    selectedItems: items,
                    billId: self.billId,
                    elementId: self.elementId,
                    type: type
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openItemRateAndTotalPreviewDialogForAllTenderers: function(type) {
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedItemStore = self.gridOpts.gridContainer.viewTendererItemPreviewStore[self.billId],
                seletedTenderStore = self.gridOpts.builderContainer.selectedTenderers,
                tenderers = [],
                items = [];

            seletedTenderStore.query().forEach(function(tenderer) {
                tenderers.push(tenderer.id);
            });

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getSelectedItemByAllTenderers', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    tendererIds: JSON.stringify(self.arrayUnique(tenderers)),
                    itemIds: JSON.stringify(self.arrayUnique(items)),
                    type: type
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemRateAndTotalGridDialog({
                    project: self.rootProject,
                    title: nls.itemRateAndTotalAllTenderers + " (" + nls[type] + ")",
                    companies: companies,
                    data: data,
                    selectedTenderers: tenderers,
                    selectedItems: items,
                    billId: self.billId,
                    elementId: self.elementId,
                    type: type
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openItemRateAndTotalRevisionsPreviewDialogForAllTenderers: function(type) {
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedItemStore = self.gridOpts.gridContainer.viewTendererItemPreviewStore[self.billId],
                seletedTenderStore = self.gridOpts.builderContainer.selectedTenderers,
                tenderers = [],
                items = [];

            seletedTenderStore.query().forEach(function(tenderer) {
                tenderers.push(tenderer.id);
            });

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getSelectedItemRevisionsByAllTenderers', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    tendererIds: JSON.stringify(self.arrayUnique(tenderers)),
                    itemIds: JSON.stringify(self.arrayUnique(items)),
                    type: type
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemRateAndTotalRevisionsGridDialog({
                    project: self.rootProject,
                    title: nls.itemRateAndTotalRevisionsAllTenderers + " (" + nls[type] + ")",
                    companies: companies,
                    data: data,
                    selectedTenderers: tenderers,
                    selectedItems: items,
                    billId: self.billId,
                    elementId: self.elementId,
                    type: type
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openElementRevisionsPreviewDialogForAllTenderers: function(type) {
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedElementStore = self.gridOpts.gridContainer.viewTendererElementPreviewStore[self.billId],
                seletedTenderStore = self.gridOpts.builderContainer.selectedTenderers,
                tenderers = [],
                elements = [];

            seletedTenderStore.query().forEach(function(tenderer) {
                tenderers.push(tenderer.id);
            });

            selectedElementStore.query().forEach(function(element) {
                elements.push(element.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getPrintingSelectedElementRevisionsByAllTenderer', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    tendererIds: JSON.stringify(self.arrayUnique(tenderers)),
                    elementIds: JSON.stringify(self.arrayUnique(elements)),
                    type: type
                }
            }).then(function(data) {
                var dialog = new PrintSelectedElementRevisionsGridDialog({
                    project: self.rootProject,
                    title: nls.projectSummaryRevisionsAllTenderers + " (" + nls[type] + ")",
                    companies: companies,
                    data: data,
                    selectedTenderers: tenderers,
                    selectedElements: elements,
                    billId: self.billId,
                    type: type
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openItemRateAndTotalPerUnitPreviewDialogForAllTenderers: function(type) {
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedItemStore = self.gridOpts.gridContainer.viewTendererItemPreviewStore[self.billId],
                seletedTenderStore = self.gridOpts.builderContainer.selectedTenderers,
                tenderers = [],
                items = [];

            seletedTenderStore.query().forEach(function(tenderer) {
                tenderers.push(tenderer.id);
            });

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getSelectedItemPerUnitByAllTenderers', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    tendererIds: JSON.stringify(self.arrayUnique(tenderers)),
                    itemIds: JSON.stringify(self.arrayUnique(items)),
                    type: type
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemRateAndTotalPerUnitGridDialog({
                    project: self.rootProject,
                    title: nls.itemRateAndTotalPerUnitAllTenderers + " (" + nls[type] + ")",
                    companies: companies,
                    data: data,
                    selectedTenderers: tenderers,
                    selectedItems: items,
                    billId: self.billId,
                    elementId: self.elementId,
                    type: type
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openElementPreviewDialogAmountPerUnitTypeForSelectedTenderer: function() {
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedElementStore = self.gridOpts.gridContainer.viewTendererElementPreviewStore[self.billId],
                seletedTenderStore = self.gridOpts.builderContainer.selectedTenderers,
                tenderers = [],
                elements = [];

            seletedTenderStore.query().forEach(function(tenderer) {
                tenderers.push(tenderer.id);
            });

            selectedElementStore.query().forEach(function(element) {
                elements.push(element.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getPrintingSelectedElementSummaryPerUnitTypeByTenderer', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    tendererIds: JSON.stringify(self.arrayUnique(tenderers)),
                    elementIds: JSON.stringify(self.arrayUnique(elements))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedElementByTypeAndTendererGridDialog({
                    project: self.rootProject,
                    label: nls.summaryPerUnit,
                    companies: companies,
                    unitTypes: data.unitTypes,
                    data: data.item,
                    selectedTenderers: tenderers,
                    selectedElements: elements,
                    billId: self.billId
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