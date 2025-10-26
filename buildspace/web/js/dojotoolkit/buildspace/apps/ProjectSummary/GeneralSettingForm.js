define('buildspace/apps/ProjectSummary/GeneralSettingForm',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/on",
    "dojo/dom",
    "dojo/dom-style",
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/ValidationTextBox",
    "dijit/form/CheckBox",
    "dojo/text!./templates/GeneralSettingForm.html",
    "dojo/currency",
    "dojo/i18n!buildspace/nls/ProjectSummary"
], function(declare, html, on, dom, domStyle, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ValidationTextBox, CheckBox, template, currency, nls){

    var GeneralSettingForm = declare('buildspace.apps.ProjectSummary._GeneralSettingForm', [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin], {
        templateString: template,
        project: null,
        region: 'center',
        style: "outline:none;padding:0px;margin:0px;border:none;overflow:auto;",
        baseClass: "buildspace-form",
        nls: nls,
        formValues: [],
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formValues);
        },
        postCreate: function(){
            this.inherited(arguments);

            var includeTax = this.formValues['project_summary_general_setting[include_tax]'];

            var displayVal = (includeTax) ? "" : "none";
            this.toggleTaxOptionsVisibility(displayVal);

            var self = this;

            on(this.includeTaxNode, "change", function(e){
                var displayVal = (this.checked) ? "" : "none";
                self.toggleTaxOptionsVisibility(displayVal);
            });

            if(this.project.status_id != buildspace.apps.ProjectSummary.ProjectStatus.STATUS_PRETENDER){
                this.projectTitleNode.set('disabled', true);
                this.additionalDescriptionNode.set('disabled', true);
                this.summaryTitleNode.set('disabled', true);
                this.includeAdditionalDescription.set('disabled', true);
                this.includePrintingDateNode.set('disabled', true);
                this.includeStateAndCountryNode.set('disabled', true);
                this.carriedToNextPageNode.set('disabled', true);
                this.continuedFromPreviousPageNode.set('disabled', true);
                this.pageNumberPrefixNode.set('disabled', true);
                this.includeTaxNode.set('disabled', true);
                this.taxNameNode.set('disabled', true);
                this.taxPercentageNode.set('disabled', true);
            }
        },
        submit: function(){
            var values = dojo.formToObject(this.id),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.saving+'. '+nls.pleaseWait+'...'
                });

            values.pid = this.project.id;

            if(this.validate()){

                pb.show();

                dojo.xhrPost({
                    url: 'projectSummary/generalSettingUpdate',
                    content: values,
                    handleAs: 'json',
                    load: function(resp) {
                        dojo.query('[id^="error-project_summary_general_setting_"]').forEach(function(node){
                            node.innerHTML = '';
                        });

                        if(!resp.success){
                            for(var key in resp.errors){
                                var msg = resp.errors[key];
                                html.set(dom.byId("error-project_summary_general_setting_"+key), msg);
                            }
                        }

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            }
        },
        toggleTaxOptionsVisibility: function(displayVal) {
            domStyle.set(this.lblTaxName, "display", displayVal);
            domStyle.set(this.txtTaxName, "display", displayVal);
            domStyle.set(this.lblTaxPercentage, "display", displayVal);
            domStyle.set(this.txtTaxPercentage, "display", displayVal);
        },
    });

    return declare('buildspace.apps.ProjectSummary.GeneralSettingForm', dijit.layout.BorderContainer, {
        project: null,
        region: 'center',
        style: "outline:none;padding:0px;margin:0px;border:none;",
        formValues: {},
        constructor: function(args){
            if(args.project.status_id != buildspace.apps.ProjectSummary.ProjectStatus.STATUS_PRETENDER){
                this.style = "outline:none;padding:10px;margin:0px;border:none;";
            }

            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);

            var form = GeneralSettingForm({
                project: this.project,
                formValues: this.formValues
            });

            if(this.project.status_id == buildspace.apps.ProjectSummary.ProjectStatus.STATUS_PRETENDER){

                var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border-left:0px;border-right:0px;border-top:0px;padding:2px;overflow:hidden;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.save,
                        iconClass: "icon-16-container icon-16-save",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(form, 'submit')
                    })
                );

                this.addChild(toolbar);
            }

            this.addChild(form);
        }
    });
});