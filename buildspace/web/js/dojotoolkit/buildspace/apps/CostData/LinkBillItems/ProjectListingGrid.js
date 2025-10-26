define('buildspace/apps/CostData/LinkBillItems/ProjectListingGrid',[
    'dojo/_base/declare',
    'dojox/grid/EnhancedGrid',
    './BillListingGrid',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, EnhancedGrid, BillListingGrid, nls){

    var Grid = declare('buildspace.apps.LinkBillItems.ProjectListingGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        escapeHTMLInData: false,
        costData: null,
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

    return declare('buildspace.apps.CostData.LinkBillItems.ProjectListingContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        splitter: true,
        costData: null,
        grid: null,
        linkedIds: [],
        selectedItemIds: [],
        deselectedItemIds: [],
        conversionFactor: null,
        updateUrl: null,
        item: null,
        stackContainer: null,
        parentContainer: null,
        onSave: null,
        postCreate: function(){
            this.inherited(arguments);

            this.addChild(this.createToolbar());

            var stackContainer = this.stackContainer = this.createStackContainer();

            var child = new dijit.layout.BorderContainer({
                title: this.title + ' - ' +nls.projects,
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                region: 'center'
            });

            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            if(self.linkedProjectsUrl){
                pb.show().then(function(){
                    dojo.xhrGet({
                        url: self.linkedProjectsUrl,
                        handleAs: "json",
                        load: function(data) {
                            if( data.success ) {
                                self.linkedIds = data.ids;
                                child.addChild(self.createBreakdownGrid());
                            }
                            pb.hide();
                        },
                        error: function(error) {
                        }
                    });
                });
            }
            else{
                child.addChild(self.createBreakdownGrid());
            }

            stackContainer.addChild(child);
        },
        createToolbar: function(){
            var self = this;
            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-container icon-16-close",
                    style: "float:right;",
                    onClick: dojo.hitch(self, 'close')
                })
            );

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
                name: nls.description,
                field: "description",
                width: 'auto',
                formatter: CustomFormatter.descriptionFormatter
            },{
                name: nls.status,
                field: 'status',
                width:'100px',
                styles:'text-align: center;',
            }];
        },
        createStackContainer: function(){
            var self = this;
            var stackContainerId = 'billItemsLinker' + self.item.id + '-stackContainer';

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
                id: 'costDataLinkBillItems'+self.costData.id+'-controllerPane',
                content: controller
            });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            return stackContainer;
        },
        createBreakdownGrid: function(){
            var self = this;

            var grid = self.grid = Grid({
                costData: self.costData,
                structure: self.getGridStructure(),
                linkedIds: self.linkedIds,
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"costData/getLinkBillItemProjectsList/costData/"+self.costData.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);


                    if(item.id[0] > 0){
                        self.createBillListingGrid(item);
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
        createBillListingGrid: function(item){
            var billListingGrid = new BillListingGrid({
                title: buildspace.truncateString(item.description, 60),
                moduleContainer: this,
                costData: this.costData,
                item: item
            });

            this.stackContainer.addChild(billListingGrid);
            this.stackContainer.selectChild(billListingGrid);
        },
        close: function(){
            this.parentContainer.removeChild(this);
            return this.destroyRecursive();
        }
    });
});