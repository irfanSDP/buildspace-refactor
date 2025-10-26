require(['dojo/_base/declare', 'buildspace/apps/_App'], function(declare, _App){
    buildspace.app = {
        //  appList: Array,
        //      Contains a list of each app's information (loaded on startup)
        appList: [],
        //  instances: Array
        //      Contains each instance of all apps
        instances: [],
        //  instanceCount: Int
        //      A counter for making new instances of apps
        instanceCount: 0,
        //  currentApp: String
        //      the current application that is running
        currentApp: "",
        loadAppList: true,
        init: function(config){
            //  summary:
            //      Loads the app list from the server
            this.loadAppList = config.loadAppList;

            if(this.loadAppList){
                dojo.xhrGet({
                    url: "default/getMyApps",
                    sync: true,
                    load: dojo.hitch(this, function(data, ioArgs){
                        this.appList = data;
                        buildspace.app.appList = this.appList;
                    }),
                    handleAs: "json"
                });
            }
        },
        launch: function(item, args, onComplete, onError){
            //  summary:
            //      Fetches an app if it's not in the cache, then launches it. Returns the process ID of the application.
            //  item:
            //      the app's object
            //  args:
            //      the arguments to be passed to the app
            //  onComplete:
            //      a callback once the app has initiated
            //  onError:
            //      if there was a problem launching the app, this will be called
            if(item.sysname){
                var sysname = item.sysname;
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:'Please wait...'
                });
                require(["buildspace/apps/"+sysname]);
                pb.show().then(function(){
                    dojo.addOnLoad(function() {
                        pb.hide();
                        dojo.publish("launchApp", [sysname]);
                        var d = new dojo.Deferred();
                        if(onComplete) d.addCallback(onComplete);
                        if(onError) d.addErrback(onError);
                        var pid = false;
                        try {
                            pid = buildspace.app.instances.length;
                            var instance = buildspace.app.instances[pid] = new buildspace.apps[sysname]({
                                sysname: sysname,
                                name: item.title,
                                instance: pid,
                                icon: item.icon,
                                args: args
                            });
                            try {
                                instance.init(args||{});
                            }catch(e){
                                dojo.publish("launchAppEnd", [sysname]);
                                console.error(e);
                                d.errback(e);
                                return;
                            }
                            instance.status = "active";
                        }catch(e){
                            dojo.publish("launchAppEnd", [sysname]);
                            console.error(e);
                            d.errback(e);
                            return;
                        }
                        d.callback(instance);
                        dojo.publish("launchAppEnd", [sysname]);
                        return pid;
                    });
                });
            }else{
                var win = dijit.byId('buildspace-main_window');
                if(win){ win.close(); }
            }
        }
    };
});
