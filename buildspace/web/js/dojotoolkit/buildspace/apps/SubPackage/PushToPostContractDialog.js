define('buildspace/apps/SubPackage/PushToPostContractDialog',[
    'dojo/_base/declare',
    'dojo/keys',
    'dojo/currency',
    "dojo/dom-style",
    "dijit/layout/ContentPane",
    'dojo/i18n!buildspace/nls/SubPackages'
], function(declare, keys, currency, domStyle, ContentPane, nls){

    return declare('buildspace.apps.SubPackage.PushDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.pushToPostContract,
        companyInfo: null,
        rootProject: null,
        subPackage: null,
        subPackageGrid: null,
        url: null,
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
        createContent: function(){
            var self = this;

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:500px;height:200px;overflow:none;",
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

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(this.saveBtn = new dijit.form.Button({
                label: nls.push,
                iconClass: "icon-16-container icon-16-indent",
                onClick: function() {
                    self.doPublishToPostContract();
                }
            }));

            borderContainer.addChild(toolbar);

            borderContainer.addChild(new ContentPane({
                content: '<table>' +
                    '<tr><td style="font-weight:bold;text-align:right;padding:2px;vertical-align:top;">'+nls.subContractor+' :</td><td>'+this.companyInfo.name+'</td></tr>' +
                    '<tr><td style="font-weight:bold;text-align:right;padding:2px;vertical-align:top;">'+nls.address+' :</td><td><pre>'+this.companyInfo.address+'</pre></td></tr>' +
                    '<tr><td style="font-weight:bold;text-align:right;padding:2px;vertical-align:top;">'+nls.postcode+' :</td><td>'+this.companyInfo.postcode+'</td></tr>' +
                    '<tr><td style="font-weight:bold;text-align:right;padding:2px;vertical-align:top;">'+nls.country+' :</td><td>'+this.companyInfo.region+'</td></tr>' +
                    '<tr><td style="font-weight:bold;text-align:right;padding:2px;vertical-align:top;">'+nls.amount+' ('+this.companyInfo.currency+') :</td><td>'+currency.format(this.companyInfo.amount)+'</td></tr>' +
                    '</table>',
                region: "center",
                style:"padding:5px;"
            }));

            return borderContainer;
        },
        doPublishToPostContract: function (){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });

           pb.show();

            dojo.xhrPost({
                url: 'subPackage/pushToPostContract',
                content: { id: self.subPackage.id, pid: self.rootProject.id },
                handleAs: 'json',
                load: function(data) {
                    self.subPackageGrid.store.save();//in case it's dirty
                    self.subPackageGrid.store.close();
                    self.subPackageGrid.sort();
                    self.subPackageGrid.disableToolbarButtons(true, ['Add']);

                    self.hide();
                    pb.hide();
                },
                error: function(error) {
                    self.hide();
                    pb.hide();
                }
            });
        }
    });
});