define('buildspace/apps/PostContract/ClaimCertificateNoteContainer',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/aspect',
    'dojo/keys',
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    "dijit/Editor",
    "dijit/_editor/plugins/LinkDialog",
    "dojox/editor/plugins/AutoUrlLink",
    "dojo/text!./templates/claimCertificateNoteForm.html",
    'dojo/i18n!buildspace/nls/PostContract'], function(declare, lang, aspect, keys, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, EnhancedGrid, GridFormatter, Editor, LinkDialog, AutoUrlLink, template, nls){

    var NoteEditorForm = declare("buildspace.apps.PostContract.ClaimCertificateNoteEditorForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        style: "outline:none;",
        claimCertificate: null,
        claimCertificateContainer: null,
        nls: nls,
        region: 'center',
        postCreate: function(){
            this.inherited(arguments);
            this.htmlEditorNode.set('value', String(this.claimCertificate.note));
        },
        submit: function(dialog){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'...'
            });
            var self = this, grid = this.claimCertificateContainer.grid,
                values = {
                    note: this.htmlEditorNode.value,
                    id: this.claimCertificate.id,
                    _csrf_token: this.claimCertificate._csrf_token
                },
                xhrArgs = {
                    url: 'claimCertificate/noteUpdate',
                    content: values,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            grid.store.fetchItemByIdentity({ 'identity' : String(self.claimCertificate.id),  onItem : function(item){
                                grid.store.setValue(item, 'note', resp.note);
                                grid.store.save();

                                self.claimCertificate.note = resp.note;
                            }});
                        }
                        pb.hide();
                        dialog.hide();
                    },
                    error: function(error) {
                        pb.hide();
                        dialog.hide();
                    }
                };

            pb.show().then(function(){
                dojo.xhrPost(xhrArgs);
            });
        }
    });

    var NoteEditorDialog = declare('buildspace.apps.PostContract.ClaimCertificateNoteEditorDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.notes,
        claimCertificate: null,
        claimCertificateContainer: null,
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
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:850px;height:350px;",
                gutters: false
            });

            var form = new NoteEditorForm({
                claimCertificate: this.claimCertificate,
                claimCertificateContainer: this.claimCertificateContainer
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(form, 'submit', this)
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

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

    var Grid = declare('buildspace.apps.PostContract.ClaimCertificateGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        rowSelector: '0px',
        escapeHTMLInData: false,
        selectable: true,
        claimCertificate: null,
        claimCertificateNoteContainer: null,
        constructor:function(args){
            this.structure = {
                noscroll: false,
                cells: [
                    [{
                        name: nls.claimNumber,
                        field: 'version',
                        width:'80px',
                        styles:'text-align:center;'
                    },{
                        name: nls.status,
                        field: 'status_txt',
                        width:'150px',
                        styles:'text-align:center;'
                    },{
                        name: nls.notes,
                        field: 'note',
                        width:'auto',
                        styles:'text-align:left;'
                    },{
                        name: nls.updatedBy,
                        field: 'creator',
                        width:'200px',
                        styles:'text-align:center;'
                    }]
                ]
            };

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    return declare('buildspace.apps.PostContract.ClaimCertificateNoteContainer', dijit.layout.BorderContainer, {
        style: "padding:0;width:100%;margin:0;border:none;height:100%;",
        title: nls.notes,
        gutters: false,
        claimCertificate:false,
        grid: null,
        postCreate: function() {
            this.inherited(arguments);

            var claimCertificate = this.claimCertificate;
            
            if(parseInt(String(claimCertificate.status)) == buildspace.constants.CLAIM_CERTIFICATE_STATUS_IN_PROGRESS){
                var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border-bottom:none;padding:2px;width:100%;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        id: String(claimCertificate.id)+'-ClaimCertificateNote-edit-btn',
                        label: nls.editNote,
                        iconClass: "icon-16-container icon-16-edit",
                        onClick: dojo.hitch(this,'editNote')
                    })
                );

                this.addChild(toolbar);
            }
            
            this.grid = new Grid({
                claimCertificateContainer: this,
                claimCertificate: claimCertificate,
                store: new dojo.data.ItemFileWriteStore({
                    url: 'claimCertificate/getNotes/id/'+String(claimCertificate.id),
                    handleAs: "json",
                    clearOnClose: true,
                    urlPreventCache: true
                })
            });

            this.addChild(this.grid);
        },
        editNote: function(){
            var editor = new NoteEditorDialog({
                claimCertificate: this.claimCertificate,
                claimCertificateContainer: this
            });
            editor.show();
        }
    });
});
