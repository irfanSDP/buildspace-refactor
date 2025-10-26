define('buildspace/apps/ProjectBuilder/LevelForm',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/form/Form",
    "dijit/form/ValidationTextBox",
    "dijit/form/Textarea",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/levelForm.html",
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function(declare, html, dom, keys, domStyle, Form, ValidateTextBox, Textarea, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls){

    var LevelFormWidget = declare("buildspace.apps.ProjectBuilder.LevelFormWidget", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        levelId: -1,
        parentId: null,
        projectBreakdownGrid: null,
        nls: nls,
        style: "padding:5px;overflow:auto;",
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
                dojo.xhrGet({
                    url: 'projectBuilder/levelForm',
                    handleAs: 'json',
                    content: { id: self.levelId, parent_id: self.parentId },
                    load: function(data){
                        self.levelForm.setFormValues(data);
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
                values = dojo.formToObject(this.levelForm.id);

            if(this.levelForm.validate()){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'projectBuilder/levelUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="error-project_level_"]').forEach(function(node){
                                node.innerHTML = '';
                            });
                            if(resp.success){
                                self.projectBreakdownGrid.reload();

                                if(self.dialogObj){
                                    self.dialogObj.hide();
                                }
                            }else{
                                for(var key in resp.errors){
                                    html.set(dom.byId("error-project_level_"+key), resp.errors[key]);
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
        close: function(){

        },
        onCancel: function(){

        }
    });

    return declare('buildspace.apps.ProjectBuilder.LevelFormDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        levelId: -1,
        parentId: null,
        projectBreakdownGrid: null,
        title: nls.addLevel,
        buildRendering: function(){
            var form = this.createForm();
            form.startup();
            this.content = form;

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
        createForm: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:400px;height:150px;",
                gutters: false
            });

            var form = new LevelFormWidget({
                dialogObj: this,
                levelId: this.levelId,
                parentId: this.parentId,
                projectBreakdownGrid: this.projectBreakdownGrid
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(form, 'save')
                })
            );
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );
            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });
});