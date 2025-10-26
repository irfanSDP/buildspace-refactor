define('buildspace/apps/PostContract/ClaimCertificateContainer',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/aspect',
    "dojo/html",
    "dojo/on",
    "dojo/dom",
    "dojo/NodeList-manipulate",
    "dojo/keys",
    "dojo/query",
    "dojo/dom-construct",
    "dojo/dom-style",
    "dojo/dom-prop",
    'dojo/currency',
    'dojo/number',
    "dijit/form/FilteringSelect",
    "dojox/widget/Standby",
    "dojox/form/manager/_Mixin",
    "dojox/form/manager/_NodeMixin",
    "dojox/form/manager/_ValueMixin",
    "dojox/form/manager/_DisplayMixin",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/claimCertificateForm.html",
    "dojo/text!./templates/claimCertificateViewForm.html",
    "dojo/text!./templates/claimCertificatePrintInfo.html",
    "dojo/text!./templates/claimCertificateFormatAPrintInfo.html",
    "dojo/text!./templates/claimCertificateFormatBPrintInfo.html",
    "dojo/text!./templates/claimCertificateFormatAPrintInfoWithTax.html",
    "dojo/text!./templates/claimCertificateFormatNscPrintInfo.html",
    "dojo/text!./templates/claimCertificatePrintSettingsForm.html",
    "dojo/text!./templates/invoiceInformationForm.html",
    "dojo/text!./templates/topManagementVerifierSelectionRow.html",
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    "./ClaimCertificatePaymentDialog",
    "./ClaimCertificateNoteContainer",
    "./PostContractClaim/ClaimExportDialog",
    "./PostContractClaim/ClaimImportDialog",
    './AccountCodeSetting/AccountCodeSettingsTabContainer',
    "dijit/form/Form",
    'dojo/i18n!buildspace/nls/PostContract'], function(declare, lang, aspect, html, on, dom, domManipulate, keys, query, domConstruct, domStyle, domProp, currency, number, FilteringSelect, Standby, _ManagerMixin, _ManagerNodeMixin, _ManagerValueMixin, _ManagerDisplayMixin, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, formTemplate, viewTemplate, printTemplate, formatAPrintTemplate, formatBPrintTemplate, formatAPrintWithTaxTemplate, formatNscPrintTemplate, printSettingsTemplate, invoiceInformationForm, topManagementVerifierSelectionRowTemplate, EnhancedGrid, GridFormatter, ClaimCertificatePaymentDialog, ClaimCertificateNoteContainer, ClaimExportDialog, ClaimImportDialog, AccountCodeSettingsTabContainer, Form, nls, bd){

    var ClaimCertificateForm = declare('buildspace.apps.PostContract.ClaimCertificateForm',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin],{
        templateString: formTemplate,
        baseClass: "buildspace-form",
        nls: nls,
        claimCertificateContainer: null,
        grid: null,
        startup: function(){
            this.inherited(arguments);
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            var xhrContent = {
                pid: this.claimCertificateContainer.project.id
            };

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'postContract/claimCertificateForm',
                    handleAs: 'json',
                    content: xhrContent,
                    load: function(data){
                        for (var key in data['post_contract_info']) {
                            html.set(self[key], data['post_contract_info'][key]);
                        }

                        html.set(self['contractorSubmittedDateLbl'], data['contractorSubmittedDateLabel']+" :");
                        html.set(self['siteVerifiedDateLbl'], data['siteVerifiedDateLabel']+" :");
                        html.set(self['certificateReceivedDateLbl'], data['certificateReceivedDateLabel']+" :");

                        self.claimCertificateForm.setFormValues(data['claim_certificate']);

                        var retentionLimit = parseFloat(data['claim_certificate']['claim_certificate[retention_limit]']);

                        var retentionSumBYGST = data['claim_certificate']['claim_certificate[retention_sum]'];

                        var currencyCode = data['currencyCode'];

                        var htmlString = "";

                        for(var key in retentionSumBYGST){
                            var retentionSumVal = number.parse(retentionSumBYGST[key]);
                            if(isNaN(retentionSumVal) || retentionSumVal == 0 || retentionSumVal == null){
                                retentionSumVal = 0;
                            }
                            retentionSumVal = currency.format(retentionSumVal);
                            htmlString += currencyCode + " " + retentionSumVal + " ( "+data['taxLabel']+" : " + key + " )% <br>";
                        }

                        html.set(self['retentionSum'], htmlString);

                        if(data['claim_certificate']['claim_certificate[is_first_claim_certificate]'] || (retentionSumBYGST.length == 0)) {
                            self.releaseRetentionAmountInput.setDisabled(true);
                            self.releaseRetentionPercentageInput.setDisabled(true);
                        } else {
                            self.releaseRetentionPercentageInput.set("constraints", {min:0,max:100,pattern:'##0.00'});
                        }

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        postCreate: function(){
            this.inherited(arguments);

            var self = this;

            new FilteringSelect({
                name: "claim_certificate[retention_tax_percentage]",
                id: "retention_tax_percentage_drop_down",
                store: new dojo.data.ItemFileReadStore({
                    url:"postContract/getTaxPercentage/pid/" + self.claimCertificateContainer.project.id
                }),
                style: "padding:2px;",
                searchAttr: "name",
                readOnly: false,
                required: false
            }).placeAt(self.retention_tax_percentage);

            new FilteringSelect({
                name: "claim_certificate[tax_percentage]",
                id: "claim_certificate_tax_percentage_drop_down",
                store: new dojo.data.ItemFileReadStore({
                    url:"postContract/getClaimCertificateTaxPercentage"
                }),
                style: "padding:2px;",
                searchAttr: "name",
                readOnly: false,
                required: false
            }).placeAt(self.claim_certificate_tax_percentage);

            on(this.releaseRetentionPercentageInput, "keyup", lang.hitch(this, "updateReleaseRetention", "percentage"));
            on(this.releaseRetentionAmountInput, "keyup", lang.hitch(this, "updateReleaseRetention", "amount"));
        },
        updateReleaseRetention: function(type){
            var self = this;
            var tax = dijit.byId('retention_tax_percentage_drop_down') ? dijit.byId('retention_tax_percentage_drop_down').get('value') : null;
            var standby = (type == 'percentage') ? this.release_retention_amount_standby : this.release_retention_percentage_standby;
            var saveClaimCertificateButton = dijit.byId('saveClaimCertificate-button');

            if(tax && tax >= 0){
                standby.show();
                saveClaimCertificateButton.setDisabled(true);

                dojo.xhrGet({
                    url: 'postContract/getRetentionSum',
                    handleAs: 'json',
                    content: {pid: self.claimCertificateContainer.project.id},
                    load: function(data){

                        var nodeToUpdate,
                        val,
                        amount;

                        self.retentionSumBYGST = data['retentionSumByGST'];
                        self.retentionLimit = self.retentionSumBYGST[tax];


                        switch(type){
                            case "percentage":
                                nodeToUpdate = self.releaseRetentionAmountInput;

                                val = (!isNaN(self.releaseRetentionPercentageInput.get("value"))) ? self.releaseRetentionPercentageInput.get("value") : 0;

                                amount = self.retentionLimit * (val / 100);

                                break;
                            default:
                                nodeToUpdate = self.releaseRetentionPercentageInput;
                                val = (!isNaN(self.releaseRetentionAmountInput.get("value"))) ? self.releaseRetentionAmountInput.get("value") : 0;

                                amount = (self.retentionLimit != 0) ? (val / self.retentionLimit) * 100 : 0;
                        }
                        
                        nodeToUpdate.set("value", amount);
                        saveClaimCertificateButton.setDisabled(false);
                        standby.hide();
                    }
                });
            }
        },
        save: function(){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'...'
            });

            var self = this,
                values = dojo.formToObject(this.claimCertificateForm.id);

            lang.mixin(values, {
                pid: this.claimCertificateContainer.project.id
            });

            if(this.claimCertificateForm.validate()){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'postContract/claimCertificateUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="error-claim_certificate_"]').forEach(function(node){
                                node.innerHTML = '';
                            });

                            if(resp.success) {
                                self.claimCertificateContainer.grid.reload();
                                self.claimCertificateContainer.workArea.removeBillTab();

                                self.claimCertificateContainer.openClaimCertificateViewForm(resp.claimCertificate);
                            } else {
                                for(var key in resp.errorMsg){
                                    var msg = resp.errorMsg[key];
                                    html.set(dom.byId("error-claim_certificate_"+key), msg);
                                }
                            }

                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }
        }
    });

    var TopManagementVerifierSelectionRow = declare('buildspace.apps.PostContract.TopManagementVerifier.SelectionRow', [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: topManagementVerifierSelectionRowTemplate,
        baseClass: 'buildspace-form',
        nls: nls,
        name: null,
        value: null,
        textAlign: null,
        editMode: null,
        locked: null,
        addVerifierButton: null,
        verifierSelect: null,
        claimCertificateInfo: null,
        recordInfo: null,
        ClaimCertificateViewForm: null,
        _csrf_token: null,
        project: null,
        constructor: function() {
            this.inherited(arguments);
        },
        postCreate: function() {
            this.inherited(arguments);
            this.toggleEditableInputElements();

            if(this.locked) {
                domStyle.set(this.actionColumnNode,'display','none');
                domStyle.set(this.deleteColumnNode,'display','none');
            } else {
                if(this.editMode) {
                    domStyle.set(this.editButton.domNode,'display','none');
                    domStyle.set(this.saveButton.domNode,'display','');
                } else {
                    domStyle.set(this.saveButton.domNode,'display','none');
                    domStyle.set(this.editButton.domNode,'display','');
                }
            }
        },
        toggleEditableInputElements: function() {
            var self = this;

            if(this.editMode) {
                var store = new dojo.data.ItemFileReadStore({
                    url:"claimCertificate/getTopManagementVerifiers/pid/" + self.project.id,
                });

                var verifierSelect = new FilteringSelect({
                    name: "claim_certificate[top_management_verifiers][]",
                    store: store,
                    style: "width:100%;padding-top:2px;padding-bottom:2px;",
                    searchAttr: "name",
                    readOnly: false,
                    required: true,
                });
                
                verifierSelect.placeAt(this.verifierSelectNode);

                this.verifierSelect = verifierSelect;

                domStyle.set(this.verifierSelectDivNode, 'display', '');
                domStyle.set(this.verifierViewNode, 'display', 'none');
            } else {
                if(this.recordInfo != null) {
                    html.set(this.verifierViewNode, this.recordInfo.name);
                    html.set(this.verifierEmailNode, this.recordInfo.email);
                }

                domStyle.set(this.verifierSelectDivNode, 'display', 'none');
                domStyle.set(this.verifierViewNode, 'display', '');
            }
        },
        edit: function() {
            var self = this;
    
            self.addVerifierButton.set('disabled', true);
            domStyle.set(self.editButton.domNode,'display','none');
            domStyle.set(self.saveButton.domNode,'display','');
            self.editMode = true;

            var store = new dojo.data.ItemFileReadStore({
                url:"claimCertificate/getTopManagementVerifiers/pid/" + self.project.id,
            });

            var verifierSelect = new FilteringSelect({
                name: "claim_certificate[top_management_verifiers][]",
                store: store,
                style: "width:100%;padding-top:2px;padding-bottom:2px;",
                searchAttr: "name",
                readOnly: false,
                required: true,
            });

            store.fetchItemByIdentity({ 'identity' : self.recordInfo.user_id,  onItem : function(item){
                verifierSelect.set('item', item);
            }});
            
            verifierSelect.placeAt(this.verifierSelectNode);

            this.verifierSelect = verifierSelect;

            domStyle.set(this.verifierSelectDivNode, 'display', '');
            domStyle.set(this.verifierViewNode, 'display', 'none');
        },
        save: function() {
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'...'
                });

            var selectedVerifierId = self.verifierSelect.get('value');
            var recordId = self.recordInfo ? self.recordInfo.id : -1;
            var url = self.recordInfo ? 'claimCertificate/updateTopManagementVerifier' : 'claimCertificate/saveTopManagementVerifier';

            pb.show().then(function(){
                dojo.xhrPost({
                    url: url,
                    content: {
                        'post_contract_claim_top_management_verifier[id]': recordId,
                        'post_contract_claim_top_management_verifier[user_id]': selectedVerifierId,
                        'post_contract_claim_top_management_verifier[objectId]' : self.claimCertificateInfo.id,
                        'post_contract_claim_top_management_verifier[_csrf_token]': self._csrf_token,
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success) {
                            self.ClaimCertificateViewForm.populatePostContractClaimTopManagementVerifierRecords();

                            self.addVerifierButton.set('disabled', false);
                        } else {
                            buildspace.dialog.alert('Warning',Object.values(resp.errors)[0],100,300);
                        }

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });

                pb.hide();
            });
        },
        removeColumn: function() {
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.deleting+'. '+nls.pleaseWait+'...'
            });

            if(self.recordInfo !== null){
                new buildspace.dialog.confirm(nls.deletePostContractTopManagementVerifierDialogBoxTitle, nls.deletePostContractTopManagementVerifierDialogBoxMsg, 80, 320, function() {

                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: 'claimCertificate/deleteTopManagementVerifier',
                            content: {
                                id: self.recordInfo.id,
                            },
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success === true) {
                                    self.ClaimCertificateViewForm.populatePostContractClaimTopManagementVerifierRecords();

                                    self.addVerifierButton.set('disabled', false);
                                }

                                pb.hide();
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        });
                    });

                });
            } else {
                this.destroyRecursive();
                this.addVerifierButton.set('disabled', false);
            }
        },
    });

    var ClaimCertificateViewForm = declare('buildspace.apps.PostContract.ClaimCertificateViewForm',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin],{
        templateString: viewTemplate,
        baseClass: "buildspace-form",
        project: null,
        claimCertificateInfo: null,
        claimCertificate: null,
        claimCertificateContainer: null,
        isTopManagementVerifierEditable: null,
        nls: nls,
        startup: function(){
            this.inherited(arguments);

            var data = this.claimCertificateInfo;

            this.claimCertificateViewForm.setFormValues(data);

            html.set(this.contractorSubmittedDateLbl, data['contractorSubmittedDateLabel']+" :");
            html.set(this.siteVerifiedDateLbl, data['siteVerifiedDateLabel']+" :");
            html.set(this.certificateReceivedDateLbl, data['certificateReceivedDateLabel']+" :");
            
            html.set(this.accRemarksNode, data.acc_remarks);
            html.set(this.qsRemarksNode, data.qs_remarks);

            html.set(this.contractSum, data.contractSum);
            html.set(this.workDoneAmount, data.workDoneAmount);
            html.set(this.completionPercentage, data.completionPercentage);

            var retentionSumBYGST = data['retention_sum'];

            var currencyCode = data['currencyCode'];

            var htmlString = "";

            for(var key in retentionSumBYGST){
                var retentionSumVal = number.parse(retentionSumBYGST[key]);
                if(isNaN(retentionSumVal) || retentionSumVal == 0 || retentionSumVal == null){
                    retentionSumVal = 0;
                }
                retentionSumVal = currency.format(retentionSumVal);
                htmlString += currencyCode + " " + retentionSumVal + " ( "+data['taxLabel']+" : " + key + " )% <br>";
            }

            html.set(this.retentionSum, htmlString);
        },
        postCreate: function(){
            this.inherited(arguments);
            this.populatePostContractClaimTopManagementVerifierRecords();
            this.btnAddVerifier.set('onClick', dojo.hitch(this, "addVerifierRow"));

            if( ! this.isTopManagementVerifierEditable ) {
                domStyle.set(this.btnAddVerifier.domNode,'display','none');
                domStyle.set(this.actionHeaderNode,'display','none');
                domStyle.set(this.deleteHeaderNode,'display','none');
            }
        },
        populatePostContractClaimTopManagementVerifierRecords: function() {
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            dojo.empty(self.topManagementVerifierRowContainer);

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'claimCertificate/getSavedTopManagementVerifiers',
                    content: {
                        claimCertificateId: self.claimCertificateInfo.id,
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.records.length > 0) {
                            dojo.forEach(resp.records, function(record){
                                var verifierSelectionRow = new TopManagementVerifierSelectionRow({
                                    editMode: false,
                                    locked: ( ! self.isTopManagementVerifierEditable ),
                                    addVerifierButton: self.btnAddVerifier,
                                    claimCertificateInfo: self.claimCertificateInfo,
                                    recordInfo: record,
                                    ClaimCertificateViewForm: self,
                                    _csrf_token: self.claimCertificate._csrf_token,
                                    project: self.project,
                                });
    
                                verifierSelectionRow.placeAt(self.topManagementVerifierRowContainer);
                            });
                        } else {
                            if( ! self.isTopManagementVerifierEditable ) {
                                domStyle.set(self.topManagementVerifierSection, 'display', 'none');
                            }
                        }
                        
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        addVerifierRow: function() {
            var self = this;

            var verifierSelectionRow = new TopManagementVerifierSelectionRow({
                editMode: true,
                locked: false,
                addVerifierButton: self.btnAddVerifier,
                claimCertificateInfo: self.claimCertificateInfo,
                recordInfo: null,
                ClaimCertificateViewForm: self,
                _csrf_token: self.claimCertificate._csrf_token,
                project: self.project,
            });

            verifierSelectionRow.placeAt(self.topManagementVerifierRowContainer);

            self.btnAddVerifier.set('disabled', true);
        },
        submitForApproval: function(){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrPost({
                    url: "claimCertificate/creditDebitNoteClaimItemsDescriptionCheck",
                    content: {
                        pid: self.project.id,
                        id: self.claimCertificateInfo.id,
                    },
                    handleAs: 'json',
                    load: function(data) {
                        if(data.success) {
                            dojo.xhrGet({
                                url: 'claimCertificate/getVerifierList',
                                content: {
                                    pid: self.project.id,
                                    id: self.claimCertificate.id,
                                },
                                handleAs: 'json',
                                load: function(resp) {
                                    var count = 0;
                                    count += Object.keys(resp.verifiers).length;
                                    count += resp.topManagementVerifiers.length;
            
                                    if(resp.success){
                                        buildspace.dialog.confirm(nls.confirmSubmit, '<div>'+nls.numberOfReviewers+': ' + count + '</div>', 60, 200, function(){
                                            var submitForApprovalPb = buildspace.dialog.indeterminateProgressBar({
                                                title:nls.pleaseWait+'...'
                                            });
            
                                            submitForApprovalPb.show().then(function(){
                                                dojo.xhrPost({
                                                    url: 'claimCertificate/submitForApproval',
                                                    handleAs: 'json',
                                                    content: {
                                                        id: self.claimCertificate.id,
                                                        pid: self.project.id
                                                    },
                                                    load: function(data){
                                                        if(data['success']){
                                                            self.claimCertificateContainer.grid.reload();
                                                            self.claimCertificateContainer.workArea.removeBillTab();
                                                            self.claimCertificateContainer.removeStackContainerChildren();
                                                        }
                                                        submitForApprovalPb.hide();
                                                    },
                                                    error: function(error) {
                                                        submitForApprovalPb.hide();
                                                    }
                                                });
                                            });
                                        });
                                    } else {
                                        buildspace.dialog.alert('Warning', resp.errorMsg, 100, 300);
                                    }
                                    pb.hide();
                                },
                                error: function(error) {
                                    pb.hide();
                                }
                            });
                        } else {
                            var errorDetails = '<div>';

                            data.details.forEach(function(item, index) {
                                errorDetails += `${(index + 1)}) ${item.account_group} => ${item.claim_description} [ ${item.count} ${nls.records} ]</br>`;
                            });

                            errorDetails += '</div>';

                            buildspace.dialog.alert(nls.error, nls.requiredDescriptionDebitCreditNoteClaimItems + '</br></br>' + errorDetails, 150, 400);
                        }

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });

                pb.hide();
            });
        }
    });

    var ClaimCertificatePrintInfoForm = declare('buildspace.apps.PostContract.ClaimCertificatePrintInfoForm',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin],{
        templateString: printTemplate,
        baseClass: "buildspace-form",
        project: null,
        claimCertificateInfo: null,
        claimCertificate: null,
        format: buildspace.constants.CERTIFICATE_INFO_FORMAT_STANDARD,
        nls: nls,
        buildRendering: function(){
            switch(this.format)
            {
                case buildspace.constants.CERTIFICATE_INFO_FORMAT_A:
                    this.templateString = formatAPrintTemplate;
                    break;
                case buildspace.constants.CERTIFICATE_INFO_FORMAT_NSC:
                    this.templateString = formatNscPrintTemplate;
                    break;
                case buildspace.constants.CERTIFICATE_INFO_FORMAT_B:
                    this.templateString = formatBPrintTemplate;
                    break;
                default : break;
            }
            this.inherited(arguments);
        },
        startup: function(){
            this.setDetails(this.claimCertificateInfo);
        },
        postCreate: function(){
            this.inherited(arguments);
        },
        addSubPackageRows: function(data){
            var row1Clone, row2Clone, row3Clone, row4Clone;

            var subPackages = data['claimCertificateInfo']['subPackages'];

            for(var i in subPackages)
            {
                row1Clone = dom.byId('subpackage-row-1-template').cloneNode(true);
                row1Clone.id = 'subpackage-row-1-'+i;
                row1Clone.hidden = false;
                row1Clone.querySelector("[data-type=title]").innerHTML = subPackages[i]['title'];
                row1Clone.querySelector("[data-type=index]").innerHTML = 'A'+(parseInt(i)+1);
                domConstruct.place(row1Clone, dom.byId('subPackagesSection'), 'before');

                row2Clone = dom.byId('subpackage-row-2-template').cloneNode(true);
                row2Clone.id = 'subpackage-row-2-'+i;
                row2Clone.hidden = false;
                domConstruct.place(row2Clone, dom.byId('subPackagesSection'), 'before');

                row3Clone = dom.byId('subpackage-row-3-template').cloneNode(true);
                row3Clone.id = 'subpackage-row-3-'+i;
                row3Clone.hidden = false;
                row3Clone.querySelector("[data-type=cumulativeAmountCertified]").innerHTML = subPackages[i]['cumulativeAmountCertified'];
                row3Clone.querySelector("[data-type=cumulativePreviousAmountCertified]").innerHTML = subPackages[i]['cumulativePreviousAmountCertified'];
                row3Clone.querySelector("[data-type=amountCertified]").innerHTML = subPackages[i]['amountCertified'];
                row3Clone.querySelector("[data-type=amountCertifiedTaxAmount]").innerHTML = subPackages[i]['amountCertifiedTaxAmount'];
                row3Clone.querySelector("[data-type=amountCertifiedIncludingTax]").innerHTML = subPackages[i]['amountCertifiedIncludingTax'];
                domConstruct.place(row3Clone, dom.byId('subPackagesSection'), 'before');

                row4Clone = dom.byId('subpackage-row-4-template').cloneNode(true);
                row4Clone.id = 'subpackage-row-4-'+i;
                row4Clone.hidden = false;
                domConstruct.place(row4Clone, dom.byId('subPackagesSection'), 'before');
            }

            if(this['subPackageNetPayableAmountCurrencyCode']){
                html.set(this['subPackageNetPayableAmountCurrencyCode'], data['progressClaimInfo']['currencyCode']);
            }
            if(this['subPackageNetPayableAmount']){
                html.set(this['subPackageNetPayableAmount'], data['claimCertificateInfo']['projectAndSubPackagesCurrentAmountCertified']);
            }
            if(this['subPackageNetPayableAmountIncludingTax']){
                html.set(this['subPackageNetPayableAmountIncludingTax'], data['claimCertificateInfo']['projectAndSubPackagesCurrentAmountCertifiedIncludingTax']);
            }
        },
        setDetails: function(data){
            if(this.format == buildspace.constants.CERTIFICATE_INFO_FORMAT_NSC) this.addSubPackageRows(data);
            for(key in data['claimCertificatePrintSettings']){
                var camelStr = buildspace.toCamelCase(key);
                var displaySectionB = data['claimCertificatePrintSettings']['display_section_b'],
                    displaySectionC = data['claimCertificatePrintSettings']['display_section_c'],
                    displaySectionD = data['claimCertificatePrintSettings']['display_section_d'];

                switch(key){
                    case "display_section_b":
                        if(!displaySectionB){
                            query(".section_b-row").forEach(function(node, index, nodelist){
                                dojo.destroy(node);
                            });
                        }
                        break;
                    case "display_section_c":
                        if(!displaySectionC){
                            query(".section_c-row").forEach(function(node, index, nodelist){
                                dojo.destroy(node);
                            });
                        }
                        break;
                    case "display_section_d":
                        if(!displaySectionD){
                            query(".section_d-row").forEach(function(node, index, nodelist){
                                dojo.destroy(node);
                            });
                        }
                        break;
                    default:
                        if(this[camelStr]){
                            if(key.indexOf("include_", 0) === 0 && typeof data['claimCertificatePrintSettings'][key] === "boolean" && data['claimCertificatePrintSettings'][key]){
                                domStyle.set(this[camelStr], "display", "");
                            }else{
                                html.set(this[camelStr], data['claimCertificatePrintSettings'][key]);
                            }
                        }
                }
            }

            var showRequestForVariationWorkDone = data['progressClaimInfo']['showRequestForVariationWorkDone'];

            if( !showRequestForVariationWorkDone ) {
                dojo.destroy(this.requestForVariationWorkDoneNode);
            }

            if(!data['letterOfAwardOptions']['retentionSumIncludeMaterialOnSite']){
                domProp.set('tr-main-contract-materialOnSite', 'hidden', 'hidden');
            }else{
                if(dom.byId('tr-misc-materialOnSite')) domProp.set('tr-misc-materialOnSite', 'hidden', 'hidden');
            }

            for (var key in data['claimCertificateInfo']) {

                var nodeName = key;

                if(this[nodeName]){
                    html.set(this[nodeName], data['claimCertificateInfo'][key]);
                }
            }

            for (key in data['progressClaimInfo']) {
                if(this[key]){
                    html.set(this[key], data['progressClaimInfo'][key]);
                }
            }

            if(data.claimCertificatePrintSettings.request_for_variation_category_id_to_print === null) {
                this.rfvCategorynameDivider && domStyle.set(this.rfvCategorynameDivider, "display", 'none');
                this.rfvCategoryNameSection && domStyle.set(this.rfvCategoryNameSection, "display", 'none');
            }

            if(displaySectionC && data['claimCertificatePrintSettings']['debit_credit_note_with_breakdown']){
                for (key in data['progressClaimInfo']['debitCreditNoteBreakdownOverallTotal']) {
                    var debitCreditNoteBreakdownOverallTotal      = data['progressClaimInfo']['debitCreditNoteBreakdownOverallTotal'][key]['total'];
                    var debitCreditNoteBreakdownPreviousClaim     = (data['progressClaimInfo']['debitCreditNoteBreakdownPreviousClaim'][key]) ? data['progressClaimInfo']['debitCreditNoteBreakdownPreviousClaim'][key]['total'] : 0;
                    var debitCreditNoteBreakdownThisClaim         = (data['progressClaimInfo']['debitCreditNoteBreakdownThisClaim'][key]) ? data['progressClaimInfo']['debitCreditNoteBreakdownThisClaim'][key]['total'] : 0;
                    var debitCreditNoteBreakdownThisClaimAfterGST = (data['progressClaimInfo']['debitCreditNoteBreakdownThisClaimAfterGST'][key]) ? data['progressClaimInfo']['debitCreditNoteBreakdownThisClaimAfterGST'][key]['total'] : 0;
                    domConstruct.create("tr", {
                        innerHTML: '<td></td>'+
                        '<td><label style="display:inline;">'+data['progressClaimInfo']['debitCreditNoteBreakdownOverallTotal'][key]['description']+'</label></td>'+
                        '<td class="label" style="text-align:right"><label style="display:inline;">'+currency.format(debitCreditNoteBreakdownOverallTotal)+'</label></td>'+
                        '<td class="label" style="text-align:right"><label style="display:inline;">'+currency.format(debitCreditNoteBreakdownPreviousClaim)+'</label></td>'+
                        '<td></td>'+
                        '<td class="label" style="text-align:right"><label style="display:inline;">'+currency.format(debitCreditNoteBreakdownThisClaim)+'</label></td>'+
                        '<td class="label" style="text-align:right"><label style="display:inline;">'+currency.format(debitCreditNoteBreakdownThisClaimAfterGST)+'</label></td>'+
                        '<td></td>'
                    }, this.includeDebitCreditNote, 'before');
                }
                dojo.destroy(this.includeDebitCreditNote);
            }
            
        }
    });

    var ClaimCertificatePrintSettingForm = declare('buildspace.apps.PostContract.ClaimCertificatePrintSettingForm',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin],{
        templateString: printSettingsTemplate,
        baseClass: "buildspace-form",
        title: nls.claimCertificatePrintSettings,
        project: null,
        nls: nls,
        startup: function(){
            this.inherited(arguments);

            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            var xhrContent = {
                pid: this.project.id
            };
            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'postContract/getEnabledPostContractClaimModules',
                    handleAs: 'json',
                    load: function(enabledModules){
                        dojo.xhrGet({
                            url: 'postContract/claimCertificatePrintSettingsForm',
                            handleAs: 'json',
                            content: xhrContent,
                            load: function(data){
                                self.claimCertificatePrintSettingsForm.setFormValues(data);

                                var displayFooterFormatATblLbl = (self.footerFormatANode.checked) ? "" : "none";
                                domStyle.set(self.footerFormatATableLblNode, "display", displayFooterFormatATblLbl);

                                var sectionBModules = {
                                    [buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT]: enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT],
                                    [buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT]: enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT],
                                    [buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE]: enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE],
                                    [buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG]: enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG],
                                    [buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE]: enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE]
                                };

                                if(!Object.values(sectionBModules).some(val => val === true)){
                                    domStyle.set(self['sectionB-label'], "display", "none");
                                    domStyle.set(self['sectionB'], "display", "none");
                                }
                                else{
                                    if(!enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT]) domStyle.set(self['sectionB-include_advance_payment'], "display", "none");
                                    if(!enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT]) domStyle.set(self['sectionB-include_deposit'], "display", "none");
                                    if(!enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE]) domStyle.set(self['sectionB-include_material_on_site'], "display", "none");
                                    if(!enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG]) domStyle.set(self['sectionB-include_ksk'], "display", "none");
                                    if(!enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE]) domStyle.set(self['sectionB-include_work_on_behalf_mc'], "display", "none");
                                }

                                var sectionCModules = {
                                    [buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEBIT_CREDIT_NOTE]: enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEBIT_CREDIT_NOTE],
                                    [buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF]: enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF],
                                    [buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF]: enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF],
                                    [buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY]: enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY]
                                };

                                if(!Object.values(sectionCModules).some(val => val === true)){
                                    domStyle.set(self['sectionC-label'], "display", "none");
                                    domStyle.set(self['sectionC'], "display", "none");
                                }
                                else{
                                    if(!enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEBIT_CREDIT_NOTE]) domStyle.set(self['sectionC-include_debit_credit_note'], "display", "none");
                                    if(!enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF]) domStyle.set(self['sectionC-include_purchase_on_behalf'], "display", "none");
                                    if(!enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF]) domStyle.set(self['sectionC-include_work_on_behalf'], "display", "none");
                                    if(!enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY]) domStyle.set(self['sectionC-include_penalty'], "display", "none");
                                }

                                var sectionDModules = {
                                    [buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT]: enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT],
                                    [buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT]: enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT]
                                };

                                if(!Object.values(sectionDModules).some(val => val === true)){
                                    domStyle.set(self['sectionD-label'], "display", "none");
                                    domStyle.set(self['sectionD'], "display", "none");
                                }
                                else{
                                    if(!enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT]) domStyle.set(self['sectionD-include_utility'], "display", "none");
                                    if(!enabledModules.modules[buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT]) domStyle.set(self['sectionD-include_permit'], "display", "none");
                                }

                                new FilteringSelect({
                                    name: "claim_certificate_print_setting[request_for_variation_category_id_to_print]",
                                    id: "request_for_variation_category_id_to_print",
                                    store: new dojo.data.ItemFileReadStore({
                                        url:"postContract/getRequestForVariationCategories"
                                    }),
                                    value: data['claim_certificate_print_setting[request_for_variation_category_id_to_print]'],
                                    style: "padding:2px;",
                                    searchAttr: "name",
                                    readOnly: false,
                                    required: false
                                }).placeAt(self.RfvCategoryToPrintNode);

                                pb.hide();
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        });
                    }
                });
            });
        },
        postCreate: function(){
            this.inherited(arguments);

            var self = this;

            on(this.footerFormatANode, "change", function(e){
                var displayVal = (this.checked) ? "" : "none";
                domStyle.set(self.footerFormatATableLblNode, "display", displayVal);
            });
        },
        save: function(){
            var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'...'
                }),
                self = this,
                values = dojo.formToObject(this.claimCertificatePrintSettingsForm.id);

            lang.mixin(values, {
                pid: this.project.id
            });

            if(this.claimCertificatePrintSettingsForm.validate()){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'postContract/claimCertificatePrintSettingsUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="error-claim_certificate_print_settings_"]').forEach(function(node){
                                node.innerHTML = '';
                            });

                            if(resp.success) {
                                //something todo here
                            } else {
                                for(var key in resp.errorMsg){
                                    var msg = resp.errorMsg[key];
                                    html.set(dom.byId("error-claim_certificate_print_settings_"+key), msg);
                                }
                            }

                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }
        }
    });

    var Grid = declare('buildspace.apps.PostContract.ClaimCertificateGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        project: null,
        claimCertificateContainer: null,
        constructor:function(args){
            var formatter = new GridFormatter();

            var CustomFormatter = {
                currentViewingCellFormatter: function(cellValue, rowIdx){
                    var item = this.grid.getItem(rowIdx);

                    cellValue = "&nbsp;";

                    if(item && !isNaN(parseInt(String(item.id)))){
                        if(item.current_selected_revision[0]){
                            cellValue = '<div style="margin:auto;" class="icon-16-container icon-16-checkmark2"></div>';
                        }else{
                            cellValue = '<a href="#" onclick="return false;">'+nls.printThisRevision+'</a>';
                        }
                    }

                    return cellValue;
                },
                paidAmountCellFormatter: function(cellValue, rowIdx){
                    var item = this.grid.getItem(rowIdx),
                        value = number.parse(cellValue);

                    cellValue = "&nbsp;";

                    if(item && !isNaN(parseInt(String(item.id)))){
                        if(isNaN(value) || value == null){
                            cellValue = "&nbsp;";
                        }else{
                            cellValue = '<a href="#" onclick="return false;">'+currency.format(value)+'</a>';
                        }
                    }

                    return cellValue;
                }
            };

            this.structure = {
                noscroll: false,
                cells: [
                    [{
                        name: nls.claimNumber,
                        field: 'version',
                        width:'80px',
                        styles:'text-align:center;'
                    }, {
                        name: nls.amountCertified,
                        field: 'amount_certified',
                        width: '200px',
                        styles:'text-align:right;',
                        formatter: formatter.unEditableCurrencyCellFormatter
                    },{
                        name: nls.status,
                        field: 'status_txt',
                        width:'150px',
                        styles:'text-align:center;'
                    },{
                        name: nls.paidAmount,
                        field: 'paid_amount',
                        width:'200px',
                        styles:'text-align:right;',
                        formatter: CustomFormatter.paidAmountCellFormatter
                    },{
                        name: nls.approvalDate,
                        field: 'approval_date',
                        width:'200px',
                        styles:'text-align:center;'
                    },{
                        name: nls.created_at,
                        field: 'created_at',
                        width:'200px',
                        styles:'text-align:center;'
                    },{
                        name: nls.currentPrintingRevision,
                        field: 'current_selected_revision',
                        width:'auto',
                        styles:'text-align:center;',
                        formatter: CustomFormatter.currentViewingCellFormatter
                    }]
                ]
            };

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);

            this.on('RowClick', function(e){
                var item = this.getItem(e.rowIndex),
                    colField = e.cell ? e.cell.field : null;
                if(colField == 'paid_amount' && !isNaN(parseInt(String(item.id))) ){
                    var d = new ClaimCertificatePaymentDialog({
                        claimCertificate: item,
                        claimCertificatesGrid: this
                    });
                    d.show();
                }else if(colField == 'current_selected_revision' && !isNaN(parseInt(String(item.id))) && !item.current_selected_revision[0]){
                    this.setSelectedRevision(item);
                }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        onRowDblClick: function (e) {
            this.inherited(arguments);
            var rowIndex = this.selection.selectedIndex,
                item = this.getItem(rowIndex),
                colField = e.cell ? e.cell.field : null;

            if(this.selection.selectedIndex > -1 && item && !isNaN(parseInt(String(item.id))) && colField != 'paid_amount' && colField != 'current_selected_revision') {
                var pb = new dijit.ProgressBar({
                    value: 0,
                    title: "Importing Claims",
                    layoutAlign:"center"
                });
                var box = new dijit.Dialog({
                    content: pb,
                    style: "background:#fff;padding:5px;height:78px;width:280px;",
                    splitter: false
                });
                box.closeButtonNode.style.display = "none";
                box._onKey = function(evt){
                    var key = evt.keyCode;
                    if (key == keys.ESCAPE) {
                        dojo.stopEvent(evt);
                    }
                };
                box.onHide = function() {
                    box.destroyRecursive();
                };

                this.importClaimProgress(item, box, pb);
            }
        },
        importClaimProgress: function(item, box, pb){
            var self = this;
            dojo.xhrPost({
                url: 'claimTransfer/getImportClaimProgress',
                content: {
                    id: parseInt(String(this.project.id))
                },
                handleAs: 'json',
                sync: false,
                load: function(data) {
                    var totalImportedFiles = parseInt(data.total_imported_files);
                    var totalFiles = parseInt(data.total_files);
                    var version = parseInt(data.version);

                    if(data.exists && totalFiles > 0 && totalImportedFiles != totalFiles){
                        if(!box.open){
                            box.show();
                        }

                        box.set({title:"Importing "+totalImportedFiles+"/"+totalFiles+" Files for Claim Revision "+version});

                        var i = totalImportedFiles / totalFiles * 100;
                        pb.set({value: i});

                        setTimeout(function(){self.importClaimProgress(item, box, pb);}, 5000);
                    }else{
                        if(box.open){
                            box.hide();
                        }
                        
                        return self.claimCertificateContainer.openClaimCertificateViewForm(item);
                    }
                },
                error: function(error) {
                    if(box.open){
                        box.hide();
                    }
                }
            });
        },
        setSelectedRevision: function(claimCertificate){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });

            var params = {
                id: claimCertificate.id,
                _csrf_token: claimCertificate._csrf_token
            };

            var self = this,
                store = this.store;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: "postContract/claimCertificateSetSelectedRevision",
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            self.claimCertificateContainer.workArea.projectBreakdown.claimCertificate = null;
                            var revisionIds = [];
                            var selectedRevisionId = -1;
                            dojo.forEach(resp.items, function(data){
                                store.fetchItemByIdentity({ 'identity' : data.id,  onItem : function(item){
                                    store.setValue(item, "current_selected_revision", data["current_selected_revision"]);

                                }});
                                revisionIds.push(data['revision_id']);
                                if(data['current_selected_revision'] == true) selectedRevisionId = data['revision_id'];
                            });

                            var latestRevisionId = Math.max.apply(null,revisionIds);

                            var selectedClaimCertificate = null;

                            if(latestRevisionId != selectedRevisionId) selectedClaimCertificate = { post_contract_claim_revision_id: selectedRevisionId };

                            self.claimCertificateContainer.workArea.projectBreakdown.claimCertificate = selectedClaimCertificate;

                            store.save();

                            self.claimCertificateContainer.workArea.projectBreakdown.grid.reload();

                            self.claimCertificateContainer.closeBillTabs();

                            pb.hide();
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var EditInvoiceInformationForm = declare('buildspace.apps.PostContract.ClaimCertificate.EditInvoiceInformationForm', [Form, 
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        _ManagerMixin,
        _ManagerNodeMixin,
        _ManagerValueMixin,
        _ManagerDisplayMixin
    ], {
        templateString: invoiceInformationForm,
        nls: nls,
        claimCertificateInfo: null,
        region: 'center',
        dialogWidget: null,
        style: "outline:none;padding:0px;margin:0px;border:none;",
        baseClass: "buildspace-form",
        startup: function(){
            this.inherited(arguments);

            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function() {
                dojo.xhrGet({
                    url: 'postContract/getInvoiceInformation',
                    handleAs: 'json',
                    content: { claimCertificateId: self.claimCertificateInfo.id },
                    load: function(response){
                        if(response.invoiceInformation == null) {
                            self.setPostMonth();
                        } else {
                            self.invoice_date.set('value', response.invoiceInformation.invoice_date);
                            self.invoice_number.set('value', response.invoiceInformation.invoice_number);
                            self.post_month.set('value', response.invoiceInformation.post_month);
                        }

                        self.setFormValues({
                            "claim_certificate_invoice[_csrf_token]": response._csrf_token,
                            "claim_certificate_invoice[claimCertificateId]": self.claimCertificateInfo.id,
                        });

                        pb.hide();
                    },
                    error: function(error) {
                        console.log(error);
                        pb.hide();
                    }
                });
            });

            this.invoice_date.on('change', dojo.hitch(this, function() {
                this.setPostMonth();
            }));
        },
        setPostMonth: function() {
            var date = this.invoice_date.get('value');

            if(date != null) {
                var month     = ('0' + (date.getMonth() + 1)).slice(-2);
                var postMonth = date.getFullYear() + '' + month;
                
                this.post_month.set('value', postMonth);
            } else {
                this.post_month.set('value', '');
            }

            
        },
        save: function(){
            if(this.validate()) {
                var self = this;

                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

                var formData = dojo.formToObject(this.id);

                pb.show().then(function() {
                    dojo.xhrPost({
                        url: 'postContract/updateInvoiceInformation',
                        handleAs: 'json',
                        content: formData,
                        load: function(data){
                            if(data.success) {
                                pb.hide();

                                buildspace.dialog.alert('Success', nls.invoiceInformationSaved, 50, 300);

                                self.dialogWidget.hide();
                            } else {
                                pb.hide();
                            }
                        },
                        error: function(error) {
                            console.log(error);
                            pb.hide();
                        }
                    });
                });
            }
        },
    });

    var ExportToExcelForm = declare('buildspace.apps.PostContract.ClaimCertificate.ExportToExcelForm', [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        _ManagerMixin,
        _ManagerNodeMixin,
        _ManagerValueMixin,
        _ManagerDisplayMixin], {
        templateString: '<form data-dojo-attach-point="containerNode">' +
            '<table class="table-form">' +
            '<tr>' +
            '<td class="label" style="width:80px;"><label style="display: inline;"></span>'+nls.exportAs+' :</label></td>' +
            '<td>' +
            '<input type="text" name="filename" style="padding:2px;width:220px;" data-dojo-type="dijit/form/ValidationTextBox" data-dojo-props="trim:true, propercase:true, maxlength:45, required: true"> .xlsx' +
            '</td>' +
            '</tr>' +
            '</table>' +
            '</form>',
        project: null,
        claimCertificateObj: null,
        region: 'center',
        dialogWidget: null,
        fileName: null,
        url: null,
        style: "outline:none;padding:0px;margin:0px;border:none;",
        baseClass: "buildspace-form",
        startup: function(){
            this.inherited(arguments);
            var d = new Date();
            this.setFormValues({filename: this.fileName + '_' + d.getDate() + (d.getMonth() + 1) + d.getFullYear()})
        },
        submit: function(){
            var values = dojo.formToObject(this.id);
            if(this.validate()){
                var filename = values.filename.replace(/ /g, '_');
                window.open(this.url + filename, '_self');

                if(this.dialogWidget){
                    this.dialogWidget.hide();
                }
            }
        }
    });

    var EditInvoiceInformationDialog = declare('buildspace.apps.PostContract.ClaimCertificate.EditInvoiceInformationDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.invoiceInformation,
        claimCertificateInfo: null,
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
        startup: function(){
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
                style:"padding:0px;width:400px;height:120px;",
                gutters: false
            }),
            toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            }),
            form = EditInvoiceInformationForm({
                claimCertificateInfo: this.claimCertificateInfo,
                dialogWidget: this,
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
        },
    });

    var ExportExcelDialog = declare('buildspace.apps.PostContract.ClaimCertificate.ExportExcelDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.exportToExcel,
        project: null,
        claimCertificateObj: null,
        fileName: null,
        url: null,
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
                style:"padding:0px;width:400px;height:80px;",
                gutters: false
            }),
            toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            }),
            form = ExportToExcelForm({
                project: this.project,
                claimCertificateObj: this.claimCertificateObj,
                dialogWidget: this,
                fileName: this.fileName,
                url: this.url,
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
                    label: nls.export,
                    iconClass: "icon-16-container icon-16-export",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(form, 'submit')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });

    return declare('buildspace.apps.PostContract.ClaimCertificateContainer', dijit.layout.BorderContainer, {
        style: "padding:0;width:100%;margin:0;border:none;height:100%;",
        gutters: false,
        project: null,
        isApproval: false,
        claimCertificate:false,
        workArea: null,
        locked: false,
        postCreate: function() {
            this.inherited(arguments);

            var self = this;
            var project = this.project;

            dojo.subscribe('postContractClaimCertificate' + project.id + '-stackContainer-selectChild', "", function (page) {
                var widget = dijit.byId('postContractClaimCertificate' + project.id + '-stackContainer');
                if (widget) {
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page));

                    index = index + 1;

                    if (children.length > index) {
                        while (children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();

                            index = index + 1;
                        }

                        if (page.grid) {
                            var selectedIndex = page.grid.selection.selectedIndex;

                            page.grid.store.save();
                            page.grid.store.close();

                            var handle = aspect.after(page.grid, "_onFetchComplete", function () {
                                handle.remove();
                                if (selectedIndex > -1) {
                                    this.scrollToRow(selectedIndex);
                                    this.selection.setSelected(selectedIndex, true);
                                }
                            });

                            page.grid.sort();
                        }
                    }
                }
            });

            var url = 'postContract/getClaimCertificates/pid/'+project.id;

            if(this.claimCertificate) url += "/oid/"+this.claimCertificate.id;

            this.grid = new Grid({
                title: nls.claimCertificateList,
                claimCertificateContainer: this,
                project: project,
                store: new dojo.data.ItemFileWriteStore({
                    url: url,
                    handleAs: "json",
                    clearOnClose: true,
                    urlPreventCache: true
                })
            });

            if(!this.locked){
                var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border-bottom:none;padding:2px;width:100%;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        id: project.id+'ActivateClaimCertificate-button',
                        label: nls.activateClaimCertificate,
                        iconClass: "icon-16-container icon-16-certificate-2",
                        onClick: dojo.hitch(this,'validateOpeningNewClaimCert')
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: project.id+'ClaimCertificatePrintSettings-button',
                        label: nls.claimCertificatePrintSettings,
                        iconClass: "icon-16-container icon-16-monitor",
                        onClick: dojo.hitch(this,'openClaimCertificatePrintSettingsForm')
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: project.id+'ReloadClaimCertificateGrid-button',
                        label: nls.reload,
                        iconClass: "icon-16-container icon-16-reload",
                        onClick: function(e){
                            self.grid.reload();
                        }
                    })
                );
            }

            var stackContainer = dijit.byId('postContractClaimCertificate' + project.id + '-stackContainer');

            if (stackContainer) {
                dijit.byId('postContractClaimCertificate' + project.id + '-stackContainer').destroyRecursive();
            }

            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: 'postContractClaimCertificate' + project.id + '-stackContainer'
            });

            stackContainer.addChild(this.grid);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'postContractClaimCertificate' + project.id + '-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var container = new dijit.layout.BorderContainer({
                region: "center",
                style: "padding:0;width:100%;margin:0;border:none;height:100%;",
                gutters: false
            });

            container.addChild(stackContainer);
            container.addChild(controllerPane);

            if(toolbar) this.addChild(toolbar);


            if(this.isApproval){
                var self = this;
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                var xhrContent = {
                    id: this.claimCertificate.id
                };
                pb.show().then(function(){
                    dojo.xhrGet({
                        url: 'postContract/claimCertificateInfo',
                        handleAs: 'json',
                        content: xhrContent,
                        load: function(data){
                            var claimCertificatePrintInfoForm = new ClaimCertificatePrintInfoForm({
                                title: nls.claimCertificatePrintInfo,
                                project: this.project,
                                claimCertificate: this.claimCertificate,
                                claimCertificateInfo: data.printInfo,
                                region: "center",
                                format: data.printInfo.claimCertificatePrintSettings.certificate_print_format,
                                style: "width:100%;border:none;height:100%;overflow:auto;"
                            });

                            self.addChild(claimCertificatePrintInfoForm);

                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }else{
                this.addChild(container);
            }
        },
        validateOpeningNewClaimCert: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                }),
                xhrContent = {
                    pid: this.project.id
                };

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'postContract/checkForPendingClaimCertificates',
                    handleAs: 'json',
                    content: xhrContent,
                    load: function(data){
                        if(data.hasPendingClaimCertificates && !data.hasInProgressClaimCertificates) {
                            buildspace.dialog.confirm(nls.confirmation, '<p>' + nls.startNewClaimCertConfirmation + '</p><p>' + nls.cannotBeUndoneWishToContinue + '</p>', 120, 380, function(){
                                self.openClaimCertificateForm();
                            });
                        }
                        else{
                            self.openClaimCertificateForm();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        openClaimCertificateForm: function(){
            var container = dijit.byId('postContractClaimCertificate' + this.project.id + '-stackContainer');

            if (container) {

                var page = dijit.byId('claimCertificatePage-' + this.project.id);

                if(page){
                    container.removeChild(page);
                    page.destroyRecursive();
                }

                var formContainer = new dijit.layout.BorderContainer({
                    title: nls.claimCertificate,
                    id: 'claimCertificatePage-' + this.project.id,
                    region: "center",
                    style: "padding:0;width:100%;margin:0;border:none;height:100%;",
                    gutters: false
                });

                var claimCertificateForm = new ClaimCertificateForm({
                    claimCertificateContainer: this,
                    region: "center",
                    style: "width:100%;border:none;height:100%;overflow:auto;",
                });

                var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;padding:2px;width:100%;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.save,
                        id: 'saveClaimCertificate-button',
                        iconClass: "icon-16-container icon-16-save",
                        onClick: dojo.hitch(claimCertificateForm, 'save')
                    })
                );

                formContainer.addChild(toolbar);
                formContainer.addChild(claimCertificateForm);

                container.addChild(formContainer);

                container.selectChild('claimCertificatePage-' + this.project.id);
            }
        },
        openClaimCertificatePrintSettingsForm: function(){
            var container = dijit.byId('postContractClaimCertificate' + this.project.id + '-stackContainer');

            if (container) {

                var page = dijit.byId('claimCertificatePage-' + this.project.id);

                if(page){
                    container.removeChild(page);
                    page.destroyRecursive();
                }

                var formContainer = new dijit.layout.BorderContainer({
                    title: nls.claimCertificatePrintSettings,
                    id: 'claimCertificatePage-' + this.project.id,
                    region: "center",
                    style: "padding:0;width:100%;margin:0;border:none;height:100%;",
                    gutters: false
                });

                var claimCertificatePrintSettingForm = new ClaimCertificatePrintSettingForm({
                    project: this.project,
                    region: "center",
                    style: "width:100%;border:none;height:100%;overflow:auto;"
                });

                var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;padding:2px;width:100%;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.save,
                        iconClass: "icon-16-container icon-16-save",
                        onClick: dojo.hitch(claimCertificatePrintSettingForm, 'save')
                    })
                );

                formContainer.addChild(toolbar);
                formContainer.addChild(claimCertificatePrintSettingForm);

                container.addChild(formContainer);

                container.selectChild('claimCertificatePage-' + this.project.id);
            }
        },
        openClaimCertificateViewForm: function(claimCertificateObj){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                }),
                xhrContent = {
                    id: claimCertificateObj.id
                };

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'postContract/claimCertificateInfo',
                    handleAs: 'json',
                    content: xhrContent,
                    load: function(data){
                        var container = dijit.byId('postContractClaimCertificate' + self.project.id + '-stackContainer');

                        if (container) {

                            var page = dijit.byId('claimCertificatePage-' + self.project.id);
            
                            if(page){
                                container.removeChild(page);
                                page.destroyRecursive();
                            }
            
                            var status = Array.isArray(claimCertificateObj.status) ? claimCertificateObj.status[0] : claimCertificateObj.status;
                            var fontColor;
                            switch(status){
                                case buildspace.constants.CLAIM_CERTIFICATE_STATUS_APPROVED:
                                    fontColor = "#69FA72";
                                    break;
                                case buildspace.constants.CLAIM_CERTIFICATE_STATUS_REJECTED:
                                    fontColor = "#cc1313";
                                    break;
                                default:
                                    fontColor = "#F7D76D";
                            }
            
                            var formContainer = new dijit.layout.TabContainer({
                                title: nls.claimCertificate+ " :: "+claimCertificateObj.version+" ( <span style='color:"+fontColor+"!important;'>"+claimCertificateObj.status_txt+"</span> )",
                                id: 'claimCertificatePage-' + self.project.id,
                                region: "center",
                                style: "padding:0;width:100%;margin:0;border:none;height:100%;",
                                gutters: false
                            });
            
                            var claimCertificateFormBorderContainer = new dijit.layout.BorderContainer({
                                title: nls.claimCertificateInfo,
                                style:"padding:0px;width:400px;height:80px;",
                                gutters: false
                            });
            
                            var claimCertificatePrintInfoFormBorderContainer = new dijit.layout.BorderContainer({
                                title: nls.claimCertificatePrintInfo,
                                style:"padding:0px;width:400px;height:80px;",
                                gutters: false
                            });

                            var claimCertificateForm = new ClaimCertificateViewForm({
                                project: self.project,
                                claimCertificateInfo: data.certInfo,
                                claimCertificate: claimCertificateObj,
                                claimCertificateContainer: self,
                                isTopManagementVerifierEditable: data.isTopManagementVerifierEditable,
                                region: "center",
                                style: "width:100%;border:none;height:100%;overflow:auto;"
                            });

                            var claimCertificatePrintInfoForm = new ClaimCertificatePrintInfoForm({
                                title: nls.claimCertificatePrintInfo,
                                project: this.project,
                                claimCertificateInfo: data.printInfo,
                                claimCertificate: claimCertificateObj,
                                claimCertificateContainer: this,
                                region: "center",
                                format: data.printInfo.claimCertificatePrintSettings.certificate_print_format,
                                style: "width:100%;border:none;height:100%;overflow:auto;"
                            });

                            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;padding:2px;width:100%;"});
            
                            if(parseInt(status) == buildspace.constants.CLAIM_CERTIFICATE_STATUS_IN_PROGRESS && (!self.locked)){
                                toolbar.addChild(
                                    new dijit.form.Button({
                                        label: nls.submit,
                                        iconClass: "icon-16-container icon-16-save",
                                        disabled: !data.certInfo.can_submit,
                                        onClick: dojo.hitch(claimCertificateForm, "submitForApproval")
                                    })
                                );
                                toolbar.addChild(new dijit.ToolbarSeparator());
            
                                toolbar.addChild(
                                    new dijit.form.Button({
                                        label: nls.edit,
                                        iconClass: "icon-16-container icon-16-edit",
                                        onClick: dojo.hitch(self, "openClaimCertificateForm")
                                    })
                                );
                                toolbar.addChild(new dijit.ToolbarSeparator());
                            }

                            toolbar.addChild(
                                new dijit.form.Button({
                                    label: nls.invoiceInformation,
                                    iconClass: "icon-16-container icon-16-list",
                                    onClick: dojo.hitch(self, "editInvoiceInformation", data.certInfo)
                                })
                            );

                            toolbar.addChild(
                                new dijit.form.Button({
                                    label: nls.exportToExcel,
                                    iconClass: "icon-16-container icon-16-spreadsheet",
                                    onClick: dojo.hitch(self, "exportToExcel", claimCertificateObj)
                                })
                            );

                            toolbar.addChild(new dijit.ToolbarSeparator());
                            toolbar.addChild(
                                new dijit.form.Button({
                                    label: nls.exportClaims,
                                    iconClass: "icon-16-container icon-16-export",
                                    onClick: function(){
                                        var claimExportDialog = ClaimExportDialog({
                                            project: self.project,
                                            claimCertificateObj: claimCertificateObj
                                        });

                                        claimExportDialog.show();
                                    }
                                })
                            );

                            toolbar.addChild(new dijit.ToolbarSeparator());
                            toolbar.addChild(
                                new dijit.form.Button({
                                    id: 'claimCertificatePage-' + self.project.id + '-importClaimsButton',
                                    label: nls.importClaims,
                                    iconClass:"icon-16-container icon-16-import",
                                    disabled: !data.importedClaim.can_submit,
                                    onClick: function(e){
                                        var claimImportDialog = new ClaimImportDialog({
                                            importUrl: "claimTransfer/importClaims/revision_id/"+claimCertificateObj.claim_revision_id,
                                            title: nls.importClaims,
                                            rootProject: self.project
                                        });

                                        claimImportDialog.show();
                                    }
                                })
                            );

                            var printInfoToolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;padding:2px;width:100%;"});
                            printInfoToolbar.addChild(
                                new dijit.form.Button({
                                    label: nls.print,
                                    iconClass: "icon-16-container icon-16-print",
                                    onClick: dojo.hitch(self, "printToPDF", claimCertificateObj)
                                })
                            );
            
                            claimCertificateFormBorderContainer.addChild(toolbar);
                            claimCertificateFormBorderContainer.addChild(claimCertificateForm);
                            
                            claimCertificatePrintInfoFormBorderContainer.addChild(printInfoToolbar);
                            claimCertificatePrintInfoFormBorderContainer.addChild(claimCertificatePrintInfoForm);

                            formContainer.addChild(claimCertificateFormBorderContainer);
                            formContainer.addChild(claimCertificatePrintInfoFormBorderContainer);

                            if(data.showAccountCodeSettings) {
                                formContainer.addChild(
                                    new AccountCodeSettingsTabContainer({
                                        title: nls.accountCodeSettings,
                                        id: String(self.project.id)+'-AccountCodeSettings',
                                        project: self.project,
                                        claimCertificate: claimCertificateObj,
                                    })
                                );
                            }

                            formContainer.addChild(new ClaimCertificateNoteContainer({
                                id: String(claimCertificateObj.id)+'-ClainCertificateNote',
                                claimCertificate: claimCertificateObj
                            }));

                            container.addChild(formContainer);
                            container.selectChild('claimCertificatePage-' + self.project.id);
                        }

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        editInvoiceInformation: function(claimCertificateInfo){
            EditInvoiceInformationDialog({
                claimCertificateInfo: claimCertificateInfo,
            }).show();
        },
        exportToExcel: function(claimCertificateObj){
            ExportExcelDialog({
                project: this.project,
                claimCertificateObj: claimCertificateObj,
                fileName: 'Claim_Certificate',
                url: 'ClaimCertificateXls/'+this.project.id+'/'+this.project._csrf_token+'/'+claimCertificateObj.id+'/',
            }).show();
        },
        printToPDF: function(claimCertificateObj){
            window.open('ClaimCertificatePDF/'+this.project.id+'/'+claimCertificateObj.id, '_blank');
            return window.focus();
        },
        removeStackContainerChildren: function(){
            var container = dijit.byId('postContractClaimCertificate' + this.project.id + '-stackContainer');

            if (container) {
                var page = dijit.byId('claimCertificatePage-' + this.project.id);
                if (page) {
                    container.removeChild(page);
                    page.destroyRecursive();
                }
            }
        },
        closeBillTabs: function() {
            return lang.hitch(this.workArea, 'removeBillTab')();
        }
    });
});
