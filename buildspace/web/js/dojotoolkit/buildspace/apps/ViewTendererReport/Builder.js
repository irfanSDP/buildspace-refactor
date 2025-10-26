define('buildspace/apps/ViewTendererReport/Builder',[
    '../../../dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    'dojo/_base/event',
    'dojo/keys',
    'dojo/on',
    'dojo/store/Memory',
    './WorkArea',
    "dojo/when",
    './AssignContractorDialog',
    'buildspace/apps/TenderingReport/Builder',
    'dojo/i18n!buildspace/nls/ViewTenderer'
], function(declare, lang, array, evt, keys, on, Memory, WorkArea, when, AssignContractorDialog, Tendering, nls){

    return declare('buildspace.apps.ViewTendererReport.Builder', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        type: null,
        workarea: null,
        selectedTenderers: null,
        postCreate: function(){
            this.inherited(arguments);

            var self = this,
                project = this.project,
                toolbar = new dijit.Toolbar({region: "top", style:"border:none;padding:2px;width:100%;"});

            this.selectedTenderers = new Memory({ idProperty: 'id' });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backTo + ' ' + nls.tenderingReport,
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

                        pb.show();

                        companyFormXhr.then(function(values){
                            return values;
                        });

                        when(companyFormXhr, function(values){
                            pb.hide();
                            var dialog = new AssignContractorDialog({
                                builderContainer: self,
                                project: self.project,
                                formValues: values,
                                disableEditing: false
                            });
                            dialog.show();
                        });
                    }
                })
            );

            var workarea = this.workarea = new WorkArea({
                builderContainer: self,
                rootProject: this.project,
                type: this.type
            });

            workarea.startup();

            this.addChild(toolbar);
            this.addChild(workarea);
        },
        openBuilderWin: function(project){
            this.kill();
            this.project = project;
            this.win = new buildspace.widget.Window({
                title: nls.tenderingReport  + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + ': ' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            var builder = Tendering({
                project: project
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