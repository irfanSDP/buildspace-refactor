define('buildspace/apps/ProjectAnalyzer/ScheduleOfRateContainer',[
    'dojo/_base/declare',
    "dojo/aspect",
    "dojo/when",
    "dojo/currency",
    './ScheduleOfRateGrid',
    './buildUpGrid',
    './buildUpRateSummary',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ProjectAnalyzer'],
    function(declare, aspect, when, currency, ScheduleOfRateGrid, BuildUpGrid, BuildUpRateSummary, GridFormatter, nls) {

    return declare('buildspace.apps.ProjectAnalyzer.ScheduleOfRateContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
        gutters: false,
        project: null,
        contractorList: null,
        postCreate: function() {
            this.inherited(arguments);

            var self = this,
                formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "projectAnalyzer/getScheduleOfRates/pid/"+self.project.id,
                    clearOnClose: true
                }),
                grid = new ScheduleOfRateGrid({
                    id: 'sor_analysis_sor-'+self.project.id,
                    stackContainerTitle: nls.scheduleOfRates,
                    pageId: 'sor_analysis_sor-'+self.project.id,
                    project: self.project,
                    gridOpts: {
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.name, field: 'name', width:'auto', formatter: formatter.analyzerDescriptionCellFormatter },
                            {name: nls.totalCost, field: 'total_cost', width:'150px', styles:'text-align:right;', formatter: formatter.currencyCellFormatter}
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if((_item.id != 'unsorted' && _item.id > 0) && _item.name[0] !== null){
                                self.createTradeGrid(_item);
                            }else if(_item.id == 'unsorted'){
                                self.createBillElementGrid(_item);
                            }
                        }
                    }
                });

            var gridContainer = this.makeGridContainer(grid, nls.scheduleOfRates);
            this.addChild(gridContainer);
        },
        createTradeGrid: function(scheduleOfRate){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"projectAnalyzer/getScheduleOfRateTrades/pid/"+self.project.id+"/id/"+scheduleOfRate.id,
                    clearOnClose: true
                });

            var grid = ScheduleOfRateGrid({
                id: 'sor_analysis_trade-'+self.project.id,
                stackContainerTitle: buildspace.truncateString(scheduleOfRate.name, 60),
                pageId: 'sor_analysis_trade-'+self.project.id+'_'+scheduleOfRate.id,
                project: self.project,
                gridOpts: {
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.analyzerDescriptionCellFormatter },
                        {name: nls.totalCost, field: 'total_cost', width:'150px', styles:'text-align:right;', formatter: formatter.currencyCellFormatter}
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if((_item.id == 'unsorted' ||_item.id > 0) && _item.description[0] !== null){
                            self.createItemGrid(_item, scheduleOfRate);
                        }
                    }
                }
            });
        },
        createItemGrid: function(trade, scheduleOfRate){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"projectAnalyzer/getScheduleOfRateItems/pid/"+self.project.id+"/sorid/"+scheduleOfRate.id+"/id/"+trade.id,
                    clearOnClose: true
                });

            var grid = ScheduleOfRateGrid({
                id: 'sor_analysis_item-'+self.project.id,
                stackContainerTitle: buildspace.truncateString(trade.description, 60),
                pageId: 'sor_analysis_item-'+self.project.id+'_'+trade.id,
                project: self.project,
                gridOpts: {
                    store: store,
                    type: 'editable',
                    unsorted: trade.id == 'unsorted' ? true : false,
                    updateUrl: 'projectAnalyzer/scheduleOfRateItemUpdate',
                    isScheduleOfRateItemGrid: true,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter},
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter},
                        {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                        {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', editable:true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.analyzerRateCellFormatter},
                        {name: nls.itemMarkup+" (%)", field: 'item_markup-value', width:'100px', styles:'text-align:right;', editable:true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.analyzerItemMarkupCellFormatter},
                        {name: nls.totalQty, field: 'total_qty', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                        {name: nls.totalCost, field: 'total_cost', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                    ],
                    onRowDblClick: function(e){
                        var cell = e.cell,
                            idx = e.rowIndex,
                            _item = this.getItem(idx);
                        if(_item.id > 0 && cell.field != 'item_markup-value'){
                            self.createBillItemGrid(_item, trade, scheduleOfRate);
                        }
                    }
                }
            });
        },
        createBillElementGrid: function(){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                url:"projectAnalyzer/getSorBillElements/pid/"+self.project.id,
                clearOnClose: true
            });

            var grid = ScheduleOfRateGrid({
                id: 'sor_analysis_bill_element-'+self.project.id+'_unsorted_container',
                stackContainerTitle: 'UNSORTED',
                pageId: 'sor_analysis_bill_element-'+self.project.id+'_unsorted',
                project: self.project,
                gridOpts: {
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.analysisTreeCellFormatter},
                        {name: nls.totalCost, field: 'total_cost', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] != null && _item.type[0] > 0){
                            self.createUnsortedBillItemGrid(_item);
                        }
                    }
                }
            });
        },
        createUnsortedBillItemGrid: function(billElement){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"projectAnalyzer/getUnsortedSorBillItems/eid/"+billElement.id,
                    clearOnClose: true
                });
            var structure;

            if(this.contractorList && this.contractorList.items.length > 0){
                var descWidth = this.contractorList.items.length > 1 ? '500px' : 'auto';
                structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width: descWidth, formatter: formatter.analysisTreeCellFormatter},
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                    {name: nls.totalQty, field: 'grand_total_quantity', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                    {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', editable: true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.total, field: 'grand_total', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                    {name: nls.itemMarkup+" (%)", field: 'markup_percentage-value', width:'100px', styles:'text-align:right;', editable:true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaPercentageCellFormatter}
                ];

                dojo.forEach(this.contractorList.items, function(contractor){
                    var subConName = contractor.awarded ? '<p style="color:#0000FF!important;">'+buildspace.truncateString(contractor.name, 45)+'</p>': buildspace.truncateString(contractor.name, 45);
                    structure.push({
                        name: subConName,
                        field: 'contractor_rate-'+contractor.id+'-value',
                        width:'120px',
                        styles:'text-align:right;',
                        editable: true,
                        cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: formatter.formulaCurrencyCellFormatter
                    });
                });
            }else{
                structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.analysisTreeCellFormatter},
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                    {name: nls.totalQty, field: 'grand_total_quantity', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                    {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', editable: true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.total, field: 'grand_total', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                    {name: nls.itemMarkup+" (%)", field: 'markup_percentage-value', width:'100px', styles:'text-align:right;', editable:true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaPercentageCellFormatter}
                ];
            }

            var grid = ScheduleOfRateGrid({
                stackContainerTitle: buildspace.truncateString(billElement.description, 60),
                pageId: 'sor_analysis_bill_item-'+self.project.id+'_'+billElement.id,
                project: self.project,
                gridOpts: {
                    type: 'editable',
                    escapeHTMLInData: false,
                    store: store,
                    updateUrl: 'projectAnalyzer/scheduleOfRateBillItemUpdate',
                    structure: structure,
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item['rate-has_build_up'][0] && e.cell.field == 'rate-value'){
                            self.createBuildUpContainer(_item, {id: -1});
                        }
                    }
                }
            });
        },
        createBillItemGrid: function(item, trade, scheduleOfRate){
            var self = this, structure, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:"projectAnalyzer/getSorBillItems/pid/"+self.project.id+"/sid/"+scheduleOfRate.id+"/tid/"+trade.id+"/id/"+item.id,
                    clearOnClose: true
                });

            if(this.contractorList && this.contractorList.items.length > 0){
                var descWidth = this.contractorList.items.length > 1 ? '500px' : 'auto';
                structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width: descWidth, formatter: formatter.analysisTreeCellFormatter},
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                    {name: nls.totalQty, field: 'grand_total_quantity', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                    {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', editable: true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.total, field: 'grand_total', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                    {name: nls.itemMarkup+" (%)", field: 'markup_percentage-value', width:'100px', styles:'text-align:right;', editable:true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaPercentageCellFormatter}
                ];

                dojo.forEach(this.contractorList.items, function(contractor){
                    var subConName = contractor.awarded ? '<p style="color:#0000FF!important;">'+buildspace.truncateString(contractor.name, 45)+'</p>': buildspace.truncateString(contractor.name, 45);
                    structure.push({
                        name: subConName,
                        field: 'contractor_rate-'+contractor.id+'-value',
                        width:'120px',
                        styles:'text-align:right;',
                        editable: true,
                        cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: formatter.formulaCurrencyCellFormatter
                    });
                });
            }else{
                structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.analysisTreeCellFormatter},
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                    {name: nls.totalQty, field: 'grand_total_quantity', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                    {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', editable: true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.total, field: 'grand_total', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                    {name: nls.itemMarkup+" (%)", field: 'markup_percentage-value', width:'100px', styles:'text-align:right;', editable:true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaPercentageCellFormatter}
                ];
            }

            var grid = ScheduleOfRateGrid({
                id: 'sor_analysis_bill_item-'+self.project.id,
                stackContainerTitle: buildspace.truncateString(item.description, 60),
                pageId: 'sor_analysis_bill_item-'+self.project.id+'_'+scheduleOfRate.id,
                project: self.project,
                gridOpts: {
                    type: 'editable',
                    escapeHTMLInData: false,
                    store: store,
                    updateUrl: 'projectAnalyzer/scheduleOfRateBillItemUpdate',
                    structure: structure,
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item['rate-has_build_up'][0] && e.cell.field == 'rate-value'){
                            self.createBuildUpContainer(_item, scheduleOfRate);
                        }
                    }
                }
            });
        },
        createBuildUpContainer: function(item, scheduleOfRate){
            var self = this,
                currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation,
                baseContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:100%;height:100%;border:0px;outline:none;",
                    gutters: false
                }),
                aContainer = new dijit.layout.AccordionContainer({
                    region: "center",
                    style:"padding:0px;width:100%;height:100%;border:0px;outline:none;"
                }),
                resourceQuery = dojo.xhrGet({
                    url: "projectAnalyzer/buildUpRateResourceList/id/"+item.id,
                    handleAs: "json"
                }),
                formatter = new GridFormatter(),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show();

            when(resourceQuery, function(resources){
                var buildUpSummaryWidget = new BuildUpRateSummary({
                    itemId: item.id,
                    container: baseContainer,
                    _csrf_token: item._csrf_token
                });
                dojo.forEach(resources, function(resource){
                    var store = new dojo.data.ItemFileWriteStore({
                        url:"projectAnalyzer/getBuildUpRateItemList/id/"+item.id+"/resource_id/"+resource.id,
                        clearOnClose: true
                    });
                    try{
                        var grid = new BuildUpGrid({
                            resource: resource,
                            billItem: item,
                            updateUrl:'projectAnalyzer/buildUpRateItemUpdate',
                            store: store,
                            buildUpSummaryWidget: buildUpSummaryWidget,
                            structure: [
                                {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                {name: nls.description, field: 'description', width:'auto', formatter: formatter.linkedCellFormatter },
                                {name: nls.number, field: 'number-value', width:'100px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                {name: nls.constant, field: 'constant-value', width:'100px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                {name: nls.qty, field: 'quantity-value', width:'70px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaNumberCellFormatter},
                                {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                                {name: nls.rate, field: 'rate-value', width:'110px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                {name: nls.total, field: 'total', width:'110px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                                {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                {name: nls.lineTotal, field: 'line_total', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                            ]
                        });
                        aContainer.addChild(new dijit.layout.ContentPane({
                            title: resource.name+'<span style="color:blue;float:right;">'+currencySetting+'&nbsp;'+currency.format(resource.total_build_up)+'</span>',
                            style: "padding:0px;border:0px;",
                            doLayout: false,
                            id: 'accPane-'+resource.id+'-'+item.id,
                            content: grid
                        }));
                    }catch(e){console.log(e)}
                });

                baseContainer.addChild(aContainer);
                baseContainer.addChild(buildUpSummaryWidget);
                var container = dijit.byId('projectAnalyzer-sor_project_'+self.project.id+'-stackContainer');
                if(container){
                    var node = document.createElement("div");
                    var child = new dojox.layout.ContentPane({
                        title: buildspace.truncateString(item.description, 60),
                        style: "padding:0px;border:0px;",
                        id: 'sor_analysis_build_up-'+self.project.id+'_'+scheduleOfRate.id,
                        executeScripts: true },
                        node );
                    container.addChild(child);
                    child.set('content', baseContainer);
                    container.selectChild('sor_analysis_build_up-'+self.project.id+'_'+scheduleOfRate.id);
                }
                pb.hide();
            });
        },
        makeGridContainer: function(content, title){
            var id = this.project.id;
            var stackContainer = dijit.byId('projectAnalyzer-sor_project_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('projectAnalyzer-sor_project_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'projectAnalyzer-sor_project_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'projectAnalyzer-sor_project_'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('projectAnalyzer-sor_project_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('projectAnalyzer-sor_project_'+id+'-stackContainer');
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