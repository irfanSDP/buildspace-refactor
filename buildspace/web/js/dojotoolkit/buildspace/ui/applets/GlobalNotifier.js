define(['dojo/_base/declare',
    "dojo/dom-class",
    'dijit/form/DropDownButton',
    "dijit/TooltipDialog",
    "dijit/popup",
    'dojo/dom-attr',
    'buildspace/ui/Applet',
    "dojo/i18n!buildspace/nls/GlobalNotifier"], function(declare, domClass, DropDownButton, TooltipDialog, popup, domAttr, Applet,nls){
    return declare("buildspace.ui.applets.GlobalNotifier", Applet, {
        dispName: "Notifier",
        postCreate: function(){
            var self = this;
            this.dialog = self.toolTipDialog();
            this.button = DropDownButton({
                label: '<div class="icon-24-container icon-24-bell_gray"></div>',
                //iconClass: 'icon-24-container icon-24-bell_gray',
                baseClass: 'buildspace-user_profile-panel',
                dropDown: this.dialog
            }, this.containerNode);
            this.inherited("postCreate", arguments);
        },
        startup: function(){
            //this.poll();
            //var signoutButton = dijit.byId('buildspace-signout_button');
            //dojo.connect(signoutButton, "onClick", dojo.hitch(this, "signout"));
            //domClass.replace(this.button.domNode, "icon-24-container icon-24-bell_gray", "icon-24-container icon-24-bell");
        },
        uninitialize: function(){
            this.inherited("uninitialize", arguments);
        },
        toolTipDialog: function(){
            //var profileImg = buildspace.user.profileImg;
            //var imgPath = require.toUrl("images/profiles/"+profileImg);
            var content = '<table width="100%"><tr>' +
                '<td><b>'+buildspace.user.fullname+'</b>' +
                '<br/>'+buildspace.user.username+'</td>'+
                '</tr><tr>' +
                '</tr>' +
                '</table>';

            return TooltipDialog({
                id:'buildspace-global_notifier_tooltip',
                baseClass: 'global_notifier-tooltip',
                style: "width: 300px;border:none;",
                content: content
            });
        },
        poll: function(){
            var self = this;
            dojo.xhrGet( {
                url: "default/globalNotifier",
                timeout: 30000,
                handleAs: "text",
                load: function(response, ioArgs) {
                    /*window.setTimeout(function() {
                        self.poll();
                    }, 30000);*/
                    //self.button.iconClass = "icon-24-container icon-24-bell";
                    self.button.attr('label','<div class="icon-24-container icon-24-bell"></div>');
                    //domClass.replace(self.button.iconNode, "icon-24-container icon-24-bell_gray", "icon-24-container icon-24-bell");
                    //console.log(self.button.iconNode);
                    return response;
                },
                error: function(response,ioArgs) {
                    window.setTimeout(function() {
                        self.poll();
                    }, 30000);
                    return response;
                }
            });
        }
    });
});