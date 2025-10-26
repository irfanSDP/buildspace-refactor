define(["../../../dojo/_base/declare",
    'dojo/_base/lang',
    "dojo/when",
    "dojo/currency",
    './Builder',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/RationalizeRate'], function(declare, lang, when, currency, Builder, GridFormatter, nls){

    return declare('buildspace.apps.RationalizeRate', buildspace.apps._App, {
        type: null,
        win: null,
        project: null,
        init: function(args){
            var project = this.project = args.project;
            var type = this.type = args.type;

            this.win = new buildspace.widget.Window({
                title: nls.tendering + ' > ' + nls.viewTenderers+' - '+buildspace.truncateString(project.title, 100),
                onClose: dojo.hitch(this, "kill")
            });

            var builder = Builder({
                project: project,
                type: type
            });

            this.win.addChild(builder);
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