define('buildspace/apps/MasterCostData/Breakdown',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/Rearrange",
    'buildspace/widget/grid/cells/Formatter',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dijit/focus",
    "dojo/when",
    'dojo/aspect',
    './WorkCategoryGrid',
    './PrimeCostSumGrid',
    './PrimeCostRateGrid',
    'buildspace/widget/grid/cells/Textarea',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, lang, EnhancedGrid, Rearrange, GridFormatter, DropDownButton, DropDownMenu, MenuItem, focusUtil, when, aspect, WorkCategoryGrid, PrimeCostSumGrid, PrimeCostRateGrid, Textarea, nls){

    var Grid = declare('buildspace.apps.MasterCostData.BreakdownGrid', EnhancedGrid, {
        masterCostData: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        borderContainerWidget: null,
        addUrl: 'masterCostData/addNewProjectOverallCostingItem',
        updateUrl: 'masterCostData/updateItem',
        constructor:function(args){
            this.inherited(arguments);
            this.rearranger = Rearrange(this, {});
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on('RowClick', function (e) {
                if (e.cell) {
                    var item = this.getItem(e.rowIndex);
                    if(item.id[0] > 0) {
                        self.enableToolbarButtons(true);
                    }
                    else if(item.id[0] === buildspace.constants.GRID_LAST_ROW) {
                        self.enableToolbarButtons(true);
                        var deleteButton = dijit.byId(self.masterCostData.id + '-delete-button');
                        deleteButton._setDisabledAttr(true);
                    }
                }
            });
        },
        enableToolbarButtons: function(enable){
            var deleteButton = dijit.byId(this.masterCostData.id + '-delete-button');
            deleteButton._setDisabledAttr(!enable);
            var addButton = dijit.byId(this.masterCostData.id + '-add-button');
            addButton._setDisabledAttr(!enable);
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            if(inAttrName != 'description') return;

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });

                var params = {
                    id: item.id,
                    attr_name: inAttrName,
                    val: val,
                    master_cost_data_id: self.masterCostData.id,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                if(item.id==buildspace.constants.GRID_LAST_ROW){
                    var prevItem = rowIdx > 0 ? self.getItem(rowIdx-1):false;
                    var currentItem = self.getItem(rowIdx);
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0,
                        current_id: currentItem.id
                    });
                    url = this.addUrl;
                }

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }
                    store.save();
                };

                var xhrArgs = {
                    url: url,
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            if(item.id > 0){
                                updateCell(resp.data, store);
                            }else{
                                store.deleteItem(item);
                                store.save();
                                dojo.forEach(resp.items, function(item){
                                    store.newItem(item);
                                });
                                store.save();
                                self.enableToolbarButtons(false);
                            }
                            var cell = self.getCellByField(inAttrName);
                            window.setTimeout(function() {
                                self.focus.setFocusIndex(rowIdx, cell.index);
                            }, 10);
                            pb.hide();
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                };

                pb.show();
                dojo.xhrPost(xhrArgs);
            }
            self.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            return true;
        },
        reload: function(){
            this.store.save();
            this.store.close();
            this.sort();
            this._refresh();
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.id[0] == 'row_separator') cell.customClasses.push('invalidTypeItemCell');
            if(!((item.id[0] > 0) || (item.id[0] == buildspace.constants.GRID_LAST_ROW))) return null;
            return parseInt(rowIdx)-3;
        },
        descriptionFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.id[0] == 'row_separator') cell.customClasses.push('invalidTypeItemCell');
            return cellValue;
        },
        typeFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.id[0] == 'row_separator') cell.customClasses.push('invalidTypeItemCell');
            return (item.id[0] != buildspace.constants.GRID_LAST_ROW && item.id[0] != 'row_separator') ? buildspace.apps.MasterCostData.getItemTypeText(cellValue) : '';
        }
    };

    return declare('buildspace.apps.MasterCostData.BreakdownContainer', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        masterCostData: null,
        grid: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var stackContainer = this.createStackContainer();

            var grid = this.createBreakdownGrid();

            var gridContainer = new dijit.layout.ContentPane( {
                style:"padding:0px;border:none;width:100%;height:100%;",
                region: 'center',
                content: grid,
                grid: grid
            });

            var child = new dijit.layout.BorderContainer({
                style: "padding:0px;width:100%;height:100%;",
                gutters: false,
                title: buildspace.truncateString(nls.overallProjectCosting, 60)
            });

            child.addChild(self.createToolbar());
            child.addChild(gridContainer);

            stackContainer.addChild(child);
        },
        createStackContainer: function(){
            var self = this;
            var stackContainerId = 'masterCostDataBreakdown' + self.masterCostData.id + '-stackContainer';

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
                containerId: 'masterCostDataBreakdown' + self.masterCostData.id + '-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                id: 'masterCostDataBreakdown'+self.masterCostData.id+'-controllerPane',
                content: controller
            });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            return stackContainer;
        },
        createToolbar: function(){
            var self = this;

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.masterCostData.id + '-add-button',
                    label: nls.addRow,
                    iconClass: "icon-16-container icon-16-add",
                    disabled: self.grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(){
                        if (self.grid.selection.selectedIndex > -1) {
                            self.addRow(self.grid.selection.selectedIndex);
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.masterCostData.id + '-delete-button',
                    label: nls.deleteRow,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: true,
                    style: "outline:none!important;",
                    onClick: function () {
                        if (self.grid.selection.selectedIndex > -1) {
                            var item = self.grid.getItem(self.grid.selection.selectedIndex);
                            if (item.id[0] > 0) {
                                self.deleteRow(item);
                            }
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.refresh,
                    iconClass: "icon-16-container icon-16-reload",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.grid.reload();
                    }
                })
            );

            return toolbar;
        },
        createBreakdownGrid: function(){
            var self = this;
            self.grid = Grid({
                masterCostData: self.masterCostData,
                borderContainerWidget: self,
                structure:[
                    {
                        name: 'No.',
                        field: 'count',
                        width:'30px',
                        styles:'text-align:center;',
                        formatter: Formatter.rowCountCellFormatter
                    },{
                        name: nls.description,
                        field: 'description',
                        width:'auto',
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        editable: true,
                        formatter: Formatter.descriptionFormatter
                    },{
                        name: nls.type,
                        field: 'type',
                        width:'150px', styles:'text-align: center;',
                        formatter: Formatter.typeFormatter
                    }
                ],
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);
                    switch(item.type[0]){
                        case buildspace.apps.MasterCostData.ItemTypes.STANDARD:
                            if(item.id[0] > 0) self.createWorkCategoryGrid(item);
                            break;
                        case buildspace.apps.MasterCostData.ItemTypes.PROVISIONAL_SUM:
                            // Do nothing.
                            break;
                        case buildspace.apps.MasterCostData.ItemTypes.PRIME_COST_SUM:
                            self.createPrimeCostSumGrid(item);
                            break;
                        case buildspace.apps.MasterCostData.ItemTypes.PRIME_COST_RATE:
                            self.createPrimeCostRateGrid(item, 1);
                            break;
                    }
                },
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"masterCostData/getBreakdown/id/"+this.masterCostData.id
                })
            });

            return self.grid;
        },
        createWorkCategoryGrid: function(item){
            var workCategoryGrid = new WorkCategoryGrid({
                title: buildspace.truncateString(item.description, 60),
                stackContainer: this.stackContainer,
                masterCostData: this.masterCostData,
                projectCostingItem: {id: item.id, _csrf_token: item._csrf_token}
            });

            this.stackContainer.addChild(workCategoryGrid);
            this.stackContainer.selectChild(workCategoryGrid);
        },
        createPrimeCostSumGrid: function(item){
            var primeCostSumGrid = new PrimeCostSumGrid({
                title: buildspace.truncateString(item.description, 60),
                stackContainer: this.stackContainer,
                masterCostData: this.masterCostData
            });

            this.stackContainer.addChild(primeCostSumGrid);
            this.stackContainer.selectChild(primeCostSumGrid);
        },
        createPrimeCostRateGrid: function(item, level){
            if(!(item.id > 0)) item.id = 0;
            var primeCostRateGrid = new PrimeCostRateGrid({
                title: buildspace.truncateString(item.description, 60),
                stackContainer: this.stackContainer,
                masterCostData: this.masterCostData,
                gridCreator: this,
                parentItem: item,
                level: level
            });

            this.stackContainer.addChild(primeCostRateGrid);
            this.stackContainer.selectChild(primeCostRateGrid);
        },
        addRow: function(rowIndex){
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.addingRow+'. '+nls.pleaseWait+'...'
                }),
                store = self.grid.store,
                currentItem = self.grid.getItem(rowIndex);

            var prevItemId = (rowIndex > 0) ? self.grid.getItem(rowIndex-1).id : 0;
            var content = {
                master_cost_data_id: self.masterCostData.id,
                prev_item_id: prevItemId,
                current_id: currentItem.id,
                _csrf_token:currentItem._csrf_token
            };

            pb.show().then(function(){
                dojo.xhrPost({
                    url: self.grid.addUrl,
                    content: content,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            dojo.forEach(resp.items,function(data){
                                if(data.id > 0){
                                    var item = store.newItem(data);
                                    store.save();
                                    var itemIdx = self.grid.getItemIndex(item);
                                    self.grid.rearranger.moveRows([itemIdx], rowIndex);
                                    self.grid.selection.clear();
                                }
                            });
                        }
                        window.setTimeout(function() {
                            self.grid.selection.setSelected(rowIndex, true);
                            self.grid.focus.setFocusIndex(rowIndex, 1);
                        },30);
                        pb.hide();
                    },
                    error: function(error) {
                        self.grid.selection.clear();
                        self.grid.enableToolbarButtons(true);
                        pb.hide();
                    }
                });
            });
        },
        deleteRow: function (item) {
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.deleting + '. ' + nls.pleaseWait + '...'
                });

            var onYes = function () {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'masterCostData/deleteItem',
                        content: {id: item.id, _csrf_token: item._csrf_token},
                        handleAs: 'json',
                        load: function (resp) {
                            if (resp.success) {
                                self.grid.store.deleteItem(item)
                                self.grid.store.save();
                                self.grid.reload();
                            }
                            else{
                                buildspace.dialog.alert(nls.cannotBeDeleted, resp.errorMsg, 80, 300);
                            }
                            pb.hide();
                            self.grid.selection.clear();
                            self.grid.enableToolbarButtons(false);
                        },
                        error: function (error) {
                            pb.hide();
                            self.grid.selection.clear();
                            self.grid.enableToolbarButtons(false);
                        }
                    });
                });
            };

            buildspace.dialog.confirm(nls.confirmation, nls.confirmationMessage + '<br/>' + nls.willDeleteAllChildItems + '<br/>' + nls.cannotBeUndone, 80, 300, onYes);
        }
    });
});