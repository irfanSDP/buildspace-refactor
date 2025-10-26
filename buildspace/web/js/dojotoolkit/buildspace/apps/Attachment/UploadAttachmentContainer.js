define('buildspace/apps/Attachment/UploadAttachmentContainer',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dijit/Menu",
    'dojo/number',
    "dijit/CheckedMenuItem",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    "dojo/html",
    "dojo/dom",
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ViewTenderer',
    'dojo/on',
    "dojo/text!./templates/attachmentsForm.html",
], function(declare,
    lang,
    array,
    domAttr,
    Menu,
    number,
    MenuCheckedItem,
    MenuPlugin,
    Selector,
    Rearrange,
    FormulatedColumn,
    evt,
    keys,
    focusUtil,
    html,
    dom,
    xhr,
    PopupMenuItem,
    MenuSeparator,
    _WidgetBase,
    _OnDijitClickMixin,
    _TemplatedMixin,
    _WidgetsInTemplateMixin,
    Textarea,
    FormulaTextBox,
    GridFormatter,
    nls,
    on_,
    attachmentFormTemplate){

    var AttachmentsForm = declare("buildspace.apps.Attachment.AttachmentsForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        baseClass: "buildspace-form",
        templateString: attachmentFormTemplate,
        region: 'center',
        style: "overflow: auto;",
        nls: nls,
        uploaderUrl: null,
        startup: function() {
            this.inherited(arguments);
            return this.createUploader();
        },
        createUploader: function() {
            var self;
            self = this;
            this.uploader = new dojox.form.Uploader({
                label: nls.upload,
                uploadOnSelect: true,
                style: 'height:24px;',
                url: this.uploaderUrl,
                name: 'attachment'
            });
            on_(this.uploader, "Begin", function(uploadedFiles) {
                self.pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait + "..."
                });
                return self.pb.show();
            });
            on_(this.uploader, "Complete", function(uploadedFiles) {
                self.attachmentList.refreshGrid();
                return self.pb.hide();
            });
            this.buildspaceFormUploader.appendChild(this.uploader.domNode);
            return this.uploader.startup();
        }
    });

    var AttachmentContainer = declare("buildspace.apps.Attachment.AttachmentContainer", dijit.layout.BorderContainer, {
        style: 'padding:0;margin:0;border:none;width:50%;',
        disableEditing: false,
        uploaderUrl: null,
        attachmentListUrl: null,
        deleteAttachmentUrl: null,
        postCreate: function(){
            var self = this;
            var formatter = new GridFormatter();
            var gridWidth = this.disableEditing ? '100%' : '75%';
            var attachmentList = self.attachmentList = new dojox.grid.EnhancedGrid({
                style: "padding:0px;margin:0px;border-top:none;width:"+gridWidth,
                region: 'center',
                startup: function(){
                    var thisGrid = this;
                    this.on('RowClick', function(e){
                        var item = thisGrid.getItem(e.rowIndex);
                        if(item && !isNaN(parseInt(String(item.id)))){
                            self.disableToolbarButtons(false);
                        }else{
                            self.disableToolbarButtons(true);
                        }
                    });
                },
                structure: [
                    {name: 'No.', field: 'count', width: '30px', styles: 'text-align:center;', formatter: formatter.rowCountCellFormatter},
                    {name: nls.name, field: 'name', width:'auto', formatter: formatter.downloadCellFormatter },
                    {name: nls.uploadedBy, field: 'updated_by', width:'180px', styles:'text-align:center;'},
                    {name: nls.uploadedAt, field: 'updated_at', width:'128px', styles:'text-align:center;'}
                ],
                store: new dojo.data.ItemFileWriteStore({
                    url: this.attachmentListUrl,
                    clearOnClose: true
                }),
                refreshGrid: function(){
                    this.store.close();
                    this._refresh();
                }
            });

            self.addChild(attachmentList);

            if(!self.disableEditing){
                var toolbar = new dijit.Toolbar({ region: "bottom", style: "padding:2px;width:100%;border-bottom:none;border-left:none;border-right:none;" });

                toolbar.addChild(
                    self.deleteButton = new dijit.form.Button({
                        label    : nls.delete,
                        iconClass: "icon-16-container icon-16-container icon-16-delete",
                        disabled : true,
                        onClick  : dojo.hitch(self, 'deleteAttachment')
                    })
                );

                var fileUpload = new AttachmentsForm({
                    region: 'right',
                    attachmentList: self.attachmentList,
                    style: 'width:25%',
                    uploaderUrl: this.uploaderUrl
                });

                self.addChild(fileUpload);
                self.addChild(toolbar);
            }
        },
        deleteAttachment: function(){
            var self = this;
            if(this.attachmentList.selection.selectedIndex > -1) {
                var _this = this,
                    _item = _this.attachmentList.getItem(this.attachmentList.selection.selectedIndex),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.deleting+'. '+nls.pleaseWait+'...'
                    });

                if(_item && !isNaN(parseInt(String(_item.id)))){
                    buildspace.dialog.confirm(nls.deleteConfirmation, '<div>'+nls.firstLevelCategoryDialogMsg+'</div>', 75, 320, function() {
                        pb.show().then(function(){
                            dojo.xhrPost({
                                url: self.deleteAttachmentUrl,
                                content: {id: _item.id, "form[_csrf_token]": _item._csrf_token},
                                handleAs: 'json',
                                load: function(resp){
                                    if(resp.success){
                                        _this.attachmentList.refreshGrid();
                                    }
                                    self.disableToolbarButtons(true);
                                    pb.hide();
                                },
                                error: function(error){
                                    pb.hide();
                                }
                            });
                        });
                    });
                }
            }
        },
        disableToolbarButtons:function(disable){
            if(this.deleteButton){
                this.deleteButton._setDisabledAttr(disable);
            }
        },
        close: function() {
            return this.destroyRecursive();
        }
    });

    return declare('buildspace.apps.Attachment.UploadAttachmentContainer', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;border:none;width:100%;height:100%;",
        gutters: true,
        item: null,
        disableEditing: false,
        splitter: true,
        uploaderUrl: null,
        attachmentListUrl: null,
        deleteAttachmentUrl: null,
        canClose: true,
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            if(!this.uploaderUrl) this.uploaderUrl = "attachment/uploadAttachment/item_id/"+self.item.id+ "/item_class/" + self.item.class;
            if(!this.attachmentListUrl) this.attachmentListUrl = "attachment/getAttachments/item_id/"+self.item.id+ "/item_class/" + self.item.class;
            if(!this.deleteAttachmentUrl) this.deleteAttachmentUrl = 'attachment/deleteAttachment';

            if(this.canClose){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;width:100%;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.close,
                        iconClass: "icon-16-container icon-16-container icon-16-close",
                        style: "float:right;",
                        onClick: dojo.hitch(this, 'close')
                    })
                );
            }

            var attachmentContainer = this.attachmentContainer = new AttachmentContainer({
                disableEditing: this.disableEditing,
                region: 'center',
                uploaderUrl: this.uploaderUrl,
                attachmentListUrl: this.attachmentListUrl,
                deleteAttachmentUrl: this.deleteAttachmentUrl
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                if(toolbar) self.addChild(toolbar);
                self.addChild(attachmentContainer);
                pb.hide();
            });
        },
        close: function(){
            if(this.getParent() && this.canClose){
                this.getParent().removeChild(this);
            }
        },
        onHide: function() {
            if(this.getParent() && this.canClose){
                this.getParent().removeChild(this);
            }
        }
    });
});
