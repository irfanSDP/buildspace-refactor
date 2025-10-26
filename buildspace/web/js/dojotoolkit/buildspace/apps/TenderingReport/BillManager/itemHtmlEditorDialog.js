define('buildspace/apps/TenderingReport/BillManager/itemHtmlEditorDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/Editor",
    "dojox/editor/plugins/TablePlugins",
    "dijit/_editor/plugins/TextColor",
    "dojo/text!./templates/itemHtmlEditorForm.html",
    'dojo/i18n!buildspace/nls/Tendering'
], function(declare, lang, when, html, dom, keys, domStyle, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, Editor, TablePlugins, TextColor, template, nls){

    var Form = declare("buildspace.apps.TenderingReport.BillManager.ItemHtmlEditorForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        style: "outline:none;",
        itemObj: null,
        billId: -1,
        nls: nls,
        billGridStore: null,
        region: 'center',
        disableEditingMode: false,
        postCreate: function(){
            this.inherited(arguments);
            this.htmlEditorNode.set('value', this.itemObj.description[0]);
            this.htmlEditorNode.set('disabled', this.disableEditingMode);
        }
    });

    var Dialog = declare('buildspace.apps.TenderingReport.BillManager.ItemHtmlEditorDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.itemDescription,
        itemObj: null,
        billId: -1,
        billGridStore: null,
        currentBillLockedStatus: false,
        currentBillVersion: 0,
        currentItemVersion: 0,
        disableEditingMode: false,
        constructor:function(args){
            if (args.currentItemVersion != args.currentBillVersion || args.currentBillLockedStatus) {
                this.disableEditingMode = true;
            }
        },
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
            key = e.keyCode;
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
                style:"padding:0px;width:850px;height:350px;",
                gutters: false
            });

            var form = new Form({
                itemObj: this.itemObj,
                billId: this.billId,
                billGridStore: this.billGridStore,
                disableEditingMode: this.disableEditingMode
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
            borderContainer.addChild(form);

            return borderContainer;
        }
    });

    return Dialog;
});