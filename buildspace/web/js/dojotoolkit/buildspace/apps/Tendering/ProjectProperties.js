define('buildspace/apps/Tendering/ProjectProperties',[
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
    'dojo/i18n!buildspace/nls/Tendering'
], function(declare, html, dom, Form, ValidateTextBox, Textarea, DateTextBox, Select, FilteringSelect, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, AssignGroupProjectGrid, template, nls){

    var MainInfoForm = declare("buildspace.apps.Tendering.MainInfoFormWidget", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        style: "border:none;padding:5px;overflow:auto;",
        rootProject: null,
        explorer: null,
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
        assignUserPermission: function() {
            var assignGroupProjectGrid = new AssignGroupProjectGrid( {
                rootProject: this.rootProject,
                sysName: 'Tendering',
                projectStatus: buildspace.constants.USER_PERMISSION_STATUS_TENDERING
            } );
            assignGroupProjectGrid.show();
            assignGroupProjectGrid.selectGroup();
        },
        close: function(){

        },
        onCancel: function(){

        }
    });

    return declare('buildspace.apps.Tendering.ProjectProperties', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        explorer: null,
        data: null,
        postCreate: function(){
            this.inherited(arguments);

            var projectMainInfoForm = this.projectMainInfoForm = new MainInfoForm({
                rootProject: this.rootProject,
                explorer: this.explorer,
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

            if(this.rootProject.is_admin[0]){
                var toolbar = new dijit.Toolbar({region: "top", style:"border-top:none;border-left:none;border-right:none;padding:2px;width:100%;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.assignUsersToProject,
                        iconClass: "icon-16-container icon-16-add",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(projectMainInfoForm, 'assignUserPermission')
                    })
                );

                this.addChild(toolbar);
            }

            this.addChild(toolbar);
            this.addChild(projectMainInfoForm);
        }
    });
});