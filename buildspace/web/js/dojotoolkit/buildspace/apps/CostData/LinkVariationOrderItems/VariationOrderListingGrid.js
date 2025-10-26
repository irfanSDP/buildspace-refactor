define('buildspace/apps/CostData/LinkVariationOrderItems/VariationOrderListingGrid',[
    'dojo/_base/declare',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    './LinkVariationOrderItemsGrid',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, EnhancedGrid, GridFormatter, LinkVariationOrderItemsGrid, nls){

    var Grid = declare('buildspace.apps.LinkVariationOrderItems.VariationOrderListingGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        escapeHTMLInData: false,
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
            if(item.is_linked[0]) return '<span style="color:#4c94e6">' + cellValue + '</span>';
            return cellValue;
        }
    };

    return declare('buildspace.apps.CostData.LinkVariationOrderItems.BillListingContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
        masterItem: null,
        item: null,
        grid: null,
        moduleContainer: null,
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            self.addChild(self.createBreakdownGrid());
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
                structure: self.getGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"variationOrder/getVariationOrderList/costData/"+self.costData.id+"/masterItem/"+self.masterItem.id+'/pid/'+self.item.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0){
                        self.createLinkVariationOrderItemGrid(item);
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
        createLinkVariationOrderItemGrid: function(item){
            var linkVariationOrderItemsGrid = new LinkVariationOrderItemsGrid({
                title: buildspace.truncateString(item.description, 60),
                moduleContainer: this.moduleContainer,
                costData: this.costData,
                masterItem: this.masterItem,
                item: item
            });

            this.moduleContainer.stackContainer.addChild(linkVariationOrderItemsGrid);
            this.moduleContainer.stackContainer.selectChild(linkVariationOrderItemsGrid);
        }
    });
});