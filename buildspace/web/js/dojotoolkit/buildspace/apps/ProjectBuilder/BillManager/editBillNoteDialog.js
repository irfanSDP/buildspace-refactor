define('buildspace/apps/ProjectBuilder/BillManager/editBillNoteDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/editBillNoteForm.html",
    'dijit/form/SimpleTextarea',
    'dojo/i18n!buildspace/nls/EditBillNote'
], function(declare, lang, connect, when, html, dom, keys, domStyle, GridFormatter, IndirectSelection, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, SimpleTextarea, nls){

    var EditBillNoteForm = declare("buildspace.apps.ProjectBuilder.BillManager.EditBillNoteForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
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

            new SimpleTextarea({
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
        save: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                }),
                values = dojo.formToObject(self.editItemNotForm.id),
                xhrArgs = {
                    url: self.updateUrl,
                    content: values,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            //Update Bill Grid
                            self.updateBillGridStore(resp.item);
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                        console.log(error);
                    }
                };

            self.dialogObj.hide();
            pb.show();

            dojo.xhrPost(xhrArgs);
        },
        updateBillGridStore: function(node){
            var self = this,
                billGridStore = self.billGrid.store;

            billGridStore.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(item){
                for(var property in node){
                    if(item.hasOwnProperty(property) && property != billGridStore._getIdentifierAttribute()){
                        billGridStore.setValue(item, property, node[property]);
                        billGridStore.save();
                    }
                }
            }});
        },
        onCancel: function(){

        }
    });

    return declare('buildspace.apps.ProjectBuilder.BillManager.EditBillNoteDialog', dijit.Dialog, {
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
            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(form, 'save')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });
});