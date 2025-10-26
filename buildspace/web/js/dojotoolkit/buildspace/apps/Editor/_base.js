define(["dojo/_base/declare",
'dojo/_base/lang',
'./Builder',
'dojo/i18n!buildspace/nls/Tendering'], function(declare, lang, Builder, nls){
    return declare('buildspace.apps.Editor', buildspace.apps._App, {
        win: null,
        pid: null,
        init: function(args){
            this.pid = args.pid;
            this.win = new buildspace.widget.Window({
                title: 'BQ Editor',
                onClose: dojo.hitch(this, "kill"),
                fullscreen: true
            });

            this.win.addChild(new Builder({
                pid: this.pid
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
