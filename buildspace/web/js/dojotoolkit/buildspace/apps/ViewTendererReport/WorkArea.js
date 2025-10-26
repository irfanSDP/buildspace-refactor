define('buildspace/apps/ViewTendererReport/WorkArea',[
    'dojo/_base/declare',
    './ProjectBreakdown',
    'dojo/i18n!buildspace/nls/ViewTenderer'], function(declare, ProjectBreakdown, nls){

    var WorkArea = declare('buildspace.apps.ViewTendererReport.WorkArea', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        rootProject: null,
        type: null,
        id: 'main-project_breakdown',
        builderContainer: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this,
                xhrArgs = {
                    url: 'tendering/mainInfoForm',
                    handleAs: 'json',
                    content: { id: self.rootProject.id },
                    load: function(data){

                        buildspace.currencyAbbreviation = buildspace.billCurrencyAbbreviation = data.currency;

                        var projectBreakdown = self.content = new ProjectBreakdown({
                            rootProject: self.rootProject,
                            type: self.type,
                            builderContainer: self.builderContainer
                        });

                        self.addChild(projectBreakdown);
                    }
                };
            dojo.xhrGet(xhrArgs);
        }
    });

    return WorkArea;
});