define('buildspace/apps/RationalizeRateReport/BillGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojo/json",
    "dojo/request",
    "dojo/aspect",
    "dijit/Menu",
    'dojo/number',
    "dijit/CheckedMenuItem",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/store/Memory',
    'dojo/_base/html',
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    "buildspace/widget/grid/cells/Formatter",
    './PrintPreviewDialog/PrintSelectedElementGridDialog',
    './PrintPreviewDialog/PrintSelectedItemRateGridDialog',
    './PrintPreviewDialog/PrintSelectedItemTotalGridDialog',
    'dojo/i18n!buildspace/nls/RationalizeRate'
], function(declare, lang, connect, array, domAttr, JSON, request, aspect, Menu, number, MenuCheckedItem, MenuPlugin, Selector, Rearrange, IndirectSelection, FormulatedColumn, evt, keys, focusUtil, Memory, html, xhr, PopupMenuItem, MenuSeparator, Textarea, FormulaTextBox, GridFormatter, PrintSelectedElementGridDialog, PrintSelectedItemRateGridDialog, PrintSelectedItemTotalGridDialog, nls){

    var BillGrid = declare('buildspace.apps.RationalizeRateReport.BillGrid', dojox.grid.EnhancedGrid, {
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
            this.currencySetting         = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            var formatter = this.formatter = new GridFormatter();

            this.rationalizedColumnChildren = [
                {name: nls.grandTotalQty, field_name: 'rationalized_grand_total_quantity', width: '90px', styles: "text-align:right;", formatter: formatter.unEditableNumberCellFormatter, },
                {name: nls.rate, field_name: 'rationalized_rate-value', width: '85px', styles: "text-align:right;", formatter: formatter.companyRateCurrencyCellFormatter },
                {name: nls.grandTotal, field_name: 'rationalized_grand_total_after_markup', width: '100px', styles: "text-align:right;", formatter: formatter.unEditableCurrencyCellFormatter}
            ];

            this.setColumnStructure();

            this.plugins = {indirectSelection: {headerSelector:true, width:"40px", styles:"text-align: center;"}};

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
                            width: 'auto',
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
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
                            showInCtxMenu: true,
                            rowSpan:2
                        },{
                            name: nls.unit,
                            field: 'uom_id',
                            width: '50px',
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

                var columnToDisplay = this.generateRationalizedRateColumn(fixedColumns);
            }
            else
            {
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
                            width: 'auto',
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
                fixedColumnsAfterTypeColumns = this.generateRationalizedGrandTotalColumn();

                var columnToDisplay = fixedColumns;
            }

            dojo.forEach(fixedColumnsAfterTypeColumns,function(column){
                columnToDisplay.cells[0].push(column);
            });

            this.structure = columnToDisplay;
        },
        generateRationalizedRateColumn: function(fixedColumns){
            var self = this,
                rationalizedColumnChildren = this.rationalizedColumnChildren,
                parentCells = [],
                childCells = [];

            var colspan = rationalizedColumnChildren.length;

            parentCells.push({
                name: nls.rationalizedRate,
                styles:'text-align:center;',
                headerClasses: "staticHeader typeHeader1",
                colSpan: colspan,
                hidden: false
            });

            var field = null;

            for(i=0;i<rationalizedColumnChildren.length;i++){

                field = rationalizedColumnChildren[i].field_name;

                var cellStructure = {
                    field: field,
                    columnType: "rationalizedColumn",
                    headerClasses: "typeHeader1"
                };

                lang.mixin(cellStructure, rationalizedColumnChildren[i]);

                fixedColumns.cells[0].push(cellStructure);
            }

            fixedColumns.cells.push(parentCells);

            return fixedColumns;
        },
        generateRationalizedGrandTotalColumn: function(){
            var columns = [],
                formatter = this.formatter;

            var structure = {
                name: nls.rationalizedRate,
                field: 'rationalized_overall_total_after_markup',
                styles: "text-align:right;",
                width: '120px',
                formatter: formatter.unEditableCurrencyCellFormatter,
                headerClasses: "typeHeader1",
                noresize: true
            };
            columns.push(structure);

            return columns;
        },
        canSort: function(inSortInfo){
            return false;
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

            request.post('rationalizeRateReport/getAffectedBillsAndItems', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    element_ids: JSON.stringify(self.borderContainerWidget.arrayUnique(elements))
                }
            }).then(function(data) {
                var billGrid = dijit.byId('rationalizedRate-bill-page-container-' + self.project.id);

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

            request.post('rationalizeRateReport/getAffectedBillsAndElements', {
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
            var billGrid    = dijit.byId('rationalizedRate-bill-page-container-' + self.project.id);
            var elementGrid = dijit.byId('rationalizedRate-element-page-container-' + self.billId);

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

    var BillGridContainer = declare('buildspace.apps.RationalizeRateReport.BillGridBuilder', dijit.layout.BorderContainer, {
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

            if ( this.type === 'element' ) {
                toolbar.addChild(
                    new dijit.form.Button({
                        id: self.rootProject.id+'-'+this.type+'-ProjectElementSummarySelectedTenderer-button',
                        label: nls.projectSummary,
                        iconClass: "icon-16-container icon-16-print",
                        onClick: function() {
                            self.openElementPreviewDialogForSelectedTender();
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
            }

            // ====================================================================================================================================
            // create tool bar item for menu item rate
            // ====================================================================================================================================
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id+'-'+this.type+'-ProjectItemRateSelectedTenderer-button',
                    label: nls.itemRate,
                    iconClass: "icon-16-container icon-16-print",
                    onClick: function() {
                        self.openItemRatePreviewDialogForSelectedTender();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            // ====================================================================================================================================

            // ====================================================================================================================================
            // create tool bar item for menu item total
            // ====================================================================================================================================
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id+'-'+this.type+'-ProjectItemTotalSelectedTenderer-button',
                    label: nls.itemTotal,
                    iconClass: "icon-16-container icon-16-print",
                    onClick: function() {
                        self.openItemTotalPreviewDialogForSelectedTender();
                    }
                })
            );
            // ====================================================================================================================================

            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('rationalizeRateReportBreakdown'+this.rootProject.id+'-stackContainer');
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

            request.post('rationalizeRateReport/getPrintingSelectedElementByTenderer', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    elementIds: JSON.stringify(self.arrayUnique(elements))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedElementGridDialog({
                    title: nls.projectSummary,
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

            request.post('rationalizeRateReport/getPrintingSelectedItemRateByTenderer', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemRateGridDialog({
                    project: self.rootProject,
                    title: nls.itemRate,
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

            request.post('rationalizeRateReport/getPrintingSelectedItemTotalByTenderer', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemTotalGridDialog({
                    project: self.rootProject,
                    title: nls.itemTotal,
                    billId: self.billId,
                    elementId: self.elementId,
                    data: data,
                    selectedItems: items
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

    return BillGridContainer;
});