define('buildspace/apps/CostData/PrimeCostRateLinkBillItems/ElementListingGrid',[
    'dojo/_base/declare',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    './LinkBillItemsGrid',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, EnhancedGrid, GridFormatter, LinkBillItemsGrid, nls){

    var Grid = declare('buildspace.apps.PrimeCostRateLinkBillItems.ElementListingGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        escapeHTMLInData: false,
        costData: null,
        item: null,
        linkedIds: [],
        canSort: function() {
            return false;
        }
    } );

    var CustomFormatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return parseInt(rowIdx)+1;
        },
        descriptionFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            if(this.grid.linkedIds.includes(item.id[0])) return '<span style="color:#4c94e6">' + cellValue + '</span>';
            return cellValue;
        }
    };

    return declare('buildspace.apps.CostData.PrimeCostRateLinkBillItems.ElementListingContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
        item: null,
        grid: null,
        moduleContainer: null,
        linkedIds: [],
        postCreate: function(){
            this.inherited(arguments);

            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            if(self.moduleContainer.linkedElementsUrl){
                pb.show().then(function(){
                    dojo.xhrGet({
                        url: self.moduleContainer.linkedElementsUrl,
                        handleAs: "json",
                        load: function(data) {
                            if( data.success ) {
                                self.linkedIds = data.ids;
                                self.addChild(self.createBreakdownGrid());
                            }
                            pb.hide();
                        },
                        error: function(error) {
                        }
                    });
                });
            }
            else{
                self.addChild(self.createBreakdownGrid());
            }
        },
        getGridStructure: function(){
            this.gridFormatter = new GridFormatter;

            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: CustomFormatter.rowCountCellFormatter
            },{
                name: nls.description,
                field: "description",
                width: 'auto',
                formatter: CustomFormatter.descriptionFormatter
            }];
        },
        createBreakdownGrid: function(){
            var self = this;

            var grid = self.grid = Grid({
                costData: self.costData,
                item: self.item,
                linkedIds: self.linkedIds,
                structure: self.getGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"costData/getLinkBillItemElementList/costData/"+self.costData.id+'/id/'+self.item.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0){
                        self.createLinkBillItemGrid(item);
                    }
                }
            });

            return new dijit.layout.ContentPane( {
                style:"padding:0px;border:none;width:100%;height:100%;",
                region: 'center',
                content: grid,
                grid: grid
            });
        },
        createLinkBillItemGrid: function(item){
            var linkBillItemsGrid = new LinkBillItemsGrid({
                title: buildspace.truncateString(item.description, 60),
                moduleContainer: this.moduleContainer,
                costData: this.costData,
                item: item,
                style:"padding:0px;margin:0px;width:100%;height:40%;",
                gutters: true
            });

            this.moduleContainer.stackContainer.addChild(linkBillItemsGrid);
            this.moduleContainer.stackContainer.selectChild(linkBillItemsGrid);
        }
    });
});