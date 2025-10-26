define('buildspace/apps/ProjectManagement/ProjectScheduleForm',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "./ImportProjectScheduleDialog",
    "dijit/form/Form",
    "dijit/form/FilteringSelect",
    "dijit/form/ValidationTextBox",
    "dijit/form/Textarea",
    "dijit/form/Select",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/projectScheduleForm.html",
    'dojo/i18n!buildspace/nls/ProjectManagement',
    "dojo/on"
], function(declare, lang, html, dom, keys, domStyle, ImportProjectScheduleDialog, Form, FilteringSelect, ValidateTextBox, Textarea, Select, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls, on){

    return declare("buildspace.apps.ProjectManagement.ProjectScheduleForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        nls: nls,
        dialogWidget: null,
        projectScheduleId: -1,
        project: null,
        subPackage: null,
        subPackageName: null,
        style: "padding:5px;overflow:auto;",
        constructor:function(args){
            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);
        },
        startup: function(){
            this.inherited(arguments);
            var self = this;

            //setting up form values before we start our form
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            pb.show().then(function(){
                var content = {id: self.projectScheduleId};

                if(self.project){
                    if(self.projectScheduleId == -1){
                        lang.mixin(content, {
                            pid: self.project.id
                        });
                    }
                }

                if(self.subPackage){
                    if(self.projectScheduleId == -1){
                        lang.mixin(content, {
                            sid: self.subPackage.id
                        });
                    }
                    domStyle.set(self.scheduleFormSubPackageName, 'display', '');
                }else{
                    domStyle.set(self.scheduleFormSubPackageName, 'display', 'none');
                }

                dojo.xhrGet({
                    url: 'projectManagement/projectScheduleForm',
                    handleAs: 'json',
                    content: content,
                    load: function(data){
                        self.projectScheduleForm.setFormValues(data.formValues);

                        new FilteringSelect({
                            name: "project_schedule[timezone]",
                            store: new dojo.data.ItemFileReadStore({
                                data: data.timezones
                            }),
                            style: "padding:2px;width:240px;",
                            required: true,
                            searchAttr: "name",
                            value: data.formValues["project_schedule[timezone]"]
                        }).placeAt(self.timezoneSelectDiv);

                        pb.hide();
                    },
                    error: function(error) {
                        //something is wrong somewhere
                        pb.hide();
                    }
                });
            });
        },
        save: function(){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'...'
            });

            var self = this,
                values = dojo.formToObject(this.projectScheduleForm.id);

            if(this.projectScheduleForm.validate()){

                pb.show().then(function(){
                    lang.mixin(values, {
                        id: self.projectScheduleId
                    });

                    if(self.subPackage && self.projectScheduleId == -1){
                        lang.mixin(values, {
                            sid: self.subPackage.id
                        });
                    }

                    dojo.xhrPost({
                        url: 'projectManagement/projectScheduleUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="error-project_schedule_"]').forEach(function(node){
                                node.innerHTML = '';
                            });
                            if(resp.success){
                                if(self.dialogWidget){
                                    self.dialogWidget.hide();
                                }

                                var scheduleListGrid = dijit.byId('projectManagement-projectScheduleListGrid');

                                scheduleListGrid.reload();

                            }else{
                                for(var key in resp.errors){
                                    html.set(dom.byId("error-project_schedule_"+key), resp.errors[key]);
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
        importProjectSchedule: function(){
            this.dialogWidget.hide();
            var dialog = new ImportProjectScheduleDialog({
                project: this.project,
                subPackage: this.subPackage
            });

            dialog.show();
        },
        close: function(){

        },
        onCancel: function(){

        }
    });
});