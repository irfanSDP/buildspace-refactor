define('buildspace/apps/PostContractReport/BillManager/editBillNoteDialog',[
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
    "dojo/text!./templates/editBillNoteForm.html",
    'dijit/form/SimpleTextarea',
    'dojo/i18n!buildspace/nls/EditBillNote'
], function(declare, lang, connect, when, html, dom, keys, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, SimpleTextarea, nls){

    var EditBillNoteForm = declare("buildspace.apps.PostContractReport.BillManager.EditBillNoteForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        billId: -1,
        noteItemId: -1,
        billGrid: null,
        updateUrl: null,
        note: null,
        nls: nls,
        style: "padding:5px;overflow:auto;",
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var noteTextArea = new SimpleTextarea({
                name: 'item_note',
                trim: true,
                rows: "10",
                value: self.note
            }).placeAt(self.noteInputDivNode);

        },
        startup: function(){
            this.inherited(arguments);
        },
        close: function(){

        },
        onCancel: function(){

        }
    });

    var Dialog = declare('buildspace.apps.PostContractReport.BillManager.EditBillNoteDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.appName,
        billId: 0,
        billGrid: null,
        updateUrl: null,
        item: null,
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
            key = e.keyCode;
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
                style:"padding:0px;width:400px;height:230px;",
                gutters: false
            });

            var form = new EditBillNoteForm({
                dialogObj: self,
                billId: self.billId,
                billGrid: self.billGrid,
                updateUrl: self.updateUrl,
                noteItemId: self.item.id,
                note: (self.item.note) ? self.item.note[0] : ''
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