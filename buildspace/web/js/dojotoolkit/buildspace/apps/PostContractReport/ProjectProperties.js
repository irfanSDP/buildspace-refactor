define('buildspace/apps/PostContractReport/ProjectProperties',[
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
    "dojo/text!./templates/projectProperties.html",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, html, dom, Form, ValidateTextBox, Textarea, DateTextBox, Select, FilteringSelect, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls){

    var MainInfoForm = declare("buildspace.apps.PostContractReport.MainInfoFormWidget", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        style: "border:none;padding:5px;overflow:auto;",
        rootProject: null,
        nls: nls,
        title: null,
        client: null,
        description: null,
        site_address: null,
        start_date: null,
        currency: null,
        country: null,
        state: null,
        work_category: null,
        eproject_reference: null,
        postCreate: function(){
            this.inherited(arguments);
            var self  = this;
        },
        startup: function(){
            this.inherited(arguments);
        },
        close: function(){

        },
        onCancel: function(){

        }
    });

    var ProjectProperties = declare('buildspace.apps.PostContractReport.ProjectProperties', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        data: null,
        postCreate: function(){
            this.inherited(arguments);

            var projectMainInfoForm = this.projectMainInfoForm = new MainInfoForm({
                rootProject: this.rootProject,
                title: this.data.title,
                description: this.data.description,
                client: this.data.client,
                site_address: this.data.site_address,
                start_date: this.data.start_date,
                currency: this.data.currency,
                country: this.data.region,
                state: this.data.subregion,
                eproject_reference: this.data.eProjectReference,
                work_category: this.data.work_category
            });

            this.addChild(projectMainInfoForm);
        }
    });

    return ProjectProperties;
});