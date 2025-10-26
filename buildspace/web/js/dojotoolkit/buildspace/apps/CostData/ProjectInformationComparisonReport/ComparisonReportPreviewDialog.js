define('buildspace/apps/CostData/ProjectInformationComparisonReport/ComparisonReportPreviewDialog',[
    'dojo/_base/declare',
    "dojo/dom-style",
    'dojo/number',
    'dojo/currency',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, domStyle, number, currency, EnhancedGrid, GridFormatter, nls){

    var Grid = declare('buildspace.apps.CostData.ProjectInformationComparisonReportPreview.Grid', dojox.grid.EnhancedGrid, {
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
            if(!((item.id[0] > 0) || (item.id[0] == buildspace.constants.GRID_LAST_ROW))) return null;
            return parseInt(rowIdx)+1;
        },
        itemFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.id[0] == buildspace.constants.GRID_LAST_ROW) return '';
            if(item.level[0] == 1) cellValue = '<b>'+cellValue+'</b>';
            var level = parseInt(String(item.level-1))*16;
            cellValue = '<div style="padding-left:'+level+'px">'+cellValue+'</div>';
            return cellValue;
        },
        descriptionFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(cellValue === undefined) return '';
            return cellValue;
        }
    };

    return declare('buildspace.apps.CostData.ProjectInformationComparisonReportPreview.Dialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.comparisonReport,
        gridUrl: null,
        costData: this.costData,
        selectedItemIds: [],
        projectInfoItem: null,
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
                    self.parent.export();
                }
            } ) );

            return toolbar;
        },
        createContent: function(){
            this.formatter = new GridFormatter();

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:1200px;height:450px;",
                gutters: false
            });

            borderContainer.addChild(this.createToolbar());

            borderContainer.addChild(this.createBreakdownGrid());

            return borderContainer;
        },
        getBreakdownGridStructure: function(){
            var structure = [{
                    name: 'No.',
                    field: 'count',
                    width:'30px',
                    styles:'text-align:center;',
                    formatter: Formatter.rowCountCellFormatter
                },{
                    name: nls.item,
                    field: 'item',
                    width:'500px',
                    formatter: this.projectInfoItem ? Formatter.descriptionFormatter : Formatter.itemFormatter
                }];

            var costDataIds = [this.costData.id].concat(this.selectedItemIds);

            for(var i in costDataIds){
                structure.push({
                    name: this.selectedCostDataInfo[costDataIds[i]]['name'],
                    field: 'description-'+costDataIds[i],
                    width: '500px',
                    formatter: Formatter.descriptionFormatter
                });
            }

            return structure;
        },
        createBreakdownGrid: function(){
            var url = "projectInformationComparisonReport/getBreakdown/cost_data_id/"+this.costData.id;

            if(this.projectInfoItem){
                url += "/parent_id/"+this.projectInfoItem.id;
            }

            if(this.selectedItemIds.length > 0){
                url += "?selected_ids="+this.selectedItemIds.join();
            }

            var gridContainer = new dijit.layout.BorderContainer({
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
            });

            var grid = Grid({
                structure: this.getBreakdownGridStructure(),
                store: dojo.data.ItemFileWriteStore({
                    url: url,
                    clearOnClose: true
                })
            });

            gridContainer.addChild(grid);

            return gridContainer;
        }
    });
});
