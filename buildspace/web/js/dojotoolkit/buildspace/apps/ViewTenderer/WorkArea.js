define('buildspace/apps/ViewTenderer/WorkArea',[
    'dojo/_base/declare',
    './ProjectBreakdown',
    'dojo/i18n!buildspace/nls/ViewTenderer'], function(declare, ProjectBreakdown, nls){

    return declare('buildspace.apps.ViewTenderer.WorkArea', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        rootProject: null,
        tenderAlternative: null,
        type: null,
        id: 'main-project_breakdown',
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            dojo.xhrGet({
                url: 'tendering/mainInfoForm',
                handleAs: 'json',
                content: { id: self.rootProject.id },
                load: function(data){

                    buildspace.currencyAbbreviation = buildspace.billCurrencyAbbreviation = data.currency;

                    var projectBreakdown = self.content = new ProjectBreakdown({
                        rootProject: self.rootProject,
                        tenderAlternative: self.tenderAlternative,
                        type: self.type
                    });

                    self.addChild(projectBreakdown);
                }
            });
        }
    });
});