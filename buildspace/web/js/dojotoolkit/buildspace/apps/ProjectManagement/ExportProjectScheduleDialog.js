define('buildspace/apps/ProjectManagement/ExportProjectScheduleDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/ValidationTextBox",
    'dojo/i18n!buildspace/nls/ProjectManagement'
], function(declare, lang, connect, when, html, dom, keys, domStyle, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ValidationTextBox, nls){

    var ExportForm = declare('buildspace.apps.ProjectManagement.ExportProjectScheduleForm', [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin], {
        templateString: '<form data-dojo-attach-point="containerNode">' +
        '<table class="table-form">' +
        '<tr>' +
        '<td class="label" style="width:80px;"><label style="display: inline;"></span>'+nls.downloadAs+' :</label></td>' +
        '<td>' +
        '<input type="text" name="filename" style="padding:2px;width:220px;" data-dojo-type="dijit/form/ValidationTextBox" data-dojo-props="trim:true, propercase:true, maxlength:45, required: true">' +
        '<input type="hidden" name="id" value="">' +
        '</td>' +
        '</tr>' +
        '</table>' +
        '</form>',
        projectSchedule: null,
        exportUrl: null,
        region: 'center',
        dialogWidget: null,
        style: "outline:none;padding:0;margin:0;border:none;",
        baseClass: "buildspace-form",
        startup: function(){
            this.inherited(arguments);

            var filename = this.projectSchedule.title[0].toString();

            if (filename.length > 60) {
                filename = filename.substring(0, 60);
            }

            this.setFormValues({
                filename: filename,
                id: this.projectSchedule.id[0]
            });
        },
        submit: function(){
            if(this.validate()){
                var values = dojo.formToObject(this.id);
                var filename = values.filename.replace(/ /g, '_');

                buildspace.windowOpen('POST', this.exportUrl, {
                    filename:filename,
                    id: values.id,
                    _csrf_token: this.projectSchedule._csrf_token[0]
                });

                if(this.dialogWidget){
                    this.dialogWidget.hide();
                }
            }
        }
    });

    return declare('buildspace.apps.ProjectManagement.ExportProjectScheduleDialog', dijit.Dialog, {
        style:"padding:0px;margin:0;",
        title: nls.downloadProjectScheduleFile,
        projectSchedule: null,
        exportUrl: null,
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;
            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0",
                margin:"0"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function(e){
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createContent: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                    style:"padding:0;width:400px;height:80px;",
                    gutters: false
                }),
                toolbar = new dijit.Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;"
                }),
                form = ExportForm({
                    projectSchedule: this.projectSchedule,
                    exportUrl: this.exportUrl,
                    dialogWidget: this
                });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.download,
                    iconClass: "icon-16-container icon-16-import",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(form, 'submit')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });
});