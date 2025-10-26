define('buildspace/apps/PostContract/SubProjectItemLink/GridContainer',[
    'dojo/_base/declare',
    'dojo/aspect',
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, aspect, EnhancedGrid, IndirectSelection, GridFormatter, nls){

    var Grid = declare('buildspace.apps.PostContract/SubProjectItemLink/Grid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        escapeHTMLInData: false,
        canSort: function() {
            return false;
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    } );

    var TaggedItemGridContainer = declare('buildspace.apps.PostContract/SubProjectItemLink/TaggedItemGridContainer', dijit.layout.BorderContainer, {
        message: null,
        style: "background-color:white;border:none;padding:0px;overflow:hidden;",
        itemId: null,
        postCreate: function(){
            this.formatter = new GridFormatter();
            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose:true,
                url: 'subProjectItemLink/getTaggedSubProjectItemInfo/id/'+this.itemId
            });
            this.addChild(new dijit.layout.ContentPane({
                style: "background-color:white;border:none;padding:0px;overflow:hidden;",
                region: 'top',
                content: this.message
            }));
            this.addChild(new Grid({
                style: "background-color:white;border:none;padding:0px;overflow:hidden;",
                region: 'center',
                store: store,
                structure: [{
                        name: "No",
                        field: "id",
                        width: '30px',
                        styles: 'text-align: center;',
                        formatter: this.formatter.rowCountCellFormatter
                    },{
                        name: nls.description,
                        field: "description",
                        width: 'auto',
                        formatter: this.formatter.analysisTreeCellFormatter
                    }
                ]
            }));
        }
    });

    var MainProjectItemGrid = declare('buildspace.apps.PostContract/SubProjectItemLink/MainProjectItemGrid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        region: 'center',
        container: null,
        getSelectedItem: function(){
            return this.container.getSelectedItem();
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on("RowClick", function(e){
                var colField = e.cell.field,
                    rowIndex = e.rowIndex,
                    _item = this.getItem(rowIndex);
                if(colField == 'tag_item' && _item.id > 0 && _item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && _item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N ){
                    if(_item.tagged_to[0]){
                        var infoGrid = TaggedItemGridContainer({
                            message: nls.untagItemConfirmation+' '+nls.currentSubProjectItemTag+':',
                            itemId: _item.tagged_to
                        });

                        buildspace.dialog.confirm(nls.confirmation, infoGrid, 250, 800, function(){
                            self.untagItem(_item);
                        });
                    }
                    else{
                        self.tagItemCheck(_item);
                    }
                }
            }, true);
        },
        untagItem: function(billItem){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'subProjectItemLink/untagBillItem',
                    content: {
                        _csrf_token: billItem._csrf_token,
                        'sub_project': self.container.subProject.id,
                        'main_project_item_id': billItem.id
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success)
                        {
                            var subProjectGrid = dijit.byId(self.container.parent.subProjectContainer.id + '-itemGrid');
                            if(subProjectGrid) subProjectGrid.reload();
                            self.reload();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        tagItemCheck: function(billItem){
            var self = this;
            var selectedItem = this.getSelectedItem();

            if(!selectedItem || selectedItem.id[0] < 1){
                buildspace.dialog.alert(nls.noItemSelectedAlert, nls.pleaseSelectItemToTag+'.', 90, 320);
            }else{
                if(selectedItem.tagged_to[0]){
                    var infoGrid = TaggedItemGridContainer({
                        message: nls.tagItemConfirmation+' '+nls.willRemoveCurrentTag+' '+nls.currentMainProjectItemTag+':',
                        itemId: selectedItem.tagged_to[0]
                    });

                    buildspace.dialog.confirm(nls.confirmation, infoGrid, 250, 800, function(){
                        self.tagItem(billItem);
                    });
                }
                else{
                    self.tagItem(billItem);
                }
            }
        },
        tagItem: function(billItem){
            var self = this;
            var selectedItem = this.getSelectedItem();
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });
            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'subProjectItemLink/tagBillItem',
                    content: {
                        _csrf_token: billItem._csrf_token,
                        'main_project_item_id': billItem.id,
                        'sub_project_item_id': selectedItem.id[0]
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success)
                        {
                            var subProjectGrid = dijit.byId(self.container.parent.subProjectContainer.id + '-itemGrid');
                            if(subProjectGrid) subProjectGrid.reload();
                            self.reload();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var TaggedVariationOrderItemGridContainer = declare('buildspace.apps.PostContract/SubProjectItemLink/TaggedVariationOrderItemGridContainer', dijit.layout.BorderContainer, {
        message: null,
        style: "background-color:white;border:none;padding:0px;overflow:hidden;",
        itemId: null,
        postCreate: function(){
            this.formatter = new GridFormatter();
            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose:true,
                url: 'subProjectItemLink/getTaggedSubProjectVariationOrderItemInfo/id/'+this.itemId
            });
            this.addChild(new dijit.layout.ContentPane({
                style: "background-color:white;border:none;padding:0px;overflow:hidden;",
                region: 'top',
                content: this.message
            }));
            this.addChild(new Grid({
                style: "background-color:white;border:none;padding:0px;overflow:hidden;",
                region: 'center',
                store: store,
                structure: [{
                        name: "No",
                        field: "id",
                        width: '30px',
                        styles: 'text-align: center;',
                        formatter: this.formatter.rowCountCellFormatter
                    },{
                        name: nls.description,
                        field: "description",
                        width: 'auto',
                        formatter: this.formatter.analysisTreeCellFormatter
                    }
                ]
            }));
        }
    });

    var MainProjectVariationOrderItemGrid = declare('buildspace.apps.PostContract/SubProjectItemLink/MainProjectVariationOrderItemGrid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        region: 'center',
        container: null,
        getSelectedItem: function(){
            return this.container.getSelectedVariationOrderItem();
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on("RowClick", function(e){
                var colField = e.cell.field,
                    rowIndex = e.rowIndex,
                    _item = this.getItem(rowIndex);
                if(colField == 'tag_item' && _item.id > 0 && _item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && _item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N ){
                    if(_item.tagged_to[0]){
                        var infoGrid = TaggedVariationOrderItemGridContainer({
                            message: nls.untagItemConfirmation+' '+nls.currentSubProjectItemTag+':',
                            itemId: _item.tagged_to
                        });

                        buildspace.dialog.confirm(nls.confirmation, infoGrid, 250, 800, function(){
                            self.untagItem(_item);
                        });
                    }
                    else{
                        self.tagItemCheck(_item);
                    }
                }
            }, true);
        },
        untagItem: function(item){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });
            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'subProjectItemLink/untagVariationOrderItem',
                    content: {
                        _csrf_token: item._csrf_token,
                        'sub_project': self.container.subProject.id,
                        'main_project_item_id': item.id
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success)
                        {
                            var subProjectGrid = dijit.byId(self.container.parent.subProjectContainer.id + '-variationOrderItemGrid');
                            if(subProjectGrid) subProjectGrid.reload();
                            self.reload();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        tagItemCheck: function(item){
            var self = this;
            var selectedItem = this.getSelectedItem();

            if(!selectedItem || selectedItem.id[0] < 1){
                buildspace.dialog.alert(nls.noItemSelectedAlert, nls.pleaseSelectItemToTag+'.', 90, 320);
            }else{
                if(selectedItem.tagged_to[0]){
                    var infoGrid = TaggedVariationOrderItemGridContainer({
                        message: nls.tagItemConfirmation+' '+nls.willRemoveCurrentTag+' '+nls.currentMainProjectItemTag+':',
                        itemId: selectedItem.tagged_to[0]
                    });

                    buildspace.dialog.confirm(nls.confirmation, infoGrid, 250, 800, function(){
                        self.tagItem(item);
                    });
                }
                else{
                    self.tagItem(item);
                }
            }
        },
        tagItem: function(item){
            var self = this;
            var selectedItem = this.getSelectedItem();
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });
            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'subProjectItemLink/tagVariationOrderItem',
                    content: {
                        _csrf_token: item._csrf_token,
                        'main_project_item_id': item.id,
                        'sub_project_item_id': selectedItem.id[0]
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success)
                        {
                            var subProjectGrid = dijit.byId(self.container.parent.subProjectContainer.id + '-variationOrderItemGrid');
                            if(subProjectGrid) subProjectGrid.reload();
                            self.reload();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var SubProjectItemGrid = declare('buildspace.apps.PostContract/SubProjectItemLink/SubProjectItemGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        selectionMode: 'single',
        escapeHTMLInData: false,
        canSort: function() {
            return false;
        },
        constructor: function(args) {
            this.plugins = {
                indirectSelection: {
                    width: "20px",
                    styles: "text-align:center;"
                }
            };
            this.inherited( arguments );
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    } );

    var CustomFormatter = {
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
        itemTreeCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                level = item.level*16,
                textColor = 'black';
            cellValue = cellValue == null ? '&nbsp': cellValue;

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                cellValue = '<b>'+cellValue+'</b>';
            }
            else if(item.id != buildspace.constants.GRID_LAST_ROW)
            {
                if(item.tagged_to[0]) textColor = '#4c94e6';
            }

            cellValue = '<div class="treeNode" style="color:' + textColor + '; padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';

            return cellValue;
        }
    };

    var MainProjectContainer = declare('buildspace.apps.PostContract/SubProjectItemLink/MainProjectContainer', dijit.layout.BorderContainer, {
        project: null,
        region: 'bottom',
        style:"padding:0px;margin:0px;width:100%;height:50%;",
        gutters: false,
        parent: null,
        subProject: null,
        billGrid: null,
        variationOrderGridContainer: null,
        postCreate: function(){
            this.inherited(arguments);

            this.formatter = new GridFormatter();

            var stackContainer = this.stackContainer = this.createStackContainer();

            this.createBillGrid();
        },
        createStackContainer: function(){
            var self = this;
            var stackContainerId = this.id + '-MainProjectContainer-stackContainer';

            var stackContainer = this.stackContainer = new dijit.layout.StackContainer({
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

                    var selectedIndex = page.grid.selection.selectedIndex;

                    page.grid.store.save();
                    page.grid.store.close();

                    var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                        handle.remove();

                        if(selectedIndex > -1){
                            this.scrollToRow(selectedIndex, true);
                            this.selection.setSelected(selectedIndex, true);
                        }
                    });

                    page.grid.sort();
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
                content: controller
            });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            return stackContainer;
        },
        getBillGridStructure: function(){
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
            }];
        },
        getElementGridStructure: function(){
            return this.getBillGridStructure();
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
                formatter: CustomFormatter.itemTreeCellFormatter
            },
            {name: nls.tagItem, field: 'tag_item', width:'100px', styles:'text-align:center;',
                formatter: function(cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx);
                    if(item.id > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                        var buttonText = item.tagged_to[0] ? nls.untagItem : nls.tagItem;
                        return '<a href="#" onclick="return false;">'+buttonText+'</a>';
                    }else{
                        cell.customClasses.push('disable-cell');
                        return "&nbsp;";
                    }
                }
            }];
        },
        createBillGrid: function(){
            var self = this;

            var gridContainer = self.billGrid = new dijit.layout.BorderContainer({
                title: buildspace.truncateString(this.project.title, 60),
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                destroy: function(){
                    self.billGrid = null;
                }
            });

            var grid = gridContainer.grid = Grid({
                structure: self.getBillGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'subProjectItemLink/getMainProjectBillList/sub_project/'+self.subProject.id+'/pid/' + self.project.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.type == buildspace.apps.PostContract.ProjectStructureConstants.TYPE_BILL && item.id[0] > 0){
                        self.createElementGrid(item);
                    }
                    else if(item.type == buildspace.apps.PostContract.ProjectStructureConstants.TYPE_VARIATION_ORDER)
                    {
                        self.createVariationOrderGrid();
                    }
                }
            });

            gridContainer.addChild(grid);

            this.stackContainer.addChild(gridContainer);
        },
        createElementGrid: function(item){
            var self = this;
            this.bill = item;

            var gridContainer = new dijit.layout.BorderContainer({
                title: buildspace.truncateString(item.description, 60),
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                item: item
            });

            var grid = gridContainer.grid = Grid({
                item: self.item,
                structure: self.getElementGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'subProjectItemLink/getMainProjectElementList/sub_project/'+self.subProject.id+'/pid/' + self.project.id + '/bill_id/' + item.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0){
                        self.createItemGrid(item);
                    }
                }
            });

            gridContainer.addChild(grid);

            this.stackContainer.addChild(gridContainer);
            this.stackContainer.selectChild(gridContainer);
        },
        createItemGrid: function(item){
            var self = this;

            var gridContainer = new dijit.layout.BorderContainer({
                title: buildspace.truncateString(item.description, 60),
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false
            });

            var grid = gridContainer.grid = MainProjectItemGrid({
                container: this,
                structure: self.getItemGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'subProjectItemLink/getMainProjectItemList/sub_project/'+self.subProject.id+'/pid/' + self.project.id + '/bill_id/' + this.bill.id + '/element_id/' + item.id
                })
            });

            gridContainer.addChild(grid);

            this.stackContainer.addChild(gridContainer);
            this.stackContainer.selectChild(gridContainer);
        },
        createVariationOrderGrid: function(){
            var self = this;

            var gridContainer = this.variationOrderGridContainer = new dijit.layout.BorderContainer({
                title: nls.variationOrders,
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                destroy: function(){
                    self.variationOrderGridContainer = null;
                    var subProjectContainer = self.parent.subProjectContainer;
                    if(subProjectContainer && subProjectContainer.billGrid)
                    {
                        subProjectContainer.stackContainer.selectChild(subProjectContainer.billGrid);
                    }
                }
            });

            var grid = gridContainer.grid = Grid({
                structure: self.getElementGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'subProjectItemLink/getMainProjectVariationOrderList/sub_project/'+self.subProject.id+'/pid/' + self.project.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0){
                        self.createVariationOrderItemGrid(item);
                    }
                }
            });

            gridContainer.addChild(grid);

            this.stackContainer.addChild(gridContainer);
            this.stackContainer.selectChild(gridContainer);

            if(!this.parent.subProjectContainer.variationOrderGridContainer && this.parent.subProjectContainer.billGrid){
                this.parent.subProjectContainer.stackContainer.selectChild(this.parent.subProjectContainer.billGrid);
                this.parent.subProjectContainer.createVariationOrderGrid();
            }
        },
        createVariationOrderItemGrid: function(item){
            var self = this;

            var gridContainer = new dijit.layout.BorderContainer({
                title: buildspace.truncateString(item.description, 60),
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false
            });

            var grid = gridContainer.grid = MainProjectVariationOrderItemGrid({
                container: this,
                structure: self.getItemGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'subProjectItemLink/getMainProjectVariationOrderItemList/sub_project/'+self.subProject.id+'/pid/' + self.project.id + '/vo_id/' + item.id
                })
            });

            gridContainer.addChild(grid);

            this.stackContainer.addChild(gridContainer);
            this.stackContainer.selectChild(gridContainer);
        },
        getSelectedItem: function(){
            return this.parent.getSelectedItem();
        },
        getSelectedVariationOrderItem: function(){
            return this.parent.getSelectedVariationOrderItem();
        },
        close: function(){
            this.parentContainer.removeChild(this);
            return this.destroyRecursive();
        }
    } );

    var SubProjectContainer = declare('buildspace.apps.PostContract/SubProjectItemLink/SubProjectContainer', dijit.layout.BorderContainer, {
        project: null,
        region: 'center',
        style:"padding:0px;width:100%;height:100%;",
        gutters: false,
        splitter: true,
        parent: null,
        subProject: null,
        bill: null,
        billGrid: null,
        variationOrderGridContainer: null,
        postCreate: function(){
            this.inherited(arguments);

            this.formatter = new GridFormatter();

            var stackContainer = this.stackContainer = this.createStackContainer();

            if(this.subProject){
                this.createBillGrid(this.subProject);
                this.initMainProjectGrid(this.subProject);
            }
            else{
                this.createSubProjectGrid();
            }
        },
        createStackContainer: function(){
            var self = this;
            var stackContainerId = this.id + '-SubProjectContainer-stackContainer';

            var stackContainer = new dijit.layout.StackContainer({
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

                    var selectedIndex = page.grid.selection.selectedIndex;

                    page.grid.store.save();
                    page.grid.store.close();

                    var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                        handle.remove();

                        if(selectedIndex > -1){
                            this.scrollToRow(selectedIndex, true);
                            this.selection.setSelected(selectedIndex, true);
                        }
                    });

                    page.grid.sort();
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
                content: controller
            });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            return stackContainer;
        },
        getBillGridStructure: function(){
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
            }];
        },
        getElementGridStructure: function(){
            return this.getBillGridStructure();
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
                formatter: CustomFormatter.itemTreeCellFormatter
            }];
        },
        getSubProjectGridStructure: function(){
            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: this.formatter.rowCountCellFormatter
            },{
                name: nls.description,
                field: "title",
                width: 'auto'
            }];
        },
        createSubProjectGrid: function(){
            var self = this;
            var gridContainer = new dijit.layout.BorderContainer({
                title: nls.subPackages,
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false
            });

            var grid = gridContainer.grid = Grid({
                item: self.item,
                structure: self.getSubProjectGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'subProjectItemLink/getSubProjectList/pid/' + self.project.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0){
                        self.createBillGrid(item);
                        self.initMainProjectGrid(item);
                    }
                }
            });

            gridContainer.addChild(grid);

            gridContainer.grid = grid;

            this.stackContainer.addChild(gridContainer);
        },
        createBillGrid: function(item){
            var self = this;

            var gridContainer = this.billGrid = new dijit.layout.BorderContainer({
                title: buildspace.truncateString(item.title, 60),
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                destroy: function(){
                    self.billGrid = null;
                    self.parent.removeMainProjectContainer();
                },
            });

            var grid = gridContainer.grid = Grid({
                structure: self.getBillGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'subProjectItemLink/getSubProjectBillList/pid/' + item.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.type == buildspace.apps.PostContract.ProjectStructureConstants.TYPE_BILL && item.id[0] > 0){
                        self.createElementGrid(item);
                    }
                    else if(item.type == buildspace.apps.PostContract.ProjectStructureConstants.TYPE_VARIATION_ORDER)
                    {
                        self.createVariationOrderGrid();
                    }
                }
            });

            this.subProject = item;

            gridContainer.addChild(grid);

            this.stackContainer.addChild(gridContainer);

            if(this.stackContainer.getChildren().length > 1) this.stackContainer.selectChild(gridContainer);
        },
        createElementGrid: function(item){
            var self = this;
            this.bill = item;

            var gridContainer = new dijit.layout.BorderContainer({
                title: buildspace.truncateString(item.description, 60),
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false
            });

            var grid = gridContainer.grid = Grid({
                structure: self.getElementGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'subProjectItemLink/getSubProjectElementList/pid/' + this.subProject.id + '/bill_id/' + item.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0){
                        self.createItemGrid(item);
                    }
                }
            });

            gridContainer.addChild(grid);

            this.stackContainer.addChild(gridContainer);
            this.stackContainer.selectChild(gridContainer);
        },
        createItemGrid: function(item){
            var self = this;

            var gridContainer = new dijit.layout.BorderContainer({
                title: buildspace.truncateString(item.description, 60),
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false
            });

            var grid = gridContainer.grid = SubProjectItemGrid({
                id: this.id + '-itemGrid',
                structure: self.getItemGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'subProjectItemLink/getSubProjectItemList/pid/' + this.subProject.id + '/bill_id/' + this.bill.id + '/element_id/' + item.id
                })
            });

            gridContainer.addChild(grid);

            this.stackContainer.addChild(gridContainer);
            this.stackContainer.selectChild(gridContainer);
        },
        getSelectedItem: function(){
            var itemGrid = dijit.byId(this.id + '-itemGrid');
            if(itemGrid)
            {
                var selectedItems = itemGrid.selection.getSelected();
                if(selectedItems.length > 0)
                {
                    if(!isNaN(selectedItems[0].id[0])) return selectedItems[0];
                }
            }
            return null;
        },
        getSelectedVariationOrderItem: function(){
            var itemGrid = dijit.byId(this.id + '-variationOrderItemGrid');
            if(itemGrid)
            {
                var selectedItems = itemGrid.selection.getSelected();
                if(selectedItems.length > 0)
                {
                    if(!isNaN(selectedItems[0].id[0])) return selectedItems[0];
                }
            }
            return null;
        },
        createVariationOrderGrid: function(){
            var self = this;

            var gridContainer = this.variationOrderGridContainer = new dijit.layout.BorderContainer({
                title: nls.variationOrders,
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                destroy: function(){
                    self.variationOrderGridContainer = null;
                    var mainProjectContainer = self.parent.mainProjectContainer;
                    if(mainProjectContainer && mainProjectContainer.billGrid)
                    {
                        mainProjectContainer.stackContainer.selectChild(mainProjectContainer.billGrid);
                    }
                }
            });

            var grid = gridContainer.grid = Grid({
                structure: self.getElementGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'subProjectItemLink/getSubProjectVariationOrderList/pid/' + this.subProject.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0){
                        self.createVariationOrderItemGrid(item);
                    }
                }
            });

            gridContainer.addChild(grid);

            this.stackContainer.addChild(gridContainer);
            this.stackContainer.selectChild(gridContainer);

            if(!this.parent.mainProjectContainer.variationOrderGridContainer && this.parent.mainProjectContainer.billGrid){
                this.parent.mainProjectContainer.stackContainer.selectChild(this.parent.mainProjectContainer.billGrid);
                this.parent.mainProjectContainer.createVariationOrderGrid();
            }
        },
        createVariationOrderItemGrid: function(item){
            var self = this;

            var gridContainer = new dijit.layout.BorderContainer({
                title: buildspace.truncateString(item.description, 60),
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false
            });

            var grid = gridContainer.grid = SubProjectItemGrid({
                id: this.id + '-variationOrderItemGrid',
                structure: self.getItemGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'subProjectItemLink/getSubProjectVariationOrderItemList/pid/' + this.subProject.id + '/vo_id/' + item.id
                })
            });

            gridContainer.addChild(grid);

            this.stackContainer.addChild(gridContainer);
            this.stackContainer.selectChild(gridContainer);
        },
        initMainProjectGrid: function(subProject){
            this.parent.addMainProjectContainer(subProject);
        },
        close: function(){
            this.parentContainer.removeChild(this);
            return this.destroyRecursive();
        }
    } );

    return declare('buildspace.apps.PostContract.SubProjectItemLink.GridContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        grid: null,
        stackContainer: null,
        parentContainer: null,
        subProjectContainer: null,
        mainProjectContainer: null,
        project: null,
        bill: null,
        mainProject: null,
        subProject: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.processing+'...'
            });
            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'subProjectItemLink/getProjectsInformation',
                    content: {
                        'pid': self.project.id
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        self.mainProject = resp.mainProject;
                        self.subProject = resp.subProject;

                        var subProjectContainer = self.subProjectContainer = new SubProjectContainer({
                            project: self.mainProject,
                            subProject: self.subProject,
                            parent: self
                        });
                        self.addChild(subProjectContainer);

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        addMainProjectContainer: function(subProject){
            var mainProjectContainer = dijit.byId(this.id+'-mainProjectContainer');
            if(!mainProjectContainer)
            {
                mainProjectContainer = this.mainProjectContainer = new MainProjectContainer({
                    id: this.id+'-mainProjectContainer',
                    project: this.mainProject,
                    subProject: subProject,
                    parent: this
                });
                this.addChild(mainProjectContainer);
            }
        },
        removeMainProjectContainer: function(){
            var mainProjectContainer = dijit.byId(this.id+'-mainProjectContainer');
            if(mainProjectContainer)
            {
                this.removeChild(mainProjectContainer);
                mainProjectContainer.destroy();

                this.mainProjectContainer = null;
            }
        },
        getSelectedItem: function(){
            return this.subProjectContainer.getSelectedItem();
        },
        getSelectedVariationOrderItem: function(){
            return this.subProjectContainer.getSelectedVariationOrderItem();
        },
        close: function(){
            this.parentContainer.removeChild(this);
            return this.destroyRecursive();
        }
    });
});