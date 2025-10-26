define('buildspace/apps/PostContract/AccountCodeSetting/AccountCodeSettingsTabContainer',[
    'dojo/_base/declare',
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    './ItemCode/ItemCodes',
    'dojo/i18n!buildspace/nls/PostContract'], 
function(declare, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, ItemCodes, nls) {
    return declare('buildspace.apps.PostContract.AccountCodeSetting.AccountCodeSettingsTabContainer', dijit.layout.TabContainer, {
        style: "padding:0;width:100%;margin:0;border:none;height:100%;",
        gutters: false,
        project: null,
        claimCertificate: null,
        nested: true,
        postCreate: function() {
            this.inherited(arguments);

            this.addChild(new ItemCodes({
                title: nls.itemCodes,
                project: this.project,
                claimCertificate: this.claimCertificate,
                id: 'itemcodes-' + this.project.id,
            }));
        },
    });
});