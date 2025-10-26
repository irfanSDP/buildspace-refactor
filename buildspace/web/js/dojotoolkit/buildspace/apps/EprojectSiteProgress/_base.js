define(["dojo/_base/declare",
    'buildspace/apps/_App',
    'buildspace/apps/Location/ProjectLocationManagement/ProgressClaim/ProgressClaimContainer',
    'dojo/i18n!buildspace/nls/Location'],
function(declare, _App, ProgressClaimContainer, nls){
    return declare('buildspace.apps.EprojectSiteProgress', buildspace.apps._App, {
        win: null,
        init: function(args){
        },
        createBuilderWin: function(project){
            this.kill();
            this.project = project;
            this.win = new buildspace.widget.Window({
                title: nls.siteProgressClaim + ' > ' + buildspace.truncateString(project.title, 100),
                onClose: dojo.hitch(this, "kill")
            });

            this.win.addChild(new ProgressClaimContainer({
                id: this.project.id+"-SiteProgressClaim",
                title: nls.progressClaims,
                project: this.project,
                baseApp: this
            }));
            this.win.show();
            this.win.startup();
        },
        kill: function(){
            if (this.win && typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});
