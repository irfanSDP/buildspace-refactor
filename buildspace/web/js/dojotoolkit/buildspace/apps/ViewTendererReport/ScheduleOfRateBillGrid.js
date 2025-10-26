define('buildspace/apps/ViewTendererReport/ScheduleOfRateBillGrid',[
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
    './PrintPreviewDialog/PrintSelectedItemRateGridDialog',
    './PrintPreviewDialog/PrintSelectedItemTotalGridDialog',
    './PrintPreviewDialog/PrintSelectedItemRateAndTotalGridDialog',
    './PrintPreviewDialog/PrintSelectedElementByTypeAndTendererGridDialog',
    './PrintPreviewDialog/PrintSelectedScheduleOfRateItemGridDialog',
    'dojo/i18n!buildspace/nls/TenderingReport'
], function(declare, lang, connect, array, domAttr, JSON, request, aspect, Menu, Rearrange, IndirectSelection, FormulatedColumn, evt, keys, focusUtil, Memory, DropDownMenu, DropDownButton, MenuItem, PopupMenuItem, GridFormatter, PrintSelectedElementGridDialog, PrintSelectedItemRateGridDialog, PrintSelectedItemTotalGridDialog, PrintSelectedItemRateAndTotalGridDialog, PrintSelectedElementByTypeAndTendererGridDialog, PrintSelectedScheduleOfRateItemGridDialog, nls) {

    var ScheduleOfRateBillGrid = declare('buildspace.apps.ViewTendererReport.ScheduleOfRateBillGrid', dojox.grid.EnhancedGrid, {
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

            request.post('viewTendererReporting/getAffectedScheduleOfRateBillsAndItems', {
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

            request.post('viewTendererReporting/getAffectedScheduleOfRateBillsAndElements', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.borderContainerWidget.arrayUnique(items))
                }
            }).then(function(data) {
                // remove existing bill and element record
                self.gridContainer.viewTendererBillPreviewStore                 = new Memory({ idProperty: 'id' });
                self.gridContainer.viewTendererElementPreviewStore[self.billId] = new Memory({ idProperty: 'id' });

                self.ScheduleOfRateupdateElementAndBillGridSelectBox(data);

                pb.hide();
            }, function(error) {
                pb.hide();
                console.log(error);
            });
        },
        ScheduleOfRateupdateElementAndBillGridSelectBox: function(data) {
            var self        = this;
            var billGrid    = dijit.byId('viewTenderer-bill-page-container-' + self.project.id);
            var elementGrid = dijit.byId('viewTenderer-sorb_element-page-container-' + self.billId);

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
            var columnToDisplay;
            var fixedColumns;

            // If [item level], else [element level]
            if(this.type == 'tree')
            {
                if(this.tender_companies.length > 0)
                {
                    descriptionWidth = '500px';
                }

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
                                showInCtxMenu: true
                            },
                            {
                                name: nls.description,
                                field: 'description',
                                width: descriptionWidth,
                                formatter: formatter.treeCellFormatter,
                                noresize: true,
                                showInCtxMenu: true
                            },{
                                name: nls.type,
                                field: 'type',
                                width: '70px',
                                styles: 'text-align:center;',
                                formatter: formatter.typeCellFormatter,
                                noresize: true,
                                showInCtxMenu: true
                            },{
                                name: nls.unit,
                                field: 'uom_id',
                                width: '50px',
                                styles: 'text-align:center;',
                                formatter: formatter.unitIdCellFormatter,
                                noresize: true,
                                showInCtxMenu: true
                            },{
                                name: nls.estimationRate + " ("+this.currencySetting+")",
                                field: 'estimation_rate',
                                styles: "text-align:right;",
                                width: '120px',
                                formatter: formatter.rateAfterMarkupCellFormatter,
                                noresize: true
                            }]
                        ]
                    };
                var fixedColumnsAfterTypeColumns = [];

                columnToDisplay = this.generateContractorRateColumn(fixedColumns);
            }
            else
            {
                if(this.tender_companies.length > 5)
                {
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
                                formatter: formatter.treeCellFormatter,
                                noresize: true
                            }]
                        ]
                    };
                fixedColumnsAfterTypeColumns = [];

                columnToDisplay = fixedColumns;
            }

            dojo.forEach(fixedColumnsAfterTypeColumns,function(column){
                columnToDisplay.cells[0].push(column);
            });

            this.structure = columnToDisplay;
        },
        generateContractorRateColumn: function(fixedColumns){
            var self = this;
            var companies = this.tender_companies;
            var colCount = 0;

            dojo.forEach(companies, function(company){

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

                fixedColumns.cells[0].push({
                    name: companyName,
                    field: company.id + '-contractor_rate',
                    width: '185px',
                    styles: "text-align:right;",
                    formatter: self.formatter.companyRateCurrencyCellFormatter
                });

            });

            return fixedColumns;
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

    return declare('buildspace.apps.ViewTendererReport.ScheduleOfRateBillGridBuilder', dijit.layout.BorderContainer, {
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

            var grid    = this.grid = new ScheduleOfRateBillGrid(self.gridOpts);
            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important; border-bottom:none; padding:2px; width:100%;"});

            var sortOptions = ['summaryAllTenderersLowToHighest', 'summaryAllTenderersHighToLowest'];

            if ( this.type === 'element' ) {
                var menuElement = new DropDownMenu({ style: "display: none;"});
                var pSubMenu    = new Menu();

                dojo.forEach(sortOptions, function(opt) {
                    var menuItem = new MenuItem({
                        id: opt+"-"+self.rootProject.id+'-'+self.type+"-menuElement",
                        label: nls[opt],
                        onClick: function() {
                            self.openElementPreviewDialogForAllTenderers(opt);
                        }
                    });
                    pSubMenu.addChild(menuItem);
                });

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
            // create tool bar item for menu item rate
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
        openItemRatePreviewDialogForAllTenderers: function(type) {
            // Item Rate (All Tenderers)
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedItemStore = self.gridOpts.gridContainer.viewTendererItemPreviewStore[self.billId],
                selectedTenderStore = self.gridOpts.builderContainer.selectedTenderers,
                tenderers = [],
                items = [];

            selectedTenderStore.query().forEach(function(tenderer) {
                tenderers.push(tenderer.id);
            });

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('viewTendererReporting/getSelectedScheduleOfRateItemByAllTenderers', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    tendererIds: JSON.stringify(self.arrayUnique(tenderers)),
                    itemIds: JSON.stringify(self.arrayUnique(items)),
                    type: type
                }
            }).then(function(data) {
                var dialog = new PrintSelectedScheduleOfRateItemGridDialog({
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
        arrayUnique: function(array) {
            return array.reverse().filter(function (e, i, arr) {
                return arr.indexOf(e, i+1) === -1;
            }).reverse();
        }
    });
});