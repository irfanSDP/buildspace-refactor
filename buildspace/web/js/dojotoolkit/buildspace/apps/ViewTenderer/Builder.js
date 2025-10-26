define('buildspace/apps/ViewTenderer/Builder',[
    '../../../dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    'dojo/_base/event',
    'dojo/keys',
    'dojo/on',
    './WorkArea',
    './PrintBillDialog',
    "dojo/when",
    './AssignContractorDialog',
    'buildspace/apps/Tendering/Builder',
    'dojo/i18n!buildspace/nls/ViewTenderer'
], function(declare, lang, array, evt, keys, on, WorkArea, PrintBillDialog, when, AssignContractorDialog, Tendering, nls){

    return declare('buildspace.apps.ViewTenderer.Builder', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        tenderAlternative: null,
        type: null,
        workarea: null,
        postCreate: function(){
            this.inherited(arguments);

            var self = this,
                project = this.project,
                tenderAlternative = this.tenderAlternative;
                toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border:none;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backTo + ' ' + nls.tendering,
                    iconClass: "icon-16-container icon-16-directional_left",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openBuilderWin', project)
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.tendererSetting,
                    iconClass: "icon-16-container icon-16-messenger",
                    onClick: function(e){
                        var companyFormXhr = dojo.xhrGet({
                                url: "viewTenderer/tenderCompanyForm/id/"+self.project.id,
                                handleAs: "json"
                            }),
                            pb = buildspace.dialog.indeterminateProgressBar({
                                title:nls.pleaseWait+'...'
                            });

                        pb.show().then(function(){
                            when(companyFormXhr, function(values){
                                pb.hide();
                                var dialog = new AssignContractorDialog({
                                    project: self.project,
                                    tenderAlternative: tenderAlternative,
                                    formValues: values,
                                    disableEditing: self.project.tendering_module_locked[0]
                                });
                                dialog.show();
                            });
                        });
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.printEstimationBQ,
                    iconClass: "icon-16-container icon-16-print",
                    onClick: dojo.hitch(this, 'printRates')
                })
            );

            var workarea = this.workarea = new WorkArea({
                rootProject: project,
                tenderAlternative: tenderAlternative,
                type: this.type
            });

            workarea.startup();

            this.addChild(toolbar);
            this.addChild(workarea);
        },
        printRates: function (){
            var self = this;

            var dialog = new PrintBillDialog({
                projectId: self.project.id,
                tenderAlternative: this.tenderAlternative,
                title: nls.printEstimationBQ,
                _csrf_token: self.project._csrf_token
            });

            dialog.show();
        },
        openBuilderWin: function(project){
            this.kill();
            this.project = project;
            this.win = new buildspace.widget.Window({
                title: buildspace.truncateString(project.title, 100) + ' (' + nls.status + ': ' + String(project.status).toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            this.win.addChild(Tendering({
                project: project
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