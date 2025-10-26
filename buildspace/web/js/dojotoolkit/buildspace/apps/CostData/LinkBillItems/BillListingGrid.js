define('buildspace/apps/CostData/LinkBillItems/BillListingGrid',[
    'dojo/_base/declare',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    './ElementListingGrid',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, EnhancedGrid, GridFormatter, ElementListingGrid, nls){

    var Grid = declare('buildspace.apps.LinkBillItems.BillListingGrid', EnhancedGrid, {
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

    return declare('buildspace.apps.CostData.LinkBillItems.BillListingContainer', dijit.layout.BorderContainer, {
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
            var self = this;
            this.inherited(arguments);

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            if(self.moduleContainer.linkedBillsUrl){
                pb.show().then(function(){
                    dojo.xhrGet({
                        url: self.moduleContainer.linkedBillsUrl,
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
                structure: self.getGridStructure(),
                linkedIds: self.linkedIds,
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"costData/getLinkBillItemBillList/costData/"+self.costData.id+'/id/'+self.item.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0){
                        self.createElementListingGrid(item);
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
        createElementListingGrid: function(item){
            var elementListingGrid = new ElementListingGrid({
                title: buildspace.truncateString(item.description, 60),
                moduleContainer: this.moduleContainer,
                costData: this.costData,
                item: item
            });

            this.moduleContainer.stackContainer.addChild(elementListingGrid);
            this.moduleContainer.stackContainer.selectChild(elementListingGrid);
        }
    });
});