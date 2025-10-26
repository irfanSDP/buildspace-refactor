var __hasProp = {}.hasOwnProperty;
define("buildspace/apps/PostContract/ClaimRevisionSettingsForm", [
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
    "dojo/text!./templates/claimRevisionSettingsForm.html",
    "dojo/text!./templates/claimRevisionSettingsRow.html",
    "dojo/i18n!buildspace/nls/PostContract"],
function(declare, html, dom, domConstruct, keys, request, domStyle, domAttr, domGeo, lang, Form, RadioButton, Select, ValidateTextBox, NumberTextBox, CurrencyTextBox, number, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, bindOn, claimRevisionSettingsTemplate, claimRevisionSettingRowTemplate, nls) {

    var maxRevision = buildspace.constants.MAX_CLAIM_REVISIONS;
    var ClaimRevisionSettingRowForm = declare("buildspace.apps.PostContract.ClaimRevisionSettingRowForm", [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        isNew: false,
        projectId: -1,
        revisionId: -1,
        postContractId: -1,
        region: "center",
        nls: nls,
        rootProject: null,
        revisionData: null,
        ClaimRevisionSettingsForm: null,
        _csrf_token: 0,
        constructor: function(args) {
            return args.templateString = claimRevisionSettingRowTemplate;
        },
        postCreate: function() {
            var self;
            this.inherited(arguments);
            self = this;

            this.setSelectedCurrentSelectedRevision();
            this.exitEditMode();
            this.updateRevisionStatusDescription(this.revisionData.locked_status);
            this.lockedStatusValue.set('value', this.revisionData.locked_status);

            return bindOn(this.showRevisionSelectedLink, 'click', function(e) {
                return self.assignNewSelectedRevision();
            });
        },
        assignNewSelectedRevision: function() {
            var self = this;
            var pb = new buildspace.dialog.indeterminateProgressBar({
                title: nls.processing+'...'
            });

            var postContent = {
                'post_contract_id': this.revisionData.post_contract_id,
                'revisionId': this.revisionId,
                'post_contract_claim_revision[post_contract_id]': this.revisionData.post_contract_id,
                'post_contract_claim_revision[current_selected_revision]': true,
                'post_contract_claim_revision[_csrf_token]': this._csrf_token
            };

            pb.show().then(function(){
                request.post('postContract/assignNewSelectedRevision', {
                    data: postContent,
                    handleAs: 'json',
                    preventCache: true
                }).then(function(response) {
                    self.ClaimRevisionSettingsForm.revisionDatas = response;
                    self.ClaimRevisionSettingsForm.clearOldRevisionAndAddNewRevision();
                    pb.hide();
                }, function(error) {
                    pb.hide();
                });
            });
        },
        save: function() {
            var baseForm = this.ClaimRevisionSettingsForm;
            var self = this;
            var pb = new buildspace.dialog.indeterminateProgressBar({
                title: nls.processing+'...'
            });

            var postContent = {
                'post_contract_id': this.revisionData.post_contract_id,
                'revisionId': this.revisionId,
                'post_contract_claim_revision[post_contract_id]': this.revisionData.post_contract_id,
                'post_contract_claim_revision[version]': this.revisionData.version,
                'post_contract_claim_revision[locked_status]': this.lockedStatusValue.value,
                'post_contract_claim_revision[_csrf_token]': this._csrf_token
            };

            pb.show().then(function(){
                request.post('postContract/saveClaimRevision', {
                    data: postContent,
                    handleAs: 'json',
                    preventCache: true
                }).then(function(response) {
                    self.revisionId = response.item.id;
                    self.updateRevisionStatusDescription(response.item.locked_status);
                    html.set(self.updatedAtView, response.item.updated_at);
                    lang.hitch(baseForm, 'closeElementItemGrid')();

                    self.exitEditMode();

                    pb.hide();
                }, function(error) {
                    self.exitEditMode();
                    pb.hide();
                });
            });
        },
        setSelectedCurrentSelectedRevision: function() {
            if (this.revisionData.selected) {
                domStyle.set(this.showRevisionSelectedMarker, "display", "block");

                var container = dijit.byId(this.rootProject.id[0]+'-ProjectRevision');
                container.set('title', nls.claimRevision + '::' +nls.version + ' ' + this.revisionData.version);

                return domStyle.set(this.showRevisionSelectedLink, "display", "none");
            } else {
                domStyle.set(this.showRevisionSelectedMarker, "display", "none");
                return domStyle.set(this.showRevisionSelectedLink, "display", "block");
            }
        },
        enterEditMode: function() {
            return this.setupEditMode();
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
        },
        updateRevisionStatusDescription: function(revisionStatus) {
            var statusLabel = revisionStatus ? nls.addendumLocked : nls.addendumProgressing;
            html.set(this.addendumStatusView, statusLabel);
            if (revisionStatus && this.ClaimRevisionSettingsForm.tableCount !== (maxRevision + 2)) {
                this.ClaimRevisionSettingsForm.addClaimButton.set('disabled', false);
            } else {
                this.ClaimRevisionSettingsForm.addClaimButton.set('disabled', true);
            }
        },
        hideActionButton: function() {
            domStyle.set(this.editButton.domNode, "display", "none");
            domStyle.set(this.saveButton.domNode, "display", "none");
            domStyle.set(this.noActionLabel, "display", "");
        }
    });

    return declare("buildspace.apps.PostContract.ClaimRevisionSettingsForm", [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        projectId: -1,
        baseClass: "buildspace-form",
        style: "padding:5px;overflow:auto;border:0px;",
        region: "center",
        rootProject: null,
        nls: nls,
        workarea: null,
        revisionDatas: null,
        postContractId: null,
        _csrf_token: 0,
        tableCount: 1,
        rowArray: [],
        constructor: function(args) {
            return args.templateString = claimRevisionSettingsTemplate;
        },
        postMixInProperties: function() {
            this.inherited(arguments);
            return this.rowArray[this.projectId] = [];
        },
        postCreate: function() {
            this.inherited(arguments);
            this.masterGenerateClaimRevisionTableRow();
        },
        masterGenerateClaimRevisionTableRow: function() {
            var _this = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + "..."
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "postContract/getClaimRevisionLists",
                    handleAs: "json",
                    content: {
                        id: _this.projectId
                    },
                    load: function(data) {
                        _this.revisionDatas = data;
                        _this.populateClaimRevisionTableRow();
                        _this.postContractId = data.postContractId;
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        addClaim: function() {
            var version, key, pb, postContent, self;
            self = this;
            key = this.rowArray[this.projectId].length;
            key = key === 1 ? 1 : key;
            version = this.tableCount;
            pb = new buildspace.dialog.indeterminateProgressBar({
                title: nls.processing+'...'
            });

            postContent = {
                'revisionId': -1,
                'post_contract_id': this.postContractId,
                'post_contract_claim_revision[post_contract_id]': this.postContractId,
                'post_contract_claim_revision[version]': version,
                'post_contract_claim_revision[locked_status]': false,
                'post_contract_claim_revision[_csrf_token]': this._csrf_token
            };

            pb.show().then(function(){
                request.post('postContract/saveClaimRevision', {
                    data: postContent,
                    handleAs: 'json',
                    preventCache: true
                }).then(function(response) {
                    self.revisionDatas = response;
                    self.clearOldRevisionAndAddNewRevision();
                    pb.hide();
                }, function(error) {
                    pb.hide();
                });
            });
        },
        clearOldRevisionAndAddNewRevision: function() {
            this.rowArray[this.projectId] = [];
            this.tableCount = 1;
            dojo.empty(this.tableContainer);
            this.populateClaimRevisionTableRow();
            return lang.hitch(this, 'closeElementItemGrid')();
        },
        populateClaimRevisionTableRow: function() {
            var form, key, largeString, lastRowStatusValue, self, value, _ref;
            self = this;
            lastRowStatusValue = null;
            largeString = "";
            this._csrf_token = this.revisionDatas.form.csrf_token;
            _ref = this.revisionDatas.claimRevisions;
            for (key in _ref) {
                if (!__hasProp.call(_ref, key)) continue;
                value = _ref[key];
                form = this.rowArray[this.projectId][key] = new ClaimRevisionSettingRowForm({
                    count: this.tableCount,
                    rootProject: this.rootProject,
                    projectId: this.projectId,
                    revisionId: value.id,
                    postContractId: value.post_contract_id,
                    revisionData: value,
                    ClaimRevisionSettingsForm: this,
                    _csrf_token: this.revisionDatas.form.csrf_token
                });
                lastRowStatusValue = value.locked_status;
                if (key > 0) {
                    this.rowArray[this.projectId][key - 1].hideActionButton();
                }
                this.addTableRow(form);
            }
            if (!value.locked_status || this.tableCount === (maxRevision + 2)) {
                this.addClaimButton.set('disabled', true);
            }
        },
        addTableRow: function(form) {
            domConstruct.place(form.domNode, this.tableContainer);
            this.tableCount++;
        },
        closeElementItemGrid: function() {
            return lang.hitch(this.workarea, 'removeBillTab')();
        }
    });
});