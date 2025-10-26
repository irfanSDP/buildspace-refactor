define('buildspace/apps/PostContractReport/BillManager/StandardBillGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojo/json",
    "dojo/request",
    "dojo/aspect",
    "dojo/store/Memory",
    "dijit/Menu",
    'dojo/number',
    "dijit/CheckedMenuItem",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dijit/DropDownMenu',
    'dijit/form/DropDownButton',
    'dijit/MenuItem',
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    "buildspace/widget/grid/cells/Formatter",
    './PrintPreviewElementDialog',
    './PrintPreviewElementWithWorkDoneOnlyDialog',
    './PrintPreviewItemDialog',
    './PrintPreviewItemWorkDoneOnlyWithQtyDialog',
    './PrintPreviewItemWorkDoneOnlyWithPercentageDialog',
    'dojo/i18n!buildspace/nls/PostContract',
    'buildspace/widget/grid/cells/TextBox'
], function(declare, lang, connect, array, domAttr, JSON, request, aspect, Memory, Menu, number, MenuCheckedItem, MenuPlugin, Selector, Rearrange, IndirectSelection, FormulatedColumn, DropDownMenu, DropDownButton, MenuItem, evt, keys, focusUtil, html, xhr, PopupMenuItem, MenuSeparator, GridFormatter, PrintPreviewElementDialog, PrintPreviewElementWithWorkDoneOnlyDialog, PrintPreviewItemDialog, PrintPreviewItemWorkDoneOnlyWithQtyDialog, PrintPreviewItemWorkDoneOnlyWithPercentageDialog, nls){

    var StandardBillGrid = declare('buildspace.apps.PostContractReport.BillManager.StandardBillGrid', dojox.grid.EnhancedGrid, {
        typeItemId: -1,
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
        markupSettings: null,
        parentGrid: null,
        elementGridStore: null,
        selectedClaimRevision: null,
        claimRevision: null,
        updateUrl: null,
        typeItem: null,
        project: null,
        gridContainer: null,
        constructor:function(args){
            this.type            = args.type;
            this.currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            var formatter = this.formatter = new GridFormatter();

            this.setColumnStructure();
            this.createHeaderCtxMenu();

            this.plugins = {indirectSelection: {headerSelector:true, width:"40px", styles:"text-align: center;"}};

            this.inherited(arguments);
        },
        postCreate: function() {
            var self = this, storeName;
            self.inherited(arguments);

            if ( self.type === 'tree' ) {
                storeName = 'itemPreviewStore';
            } else {
                storeName = 'elementPreviewStore';
            }

            aspect.after(self, "_onFetchComplete", function() {
                self.gridContainer.markedCheckBoxObject(self, self.gridContainer[storeName][self.typeItemId]);
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
                    self.gridContainer.itemPreviewStore[self.typeItemId].put({ id:item.id[0] });

                    return self.getAffectedElementsAndTypesByItems(item, 'add');
                } else {
                    self.gridContainer.elementPreviewStore[self.typeItemId].put({ id:item.id[0] });

                    return self.getAffectedItemsAndTypesByElement(item, 'add');
                }
            } else {
                if (self.type === 'tree') {
                    self.gridContainer.itemPreviewStore[self.typeItemId].remove(item.id[0]);

                    self.removedIds.push(item.id[0]);

                    return self.getAffectedElementsAndTypesByItems(item, 'remove');
                } else {
                    self.gridContainer.elementPreviewStore[self.typeItemId].remove(item.id[0]);

                    self.removedIds.push(item.id[0]);

                    return self.getAffectedItemsAndTypesByElement(item, 'remove');
                }
            }
        },
        toggleAllSelection: function(checked) {
            var self = this, selection = this.selection, storeName;

            // used to store removeable selection
            self.removedIds = [];

            if ( self.type === 'tree' ) {
                storeName = 'itemPreviewStore';
            } else {
                storeName = 'elementPreviewStore';
            }

            if (checked) {
                selection.selectRange(0, self.rowCount-1);
                self.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item.id > 0) {
                                self.gridContainer[storeName][self.typeItemId].put({ id: item.id[0] });
                            }
                        });
                    }
                });

                if (self.type === 'tree') {
                    return self.getAffectedElementsAndTypesByItems(null , 'add');
                } else {
                    return self.getAffectedItemsAndTypesByElement(null, 'add');
                }
            } else {
                selection.deselectAll();

                self.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item.id > 0) {
                                self.gridContainer[storeName][self.typeItemId].remove(item.id[0]);

                                self.removedIds.push(item.id[0]);
                            }
                        });
                    }
                });

                if (self.type === 'tree') {
                    return self.getAffectedElementsAndTypesByItems(null, 'remove');
                } else {
                    return self.getAffectedItemsAndTypesByElement(null, 'remove');
                }
            }
        },
        getAffectedItemsAndTypesByElement: function(element, type) {
            var self = this,
                selectedItemStore = self.gridContainer.elementPreviewStore[self.typeItemId],
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
                for (var elementKeyIndex in self.removedIds) {
                    elements.push(self.removedIds[elementKeyIndex]);
                }
            }

            request.post('postContractStandardBillClaimReporting/getAffectedTypesAndItems', {
                handleAs: 'json',
                data: {
                    typeId: self.typeItemId,
                    bill_id: self.billId,
                    element_ids: JSON.stringify(self.borderContainerWidget.arrayUnique(elements))
                }
            }).then(function(data) {
                var typeGrid = dijit.byId('postContractReport-type-page-container-' + self.billId);

                if ( type === 'add' ) {
                    for (var typeId in data) {
                        typeGrid.store.fetchItemByIdentity({
                            identity: typeId,
                            onItem: function(node) {
                                if ( ! node ) {
                                    return;
                                }

                                self.gridContainer.typesPreviewStore.put({ id: typeId });

                                return typeGrid.rowSelectCell.toggleRow(node._0, true);
                            }
                        });

                        for (var elementId in data[typeId]) {
                            for (var itemId in data[typeId][elementId]) {
                                self.gridContainer.itemPreviewStore[self.typeItemId].put({ id: data[typeId][elementId][itemId] });
                            }
                        }
                    }
                } else {
                    for (var typeId in data) {
                        for (var elementId in data[typeId]) {
                            self.gridContainer.elementPreviewStore[self.typeItemId].remove(elementId);

                            for (var itemId in data[typeId][elementId]) {
                                self.gridContainer.itemPreviewStore[self.typeItemId].remove(data[typeId][elementId][itemId]);
                            }
                        }

                        // remove checked type selection if no element is selected in the current type
                        typeGrid.store.fetchItemByIdentity({
                            identity: typeId,
                            onItem: function(node) {
                                if ( ! node ) {
                                    return;
                                }

                                if ( self.gridContainer.elementPreviewStore[self.typeItemId].data.length === 0 ) {
                                    self.gridContainer.typesPreviewStore.remove(typeId);

                                    return typeGrid.rowSelectCell.toggleRow(node._0, false);
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
        getAffectedElementsAndTypesByItems: function(item, type) {
            var self = this,
                items = [];

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            if (type === 'add') {
                self.gridContainer.itemPreviewStore[self.typeItemId].query().forEach(function(item) {
                    items.push(item.id);
                });
            } else {
                for (var itemKeyIndex in self.removedIds) {
                    items.push(self.removedIds[itemKeyIndex]);
                }
            }

            request.post('postContractStandardBillClaimReporting/getAffectedTypesAndElements', {
                handleAs: 'json',
                data: {
                    typeId: self.typeItemId,
                    bill_id: self.billId,
                    itemIds: JSON.stringify(self.borderContainerWidget.arrayUnique(items))
                }
            }).then(function(data) {
                var typeGrid    = dijit.byId('postContractReport-type-page-container-' + self.billId);
                var elementGrid = dijit.byId('postContractReport-element-page-container-' + self.billId);

                if ( type === 'add' ) {
                    for (var typeId in data) {
                        typeGrid.store.fetchItemByIdentity({
                            identity: typeId,
                            onItem: function(node) {
                                if ( ! node ) {
                                    return;
                                }

                                self.gridContainer.typesPreviewStore.put({ id: typeId });

                                return typeGrid.rowSelectCell.toggleRow(node._0, true);
                            }
                        });

                        for (var elementId in data[typeId]) {
                            elementGrid.grid.store.fetchItemByIdentity({
                                identity: elementId,
                                onItem: function(node) {
                                    if ( ! node ) {
                                        return;
                                    }

                                    self.gridContainer.elementPreviewStore[self.typeItemId].put({ id: elementId });

                                    return elementGrid.grid.rowSelectCell.toggleRow(node._0, true);
                                }
                            });
                        }
                    }
                } else {
                    for (var typeId in data) {
                        for (var elementId in data[typeId]) {
                            for (var itemIdIndex in data[typeId][elementId]) {
                                self.gridContainer.itemPreviewStore[self.typeItemId].remove(data[typeId][elementId][itemIdIndex]);
                            }

                            elementGrid.grid.store.fetchItemByIdentity({
                                identity: elementId,
                                onItem: function(node) {
                                    if ( ! node ) {
                                        return;
                                    }

                                    if ( self.gridContainer.itemPreviewStore[self.typeItemId].data.length === 0 ) {
                                        self.gridContainer.elementPreviewStore[self.typeItemId].remove(elementId);

                                        return elementGrid.grid.rowSelectCell.toggleRow(node._0, false);
                                    }
                                }
                            });
                        }

                        typeGrid.store.fetchItemByIdentity({
                            identity: typeId,
                            onItem: function(node) {
                                if ( ! node ) {
                                    return;
                                }

                                if ( self.gridContainer.elementPreviewStore[self.typeItemId].data.length === 0 ) {
                                    self.gridContainer.typesPreviewStore.remove(typeId);

                                    return typeGrid.rowSelectCell.toggleRow(node._0, false);
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
            //do nothing
        },
        setColumnStructure: function(){
            var formatter = this.formatter;

            if(this.type == 'tree')
            {
                this.structure = {
                    noscroll: false,
                    cells: [
                        [{
                            name: 'No.',
                            field: 'id',
                            width:'30px',
                            styles:'text-align:center;',
                            formatter: formatter.rowCountCellFormatter,
                            rowSpan : 2
                        },{
                            name: nls.billReference,
                            field: 'bill_ref',
                            styles: "text-align:center; color: red;",
                            width: '80px',
                            noresize: true,
                            showInCtxMenu: true,
                            formatter: formatter.billRefCellFormatter,
                            rowSpan:2,
                            hidden: true
                        },{
                            name: nls.description,
                            field: 'description',
                            width:'500px',
                            formatter: formatter.claimTreeCellFormatter,
                            showInCtxMenu: true,
                            rowSpan : 2
                        },{
                            name: nls.type,
                            field: 'type',
                            width:'70px',
                            rowSpan : 2,
                            type: 'dojox.grid.cells.Select',
                            formatter: formatter.typeCellFormatter,
                            styles:'text-align:center;',
                            showInCtxMenu: true,
                            hidden: true
                        },{
                            name: nls.qty,
                            field: 'qty_per_unit',
                            width:'90px',
                            styles:'text-align: right;',
                            formatter: formatter.claimQtyPerUnitCellFormatter,
                            showInCtxMenu: true,
                            noresize: true,
                            rowSpan : 2
                        },{
                            name: nls.unit,
                            field: 'uom_symbol',
                            width:'70px',
                            styles:'text-align: center;',
                            formatter: formatter.unitIdCellFormatter,
                            showInCtxMenu: true,
                            rowSpan : 2
                        },{
                            name: nls.rate,
                            field: 'rate',
                            width:'70px',
                            styles:'text-align: right;',
                            formatter: formatter.claimRateLSCellFormatter,
                            showInCtxMenu: true,
                            rowSpan : 2
                        },{
                            name: nls.total,
                            field: 'total_per_unit',
                            width:'90px',
                            styles:'text-align: right;color:blue;',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            showInCtxMenu: true,
                            rowSpan : 2
                        },{
                            name: nls.percent,
                            field: 'prev_percentage',
                            width:'70px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.unEditablePercentageCellFormatter,
                            styles:'text-align: right;'
                        },{
                            name: nls.amount,
                            field: 'prev_amount',
                            width:'110px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            styles:'text-align: right;'
                        },{
                            name: nls.percent,
                            field: 'current_percentage',
                            width:'70px',
                            headerClasses: "typeHeader2",
                            styles:'text-align: right;',
                            formatter: formatter.editableClaimPercentageCellFormatter
                        },{
                            name: nls.amount,
                            field: 'current_amount',
                            width:'110px',
                            headerClasses: "typeHeader2",
                            styles:'text-align: right;',
                            formatter: formatter.claimAmountCellFormatter
                        },{
                            name: nls.percent,
                            field: 'up_to_date_percentage',
                            width:'70px',
                            headerClasses: "typeHeader1",
                            styles:'text-align: right;',
                            formatter: formatter.editableClaimPercentageCellFormatter
                        },{
                            name: nls.qty,
                            field: 'up_to_date_qty',
                            width:'90px',
                            headerClasses: "typeHeader1",
                            styles:'text-align: right;',
                            formatter: formatter.claimAmountCellFormatter
                        },{
                            name: nls.amount,
                            field: 'up_to_date_amount',
                            width:'110px',
                            headerClasses: "typeHeader1",
                            styles:'text-align: right;',
                            formatter: formatter.claimAmountCellFormatter
                        }],
                        [{
                            name: nls.previousClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader1",
                            colSpan : 2
                        },{
                            name: nls.currentClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader2",
                            colSpan : 2
                        },{
                            name: nls.upToDateClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader1",
                            colSpan : 3
                        }]
                    ]
                };
            }
            else
            {
                this.structure = {
                    noscroll: false,
                    cells: [
                        [{
                            name: 'No.',
                            field: 'id',
                            width:'30px',
                            styles:'text-align:center;',
                            formatter: formatter.rowCountCellFormatter,
                            noresize: true,
                            rowSpan : 2
                        }, {
                            name: nls.description,
                            field: 'description',
                            width:'auto',
                            noresize: true,
                            formatter: formatter.claimTreeCellFormatter,
                            rowSpan : 2
                        },{
                            name: nls.total,
                            field: 'total_per_unit',
                            width:'90px',
                            styles:'text-align: right;color:blue;',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true,
                            rowSpan : 2
                        },{
                            name: nls.percent,
                            field: 'prev_percentage',
                            width:'70px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.unEditablePercentageCellFormatter,
                            styles:'text-align: right;',
                            noresize: true
                        },{
                            name: nls.amount,
                            field: 'prev_amount',
                            width:'110px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            styles:'text-align: right;',
                            noresize: true
                        },{
                            name: nls.percent,
                            field: 'current_percentage',
                            width:'70px',
                            headerClasses: "typeHeader2",
                            styles:'text-align: right;',
                            formatter: formatter.unEditablePercentageCellFormatter,
                            noresize: true
                        },{
                            name: nls.amount,
                            field: 'current_amount',
                            width:'110px',
                            headerClasses: "typeHeader2",
                            styles:'text-align: right;',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true
                        },{
                            name: nls.percent,
                            field: 'up_to_date_percentage',
                            width:'70px',
                            headerClasses: "typeHeader1",
                            styles:'text-align: right;',
                            formatter: formatter.elementClaimEditablePercentageCellFormatter
                        },{
                            name: nls.amount,
                            field: 'up_to_date_amount',
                            width:'110px',
                            headerClasses: "typeHeader1",
                            styles:'text-align: right;',
                            formatter: formatter.elementClaimAmountCellFormatter
                        }],
                        [{
                            name: nls.previousClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader1",
                            colSpan : 2,
                            noresize: true
                        },{
                            name: nls.currentClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader2",
                            colSpan : 2,
                            noresize: true
                        },{
                            name: nls.upToDateClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader1",
                            colSpan : 2
                        }]
                    ]
                };
            }
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
        createHeaderCtxMenu: function(){
            if (typeof this.structure !== 'undefined') {
                var column = this.structure.cells[0],
                    self = this,
                    menusObject = {
                        headerMenu: new dijit.Menu()
                    };
                dojo.forEach(column, function(data, index)
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

                                self.showHideColumn(show, index);
                            }
                        }));
                    }
                });

                this.plugins = {menus: menusObject};
            }
        },
        showHideColumn: function(show, index) {
            this.beginUpdate();
            this.layout.setColumnVisibility(index, show);
            this.endUpdate();
        }
    });

    return declare('buildspace.apps.PostContractReport.BillManager.StandardBillGridBuilder', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: '',
        rootProject: null,
        billId: -1,
        elementId: 0,
        disableEditing: false,
        itemId: -1,
        rowSelector: null,
        typeItem: null,
        gridOpts: {},
        type: null,
        postCreate: function(){
            var self = this;

            self.inherited(arguments);

            lang.mixin(self.gridOpts, { billId: self.billId, typeItem: self.typeItem, project:self.rootProject, elementId: self.elementId, type:self.type, region:"center", disableEditing: self.disableEditing, borderContainerWidget: self });

            var grid = this.grid = new StandardBillGrid(self.gridOpts);
            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important; border-bottom:none; padding:2px; width:100%;"});

            if ( this.type === 'element' ) {
                var menuElement = new DropDownMenu({ style: "display: none;"});

                var menuElementItemOne = new MenuItem({
                    label: nls.elementDetail,
                    onClick: function() {
                        self.openSelectedElementDetailPrintPreview();
                    }
                });
                menuElement.addChild(menuElementItemOne);

                var menuElementItemTwo = new MenuItem({
                    label: nls.elementWorkDoneOnly,
                    onClick: function() {
                        self.openElementWorkDoneOnlyDetailPrintPreview();
                    }
                });
                menuElement.addChild(menuElementItemTwo);

                toolbar.addChild(
                    new DropDownButton({
                        label: nls.elements,
                        iconClass: "icon-16-container icon-16-print",
                        dropDown: menuElement
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
            }

            var menuItem = new DropDownMenu({ style: "display: none;"});

            var menuItemItemOne = new MenuItem({
                label: nls.allItems,
                onClick: function() {
                    self.openSelectedItemPrintPreviewDialog();
                }
            });
            menuItem.addChild(menuItemItemOne);

            var menuItemItemTwo = new MenuItem({
                label: nls.itemsWithCurrentClaimMoreThanZero,
                onClick: function() {
                    self.openItemsWithCurrentClaimPrintPreviewDialog();
                }
            });
            menuItem.addChild(menuItemItemTwo);

            var menuItemItemThree = new MenuItem({
                label: nls.itemsWithClaim,
                onClick: function() {
                    self.openItemsWithClaimPrintPreviewDialog();
                }
            });
            menuItem.addChild(menuItemItemThree);

            var menuItemItemFour = new MenuItem({
                label: nls.workDoneOnlyWithQty,
                onClick: function() {
                    self.openItemsWorkDoneQtyPrintPreviewDialog();
                }
            });
            menuItem.addChild(menuItemItemFour);

            var menuItemItemFive = new MenuItem({
                label: nls.workDoneOnlyWithPercent,
                onClick: function() {
                    self.openItemsWorkDonePercentagePrintPreviewDialog();
                }
            });
            menuItem.addChild(menuItemItemFive);

            toolbar.addChild(
                new DropDownButton({
                    label: nls.items,
                    iconClass: "icon-16-container icon-16-print",
                    dropDown: menuItem
                })
            );

            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('postContractReportStandardBill' + this.billId + '-stackContainer');

            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(self.stackContainerTitle, 60),
                    id: self.pageId
                }, node);
                container.addChild(child);
                child.set('content', self);
                child.set('grid', self.grid);
                container.selectChild(self.pageId);
            }
        },
        openSelectedElementDetailPrintPreview: function() {
            var self = this,
                selectedElementStore = self.gridOpts.gridContainer.elementPreviewStore[self.gridOpts.typeItemId],
                elements = [];

            selectedElementStore.query().forEach(function(item) {
                elements.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractStandardBillClaimReporting/getPrintingSelectedElementClaims', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    type_ref_id: self.typeItem.id,
                    itemIds: JSON.stringify(self.arrayUnique(elements))
                }
            }).then(function(data) {
                var dialog = new PrintPreviewElementDialog({
                    project: self.rootProject,
                    type_ref_id: self.typeItem.id,
                    billId: self.billId,
                    title: nls.elementDetail,
                    data: data,
                    selectedItems: elements
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openElementWorkDoneOnlyDetailPrintPreview: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractStandardBillClaimReporting/getPrintingElementWorkDoneOnlyClaims', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    type_ref_id: self.typeItem.id
                }
            }).then(function(data) {
                var dialog = new PrintPreviewElementWithWorkDoneOnlyDialog({
                    project: self.rootProject,
                    type_ref_id: self.typeItem.id,
                    billId: self.billId,
                    title: nls.elementWorkDoneOnly,
                    data: data
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openSelectedItemPrintPreviewDialog: function() {
            var self = this,
                selectedItemStore = self.gridOpts.gridContainer.itemPreviewStore[self.gridOpts.typeItemId],
                items = [];

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractStandardBillClaimReporting/getPrintingSelectedItemClaims', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    type_ref_id: self.typeItem.id,
                    itemIds: JSON.stringify(self.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintPreviewItemDialog({
                    project: self.rootProject,
                    type_ref_id: self.typeItem.id,
                    billId: self.billId,
                    title: nls.allItems,
                    printURL: 'printReport/printPostContractItem',
                    exportURL: 'exportExcelReport/exportPostContractItem',
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
        openItemsWithCurrentClaimPrintPreviewDialog: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractStandardBillClaimReporting/getPrintingPreviewItemsWithCurrentClaim', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    type_ref_id: self.typeItem.id
                }
            }).then(function(data) {
                var dialog = new PrintPreviewItemDialog({
                    project: self.rootProject,
                    type_ref_id: self.typeItem.id,
                    billId: self.billId,
                    title: nls.itemsWithCurrentClaimMoreThanZero,
                    printURL: 'printReport/printPostContractItemWithCurrentClaim',
                    exportURL: 'exportExcelReport/exportPostContractItemWithCurrentClaim',
                    data: data
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openItemsWithClaimPrintPreviewDialog: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractStandardBillClaimReporting/getPrintingPreviewItemsWithClaim', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    type_ref_id: self.typeItem.id
                }
            }).then(function(data) {
                var dialog = new PrintPreviewItemDialog({
                    project: self.rootProject,
                    type_ref_id: self.typeItem.id,
                    billId: self.billId,
                    title: nls.itemsWithClaims,
                    printURL: 'printReport/printPostContractItemWithClaim',
                    exportURL: 'exportExcelReport/exportPostContractItemWithClaim',
                    data: data
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openItemsWorkDoneQtyPrintPreviewDialog: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractStandardBillClaimReporting/getPrintingPreviewItemsWorkDoneWithQty', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    type_ref_id: self.typeItem.id
                }
            }).then(function(data) {
                var dialog = new PrintPreviewItemWorkDoneOnlyWithQtyDialog({
                    project: self.rootProject,
                    type_ref_id: self.typeItem.id,
                    billId: self.billId,
                    title: nls.workDoneOnlyWithQty,
                    data: data
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openItemsWorkDonePercentagePrintPreviewDialog: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractStandardBillClaimReporting/getPrintingPreviewItemsWorkDoneWithPercentage', {
                handleAs: 'json',
                data: {
                    bill_id: self.billId,
                    type_ref_id: self.typeItem.id
                }
            }).then(function(data) {
                var dialog = new PrintPreviewItemWorkDoneOnlyWithPercentageDialog({
                    project: self.rootProject,
                    type_ref_id: self.typeItem.id,
                    billId: self.billId,
                    title: nls.workDoneOnlyWithPercent,
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