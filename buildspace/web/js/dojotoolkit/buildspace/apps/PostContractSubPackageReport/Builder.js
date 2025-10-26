define('buildspace/apps/PostContractSubPackageReport/Builder',[
    '../../../dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    'dojo/_base/event',
    'dojo/keys',
    'dojo/on',
    './WorkArea',
    'dojo/i18n!buildspace/nls/PostContractSubPackage'
], function(declare, lang, array, evt, keys, on, WorkArea, nls){

    return declare('buildspace.apps.PostContractSubPackageReport.Builder', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        subPackage: null,
        workarea: null,
        postCreate: function(){
            this.inherited(arguments);

            var workarea = this.workarea = new WorkArea({
                rootProject: this.project,
                subPackage: this.subPackage
            });

            workarea.startup();

            this.addChild(workarea);
        }
    });
});