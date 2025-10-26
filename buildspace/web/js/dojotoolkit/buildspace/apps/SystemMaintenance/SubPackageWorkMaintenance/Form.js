define(['dojo/_base/declare',
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/Form",
    "dijit/form/Button",
    "dijit/form/ValidationTextBox",
    "dijit/Toolbar",
    "dojo/text!./templates/Form.html",
    "buildspace/widget/grid/cells/Formatter",
    "./Grid",
    'dojo/i18n!buildspace/nls/SubPackageWorkMaintenance',
    "dojo/html"
    ],
    function(declare, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, Form, Button, ValidateTextBox, Toolbar, CategoryFormTemplate, GridFormatter, MaintenanceGrid, nls, html){

    var RecordForm = declare('buildspace.apps.SystemMaintenance.SubPackageWorkForm',[Form, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, dojox.form.manager._Mixin, dojox.form.manager._NodeMixin, dojox.form.manager._ValueMixin, dojox.form.manager._DisplayMixin], {
        baseClass: "buildspace-form",
        formValues: [],
        nls: nls,
        templateString: CategoryFormTemplate,
        param: null,
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formValues);
        },
        submit: function(){
            var self = this;

            this.MaintenanceGrid.grid.store.save();

            var values = dojo.formToObject(this.id),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });

            values['sub_package_works[type]'] = self.param;

            if(this.validate()){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'systemMaintenance/subPackageWorkUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp){
                            dojo.query('[id^="sub_package_works_error-"]').forEach(function(node){
                                node.innerHTML = '';
                            });

                            if(resp.success == true && self.MaintenanceGrid){
                                self.setFormValues(resp.values);
                                self.MaintenanceGrid.refreshGrid();

                                var container = dijit.byId('SubPackageWorkMaintenance_'+self.param+'-stackContainer'), form = dijit.byId('SubPackageWorkForm');

                                container.removeChild(form);
                                form.destroyRecursive();
                                self.MaintenanceGrid.refreshGrid();
                            }else{
                                var errors = resp.errors;
                                for(var error in errors) {
                                    if(self['sub_package_works_error-'+error]) {
                                        html.set(self['sub_package_works_error-'+error], errors[error]);
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

    return declare('buildspace.apps.SubPackageWorkMaintenance.SubPackageWorkMaintenanceFormContainer', dijit.layout.BorderContainer, {
        style:"padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        resource: null,
        param: {},
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            var formatter = new GridFormatter(),
                stackContainer = dijit.byId('SubPackageWorkMaintenance_'+self.param+'-stackContainer');

            if(stackContainer){
                dijit.byId('SubPackageWorkMaintenance_'+self.param+'-stackContainer').destroyRecursive();
            }

            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;',
                region: "center",
                id: 'SubPackageWorkMaintenance_'+self.param+'-stackContainer'
            });

            var grid = this.maintenanceGrid = new MaintenanceGrid({
                gridOpts: {
                    param: self.param,
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose: true,
                        url: "systemMaintenance/getSubPackageWorks/type/"+self.param
                    }),
                    FormContainer: this,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.name, field: 'name', editable: false, width:'auto'}
                    ],
                    onRowDblClick: dojo.hitch(this, "editRecord")
                }
            });

            var stackPane = new dijit.layout.ContentPane({
                title: nls.subPackageWorkList,
                content: grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'SubPackageWorkMaintenance_'+self.param+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding: 0px; overflow: hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            this.addChild(stackContainer);
            this.addChild(controllerPane);

            dojo.subscribe('SubPackageWorkMaintenance_'+self.param+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('SubPackageWorkMaintenance_'+self.param+'-stackContainer');
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
            var self = this;
            var stackContainer = dijit.byId('SubPackageWorkMaintenance_'+self.param+'-stackContainer');
            var pane = new dijit.layout.ContentPane({
                title: name,
                id:"SubPackageWorkForm",
                content: content
            });

            stackContainer.addChild(pane);
            stackContainer.selectChild(pane);
        },
        FormContainer: function(formValues, isNew){
            var self = this;
            var form = this.RecordForm = new RecordForm({
                region: "center",
                formValues: formValues,
                MaintenanceGrid: this.maintenanceGrid,
                param: self.param
            }),
            toolbar = new Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(new Button({
                label: nls.save,
                iconClass: 'icon-16-container icon-16-save',
                style: 'outline:none!important;',
                onClick: dojo.hitch(this.RecordForm, "submit")
            }));

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                baseClass: 'form',
                gutters: false,
                region: "center"
            });

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            var title = isNew ? nls.addSubPackageWork : nls.editSubPackageWork;
        
            this.makePane(title, borderContainer);
        },
        addRecord: function(){
            var self = this,
            pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "systemMaintenance/subPackageWorkForm",
                    handleAs: "json"
                }).then(function(formValues){
                    pb.hide();
                    self.FormContainer(formValues, true);
                });
            });
        },
        editRecord: function(){
            if(this.maintenanceGrid.grid.selection.selectedIndex > -1){
                var self = this,
                    _item = this.maintenanceGrid.grid.getItem(this.maintenanceGrid.grid.selection.selectedIndex),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.pleaseWait+'...'
                    });

                if(_item && !isNaN(parseInt(_item.id[0]))){
                    pb.show().then(function(){
                        dojo.xhrGet({
                            url: "systemMaintenance/subPackageWorkForm/id/"+_item.id,
                            handleAs: "json"
                        }).then(function(formValues){
                            pb.hide();
                            self.FormContainer(formValues, false);
                        });
                    });
                }
            }
        },
        deleteRecord: function(){
            if(this.maintenanceGrid.grid.selection.selectedIndex > -1) {
                var _this = this,
                    _item = _this.maintenanceGrid.grid.getItem(this.maintenanceGrid.grid.selection.selectedIndex),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.deleting+'. '+nls.pleaseWait+'...'
                    });

                if(_item && !isNaN(parseInt(_item.id[0]))){
                    if(_item.can_be_deleted[0]){
                        buildspace.dialog.confirm(nls.deleteConfirmation, '<div>'+nls.firstLevelCategoryDialogMsg+'</div>', 75, 320, function() {
                            pb.show().then(function(){
                                dojo.xhrPost({
                                    url: 'systemMaintenance/subPackageWorkDelete',
                                    content: {id: _item.id, _csrf_token: _item._csrf_token},
                                    handleAs: 'json',
                                    load: function(resp){
                                        if(resp.success){
                                            _this.maintenanceGrid.grid.store.deleteItem(_item);
                                            _this.maintenanceGrid.grid.store.save();
                                            _this.maintenanceGrid.refreshGrid();
                                        }
                                        _this.maintenanceGrid.grid.disableToolbarButtons(true);
                                        pb.hide();
                                    },
                                    error: function(error){
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