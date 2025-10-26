define('buildspace/apps/PostContract/VariationOrder/TopManagementVerifier',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/FilteringSelect",
    "dojo/text!./templates/topManagementVerifier.html",
    "dojo/text!../templates/topManagementVerifierSelectionRow.html",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, html, domStyle, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, FilteringSelect, topManagementVerifierTemplate, topManagementVerifierSelectionRowTemplate, nls){
    var TopManagementVerifierForm = declare('buildspace.apps.PostContract.VariationOrder.TopManagementVerifier.Form', [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        region: 'center',
        templateString: topManagementVerifierTemplate,
        baseClass: 'buildspace-form',
        nls: nls,
        title:null,
        project: null,
        variationOrder: null,
        constructor: function() {
            this.inherited(arguments);
        },
        postCreate: function() {
            this.inherited(arguments);
            this.populatePostContractClaimTopManagementVerifierRecords();

            if(this.variationOrder.can_be_edited[0]) {
                this.btnAddVerifier.set('onClick', dojo.hitch(this, "addVerifierRow"));
            } else {
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
                    url: 'variationOrder/getSavedTopManagementVerifiers',
                    content: {
                        variationOrderId: self.variationOrder.id,
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.records.length > 0) {
                            dojo.forEach(resp.records, function(record){
                                var verifierSelectionRow = new TopManagementVerifierSelectionRow({
                                    editMode: false,
                                    locked: !self.variationOrder.can_be_edited[0],
                                    addVerifierButton: self.btnAddVerifier,
                                    project: self.project,
                                    variationOrder: self.variationOrder,
                                    recordInfo: record,
                                    topManagementVerifierForm: self,
                                    _csrf_token: self.variationOrder._csrf_token,
                                });
    
                                verifierSelectionRow.placeAt(self.topManagementVerifierRowContainer);
                            });
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
                project: self.project,
                variationOrder: self.variationOrder,
                topManagementVerifierForm: self,
                _csrf_token: self.variationOrder._csrf_token,
            });

            verifierSelectionRow.placeAt(self.topManagementVerifierRowContainer);

            self.btnAddVerifier.set('disabled', true);
        },
    });
    
    var TopManagementVerifierSelectionRow = declare('buildspace.apps.PostContract.VariationOrder.TopManagementVerifier.SelectionRow', [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
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
        variationOrder: null,
        topManagementVerifierForm: null,
        recordInfo: null,
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
                    url:"variationOrder/getTopManagementVerifiers/pid/" + self.project.id,
                });

                var verifierSelect = new FilteringSelect({
                    name: "variationOrder[top_management_verifiers][]",
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
                url:"variationOrder/getTopManagementVerifiers/pid/" + self.project.id,
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
            var url = self.recordInfo ? 'variationOrder/updateTopManagementVerifier' : 'variationOrder/saveTopManagementVerifier';

            pb.show().then(function(){
                dojo.xhrPost({
                    url: url,
                    content: {
                        'post_contract_claim_top_management_verifier[id]': recordId,
                        'post_contract_claim_top_management_verifier[user_id]': selectedVerifierId,
                        'post_contract_claim_top_management_verifier[objectId]' : self.variationOrder.id,
                        'post_contract_claim_top_management_verifier[_csrf_token]': self._csrf_token,
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success) {
                            self.topManagementVerifierForm.populatePostContractClaimTopManagementVerifierRecords();
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
                            url: 'variationOrder/deleteTopManagementVerifier',
                            content: {
                                id: self.recordInfo.id,
                            },
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success === true) {
                                    self.topManagementVerifierForm.populatePostContractClaimTopManagementVerifierRecords();

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

    return declare('buildspace.apps.PostContract.VariationOrder.TopManagementVerifier', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;border:0px;width:100%;height:100%;",
        gutters: false,
        title: null,
        project: null,
        variationOrder: null,
        locked: false,
        constructor: function() {
            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            this.addChild(new TopManagementVerifierForm({
                project: self.project,
                variationOrder: self.variationOrder,
            }));
        }
    });
});
