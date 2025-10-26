define('buildspace/apps/CostData/TenderComparisonReport/TenderComparisonReportDialog',[
    'dojo/_base/declare',
    "dojo/dom-style",
    'dojox/grid/EnhancedGrid',
    "./TenderComparisonReportPreviewDialog",
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, domStyle, EnhancedGrid, PreviewDialog, nls){

    var Grid = declare('buildspace.apps.CostData.TenderComparisonReport.Grid', dojox.grid.EnhancedGrid, {
        region: 'center',
        style: "border-top:none;",
        canSort: function(inSortInfo){
            return false;
        }
    });

    var CustomFormatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return parseInt(rowIdx)+1;
        }
    };

    return declare('buildspace.apps.CostData.TenderComparisonReport.Dialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.tenderComparisonReport,
        exportUrl: null,
        costData: null,
        level: null,
        parentItemId: null,
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

            return toolbar;
        },
        getGridStructure: function(){
            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: CustomFormatter.rowCountCellFormatter
            },{
                name: nls.project,
                field: "title",
                width: 'auto'
            }];
        },
        createContent: function(){
            var self = this;

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:800px;height:350px;",
                gutters: false
            });

            var grid = this.grid = Grid({
                structure: this.getGridStructure(),
                store: dojo.data.ItemFileWriteStore({
                    url: "tenderComparison/getProjectList/costData/"+self.costData.id,
                    clearOnClose: true
                }),
                onRowDblClick: function(e) {
                    var item = this.getItem(e.rowIndex);

                    if(item.id[0] > 0) self.createPreviewDialog(item);
                }
            });
            borderContainer.addChild(this.createToolbar());
            borderContainer.addChild(grid);

            return borderContainer;
        },
        createPreviewDialog: function(item){
            var previewDialog = new PreviewDialog({
                costData: this.costData,
                project: item,
                parent: this,
                parentItemId: this.parentItemId,
                level: this.level,
                exportUrl: this.exportUrl
            });

            previewDialog.show();
        }
    });
});
