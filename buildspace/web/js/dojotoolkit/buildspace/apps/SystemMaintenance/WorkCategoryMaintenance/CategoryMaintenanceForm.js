define(['dojo/_base/declare',
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/Form",
    "dijit/form/Button",
    "dijit/form/ValidationTextBox",
    "dijit/Toolbar",
    "dojo/text!./templates/WorkCategory.html",
    "buildspace/widget/grid/cells/Formatter",
    "./CategoryGrid",
    'dojo/i18n!buildspace/nls/WorkCategoryMaintenance',
    "dojo/html"
],
function(declare, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, Form, Button, ValidateTextBox, Toolbar, CategoryFormTemplate, GridFormatter, WorkCategoryMaintenanceGrid, nls, html){

    var WorkCategoryForm = declare('buildspace.apps.SystemMaintenance.WorkCategoryForm',[Form, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, dojox.form.manager._Mixin, dojox.form.manager._NodeMixin, dojox.form.manager._ValueMixin, dojox.form.manager._DisplayMixin], {
        baseClass: "buildspace-form",
        formValues: [],
        nls: nls,
        templateString: CategoryFormTemplate,
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formValues);
        },
        submit: function(){
            var self = this;

            this.WorkCategoryMaintenanceGrid.grid.store.save();

            var values = dojo.formToObject(this.id),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });

            if(this.validate()){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'systemMaintenance/workCategoryUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp){
                            dojo.query('[id^="work_category_error-"]').forEach(function(node){
                                node.innerHTML = '';
                            });

                            if(resp.success == true && self.WorkCategoryMaintenanceGrid){
                                self.setFormValues(resp.values);
                                self.WorkCategoryMaintenanceGrid.refreshGrid();

                                var container = dijit.byId('WorkCategoryMaintenance-stackContainer'), form = dijit.byId('WorkCategoryForm');

                                container.removeChild(form);
                                form.destroyRecursive();
                                self.WorkCategoryMaintenanceGrid.refreshGrid();
                            }else{
                                var errors = resp.errors;
                                for(var error in errors) {
                                    if(self['work_category_error-'+error]) {
                                        html.set(self['work_category_error-'+error], errors[error]);
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

    return declare('buildspace.apps.WorkCategoryMaintenance.WorkCategoryMaintenanceFormContainer', dijit.layout.BorderContainer, {
        style:"padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        resource: null,
        postCreate: function(){
            this.inherited(arguments);
            var formatter = new GridFormatter(),
                stackContainer = dijit.byId('WorkCategoryMaintenance-stackContainer');

            if(stackContainer){
                dijit.byId('WorkCategoryMaintenance-stackContainer').destroyRecursive();
            }

            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;',
                region: "center",
                id: 'WorkCategoryMaintenance-stackContainer'
            });

            var grid = this.workCategoryMaintenanceGrid = new WorkCategoryMaintenanceGrid({
                gridOpts: {
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose: true,
                        url: "systemMaintenance/getWorkCategories"
                    }),
                    CategoryFormContainer: this,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.name, field: 'name', editable: false, width:'280px'},
                        {name: nls.description, field: 'description', editable: false, width:'auto'},
                        {name: nls.updatedAt, field: 'updated_at', styles:'text-align:center;', width:'120px'}
                    ],
                    onRowDblClick: dojo.hitch(this, "editCategory")
                }
            });

            var stackPane = new dijit.layout.ContentPane({
                title: nls.workCategoryList,
                content: grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'WorkCategoryMaintenance-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding: 0px; overflow: hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            this.addChild(stackContainer);
            this.addChild(controllerPane);

            dojo.subscribe('WorkCategoryMaintenance-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('WorkCategoryMaintenance-stackContainer');
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
            var stackContainer = dijit.byId('WorkCategoryMaintenance-stackContainer');
            var pane = new dijit.layout.ContentPane({
                title: name,
                id:"WorkCategoryForm",
                content: content
            });

            stackContainer.addChild(pane);
            stackContainer.selectChild(pane);
        },
        CategoryFormContainer: function(formValues, isNew){
            var form = this.workCategoryForm = new WorkCategoryForm({
                    region: "center",
                    formValues: formValues,
                    WorkCategoryMaintenanceGrid: this.workCategoryMaintenanceGrid
                }),
                toolbar = new Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;"
                });

            toolbar.addChild(new Button({
                label: nls.save,
                iconClass: 'icon-16-container icon-16-save',
                style: 'outline:none!important;',
                onClick: dojo.hitch(this.workCategoryForm, "submit")
            }));

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                baseClass: 'form',
                gutters: false,
                region: "center"
            });

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            var title = isNew ? nls.addWorkCategory : nls.editWorkCategory;

            this.makePane(title, borderContainer);
        },
        addCategory: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait + '...'
                });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "systemMaintenance/workCategoryForm",
                    handleAs: "json"
                }).then(function(formValues){
                    pb.hide();
                    self.CategoryFormContainer(formValues, true);
                });
            });
        },
        editCategory: function(){
            if(this.workCategoryMaintenanceGrid.grid.selection.selectedIndex > -1){
                var self = this,
                    _item = this.workCategoryMaintenanceGrid.grid.getItem(this.workCategoryMaintenanceGrid.grid.selection.selectedIndex),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.pleaseWait+'...'
                    });

                if(_item && !isNaN(parseInt(_item.id[0]))){
                    pb.show().then(function(){
                        dojo.xhrGet({
                            url: "systemMaintenance/workCategoryForm/id/"+_item.id,
                            handleAs: "json"
                        }).then(function(formValues){
                            pb.hide();
                            self.CategoryFormContainer(formValues, false);
                        });
                    });
                }
            }
        },
        deleteCategory: function(){
            if(this.workCategoryMaintenanceGrid.grid.selection.selectedIndex > -1) {
                var _this = this,
                    _item = _this.workCategoryMaintenanceGrid.grid.getItem(this.workCategoryMaintenanceGrid.grid.selection.selectedIndex),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.deleting+'. '+nls.pleaseWait+'...'
                    });

                if(_item && !isNaN(parseInt(_item.id[0]))){
                    if(_item.can_be_deleted[0]){
                        buildspace.dialog.confirm(nls.confirmationCategory, '<div>'+nls.firstLevelCategoryDialogMsg+'</div>', 75, 320, function() {
                            pb.show().then(function(){
                                dojo.xhrPost({
                                    url: 'systemMaintenance/workCategoryDelete',
                                    content: {id: _item.id, _csrf_token: _item._csrf_token},
                                    handleAs: 'json',
                                    load: function(resp){
                                        if(resp.success){
                                            _this.workCategoryMaintenanceGrid.grid.store.deleteItem(_item);
                                            _this.workCategoryMaintenanceGrid.grid.store.save();
                                            _this.workCategoryMaintenanceGrid.refreshGrid();
                                        }
                                        _this.workCategoryMaintenanceGrid.grid.disableToolbarButtons(true);
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