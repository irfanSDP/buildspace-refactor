define(['dojo/_base/declare',
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    'dojo/_base/lang',
    "dijit/form/Form",
    "dijit/form/Button",
    "dijit/form/ValidationTextBox",
    "dijit/form/TextBox",
    "dijit/form/DateTextBox",
    "dijit/Toolbar",
    "dijit/form/FilteringSelect",
    "dijit/form/Select",
    "./grid",
    "dojo/text!./templates/businessTypeMaintenanceForm.html",
    'dojo/i18n!buildspace/nls/BusinessTypeMaintenance',
    "dojo/html", "dojo/dom", "dojo/on", "buildspace/widget/grid/cells/Formatter",
    "dojo/dom-construct", "dojo/dom-style", "dojo/domReady!",
    "dojox/layout/TableContainer",
    'dojox/form/Manager',
    "dijit/InlineEditBox",
    "buildspace/widget/forms/InlineEditBox"
], function(declare, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, lang, Form, Button, ValidateTextBox, TextBox, DateTextBox, Toolbar, FilteringSelect, Select, BusinessTypeMaintenanceGrid, template, nls, html, dom, on, GridFormatter, domConstruct, domStyle){

    var BusinessTypeUpdateForm = declare('buildspace.apps.BusinessTypeMaintenance.BusinessTypeUpdateForm',[Form, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, dojox.form.manager._Mixin, dojox.form.manager._NodeMixin, dojox.form.manager._ValueMixin, dojox.form.manager._DisplayMixin], {
        baseClass: "buildspace-form",
        formValues: [],
        nls: nls,
        isNew: true,
        templateString: template,
        businessTypeMaintenanceGrid: null,
        postCreate: function(){
            this.inherited(arguments);
        },
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formValues);
        },
        submit: function(){
            var self = this;

            this.businessTypeMaintenanceGrid.grid.store.save();

            var values = dojo.formToObject(this.id),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });

            if(this.validate()){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'systemAdministration/businessTypeUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp){
                            dojo.query('[id^="company_business_type_error-"]').forEach(function(node){
                                node.innerHTML = '';
                            });

                            if(resp.success == true && self.businessTypeMaintenanceGrid){
                                self.setFormValues(resp.values);
                                var container = dijit.byId('BusinessTypeMaintenance-stackContainer'),
                                    form = dijit.byId('BusinessTypeForm');

                                container.removeChild(form);
                                form.destroyRecursive();
                                self.businessTypeMaintenanceGrid.refreshGrid();
                            }else{
                                var errors = resp.errors;
                                for(var error in errors) {
                                    if(self['company_business_type_error-'+error]) {
                                        html.set(self['company_business_type_error-'+error], errors[error]);
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

    return declare('buildspace.apps.BusinessTypeMaintenance.BusinessTypeMaintenanceFormContainer', dijit.layout.BorderContainer, {
        style:"padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        postCreate: function(){
            this.inherited(arguments);

            var formatter = new GridFormatter();

            var stackContainer = dijit.byId('BusinessTypeMaintenance-stackContainer');

            if(stackContainer){
                dijit.byId('BusinessTypeMaintenance-stackContainer').destroyRecursive();
            }

            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;',
                region: "center",
                id: 'BusinessTypeMaintenance-stackContainer'
            });

            var grid = this.businessTypeMaintenanceGrid = new BusinessTypeMaintenanceGrid({
                gridOpts: {
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose: true,
                        url: "systemAdministration/getBusinessTypes"
                    }),
                    formContainer: this,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.name, field: 'name', width:'auto'},
                        {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align:center;' }
                    ],
                    onRowDblClick: dojo.hitch(this, "editBusinessType")
                }
            });

            var stackPane = new dijit.layout.ContentPane({
                title: nls.BusinessTypeList,
                content: grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'BusinessTypeMaintenance-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding: 0px; overflow: hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            this.addChild(stackContainer);
            this.addChild(controllerPane);

            dojo.subscribe('BusinessTypeMaintenance-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('BusinessTypeMaintenance-stackContainer');
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
            var stackContainer = dijit.byId('BusinessTypeMaintenance-stackContainer');
            var pane = new dijit.layout.ContentPane({
                title: name,
                id: "BusinessTypeForm",
                content: content
            });

            stackContainer.addChild(pane);
            stackContainer.selectChild(pane);
        },
        FormContainer: function(formValues, isNew){
            var form = this.businessTypeUpdateForm = new BusinessTypeUpdateForm({
                    region: "center",
                    formValues: formValues,
                    businessTypeMaintenanceGrid: this.businessTypeMaintenanceGrid
                }),
                toolbar = new Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;"
                });

            toolbar.addChild(new Button({
                label: nls.save,
                iconClass: 'icon-16-container icon-16-save',
                style: 'outline:none!important;',
                onClick: dojo.hitch(form, "submit")
            }));

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                baseClass: 'form',
                gutters: false,
                region: "center"
            });

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            var title = isNew ? nls.addBusinessType : nls.editBusinessType;

            this.makePane(title, borderContainer);
        },
        addBusinessType: function() {
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait + '...'
                });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'systemAdministration/getBusinessType',
                    handleAs: "json"
                }).then(function(formValues){
                    pb.hide();
                    self.FormContainer(formValues, true);
                });
            });
        },
        editBusinessType: function(){
            if(this.businessTypeMaintenanceGrid.grid.selection.selectedIndex > -1) {
                var self = this,
                    _item = this.businessTypeMaintenanceGrid.grid.getItem(this.businessTypeMaintenanceGrid.grid.selection.selectedIndex),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.pleaseWait+'...'
                    });

                if(_item && !isNaN(parseInt(_item.id[0]))) {
                    pb.show().then(function(){
                        dojo.xhrGet({
                            url: 'systemAdministration/getBusinessType/id/'+_item.id,
                            handleAs: "json"
                        }).then(function(formValues){
                            pb.hide();
                            self.FormContainer(formValues, false);
                        });
                    });
                }
            }
        },
        deleteBusinessType: function(rowIndex){
            if(this.businessTypeMaintenanceGrid.grid.selection.selectedIndex > -1) {
                var _this = this,
                    _item = _this.businessTypeMaintenanceGrid.grid.getItem(this.businessTypeMaintenanceGrid.grid.selection.selectedIndex),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.deleting+'. '+nls.pleaseWait+'...'
                    });

                if(_item && !isNaN(parseInt(_item.id[0]))){
                    dojo.xhrGet({
                        url: 'systemAdministration/businessTypePreDelete',
                        content: { id: _item.id },
                        handleAs: "json"
                    }).then(function(resp){

                        if(!resp.can_delete){
                            buildspace.dialog.alert(nls.itemCannotBeDeleted, nls.cannotDeleteUsedElsewhere, 75, 320);
                        } else {
                            buildspace.dialog.confirm(nls.confirmationBusinessType, '<div>'+nls.businessTypeDeleteDialogMsg+'<div>', 75, 320, function(){
                                pb.show().then(function(){
                                    dojo.xhrPost({
                                        url: 'systemAdministration/deleteBusinessType',
                                        content: { id: _item.id, _csrf_token: _item._csrf_token },
                                        handleAs: 'json',
                                        load: function(resp){
                                            if(resp.success){
                                                _this.businessTypeMaintenanceGrid.refreshGrid();
                                                _this.businessTypeMaintenanceGrid.grid.disableToolbarButtons(true);
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
        }
    });
});