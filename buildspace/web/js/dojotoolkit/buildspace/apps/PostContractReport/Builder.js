define('buildspace/apps/PostContractReport/Builder',[
    '../../../dojo/_base/declare',
    './WorkArea',
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, WorkArea, nls){

    return declare('buildspace.apps.PostContractReport.Builder', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        workarea: null,
        postCreate: function(){
            this.inherited(arguments);

            var workArea = this.workArea = new WorkArea({
                rootProject: this.project
            });

            this.addChild(workArea);
        }
    });
});
