define('buildspace/apps/ViewTendererReport/SupplyOfMaterialBillGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojo/request",
    "dojo/aspect",
    'dojo/store/Memory',
    "dijit/Menu",
    "dojox/grid/enhanced/plugins/IndirectSelection",    
    'dojo/number',
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    'buildspace/widget/grid/cells/Textarea',
    "buildspace/widget/grid/cells/Formatter",
    './PrintPreviewDialog/PrintSelectedSupplyOfMaterialItemGridDialog',
    'dojo/i18n!buildspace/nls/TenderingReport',
    'dojo/on',
    'dojox/grid/_Grid',
    "dojox/widget/PlaceholderMenuItem",
    'buildspace/widget/grid/cells/TextBox'
], function(declare, lang, connect, array, domAttr, request, aspect, Memory, Menu, IndirectSelection, number, evt, keys, focusUtil, html, xhr, PopupMenuItem, MenuSeparator, Textarea, GridFormatter, PrintSelectedSupplyOfMaterialItemGridDialog, nls, on){

    var BillGrid = declare('buildspace.apps.ViewTendererReport.SupplyOfMaterialBillGrid', dojox.grid.EnhancedGrid, {
        type: null,
        billId: -1,
        elementId: 0,
        itemId: -1,
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        keepSelection: true,
        rowSelector: '0px',
        parentGrid: null,
        elementGridStore: null,
        updateUrl: null,
        project: null,
        constructor:function(args){
            this.type             = args.type;
            this.tender_setting   = args.tender_setting;
            this.tender_companies = args.tender_companies;
            this.currencySetting  = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            var formatter = this.formatter = new GridFormatter();

            this.companyColumnChildren = [
                {name: nls.estimatedQty+"<br/>("+nls.includeWastage+")<br />(A)", field_name: 'estimated_qty', width: '85px', cellType:'buildspace.widget.grid.cells.TextBox', styles: "text-align:right;", editable: true, formatter: formatter.currencyCellFormatter },
                {name: "% "+nls.ofWastageAllowed, field_name: 'percentage_of_wastage', width: '65px', cellType:'buildspace.widget.grid.cells.TextBox', styles: "text-align:right;", editable: true, formatter: formatter.currencyCellFormatter},
                {name: nls.contractorRate+"<br />(X)", field_name: 'contractor_supply_rate', width: '85px', cellType:'buildspace.widget.grid.cells.TextBox', styles: "text-align:right;", editable: true, formatter: formatter.currencyCellFormatter },
                {name: nls.difference+"<br />(B)=(Y)-(X)", field_name: 'difference', width: '85px', styles: "text-align:right;", formatter: formatter.unEditableCurrencyCellFormatter },
                {name: nls.amount+ " ("+this.currencySetting+")<br />(C)=(A)x(B)", field_name: 'amount', width: '85px', styles: "text-align:right;", formatter: formatter.unEditableCurrencyCellFormatter }
            ];

            this.setColumnStructure();

            this.plugins = {indirectSelection: {headerSelector:true, width:"40px", styles:"text-align: center;"}};

            this.inherited(arguments);
        },
        setColumnStructure: function(){
            var formatter = this.formatter;

            var descriptionWidth = 'auto';
            var fixedColumns, fixedColumnsAfterTypeColumns, columnToDisplay;

            if(this.type == 'tree'){
                if(this.tender_companies.length > 0){
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
                            showInCtxMenu: true,
                            rowSpan:2
                        },{
                            name: nls.description,
                            field: 'description',
                            width: descriptionWidth,
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan:2
                        },{
                            name: nls.type,
                            field: 'type',
                            width: '70px',
                            styles: 'text-align:center;',
                            formatter: formatter.typeCellFormatter,
                            noresize: true,
                            rowSpan:2
                        },{
                            name: nls.unit,
                            field: 'uom_id',
                            width: '50px',
                            editable: false,
                            styles: 'text-align:center;',
                            formatter: formatter.unitIdCellFormatter,
                            noresize: true,
                            rowSpan:2
                        },{
                            name: nls.supplyRate,
                            field: 'supply_rate',
                            styles: "text-align:right;",
                            width: '100px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true,
                            rowSpan:2
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
                            formatter: formatter.treeCellFormatter,
                            noresize: true
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
                parentCells = [];
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

                if(company.awarded){
                    companyName = '<span style="color:blue;">'+buildspace.truncateString(company.name, 28)+'</span>';
                }else{
                    companyName = buildspace.truncateString(company.name, 28);
                }

                var structure = {
                    name: companyName,
                    field: company.id+'-total',
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
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            if ( self.type === 'tree' ) {
                storeName = 'viewTendererSupplyOfMaterialItemPreviewStore';
            } else {
                storeName = 'viewTendererSupplyOfMaterialElementPreviewStore';
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
                storeName = 'viewTendererSupplyOfMaterialItemPreviewStore';
            } else {
                storeName = 'viewTendererSupplyOfMaterialElementPreviewStore';
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
                selectedItemStore = self.gridContainer.viewTendererSupplyOfMaterialElementPreviewStore[self.billId],
                elements = [];

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            if ( element ) {
                self.gridContainer.viewTendererSupplyOfMaterialElementPreviewStore[self.billId].put({ id: element.id[0] });
                elements.push(element.id[0]);
            } else {
                selectedItemStore.query().forEach(function(item) {
                    elements.push(item.id);
                });
            }

            request.post('viewTendererReporting/getAffectedSupplyOfMaterialBillsAndItems', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    element_ids: JSON.stringify(self.borderContainerWidget.arrayUnique(elements))
                }
            }).then(function(data) {
                var billGrid = dijit.byId('viewTenderer-bill-page-container-' + self.project.id);

                if ( type === 'add' ) {
                    for (var billId in data) {
                        for (var elementId in data[billId]) {
                            for (var itemId in data[billId][elementId]) {
                                self.gridContainer.viewTendererSupplyOfMaterialItemPreviewStore[self.billId].put({ id: data[billId][elementId][itemId] });
                            }
                        }
                    }
                } else {
                    for (var billId in data) {
                        for (var elementId in data[billId]) {
                            self.gridContainer.viewTendererSupplyOfMaterialElementPreviewStore[self.billId].remove(elementId);

                            for (var itemId in data[billId][elementId]) {
                                self.gridContainer.viewTendererSupplyOfMaterialItemPreviewStore[self.billId].remove(data[billId][elementId][itemId]);
                            }
                        }

                        // remove checked bill selection if no element is selected
                        billGrid.store.fetchItemByIdentity({
                            identity: billId,
                            onItem: function(node) {
                                if ( ! node ) {
                                    return;
                                }

                                if ( self.gridContainer.viewTendererSupplyOfMaterialElementPreviewStore[self.billId].data.length === 0 ) {
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
                    self.gridContainer.viewTendererSupplyOfMaterialItemPreviewStore[self.billId].remove(item.id[0]);
                } else {
                    self.gridContainer.viewTendererSupplyOfMaterialItemPreviewStore[self.billId].put({ id: item.id[0] });
                }
            }

            self.gridContainer.viewTendererSupplyOfMaterialItemPreviewStore[self.billId].query().forEach(function(item) {
                items.push(item.id);
            });

            request.post('viewTendererReporting/getAffectedSupplyOfMaterialBillsAndElements', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.borderContainerWidget.arrayUnique(items))
                }
            }).then(function(data) {
                // remove existing bill and element record
                self.gridContainer.viewTendererBillPreviewStore                 = new Memory({ idProperty: 'id' });
                self.gridContainer.viewTendererSupplyOfMaterialElementPreviewStore[self.billId] = new Memory({ idProperty: 'id' });

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
            var elementGrid = dijit.byId('viewTenderer-supplyOfMaterial-element-grid-' + self.billId);

            // clear previous selection
            billGrid.store.fetch({
                onItem: function(node) {
                    if ( ! node ) {
                        return;
                    }

                    return billGrid.rowSelectCell.toggleRow(node._0, false);
                }
            });

            elementGrid.store.fetch({
                onItem: function(node) {
                    if ( ! node ) {
                        return;
                    }

                    return elementGrid.rowSelectCell.toggleRow(node._0, false);
                }
            });

            for (var billId in data) {
                for (var elementId in data[billId]) {
                    elementGrid.store.fetchItemByIdentity({
                        identity: elementId,
                        onItem: function(node) {
                            if ( ! node ) {
                                return;
                            }

                            self.gridContainer.viewTendererSupplyOfMaterialElementPreviewStore[self.billId].put({ id: elementId });

                            return elementGrid.rowSelectCell.toggleRow(node._0, true);
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
        destroy: function() {
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.ViewTendererReport.SupplyOfMaterialBillGridBuilder', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;border:none;width:100%;height:100%;",
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
            var self = this;
            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important; border-bottom:none; padding:2px; width:100%;"});
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.print,
                    iconClass: "icon-16-container icon-16-print",
                    onClick: function(){
                        self.openPreviewDialogForAllTenderers();
                    }
                })
            );
            this.addChild(toolbar);

            lang.mixin(this.gridOpts, {
                billId: this.billId,
                project:this.rootProject,
                elementId: this.elementId,
                type: this.type,
                region:"center",
                borderContainerWidget: this
            });

            var grid = this.grid = new BillGrid(this.gridOpts);

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('viewTendererReportBreakdown'+this.rootProject.id+'-stackContainer');
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
        },
        openPreviewDialogForAllTenderers: function() {
            var self = this,
                companies = self.gridOpts.tender_companies,
                selectedItemStore = self.gridOpts.gridContainer.viewTendererSupplyOfMaterialItemPreviewStore[self.billId],
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

            request.post('viewTendererReporting/getSupplyOfMaterialItemRate', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    tendererIds: JSON.stringify(self.arrayUnique(tenderers)),
                    itemIds: JSON.stringify(self.arrayUnique(items)),
                }
            }).then(function(data) {
                var dialog = new PrintSelectedSupplyOfMaterialItemGridDialog({
                    project: self.rootProject,
                    title: nls.supplyOfMaterial,
                    companies: companies,
                    data: data,
                    selectedTenderers: tenderers,
                    selectedItems: items,
                    billId: self.billId,
                    elementId: self.elementId,
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