define(["../../../dojo/_base/declare",
    'dojo/_base/lang',
    "dojo/when",
    "dojo/currency",
    './Builder',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ViewTenderer'], function(declare, lang, when, currency, Builder, GridFormatter, nls){

    return declare('buildspace.apps.ViewTenderer', buildspace.apps._App, {
        type: null,
        win: null,
        project: null,
        tenderAlternative: null,
        init: function(args){
            var project = this.project = args.project;
            var type = this.type = args.type;
            var tenderAlternative = this.tenderAlternative = args.tenderAlternative;

            var title;
            if(tenderAlternative){
                title = nls.tendering + ' > ' + nls.viewTenderers+' - '+buildspace.truncateString(tenderAlternative.title, 30)+' - '+buildspace.truncateString(project.title, 70);;
            }else{
                title = nls.tendering + ' > ' + nls.viewTenderers+' - '+buildspace.truncateString(project.title, 100);
            }
            this.win = new buildspace.widget.Window({
                title: title,
                onClose: dojo.hitch(this, "kill")
            });

            this.win.addChild(Builder({
                project: project,
                tenderAlternative: tenderAlternative,
                type: type
            }));
            this.win.show();
            this.win.startup();
        },
        kill: function(){
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});