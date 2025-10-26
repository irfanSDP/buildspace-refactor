define(["dojo/_base/declare",
'dojo/_base/lang',
'./Builder',
'dojo/i18n!buildspace/nls/CostData'
], function(declare, lang, Builder, nls){
    return declare('buildspace.apps.MasterCostData', buildspace.apps._App, {
        win: null,
        id: null,
        data: {},
        init: function(args){
            this.id = args.id;
            this.data = args.data;
            this.win = new buildspace.widget.Window({
                title: nls.masterCostData + ' - ' + this.data.title,
                onClose: dojo.hitch(this, "kill"),
                fullscreen: true
            });

            this.win.addChild(new Builder({
                id: this.id,
                data: this.data
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
