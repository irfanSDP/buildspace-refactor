define('buildspace/apps/ProjectBuilder/ProjectProperties',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/dom",
    "dijit/form/Form",
    "dijit/form/ValidationTextBox",
    "dijit/form/Textarea",
    "dijit/form/DateTextBox",
    "dijit/form/Select",
    "dijit/form/FilteringSelect",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    'buildspace/apps/AssignUser/assignGroupProjectGrid',
    "dojo/text!./templates/projectProperties.html",
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function(declare, html, dom, Form, ValidateTextBox, Textarea, DateTextBox, Select, FilteringSelect, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, AssignGroupProjectGrid, template, nls){

    var MainInfoForm = declare("buildspace.apps.ProjectBuilder.MainInfoFormWidget", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        style: "border:none;padding:5px;overflow:auto;",
        rootProject: null,
        nls: nls,
        formData: null,
        postCreate: function(){
            this.inherited(arguments);
            var self  = this;

            var currencySelectStore = new dojo.data.ItemFileReadStore({
                url:"projectBuilder/getCurrency"
            });

            var countrySelectStore = new dojo.data.ItemFileReadStore({
                url:"projectBuilder/getCountry"
            });

            var countryId = (this.formData.formValues['project_main_information[region_id]']) ? this.formData.formValues['project_main_information[region_id]'] : 0;

            this.stateSelectStore = stateSelectStore = new dojo.data.ItemFileReadStore({
                url:"projectBuilder/getStateByCountry/regionId/"+countryId
            });

            this.countrySelect = new FilteringSelect({
                name: "project_main_information[region_id]",
                searchAttr: "name",
                style: "padding:2px;",
                store: countrySelectStore,
                onChange: function(country){
                    if(country){
                        self.stateSelect.set('readOnly' , false);
                    }else{
                        self.stateSelect.set('readOnly' , true);
                    }

                    self.updateStateSelectStore(country);
                }
            }).placeAt(this.countrySelectDivNode);

            this.stateSelect = new FilteringSelect({
                name: "project_main_information[subregion_id]",
                store: stateSelectStore,
                style: "padding:2px;",
                searchAttr: "name"
            }).placeAt(this.stateSelectDivNode);

            this.currencySelect = new FilteringSelect({
                name: "project_main_information[currency_id]",
                store: currencySelectStore,
                style: "padding:2px;",
                searchAttr: "name"
            }).placeAt(this.currencySelectDivNode);
        },
        disableForm: function(){
            this.inputTitle.set('disabled', true);
            this.countrySelect.set('disabled', true);
            this.stateSelect.set('disabled', true);
            this.currencySelect.set('disabled', true);
            this.inputSiteAdress.set('disabled', true);
            this.inputDescription.set('disabled', true);
            this.inputDate.set('disabled', true);
            this.inputClient.set('disabled', true);
            this.workCategories.set('disabled', true);

            var saveBtn = dijit.byId('mainFormSaveButton' + this.rootProject.id);

            saveBtn.set('disabled', true);
        },
        refreshStateSelectStore: function(){
            this.stateSelect.store.close();
            this.stateSelect.set('store', this.stateSelectStore);
            this.stateSelect.set('value', '');
        },
        updateStateSelectStore: function(country){
            var countryId = (country) ? country : 0,
                self = this;

            dojo.xhrPost({
                url: 'projectBuilder/getCurrencyValueByCountry',
                content: {regionId : countryId},
                handleAs: 'json',
                load: function(resp) {
                    self.stateSelectStore = new dojo.data.ItemFileReadStore({
                        url:"projectBuilder/getStateByCountry/regionId/"+countryId,
                        clearOnClose: true
                    });

                    self.currencySelect.set('value', resp.id);

                    self.refreshStateSelectStore();
                },
                error: function(error) {
                }
            });
        },
        startup: function(){
            this.inherited(arguments);
            this.mainInfoForm.setFormValues(this.formData.formValues);

            this.workCategories = new Select({
                name: "project_main_information[work_category_id]",
                style: "width:180px;padding:2px!important;",
                options: this.formData.workCategoryOptions
            }, "project_main_information_work_category_id").set('readOnly', true);

            if(this.rootProject.status_id != buildspace.constants.STATUS_PRETENDER){
                this.disableForm();
            }
        },
        save: function(){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'...'
            });
            var self = this,
                values = dojo.formToObject(this.mainInfoForm.id);

            if(this.mainInfoForm.validate()){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'projectBuilder/mainInfoUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="error-project_main_information_"]').forEach(function(node){
                                node.innerHTML = '';
                            });
                            if(resp.success){
                                var win = dijit.byId('buildspace-main_window');
                                win.titleNode.innerHTML = buildspace.truncateString(resp.title, 100) + ' (' + nls.status + '::' + self.rootProject.status[0].toUpperCase() + ')';
                                self.rootProject.title[0] = resp.title;

                                buildspace.currencyAbbreviation = buildspace.billCurrencyAbbreviation = resp.currency;
                            }else{
                                for(var key in resp.errors){
                                    var msg = resp.errors[key];
                                    html.set(dom.byId("error-project_main_information_"+key), msg);
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
        },
        assignUserPermission: function() {
            var assignGroupProjectGrid = new AssignGroupProjectGrid( {
                rootProject: this.rootProject,
                sysName: 'ProjectBuilder',
                projectStatus: buildspace.constants.USER_PERMISSION_STATUS_PROJECT_BUILDER
            } );
            assignGroupProjectGrid.show();
            assignGroupProjectGrid.selectGroup();
        },
        close: function(){

        },
        onCancel: function(){

        }
    });

    return declare('buildspace.apps.ProjectBuilder.ProjectProperties', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        data: null,
        postCreate: function(){
            this.inherited(arguments);

            var projectMainInfoForm = this.projectMainInfoForm = new MainInfoForm({
                rootProject: this.rootProject,
                formData: this.data
            });

            var toolbar = new dijit.Toolbar({region: "top", style:"border-top:none;border-left:none;border-right:none;padding:2px;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style:"outline:none!important;",
                    id: 'mainFormSaveButton' + this.rootProject.id,
                    onClick: dojo.hitch(projectMainInfoForm, 'save')
                })
            );

            if(this.rootProject.is_admin[0]){
                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.assignUsersToProject,
                        iconClass: "icon-16-container icon-16-add",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(projectMainInfoForm, 'assignUserPermission')
                    })
                );
            }

            this.addChild(toolbar);
            this.addChild(projectMainInfoForm);
        }
    });
});