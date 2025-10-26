define('buildspace/apps/ProjectAnalyzer/ResourceContainer',[
    'dojo/_base/declare',
    "dojo/aspect",
    './ResourceGrid',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ProjectAnalyzer'],
    function(declare, aspect, ResourceGrid, GridFormatter, nls) {

    return declare('buildspace.apps.ProjectAnalyzer.ResourceContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
        gutters: false,
        project: null,
        type: null,
        postCreate: function() {
            this.inherited(arguments);
            var self = this,
                formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "projectAnalyzer/getResources/pid/"+self.project.id,
                    clearOnClose: true
                }),
                grid = new ResourceGrid({
                    id: 'resource_analysis_category-'+self.project.id,
                    stackContainerTitle: nls.resources,
                    pageId: 'resource_analysis_category-'+self.project.id,
                    project: self.project,
                    gridOpts: {
                        store: store,
                        escapeHTMLInData: false,
                        structure: [
                            {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.description, field: 'name', width:'auto', formatter: formatter.analyzerDescriptionCellFormatter },
                            {name: nls.totalCost, field: 'total_cost', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.name[0] !== null){
                                self.createTradeGrid(_item);
                            }
                        }
                    }
                });

            var gridContainer = this.makeGridContainer(grid, nls.resources);
            this.addChild(gridContainer);
        },
        createTradeGrid: function(resource){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                url:"projectAnalyzer/getResourceTrades/pid/"+self.project.id+"/id/"+resource.id,
                clearOnClose: true
            });

            var grid = ResourceGrid({
                id: 'resource_analysis_trade-'+self.project.id,
                stackContainerTitle: buildspace.truncateString(resource.name, 60),
                pageId: 'resource_analysis_trade-'+self.project.id+'_'+resource.id,
                project: self.project,
                gridOpts: {
                    store: store,
                    escapeHTMLInData: false,
                    structure: [
                        {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.analyzerDescriptionCellFormatter },
                        {name: nls.totalCost, field: 'total_cost', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if((_item.id == 'unsorted' ||_item.id > 0) && _item.description[0] !== null){
                            self.createItemGrid(_item, resource);
                        }
                    }
                }
            });
        },
        createItemGrid: function(trade, resource){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                url:"projectAnalyzer/getResourceItems/pid/"+self.project.id+"/rid/"+resource.id+"/id/"+trade.id,
                clearOnClose: true
            });

            var structure = [
                {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.analyzerRateCellFormatter},
                {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.analyzerWastageCellFormatter},
                {name: nls.totalQty, field: 'total_qty', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                {name: nls.totalCost, field: 'total_cost', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
            ];

            if(this.type == buildspace.constants.STATUS_POSTCONTRACT) {
                structure.push({name: nls.claimedQty, field: 'claim_quantity', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter});
                structure.push({name: nls.claimedAmount, field: 'claim_amount', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter});
            }

            var grid = ResourceGrid({
                id: 'resource_analysis_item-'+self.project.id,
                stackContainerTitle: buildspace.truncateString(trade.description, 60),
                pageId: 'resource_analysis_item-'+self.project.id+'_'+trade.id,
                project: self.project,
                gridOpts: {
                    store: store,
                    type: 'editable',
                    unsorted: (trade.id == 'unsorted'),
                    escapeHTMLInData: false,
                    updateUrl: 'projectAnalyzer/resourceItemUpdate',
                    structure: structure,
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if( _item.hasOwnProperty('multi-rate') && _item['multi-rate'][0] && e.cell.field == 'rate-value'){
                            return;
                        }else if( _item.hasOwnProperty('multi-wastage') && _item['multi-wastage'][0] && e.cell.field == 'wastage-value'){
                            return;
                        }else if(_item.id > 0 && _item.description[0] !== null && _item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER){
                            self.createBillGrid(_item, trade, resource);
                        }
                    }
                }
            });
        },
        createBillGrid: function(item, trade, resource){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                url:"projectAnalyzer/getBills/pid/"+self.project.id+"/rid/"+resource.id+"/tid/"+trade.id+"/id/"+item.id,
                clearOnClose: true
            });

            var structure = [
                {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                {name: nls.description, field: 'description', width:'auto', formatter: formatter.analysisTreeCellFormatter},
                {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                {name: nls.totalQty, field: 'total_qty', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter},
                {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', editable: true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                {name: nls.totalCost, field: 'total_cost', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter}
            ];

            if(this.type == buildspace.constants.STATUS_POSTCONTRACT) {
                structure.push({name: nls.claimedQty, field: 'claim_quantity', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter});
                structure.push({name: nls.claimedAmount, field: 'claim_amount', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter});
            }

            var grid = ResourceGrid({
                id: 'resource_analysis_bill-'+self.project.id,
                stackContainerTitle: buildspace.truncateString(item.description, 60),
                pageId: 'resource_analysis_bill-'+self.project.id+'_'+item.id,
                project: self.project,
                gridOpts: {
                    type: 'editable',
                    store: store,
                    unsorted: (trade.id == 'unsorted'),
                    escapeHTMLInData: false,
                    updateUrl: 'projectAnalyzer/billItemUpdate/rid/'+item.id,
                    structure: structure
                }
            });
        },
        makeGridContainer: function(content, title){
            var id = this.project.id;
            var stackContainer = dijit.byId('projectAnalyzer-resource_project_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('projectAnalyzer-resource_project_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'projectAnalyzer-resource_project_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'projectAnalyzer-resource_project_'+id+'-stackContainer'
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

            dojo.subscribe('projectAnalyzer-resource_project_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('projectAnalyzer-resource_project_'+id+'-stackContainer');
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