define('buildspace/apps/SubPackageReport/SubPackageContainer',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/dom-style",
    "dojo/when",
    "dojo/currency",
    'dojo/aspect',
    'dojo/request',
    'dojo/store/Memory',
    "buildspace/widget/grid/cells/Formatter",
    './grid',
    'dojo/i18n!buildspace/nls/SubPackages'],
function(declare, lang, domStyle, when, currency, aspect, request, Memory, Formatter, SubPackagesGrid, nls) {

    return declare('buildspace.apps.SubPackageReport.SubPackageContainer', dijit.layout.BorderContainer, {
        region: "center",
        rootProject: null,
        gutters: false,
        style: "padding:0px;border:0px;margin:0px;width:100%;height:100%;",
        selectedBillStore: [],
        selectedElementStore: [],
        selectedItemStore: [],
        elementStore: [],
        elementItemStore: [],
        postCreate: function() {
            this.inherited(arguments);
            var self = this;
            this.createSubPackageListGrid();

            dojo.subscribe('SubPackagesTenderingReport-' + self.rootProject.id + '-stackContainer-selectChild', "", function(page) {
                var widget = dijit.byId('SubPackagesTenderingReport-' + self.rootProject.id + '-stackContainer');
                if(widget) {
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){

                        while(children.length > index) {
                            widget.removeChild(children[ index ]);
                            children[ index ].destroyRecursive(true);

                            index = index + 1;
                        }

                        var selectedIndex = page.grid.selection.selectedIndex;

                        page.grid.store.save();
                        page.grid.store.close();

                        var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                            handle.remove();
                            if(selectedIndex > -1){
                                this.scrollToRow(selectedIndex);
                                this.selection.setSelected(selectedIndex, true);
                            }
                        });

                        page.grid.sort();
                    }
                }
            });
        },
        createSubPackageListGrid: function(){
            var stackContainer = dijit.byId('SubPackagesTenderingReport-' + this.rootProject.id + '-stackContainer');
            if(stackContainer) {
                dijit.byId('SubPackages' + this.rootProject.id + '-stackContainer').destroyRecursive();
            }
            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style: 'padding:0px;border:0px;width:100%;height:100%;',
                region: "center",
                id: 'SubPackagesTenderingReport-' + this.rootProject.id + '-stackContainer'
            });
            var store = dojo.data.ItemFileWriteStore({
                    url: "subPackage/getSubPackageList/id/" + this.rootProject.id,
                    clearOnClose: true,
                    urlPreventCache:true
                }),
                formatter = new Formatter(),
                me = this;

            try{
                var grid = new SubPackagesGrid({
                    gridContainer: self,
                    id: 'sub_packages_list-page-container-'+me.rootProject.id,
                    stackContainerTitle: nls.subPackages,
                    pageId: 'sub_packages_grid_page-'+me.rootProject.id,
                    rootProject: me.rootProject,
                    type: 'sub_packages-list',
                    gridOpts: {
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.name, field: 'name', width:'auto', cellType:'buildspace.widget.grid.cells.Textarea' },
                            {name: nls.estAmount, field: 'est_amount', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                            {name: nls.selectedAmount, field: 'selected_amount', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.name[0] !== null){
                                me.createBillGrid(_item);
                            }
                        }
                    }
                });

                var controller = new dijit.layout.StackController({
                    region: "top",
                    containerId: 'SubPackagesTenderingReport-' + me.rootProject.id + '-stackContainer'
                });

                me.addChild(stackContainer);
                me.addChild(new dijit.layout.ContentPane({
                    style: "padding:0px;overflow:hidden;",
                    baseClass: 'breadCrumbTrail',
                    region: 'top',
                    id: 'SubPackagesTenderingReport-'+me.rootProject.id+'-controllerPane',
                    content: controller
                }));
            }catch(e){
                console.debug(e);
            }
        },
        createBillGrid: function(subPackage){
            this.selectedBillStore    = new Memory({ idProperty: 'id' });
            this.selectedElementStore = new Memory({ idProperty: 'id' });
            this.selectedItemStore    = new Memory({ idProperty: 'id' });
            this.elementStore         = [];
            this.elementItemStore     = [];

            var self = this,
                subContractorsXhr = dojo.xhrGet({
                    url: "subPackageReporting/getSubContractors/sid/"+subPackage.id,
                    handleAs: "json"
                }),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show();

            return when(subContractorsXhr, function(subContractors){
                pb.hide();

                var formatter = Formatter(),
                    store = new dojo.data.ItemFileWriteStore({
                        url:"subPackage/getBills/id/"+subPackage.id,
                        clearOnClose: true,
                        urlPreventCache:true
                    }),
                    descriptionWidth = subContractors.length > 5 ? '500px' : 'auto',
                    structure = [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.title, field: 'title', width:descriptionWidth },
                        {name: nls.estAmount, field: 'est_amount', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                    ];

                    dojo.forEach(subContractors, function(subContractor){
                        if(subContractor.id > 0){
                            var companyName = (subContractor.shortname != null && subContractor.shortname.length > 0) ? subContractor.shortname : buildspace.truncateString(subContractor.name, 20);
                            structure.push({
                                name: subContractor.selected ? '<p style="color:#0000FF!important;">'+companyName+'</p>': companyName,
                                field: 'total_amount-'+subContractor.id,
                                width: '120px',
                                styles:'text-align:right;',
                                formatter: formatter.unEditableCurrencyCellFormatter
                            });
                        }
                    });

                var grid = new SubPackagesGrid({
                    gridContainer: self,
                    id: 'sub_packages_bills-page-container-'+self.rootProject.id+'-'+subPackage.id,
                    stackContainerTitle: subPackage.name,
                    pageId: 'sub_packages-bills_grid_page-'+self.rootProject.id+'-'+subPackage.id,
                    rootProject: self.rootProject,
                    subPackage: subPackage,
                    type: 'sub_packages-bill_list',
                    gridOpts: {
                        subContractors: subContractors,
                        store: store,
                        structure: structure,
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.title[0] !== null){
                                self.createBillElementGrid(_item, subPackage, subContractors);
                            }
                        },
                        singleCheckBoxSelection: function(e) {
                            var self = this,
                                rowIndex = e.rowIndex,
                                checked = this.selection.selected[rowIndex],
                                item = this.getItem(rowIndex);

                            // used to store removeable selection
                            self.removedIds = [];

                            if ( checked ) {
                                self.gridContainer.selectedBillStore.put({ id: item.id[0] });

                                return self.getAffectedElementsAndItemsByBill(item, 'add');
                            } else {
                                self.gridContainer.selectedBillStore.remove(item.id[0]);

                                self.removedIds.push(item.id[0]);

                                return self.getAffectedElementsAndItemsByBill(item, 'remove');
                            }
                        },
                        toggleAllSelection: function(checked) {
                            var self = this, selection = this.selection, storeName;

                            // used to store removeable selection
                            self.removedIds = [];

                            if (checked) {
                                selection.selectRange(0, self.rowCount-1);
                                self.store.fetch({
                                    onComplete: function (items) {
                                        dojo.forEach(items, function (item, index) {
                                            if(item.id > 0) {
                                                self.gridContainer.selectedBillStore.put({ id: item.id[0] });
                                            }
                                        });
                                    }
                                });

                                return self.getAffectedElementsAndItemsByBill(null , 'add');
                            } else {
                                selection.deselectAll();

                                self.store.fetch({
                                    onComplete: function (items) {
                                        dojo.forEach(items, function (item, index) {
                                            if(item.id > 0) {
                                                self.gridContainer.selectedBillStore.remove(item.id[0]);

                                                self.removedIds.push(item.id[0]);
                                            }
                                        });
                                    }
                                });

                                return self.getAffectedElementsAndItemsByBill(null, 'remove');
                            }
                        },
                        getAffectedElementsAndItemsByBill: function(bill, type) {
                            var self = this,
                                bills = [];

                            var pb = buildspace.dialog.indeterminateProgressBar({
                                title: nls.pleaseWait+'...'
                            });

                            pb.show();

                            if (type === 'add') {
                                // if single bill, then only push affected bill only
                                if (bill) {
                                    bills.push(bill.id[0]);
                                } else {
                                    self.gridContainer.selectedBillStore.query().forEach(function(bill) {
                                        bills.push(bill.id);
                                    });
                                }
                            } else {
                                for (var itemKeyIndex in self.removedIds) {
                                    bills.push(self.removedIds[itemKeyIndex]);
                                }
                            }

                            request.post('subPackage/getSelectionAffectedElementsAndItems', {
                                handleAs: 'json',
                                data: {
                                    sid: subPackage.id,
                                    bill_ids: JSON.stringify(self.gridContainer.arrayUnique(bills))
                                }
                            }).then(function(data) {
                                // create default placeholder for storing item(s) associated with bill
                                for (var billId in data) {
                                    if ( ! self.gridContainer.elementStore[billId] ) {
                                        self.gridContainer.elementStore[billId] = new Memory({ idProperty: 'id' });
                                    }

                                    for (var elementId in data[billId]) {
                                        if ( ! self.gridContainer.elementItemStore[elementId] ) {
                                            self.gridContainer.elementItemStore[elementId] = new Memory({ idProperty: 'id' });
                                        }
                                    }
                                }

                                if ( type === 'add' ) {
                                    for (var billId in data) {
                                        self.gridContainer.selectedBillStore.put({ id: billId });

                                        for (var elementId in data[billId]) {
                                            self.gridContainer.elementStore[billId].put({ id: elementId });
                                            self.gridContainer.selectedElementStore.put({ id: elementId });

                                            for (var itemId in data[billId][elementId]) {
                                                self.gridContainer.elementItemStore[elementId].put({ id: itemId });
                                                self.gridContainer.selectedItemStore.put({ id: itemId });
                                            }
                                        }
                                    }
                                } else {
                                    for (var billId in data) {
                                        self.gridContainer.selectedBillStore.remove(billId);

                                        for (var elementId in data[billId]) {
                                            self.gridContainer.elementStore[billId].remove(elementId);
                                            self.gridContainer.selectedElementStore.remove(elementId);

                                            for (var itemId in data[billId][elementId]) {
                                                self.gridContainer.elementItemStore[elementId].remove(itemId);
                                                self.gridContainer.selectedItemStore.remove(itemId);
                                            }
                                        }
                                    }
                                }

                                pb.hide();
                            }, function(error) {
                                pb.hide();
                                console.log(error);
                            });
                        }
                    }
                });
            });
        },
        createBillElementGrid: function(bill, subPackage, subContractors){
            var self = this, formatter = Formatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"subPackage/getBillElements/id/"+bill.id+"/sid/"+subPackage.id,
                    clearOnClose: true,
                    urlPreventCache:true
                });

            var descriptionWidth = parseInt(subContractors.length) > 5 ? '500px' : 'auto';
            var structure = [{
                name: 'No',
                field: 'id',
                styles: "text-align:center;",
                width: '30px',
                formatter: formatter.rowCountCellFormatter,
                noresize: true
            },{
                name: nls.description,
                field: 'description',
                width: descriptionWidth,
                noresize: true
            },{
                name: nls.estAmount,
                field: 'est_amount_total',
                styles: "text-align:right;color:blue;",
                width: '120px',
                formatter: formatter.unEditableCurrencyCellFormatter,
                noresize: true
            }];

            dojo.forEach(subContractors, function(subContractor){
                if(subContractor.id > 0){
                    var companyName = (subContractor.shortname != null && subContractor.shortname.length > 0) ? subContractor.shortname : buildspace.truncateString(subContractor.name, 20);

                    structure.push({
                        name: subContractor.selected ? '<p style="color:#0000FF!important;">'+companyName+'</p>': companyName,
                        field: 'est_amount-bill_column-'+subContractor.id,
                        width: '150px',
                        styles:'text-align:right;',
                        noresize: true,
                        formatter: formatter.unEditableCurrencyCellFormatter
                    });
                }
            });

            new SubPackagesGrid({
                gridContainer: self,
                id: 'sub_packages_tendering_report_bill_elements-page-container-'+self.rootProject.id+'-'+subPackage.id+'-'+bill.id,
                stackContainerTitle: bill.title,
                pageId: 'sub_packages_tendering_report-bill_elements_grid_page-'+self.rootProject.id+'-'+subPackage.id+'-'+bill.id,
                rootProject: self.rootProject,
                subPackage: subPackage,
                type: 'sub_packages-bill_element_list',
                gridOpts: {
                    subContractors: subContractors,
                    bill: bill,
                    store: store,
                    structure: structure,
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createBillItemGrid(bill, _item, subPackage, subContractors);
                        }
                    },
                    singleCheckBoxSelection: function(e) {
                        var self = this,
                            rowIndex = e.rowIndex,
                            checked = this.selection.selected[rowIndex],
                            item = this.getItem(rowIndex);

                        // used to store removeable selection
                        self.removedIds = [];

                        if ( checked ) {
                            self.gridContainer.selectedElementStore.put({ id: item.id[0] });

                            return self.getAffectedBillAndItemsByElement(item, 'add');
                        } else {
                            self.gridContainer.selectedElementStore.remove(item.id[0]);

                            self.removedIds.push(item.id[0]);

                            return self.getAffectedBillAndItemsByElement(item, 'remove');
                        }
                    },
                    toggleAllSelection: function(checked) {
                        var self = this, selection = this.selection, storeName;

                        // used to store removeable selection
                        self.removedIds = [];

                        if (checked) {
                            selection.selectRange(0, self.rowCount-1);
                            self.store.fetch({
                                onComplete: function (items) {
                                    dojo.forEach(items, function (item, index) {
                                        if(item.id > 0) {
                                            self.gridContainer.selectedElementStore.put({ id: item.id[0] });
                                        }
                                    });
                                }
                            });

                            return self.getAffectedBillAndItemsByElement(null , 'add');
                        } else {
                            selection.deselectAll();

                            self.store.fetch({
                                onComplete: function (items) {
                                    dojo.forEach(items, function (item, index) {
                                        if(item.id > 0) {
                                            self.gridContainer.selectedElementStore.remove(item.id[0]);

                                            self.removedIds.push(item.id[0]);
                                        }
                                    });
                                }
                            });

                            return self.getAffectedBillAndItemsByElement(null, 'remove');
                        }
                    },
                    getAffectedBillAndItemsByElement: function(element, type) {
                        var self = this,
                            elements = [];

                        var pb = buildspace.dialog.indeterminateProgressBar({
                            title: nls.pleaseWait+'...'
                        });

                        pb.show();

                        if (type === 'add') {
                            // if single element, then only push affected element only
                            if (element) {
                                elements.push(element.id[0]);
                            } else {
                                self.gridContainer.selectedElementStore.query().forEach(function(element) {
                                    elements.push(element.id);
                                });
                            }
                        } else {
                            for (var itemKeyIndex in self.removedIds) {
                                elements.push(self.removedIds[itemKeyIndex]);
                            }
                        }

                        request.post('subPackage/getAffectedBillAndItemsByElements', {
                            handleAs: 'json',
                            data: {
                                sid: self.subPackage.id,
                                element_ids: JSON.stringify(self.gridContainer.arrayUnique(elements))
                            }
                        }).then(function(data) {
                            var billGrid = dijit.byId('sub_packages_bills-page-container-'+self.rootProject.id+'-'+self.subPackage.id);

                            // create default placeholder for storing item(s) associated with bill
                            for (var billId in data) {
                                if ( ! self.gridContainer.elementStore[billId] ) {
                                    self.gridContainer.elementStore[billId] = new Memory({ idProperty: 'id' });
                                }

                                for (var elementId in data[billId]) {
                                    if ( ! self.gridContainer.elementItemStore[elementId] ) {
                                        self.gridContainer.elementItemStore[elementId] = new Memory({ idProperty: 'id' });
                                    }
                                }
                            }

                            if ( type === 'add' ) {
                                for (var billId in data) {
                                    self.gridContainer.selectedBillStore.put({ id: billId });

                                    for (var elementId in data[billId]) {
                                        self.gridContainer.elementStore[billId].put({ id: elementId });
                                        self.gridContainer.selectedElementStore.put({ id: elementId });

                                        // checked bill selection if there is element(s) in the current bill
                                        billGrid.grid.store.fetchItemByIdentity({
                                            identity: billId,
                                            onItem: function(node) {
                                                if ( ! node ) {
                                                    return;
                                                }

                                                if ( self.gridContainer.elementStore[billId].data.length > 0 ) {
                                                    return billGrid.grid.rowSelectCell.toggleRow(node._0, true);
                                                }
                                            }
                                        });

                                        for (var itemId in data[billId][elementId]) {
                                            self.gridContainer.elementItemStore[elementId].put({ id: itemId });
                                            self.gridContainer.selectedItemStore.put({ id: itemId });
                                        }
                                    }
                                }
                            } else {
                                for (var billId in data) {
                                    for (var elementId in data[billId]) {
                                        self.gridContainer.elementStore[billId].remove(elementId);
                                        self.gridContainer.selectedElementStore.remove(elementId);

                                        for (var itemId in data[billId][elementId]) {
                                            self.gridContainer.elementItemStore[elementId].remove(itemId);
                                            self.gridContainer.selectedItemStore.remove(itemId);
                                        }

                                        // remove checked bill selection if there is no element(s) in the current bill
                                        billGrid.grid.store.fetchItemByIdentity({
                                            identity: billId,
                                            onItem: function(node) {
                                                if ( ! node ) {
                                                    return;
                                                }

                                                if ( self.gridContainer.elementStore[billId].data.length === 0 ) {
                                                    self.gridContainer.selectedBillStore.remove(billId);
                                                    return billGrid.grid.rowSelectCell.toggleRow(node._0, false);
                                                }
                                            }
                                        });
                                    }
                                }
                            }

                            pb.hide();
                        }, function(error) {
                            pb.hide();
                            console.log(error);
                        });
                    }
                }
            });
        },
        createBillItemGrid: function(bill, billElement, subPackage, subContractors){
            var self = this, formatter = Formatter(),
                descriptionWidth = parseInt(subContractors.length) > 4 ? '500px' : 'auto',
                store = new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url:"subPackage/getBillItems/sid/"+subPackage.id+'/eid/'+billElement.id
                }),
                structure = {
                    noscroll: false,
                    cells: [
                        [{
                            name: 'No',
                            field: 'id',
                            styles: "text-align:center;",
                            width: '30px',
                            formatter: formatter.rowCountCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.billReference,
                            field: 'bill_ref',
                            styles: "text-align:center; color: red;",
                            width: '80px',
                            noresize: true,
                            hidden: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },{
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
                            hidden: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },{
                            name: nls.unit,
                            field: 'uom_id',
                            width: '70px',
                            styles: 'text-align:center;',
                            formatter: formatter.unitIdCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        },{
                            name: nls.totalQty,
                            field: 'total_qty',
                            styles: "text-align:right;color:blue;",
                            width: '90px',
                            formatter: formatter.unEditableNumberCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
                        },{
                            name: nls.estRate,
                            field: 'rate-value',
                            styles: "text-align:right;color:blue;",
                            width: '100px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
                        },{
                            name: nls.estAmount,
                            field: 'total_est_amount',
                            styles: "text-align:right;color:blue;",
                            width: '100px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
                        }]
                    ]
                };

            var colCount = 0,
                parentCells = [],
                subConColumnChildren = [
                    {name: nls.rate, field_name: 'rate-value', width: '100px', cellType:'buildspace.widget.grid.cells.TextBox', styles: "text-align:right;", editable: true, formatter: formatter.companyRateCurrencyCellFormatter },
                    {name: nls.totalAmount, field_name: 'total_amount', width: '100px', styles: "text-align:right;", editable: false, formatter: formatter.unEditableCurrencyCellFormatter}
                ];

            dojo.forEach(subContractors, function(subContractor){
                if(subContractor.id > 0){
                    colCount++;

                    var companyName = (subContractor.shortname != null && subContractor.shortname.length > 0) ? subContractor.shortname : buildspace.truncateString(subContractor.name, 20);

                    parentCells.push({
                        name: subContractor.selected ? '<p style="color:#0000FF!important;">'+companyName+'</p>': companyName,
                        styles:'text-align:center;',
                        headerClasses: "staticHeader typeHeader"+colCount,
                        colSpan: subConColumnChildren.length,
                        headerId: subContractor.id,
                        hidden: false
                    });

                    for(i=0;i<subConColumnChildren.length;i++){
                        field = subConColumnChildren[i].field_name+'-'+subContractor.id;

                        var cellStructure = {
                            field: field,
                            columnType: "contractorColumn",
                            companyId: subContractor.id,
                            headerClasses: "typeHeader"+colCount
                        };

                        lang.mixin(cellStructure, subConColumnChildren[i]);

                        structure.cells[0].push(cellStructure);
                    }
                }
            });

            structure.cells.push(parentCells);

            new SubPackagesGrid({
                gridContainer: self,
                id: 'sub_packages_tendering_report_bill_items-page-container-'+self.rootProject.id+'-'+subPackage.id+'-'+billElement.id,
                stackContainerTitle: billElement.description,
                pageId: 'sub_packages_tendering_report-bill_items_grid_page-'+self.rootProject.id+'-'+subPackage.id+'-'+billElement.id,
                rootProject: self.rootProject,
                subPackage: subPackage,
                type: 'sub_packages-bill_item_list',
                gridOpts: {
                    subContractors: subContractors,
                    store: store,
                    escapeHTMLInData: false,
                    structure: structure,
                    bill: bill,
                    singleCheckBoxSelection: function(e) {
                        var self = this,
                            rowIndex = e.rowIndex,
                            checked = this.selection.selected[rowIndex],
                            item = this.getItem(rowIndex);

                        // used to store removeable selection
                        self.removedIds = [];

                        if ( checked ) {
                            self.gridContainer.selectedItemStore.put({ id: item.id[0] });

                            return self.getAffectedBillAndElementsByItem(item, 'add');
                        } else {
                            self.gridContainer.selectedItemStore.remove(item.id[0]);

                            self.removedIds.push(item.id[0]);

                            return self.getAffectedBillAndElementsByItem(item, 'remove');
                        }
                    },
                    toggleAllSelection: function(checked) {
                        var self = this, selection = this.selection, storeName;

                        // used to store removeable selection
                        self.removedIds = [];

                        if (checked) {
                            selection.selectRange(0, self.rowCount-1);
                            self.store.fetch({
                                onComplete: function (items) {
                                    dojo.forEach(items, function (item, index) {
                                        if(item.id > 0) {
                                            self.gridContainer.selectedItemStore.put({ id: item.id[0] });
                                        }
                                    });
                                }
                            });

                            return self.getAffectedBillAndElementsByItem(null , 'add');
                        } else {
                            selection.deselectAll();

                            self.store.fetch({
                                onComplete: function (items) {
                                    dojo.forEach(items, function (item, index) {
                                        if(item.id > 0) {
                                            self.gridContainer.selectedItemStore.remove(item.id[0]);

                                            self.removedIds.push(item.id[0]);
                                        }
                                    });
                                }
                            });

                            return self.getAffectedBillAndElementsByItem(null, 'remove');
                        }
                    },
                    getAffectedBillAndElementsByItem: function(item, type) {
                        var self = this,
                            items = [];

                        var pb = buildspace.dialog.indeterminateProgressBar({
                            title: nls.pleaseWait+'...'
                        });

                        pb.show();

                        if (type === 'add') {
                            // if single item, then only push affected item only
                            if (item) {
                                items.push(item.id[0]);
                            } else {
                                self.gridContainer.selectedItemStore.query().forEach(function(item) {
                                    items.push(item.id);
                                });
                            }
                        } else {
                            for (var itemKeyIndex in self.removedIds) {
                                items.push(self.removedIds[itemKeyIndex]);
                            }
                        }

                        request.post('subPackage/getAffectedBillAndElementsByItem', {
                            handleAs: 'json',
                            data: {
                                sid: self.subPackage.id,
                                bid: self.bill.id,
                                item_ids: JSON.stringify(self.gridContainer.arrayUnique(items))
                            }
                        }).then(function(data) {
                            var billGrid    = dijit.byId('sub_packages_bills-page-container-'+self.rootProject.id+'-'+self.subPackage.id);
                            var elementGrid = dijit.byId('sub_packages_tendering_report_bill_elements-page-container-'+self.rootProject.id+'-'+self.subPackage.id+'-'+self.bill.id);

                            // create default placeholder for storing item(s) associated with bill
                            for (var billId in data) {
                                if ( ! self.gridContainer.elementStore[billId] ) {
                                    self.gridContainer.elementStore[billId] = new Memory({ idProperty: 'id' });
                                }

                                for (var elementId in data[billId]) {
                                    if ( ! self.gridContainer.elementItemStore[elementId] ) {
                                        self.gridContainer.elementItemStore[elementId] = new Memory({ idProperty: 'id' });
                                    }
                                }
                            }

                            if ( type === 'add' ) {
                                for (var billId in data) {
                                    self.gridContainer.selectedBillStore.put({ id: billId });

                                    for (var elementId in data[billId]) {
                                        self.gridContainer.elementStore[billId].put({ id: elementId });
                                        self.gridContainer.selectedElementStore.put({ id: elementId });

                                        // checked bill selection if there is element(s) in the current bill
                                        billGrid.grid.store.fetchItemByIdentity({
                                            identity: billId,
                                            onItem: function(node) {
                                                if ( ! node ) {
                                                    return;
                                                }

                                                if ( self.gridContainer.elementStore[billId].data.length > 0 ) {
                                                    return billGrid.grid.rowSelectCell.toggleRow(node._0, true);
                                                }
                                            }
                                        });

                                        for (var itemId in data[billId][elementId]) {
                                            self.gridContainer.elementItemStore[elementId].put({ id: itemId });
                                            self.gridContainer.selectedItemStore.put({ id: itemId });

                                            // checked element selection if there is item(s) in the current element
                                            elementGrid.grid.store.fetchItemByIdentity({
                                                identity: elementId,
                                                onItem: function(node) {
                                                    if ( ! node ) {
                                                        return;
                                                    }

                                                    if ( self.gridContainer.elementItemStore[elementId].data.length > 0 ) {
                                                        return elementGrid.grid.rowSelectCell.toggleRow(node._0, true);
                                                    }
                                                }
                                            });
                                        }
                                    }
                                }
                            } else {
                                for (var billId in data) {
                                    for (var elementId in data[billId]) {
                                        for (var itemId in data[billId][elementId]) {
                                            self.gridContainer.elementItemStore[elementId].remove(itemId);
                                            self.gridContainer.selectedItemStore.remove(itemId);

                                            // unchecked element selection if there is no item(s) in the current element
                                            elementGrid.grid.store.fetchItemByIdentity({
                                                identity: elementId,
                                                onItem: function(node) {
                                                    if ( ! node ) {
                                                        return;
                                                    }

                                                    if ( self.gridContainer.elementItemStore[elementId].data.length === 0 ) {
                                                        self.gridContainer.elementStore[billId].remove(elementId);
                                                        self.gridContainer.selectedElementStore.remove(elementId);

                                                        return elementGrid.grid.rowSelectCell.toggleRow(node._0, false);
                                                    }
                                                }
                                            });
                                        }
                                    }

                                    // unchecked bill selection if there is no element(s) in the current bill
                                    billGrid.grid.store.fetchItemByIdentity({
                                        identity: billId,
                                        onItem: function(node) {
                                            if ( ! node ) {
                                                return;
                                            }

                                            if ( self.gridContainer.elementStore[billId].data.length === 0 ) {
                                                self.gridContainer.selectedBillStore.remove(billId);
                                                return billGrid.grid.rowSelectCell.toggleRow(node._0, false);
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
                    }
                }
            });
        },
        markedCheckBoxObject: function(grid, selectedRowStore) {
            var store = grid.store;

            selectedRowStore.query().forEach(function(item) {
                if (item.id == buildspace.constants.GRID_LAST_ROW) {
                    return;
                }

                store.fetchItemByIdentity({
                    identity: item.id,
                    onItem: function(node) {
                        if ( ! node ) {
                            return;
                        }

                        return grid.rowSelectCell.toggleRow(node._0, true);
                    }
                });
            });
        },
        arrayUnique: function(array) {
            return array.reverse().filter(function (e, i, arr) {
                return arr.indexOf(e, i+1) === -1;
            }).reverse();
        }
    });
});