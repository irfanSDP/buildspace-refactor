define(["dojo/_base/declare",
'./Builder',
'dojo/i18n!buildspace/nls/CostData'
], function(declare, Builder, nls){
    return declare('buildspace.apps.CostData', buildspace.apps._App, {
        win: null,
        costData: {},
        init: function(args){
            this.isEditor = args.data.isEditor;
            this.costData = args.data.costData;
            this.win = new buildspace.widget.Window({
                title: nls.costData + ' - ' + this.costData.name,
                onClose: dojo.hitch(this, "kill"),
                fullscreen: true
            });

            this.win.addChild(new Builder({
                isEditor: this.isEditor,
                costData: this.costData
            }));

            this.win.show();
            this.win.startup();
        },
        makeTab: function(appName, title, pane){
            this.tabArea.addChild(pane);
            this.tabArea.selectChild(pane);

            pane.mod_info = {
                title: title,
                appName: appName
            };
        },
        kill: function(){
            if (this.win && typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});
