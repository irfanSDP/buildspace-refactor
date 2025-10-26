define('buildspace/apps/Tendering/newPostContractFormDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/on",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/registry",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/FilteringSelect",
    "dijit/form/NumberTextBox",
    "dijit/form/CheckBox",
    "dijit/layout/ContentPane",
    "buildspace/widget/grid/cells/Formatter",
    "dijit/form/DropDownButton",
    "dijit/Menu",
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dijit/PopupMenuItem",
    "dojo/text!./templates/newPostContractForm.html",
    'dojo/i18n!buildspace/nls/NewPostContractForm'
], function(declare, lang, connect, when, on, html, dom, keys, domStyle, registry, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, FilteringSelect, NumberTextBox, CheckBox, ContentPane, GridFormatter, DropDownButton, Menu, DropDownMenu, MenuItem, PopupMenuItem, template, nls){

    var Form = declare("buildspace.apps.Tendering.NewContractForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        project: null,
        selectedContractor: null,
        eTenderWaiverSelected: null,
        eAuctionWaiverSelected: null,
        nls: nls,
        startup: function(){
            this.inherited(arguments);

            html.set(this.projectTitleNode, String(this.project.title));

            var self = this, project = this.project;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'tendering/getSelectedContractor',
                    handleAs: 'json',
                    content: {
                        project_id: project.id
                    }
                }).then(function(data){
                    var contractorName = data.name ? String(data.name) : "";
                    html.set(self.selectedContractorNameNode, contractorName);
                    
                    domStyle.set(self.awardedTenderAlternativeRowNode, {'display': 'none'});

                    if(parseInt(data.tender_alternative_id) > 0){
                        html.set(self.awardedTenderAlternativeTitleNode, String(data.tender_alternative_title));
                        domStyle.set(self.awardedTenderAlternativeRowNode, {'display': ''});
                    }

                    dojo.xhrGet({
                        url: "tendering/newPostContractFormInformationForm",
                        handleAs: "json",
                        content: {
                            project_id: project.id,
                            use_original_rate: self.dialogObj.useOriginalRate,
                            without_not_listed_item: self.dialogObj.withoutNotListedItem
                        },
                        load: function(data) {
                            self.initWaiverOptionState();

                            self.eTenderWaiverOption.set('checked', (data.eTenderWaiverOption != null));
                            self.eAuctionWaiverOption.set('checked', (data.eAuctionWaiverOption != null));

                            if(data.eTenderWaiverOption != null) {
                                self.eTenderWaiverOptionSelect.set('value', data.eTenderWaiverOption);
                            }

                            if(data.eAuctionWaiverOption != null) {
                                self.eAuctionWaiverOptionSelect.set('value', data.eAuctionWaiverOption);
                            }

                            self.eTenderWaiverUserDefinedOptionInput.set('value', data.eTenderWaiverUserDefinedOption);
                            self.eAuctionWaiverUserDefinedOptionInput.set('value', data.eAuctionWaiverUserDefinedOption);

                            if(data.eproject_in_post_contract){
                                var keysToDelete = ["new_post_contract_form_information[contract_period_from]", "new_post_contract_form_information[contract_period_to]"];
                                for(var key in self.newPostContractForm.formWidgets){
                                    var idx = keysToDelete.indexOf(key);
                                    if(idx != -1){
                                        self.newPostContractForm.formWidgets[key].widget.destroyRecursive(true);
                                        delete self.newPostContractForm.formWidgets[key];
                                    }
                                }

                                html.set(dom.byId("new_post_contract_form_information-contract_period_from-column"), data.commencement_date);
                                html.set(dom.byId("new_post_contract_form_information-contract_period_to-column"), data.completion_date);

                                if(data.selected_trade){
                                    self.newPostContractForm.formWidgets["new_post_contract_form_information[pre_defined_location_code_id]"].widget.destroyRecursive(true);
                                    delete self.newPostContractForm.formWidgets["new_post_contract_form_information[pre_defined_location_code_id]"];

                                    html.set(dom.byId("new_post_contract_form_information-trade-column"), data.selected_trade);
                                }

                                self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_hours]"].widget.destroyRecursive(true);
                                delete self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_hours]"];

                                html.set(dom.byId("new_post_contract_form_information-labour_rate_hours"), data.labour_rates.hours)

                                self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_skilled_normal]"].widget.destroyRecursive(true);
                                delete self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_skilled_normal]"];

                                html.set(dom.byId("new_post_contract_form_information-labour_rate_skilled_normal"), data.labour_rates.skilled.normal);

                                self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_skilled_ot]"].widget.destroyRecursive(true);
                                delete self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_skilled_ot]"];

                                html.set(dom.byId("new_post_contract_form_information-labour_rate_skilled_ot"), data.labour_rates.skilled.ot);

                                self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_semi_skilled_normal]"].widget.destroyRecursive(true);
                                delete self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_semi_skilled_normal]"];

                                html.set(dom.byId("new_post_contract_form_information-labour_rate_semi_skilled_normal"), data.labour_rates.semi_skilled.normal);

                                self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_semi_skilled_ot]"].widget.destroyRecursive(true);
                                delete self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_semi_skilled_ot]"];

                                html.set(dom.byId("new_post_contract_form_information-labour_rate_semi_skilled_ot"), data.labour_rates.semi_skilled.ot);

                                self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_labour_normal]"].widget.destroyRecursive(true);
                                delete self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_labour_normal]"];

                                html.set(dom.byId("new_post_contract_form_information-labour_rate_labour_normal"), data.labour_rates.labour.normal);

                                self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_labour_ot]"].widget.destroyRecursive(true);
                                delete self.newPostContractForm.formWidgets["new_post_contract_form_information[labour_rate_labour_ot]"];

                                html.set(dom.byId("new_post_contract_form_information-labour_rate_labour_ot"), data.labour_rates.labour.ot);
                            }

                            if(data.labour_rates.len < 1){
                                data.formValues["new_post_contract_form_information[labour_rate_hours]"] = data.labour_rates.hours;
                                data.formValues["new_post_contract_form_information[labour_rate_skilled_normal]"] = data.labour_rates.skilled.normal;
                                data.formValues["new_post_contract_form_information[labour_rate_skilled_ot]"] = data.labour_rates.skilled.ot;
                                data.formValues["new_post_contract_form_information[labour_rate_semi_skilled_normal]"] = data.labour_rates.semi_skilled.normal;
                                data.formValues["new_post_contract_form_information[labour_rate_semi_skilled_ot]"] = data.labour_rates.semi_skilled.ot;
                                data.formValues["new_post_contract_form_information[labour_rate_labour_normal]"] = data.labour_rates.labour.normal;
                                data.formValues["new_post_contract_form_information[labour_rate_labour_ot]"] = data.labour_rates.labour.ot;
                            }

                            self.newPostContractForm.setFormValues(data.formValues);

                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            });
        },
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var formTypeSelect = self.formTypeSelect = new FilteringSelect({
                name: "new_post_contract_form_information[type]",
                store: new dojo.data.ItemFileReadStore({
                    url:"tendering/getNewPostContractFormType"
                }),
                style: "padding:2px;",
                searchAttr: "name",
                readOnly: false,
                onChange: function(formType) {
                    var numberLabel;
                    switch(formType) {
                        case buildspace.constants.NEW_POST_CONTRACT_FORM_TYPE_WORK_ORDER:
                            numberLabel = nls.workOrderNo;
                            break;
                        case buildspace.constants.NEW_POST_CONTRACT_FORM_TYPE_CONTRACT_INFO:
                            numberLabel = nls.contractInfoNo;
                            break;
                        default:
                            numberLabel = nls.letterOfAwardNo;
                    }
                    html.set(dom.byId('label_number'), numberLabel);

                    var value = this.get("value");
                    var pb = buildspace.dialog.indeterminateProgressBar({
                        title: nls.pleaseWait+'...'
                    });
                    pb.show().then(function(){
                        dojo.xhrGet({
                            url: 'tendering/getNewPostContractFormNumber',
                            content: {
                                project_id: self.project.id,
                                form_type: value
                            },
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success) {
                                    registry.byId("form_number").set("value", resp.form_number);
                                }
                                pb.hide();
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        });
                    });
                }
            }).placeAt(self.formTypeSelectDivNode);

            registry.byId("form_number").on("change", function(){
                var formNumber = this.get("value");
                var formType = self.formTypeSelect.get("value");
                dojo.xhrGet({
                    url: 'tendering/getLetterOfAwardCode',
                    content: {
                        project_id: self.project.id,
                        form_type: formType,
                        form_number: formNumber
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success) {
                            dijit.byId("new_post_contract_form_information[reference]").set("value", resp.code);
                        }
                    },
                    error: function(error) {
                    }
                });
            });

            new FilteringSelect({
                id: "new_post_contract_form_information-pre_defined_location_code_id",
                name: "new_post_contract_form_information[pre_defined_location_code_id]",
                store: new dojo.data.ItemFileReadStore({
                    url:"tendering/getLocationTradesDropDownList"
                }),
                style: "padding:2px;",
                searchAttr: "name",
                readOnly: false
            }).placeAt(self.tradeSelectDivNode);

            new FilteringSelect({
                name: "new_post_contract_form_information[works_1]",
                store: new dojo.data.ItemFileReadStore({
                    url:"tendering/getSubPackageWorksDropDownList/type/" + buildspace.constants.SUB_PACKAGE_WORKS_TYPE_1
                }),
                style: "padding:2px;",
                searchAttr: "name",
                readOnly: false,
                required: false
            }).placeAt(self.worksSelectDivNode);

            new FilteringSelect({
                name: "new_post_contract_form_information[works_2]",
                store: new dojo.data.ItemFileReadStore({
                    url:"tendering/getSubPackageWorksDropDownList/type/" + buildspace.constants.SUB_PACKAGE_WORKS_TYPE_2
                }),
                style: "padding:2px;",
                searchAttr: "name",
                readOnly: false,
                required: false
            }).placeAt(self.works2SelectDivNode);
            
            new NumberTextBox({
                name: "new_post_contract_form_information[labour_rate_hours]",
                id: "new_post_contract_form_information[labour_rate_hours]",
                style: "padding:2px;width:70px;",
                value: 0,
                trim: true,
                readOnly: false,
                required: true
            }).placeAt(self.labourRateHoursDivNode);

            new NumberTextBox({
                name: "new_post_contract_form_information[labour_rate_skilled_normal]",
                id: "new_post_contract_form_information[labour_rate_skilled_normal]",
                style: "padding:2px;width:70px;",
                value: 0,
                trim: true,
                readOnly: false,
                required: true
            }).placeAt(self.labourRateSkilledNormalDivNode);

            new NumberTextBox({
                name: "new_post_contract_form_information[labour_rate_skilled_ot]",
                id: "new_post_contract_form_information[labour_rate_skilled_ot]",
                style: "padding:2px;width:70px;",
                value: 0,
                trim: true,
                readOnly: false,
                required: true
            }).placeAt(self.labourRateSkilledOTDivNode);

            new NumberTextBox({
                name: "new_post_contract_form_information[labour_rate_semi_skilled_normal]",
                id: "new_post_contract_form_information[labour_rate_semi_skilled_normal]",
                style: "padding:2px;width:70px;",
                value: 0,
                trim: true,
                readOnly: false,
                required: true
            }).placeAt(self.labourRateSemiSkilledNormalDivNode);

            new NumberTextBox({
                name: "new_post_contract_form_information[labour_rate_semi_skilled_ot]",
                id: "new_post_contract_form_information[labour_rate_semi_skilled_ot]",
                style: "padding:2px;width:70px;",
                value: 0,
                trim: true,
                readOnly: false,
                required: true
            }).placeAt(self.labourRateSemiSkilledOTDivNode);

            new NumberTextBox({
                name: "new_post_contract_form_information[labour_rate_labour_normal]",
                id: "new_post_contract_form_information[labour_rate_labour_normal]",
                style: "padding:2px;width:70px;",
                value: 0,
                trim: true,
                readOnly: false,
                required: true
            }).placeAt(self.labourRateLabourNormalDivNode);

            on(dijit.byId('new_post_contract_form_information[retention]'), 'keyup', function(){
                dijit.byId("new_post_contract_form_information[max_retention_sum]").set("value", dijit.byId("new_post_contract_form_information[retention]").textbox.value);
            });

            dijit.byId('includeVO').on('change', function(isChecked){
                var displayType = isChecked ? 'block' : 'none';
                var el = dom.byId("includeVODesription");
                if(el){
                    domStyle.set(el, "display", displayType);
                }
            });

            new NumberTextBox({
                name: "new_post_contract_form_information[labour_rate_labour_ot]",
                id: "new_post_contract_form_information[labour_rate_labour_ot]",
                style: "padding:2px;width:70px;",
                value: 0,
                trim: true,
                readOnly: false,
                required: true
            }).placeAt(self.labourRateLabourOTDivNode);

            var select = dijit.byId('new_post_contract_form_information-pre_defined_location_code_id');

            select.on("change", function(id){
                dojo.xhrGet({
                    url: 'tendering/getProjectLabourRateRecords',
                    handleAs: 'json',
                    content: {
                        trade_id: id,
                        project_id: self.project.id
                    }
                }).then(function(data){

                    if(data.projectLabourRates){
                        dijit.byId("new_post_contract_form_information[labour_rate_hours]").textbox.value = data.projectLabourRates.hours;

                        dijit.byId("new_post_contract_form_information[labour_rate_skilled_normal]").textbox.value = data.projectLabourRates.skilled.normal;

                        dijit.byId("new_post_contract_form_information[labour_rate_skilled_ot]").textbox.value = data.projectLabourRates.skilled.ot;

                        dijit.byId("new_post_contract_form_information[labour_rate_semi_skilled_normal]").textbox.value = data.projectLabourRates.semi_skilled.normal;

                        dijit.byId("new_post_contract_form_information[labour_rate_semi_skilled_ot]").textbox.value = data.projectLabourRates.semi_skilled.ot;
                        
                        dijit.byId("new_post_contract_form_information[labour_rate_labour_normal]").textbox.value = data.projectLabourRates.labour.normal;

                        dijit.byId("new_post_contract_form_information[labour_rate_labour_ot]").textbox.value = data.projectLabourRates.labour.ot;
                    }

                });
            });
            
            on(this.eTenderWaiverOption, "change", function(e){
                html.set(dom.byId("new_post_contract_form_information_error-e_tender_waiver"), '');
                self.toggleETenderWaiverOption(this.checked);
                self.toggleETenderWaiverDescription(this.checked);
            });

            on(this.eAuctionWaiverOption, "change", function(e){
                html.set(dom.byId("new_post_contract_form_information_error-e_auction_waiver"), '');
                self.toggleEAuctionWaiverOption(this.checked);
                self.toggleEAuctionWaiverDescription(this.checked);
            });

            var eTenderWaiverOptionSelect = self.eTenderWaiverOptionSelect = new FilteringSelect({
                name: "new_post_contract_form_information[eTenderWaiverOption]",
                store: new dojo.data.ItemFileReadStore({
                    url:"tendering/getWaiverOptionType/type/" + buildspace.constants.WAIVER_OPTION_TYPE_E_TENDER
                }),
                required: false,
                onChange: function(selection) {
                    var showUserDefinedOption = (selection == buildspace.constants.E_TENDER_WAIVER_OPTION_OTHERS);

                    self.toggleETenderWaiverUserDefinedOption(showUserDefinedOption);
                }
            }).placeAt(self.eTenderWaiverOptionSelectDivNode);

            var eAuctionWaiverOptionSelect = self.eAuctionWaiverOptionSelect = new FilteringSelect({
                name: "new_post_contract_form_information[eAuctionWaiverOption]",
                store: new dojo.data.ItemFileReadStore({
                    url:"tendering/getWaiverOptionType/type/" + buildspace.constants.WAIVER_OPTION_TYPE_E_AUCTION
                }),
                required: false,
                onChange: function(selection) {
                    var showUserDefinedOption = (selection == buildspace.constants.E_AUCTION_WAIVER_OPTION_OTHERS);

                    self.toggleEAuctionWaiverUserDefinedOption(showUserDefinedOption);
                }
            }).placeAt(self.eAuctionWaiverOptionSelectDivNode);
        },
        initWaiverOptionState: function() {
            this.toggleETenderWaiverOption(false);
            this.toggleETenderWaiverUserDefinedOption(false);
            this.toggleETenderWaiverDescription(false);

            this.toggleEAuctionWaiverOption(false);
            this.toggleEAuctionWaiverUserDefinedOption(false);
            this.toggleEAuctionWaiverDescription(false);
        },
        toggleETenderWaiverOption: function(showOption) {
            var displayOptionVal = showOption ? '' : 'none';

            domStyle.set(this.eTenderWaiverOptionLabel, "display", displayOptionVal);
            domStyle.set(this.eTenderWaiverOptionSelectDivNode, "display", displayOptionVal);
        },
        toggleETenderWaiverUserDefinedOption: function(showUserDefinedOption) {
            var displayOthersTextInput = showUserDefinedOption ? '' : 'none';

            domStyle.set(this.eTenderWaiverUserDefinedOptionLabel, "display", displayOthersTextInput);
            domStyle.set(this.eTenderWaiverOptionUserDefinedInputDiv, "display", displayOthersTextInput);
        },
        toggleETenderWaiverDescription: function(show) {
            var displayValue = show ? '' : 'none';

            domStyle.set(this.eTenderWaiverDescription, 'display', displayValue);
        },
        toggleEAuctionWaiverOption: function(showOption) {
            var displayOptionVal = showOption ? '' : 'none';

            domStyle.set(this.eAuctionWaiverOptionLabel, "display", displayOptionVal);
            domStyle.set(this.eAuctionWaiverOptionSelectDivNode, "display", displayOptionVal);
        },
        toggleEAuctionWaiverUserDefinedOption: function(showUserDefinedOption) {
            var displayOthersTextInput = showUserDefinedOption ? '' : 'none';

            domStyle.set(this.eAuctionWaiverUserDefinedOptionLabel, "display", displayOthersTextInput);
            domStyle.set(this.eAuctionWaiverOptionUserDefinedInputDiv, "display", displayOthersTextInput);
        },
        toggleEAuctionWaiverDescription: function(show) {
            var displayValue = show ? '' : 'none';

            domStyle.set(this.eAuctionWaiverDescription, 'display', displayValue);
        },
        save: function(){
            var self = this,
                values = dojo.formToObject(this.newPostContractForm.id);
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.submitting+'. '+nls.pleaseWait+'...'
            });

            lang.mixin(values, {
                "new_post_contract_form_information[project_structure_id]": this.project.id,
                pid: this.project.id,
                use_original_rate: this.dialogObj.useOriginalRate,
                without_not_listed_item: this.dialogObj.withoutNotListedItem
            });

            if(this.newPostContractForm.validate()){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'tendering/submitNewContractForm',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="new_post_contract_form_information_error-"]').forEach(function(node){
                                node.innerHTML = '';
                            });

                            if(resp.success) {
                                self.dialogObj.checkPublishRequirementDialog.sendNewPostContractFormForApproval();
                                self.dialogObj.hide();
                            } else {
                                for(var key in resp.errors){
                                    var msg = resp.errors[key];
                                    html.set(dom.byId("new_post_contract_form_information_error-"+key), msg);
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

    return declare('buildspace.apps.Tendering.ChooseNewPostContractFormTypeDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.postContract,
        rootProject: null,
        useOriginalRate: false,
        withoutNotListedItem: false,
        checkPublishRequirementDialog: null,
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
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:960px;height:520px;",
                gutters: false
            });

            var form = new Form({
                dialogObj: this,
                project: this.rootProject,
                style: "width:100%;border:none;height:100%;overflow:auto;"
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
                    onClick: dojo.hitch(form, 'save')
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
});
