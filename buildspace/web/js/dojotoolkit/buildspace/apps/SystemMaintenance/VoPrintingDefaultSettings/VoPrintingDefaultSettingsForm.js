define('buildspace/apps/SystemMaintenance/VoPrintingDefaultSettings/VoPrintingDefaultSettingsForm',[
    "dijit/_editor/plugins/FontChoice",
    "dijit/_editor/plugins/AlwaysShowToolbar",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetBase",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/Editor",
    "dijit/form/CheckBox",
    "dijit/form/Form",
    "dijit/form/ValidationTextBox",
    'dojo/_base/declare',
    "dojo/currency",
    "dojo/dom",
    "dojo/html",
    "dojo/i18n!buildspace/nls/VoFooterSetting",
    'dojo/text!./templates/SettingsForm.html'
    ], function(
    FontChoice,
    AlwaysShowToolbar,
    _OnDijitClickMixin,
    _TemplatedMixin,
    _WidgetBase,
    _WidgetsInTemplateMixin,
    Editor,
    CheckBox,
    Form,
    ValidationTextBox,
    declare,
    currency,
    dom,
    html,
    nls,
    SettingsFormTemplate
    ){


    var SettingsForm = declare('buildspace.apps.SystemMaintenance.VoPrintingDefaultSettings.SettingsForm',
        [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin
        ],{
        templateString: SettingsFormTemplate,
        project: null,
        region: 'center',
        style: "outline:none;padding:0px;margin:0px;border:none;",
        baseClass: "buildspace-form",
        nls: nls,
        formValues: [],
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formValues);
        },
    });

    return declare('buildspace.apps.VoPrintingDefaultSettings.VoPrintingDefaultSettingsFormContainer', dijit.layout.BorderContainer, {
        project: null,
        region: 'center',
        style: "outline:none;padding:0px;margin:0px;",
        formValues: {},
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            this.formValues = dojo.xhrGet({
                url: 'systemMaintenance/getVoPrintingDefaultSettings',
                handleAs: 'json',
                load: function(results){
                    var settingsForm = this.settingsForm = new SettingsForm({
                        id: 'vo_footer_default_setting-settings_form',
                        region: 'top',
                        formValues: results.settings
                    });
                    self.leftEditor.set('value', results.left_text);
                    self.rightEditor.set('value', results.right_text);

                    formContainer.addChild(settingsForm);
                },
            });

            var toolbar = new dijit.Toolbar({
                style:"outline:none!important;border-left:0px;border-right:0px;border-top:0px;padding:2px;overflow:hidden;",
                region: 'top'
            });
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.save,
                    iconClass: 'icon-16-container icon-16-save',
                    style: 'outline:none!important',
                    onClick: dojo.hitch(this, 'submit')
                })
            );

            var formContainer = new dijit.layout.BorderContainer({
                gutters: 'false',
                region: 'center'
            });

            this.leftEditor = new Editor({
                region:"left",
                plugins:['fontSize', '|', 'bold','italic','underline','|', 'indent', 'outdent', 'justifyLeft', 'justifyCenter', 'justifyRight'],
                style: "width:40%;font:81.25% arial,helvetica,sans-serif;",
            });

            this.rightEditor = new Editor({
                region:"right",
                plugins:['fontSize', '|', 'bold','italic','underline','|', 'indent', 'outdent', 'justifyLeft', 'justifyCenter', 'justifyRight'],
                style: "width:40%;font:81.25% arial,helvetica,sans-serif;",
            });
            formContainer.addChild(this.leftEditor);

            formContainer.addChild(this.rightEditor);

            this.addChild(toolbar);
            this.addChild(formContainer);
        },
        submit: function(){
            var self = this,
                formValues = null,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.saving+'. '+nls.pleaseWait+'...'
                });

            if(dijit.byId('vo_footer_default_setting-settings_form')){
                formValues = dojo.formToObject(dijit.byId('vo_footer_default_setting-settings_form').id);
            }

            formValues['vo_footer_default_setting[left_text]'] = this.leftEditor.get('value');
            formValues['vo_footer_default_setting[right_text]'] = this.rightEditor.get('value');

            pb.show();

            dojo.xhrPost({
                url: 'systemMaintenance/updateVoPrintingDefaultSettings',
                content: formValues,
                handleAs: 'json',
                load: function(resp) {
                    dojo.query('[id^="error-vo_footer_default_setting_"]').forEach(function(node){
                        node.innerHTML = '';
                    });

                    if(!resp.success){
                        for(var key in resp.errors){
                            html.set(dom.byId("error-vo_footer_default_setting_"+key), resp.errors[key]);
                        }
                    }
                    pb.hide();
                },
                error: function(error){
                    pb.hide();
                }
            });
        }
    });

});