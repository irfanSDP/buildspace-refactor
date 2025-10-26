define(['dojo/_base/declare',
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/Form",
    "dijit/form/Button",
    "dijit/form/ValidationTextBox",
    "dijit/Toolbar",
    "dijit/form/FilteringSelect",
    "./grid",
    "dojo/text!./templates/unitOfMeasurementUpdateForm.html",
    'dojo/i18n!buildspace/nls/UnitOfMeasurementMaintenance',
    "dojo/html", "buildspace/widget/grid/cells/Formatter",
    "dojox/layout/TableContainer",
    'dojox/form/Manager'
], function(declare, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, Form, Button, ValidateTextBox, Toolbar, FilteringSelect, UnitOfMeasurementMaintenanceGrid, template, nls, html, GridFormatter){

    var UnitOfMeasurementDimensionGrid = declare('buildspace.apps.UnitOfMeasurementMaintenance.UnitOfMeasurementDimensionGrid', dojox.grid.EnhancedGrid, {
        style: "border-top:none;",
        rowSelector: '0px',
        dimensionFilterSelect: null,
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on('CellClick', function(e){
                var colField = e.cell.field,
                    item     = self.getItem(e.rowIndex);

                if ( (colField === 'up' || colField === 'down') && item && item.id > 0) {
                    self.updatePriority(colField, item);
                }

                if ( colField === 'delete' && item && item.id > 0) {
                    self.removeDimension(item);
                }
            });
        },
        updatePriority: function(direction, item){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'unitOfMeasurement/unitOfMeasurementDimensionPriorityUpdate',
                    content: {id: item.id, dir:direction, _csrf_token: item._csrf_token},
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            self.store.close();
                            self._refresh();
                            self.unitOfMeasurementMaintenanceGrid.refreshGrid();

                            self.store.fetchItemByIdentity({ 'identity' :item.id,  onItem : function(_item){
                                self.selection.clear();
                                self.selection.setSelected(_item._0, true);
                            }});
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        removeDimension: function(item){
            var self = this,
                dimensionFilterSelectStore = self.dimensionFilterSelect.store,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });

            if(item.can_be_deleted[0]){
                buildspace.dialog.confirm(nls.confirmationDimension, '<div>'+nls.firstLevelDimensionDialogMsg+'</div>', 60, 280, function() {
                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: 'unitOfMeasurement/unitOfMeasurementDimensionDelete',
                            content: {id: item.id, _csrf_token: item._csrf_token},
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success){
                                    self.store.close();
                                    self._refresh();

                                    self.dimensionFilterSelect.store.close();
                                    self.dimensionFilterSelect.set('store', dimensionFilterSelectStore);
                                    self.dimensionFilterSelect.set('value', '');

                                    self.unitOfMeasurementMaintenanceGrid.refreshGrid();
                                }
                                pb.hide();
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        });
                    });
                });
            }else{
                buildspace.dialog.alert(nls.itemCannotBeDeleted,nls.cannotRemoveDimension,60,300);
            }
        }
    });

    var UnitOfMeasurementUpdateForm = declare('buildspace.apps.UnitOfMeasurementMaintenance.UnitOfMeasurementUpdateForm',[Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin],{
        baseClass: "buildspace-form",
        nls: nls,
        formValues: [],
        uomType: 1,
        templateString: template,
        unitOfMeasurementMaintenanceGrid: null,
        postCreate: function(){
            this.inherited(arguments);

            var uomId = this.formValues.id,
                formatter = new GridFormatter(),
                borderContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;margin:0px;width:100%;height:240px;",
                    gutters: false
                }),
                addDimensionBtn = this.addDimensionBtn = new Button({
                    label: nls.addDimension,
                    iconClass: 'icon-16-container icon-16-add',
                    style: 'outline:none!important;',
                    disabled: uomId > 0 ? false : true,
                    onClick: dojo.hitch(this, 'addDimension')
                }),
                toolbar = new Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;border:none;background:#fff;"
                }),
                filterSelectStore = this.filterSelectStore = new dojo.data.ItemFileReadStore({
                    clearOnClose: true,
                    url: "unitOfMeasurement/getDimensionSelectList/uid/"+uomId
                }),
                store = new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: "unitOfMeasurement/getUnitOfMeasurementDimensions/id/"+uomId
                }),
                dimensionFilterSelect = this.dimensionFilterSelect = new dijit.form.FilteringSelect({
                    store: filterSelectStore,
                    searchAttr: 'name',
                    required: false,
                    style: "padding:2px;width:240px;"
                }),
                dimensionGrid = this.dimensionGrid = new UnitOfMeasurementDimensionGrid({
                    store: store,
                    region: 'center',
                    dimensionFilterSelect: dimensionFilterSelect,
                    unitOfMeasurementMaintenanceGrid: this.unitOfMeasurementMaintenanceGrid,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.name, field: 'name', width:'auto'},
                        {name: nls.priority, field: 'up', width:'60px', styles:'text-align:center;', formatter: formatter.moveUpCellFormatter},
                        {name: nls.priority, field: 'down', width:'60px', styles:'text-align:center;', formatter: formatter.moveDownCellFormatter},
                        {name: nls.action, field: 'delete', width:'80px', styles:'text-align:center;', formatter: formatter.removeCellFormatter }
                    ]
                });

            toolbar.addChild(dimensionFilterSelect);
            toolbar.addChild(addDimensionBtn);
            borderContainer.addChild(toolbar);
            borderContainer.addChild(dimensionGrid);

            borderContainer.placeAt(this['dimensionBCNode' + this.uomType]);
        },
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formValues);
        },
        addDimension: function(){
            var dimensionId = this.dimensionFilterSelect.value,
                formValues = dojo.formToObject(this.id),
                uomId = formValues.id,
                self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });

            if(dimensionId && dimensionId > 0){
                this.filterSelectStore.fetchItemByIdentity({ 'identity' :dimensionId,  onItem : function(dimension){
                    if(dimension){
                        pb.show().then(function(){
                            dojo.xhrPost({
                                url: 'unitOfMeasurement/unitOfMeasurementDimensionAdd',
                                content: {did: dimension.id, uid:uomId, _csrf_token: dimension._csrf_token},
                                handleAs: 'json',
                                load: function(resp) {
                                    if(resp.success){
                                        self.dimensionFilterSelect.store.close();
                                        self.dimensionFilterSelect.set('store', self.filterSelectStore);
                                        self.dimensionFilterSelect.set('value', '');

                                        self.dimensionGrid.store.close();
                                        self.dimensionGrid._refresh();

                                        self.unitOfMeasurementMaintenanceGrid.refreshGrid();
                                    }
                                    pb.hide();
                                },
                                error: function(error) {
                                    pb.hide();
                                }
                            });
                        });
                    }
                }});
            }
        },
        submit: function(){
            var self = this,
                values = dojo.formToObject(this.id),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });

            if(this.validate()){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'unitOfMeasurement/unitOfMeasurementUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="uom_'+self.uomType+'_error-"]').forEach(function(node){
                                node.innerHTML = '';
                            });

                            if(resp.success == true && self.unitOfMeasurementMaintenanceGrid){
                                self.setFormValues(resp.values);
                                self.addDimensionBtn._setDisabledAttr(false);

                                var filterSelectStore = self.filterSelectStore = new dojo.data.ItemFileReadStore({
                                    clearOnClose: true,
                                    url: "unitOfMeasurement/getDimensionSelectList/uid/"+resp.values.id
                                });

                                self.dimensionFilterSelect.store.close();
                                self.dimensionFilterSelect.set('store', filterSelectStore);
                                self.dimensionFilterSelect.set('value', '');

                                var store = new dojo.data.ItemFileWriteStore({
                                    clearOnClose: true,
                                    url: "unitOfMeasurement/getUnitOfMeasurementDimensions/id/"+resp.values.id
                                });

                                self.dimensionGrid.store.close();
                                self.dimensionGrid.set('store', store);
                                self.dimensionGrid._refresh();

                                self.unitOfMeasurementMaintenanceGrid.refreshGrid();
                            }else{
                                var errors = resp.errors;
                                for(var error in errors){
                                    if(self['uom_'+self.uomType+'_error-'+error]){
                                        html.set(self['uom_'+self.uomType+'_error-'+error], errors[error]);
                                    }
                                }
                            }
                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }
        }
    });

    return declare('buildspace.apps.UnitOfMeasurementMaintenance.UnitOfMeasurementMaintenanceFormContainer', dijit.layout.BorderContainer, {
        style:"padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        resource: null,
        param: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this,
                formatter = new GridFormatter(),
                stackContainer = dijit.byId('unitOfMeasurementMaintenance-stackContainer-'+self.param);

            if(stackContainer){
                dijit.byId('unitOfMeasurementMaintenance-stackContainer-'+self.param).destroyRecursive();
            }

            var stackContainer = self.stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;',
                region: "center",
                id: 'unitOfMeasurementMaintenance-stackContainer-'+self.param
            });

            var grid = this.unitOfMeasurementMaintenanceGrid = new UnitOfMeasurementMaintenanceGrid({
                param: self.param,
                gridOpts: {
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose: true,
                        url: "unitOfMeasurement/getUnitOfMeasurements/type/" + self.param
                    }),
                    unitOfMeasurementFormContainer: this,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.name, field: 'name', width:'auto'},
                        {name: nls.symbol, field: 'symbol', width:'80px', styles:'text-align:center;'},
                        {name: nls.dimensions, field: 'dimensions', width:'200px'},
                        {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align:center;' }
                    ],
                    onRowDblClick: function(e){
                        this.unitOfMeasurementFormContainer.editUnitOfMeasurement(e.rowIndex);
                    }
                }
            });

            var stackPane = new dijit.layout.ContentPane({
                title: nls.unitOfMeasurementList,
                content: grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'unitOfMeasurementMaintenance-stackContainer-'+self.param
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding: 0px; overflow: hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            dojo.subscribe('unitOfMeasurementMaintenance-stackContainer-'+self.param+'-selectChild',"",function(page){
                var widget = dijit.byId('unitOfMeasurementMaintenance-stackContainer-'+self.param);
                if(widget){
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page.id));
                    index = index+1;
                    while( children.length > index ){
                        widget.removeChild(children[ index ]);
                        children[ index ].destroyRecursive(true);
                        index = index + 1;
                    }
                }
            });
        },
        makePane: function(name, content){
            var stackContainer = dijit.byId('unitOfMeasurementMaintenance-stackContainer-'+this.param),
                pane = new dijit.layout.ContentPane({
                    title: name,
                    content: content
                });
            stackContainer.addChild(pane);
            stackContainer.selectChild(pane);
        },
        unitOfMeasurementFormContainer: function(formValues, isNew){
            var form = this.unitOfMeasurementUpdateForm = new UnitOfMeasurementUpdateForm({
                    region: "center",
                    formValues: formValues,
                    uomType: this.param,
                    unitOfMeasurementMaintenanceGrid: this.unitOfMeasurementMaintenanceGrid
                }),
                toolbar = new Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;"
                });

            toolbar.addChild(new Button({
                label: nls.save,
                iconClass: 'icon-16-container icon-16-save',
                style: 'outline:none!important;',
                onClick: dojo.hitch(this.unitOfMeasurementUpdateForm, "submit")
            }));

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                baseClass: 'form',
                gutters: false,
                region: "center"
            });

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            var title = isNew ? nls.addUnitOfMeasurement : nls.editUnitOfMeasurement;

            this.makePane(title, borderContainer);
        },
        addUnitOfMeasurement: function(){
            var self = this, type = this.param,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "unitOfMeasurement/unitOfMeasurementForm/t/"+type,
                    handleAs: "json"
                }).then(function(formValues){
                    pb.hide();
                    self.unitOfMeasurementFormContainer(formValues, true);
                });
            });
        },
        editUnitOfMeasurement: function(rowIndex){
            var self = this,
                _item = self.unitOfMeasurementMaintenanceGrid.grid.getItem(rowIndex);

            if ( _item.id == buildspace.constants.GRID_LAST_ROW ) {
                return false;
            }

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            if(_item){
                pb.show().then(function(){
                    dojo.xhrGet({
                        url: "unitOfMeasurement/unitOfMeasurementForm/id/"+_item.id,
                        handleAs: "json"
                    }).then(function(formValues){
                        pb.hide();
                        self.unitOfMeasurementFormContainer(formValues, false);
                    });
                });
            }
        },
        deleteUnitOfMeasurement: function(rowIndex){
            var _this = this,
                _item = _this.unitOfMeasurementMaintenanceGrid.grid.getItem(rowIndex),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });

            if(_item && !isNaN(parseInt(_item.id[0]))){
                return buildspace.dialog.confirm(nls.confirmationUnit, '<div>'+nls.firstLevelUnitDialogMsg+'</div>', 75, 320, function() {
                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: 'unitOfMeasurement/unitOfMeasurementDelete',
                            content: {id: _item.id, _csrf_token: _item._csrf_token},
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success){
                                    _this.unitOfMeasurementMaintenanceGrid.grid.store.deleteItem(_item);
                                    _this.unitOfMeasurementMaintenanceGrid.grid.store.save();
                                } else {
                                    buildspace.dialog.alert(nls.itemCannotBeDeleted,nls.cannotDeleteUom,60,300);
                                }

                                _this.unitOfMeasurementMaintenanceGrid.grid.disableToolbarButtons(true);
                                pb.hide();
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        });
                    });
                });
            }
        }
    });
});