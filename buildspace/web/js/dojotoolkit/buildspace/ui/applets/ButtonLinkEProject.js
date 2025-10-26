define(['dojo/_base/declare',
    'dijit/form/Button',
    'buildspace/ui/Applet'], function(declare, Button, Applet){
    return declare("buildspace.ui.applets.ButtonLinkEProject", Applet, {
        dispName: "Notifier",
        constructor: function(){
            var self = this;
            dojo.xhrGet({
                url: "default/getEProjectUrl",
                load: dojo.hitch(this, function(data){
                    self.eProjectUrl = data["eproject_url"];
                }),
                handleAs: "json"
            });
        },
        postCreate: function(){
            var self = this;
            this.button = Button({
                id: "eproject-link",
                iconClass: 'icon-24-buildspace_eproject',
                baseClass: 'buildspace-user_profile-panel',
                style: 'padding:2px;',
                onClick: function(){
                    window.open(self.eProjectUrl, '_blank');
                }
            }, this.containerNode);

            this.inherited("postCreate", arguments);
        }
    });
});