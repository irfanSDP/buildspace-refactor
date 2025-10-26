define('buildspace/apps/PostContractSubPackageReport/ProjectProperties',[
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
    'dojo/i18n!buildspace/nls/PostContractSubPackage'
], function(declare, html, dom, Form, ValidateTextBox, Textarea, DateTextBox, Select, FilteringSelect, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls){

    var MainInfoForm = declare("buildspace.apps.PostContractSubPackageReport.MainInfoFormWidget", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        style: "border:none;padding:5px;overflow:auto;",
        rootProject: null,
        selected_company: null,
        sub_package_title: null,
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

    var ProjectProperties = declare('buildspace.apps.PostContractSubPackageReport.ProjectProperties', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        data: null,
        postCreate: function(){
            this.inherited(arguments);

            var projectMainInfoForm = this.projectMainInfoForm = new MainInfoForm({
                rootProject: this.rootProject,
                selected_company: this.data.selected_company,
                sub_package_title: this.data.sub_package_title,
                title: this.data.title,
                description: this.data.description,
                client: this.data.client,
                site_address: this.data.site_address,
                start_date: this.data.start_date,
                currency: this.data.currency,
                country: this.data.region,
                state: this.data.subregion,
                work_category: this.data.work_category
            });

            this.addChild(projectMainInfoForm);
        }
    });

    return ProjectProperties;
});