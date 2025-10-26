define('buildspace/apps/Approval/LetterOfAward/ViewArea',[
    'dojo/_base/declare',
    './MainInformation',
    'buildspace/apps/ViewTenderer/ProjectBreakdown',
    'buildspace/apps/ViewTenderer',
    'dojo/i18n!buildspace/nls/Approval'], function(declare, MainInformation, ViewTendererProjectBreakdown, ViewTenderer, nls){

    return declare('buildspace.apps.Approval.LetterOfAward.ViewArea', dijit.layout.TabContainer, {
        region: "center",
        nested: true,
        style: "padding:0;border:none;margin:0;width:100%;height:100%;",
        rootProject: null,
        postCreate: function(){
            this.inherited(arguments);

            var project = this.rootProject;
            var self = this;

            this.addChild(new MainInformation({
                selected: true,
                title: nls.mainInformation,
                rootProject: project,
                id: 'letterOfAward-' + project.id
            }));

            var awardedTenderAlternativeId = (project.hasOwnProperty('awarded_tender_alternative_id') && !isNaN(parseInt(String(project.awarded_tender_alternative_id)))) ? parseInt(String(project.awarded_tender_alternative_id)) : -1;
            
            if(awardedTenderAlternativeId > 0){
                dojo.xhrGet({
                    url: "getTenderAlternative/"+awardedTenderAlternativeId,
                    handleAs: "json"
                }).then(function(tenderAlternative){
                    self.generateProjectBreakdown(tenderAlternative);
                });
            }else{
                this.generateProjectBreakdown(null);
            }
        },
        generateProjectBreakdown: function(tenderAlternative){
            this.addChild(new ViewTendererProjectBreakdown({
                title: nls.breakdown,
                rootProject: this.rootProject,
                tenderAlternative: tenderAlternative,
                type: parseInt(String(this.rootProject.tender_type_id))
            }));
        }
    });
});
