define('buildspace/apps/RationalizeRateReport/WorkArea',[
    'dojo/_base/declare',
    './ProjectBreakdown',
    'dojo/i18n!buildspace/nls/RationalizeRate'], function(declare, ProjectBreakdown, nls){
    var ProjectRevisionContainer = declare('buildspace.apps.RationalizeRateReport.ProjectRevisionContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
        gutters: false,
        projectId: null,
        rootProject: null,
        explorer: null,
        workarea: null,
        postCreate: function() {
            this.inherited(arguments);
            var projectRevisionSettingsForm = this.projectRevisionSettingsForm = ProjectRevisionSettingsForm({
                projectId: this.projectId,
                rootProject: this.rootProject,
                explorer: this.explorer,
                workarea: this.workarea
            });
            this.addChild(projectRevisionSettingsForm);
        }
    });

    var WorkArea = declare('buildspace.apps.RationalizeRateReport.WorkArea', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        rootProject: null,
        type: null,
        explorer: null,
        id: 'main-project_breakdown',
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
                            explorer: self.explorer,
                            type: self.type
                        });

                        self.addChild(projectBreakdown);
                    }
                };
            dojo.xhrGet(xhrArgs);
        }
    });

    return WorkArea;
});