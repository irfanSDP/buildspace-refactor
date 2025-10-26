define('buildspace/apps/PostContract/PostContractClaim/ClaimImportDialog',[
    'dojo/_base/declare',
    "dojo/keys",
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!../templates/claimsImportForm.html",
    'dojo/i18n!buildspace/nls/PostContract'], function(declare, keys, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, claimsFormTemplate, nls){

    var ClaimForm = declare("buildspace.apps.Claims.ClaimFileImportForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: claimsFormTemplate,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        rootProject: null,
        nls: nls,
        style: "padding:5px;overflow:auto;",
        postCreate: function(){
            this.inherited(arguments);
            //attach Complete Event
            var fileUploadField = this.fileUploaderNode;
            fileUploadField.on('Complete', dojo.hitch(this, "uploadComplete"));
            fileUploadField.on('Begin', dojo.hitch(this, "uploadBegin"));
        },
        startup: function(){
            this.inherited(arguments);
        },
        uploadBegin: function(data){
            var pb = this.pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.importingData+'. '+nls.pleaseWait+'...'
            });

            pb.show();
        },
        uploadComplete:function(data){
            if(data.running){
                var pb = new dijit.ProgressBar({
                    value: 0,
                    title: "Importing Claims",
                    layoutAlign:"center"
                });
                var box = new dijit.Dialog({
                    content: pb,
                    style: "background:#fff;padding:5px;height:78px;width:280px;",
                    splitter: false
                });
                box.closeButtonNode.style.display = "none";
                box._onKey = function(evt){
                    var key = evt.keyCode;
                    if (key == keys.ESCAPE) {
                        dojo.stopEvent(evt);
                    }
                };
                box.onHide = function() {
                    box.destroyRecursive();
                };

                this.getImportLogStatus(box, pb);
            }else{
                this.dialogObj.hide();
                if(this.pb)
                    this.pb.hide();
            }
        },
        getImportLogStatus: function(box, pb){
            var self = this;
            dojo.xhrGet({
                url: 'claimTransfer/getImportClaimProgress',
                content: {
                    id: parseInt(String(this.rootProject.id))
                },
                handleAs: 'json',
                sync: false,
                load: function(data) {
                    var totalImportedFiles = parseInt(data.total_imported_files);
                    var totalFiles = parseInt(data.total_files);
                    var version = parseInt(data.version);

                    if(data.exists){
                        if(!box.open && totalFiles > 0 && totalImportedFiles != totalFiles){
                            if(self.dialogObj.open)
                                self.dialogObj.hide();
                            if(self.pb)
                                self.pb.hide();

                            box.show();
                        }

                        box.set({title:"Importing "+totalImportedFiles+"/"+totalFiles+" Files for Claim Revision "+version});

                        var i = totalImportedFiles / totalFiles * 100;
                        pb.set({value: i});

                        setTimeout(function(){self.getImportLogStatus(box, pb);}, 2000);
                    }else{
                        var importClaimsButton = dijit.byId('claimCertificatePage-' + self.rootProject.id + '-importClaimsButton');
                        importClaimsButton.setDisabled(true);

                        if(box.open)
                            box.hide();
                        if(self.dialogObj.open)
                            self.dialogObj.hide();
                        if(self.pb)
                            self.pb.hide();
                    }
                },
                error: function(error) {
                    if(self.dialogObj.open)
                        self.dialogObj.hide();
                    if(self.pb)
                        self.pb.hide();
                }
            });
        }
    });

    return declare('buildspace.apps.Claims.ClaimImportDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importClaims,
        rootProject: null,
        importUrl: null,
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
                style:"padding:0px;width:400px;height:120px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(new ClaimForm({
                dialogObj: this,
                title: this.title,
                rootProject: this.rootProject,
                importUrl: this.importUrl
            }));

            return borderContainer;
        }
    });
});
