define('buildspace/apps/SystemMaintenance/RegionMaintenance/grid',[
    'dojo/_base/declare',
    'dojo/aspect',
    'dijit/layout/BorderContainer',
    'dijit/layout/ContentPane',
    'dojox/grid/EnhancedGrid',
    'dijit/form/Form',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dijit/form/Button',
    'dijit/ToolbarSeparator',
    'dijit/Toolbar',
    'dijit/Menu',
    'dijit/MenuItem',
    'dojo/keys',
    'dojo/_base/event',
    'dojo/text!./templates/regionUpdateForm.html',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!../../../nls/SystemMaintenance',
    'dojo/html',
    'dojox/grid/enhanced/plugins/Rearrange',
    'buildspace/widget/grid/Filter'
],
function(
    declare,
    aspect,
    BorderContainer,
    ContentPane,
    EnhancedGrid,
    Form,
    _WidgetBase,
    _TemplatedMixin,
    _WidgetsInTemplateMixin,
    Button,
    ToolbarSeparator,
    Toolbar,
    Menu,
    MenuItem,
    keys,
    evt,
    regionUpdateFormTemplate,
    Formatter,
    nls,
    html,
    Rearrange,
    FilterToolbar)
{
    var RegionMaintenanceGrid = declare('buildspace.apps.SystemMaintenance.RegionMaintenance.Grid', EnhancedGrid, {
        rowSelector: false,
        style: 'border-top: none',
        keepSelection: true,
        region: 'center',
        store: null,
        regionMaintenanceGridContainer: null,
        canSort: function(){
            return false;
        },
        constructor: function(args){
            this.rearranger = Rearrange(this,{});
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on("RowContextMenu", function(e){
                self.selection.clear();
                var item = self.getItem(e.rowIndex);
                self.selection.setSelected(e.rowIndex, true);
                self.contextMenu(e);
                if(item && !isNaN(parseInt(item.id[0]))){
                    self.disableToolbarButtons(false);
                }else{
                    self.disableToolbarButtons(true);
                }
            }, true);

            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex);
                if(item && !isNaN(parseInt(item.id[0]))){
                    self.disableToolbarButtons(false);
                }else{
                    self.disableToolbarButtons(true);
                }
            });
        },
        disableToolbarButtons: function(isDisable){
            var editRowBtn = dijit.byId('editRegionRow-button');
            var deleteRowBtn = dijit.byId('deleteRegionRow-button');
            editRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        onRowDblClick: function(e){
            this.regionMaintenanceGridContainer.editRegion();
        },
        contextMenu: function(e){
            var rowCtxMenu = this.rowCtxMenu = new Menu();
            this.contextMenuItems(e);
            var info = {target: e.target, coords: e.keyCode !== keys.F10 && "pageX" in e ? {x: e.pageX, y: e.pageY } : null};
            var item = this.getItem(e.rowIndex);
            if(rowCtxMenu && item && (this.selection.isSelected(e.rowIndex) || e.rowNode && html.hasClass(e.rowNode, 'dojoxGridRowbar'))){
                rowCtxMenu._openMyself(info);
                evt.stop(e);
                return;
            }
        },
        contextMenuItems: function(e){
            var item = this.getItem(e.rowIndex);
            if(item && !isNaN(parseInt(item.id[0]))){
                this.rowCtxMenu.addChild(
                    new MenuItem({
                        label: nls.addRegion,
                        iconClass: 'icon-16-container icon-16-add',
                        onClick: dojo.hitch(this.regionMaintenanceGridContainer, 'addRegion')
                    })
                );
                var actions = ['edit', 'delete'];

                for(var action in actions){
                    var nlsName = actions[action]+'Region';
                    this.rowCtxMenu.addChild(
                        new MenuItem({
                            label: nls[nlsName],
                            iconClass: 'icon-16-container icon-16-'+actions[action],
                            onClick: dojo.hitch(this.regionMaintenanceGridContainer, nlsName)
                        })
                    );
                }
            }
        }
    });

    var RegionMaintenanceGridContainer = declare('buildspace.apps.RegionMaintenance.RegionMaintenanceFormContainer', BorderContainer, {
        style: 'padding: 0px; width: 100%; height: 100%',
        gutters: false,
        title: null,
        rowSelector: null,
        refreshGrid: function(){
            this.grid.store.close();
            this.grid._refresh();
        },
        postCreate: function(){
            var self = this,
                formatter = new Formatter(),
                regionGridLayout = [
                    { name: 'No.'            , field: 'id'           , width: '30px' , editable: false, styles: 'text-align: center;', formatter: formatter.rowCountCellFormatter},
                    { name: nls.country      , field: 'country'      , width: 'auto' , editable: false},
                    { name: nls.currency_code, field: 'currency_code', width: '90px' , editable: false},
                    { name: nls.currency_name, field: 'currency_name', width: '120px', editable: false}
                ],
                grid = this.grid = new RegionMaintenanceGrid({
                    structure: regionGridLayout,
                    store: new dojo.data.ItemFileWriteStore({
                        url: 'systemMaintenance/getRegions',
                        clearOnClose: true
                    }),
                    regionMaintenanceGridContainer: this
                }),
                toolbar = new Toolbar({
                    region: 'top',
                    style: 'outline:none!important;padding:2px;overflow:hidden!important;border:none;'
                });

            toolbar.addChild(
                new Button({
                    id: 'addRegionRow-button',
                    label: nls.addRegion,
                    iconClass: 'icon-16-container icon-16-add',
                    onClick: dojo.hitch(this, "addRegion")
                })
            );

            toolbar.addChild(new ToolbarSeparator());
            toolbar.addChild(
                new Button({
                    id: 'editRegionRow-button',
                    label: nls.editRegion,
                    iconClass: 'icon-16-container icon-16-edit',
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: dojo.hitch(this, "editRegion")
                })
            );

            toolbar.addChild(new ToolbarSeparator());
            toolbar.addChild(
                new Button({
                    id: 'deleteRegionRow-button',
                    label: nls.deleteRegion,
                    iconClass: 'icon-16-container icon-16-delete',
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: dojo.hitch(this, "deleteRegion")
                })
            );

            var gridContainer = new BorderContainer({
                    style: 'width: 100%; height: 100%',
                    gutters: false
                }),
                filterToolbar = this.filterToolbar = new FilterToolbar({
                    region: 'top',
                    grid: this.grid,
                    editableGrid: false,
                    filterFields: [{country : nls.country, currency_code: nls.currency_code, currency_name: nls.currency_name}]
                });
            gridContainer.addChild(filterToolbar);

            gridContainer.addChild(toolbar);
            gridContainer.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var stackContainer = dijit.byId('regionMaintenance-stackContainer');

            if(stackContainer){
                stackContainer.destroyRecursvie();
            }
            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                region: 'center',
                style: 'width: 100%; height: 100%',
                id: 'regionMaintenance-stackContainer'
            });

            var stackPane = new ContentPane({
                region: 'center',
                title: nls.regions,
                content: gridContainer,
                grid: grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                    region: 'top',
                    containerId: 'regionMaintenance-stackContainer'
                }),
                controllerPane = new ContentPane({
                    style: 'padding: 0px; overflow: hidden',
                    baseClass: 'breadCrumbTrail',
                    region: 'top',
                    content: controller
                });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            dojo.subscribe('regionMaintenance-stackContainer-selectChild',"", function(page){
                var widget = dijit.byId('regionMaintenance-stackContainer');
                if(widget){
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page.id));
                    index = index+1;
                    while(children.length >index){
                        widget.removeChild(children[index]);
                        children[index].destroyRecursive(true);
                        index = index+1;
                    }
                }

                if(page.hasOwnProperty("grid")){
                    page.grid.store.close();

                    var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                        handle.remove();
                        if(this.selection.selectedIndex && this.selection.selectedIndex > -1){
                            this.scrollToRow(this.selection.selectedIndex);
                        }
                    });

                    page.grid._refresh();
                }
            });
        },
        addRegion: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'systemMaintenance/regionForm',
                    handleAs: 'json'
                }).then(function(formValues){
                    pb.hide();
                    self.regionFormContainer(formValues, true);
                });
            });
        },
        editRegion: function(){
            if(this.grid.selection.selectedIndex > -1) {
                var self = this,
                    _item = this.grid.getItem(this.grid.selection.selectedIndex),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title: nls.pleaseWait+'...'
                    });

                if(_item && !isNaN(parseInt(_item.id[0]))){
                    pb.show().then(function(){
                        dojo.xhrGet({
                            url: 'systemMaintenance/regionForm',
                            handleAs: 'json',
                            content: {
                                id: _item.id
                            }
                        }).then(function(formValues){
                            pb.hide();
                            self.regionFormContainer(formValues, false);
                        });
                    });
                }
            }
        },
        deleteRegion: function(){
            if(this.grid.selection.selectedIndex > -1) {
                var self = this,
                    _item = this.grid.getItem(this.grid.selection.selectedIndex),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title: nls.deleting+'. '+nls.pleaseWait+'...'
                    });

                if(_item && !isNaN(parseInt(_item.id[0]))){
                    dojo.xhrGet({
                        url: 'systemMaintenance/regionPreDelete',
                        content: { id: _item.id },
                        handleAs: "json"
                    }).then(function(resp){
                        if(!resp.can_delete){
                            buildspace.dialog.alert(nls.itemCannotBeDeleted, nls.cannotDeleteUsedElsewhere, 75, 320);
                        } else {
                            buildspace.dialog.confirm(nls.confirmationRegion, '<div>'+nls.regionDeleteDialogMsg+'<div>', 75, 320, function(){
                                pb.show().then(function(){
                                    dojo.xhrPost({
                                        url: 'systemMaintenance/regionDelete',
                                        content: { id: _item.id, _csrf_token: _item._csrf_token },
                                        handleAs: 'json',
                                        load: function(resp){
                                            if(resp.success){
                                                //move to previous row after delete
                                                var newSelectedRow = self.grid.selection.selectedIndex-1;
                                                if(newSelectedRow<0){
                                                    newSelectedRow = 0;
                                                }

                                                self.grid.store.deleteItem(_item);
                                                self.grid.store.save();
                                                self.refreshGrid();

                                                var handle = aspect.after(self.grid, "_onFetchComplete", function() {
                                                    handle.remove();
                                                    self.grid.selection.setSelected(newSelectedRow, true);
                                                    self.grid.scrollToRow(newSelectedRow);
                                                });

                                                self.grid.disableToolbarButtons(true);
                                            }
                                            pb.hide();
                                        },
                                        error: function(){
                                            pb.hide();
                                        }
                                    });
                                });
                            });
                        }
                    });
                }
            }
        },
        makePane: function(name, content, id){
            var stackContainer = dijit.byId('regionMaintenance-stackContainer'),
                pane = new ContentPane({
                    title: name,
                    content: content,
                    region: 'center',
                    id: id
                });
            stackContainer.addChild(pane);
            stackContainer.selectChild(pane);
        },
        regionFormContainer: function(formValues, isNew){
            var form = this.regionUpdateForm = new RegionUpdateForm({
                    region: 'center',
                    formValues: formValues,
                    regionMaintenanceGrid: this.grid,
                    regionMaintenanceGridContainer: this
                }),
                toolbar = new Toolbar({
                    region: 'top',
                    style: 'outline:none!important;padding:2px;overflow:hidden;'
                });
            toolbar.addChild(
                new Button({
                    label: nls.save,
                    id: 'regionMaintenance-submitButton',
                    iconClass: 'icon-16-container icon-16-save',
                    style: 'outline:none!important; width:auto',
                    onClick: dojo.hitch(this.regionUpdateForm, 'submit')
                })
            );

            var borderContainer = new BorderContainer({
                style: 'padding: 0px; width: 100%, height: 100%',
                baseClass: 'form',
                gutters: false
            });
            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            var title = isNew ? nls.addRegion : nls.editRegion;
            this.makePane(title, borderContainer, 'regionUpdateFormContainer');
        }
    });

    var RegionUpdateForm = declare('buildspace.apps.SystemMaintenance.RegionMaintenance.RegionUpdateForm',
        [Form,
            _WidgetBase,
            _TemplatedMixin,
            _WidgetsInTemplateMixin,
            dojox.form.manager._Mixin,
            dojox.form.manager._NodeMixin,
            dojox.form.manager._ValueMixin,
            dojox.form.manager._DisplayMixin
        ], {
            baseClass: 'buildspace-form',
            nls: nls,
            formValues: [],
            regionMaintenanceGrid: null,
            regionMaintenanceGridContainer: null,
            templateString: regionUpdateFormTemplate,
            startup: function(){
                this.inherited(arguments);
                this.setFormValues(this.formValues);
                var self = this;
                dojo.query('[id^="region_input-"]').forEach(function(node){
                    node.onkeypress = function(event){
                        if (event.keyCode == 13){
                            self.submit();
                        }
                    }
                });
            },
            submit: function(){
                var self = this,
                    values = dojo.formToObject(this.id),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title: nls.savingData+'. '+nls.pleaseWait+'...'
                    });

                for(var value in values){
                    if (value == 'regions[iso]' || value == 'regions[iso3]' || value == 'regions[continent]'){
                        values[value] = values[value].toUpperCase();
                    }
                }

                var xhrArgs = {
                    url: 'systemMaintenance/regionUpdate',
                    handleAs: 'json',
                    content: values,
                    load: function(resp){
                        dojo.query('[id^="regions_error-"]').forEach(function(node){
                            node.innerHTML = '';
                        });
                        if(resp.success && self.regionMaintenanceGridContainer){

                            var beforeRefreshRowCount = self.regionMaintenanceGrid.rowCount;

                            self.removeForm();

                            self.regionMaintenanceGrid.store.close();
                            self.regionMaintenanceGridContainer.refreshGrid();

                            self.regionMaintenanceGrid.store.fetch({
                                onComplete: function(){

                                    self.regionMaintenanceGrid.setStore(self.regionMaintenanceGrid.store);
                                    self.regionMaintenanceGridContainer.refreshGrid();

                                    var handle = aspect.after(self.regionMaintenanceGrid, "_onFetchComplete", function() {
                                        handle.remove();
                                        if(this.rowCount<=beforeRefreshRowCount){
                                            // delete/update (NOT add)
                                            var indexToSelect = this.selection.selectedIndex;
                                            this.scrollToRow(this.selection.selectedIndex);

                                            for (var rowIdx in this.selection.selected){
                                                this.selection.setSelected(rowIdx, false);
                                            }
                                            this.selection.setSelected(indexToSelect, true);
                                        }
                                        else{
                                            // add
                                            // selects and scrolls to the newly added item (item with largest id)
                                            var arrayOfId = [];
                                            for(var itemId in this.store._itemsByIdentity){
                                                arrayOfId.push(itemId);
                                                this.selection.setSelected(this.store._itemsByIdentity[itemId]._0, false);
                                            }
                                            this.scrollToRow(this.store._itemsByIdentity[Math.max.apply(Math, arrayOfId)]._0);
                                            this.selection.setSelected(this.store._itemsByIdentity[Math.max.apply(Math, arrayOfId)]._0, true);
                                        }
                                    });
                                }
                            });
                        }
                        else{
                            var errors = resp.errors;
                            for(var error in errors){
                                html.set(self['regions_error-'+error], errors[error]);
                            }
                        }
                        pb.hide();
                    },
                    error: function(){
                        pb.hide();
                    }

                };
                if(this.validate()){
                    pb.show().then(function(){
                        dojo.xhrPost(xhrArgs);
                    });
                }
            },
            removeForm: function(){
                var regionUpdateFormContainer = dijit.byId('regionUpdateFormContainer');
                dijit.byId('regionMaintenance-stackContainer').removeChild(regionUpdateFormContainer);
                regionUpdateFormContainer.destroyRecursive();
            }
        });

    return RegionMaintenanceGridContainer;
});