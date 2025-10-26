define('buildspace/apps/RationalizeRateReport/Builder',[
    '../../../dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    'dojo/_base/event',
    'dojo/keys',
    'dojo/on',
    './WorkArea',
    "dijit/form/DropDownButton",
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dojo/when",
    'buildspace/apps/TenderingReport/Builder',
    'dojo/i18n!buildspace/nls/RationalizeRate'
], function(declare, lang, array, evt, keys, on, WorkArea, DropDownButton, DropDownMenu, MenuItem, when, TenderingReport, nls){

    var Explorer = declare('buildspace.apps.RationalizeRateReport.Explorer', dijit.EditableTree, {
        splitter: true,
        region: "left",
        container: null,
        openOnClick:false,
        labelAttr: 'name',
        rootProject: {},
        autoExpand: true,
        billElementObj: null,
        style: "background-color:#e5e5e5;width:230px;height:100%;",
        constructor: function(args){
            this.inherited(arguments);
            var libraryStore = new dojo.data.ItemFileWriteStore({
                url:"tendering/getProjectStructure/id/"+args.rootProject.id
            });
            var treeModel = new dijit.tree.ForestStoreModel({
                store: libraryStore,
                rootId: args.rootProject.id,
                rootLabel: buildspace.truncateString(args.rootProject.title, 200),
                childrenAttrs: ["__children"]
            });

            this.getIconClass = dojo.hitch(libraryStore, function(item, opened){
                if(item.root || item.type == buildspace.apps.RationalizeRateReport.ProjectStructureConstants.TYPE_LEVEL){
                    return opened ? 'icon-16-container icon-16-file' : 'icon-16-container icon-16-folder';
                }else if(item.type == buildspace.apps.RationalizeRateReport.ProjectStructureConstants.TYPE_BILL){
                    return 'icon-16-container icon-16-list';
                }else if(item.type == buildspace.apps.RationalizeRateReport.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL){
                    return 'icon-16-container icon-16-spreadsheet';
                }else{
                    return 'icon-16-container icon-16-document';
                }
            });
            this.model = treeModel;
        },
        createBill: function(parent, type){
            var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                }),
                self = this,
                xhrArgs = {
                    url: 'tendering/createBill',
                    content: {parent_id: parent.id, type: type, _csrf_token: parent._csrf_token},
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            var model = self.model;
                            if(!parent.root){
                                model.newItem(resp.item, parent);
                            }else{
                                model.newItem(resp.item);
                            }
                            var projectBreakdown = dijit.byId('main-project_breakdown');
                            projectBreakdown.content.grid.reload();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                };

            pb.show();
            dojo.xhrPost(xhrArgs);
        },
        removeBillTab: function(){
            this.container.workarea.removeBillTab();
        },
        initTab: function(item, options){
            var opened = false;
            var tac = this.container.workarea.getChildren();
            for(var i in tac){
                if(typeof tac[i].pane_info != "object") continue;
                if(tac[i].pane_info.id == item.id+'-form_'+item.type){
                    opened = true;
                    this.container.workarea.selectChild(tac[i]);
                    continue;
                }
            };
            if(!opened){
                this.container.workarea.makeTab(item, options, true);
            }
        }
    });

    return declare('buildspace.apps.RationalizeRateReport.Builder', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        type: null,
        workarea: null,
        postCreate: function(){
            this.inherited(arguments);

            var explorer = this.explorer = Explorer({
                rootProject: this.project,
                container: this
            }),
            self = this,
            project = this.project,
            toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backTo + ' ' + nls.tenderingReport,
                    iconClass: "icon-16-container icon-16-directional_left",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openBuilderWin', project)
                })
            );

            var workarea = this.workarea = new WorkArea({
                rootProject: this.project,
                explorer: explorer,
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
                title: nls.tenderingReport + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + ': ' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            var builder = new TenderingReport({
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