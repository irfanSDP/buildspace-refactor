define(['dojo/_base/declare',
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/Form",
    "dijit/form/Button",
    "dijit/form/ValidationTextBox",
    "dijit/Toolbar",
    "dojo/text!./templates/retentionSumCodeForm.html",
    'dojo/i18n!buildspace/nls/SystemMaintenance',
    "dojo/html",
    "dojox/layout/TableContainer",
    'dojox/form/Manager'
], function(declare, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, Form, Button, ValidateTextBox, Toolbar, template, nls, html){

    var RetentionSumCodeForm = declare('buildspace.apps.RetentionSumCodeMaintenance.RetentionSumCodeForm',[Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin],{
        baseClass: "buildspace-form",
        nls: nls,
        templateString: template,
        postCreate: function(){
            this.inherited(arguments);

            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'systemMaintenance/retentionSumCodeForm',
                    handleAs: 'json'
                }).then(function(formValues){
                    self.setFormValues(formValues);
                    pb.hide();
                });
            });
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
                        url: 'systemMaintenance/retentionSumCodeUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="retention_sum_code_error-"]').forEach(function(node){
                                node.innerHTML = '';
                            });

                            if(resp.success == true){
                                self.setFormValues(resp.values);
                            }else{
                                var errors = resp.errors;
                                for(var error in errors){
                                    if(self['retention_sum_code_error-'+error]){
                                        html.set(self['retention_sum_code_error-'+error], errors[error]);
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

    return declare('buildspace.apps.RetentionSumCodeMaintenance.RetentionSumCodeMaintenanceFormContainer', dijit.layout.BorderContainer, {
        style:"padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        postCreate: function(){
            this.inherited(arguments);

            var formContainer = this.retentionSumCodeFormContainer();

            this.addChild(formContainer);
        },
        retentionSumCodeFormContainer: function(){
            var form = this.retentionSumCodeForm = new RetentionSumCodeForm({
                    region: "center"
                }),
                toolbar = new Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;"
                });

            toolbar.addChild(new Button({
                label: nls.save,
                iconClass: 'icon-16-container icon-16-save',
                style: 'outline:none!important;',
                onClick: dojo.hitch(this.retentionSumCodeForm, "submit")
            }));

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                baseClass: 'form',
                gutters: false,
                region: "center"
            });

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });
});