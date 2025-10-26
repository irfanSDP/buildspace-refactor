define('buildspace/apps/Approval/LetterOfAward/MainInformation',[
    'dojo/_base/declare',
    "dojo/dom-style",
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!../../Approval/LetterOfAward/templates/mainInformation.html",
    'dojo/i18n!buildspace/nls/NewPostContractForm'
], function(declare, domStyle, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls){

    var MainInfoForm = declare("buildspace.apps.Approval.LetterOfAward.MainInformationForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        style: "border:none;padding:5px;overflow:auto;",
        nls: nls,
        postCreate: function() {
            this.inherited(arguments);
            
            if(this.eTenderWaiverOption == null) {
                domStyle.set(this.eTenderWaiverOptionLabel, 'display', 'none');
                domStyle.set(this.eTenderWaiverOptionDescription, 'display', 'none');
            }

            if(this.eAuctionWaiverOption == null) {
                domStyle.set(this.eAuctionWaiverOptionLabel, 'display', 'none');
                domStyle.set(this.eAuctionWaiverOptionDescription, 'display', 'none');
            }

            if(this.eTenderWaiverUserDefinedOption == null) {
                domStyle.set(this.eTenderWaiverUserDefinedOptionLabel, "display", 'none');
                domStyle.set(this.eTenderWaiverUserDefinedOptionDescription, "display", 'none');
            }

            if(this.eAuctionWaiverUserDefinedOption == null) {
                domStyle.set(this.eAuctionWaiverUserDefinedOptionLabel, "display", 'none');
                domStyle.set(this.eAuctionWaiverUserDefinedOptionDescription, "display", 'none');
            }

            if(parseInt(this.tender_alternative_id) > 0) {
                domStyle.set(this.awardedTenderAlternativeRowNode, 'display', '');
            }
        },
    });

    return declare('buildspace.apps.Approval.LetterOfAward.MainInformation', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;border:none;width:100%;height:100%;",
        region: "center",
        gutters: false,
        rootProject: null,
        explorer: null,
        data: null,
        postCreate: function(){
            this.inherited(arguments);

            var self = this;

            dojo.xhrGet({
                url: "tendering/getLetterOfAwardInformation",
                content: { project_id: self.rootProject.id },
                handleAs: "json"
            }).then(function(data){
                var mainInformationForm = self.mainInformationForm = new MainInfoForm({
                    rootProject: self.rootProject,
                    project_owner: data.project_owner,
                    title: data.title,
                    sub_con: data.sub_con,
                    type: data.type,
                    reference: data.reference,
                    contract_period_from: data.contract_period_from,
                    contract_period_to: data.contract_period_to,
                    awarded_date: data.awarded_date,
                    trade: data.trade,
                    works: data.works,
                    works_2: data.works_2,
                    creditor_code: data.creditor_code,
                    contract_sum: data.contract_sum,
                    retention: data.retention,
                    max_retention_sum: data.max_retention_sum,
                    remarks: data.remarks,
                    submitted_at: data.submitted_at,
                    submitted_by: data.submitted_by,
                    normal_working_hours:data.project_labour_rates.hours,
                    skilled_normal_rates:data.project_labour_rates.skilled['normal'],
                    skilled_ot_rates:data.project_labour_rates.skilled['ot'],
                    semi_skilled_normal_rates:data.project_labour_rates.semi_skilled['normal'],
                    semi_skilled_ot_rates:data.project_labour_rates.semi_skilled['ot'],
                    labour_normal_rates:data.project_labour_rates.labour['normal'],
                    labour_ot_rates:data.project_labour_rates.labour['ot'],
                    includeVO: data.includeVO ? 'checked' : '',
                    includeMaterialOnSite: data.includeMaterialOnSite ? 'checked' : '',
                    eTenderWaiverOptionChecked: (data.eTenderWaiverOption != null) ? 'checked' : '',
                    eAuctionWaiverOptionChecked: (data.eAuctionWaiverOption != null) ? 'checked' : '',
                    eTenderWaiverOption: data.eTenderWaiverOption,
                    eAuctionWaiverOption: data.eAuctionWaiverOption,
                    eTenderWaiverUserDefinedOption: data.eTenderWaiverUserDefinedOption,
                    eAuctionWaiverUserDefinedOption: data.eAuctionWaiverUserDefinedOption,
                    tender_alternative_id: parseInt(data.awarded_tender_alternative_id),
                    tender_alternative_title: data.awarded_tender_alternative_title
                });

                self.addChild(mainInformationForm);
            });
        },
    });
});
