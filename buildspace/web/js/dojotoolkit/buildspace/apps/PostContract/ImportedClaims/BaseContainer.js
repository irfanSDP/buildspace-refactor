define('buildspace/apps/PostContract/ImportedClaims/BaseContainer',[
    'dojo/_base/declare',
    'buildspace/apps/Attachment/UploadAttachmentContainer',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, UploadAttachmentContainer, EnhancedGrid, GridFormatter, nls){

    var BreakdownGrid = declare('buildspace.apps.PostContract/ImportedClaims/BreakdownGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        escapeHTMLInData: false,
        canSort: function() {
            return false;
        },
        parentContainer: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            this.on('CellClick', function(e) {
                var colField = e.cell.field,
                    item = this.getItem(e.rowIndex);

                if (item.id > 0 && colField == 'attachment'){
                    self.parentContainer.addAttachmentContainer(item);
                }
            });
        }
    } );

    var ItemGrid = declare('buildspace.apps.PostContract/ImportedClaims/ItemGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        escapeHTMLInData: false,
        canSort: function() {
            return false;
        },
        parentContainer: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            this.on('CellClick', function(e) {
                var colField = e.cell.field,
                    item = this.getItem(e.rowIndex);

                if (item.id > 0 && colField == 'attachment'){
                    self.parentContainer.addAttachmentContainer(item);
                }
            });
        }
    } );

    return declare('buildspace.apps.PostContract.ImportedClaims.Base', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:40%;",
        gutters: false,
        splitter: true,
        grid: null,
        stackContainer: null,
        parentContainer: null,
        project: null,
        claimCertificate: null,
        postCreate: function(){
            this.inherited(arguments);

            this.formatter = new GridFormatter();
            this.hierarchyTypes = {
                options: [
                    buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT,
                    buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT
                ],
                values: [
                    buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER,
                    buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM
                ]
            };

            this.addChild(this.createToolbar());

            var stackContainer = this.stackContainer = this.createStackContainer();

            var child = new dijit.layout.BorderContainer({
                title: this.title,
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                region: 'center'
            });

            var self = this;

            child.addChild(self.createBreakdownGrid());

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
        getBreakdownGridStructure: function(){
            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: this.formatter.rowCountCellFormatter
            },{
                name: nls.description,
                field: "description",
                width: 'auto'
            },{
                name: nls.amount,
                field: 'total_amount',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.upToDateClaim,
                field: 'up_to_date_amount',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            }];
        },
        getItemGridStructure: function(){
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
                formatter: this.formatter.treeCellFormatter,
                noresize: true
            },{
                name: nls.type,
                field: 'type',
                width: '70px',
                styles: 'text-align:center;',
                editable: false,
                type: 'dojox.grid.cells.Select',
                options: this.hierarchyTypes.options,
                values: this.hierarchyTypes.values,
                formatter: this.formatter.typeCellFormatter
            },{
                name: nls.amount,
                field: 'total_amount',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.upToDateClaim,
                field: 'up_to_date_amount',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            }];
        },
        createStackContainer: function(){
            var self = this;
            var stackContainerId = this.id + '-importedBreakdown-stackContainer';

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
                id: self.id + '-importedBreakdown-controllerPane',
                content: controller
            });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            return stackContainer;
        },
        createBreakdownGrid: function(){
            var self = this;

            var url = this.getBreakdownGridUrl();

            var breakdownGrid = self.breakdownGrid = BreakdownGrid({
                structure: self.getBreakdownGridStructure(),
                parentContainer: self,
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: url
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);


                    if(item.id[0] > 0){
                        self.createItemGrid(item);
                    }
                }
            });

            return new dijit.layout.ContentPane( {
                style:"padding:0px;border:none;width:100%;height:100%;",
                region: 'center',
                content: breakdownGrid,
                grid: breakdownGrid
            });
        },
        createItemGrid: function(item){
            var self = this;

            var itemGridContainer = new dijit.layout.BorderContainer({
                title: buildspace.truncateString(item.description, 60),
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                item: item
            });

            var url = this.getItemGridUrl(item);

            var itemGrid = self.itemGrid = ItemGrid({
                item: self.item,
                structure: self.getItemGridStructure(),
                parentContainer: self,
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: url
                })
            });

            itemGridContainer.addChild(itemGrid);

            this.stackContainer.addChild(itemGridContainer);
            this.stackContainer.selectChild(itemGridContainer);
        },
        getBreakdownGridUrl: function(){},
        getItemGridUrl: function(item){},
        addAttachmentContainer: function(item){
            var id = 'project-'+this.project.id+'-importedAttachments';
            var container = dijit.byId(id);

            if(container){
                this.removeChild(container);
                container.destroy();
            }

            container = new UploadAttachmentContainer({
                id: id,
                region: 'bottom',
                item: item,
                disableEditing: true,
                style:"padding:0;margin:0;border:none;width:100%;height:40%;"
            });

            this.addChild(container);
        },
        close: function(){
            this.parentContainer.removeChild(this);
            return this.destroyRecursive();
        }
    });
});