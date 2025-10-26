define('buildspace/apps/CostData/Builder',[
    'dojo/_base/declare',
    'dijit/layout/TabContainer',
    "./WorkArea",
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, TabContainer, WorkArea, nls){

    return declare('buildspace.apps.CostData.Builder', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        costData: {},
        isEditor: false,
        workarea: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var workarea = self.workarea = WorkArea({
                costData: this.costData,
                editable: this.isEditor
            });

            workarea.startup();

            self.addChild(workarea);
        }
    });
});
