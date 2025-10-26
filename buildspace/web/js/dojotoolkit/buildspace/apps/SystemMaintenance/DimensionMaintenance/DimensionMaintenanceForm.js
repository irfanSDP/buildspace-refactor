define(['dojo/_base/declare',
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/Form",
    "dijit/form/Button",
    "dijit/form/ValidationTextBox",
    "dijit/Toolbar",
    "dojo/text!./templates/dimensionUpdateForm.html",
    "buildspace/widget/grid/cells/Formatter",
    "./grid",
    'dojo/i18n!buildspace/nls/DimensionMaintenance',
    "dojo/html"
], function(declare, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, Form, Button, ValidateTextBox, Toolbar, dimensionUpdateFormTemplate, GridFormatter, DimensionMaintenanceGrid, nls, html){

    var DimensionUpdateForm = declare('buildspace.apps.DimensionMaintenance.DimensionUpdateForm',[Form,
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
        templateString: dimensionUpdateFormTemplate,
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formValues);
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
                        url: 'unitOfMeasurement/dimensionUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="dimension_error-"]').forEach(function(node){
                                node.innerHTML = '';
                            });

                            if(resp.success == true && self.dimensionMaintenanceGrid){
                                self.setFormValues(resp.values);

                                var container = dijit.byId('dimensionMaintenance-stackContainer'),
                                    form = dijit.byId('dimensionForm');

                                container.removeChild(form);
                                form.destroyRecursive();

                                self.dimensionMaintenanceGrid.refreshGrid();
                            }else{
                                var errors = resp.errors;
                                for(var error in errors){
                                    if(self['dimension_error-'+error]){
                                        html.set(self['dimension_error-'+error], errors[error]);
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

    return declare('buildspace.apps.DimensionMaintenance.DimensionMaintenanceFormContainer', dijit.layout.BorderContainer, {
        style:"padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        resource: null,
        postCreate: function(){
            this.inherited(arguments);
            var formatter = new GridFormatter(),
                stackContainer = dijit.byId('dimensionMaintenance-stackContainer');

            if(stackContainer){
                dijit.byId('dimensionMaintenance-stackContainer').destroyRecursive();
            }

            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;',
                region: "center",
                id: 'dimensionMaintenance-stackContainer'
            });

            var grid = this.dimensionMaintenanceGrid = new DimensionMaintenanceGrid({
                gridOpts: {
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose: true,
                        url: "unitOfMeasurement/getDimensions"
                    }),
                    dimensionFormContainer: this,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.name, field: 'name', editable: true, width:'auto'},
                        {name: nls.lastUpdated, field: 'updated_at', styles:'text-align:center;', width:'120px'}
                    ],
                    onRowDblClick: dojo.hitch(this, "editDimension")
                }
            });

            var stackPane = new dijit.layout.ContentPane({
                title: nls.dimensionList,
                content: grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'dimensionMaintenance-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding: 0px; overflow: hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            this.addChild(stackContainer);
            this.addChild(controllerPane);

            dojo.subscribe('dimensionMaintenance-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('dimensionMaintenance-stackContainer');
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
            var stackContainer = dijit.byId('dimensionMaintenance-stackContainer');
            var pane = new dijit.layout.ContentPane({
                title: name,
                id: "dimensionForm",
                content: content
            });

            stackContainer.addChild(pane);
            stackContainer.selectChild(pane);
        },
        dimensionFormContainer: function(formValues, isNew){
            var form = this.dimensionUpdateForm = new DimensionUpdateForm({
                    region: "center",
                    formValues: formValues,
                    dimensionMaintenanceGrid: this.dimensionMaintenanceGrid
                }),
                toolbar = new Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;"
                });

            toolbar.addChild(new Button({
                label: nls.save,
                iconClass: 'icon-16-container icon-16-save',
                style: 'outline:none!important;',
                onClick: dojo.hitch(this.dimensionUpdateForm, "submit")
            }));

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                baseClass: 'form',
                gutters: false,
                region: "center"
            });

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            var title = isNew ? nls.addDimension : nls.editDimension;

            this.makePane(title, borderContainer);
        },
        addDimension: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "unitOfMeasurement/dimensionForm",
                    handleAs: "json"
                }).then(function(formValues){
                    pb.hide();
                    self.dimensionFormContainer(formValues, true);
                });
            });
        },
        editDimension: function(){
            if(this.dimensionMaintenanceGrid.grid.selection.selectedIndex > -1) {
                var self = this,
                    _item = this.dimensionMaintenanceGrid.grid.getItem(this.dimensionMaintenanceGrid.grid.selection.selectedIndex),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.pleaseWait+'...'
                    });

                if(_item && !isNaN(parseInt(_item.id[0]))){
                    pb.show().then(function(){
                        dojo.xhrGet({
                            url: "unitOfMeasurement/dimensionForm/id/"+_item.id,
                            handleAs: "json"
                        }).then(function(formValues){
                            pb.hide();
                            self.dimensionFormContainer(formValues, false);
                        });
                    });
                }
            }
        },
        deleteDimension: function(){
            if(this.dimensionMaintenanceGrid.grid.selection.selectedIndex > -1) {
                var _this = this,
                    _item = _this.dimensionMaintenanceGrid.grid.getItem(this.dimensionMaintenanceGrid.grid.selection.selectedIndex),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.deleting+'. '+nls.pleaseWait+'...'
                    });

                if(_item && !isNaN(parseInt(_item.id[0]))){
                    if(_item.can_be_deleted[0]){
                        buildspace.dialog.confirm(nls.confirmationDimension, '<div>'+nls.firstLevelDimensionDialogMsg+'</div>', 75, 320, function() {
                            pb.show().then(function(){
                                dojo.xhrPost({
                                    url: 'unitOfMeasurement/dimensionDelete',
                                    content: {id: _item.id, _csrf_token: _item._csrf_token},
                                    handleAs: 'json',
                                    load: function(resp) {
                                        if(resp.success){
                                            _this.dimensionMaintenanceGrid.grid.store.deleteItem(_item);
                                            _this.dimensionMaintenanceGrid.grid.store.save();
                                        }
                                        _this.dimensionMaintenanceGrid.grid.disableToolbarButtons(true);
                                        pb.hide();
                                    },
                                    error: function(error) {
                                        pb.hide();
                                    }
                                });
                            });
                        });
                    }else{
                        buildspace.dialog.alert(nls.itemCannotBeDeleted,nls.cannotDeleteUseInUOM,75,320);
                    }
                }
            }
        }
    });
});