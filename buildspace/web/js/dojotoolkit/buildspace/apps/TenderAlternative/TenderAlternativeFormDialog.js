define('buildspace/apps/TenderAlternative/TenderAlternativeFormDialog',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/on",
    "dijit/form/Form",
    "dijit/form/ValidationTextBox",
    "dijit/form/Textarea",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/tenderAlternativeForm.html",
    'dojo/i18n!buildspace/nls/TenderAlternative',
], function(declare, html, dom, keys, domStyle, on, Form, ValidateTextBox, Textarea, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls){

    var TenderAlternativeFormWidget = declare("buildspace.apps.TenderAlternative.TenderAlternativeFormWidget", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        project: null,
        projectBreakdownGrid: null,
        tenderAlternativeId: -1,
        nls: nls,
        style: "padding:5px;overflow:auto;",
        startup: function(){
            this.inherited(arguments);
            var self = this;
            //setting up form values before we start our form
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'tenderAlternative/tenderAlternativeForm',
                    handleAs: 'json',
                    content: { id: self.tenderAlternativeId, pid: self.project.id },
                    load: function(data){
                        self.tenderAlternativeForm.setFormValues(data);
                        pb.hide();
                    },
                    error: function(error) {
                        //something is wrong somewhere
                        pb.hide();
                    }
                });
            });
        },
        postCreate: function(){
            this.inherited(arguments);
        },
        save: function(){

            if(this.tenderAlternativeForm.validate()){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'...'
                });

                var self = this,
                    values = dojo.formToObject(this.tenderAlternativeForm.id);

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'tenderAlternative/tenderAlternativeUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="error-tender_alternative_"]').forEach(function(node){
                                node.innerHTML = '';
                            });
                            if(resp.success){
                                if(resp.item.rebuild_breakdown_grid){
                                    dojo.xhrGet({
                                        url: 'getTenderAlternativeProject/'+String(self.project.id),
                                        handleAs: 'json',
                                    }).then(function(proj){
                                        var projectBreakdownTab = self.projectBreakdownGrid.workArea.createProjectBreakdownTab(proj, false);
                                        self.projectBreakdownGrid = projectBreakdownTab.grid;
                                    });
                                }else{
                                    //we have to query from dijit because the projectbreakdown tab might be recreated from createProjectBreakdownTab() and it is not been updated yet in local variable
                                    var projectBreakdownTab = dijit.byId('main-project_breakdown');
                                    if(projectBreakdownTab){
                                        self.projectBreakdownGrid = projectBreakdownTab.grid;
                                        self.projectBreakdownGrid.reload();
                                    }
                                }

                                if(self.tenderAlternativeId && parseInt(self.tenderAlternativeId) > 0){
                                    var billGrid = dijit.byId(String(self.project.id)+"-"+self.tenderAlternativeId+"-tenderAlternative-billListGrid");
                                    if(billGrid){
                                        billGrid.reload();
                                    }
                                }

                                self.close();
                            }else{
                                for(var key in resp.errors){
                                    html.set(dom.byId("error-tender_alternative_"+key), resp.errors[key]);
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
            var item = {
                id: this.project.id,
                type: 1337
            };

            var workArea = this.projectBreakdownGrid.workArea;
            workArea.initTab(item, {
                projectBreakdownGrid: this.projectBreakdownGrid,
                project: this.project,
                editable: true
            }, false);

            var tenderAlternativeGrid = dijit.byId(String(this.project.id)+"-tenderAlternative-tenderAlternativeListGrid");
            if(tenderAlternativeGrid){
                tenderAlternativeGrid.reload();
            }

            if(this.dialogObj){
                this.dialogObj.hide();
            }
        },
        onCancel: function(){

        }
    });

    return declare('buildspace.apps.TenderAlternative.TenderAlternativeFormDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        project: null,
        projectBreakdownGrid: null,
        tenderAlternativeId: -1,
        title: nls.createNewTenderAlternative,
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
                style:"padding:0px;width:580px;height:240px;",
                gutters: false
            });

            var form = new TenderAlternativeFormWidget({
                dialogObj: this,
                project: this.project,
                projectBreakdownGrid: this.projectBreakdownGrid,
                tenderAlternativeId: this.tenderAlternativeId
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