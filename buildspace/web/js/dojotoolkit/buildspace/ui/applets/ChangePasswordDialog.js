define('buildspace/ui/applets/ChangePasswordDialog',[
    'dojo/_base/declare',
    "dojox/form/Manager",
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/text!./../templates/changePasswordForm.html",
    'dojo/i18n!buildspace/nls/Common'
], function(declare, FormManager, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, html, dom, keys, domStyle, template, nls){

    var ChangePasswordForm = declare('buildspace.ui.applets.ChangePasswordForm', [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin], {
        templateString: template,
        dialogWidget: null,
        region: 'center',
        style: "outline:none;",
        baseClass: "buildspace-form",
        nls: nls,
        formValues: [],
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formValues);
        },
        postCreate: function(){
            this.inherited(arguments);
        },
        submit: function(){
            var self = this,
                values = dojo.formToObject(this.id),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            var xhrArgs = {
                url: 'default/defaultPasswordUpdate',
                content: values,
                handleAs: 'json',
                load: function(resp) {
                    dojo.query('[id^="error-"]').forEach(function(node){
                        node.innerHTML = '';
                    });

                    if(resp.success == true){
                        self.dialogWidget.hide();
                    }else{
                        var errors = resp.errors;
                        for(var error in errors){
                            if(self['error-'+error]){
                                html.set(self['error-'+error], errors[error]);
                            }
                        }
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            };

            if(this.validate()){
                pb.show();
                dojo.xhrPost(xhrArgs);
            }
        }
    });

    return declare('buildspace.ui.applets.ChangePasswordDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.changeDefaultPassword,
        formValues: [],
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;
            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
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
        signout: function(){
            var onYes = function(){
                window.location.href = 'logout';
            };

            var content = '<div class="icon-24-container icon-24-poweroff" style="float:left;width:32px;"></div><div>'+nls.wantToSignOut+'</div>';
            buildspace.dialog.confirm(nls.signout,content,60,280, onYes);
        },
        createContent: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;margin:0px;width:450px;height:230px;overflow:hidden;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;border-right:0px;border-left:0px;border-top:0px;"
                }),
                form = new ChangePasswordForm({
                    dialogWidget: this,
                    formValues: this.formValues
                }),
                contentPane = new dijit.layout.ContentPane({
                    content: '<div class="yellow-cell" style="padding:4px;">'+nls.pleaseChangeDefaultPassword+'</div>',
                    style: "top:30px!important;padding:5px;margin:0px;overflow:hidden;text-align:center;",
                    region: "top"
                });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style:"outline:none!important;",
                    onClick: function(e){
                        form.submit();
                    }
                })
            );
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.signout,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'signout')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(contentPane);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });
});