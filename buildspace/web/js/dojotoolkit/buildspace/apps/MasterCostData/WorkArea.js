define('buildspace/apps/MasterCostData/WorkArea',[
    'dojo/_base/declare',
    './Breakdown',
    './ProjectParticulars',
    './ProjectInformation',
    'dojo/i18n!buildspace/nls/CostData'], function(declare, Breakdown, ProjectParticulars, ProjectInformation, nls){

    return declare('buildspace.apps.MasterCostData.WorkArea', dijit.layout.TabContainer, {
        region: "center",
        style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        masterCostData: null,
        postCreate: function(){
            var breakdown = this.breakdown = Breakdown({
                title: nls.breakdown,
                masterCostData: this.masterCostData
            });
            this.addChild(breakdown);

            var projectParticulars = this.projectParticulars = ProjectParticulars({
                title: nls.projectParticulars,
                masterCostData: this.masterCostData
            });
            this.addChild(projectParticulars);

            var projectInformation = this.projectInformation = ProjectInformation({
                title: nls.projectInfo,
                parentItem: {id: 0},
                masterCostData: this.masterCostData
            });
            this.addChild(projectInformation);
        }
    });
});