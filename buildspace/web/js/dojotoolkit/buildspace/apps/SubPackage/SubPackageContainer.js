define('buildspace/apps/SubPackage/SubPackageContainer',[
        'dojo/_base/declare',
        'dojo/_base/lang',
        "dojo/dom-style",
        "dojo/when",
        "dojo/currency",
        'dojo/aspect',
        "buildspace/widget/grid/cells/Formatter",
        './grid',
        'dojo/i18n!buildspace/nls/SubPackages'],
    function(declare, lang, domStyle, when, currency, aspect, Formatter, SubPackagesGrid, nls) {

    return declare('buildspace.apps.SubPackage.SubPackageContainer', dijit.layout.BorderContainer, {
        region: "center",
        rootProject: null,
        disableEditing: false,
        gutters: false,
        style: "padding:0px;border:0px;margin:0px;width:100%;height:100%;",
        postCreate: function() {
            this.inherited(arguments);
            var self = this;
            this.createSubPackageListGrid();

            dojo.subscribe('SubPackages-' + self.rootProject.id + '-stackContainer-selectChild', "", function(page) {
                var widget = dijit.byId('SubPackages-' + self.rootProject.id + '-stackContainer');
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
            var stackContainer = dijit.byId('SubPackages-' + this.rootProject.id + '-stackContainer');
            if(stackContainer) {
                dijit.byId('SubPackages' + this.rootProject.id + '-stackContainer').destroyRecursive();
            }
            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style: 'padding:0px;border:0px;width:100%;height:100%;',
                region: "center",
                id: 'SubPackages-' + this.rootProject.id + '-stackContainer'
            });
            var store = dojo.data.ItemFileWriteStore({
                    url: "subPackage/getSubPackageList/id/" + this.rootProject.id,
                    clearOnClose: true,
                    urlPreventCache:true
                }),
                formatter = new Formatter(),
                me = this;

            try{
                var customFormatter = {
                    filterCellFormatter: function (cellValue, rowIdx) {
                        var item = this.grid.getItem(rowIdx);

                        if (parseInt(String(item.id)) > 0) {
                            return '<span class="dijitReset dijitInline dijitIcon icon-16-container icon-16-zoom"></span>';
                        }

                        return null;
                    }
                };
                var grid = new SubPackagesGrid({
                    id: 'sub_packages_list-page-container-'+me.rootProject.id,
                    stackContainerTitle: nls.subPackages,
                    pageId: 'sub_packages_grid_page-'+me.rootProject.id,
                    disableEditing: me.disableEditing,
                    rootProject: me.rootProject,
                    type: 'sub_packages-list',
                    gridOpts: {
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.name, field: 'name', width:'auto', editable: (me.disableEditing) ? false : true, cellType:'buildspace.widget.grid.cells.Textarea' },
                            {name: '&nbsp;', field: 'filter', width:'32px', styles:'text-align:center;', formatter: customFormatter.filterCellFormatter},
                            {name: nls.estAmount, field: 'est_amount', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                            {name: nls.selectedAmount, field: 'selected_amount', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                        ],
                        addUrl:'subPackage/subPackageAdd',
                        updateUrl:'subPackage/subPackageUpdate',
                        deleteUrl:'subPackage/subPackageDelete',
                        pasteUrl:'subPackage/subPackagePaste',
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            var isFilterCol = (e.cell && e.cell.field == 'filter');
                            if(parseInt(String(_item.id)) > 0 && !isFilterCol){
                                me.createBillGrid(_item);
                            }
                        }
                    }
                });

                var controller = new dijit.layout.StackController({
                    region: "top",
                    containerId: 'SubPackages-' + me.rootProject.id + '-stackContainer'
                });

                me.addChild(stackContainer);
                me.addChild(new dijit.layout.ContentPane({
                    style: "padding:0px;overflow:hidden;",
                    baseClass: 'breadCrumbTrail',
                    region: 'top',
                    id: 'SubPackages-'+me.rootProject.id+'-controllerPane',
                    content: controller
                }));
            }catch(e){
                console.debug(e);
            }
        },
        createBillGrid: function(subPackage){
            var self = this,
                subContractorsXhr = dojo.xhrGet({
                    url: "subPackage/getSubContractors/id/"+subPackage.id,
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

                dojo.forEach(subContractors.items, function(subContractor){
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

                new SubPackagesGrid({
                    id: 'sub_packages_bills-page-container-'+self.rootProject.id+'-'+subPackage.id,
                    stackContainerTitle: subPackage.name,
                    pageId: 'sub_packages-bills_grid_page-'+self.rootProject.id+'-'+subPackage.id,
                    rootProject: self.rootProject,
                    subPackage: subPackage,
                    type: 'sub_packages-bill_list',
                    gridOpts: {
                        store: store,
                        structure: structure,
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.title[0] !== null){
                                self.createBillElementGrid(_item, subPackage, subContractors.items);
                            }
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
                id: 'sub_packages_bill_elements-page-container-'+self.rootProject.id+'-'+subPackage.id+'-'+bill.id,
                stackContainerTitle: bill.title,
                pageId: 'sub_packages-bill_elements_grid_page-'+self.rootProject.id+'-'+subPackage.id+'-'+bill.id,
                rootProject: self.rootProject,
                type: 'sub_packages-bill_element_list',
                gridOpts: {
                    store: store,
                    structure: structure,
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createBillItemGrid(_item, subPackage, subContractors);
                        }
                    }
                }
            });
        },
        createBillItemGrid: function(billElement, subPackage, subContractors){
            var self = this, formatter = Formatter(),
                descriptionWidth = parseInt(subContractors.length) > 5 ? '500px' : 'auto',
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
                id: 'sub_packages_bill_items-page-container-'+self.rootProject.id+'-'+subPackage.id+'-'+billElement.id,
                stackContainerTitle: billElement.description,
                pageId: 'sub_packages-bill_items_grid_page-'+self.rootProject.id+'-'+subPackage.id+'-'+billElement.id,
                rootProject: self.rootProject,
                type: 'sub_packages-bill_item_list',
                gridOpts: {
                    store: store,
                    escapeHTMLInData: false,
                    structure: structure,
                    updateUrl:'subPackage/billItemUpdate',
                    subPackage: subPackage
                }
            });
        }
    });
});