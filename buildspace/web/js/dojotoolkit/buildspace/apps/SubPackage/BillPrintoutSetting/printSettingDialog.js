define('buildspace/apps/SubPackage/BillPrintoutSetting/printSettingDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "./masterContainer",
    'dojo/i18n!buildspace/nls/PrintLayoutSetting'
], function(declare, lang, connect, when, html, dom, keys, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, BillPrintoutSettingMasterContainer, nls){

    var BillPrintoutSettingContainer = declare('buildspace.apps.SubPackage.BillPrintoutSettingContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
        gutters: false,
        region: "center",
        rootProject: null,
        subPackage: null,
        postCreate: function() {
            this.inherited(arguments);
            this.addChild(new BillPrintoutSettingMasterContainer({
                projectId: this.rootProject.id,
                subPackageId: this.subPackage.id
            }));
        }
    });
    
    return declare('buildspace.apps.SubPackage.BillPrintoutSetting.PrintSettingDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.printSettings,
        rootProject: null,
        subPackage: null,
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
            var self = this;

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:900px;height:450px;",
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
            borderContainer.addChild(new BillPrintoutSettingContainer({
                    rootProject: this.rootProject,
                    subPackage: this.subPackage
                })
            );

            return borderContainer;
        }
    });
});