define('buildspace/apps/CostData/WorkArea',[
    'dojo/_base/declare',
    './Breakdown',
    './ProjectParticulars',
    './ProjectInformation',
    './CostDataInformationForm',
    'buildspace/apps/Attachment/UploadAttachmentContainer',
    'dojo/i18n!buildspace/nls/CostData'], function(declare, Breakdown, ProjectParticulars, ProjectInformation, CostDataInformationForm, AttachmentsContainer, nls){

    return declare('buildspace.apps.CostData.WorkArea', dijit.layout.TabContainer, {
        region: "center",
        style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        costData: null,
        editable: false,
        postCreate: function(){
            var breakdown = this.breakdown = Breakdown({
                title: nls.breakdown,
                editable: this.editable,
                costData: this.costData
            });
            this.addChild(breakdown);

            var projectParticulars = this.projectParticulars = ProjectParticulars({
                title: nls.projectParticulars,
                editable: this.editable,
                costData: this.costData
            });
            this.addChild(projectParticulars);

            var projectInfo = this.projectInfo = ProjectInformation({
                title: nls.projectInfo,
                editable: this.editable,
                costData: this.costData
            });
            this.addChild(projectInfo);

            var costDataInformationForm = CostDataInformationForm({
                title: nls.costDataInformation,
                disableEditing: !this.editable,
                costData: this.costData
            });
            this.addChild(costDataInformationForm);

            var attachments = this.attachments = AttachmentsContainer({
                title: nls.attachments,
                disableEditing: !this.editable,
                item: {
                    id: this.costData.id,
                    class: this.costData.class
                },
                canClose: false
            });
            this.addChild(attachments);
        }
    });
});