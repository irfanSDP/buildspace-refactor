define('buildspace/ui/applets/UserProfileForm',[
    'dojo/_base/declare',
    'dojo/on',
    'dojo/dom-form',
    "dojo/html",
    "dojo/dom",
    'dojo/dom-attr',
    'dojo/dom-style',
    'dojo/_base/array',
    'dojox/form/Manager',
    "dijit/form/Form",
    'dojox/form/Uploader',
    'dojox/form/uploader/plugins/Flash',
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    'dojo/text!../templates/userProfileForm.html',
    'dojo/text!../templates/userProfilePasswordForm.html',
    "dojo/i18n!buildspace/nls/UserProfile"
], function(declare, on_, domForm, html, dom, domAttr, domStyle, array, Manager, Form, Uploader, Flash, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, userProfileFormTemplate, userProfilePasswordTemplate, nls){

    var UserProfileForm = declare("buildspace.apps.applets.UserProfileForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: userProfileFormTemplate,
        baseClass: "buildspace-form",
        region: 'center',
        style: "border:none;padding:5px;overflow:hidden;",
        data: null,
        nls: nls,
        constructor: function(args) {
            var profileImg = buildspace.user.profileImg;
            var imgPath = this.imgPath = require.toUrl("images/profiles/"+profileImg);
            this.user = args.data;
        },
        postCreate: function() {
            this.inherited(arguments);

            var self = this;

            var uploader = new dojox.form.Uploader({
                label: self.nls.changeProfilePic,
                uploadOnSelect: true,
                url: "default/uploadMyProfileImage",
                styles: "padding: 5px;",
                name: 'sf_guard_user_profile[profile_photo]'
            });

            on_(uploader, "Begin", function(uploadedFiles){
                self.pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait + "..."
                });

                var errorBlock = self["error-profile_photo"];

                errorBlock.innerHTML = null;

                domStyle.set(errorBlock, 'display', 'none');

                self.pb.show();
            });

            on_(uploader, "Complete", function(uploadedFiles){
                self.pb.hide();

                var errorBlock = self["error-profile_photo"];

                if ( uploadedFiles.success ) {
                    errorBlock.innerHTML = null;

                    domStyle.set(errorBlock, 'display', 'none');

                    domAttr.set(self.profilePhotoHolder, 'src', uploadedFiles.imgURL);

                    var toolTipUserProfilePic = dom.byId('toolTipUserProfilePic');
                    domAttr.set(toolTipUserProfilePic, 'src', uploadedFiles.imgURL);

                    buildspace.user.profileImg = uploadedFiles.imgName;
                } else {
                    errorBlock.innerHTML = uploadedFiles.errorMsgs.profile_photo;

                    domStyle.set(errorBlock, 'display', 'block');
                }
            });

            this.myProfileImageUploader.appendChild(uploader.domNode);
        }
    });

    var UserProfileFormContainer = declare('buildspace.apps.applets.UserProfileFormContainer', dijit.layout.BorderContainer, {
        region: "top",
        style:"padding:0px;margin:0px;width:560px;height:200px;overflow:hidden;",
        gutters: false,
        data: null,
        myProfileDialogBox: null,
        postCreate: function() {
            this.inherited(arguments);

            var userProfileForm = new UserProfileForm({
                data: this.data
            });

            var toolbar = new dijit.Toolbar({region: "top", style:"outline:none!important;border-bottom: 1px solid #ccc;border-top:none;border-left:none;border-right:none;padding:2px;width:100%;"});
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this.myProfileDialogBox, 'hide')
                })
            );

            this.addChild(toolbar);
            this.addChild(userProfileForm);
        }
    });

    return declare('buildspace.apps.applets.UserProfileContainer', dijit.layout.BorderContainer, {
        region: "top",
        style:"padding:0px;margin:0px;width:600px;height:240px;overflow:hidden;",
        gutters: false,
        data: null,
        myProfileDialogBox: null,
        postCreate: function() {
            this.inherited(arguments);

            this.addChild(new UserProfileFormContainer({
                data: this.data,
                myProfileDialogBox: this.myProfileDialogBox
            }));
        }
    });
});