define(['dojo/_base/declare',
    'dijit/form/DropDownButton',
    "dijit/TooltipDialog",
    "dijit/popup",
    'dojo/keys',
    'dojo/dom-style',
    'dojo/dom-attr',
    'dijit/Dialog',
    'buildspace/ui/Applet',
    'buildspace/ui/applets/UserProfileForm',
    "dojo/i18n!buildspace/nls/UserProfile"], function(declare, DropDownButton, TooltipDialog, popup, keys, domStyle, domAttr, Dialog, Applet, UserProfileForm, nls){

    var UserProfileDialogBox = declare('buildspace.apps.applets.UserProfileDialogBox', Dialog, {
        style:"padding:0px;margin:0px;",
        postCreate: function(){
            this.inherited(arguments);

            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
            });
            this.closeButtonNode.style.display = "none";
        },
        _onKey: function(e){
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        }
    });

    return declare("buildspace.ui.applets.UserProfile", Applet, {
        dispName: "User Profile",
        postCreate: function(){
            this.dialog = this.toolTipDialog();
            this.button = DropDownButton({
                iconClass: 'icon-24-container icon-24-user',
                baseClass: 'buildspace-user_profile-panel',
                dropDown: this.dialog
            }, this.containerNode);
            this.inherited("postCreate", arguments);
        },
        startup: function(){
            var signoutButton = dijit.byId('buildspace-signout_button'),
                profileFormButton = dijit.byId('buildspace-userToolTipProfileForm');

            dojo.connect(profileFormButton, "onClick", dojo.hitch(this, "userProfileForm"));
            dojo.connect(signoutButton, "onClick", dojo.hitch(this, "signout"));
        },
        uninitialize: function(){
            this.inherited("uninitialize", arguments);
        },
        toolTipDialog: function(){
            var profileImg = buildspace.user.profileImg;
            var imgPath = require.toUrl("images/profiles/"+profileImg);

            return TooltipDialog({
                id:'buildspace-user_profile_tooltip',
                baseClass: 'user_profile-tooltip',
                style: "width: 300px;border:none;",
                content: '<table width="100%"><tr>' +
                    '<td><img id="toolTipUserProfilePic" src="'+imgPath+'" height="96" width="96"></td>'+
                    '<td><strong id="toolTipUserName">'+buildspace.user.fullname+'</strong>' +
                    '<br/>'+buildspace.user.username+'</td>'+
                    '</tr><tr>' +
                    '<td style="text-align: right;"><button data-dojo-type="dijit/form/Button" id="buildspace-userToolTipProfileForm" type="button">View Profile</button></td>'+
                    '<td><button data-dojo-type="dijit/form/Button" id="buildspace-signout_button" type="button">Sign out</button></td>'+
                    '</tr>' +
                    '</table>'
            });
        },
        userProfileForm: function() {
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + "..."
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "default/getMyProfileInformation",
                    handleAs: "json",
                    load: function(data) {
                        pb.hide();
    
                        var tooltip = dijit.byId('buildspace-user_profile_tooltip');
                        popup.close(tooltip);
    
                        var dia = new UserProfileDialogBox({
                            title: nls.myProfile,
                            style: "width: 600px; height: 240px;"
                        });
    
                        dia.set('content', new UserProfileForm({
                            data: data,
                            myProfileDialogBox: dia
                        }));
    
                        dia.show();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        signout: function(){
            var tooltip = dijit.byId('buildspace-user_profile_tooltip');
            popup.close(tooltip);

            var onYes = function(){
                window.location.href = 'logout';
            };

            var content = '<div class="icon-24-container icon-24-poweroff" style="float:left;width:32px;"></div><div>'+nls.content+'</div>';
            buildspace.dialog.confirm(nls.signout,content,60,280, onYes);
        }
    });
});