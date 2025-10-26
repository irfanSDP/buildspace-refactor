define('buildspace/apps/CostData/ProjectInformation',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    './ProjectInformationComparisonReport/ComparisonReportDialog',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, lang, EnhancedGrid, GridFormatter, ComparisonReportDialog, nls){

    var Grid = declare('buildspace.apps.CostData.ProjectInformationGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        escapeHTMLInData: false,
        costData: null,
        parentItem: null,
        constructor:function(args){
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        focusCell: function(rowIdx, inAttrName){
            var self = this;
            var cell = self.getCellByField(inAttrName);
            window.setTimeout(function() {
                self.focus.setFocusIndex(rowIdx, cell.index);
            }, 10);
        },
        updateRecord: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });
            var params = {
                id: item.id,
                val: val,
                cost_data_id: self.costData.id,
                item_id: item.id,
                _csrf_token: item._csrf_token ? item._csrf_token : null
            };

            var updateCell = function(data, store){
                for(var property in data){
                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                        store.setValue(item, property, data[property]);
                    }
                }
                store.save();
            };

            var xhrArgs = {
                url: 'projectInformation/updateItem',
                content: params,
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        if(item.id > 0){
                            updateCell(resp.data, store);
                        }
                        self.focusCell(rowIdx, inAttrName);
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            };

            pb.show();
            dojo.xhrPost(xhrArgs);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx);

            if(val !== item[inAttrName][0]){
                self.updateRecord(val, rowIdx, inAttrName);
            }

            self.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            if(inCell != undefined) {
                var item = this.getItem(inRowIndex);
                return item.id[0] > 0;
            }

            return this._canEdit;
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return parseInt(rowIdx)+1;
        },
        descriptionFormatter: function(cellValue, rowIdx, cell){
            return cellValue;
        }
    };

    var Container = declare('buildspace.apps.CostData.ProjectInformationContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
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
                    label: nls.refresh,
                    iconClass: "icon-16-container icon-16-reload",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.grid.reload();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.comparisonReport,
                    iconClass: "icon-16-container icon-16-print",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.createComparisonReportDialog();
                    }
                })
            );

            return toolbar;
        },
        createBreakdownGrid: function(){
            var self = this;
            var structure = [
                {
                    name: 'No.',
                    field: 'count',
                    width:'30px',
                    styles:'text-align:center;',
                    formatter: Formatter.rowCountCellFormatter
                },{
                    name: nls.item,
                    field: 'description',
                    width: (this.level == 1) ? 'auto' : '280px'
                }
            ];

            if(this.level > 1){
                structure.push({
                    name: nls.description,
                    field: 'value',
                    width:'auto',
                    cellType: 'buildspace.widget.grid.cells.Textarea',
                    editable: true
                });
            }

            var gridUrl = "projectInformation/getBreakdown/costData/"+this.costData.id;

            if(this.parentItem) gridUrl += "/parent_id/"+this.parentItem.id;

            self.grid = Grid({
                costData: self.costData,
                parentItem: self.parentItem,
                structure: structure,
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:gridUrl
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
        createComparisonReportDialog: function(){
            var exportUrl = 'projectInformationComparisonReport/export/costData/'+this.costData.id;

            if(this.parentItem) exportUrl = exportUrl +'/parent_id/' + this.parentItem.id;

            var comparisonReportDialog = new ComparisonReportDialog({
                gridUrl: "costData/getCostDataList/costData/"+this.costData.id,
                title: nls.comparisonReport,
                exportUrl: exportUrl,
                costData: this.costData,
                projectInfoItem: this.parentItem
            });

            comparisonReportDialog.show();
        },
    });

    return declare('buildspace.apps.CostData.ProjectInformationStackContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
        grid: null,
        stackContainer: null,
        level: null,
        postCreate: function(){
            this.inherited(arguments);

            this.stackContainer = this.createStackContainer();

            this.addBreakdownContainer(null, 1);
        },
        createStackContainer: function(){
            var self = this;
            var stackContainerId = 'costDataProjectInformation' + self.costData.id + '-stackContainer';

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
                id: 'costDataProjectInformation'+self.costData.id+'-controllerPane',
                content: controller
            });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            return stackContainer;
        },
        addBreakdownContainer: function(item, level){
            var self = this;

            var containerDescription = item ? item.description : nls.projectInfo;

            var container = new Container({
                title: buildspace.truncateString(containerDescription, 60),
                costData: this.costData,
                level: level,
                stackContainer: self,
                parentItem: item
            });

            self.stackContainer.addChild(container);

            if(self.stackContainer.getChildren().length > 1) self.stackContainer.selectChild(container);
        }
    });
});