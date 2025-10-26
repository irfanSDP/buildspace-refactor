define('buildspace/apps/ProjectSummary/TableFooterForm',[
    'dojo/_base/declare',
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/TextBox",
    "dojo/text!./templates/TableFooterForm.html",
    "dojo/currency",
    "dojo/i18n!buildspace/nls/ProjectSummary"
], function(declare, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ValidateTextBox, template, currency, nls){

    return declare('buildspace.apps.ProjectSummary.TableFooterForm', [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin], {
        templateString: template,
        project: null,
        region: 'bottom',
        style: "outline:none;padding-top:0;padding-left:0;padding-right:0;padding-bottom:10px;margin:0px;",
        baseClass: "buildspace-form",
        nls: nls,
        formValues: [],
        totalCost: 0,
        postCreate: function(){
            this.inherited(arguments);

            var firstRowText = this.formValues['project_summary_footer[first_row_text]'] ? this.formValues['project_summary_footer[first_row_text]'] : "",
                secondRowText = this.formValues['project_summary_footer[second_row_text]'] ? this.formValues['project_summary_footer[second_row_text]'] : "";

            this.firstRowTextNode.set("value", firstRowText);
            this.secondRowTextNode.set("value", secondRowText);

            if(this.project.status_id != buildspace.apps.ProjectSummary.ProjectStatus.STATUS_PRETENDER){
                this.firstRowTextNode.set('disabled', true);
                this.secondRowTextNode.set('disabled', true);
            }
        },
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formValues);
            this.totalCostNode.innerHTML = currency.format(this.totalCost);
        },
        submit: function(){
            var values = dojo.formToObject(this.id),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.saving+'. '+nls.pleaseWait+'...'
                });

            if(!values.hasOwnProperty('project_summary_footer[first_row_text]')){
                values['project_summary_footer[first_row_text]'] = this.firstRowTextNode.get("value");
            }

            if(!values.hasOwnProperty('project_summary_footer[second_row_text]')){
                values['project_summary_footer[second_row_text]'] = this.secondRowTextNode.get("value");
            }

            values.pid = this.project.id;

            if(this.validate()){

                pb.show();

                dojo.xhrPost({
                    url: 'projectSummary/tableFooterUpdate',
                    content: values,
                    handleAs: 'json',
                    load: function(resp) {
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            }
        }
    });
});