define('buildspace/apps/ViewTenderer/TendererDetails',[
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
    "./HistoricalRateSearchDialog",
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ViewTenderer',
    'dojo/on',
    "dojo/text!./templates/attachmentsForm.html",
    "dojo/text!./templates/tendererRemarksForm.html"
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
    HistoricalRateSearchDialog,
    Textarea,
    FormulaTextBox,
    GridFormatter,
    nls,
    on_,
    attachmentFormTemplate,
    tendererRemarksFormTemplate){

    var AttachmentsForm = declare("buildspace.apps.ViewTenderer.AttachmentsForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        baseClass: "buildspace-form",
        templateString: attachmentFormTemplate,
        region: 'center',
        style: "overflow: auto;",
        nls: nls,
        project: null,
        company_id: -1,
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
                url: "viewTenderer/uploadTendererAttachment/project/" + self.project.id + "/company/" + self.company_id,
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

    var AttachmentContainer = declare("buildspace.apps.ViewTenderer.AttachmentList", dijit.layout.BorderContainer, {
        project: null,
        disableEditing: false,
        company_id: -1,
        postCreate: function(){
            var self = this;
            var formatter = new GridFormatter();

            var attachmentList = self.attachmentList = new dojox.grid.EnhancedGrid({
                style: (self.disableEditing ? 'width: 100%' : 'width:75%'),
                region: 'center',
                startup: function(){
                    var thisGrid = this;
                    this.on('RowClick', function(e){
                        var item = thisGrid.getItem(e.rowIndex);
                        if(item && !isNaN(parseInt(item.id[0]))){
                            self.disableToolbarButtons(false);
                        }else{
                            self.disableToolbarButtons(true);
                        }
                    });
                },
                structure: [
                    {name: 'No.', field: 'count', width: '30px', styles: 'text-align:center;', formatter: formatter.rowCountCellFormatter},
                    {name: nls.name, field: 'name', width:'180px', formatter: formatter.downloadCellFormatter },
                    {name: nls.uploadedBy, field: 'updated_by', width:'120px', styles:'text-align:center;'},
                    {name: nls.uploadedAt, field: 'updated_at', width:'120px', styles:'text-align:center;'}
                ],
                store: new dojo.data.ItemFileWriteStore({
                    url: "viewTenderer/getTendererAttachments/project_id/"+self.project.id+"/company_id/"+self.company_id,
                    clearOnClose: true
                }),
                refreshGrid: function(){
                    this.store.close();
                    this._refresh();
                }
            });

            self.addChild(attachmentList);

            if(!self.disableEditing)
            {
                var toolbar = new dijit.Toolbar({ region: "bottom", style: "padding:2px;width:100%;" });

                toolbar.addChild(
                    self.deleteButton = new dijit.form.Button(
                        {
                            label    : nls.delete,
                            iconClass: "icon-16-container icon-16-container icon-16-delete",
                            disabled : true,
                            onClick  : dojo.hitch(self, 'deleteAttachment')
                        }
                    )
                );

                var fileUpload = new AttachmentsForm({
                    region: 'right',
                    project: self.project,
                    company_id: self.company_id,
                    attachmentList: self.attachmentList,
                    style: 'width:25%'
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

                if(_item && !isNaN(parseInt(_item.id))){
                    buildspace.dialog.confirm(nls.deleteConfirmation, '<div>'+nls.firstLevelCategoryDialogMsg+'</div>', 75, 320, function() {
                        pb.show().then(function(){
                            dojo.xhrPost({
                                url: 'viewTenderer/deleteTendererAttachment',
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
            this.deleteButton._setDisabledAttr(disable);
        },
        close: function() {
            return this.destroyRecursive();
        }
    });

    var RemarksForm = declare("buildspace.apps.ViewTenderer.RemarksForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        baseClass: "buildspace-form",
        templateString: tendererRemarksFormTemplate,
        region: 'center',
        nls: nls,
        project: null,
        company_id: -1,
        tendererRemarksInputId: null,
        readonly: '',
        formid: null,
        startup: function() {
            this.inherited(arguments);
            this.refreshRemarks();
        },
        refreshRemarks:function(){
            var self = this;
            dojo.xhrGet({
                url: "viewTenderer/getTendererRemarks",
                content: {
                    project_id: self.project.id,
                    company_id: self.company_id
                },
                handleAs: "json",
                load: function (resp) {
                    dijit.byId(self.tendererRemarksInputId).set("value", resp.data.remarks);
                },
                error: function (error) {
                }
            });
        }
    });

    var RemarksContainer = declare("buildspace.apps.ViewTenderer.AttachmentList", dijit.layout.BorderContainer, {
        project: null,
        company_id: -1,
        disableEditing: false,
        postCreate: function() {
            var self = this;

            var remarksForm = self.remarksForm = new RemarksForm({
                region: 'center',
                project: self.project,
                company_id: self.company_id,
                readonly: self.disableEditing ? 'readonly' : '',
                tendererRemarksInputId: "tendererRemarksInput-p"+self.project.id+"-c"+self.company_id,
                formid: 'tenderer-details-p'+self.project.id+'-c'+self.company_id
            });

            dojo.xhrPost({
                url: 'default/getCsrfToken',
                handleAs: 'json',
                load: function(resp) {
                    self.remarksForm._csrf_token = resp._csrf_token;
                }
            }).then(function(){
                self.addChild(remarksForm);

                if(!self.disableEditing)
                {
                    var toolbar = new dijit.Toolbar({ region: "bottom", style: "padding:2px;width:100%;" });

                    toolbar.addChild(
                        new dijit.form.Button(
                            {
                                label    : nls.save,
                                iconClass: "icon-16-container icon-16-container icon-16-save",
                                onClick  : dojo.hitch(self, 'save')
                            }
                        )
                    );

                    self.addChild(toolbar);
                }
            });
        },
        save: function(){
            var self = this;
            var values = dojo.formToObject(self.remarksForm.formid);

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrPost({
                    url: "viewTenderer/updateTendererRemarks",
                    content: {
                        project_id: self.project.id,
                        company_id: self.company_id,
                        remarks: values.remarks,
                        'form[_csrf_token]': self.remarksForm._csrf_token
                    },
                    handleAs: "json",
                    load: function (data) {
                        self.remarksForm.refreshRemarks();
                        pb.hide();
                    },
                    error: function (error) {
                        pb.hide();
                    }
                });
            });
        },
        close: function() {
            return this.destroyRecursive();
        }
    });

    return declare('buildspace.apps.ViewTenderer.TendererDetails', dijit.layout.BorderContainer, {
        style: "padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        project: null,
        company_id: -1,
        tender_companies: [],
        disableEditing: false,
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-container icon-16-close",
                    style: "float:right;",
                    onClick: dojo.hitch(self, 'close')
                })
            );

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.compareRemarks,
                    iconClass: "icon-16-container icon-16-container icon-16-clipboard",
                    onClick: dojo.hitch(self, 'openRemarksComparison')
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.compareAttachments,
                    iconClass: "icon-16-container icon-16-container icon-16-clip",
                    onClick: dojo.hitch(self, 'openAttachmentsComparison')
                })
            );

            var remarksContainer = self.remarksContainer = new RemarksContainer({
                region: 'left',
                project: self.project,
                company_id: self.company_id,
                disableEditing: self.project.tendering_module_locked[0],
                style: 'width:50%;'
            });

            var attachmentContainer = self.attachmentContainer = new AttachmentContainer({
                region: 'right',
                project: self.project,
                company_id: self.company_id,
                disableEditing: self.project.tendering_module_locked[0],
                style: 'width:50%;'
            });

            var titlePane;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function() {
                dojo.xhrGet(
                    {
                        url     : "viewTenderer/getCompanyDetails",
                        content : {
                            company_id: self.company_id
                        },
                        handleAs: "json",
                        load    : function(resp) {
                            titlePane = self.titlePane = self.createTitlePane(resp.data.name);
                        },
                        error   : function(error) {
                        }
                    }
                ).then(function() {
                    self.addChild(toolbar);
                    self.addChild(titlePane);
                    self.addChild(remarksContainer);
                    self.addChild(attachmentContainer);
                    pb.hide();
                    }
                )
            });
        },
        createTitlePane: function(title){
            return new dijit.layout.ContentPane(
                {
                    style  : "background-color: #dddddd; border:none;",
                    content: '<strong>' + title + '</strong>',
                    region : 'top',
                    close: function(){
                        return this.destroyRecursive();
                    }
                });
        },
        openRemarksComparison: function(){
            var self = this;
            self.remarksContainer.close();
            self.attachmentContainer.close();
            self.titlePane.close();

            self.pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + "..."
            });

            if(self.remarksComparisonContainer) self.remarksComparisonContainer.close();

            var remarksComparisonContainer = self.remarksComparisonContainer = new dijit.layout.BorderContainer({
                region: 'center',
                gutters: false,
                style: 'overflow: auto',
                close: function(){
                    return this.destroyRecursive();
                }
            });

            var currentTendererContainer;

            for(var i in self.tender_companies)
            {
                currentTendererContainer = new dijit.layout.BorderContainer({
                    region: 'left',
                    style: 'width: '+100/self.tender_companies.length+'%; min-width:350px;'
                });

                currentTendererContainer.addChild(self.createTitlePane(buildspace.truncateString(self.tender_companies[i].name, 28)));

                currentTendererContainer.addChild(new RemarksContainer({
                    region: 'center',
                    project: self.project,
                    company_id: self.tender_companies[i].id,
                    disableEditing: self.project.tendering_module_locked[0],
                    style: 'width:100%;'
                }));

                remarksComparisonContainer.addChild(currentTendererContainer);
            }

            self.addChild(remarksComparisonContainer);
        },
        openAttachmentsComparison: function(){
            var self = this;
            self.remarksContainer.close();
            self.attachmentContainer.close();
            self.titlePane.close();

            if(self.attachmentsComparisonContainer) self.attachmentsComparisonContainer.close();

            var attachmentsComparisonContainer = self.attachmentsComparisonContainer = new dijit.layout.BorderContainer({
                region: 'center',
                gutters: false,
                style: 'overflow: auto',
                close: function(){
                    return this.destroyRecursive();
                }
            });

            var currentTendererContainer;

            for(var i in self.tender_companies)
            {
                currentTendererContainer = new dijit.layout.BorderContainer({
                    region: 'left',
                    style: 'width: '+100/self.tender_companies.length+'%; min-width:350px;'
                });

                currentTendererContainer.addChild(self.createTitlePane(buildspace.truncateString(self.tender_companies[i].name, 28)));

                currentTendererContainer.addChild(new AttachmentContainer({
                    region: 'center',
                    project: self.project,
                    company_id: self.tender_companies[i].id,
                    disableEditing: self.project.tendering_module_locked[0],
                    style: 'width:100%;'
                }));

                attachmentsComparisonContainer.addChild(currentTendererContainer);
            }

            self.addChild(attachmentsComparisonContainer);
        },
        close: function(){
            return this.destroyRecursive();
        },
        onHide: function() {
            return this.destroyRecursive();
        }
    });
});