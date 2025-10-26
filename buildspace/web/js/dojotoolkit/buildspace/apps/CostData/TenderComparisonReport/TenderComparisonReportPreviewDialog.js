define('buildspace/apps/CostData/TenderComparisonReport/TenderComparisonReportPreviewDialog',[
    'dojo/_base/declare',
    "dojo/dom-style",
    'dojo/number',
    'dojo/currency',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, domStyle, number, currency, EnhancedGrid, GridFormatter, nls){

    var Grid = declare('buildspace.apps.CostData.TenderComparisonReportPreview.Grid', dojox.grid.EnhancedGrid, {
        region: 'center',
        style: "border-top:none;",
        canSort: function(inSortInfo){
            return false;
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
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
            }
            if(!((item.id[0] > 0) || (item.id[0] == buildspace.constants.GRID_LAST_ROW))) return null;
            return parseInt(rowIdx)+1;
        },
        descriptionFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.id[0] == 'row_separator') cell.customClasses.push('invalidTypeItemCell');
            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
                cell.customClasses.push('pull-right');
                cellValue = '<strong>' + cellValue + '</strong>';
            }
            return cellValue;
        },
        amountFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
            }

            if((item.id > 0 || item.id == 'provisional_sum' || item.type == 'summary') && cellValue != 0 && cellValue && !isNaN(cellValue)){
                cellValue = currency.format(cellValue);
            }
            else{
                cellValue = '&nbsp;';
            }

            return cellValue;
        }
    };

    return declare('buildspace.apps.CostData.TenderComparisonReportPreview.Dialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.tenderComparisonReport,
        exportUrl: null,
        gridUrl: null,
        project: null,
        selectedCostDataInfo: {},
        parent: null,
        parentItemId: null,
        level: null,
        tenderCompanies: [],
        exportUrl: null,
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
        onHide: function() {
            this.destroyRecursive();
        },
        createToolbar: function(){
            var self = this;

            var toolbar = new dijit.Toolbar( {
                region: "top",
                style: "outline:none!important;padding:2px;width:100%;"
            } );

            toolbar.addChild( new dijit.form.Button( {
                label: nls.close,
                iconClass: "icon-16-container icon-16-close",
                onClick: function() {
                    self.hide();
                }
            } ) );

            toolbar.addChild( new dijit.ToolbarSeparator() );

            toolbar.addChild( new dijit.form.Button( {
                label: nls.export,
                iconClass: "icon-16-container icon-16-print",
                onClick: function() {
                    self.export();
                }
            } ) );

            return toolbar;
        },
        createContent: function(){
            var self = this;
            this.formatter = new GridFormatter();

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:1200px;height:450px;",
                gutters: false
            });

            borderContainer.addChild(this.createToolbar());

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            var xhrArgs = {
                url: 'tenderComparison/getTenderCompanies/pid/'+this.project.id,
                handleAs: 'json',
                load: function(resp) {
                    self.tenderCompanies = resp.data;

                    switch(self.level)
                    {
                        case buildspace.apps.CostData.Levels.overallProjectCosting:
                            borderContainer.addChild(self.createOverallProjectCostingPreviewGrid());
                            break;
                        case buildspace.apps.CostData.Levels.workCategory:
                            borderContainer.addChild(self.createWorkCategoryPreviewGrid());
                            break;
                        default:
                            borderContainer.addChild(self.createElementPreviewGrid());
                            break;
                    }

                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            };

            pb.show();
            dojo.xhrPost(xhrArgs);

            return borderContainer;
        },
        getGridStructure: function(){
            var fixedHeaders = [{
                name: 'No.',
                field: 'count',
                width:'30px',
                styles:'text-align:center;',
                formatter: Formatter.rowCountCellFormatter
            },{
                name: nls.description,
                field: 'description',
                width:'500px',
                formatter: Formatter.descriptionFormatter
            }];

            var cells = [];

            for(var i in this.tenderCompanies){
                cells.push({
                    name: this.tenderCompanies[i]['name'],
                    field: this.tenderCompanies[i]['id']+'_amount',
                    width:'140px',
                    styles:'text-align:right;',
                    formatter: Formatter.amountFormatter
                });
            }

            return [{
                noscroll: true,
                cells: fixedHeaders
            },{
                noscroll: false,
                cells: cells
            }];
        },
        createOverallProjectCostingPreviewGrid: function(){
            var url = "tenderComparison/getBreakdown/cost_data_id/"+this.costData.id+"/pid/"+this.project.id;

            var gridContainer = new dijit.layout.BorderContainer({
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
            });

            var grid = Grid({
                structure: this.getGridStructure(),
                store: dojo.data.ItemFileWriteStore({
                    url: url,
                    clearOnClose: true
                })
            });

            gridContainer.addChild(grid);

            return gridContainer;
        },
        createWorkCategoryPreviewGrid: function(){
            var self = this;

            var gridContainer = new dijit.layout.BorderContainer({
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
            });

            var url = "tenderComparison/getWorkCategoryList/cost_data_id/"+self.costData.id+"/pid/"+this.project.id+"/parent_id/"+self.parentItemId;

            var grid = Grid({
                structure: self.getGridStructure(),
                store: dojo.data.ItemFileWriteStore({
                    url: url,
                    clearOnClose: true
                })
            });

            gridContainer.addChild(grid);

            return gridContainer;
        },
        createElementPreviewGrid: function(){
            var url = "tenderComparison/getElementList/cost_data_id/"+this.costData.id+"/pid/"+this.project.id+"/parent_id/"+this.parentItemId;

            var gridContainer = new dijit.layout.BorderContainer({
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
            });

            var grid = Grid({
                structure: this.getGridStructure(),
                store: dojo.data.ItemFileWriteStore({
                    url: url,
                    clearOnClose: true
                })
            });

            gridContainer.addChild(grid);

            return gridContainer;
        },
        export: function(){
            this.exportUrl += "/pid/"+this.project.id;

            form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", this.exportUrl);
            form.setAttribute("target", "_self");

            document.body.appendChild(form);

            return form.submit();
        }
    });
});
