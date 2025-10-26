define('buildspace/apps/Approval/BaseBuilder',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/i18n!buildspace/nls/Approval'
], function(declare, lang, nls){

    return declare('buildspace.apps.Approval.Builder', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        viewArea: null,
        url: null,
        data: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.approve,
                    id: 'approve-button',
                    iconClass: "icon-16-container icon-16-container icon-16-hand_thumbsup",
                    onClick: dojo.hitch(self, 'approve')
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.reject,
                    id: 'reject-button',
                    iconClass: "icon-16-container icon-16-container icon-16-hand_thumbsdown",
                    onClick: dojo.hitch(self, 'reject')
                })
            );

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.processing+'...'
            });

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'default/getCsrfToken',
                    handleAs: 'json',
                    load: function(resp) {
                        self.xhrPostData = {
                            project_id: self.project.id,
                            module_identifier: self.object.module_identifier,
                            'form[_csrf_token]': resp._csrf_token
                        };

                        if(self.object.id){
                            self.xhrPostData[ 'object_id' ] = self.object.id;
                        }
                    }
                }).then(function(){
                    var viewArea = self.viewArea = self.initViewArea();
                    viewArea.startup();

                    self.addChild(toolbar);
                    self.addChild(viewArea);
                    pb.hide();
                });
            });
        },
        initViewArea: function(){
            // Each module defines its own View Area.
        },
        refreshViewArea: function(){
            var self = this;

            self.viewArea.destroyRecursive();

            var viewArea = self.viewArea = self.initViewArea();

            viewArea.startup();

            self.addChild(viewArea);
        },
        approve: function(){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.processing+'...'
            });

            buildspace.dialog.confirmWithInput(nls.confirm, '<div>'+nls.confirmApprove+'</div>', 180, 360, function(remarks) {
                pb.show().then(function(){
                    var xhrPostData = self.xhrPostData;
                    lang.mixin(xhrPostData, {
                        approve: true,
                        remarks: remarks
                    });
                    dojo.xhrPost({
                        url: self.url,
                        content: xhrPostData,
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp['errors']) {
                                buildspace.dialog.alert(nls.alert, '<div>'+resp['errors']+'</div>', 90, 360, function() {});
                            }
                            else{
                                self.refreshViewArea();
                                self.disableToolbarButtons();
                            }
                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }, function(){}, nls.addRemarks);
        },
        reject: function(){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.processing+'...'
            });

            buildspace.dialog.confirmWithInput(nls.confirm, '<div>'+nls.confirmReject+'</div>', 180, 360, function(remarks) {
                pb.show().then(function(){
                    var xhrPostData = self.xhrPostData;
                    lang.mixin(xhrPostData, {
                        approve: false,
                        remarks: remarks
                    });
                    dojo.xhrPost({
                        url: self.url,
                        content: xhrPostData,
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp['success']) {
                                self.disableToolbarButtons();
                            }
                            else{
                                buildspace.dialog.alert(nls.alert, '<div>'+resp['errors']+'</div>', 90, 360, function() {});
                            }
                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }, function(){}, nls.addRemarks);
        },
        disableToolbarButtons: function(){
            dijit.byId('approve-button')._setDisabledAttr(true);
            dijit.byId('reject-button')._setDisabledAttr(true);
        }
    });
});
