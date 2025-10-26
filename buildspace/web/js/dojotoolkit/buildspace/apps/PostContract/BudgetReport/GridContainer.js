define('buildspace/apps/PostContract/BudgetReport/GridContainer',[
    'dojo/_base/declare',
    'dojo/currency',
    'dojo/number',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, currency, number, EnhancedGrid, GridFormatter, nls){

    var Grid = declare('buildspace.apps.PostContract/BudgetReport/Grid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        escapeHTMLInData: false,
        canSort: function() {
            return false;
        }
    } );

    var CustomFormatter = {
        treeCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                level = item.level*16,
                textColor = 'black';
            cellValue = cellValue == null ? '&nbsp': cellValue;

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                cellValue =  '<b>'+cellValue+'</b>';
            }

            if(!item.is_sub_project && item.id != buildspace.constants.GRID_LAST_ROW) cell.customClasses.push('hasAddendumTypeItemCell');

            if(item.is_tagged != undefined && !item.is_tagged[0]) cell.customClasses.push('hasCurrentAddendumTypeItemCell');

            cellValue = '<div class="treeNode" style="color:' + textColor + '; padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';

            return cellValue;
        },
        billTreeCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            var level = (item.level-1)*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;

            if((item.type == buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ROOT) && (!isNaN(item.id))){
                cellValue =  '<b>'+cellValue+'</b>';
            }

            if(item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }
            else
            {
                cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            }

            return cellValue;
        },
        subConCurrencyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);
            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = currency.format(value);

                cellValue = formattedValue;

                if(item.type == buildspace.constants.HIERARCHY_TYPE_WORK_ITEM)
                {
                    cellValue = '<strong><span style="color:green;">'+formattedValue+'</span></strong>';
                }
            }

            cell.customClasses.push('disable-cell');

            return cellValue;
        },
    };

    return declare('buildspace.apps.PostContract.BudgetReport.GridContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        grid: null,
        stackContainer: null,
        parentContainer: null,
        project: null,
        bill: null,
        variationOrder: null,
        postCreate: function(){
            this.inherited(arguments);

            this.formatter = new GridFormatter();

            var stackContainer = this.stackContainer = this.createStackContainer();

            var child = new dijit.layout.BorderContainer({
                title: this.title,
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                region: 'center'
            });

            child.addChild(this.createProjectBudgetGrid());

            stackContainer.addChild(child);
        },
        exportProjectReport: function(){
            form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", 'budgetReportExport/exportProjectReport/pid/'+this.project.id);
            form.setAttribute("target", "_self");

            document.body.appendChild(form);

            return form.submit();
        },
        exportBillReport: function(){
            form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", 'budgetReportExport/exportBillReport/pid/'+this.project.id+"/bill_id/"+this.bill.id);
            form.setAttribute("target", "_self");

            document.body.appendChild(form);

            return form.submit();
        },
        exportElementReport: function(){
            form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", 'budgetReportExport/exportElementReport/pid/'+this.project.id+"/bill_id/"+this.bill.id+"/element_id/"+this.element.id);
            form.setAttribute("target", "_self");

            document.body.appendChild(form);

            return form.submit();
        },
        exportVariationOrderReport: function(){
            form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", 'budgetReportExport/exportVariationOrderReport/pid/'+this.project.id);
            form.setAttribute("target", "_self");

            document.body.appendChild(form);

            return form.submit();
        },
        exportVariationOrderItemReport: function(){
            form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", 'budgetReportExport/exportVariationOrderItemReport/pid/'+this.project.id+"/vo_id/"+this.variationOrder.id);
            form.setAttribute("target", "_self");

            document.body.appendChild(form);

            return form.submit();
        },
        getProjectBudgetGridStructure: function(){
            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: this.formatter.rowCountCellFormatter
            },{
                name: nls.description,
                field: "description",
                width: 'auto',
                formatter: CustomFormatter.billTreeCellFormatter
            },{
                name: nls.untaggedItems,
                field: 'untagged_item_count',
                width:'90px',
                styles:'text-align: center;',
                formatter: this.formatter.unEditableIntegerCellFormatter
            },{
                name: nls.revenue,
                field: 'revenue',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.budget,
                field: 'budget',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.subConBudget,
                field: 'sub_con_budget',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.subConCost,
                field: 'sub_con_cost',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.progressRevenue,
                field: 'progress_revenue',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.progressCost,
                field: 'progress_cost',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            }];
        },
        getBillBudgetGridStructure: function(){
            return this.getProjectBudgetGridStructure();
        },
        getElementBudgetGridStructure: function(){
            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: this.formatter.rowCountCellFormatter
            },{
                name: nls.description,
                field: "description",
                width: '500px',
                formatter: CustomFormatter.treeCellFormatter,
            },{
                name: nls.unit,
                field: 'uom_symbol',
                width:'70px',
                styles:'text-align: center;',
                formatter: this.formatter.unitIdCellFormatter
            },{
                name: nls.contractQty,
                field: 'total_quantity',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.subConQty,
                field: 'sub_con_quantity',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.progressQty,
                field: 'up_to_date_qty',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.contractRate,
                field: 'rate',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.budgetRate,
                field: 'budget_rate',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.subConRate,
                field: 'sub_con_rate',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.revenue,
                field: 'revenue',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.budget,
                field: 'budget',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.subConBudget,
                field: 'sub_con_budget',
                width:'90px',
                styles:'text-align: right;',
                formatter: CustomFormatter.subConCurrencyCellFormatter
            },{
                name: nls.subConCost,
                field: 'sub_con_cost',
                width:'90px',
                styles:'text-align: right;',
                formatter: CustomFormatter.subConCurrencyCellFormatter
            },{
                name: nls.progressRevenue,
                field: 'progress_revenue',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.progressCost,
                field: 'progress_cost',
                width:'90px',
                styles:'text-align: right;',
                formatter: CustomFormatter.subConCurrencyCellFormatter
            }];
        },
        getVariationOrderGridStructure: function(){
            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: this.formatter.rowCountCellFormatter
            },{
                name: nls.description,
                field: "description",
                width: 'auto',
                formatter: CustomFormatter.billTreeCellFormatter
            },{
                name: nls.untaggedItems,
                field: 'untagged_item_count',
                width:'90px',
                styles:'text-align: center;',
                formatter: this.formatter.unEditableIntegerCellFormatter
            },{
                name: nls.revenue,
                field: 'revenue',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.subConCost,
                field: 'sub_con_cost',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.progressRevenue,
                field: 'progress_revenue',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.progressCost,
                field: 'progress_cost',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            }];
        },
        getVariationOrderItemGridStructure: function(){
            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: this.formatter.rowCountCellFormatter
            },{
                name: nls.description,
                field: "description",
                width: 'auto',
                formatter: CustomFormatter.treeCellFormatter,
            },{
                name: nls.unit,
                field: 'uom_symbol',
                width:'70px',
                styles:'text-align: center;',
                formatter: this.formatter.unitIdCellFormatter
            },{
                name: nls.revenue,
                field: 'revenue',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.subConCost,
                field: 'sub_con_cost',
                width:'90px',
                styles:'text-align: right;',
                formatter: CustomFormatter.subConCurrencyCellFormatter
            },{
                name: nls.progressRevenue,
                field: 'progress_revenue',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.progressCost,
                field: 'progress_cost',
                width:'90px',
                styles:'text-align: right;',
                formatter: CustomFormatter.subConCurrencyCellFormatter
            }];
        },
        createStackContainer: function(){
            var self = this;
            var stackContainerId = this.id + '-budgetReport-stackContainer';

            var stackContainer = self.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: stackContainerId
            });

            dojo.subscribe(stackContainerId+'-selectChild', "", function(page) {
                var widget = dijit.byId(stackContainerId);
                if(widget) {
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){
                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();

                            index = index + 1;
                        }
                    }
                }
            });

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: stackContainerId
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                id: self.id + '-budgetReport-controllerPane',
                content: controller
            });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            return stackContainer;
        },
        createProjectBudgetGrid: function(){
            var self = this;

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.export,
                    iconClass: "icon-16-container icon-16-print",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.exportProjectReport();
                    }
                })
            );

            var projectBudgetGrid = Grid({
                structure: self.getProjectBudgetGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'budgetReport/getProjectBudgetReport/pid/' + self.project.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.type == buildspace.apps.PostContract.ProjectStructureConstants.TYPE_BILL && item.id[0] > 0){
                        self.createBillBudgetGrid(item);
                    }
                    else if(item.type == buildspace.apps.PostContract.ProjectStructureConstants.TYPE_VARIATION_ORDER)
                    {
                        self.createVariationOrderGrid();
                    }
                }
            });

            var gridContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                region: 'center'
            });

            gridContainer.addChild(toolbar);
            gridContainer.addChild(projectBudgetGrid);

            return gridContainer;
        },
        createBillBudgetGrid: function(item){
            var self = this;
            this.bill = item;

            var billBudgetGridContainer = new dijit.layout.BorderContainer({
                title: buildspace.truncateString(item.description, 60),
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                item: item
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.export,
                    iconClass: "icon-16-container icon-16-print",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.exportBillReport();
                    }
                })
            );

            var billBudgetGrid = Grid({
                item: self.item,
                structure: self.getBillBudgetGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'budgetReport/getBillBudgetReport/pid/' + self.project.id + '/bill_id/' + item.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0){
                        self.createElementBudgetGrid(item);
                    }
                }
            });

            billBudgetGridContainer.addChild(toolbar);
            billBudgetGridContainer.addChild(billBudgetGrid);

            this.stackContainer.addChild(billBudgetGridContainer);
            this.stackContainer.selectChild(billBudgetGridContainer);
        },
        createElementBudgetGrid: function(item){
            var self = this;
            this.element = item;

            var elementBudgetGridContainer = new dijit.layout.BorderContainer({
                title: buildspace.truncateString(item.description, 60),
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                item: item
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.export,
                    iconClass: "icon-16-container icon-16-print",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.exportElementReport();
                    }
                })
            );

            var elementBudgetGrid = Grid({
                item: self.item,
                structure: self.getElementBudgetGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'budgetReport/getElementBudgetReport/pid/' + self.project.id + '/bill_id/' + this.bill.id + '/element_id/' + item.id
                })
            });

            elementBudgetGridContainer.addChild(toolbar);
            elementBudgetGridContainer.addChild(elementBudgetGrid);

            this.stackContainer.addChild(elementBudgetGridContainer);
            this.stackContainer.selectChild(elementBudgetGridContainer);
        },
        createVariationOrderGrid: function(){
            var self = this;

            var gridContainer = new dijit.layout.BorderContainer({
                title: nls.variationOrders,
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.export,
                    iconClass: "icon-16-container icon-16-print",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.exportVariationOrderReport();
                    }
                })
            );

            var grid = Grid({
                structure: self.getVariationOrderGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'budgetReport/getVariationOrderList/pid/' + self.project.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0){
                        self.createVariationOrderItemGrid(item);
                    }
                }
            });

            gridContainer.addChild(toolbar);
            gridContainer.addChild(grid);

            this.stackContainer.addChild(gridContainer);
            this.stackContainer.selectChild(gridContainer);
        },
        createVariationOrderItemGrid: function(item){
            var self = this;
            this.variationOrder = item;

            var gridContainer = new dijit.layout.BorderContainer({
                title: nls.variationOrders,
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.export,
                    iconClass: "icon-16-container icon-16-print",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.exportVariationOrderItemReport();
                    }
                })
            );

            var grid = Grid({
                structure: self.getVariationOrderItemGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'budgetReport/getVariationOrderItemList/pid/' + self.project.id + '/vo_id/' + item.id
                })
            });

            gridContainer.addChild(toolbar);
            gridContainer.addChild(grid);

            this.stackContainer.addChild(gridContainer);
            this.stackContainer.selectChild(gridContainer);
        },
        close: function(){
            this.parentContainer.removeChild(this);
            return this.destroyRecursive();
        }
    });
});