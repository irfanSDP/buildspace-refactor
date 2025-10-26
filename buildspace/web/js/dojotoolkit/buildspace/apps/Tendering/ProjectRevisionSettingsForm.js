define("buildspace/apps/Tendering/ProjectRevisionSettingsForm", [
    "dojo/_base/declare",
    "dojo/html",
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/keys",
    "dojo/request",
    "dojo/dom-style",
    "dojo/dom-attr",
    "dojo/dom-geometry",
    "dojo/_base/lang",
    "dijit/form/Form",
    "dijit/form/RadioButton",
    "dijit/form/Select",
    "dijit/form/ValidationTextBox",
    "dijit/form/NumberTextBox",
    "dijit/form/CurrencyTextBox",
    "dojo/number",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/on",
    'buildspace/apps/PageGenerator/GeneratorDialog',
    "dojo/text!./templates/projectRevisionSettingsForm.html",
    "dojo/text!./templates/projectRevisionSettingsRow.html",
    "dojo/text!./templates/projectParticipatedRevisionSettingsForm.html",
    "dojo/text!./templates/projectParticipatedRevisionSettingsRow.html",
    "dojo/i18n!buildspace/nls/Tendering"
    ], function(declare, html, dom, domConstruct, keys, request, domStyle, domAttr, domGeo, lang, Form, RadioButton, Select, ValidateTextBox, NumberTextBox, CurrencyTextBox, number, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, bindOn, GeneratorDialog, projectRevisionSettingsTemplate, projectRevisionSettingRowTemplate, projectParticipatedRevisionSettingsTemplate, projectParticipatedRevisionSettingRowTemplate, nls) {
    
    var ExportAddendumForm = declare('buildspace.apps.Tendering.ExportAddendumForm', [
        Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin], {
        templateString: '<form data-dojo-attach-point="containerNode">' +
            '<table class="table-form">' +
            '<tr>' +
            '<td class="label" style="width:80px;"><label style="display: inline;"></span>'+nls.downloadAs+' :</label></td>' +
            '<td>' +
            '<input type="text" name="filename" style="padding:2px;width:220px;" data-dojo-type="dijit/form/ValidationTextBox" data-dojo-props="trim:true, propercase:true, required: true">' +
            '<input type="hidden" name="id" value="">' +
            '<input type="hidden" name="rid" value="">' +
            '<input type="hidden" name="_csrf_token" value="">' +
            '</td>' +
            '</tr>' +
            '</table>' +
            '</form>',
        project: null,
        revisionData: null,
        region: 'center',
        dialogWidget: null,
        exportUrl: null,
        style: "outline:none;padding:0;margin:0;border:none;",
        baseClass: "buildspace-form",
        startup: function(){
            this.inherited(arguments);
            var filename = this.revisionData.revision.toString();

            if (filename.length > 60) {
                filename = filename.substring(0, 60);
            }

            this.setFormValues({
                filename: filename,
                id: this.project.id,
                rid: this.revisionData.id,
                _csrf_token: this.project._csrf_token
            });
        },
        submit: function(){
            if(this.validate() && this.exportUrl){
                var values = dojo.formToObject(this.id);
                var filename = values.filename.replace(/ /g, '_');

                buildspace.windowOpen('POST', this.exportUrl, {
                    filename: filename,
                    id: values.id,
                    rid: values.rid,
                    _csrf_token: values._csrf_token
                });

                if(this.dialogWidget){
                    this.dialogWidget.hide();
                }
            }
        }
    });

    var ExportAddendumDialog = declare('buildspace.apps.Tendering.ExportAddendumDialog', dijit.Dialog, {
        style:"padding:0px;margin:0;",
        title: nls.exportAddendum,
        project: null,
        revisionData: null,
        exportUrl: null,
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;
            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0",
                margin:"0"
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
                    style:"padding:0;width:400px;height:80px;",
                    gutters: false
                }),
                toolbar = new dijit.Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;"
                }),
                form = ExportAddendumForm({
                    project: this.project,
                    revisionData: this.revisionData,
                    exportUrl: this.exportUrl,
                    dialogWidget: this
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
                    label: nls.download,
                    iconClass: "icon-16-container icon-16-import",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(form, 'submit')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });

    var ProjectRevisionSettingRowForm = declare("buildspace.apps.Tendering.ProjectRevisionSettingRowForm", [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        isNew: false,
        projectId: -1,
        revisionId: -1,
        region: "center",
        nls: nls,
        rootProject: null,
        revisionData: null,
        ProjectRevisionSettingsForm: null,
        _csrf_token: 0,
        constructor: function(args) {
            if (parseInt(args.rootProject.tender_type_id[0], 10) === buildspace.constants.TENDER_TYPE_PARTICIPATED) {
                return args.templateString = projectParticipatedRevisionSettingRowTemplate;
            } else {
                return args.templateString = projectRevisionSettingRowTemplate;
            }
        },
        postCreate: function() {
            var self;
            this.inherited(arguments);
            self = this;
            
            this.setSelectedCurrentPrintingRevision();
            if (parseInt(this.rootProject.tender_type_id[0], 10) !== buildspace.constants.TENDER_TYPE_PARTICIPATED) {
                if (this.isNew) {
                    this.save();
                } else {
                    this.exitEditMode();
                }
                
                if ((!this.revisionData.locked_status) || parseInt(String(this.rootProject.status_id)) === buildspace.constants.STATUS_IMPORT) {
                    this.exportButton.set('disabled', true);
                }
                
                this.updateRevisionStatusDescription(this.revisionData.locked_status);
                this.lockedStatusValue.set('value', this.revisionData.locked_status);
            }
            
            bindOn(this.showRevisionSelectedLink, 'click', function(e) {
                self.assignNewSelectedRevision();
            });
        },
        assignNewSelectedRevision: function() {
            var self = this;
            var pb = new buildspace.dialog.indeterminateProgressBar({
                title: 'Processing...'
            });
            
            var postContent = {
                'id': this.projectId,
                'revisionId': this.revisionId,
                'project_revision[project_structure_id]': this.projectId,
                'project_revision[current_selected_revision]': true,
                'project_revision[_csrf_token]': this._csrf_token
            };

            pb.show().then(function(){
                request.post('projectBuilder/assignNewSelectedRevision', {
                    data: postContent,
                    handleAs: 'json',
                    preventCache: true
                }).then(function(response) {
                    self.ProjectRevisionSettingsForm.revisionDatas = response;
                    pb.hide();
                    
                    self.ProjectRevisionSettingsForm.clearOldRevisionAndAddNewRevision();
                }, function(error) {
                    console.log(error);
                    pb.hide();
                });
            });
        },
        save: function() {
            var self = this;
            
            if (parseInt(this.revisionData.locked_status) === parseInt(this.lockedStatusValue.value)) {
                this.exitEditMode();
            }

            var lockedStatus = this.lockedStatusValue.value;
            
            if (parseInt(lockedStatus) === 1) {
                var workArea = this.ProjectRevisionSettingsForm.workarea;
                var projectBreakdown = dijit.byId('main-project_breakdown');
                var d = new GeneratorDialog({
                    project: this.rootProject,
                    validateUrl: 'pageGenerator/validateAddendumBill',
                    onSuccess: dojo.hitch(this, "_lockRevision"),
                    onClickErrorNode: function(bill, evt){
                        switch (parseInt(bill.type)) {//only normal bills will have addendum. So we epect only normal bill will throws error (if any)
                            case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL:
                                if (bill['bill_status'] == buildspace.apps.Tendering.ProjectStructureConstants.BILL_STATUS_OPEN) {
                                    workArea.initTab(bill, {
                                        billId: bill.id,
                                        billType: bill.bill_type,
                                        billLayoutSettingId: bill.billLayoutSettingId,
                                        projectBreakdownGrid: projectBreakdown.grid,
                                        rootProject: self.rootProject
                                    });
                                }
                                break;
                            default:
                                break;
                        }
                    }
                });
    
                d.show();
            } else {
                self.formSubmission({
                    'id': this.projectId,
                    'revisionId': this.revisionId,
                    'project_revision[project_structure_id]': this.projectId,
                    'project_revision[revision]': this.revisionValue.value,
                    'project_revision[version]': this.revisionData.version,
                    'project_revision[locked_status]': this.lockedStatusValue.value,
                    'project_revision[_csrf_token]': this._csrf_token
                });
            }
        },
        _lockRevision: function(){
            var postContent = {
                'id': this.projectId,
                'revisionId': this.revisionId,
                'project_revision[project_structure_id]': this.projectId,
                'project_revision[revision]': this.revisionValue.value,
                'project_revision[version]': this.revisionData.version,
                'project_revision[locked_status]': this.lockedStatusValue.value,
                'project_revision[_csrf_token]': this._csrf_token
            };

            var _this = this;
            new buildspace.dialog.confirm(nls.updateBillSummaryPhrases, nls.updateBillSummaryPhrasesMsg, 80, 320, function() {
                _this.lockedStatusValue.set('value', _this.revisionData.locked_status);
                _this.exitEditMode();
            }, function() {
                _this.formSubmission(postContent);
            });
        },
        formSubmission: function (postContent) {
            var self = this;
            var baseForm = this.ProjectRevisionSettingsForm;
            
            var pb = new buildspace.dialog.indeterminateProgressBar({
                title: 'Processing...'
            });

            pb.show().then(function(){
                request.post('projectBuilder/saveProjectRevision', {
                    data: postContent,
                    handleAs: 'json',
                    preventCache: true
                }).then(function(response) {
                    pb.hide();

                    if(!response['success']) {
                        buildspace.dialog.alert('Warning',response.errors[Object.keys(response.errors)[0]],100,300);
                    }else{
                        if ((!response.item.locked_status) && (parseInt(String(self.rootProject.status_id)) !== buildspace.constants.STATUS_IMPORT)) {
                            self.exportButton.set('disabled', true);

                            if (self.reuploadTenderDocumentButton) {
                                self.reuploadTenderDocumentButton.set('disabled', true);
                            }
                        } else {
                            self.exportButton.set('disabled', false);
                            if (self.reuploadTenderDocumentButton) {
                                self.reuploadTenderDocumentButton.set('disabled', false);
                            }

                            // disable add bill button
                            const addBillButton = dijit.byId(self.rootProject.id + 'AddBillRow-button');
                            addBillButton.domNode.style.display = 'none';

                            // disable delete bill button
                            const deleteBillButton = dijit.byId(self.rootProject.id + 'DeleteRow-button');
                            deleteBillButton.domNode.style.display = 'none';
                        }
                        
                        self.revisionId = response.item.id;
                        self.updateRevisionStatusDescription(response.item.locked_status);
                        html.set(self.updatedAtView, response.item.updated_at);
                        self.revisionData.locked_status = response.item.locked_status;
    
                        if(response.item.locked_status){
                            self.hideActionButton();
    
                            var publishToPostContractBtn = dijit.byId(self.rootProject.id+'PublishToPostContractRow-button');
                            if(!publishToPostContractBtn){
                                var toolbar = dijit.byId(self.rootProject.id+'TenderingBuilder-toolbar');
                                if(self.rootProject.is_admin[0] && toolbar){
                                    var workArea = self.ProjectRevisionSettingsForm.workarea;
                                    toolbar.addChild(new dijit.ToolbarSeparator({
                                        id: self.rootProject.id+'PublishToPostContractBtn-toolbar_separator'
                                    }));
                                    toolbar.addChild(
                                        new dijit.form.Button({
                                            id: self.rootProject.id+'PublishToPostContractRow-button',
                                            label: nls.pushToPostContract,
                                            iconClass: "icon-16-container icon-16-indent",
                                            onClick: function(e){
                                                workArea.removeBillTab();
                                                workArea.removeTabByType(9999999); //remove subpackage tab
                                                var builder = workArea.getParent();
                                                if(builder){
                                                    builder.publishToPostContract();
                                                }
                                            }
                                        })
                                    );
                                }
                            }
                        }
    
                        baseForm.closeElementItemGrid();
                    }
                    
                }, function(error) {
                    pb.hide();
                });
            });
            
            this.exitEditMode();
        },
        setSelectedCurrentPrintingRevision: function() {
            var showRevisionSelectedMarkerDisplay = (this.revisionData.selected) ? "block" : "none";
            var showRevisionSelectedLinkDisplay = (this.revisionData.selected) ? "none" : "block";

            domStyle.set(this.showRevisionSelectedMarker, "display", showRevisionSelectedMarkerDisplay);
            domStyle.set(this.showRevisionSelectedLink, "display", showRevisionSelectedLinkDisplay);
        },
        exportAddendum: function() {
            var dialog = ExportAddendumDialog({
                project: this.rootProject,
                revisionData: this.revisionData,
                exportUrl: 'tenderingExportFile/exportAddendum'
            });
            dialog.show();
        },
        reuploadTenderDocumentAddendum: function() {
            var xhrArgs = {
                url: 'tendering/generateAddendumFileInTenderDocument',
                content: { pid: parseInt(String(this.rootProject.id)), rid: parseInt(this.revisionData.id), _csrf_token: String(this.rootProject._csrf_token) },
                handleAs: 'json',
                load: function(data) {
                    buildspace.dialog.alert('Notice', nls.pleaseCheckAddendumInTenderDocument,100,320);
                }
            };

            new buildspace.dialog.confirm(nls.reuploadToTenderDocument, nls.reuploadToTenderDocumentMsg, 100, 320, function() {
                dojo.xhrPost(xhrArgs);
            });
        },
        enterEditMode: function() {
            this.setupEditMode();
        },
        setupEditMode: function() {
            domStyle.set(this.addendumStatusInput, "display", "");
            domStyle.set(this.addendumStatusView, "display", "none");
            domStyle.set(this.editButton.domNode, "display", "none");
            domStyle.set(this.saveButton.domNode, "display", "");
        },
        exitEditMode: function() {
            domStyle.set(this.addendumStatusInput, "display", "none");
            domStyle.set(this.addendumStatusView, "display", "");
            domStyle.set(this.editButton.domNode, "display", "");
            domStyle.set(this.saveButton.domNode, "display", "none");

            if(this.tableTenderDocumentRow && parseInt(String(this.rootProject.status_id)) == buildspace.constants.STATUS_TENDERING){
                domStyle.set(this.ProjectRevisionSettingsForm.tableTenderDocumentHeader, "display", "");
                domStyle.set(this.tableTenderDocumentRow, "display", "");

                if ((!this.revisionData.locked_status) || (parseInt(this.revisionData.version) == 0)) {
                    this.reuploadTenderDocumentButton.set('disabled', true);
                }

            }else{
                domStyle.set(this.ProjectRevisionSettingsForm.tableTenderDocumentHeader, "display", "none");
                domStyle.set(this.tableTenderDocumentRow, "display", "none");
            }
        },
        updateRevisionStatusDescription: function(revisionStatus) {
            var statusLabel = revisionStatus ? nls.addendumLocked : nls.addendumProgressing;
            html.set(this.addendumStatusView, statusLabel);
            if (revisionStatus) {
                this.ProjectRevisionSettingsForm.addAddendumButton.set('disabled', false);
            } else {
                this.ProjectRevisionSettingsForm.addAddendumButton.set('disabled', true);
            }
        },
        hideActionButton: function() {
            domStyle.set(this.editButton.domNode, "display", "none");
            domStyle.set(this.saveButton.domNode, "display", "none");
            domStyle.set(this.noActionLabel, "display", "");
        }
    });
    
    return declare("buildspace.apps.Tendering.ProjectRevisionSettingsForm", [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        projectId: -1,
        baseClass: "buildspace-form",
        style: "padding:5px;overflow:auto;border:0px;",
        region: "center",
        rootProject: null,
        nls: nls,
        workarea: null,
        revisionDatas: null,
        _csrf_token: 0,
        tableCount: 1,
        rowArray: [],
        constructor: function(args) {
            if (parseInt(args.rootProject.tender_type_id[0], 10) === buildspace.constants.TENDER_TYPE_PARTICIPATED) {
                return args.templateString = projectParticipatedRevisionSettingsTemplate;
            } else {
                return args.templateString = projectRevisionSettingsTemplate;
            }
        },
        postMixInProperties: function() {
            this.inherited(arguments);
            this.rowArray[this.projectId] = [];
        },
        postCreate: function() {
            this.inherited(arguments);
            this.masterGenerateProjectRevisionTableRow();
        },
        masterGenerateProjectRevisionTableRow: function () {
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + "..."
            });
            var self = this;

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "projectBuilder/getProjectRevisionLists",
                    handleAs: "json",
                    content: { id: self.projectId },
                    load: function(data) {
                        self.revisionDatas = data;
                        self.populateProjectRevisionTableRow();
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        addAddendum: function () {
            var self = this;
            var key = this.rowArray[this.projectId].length;
            key = key === 1 ? 1 : key;
            var addendumVersion = this.tableCount - 1;
            
            var pb = new buildspace.dialog.indeterminateProgressBar({
                title: 'Processing...'
            });
            
            var postContent = {
                'id': this.projectId,
                'revisionId': -1,
                'project_revision[project_structure_id]': this.projectId,
                'project_revision[revision]': "Addendum " + addendumVersion,
                'project_revision[version]': addendumVersion,
                'project_revision[locked_status]': false,
                'project_revision[_csrf_token]': this._csrf_token
            };

            pb.show().then(function(){
                request.post('projectBuilder/saveProjectRevision', {
                    data: postContent,
                    handleAs: 'json',
                    preventCache: true
                }).then(function(response) {
                    self.revisionDatas = response;
                    pb.hide();
                    
                    if(!response['success']) {
                        buildspace.dialog.alert('Warning',response.errors[Object.keys(response.errors)[0]],100,300);
                    }else {
                        self.clearOldRevisionAndAddNewRevision();

                        // enable add bill button
                        const addBillButton = dijit.byId(self.rootProject.id + 'AddBillRow-button');
                        addBillButton.domNode.style.display = 'inline';

                        // enable delete bill button
                        const deleteBillButton = dijit.byId(self.rootProject.id + 'DeleteRow-button');
                        deleteBillButton.domNode.style.display = 'inline';
                    }
                }, function(error) {
                    pb.hide();
                });
            });
        },
        clearOldRevisionAndAddNewRevision: function() {
            this.rowArray[this.projectId] = [];
            this.tableCount = 1;
            dojo.empty(this.tableContainer);

            var publishToPostContractBtn = dijit.byId(this.rootProject.id+'PublishToPostContractRow-button');
            if(publishToPostContractBtn){
                var toolbarSeparator = dijit.byId(this.rootProject.id+'PublishToPostContractBtn-toolbar_separator');
                if(toolbarSeparator){
                    toolbarSeparator.destroyRecursive();
                }
                publishToPostContractBtn.destroyRecursive();
            }

            this.populateProjectRevisionTableRow();
            
            this.closeElementItemGrid();
        },
        populateProjectRevisionTableRow: function() {
            this._csrf_token = this.revisionDatas.form.csrf_token;

            var _ref = this.revisionDatas.projectRevisions;

            for (var key in _ref) {
                var value = _ref[key];
                var form = this.rowArray[this.projectId][key] = new ProjectRevisionSettingRowForm({
                    count: this.tableCount,
                    rootProject: this.rootProject,
                    projectId: this.projectId,
                    revisionId: value.id,
                    revisionData: value,
                    ProjectRevisionSettingsForm: this,
                    _csrf_token: this.revisionDatas.form.csrf_token
                });

                if (parseInt(this.rootProject.tender_type_id[0], 10) !== buildspace.constants.TENDER_TYPE_PARTICIPATED && ((Number(key) === 0) || this.rootProject.tendering_module_locked[0] || value.locked_status)) {
                    this.rowArray[this.projectId][key].hideActionButton();
                }
                
                this.addTableRow(form);
            }

            if (parseInt(this.rootProject.tender_type_id[0], 10) !== buildspace.constants.TENDER_TYPE_PARTICIPATED) {
                if (!value.locked_status) {
                    this.addAddendumButton.set('disabled', true);
                }
                
                if (this.rootProject.tendering_module_locked[0]) {
                    this.addAddendumButton.set('disabled', true);
                }
                
                return true;
            }
        },
        addTableRow: function(form) {
            domConstruct.place(form.domNode, this.tableContainer);
            this.tableCount++;
        },
        closeElementItemGrid: function() {
            var projectBreakdown = dijit.byId('main-project_breakdown');
            projectBreakdown.grid.reload();

            if(this.workarea){
                this.workarea.removeBillTab();
            }
        }
    });
});