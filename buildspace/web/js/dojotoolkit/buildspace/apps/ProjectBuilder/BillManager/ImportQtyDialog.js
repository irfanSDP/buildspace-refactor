define('buildspace/apps/ProjectBuilder/BillManager/ImportQtyDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/aspect",
    'dojo/number',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/BillManagerImport',
    'buildspace/widget/grid/Filter'
], function(declare, lang, dom, keys, domStyle, aspect, number, GridFormatter, nls, FilterToolbar){

    var ImportItemGrid = declare('buildspace.apps.ProjectBuilder.BillManager.ImportQtyGrid', dojox.grid.EnhancedGrid, {
        type: null,
        billId: 0,
        billItem: null,
        qtyType: 'qty',
        targetBillColumnSettingId: 0,
        dialogWidget: null,
        billGrid: null,
        style: "border-top:none;",
        constructor: function(args){
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            if(this.type == 'tree'){
                this.on("RowClick", function(e){
                    var colField = e.cell.field,
                        rowIndex = e.rowIndex,
                        _item = this.getItem(rowIndex);
                    if((colField == 'quantity_import' || colField == 'quantity_remeasurement_import') && _item.id > 0 && _item.description[0] !== null && _item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && _item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && _item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                        self.importQty(_item, colField);
                    }
                }, true);
            }
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        importQty: function(selectedItem, qtyType){
            var self = this, pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });
            pb.show();
            dojo.xhrPost({
                url: 'billManagerImportQty/import',
                content: {tid: self.billItem.id, type: self.qtyType, tcid: self.targetBillColumnSettingId, sid: selectedItem.id, rid: selectedItem[qtyType], _csrf_token: selectedItem._csrf_token},
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        var store = self.billGrid.store;

                        store.save();
                        store.close();

                        var handle = aspect.after(self.billGrid, "_onFetchComplete", function() {
                            handle.remove();
                            this.scrollToRow(this.selection.selectedIndex);
                        });

                        self.billGrid.sort();
                    }
                    pb.hide();
                    if(self.dialogWidget){
                        self.dialogWidget.hide();
                    }
                },
                error: function(error) {
                    pb.hide();
                    if(self.dialogWidget){
                        self.dialogWidget.hide();
                    }
                }
            });
        }
    });

    var ImportGridContainer = declare('buildspace.apps.ProjectBuilder.BillManager.ImportQtyGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        billId: 0,
        billItem: null,
        billGrid: null,
        qtyType: 'qty',
        targetBillColumnSettingId: 0,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            lang.mixin(self.gridOpts, {
                type: self.type,
                qtyType: self.qtyType,
                billId: self.billId,
                billItem: self.billItem,
                billGrid: self.billGrid,
                targetBillColumnSettingId: self.targetBillColumnSettingId,
                region:"center"
            });
            var grid = this.grid = new ImportItemGrid(self.gridOpts);
            this.addChild(new FilterToolbar({
                grid:self.grid,
                region:"top",
                filterFields: this.filterFields
            }));

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('billManager-qty_import_'+this.billId+'_'+this.billItem.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 25), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                lang.mixin(child, {grid: grid});
                container.selectChild(self.pageId);
            }
        }
    });

    var CustomFormatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return cellValue > 0 ? cellValue : '';
        },
        treeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            var level = item.level*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;
            if(item.type < buildspace.constants.TYPE_BILL){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        },
        billTypeCellFormatter: function(cellValue, rowIdx){
            return buildspace.getBillTypeText(cellValue);
        },
        importQtyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                qty = number.parse(item['quantity_per_unit-final_value']);
            if(item.id > 0 && qty != 0 && !isNaN(qty) && qty != null && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                return '<a href="#" onclick="return false;">'+nls.import+'</a>';
            }else{
                cell.customClasses.push('disable-cell');
                return "&nbsp;";
            }
        },
        importRemeasurementQtyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                qty = number.parse(item['quantity_per_unit_remeasurement-final_value']);
            if(item.id > 0 && qty != 0 && !isNaN(qty) && qty != null && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                return '<a href="#" onclick="return false;">'+nls.import+'</a>';
            }else{
                cell.customClasses.push('disable-cell');
                return "&nbsp;";
            }
        }
    };

    return declare('buildspace.apps.ProjectBuilder.BillManager.ImportQtyDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importQtyFromProjects,
        billItem: null,
        billId: 0,
        billGrid: null,
        qtyType: 'qty',
        targetBillColumnSettingId: 0,
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;

            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function(e){
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createContent: function(){
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:950px;height:500px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: "billManagerImportQty/getProjects"
                }),
                content = new ImportGridContainer({
                    stackContainerTitle: nls.projects,
                    pageId: 'importQty-page_project-'+this.billId+'_'+this.billItem.id,
                    billId: this.billId,
                    billItem: this.billItem,
                    filterFields:[{title:'Title', state:'State', country:'Country'}],
                    gridOpts: {
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.title, field: 'title', width:'auto' },
                            {name: nls.country, field: 'country', width:'120px', styles:'text-align:center;'},
                            {name: nls.state, field: 'state', width:'120px', styles:'text-align:center;'}
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.title[0] !== null){
                                self.createBillGrid(_item);
                            }
                        }
                    }
                });
            var gridContainer = this.makeGridContainer(content,nls.projects);
            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        createBillGrid: function(project){
            var self = this;

            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose: true,
                url:"billManagerImportQty/getBillList/pid/"+project.id
            });

            new ImportGridContainer({
                stackContainerTitle: project.title,
                pageId: 'importQty-page_bill-'+this.billId+'_'+this.billItem.id,
                billId: self.billId,
                billItem: self.billItem,
                filterFields:[{'title':'Title'}],
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    structure: [
                        {name: 'No.', field: 'count', width:'40px', styles:'text-align:center;', formatter: CustomFormatter.rowCountCellFormatter },
                        {name: nls.title, field: 'title', width:'auto', formatter: CustomFormatter.treeCellFormatter},
                        {name: nls.billType, field: 'bill_type', width:'180px', styles:'text-align:center;', formatter: CustomFormatter.billTypeCellFormatter}
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.title[0] !== null){
                            self.createBillColumnSettingGrid(_item);
                        }
                    }
                }
            });
        },
        createBillColumnSettingGrid: function(bill){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose: true,
                url:"billManagerImportQty/getBillColumnSettingList/bid/"+bill.id
            });

            new ImportGridContainer({
                stackContainerTitle: bill.title,
                pageId: 'importQty-page_bill_column_setting-'+this.billId+'_'+this.billItem.id,
                billId: self.billId,
                billItem: self.billItem,
                filterFields:[{'name':'Name'}],
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    structure: [
                        {name: 'No.', field: 'count', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.name, field: 'name', width:'auto'}
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.name[0] !== null){
                            self.createElementGrid(_item);
                        }
                    }
                }
            });
        },
        createElementGrid: function(billColumnSetting){
            var self = this, formatter = GridFormatter();
            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose: true,
                url:"billManagerImportQty/getElementList/cid/"+billColumnSetting.id
            });

            new ImportGridContainer({
                stackContainerTitle: billColumnSetting.name,
                pageId: 'import-page_element-'+this.billId+'_'+this.billItem.id,
                billId: self.billId,
                billItem: self.billItem,
                filterFields:[{'description':'Description'}],
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    structure: [
                        {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto' }
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createItemGrid(_item, billColumnSetting);
                        }
                    }
                }
            });
        },
        createItemGrid: function(element, billColumnSetting){
            var structure, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"billManagerImportQty/getItemList/eid/"+element.id+"/cid/"+billColumnSetting.id
                });

            if(billColumnSetting.remeasurement_quantity_enabled[0]){
                structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                    {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                    {name: nls.qty+'/'+nls.unit, field: 'quantity_per_unit-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.import+" "+nls.qty, field: 'quantity_import', width:'80px', styles:'text-align:center;', formatter: CustomFormatter.importQtyCellFormatter },
                    {name: nls.qty+'/'+nls.unit+" (2)", field: 'quantity_per_unit_remeasurement-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.import+" "+nls.qty+" 2", field: 'quantity_remeasurement_import', width:'80px', styles:'text-align:center;', formatter: CustomFormatter.importRemeasurementQtyCellFormatter }
                ];
            }else{
                structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                    {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                    {name: nls.qty+'/'+nls.unit, field: 'quantity_per_unit-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.import+" "+nls.qty, field: 'quantity_import', width:'80px', styles:'text-align:center;', formatter: CustomFormatter.importQtyCellFormatter}
                ];
            }
            new ImportGridContainer({
                stackContainerTitle: element.description,
                pageId: 'import-page_item-'+this.billId+'_'+this.billItem.id,
                billId: this.billId,
                billItem: this.billItem,
                billGrid: this.billGrid,
                qtyType: this.qtyType,
                targetBillColumnSettingId: this.targetBillColumnSettingId,
                filterFields:[{'description':'Description'},{'uom_symbol':'Unit'}],
                type: 'tree',
                gridOpts: {
                    store: store,
                    escapeHTMLInData: false,
                    dialogWidget: this,
                    billGrid: this.billGrid,
                    structure: structure
                }
            });
        },
        makeGridContainer: function(content, title){
            var id = this.billId+'_'+this.billItem.id;
            var stackContainer = dijit.byId('billManager-qty_import_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('billManager-qty_import_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'billManager-qty_import_'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'billManager-qty_import_'+id+'-stackContainer'
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });
            borderContainer.addChild(stackContainer);
            borderContainer.addChild(new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            }));

            dojo.subscribe('billManager-qty_import_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('billManager-qty_import_'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    if(children.length > index + 1){
                        page.grid.store.save();
                        page.grid.store.close();

                        var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                            handle.remove();
                            this.scrollToRow(this.selection.selectedIndex);
                        });

                        page.grid._refresh();
                    }

                    while(children.length > index+1 ){
                        index = index + 1;
                        widget.removeChild(children[ index ]);
                        children[ index ].destroyRecursive(true);
                    }
                }
            });

            return borderContainer;
        }
    });
});