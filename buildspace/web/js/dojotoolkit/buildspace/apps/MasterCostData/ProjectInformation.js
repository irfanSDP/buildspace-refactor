define('buildspace/apps/MasterCostData/ProjectInformation',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, lang, EnhancedGrid, GridFormatter, nls){

    var Grid = declare('buildspace.apps.MasterCostData.ProjectInformationGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        masterCostData: null,
        parentItem: null,
        addUrl: 'masterCostData/addNewProjectInformation',
        updateUrl: 'masterCostData/updateProjectInformation',
        constructor:function(args){
            this.inherited(arguments);
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
                    var enable = false;
                    if(item.id[0] > 0) {
                        enable = true;
                    }

                    self.enableToolbarButtons(enable);
                }
            });
        },
        enableToolbarButtons: function(enable){
            var deleteButton = dijit.byId(this.parentItem.id + 'project-information-delete-button');
            deleteButton._setDisabledAttr(!enable);
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    val: val,
                    master_cost_data_id: self.masterCostData.id,
                    parent_id: self.parentItem.id,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                if(item.id==buildspace.constants.GRID_LAST_ROW){
                    var prevItem = rowIdx > 0 ? self.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0
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
            this.store.close();
            this._refresh();
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return parseInt(rowIdx)+1;
        }
    };

    var Container = declare('buildspace.apps.MasterCostData.ProjectInformationContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        masterCostData: null,
        grid: null,
        stackContainer: null,
        level: null,
        parentItem: null,
        postCreate: function(){
            this.inherited(arguments);

            var grid = this.createBreakdownGrid();

            var gridContainer = new dijit.layout.ContentPane( {
                style:"padding:0px;border:none;width:100%;height:100%;",
                region: 'center',
                content: grid,
                grid: grid
            });

            this.addChild(this.createToolbar());
            this.addChild(gridContainer);
        },
        createToolbar: function(){
            var self = this;
            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    id: self.parentItem.id + 'project-information-delete-button',
                    label: nls.delete,
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

            return toolbar;
        },
        createBreakdownGrid: function(){
            var self = this;
            self.grid = Grid({
                masterCostData: self.masterCostData,
                parentItem: self.parentItem,
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
                        editable: true
                    }
                ],
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"masterCostData/getProjectInformationBreakdown/masterCostData/"+this.masterCostData.id+"/parent_id/"+self.parentItem.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);
                    if(self.level < 2 && item.id[0] > 0){
                        self.stackContainer.addBreakdownContainer(item, self.level+1);
                    }
                }
            });

            return self.grid;
        },
        deleteRow: function (item) {
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.deleting + '. ' + nls.pleaseWait + '...'
                });

            var onYes = function () {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'masterCostData/deleteProjectInformation',
                        content: {id: item.id, _csrf_token: item._csrf_token},
                        handleAs: 'json',
                        load: function (resp) {
                            if (resp.success) {
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

            buildspace.dialog.confirm(nls.confirmation, nls.confirmationMessage + '<br/>' + nls.cannotBeUndone, 80, 300, onYes);
        }
    });

    return declare('buildspace.apps.MasterCostData.ProjectInformationStackContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        masterCostData: null,
        grid: null,
        stackContainer: null,
        level: null,
        postCreate: function(){
            this.inherited(arguments);

            this.stackContainer = this.createStackContainer();

            this.addBreakdownContainer({id: 0, description: nls.projectInfo}, 1);
        },
        createStackContainer: function(){
            var self = this;
            var stackContainerId = 'masterCostDataProjectInformation' + self.masterCostData.id + '-stackContainer';

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
                id: 'masterCostDataProjectInformation'+self.masterCostData.id+'-controllerPane',
                content: controller
            });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            return stackContainer;
        },
        addBreakdownContainer: function(item, level){
            var self = this;

            var container = new Container({
                title: buildspace.truncateString(item.description, 60),
                masterCostData: this.masterCostData,
                level: level,
                stackContainer: self,
                parentItem: item
            });

            self.stackContainer.addChild(container);

            if(self.stackContainer.getChildren().length > 1) self.stackContainer.selectChild(container);
        }
    });
});