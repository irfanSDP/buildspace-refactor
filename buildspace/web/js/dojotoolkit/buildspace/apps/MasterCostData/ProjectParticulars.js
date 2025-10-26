define('buildspace/apps/MasterCostData/ProjectParticulars',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dojo/when",
    'dojo/aspect',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/Select',
    './SelectComponentsDialog',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, lang, EnhancedGrid, GridFormatter, DropDownButton, DropDownMenu, MenuItem, when, aspect, Textarea, Select, SelectComponentsDialog, nls){

    var Grid = declare('buildspace.apps.MasterCostData.ProjectParticularsGrid', EnhancedGrid, {
        masterCostData: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        borderContainerWidget: null,
        addUrl: 'masterCostData/addNewProjectParticular',
        updateUrl: 'masterCostData/updateProjectParticular',
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
                    var colField = e.cell.field;
                    var item = this.getItem(e.rowIndex);
                    var enable = false;
                    if(item.id[0] > 0) {
                        enable = true;
                    }

                    self.enableToolbarButtons(enable);

                    switch(colField)
                    {
                        case 'is_summary_displayed':
                            if(item && item.id[0] > 0){
                                self.borderContainerWidget.toggleDisplayInSummary(item);
                            }
                            break;
                        case 'is_prime_cost_rate_summary_displayed':
                            if(item && item.id[0] > 0){
                                self.borderContainerWidget.togglePrimeCostRateDisplayInSummary(item);
                            }
                            break;
                        case 'is_used_for_cost_comparison':
                            if(item && item.id[0] > 0){
                                self.borderContainerWidget.useForCostComparison(item);
                            }
                            break;
                        case 'components':
                            if(item && item.id[0] > 0){
                                self.borderContainerWidget.createSelectComponentsDialog(item);
                            }
                            break;
                    }
                }                
            });
        },
        enableToolbarButtons: function(enable){
            var deleteButton = dijit.byId(this.masterCostData.id + '-project-particular-delete-button');
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
                    attr_name: inAttrName,
                    val: val,
                    master_cost_data_id: self.masterCostData.id,
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

                        }
                        else{
                            buildspace.dialog.alert(nls.cannotBeEdited, resp.errorMsg, 80, 300, function(){updateCell(resp.data, store);});
                        }
                        pb.hide();
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

    return declare('buildspace.apps.MasterCostData.ProjectParticularsContainer', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        masterCostData: null,
        grid: null,
        unitsOfMeasurement: [],
        postCreate: function(){
            this.inherited(arguments);

            this.gridFormatter = new GridFormatter();

            var self = this;

            self.addChild(self.createToolbar());

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show();
            dojo.xhrPost({
                url: 'default/getAllUnits',
                handleAs: 'json',
                load: function(resp) {
                    self.unitsOfMeasurement.values = resp.values;
                    self.unitsOfMeasurement.options = resp.options;
                },
                error: function(error) {
                }
            }).then(function(){
                var grid = self.createBreakdownGrid();

                var gridContainer = new dijit.layout.ContentPane( {
                    style:"padding:0px;border:none;width:100%;height:100%;",
                    region: 'center',
                    content: grid,
                    grid: grid
                });
                self.addChild(gridContainer);
                pb.hide();
            });
        },
        createToolbar: function(){
            var self = this;
            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.masterCostData.id + '-project-particular-delete-button',
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
                        formatter: this.gridFormatter.rowCountCellFormatter
                    },{
                        name: nls.description,
                        field: 'description',
                        width: '250px',
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        editable: true
                    },{
                        name: nls.unit,
                        field: 'uom_id',
                        width: '70px',
                        editable: true,
                        styles: 'text-align:center;',
                        type: 'dojox.grid.cells.Select',
                        options: self.unitsOfMeasurement.options,
                        values: self.unitsOfMeasurement.values,
                        formatter: this.gridFormatter.unitCellFormatter
                    },{
                        name: nls.displayInSummary,
                        field: 'is_summary_displayed',
                        width: '70px',
                        styles: 'text-align:center;',
                        formatter: this.gridFormatter.includedCellFormatter
                    },{
                        name: nls.displayInPrimeCostRateSummary,
                        field: 'is_prime_cost_rate_summary_displayed',
                        width: '70px',
                        styles: 'text-align:center;',
                        formatter: this.gridFormatter.includedCellFormatter
                    },{
                        name: nls.summaryDescription,
                        field: 'summary_description',
                        width:'auto',
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        editable: true
                    },{
                        name: nls.displayComparison,
                        field: 'is_used_for_cost_comparison',
                        width: '70px',
                        styles: 'text-align:center;',
                        formatter: this.gridFormatter.includedCellFormatter
                    },{
                        name: nls.components,
                        field: 'components',
                        width: '70px',
                        styles: 'text-align:center;',
                        formatter: function(cellValue, rowIdx, cell){
                            var item = this.grid.getItem(rowIdx);
                            if(item && parseInt(String(item.id)) > 0){
                                return '<a href="#" onclick="return false;">'+nls.select+'</a>';
                            }
                            else{
                                return "&nbsp;";
                            }
                        },
                    }
                ],
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"masterCostData/getProjectParticularList/master_cost_data_id/"+this.masterCostData.id
                })
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
                        url: 'masterCostData/deleteProjectParticular',
                        content: {id: item.id, _csrf_token: item._csrf_token},
                        handleAs: 'json',
                        load: function (resp) {
                            if (resp.success) {
                                self.grid.reload();
                                self.grid.selection.clear();
                                self.grid.enableToolbarButtons(false);
                            }
                            else{
                                buildspace.dialog.alert(nls.cannotBeDeleted, resp.errorMsg, 80, 300);
                            }
                            pb.hide();
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
        },
        toggleDisplayInSummary: function(item){
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });

            pb.show();

            dojo.xhrPost({
                url: 'masterCostData/toggleDisplayInSummary/master_cost_data_id/'+self.masterCostData.id,
                content: {
                    id: item.id,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                },
                handleAs: 'json',
                load: function(data) {
                    if(data.success){
                        self.grid.store.fetchItemByIdentity({ 'identity' : item.id,  onItem : function(affectedItem){
                            for(var property in data.data){
                                if(affectedItem.hasOwnProperty(property) && property != self.grid.store._getIdentifierAttribute()){
                                    self.grid.store.setValue(affectedItem, property, data.data[property]);
                                }
                            }
                        }});
                        self.grid.store.save();
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            });
        },
        togglePrimeCostRateDisplayInSummary: function(item){
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });

            pb.show();

            dojo.xhrPost({
                url: 'masterCostData/togglePrimeCostRateDisplayInSummary/master_cost_data_id/'+self.masterCostData.id,
                content: {
                    id: item.id,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                },
                handleAs: 'json',
                load: function(data) {
                    if(data.success){
                        self.grid.store.fetchItemByIdentity({ 'identity' : item.id,  onItem : function(affectedItem){
                            for(var property in data.data){
                                if(affectedItem.hasOwnProperty(property) && property != self.grid.store._getIdentifierAttribute()){
                                    self.grid.store.setValue(affectedItem, property, data.data[property]);
                                }
                            }
                        }});
                        self.grid.store.save();
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            });
        },
        useForCostComparison: function(item){
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });

            pb.show();

            dojo.xhrPost({
                url: 'masterCostData/useForCostComparison/master_cost_data_id/'+self.masterCostData.id,
                content: {
                    id: item.id,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                },
                handleAs: 'json',
                load: function(data) {
                    if(data.success){
                        self.grid.store.fetchItemByIdentity({ 'identity' : item.id,  onItem : function(affectedItem){
                            for(var property in data.data){
                                if(affectedItem.hasOwnProperty(property) && property != self.grid.store._getIdentifierAttribute()){
                                    self.grid.store.setValue(affectedItem, property, data.data[property]);
                                }
                            }
                        }});
                        self.grid.store.save();
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            });
        },
        createSelectComponentsDialog: function(item){
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            var params = {
                _csrf_token: item._csrf_token ? item._csrf_token : null
            };

            var xhrArgs = {
                url: "masterCostData/getProjectParticularGetSelectedComponents/id/"+item.id,
                content: params,
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        var selectComponentsDialog = new SelectComponentsDialog({
                            gridUrl: "masterCostData/getProjectParticularComponentList/master_cost_data_id/"+self.masterCostData.id+"/id/"+item.id,
                            masterCostData: self.masterCostData,
                            projectParticular: item,
                            selectedItemIds: resp.selected_ids
                        });

                        selectComponentsDialog.show();
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
    });
});