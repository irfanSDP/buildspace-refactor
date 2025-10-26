define(["dojo/_base/declare",
    './Builder',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'],function(declare, Builder, GridFormatter, nls){
    return declare('buildspace.apps.PostContractReport', buildspace.apps._App, {
        win: null,
        init: function(args){
            this.win = new buildspace.widget.Window({
                title: nls.postContractReport + ' > ' + nls.projectList,
                onClose: dojo.hitch(this, "kill")
            });

            var formatter = new GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:'postContract/getProjects'
                });

            this.projectListing = new buildspace.apps.PostContractReport.ProjectListing({
                region: 'center',
                gridOpts: {
                    structure: [
                        {name: '&nbsp;', field: 'id', width:'30px', styles:'text-align:center;',formatter: formatter.rowCountCellFormatter, filterable: false},
                        {name: nls.title, field: 'title', width:'auto', autoComplete: true},
                        {name: nls.reference, field: 'reference', width:'120px', styles:'text-align: center;', autoComplete: true},
                        {name: nls.country, field: 'country', width:'100px', styles:'text-align: center;', autoComplete: true},
                        {name: nls.state, field: 'state', width:'120px', styles:'text-align: center;', autoComplete: true},
                        {name: nls.status, field: 'status', width:'100px', styles:'text-align: center;', autoComplete: true},
                        {name: nls.created_at, field: 'created_at', width:'120px', styles:'text-align: center;', filterable: false}
                    ],
                    store: store,
                    onRowDblClick: dojo.hitch(this, 'projectListingDblClick')
                }
            });

            this.win.addChild(this.projectListing);
            this.win.show();
            this.win.startup();
        },
        projectListingDblClick: function(e){
            var item = this.projectListing.grid.getItem(e.rowIndex);
            if(item.id > 0){
                this.createBuilderWin(item);
            }
        },
        createBuilderWin: function(project){
            this.kill();
            this.project = project;
            this.win = new buildspace.widget.Window({
                title: nls.postContractReport + ' > ' + buildspace.truncateString(project.title, 100)  + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            var builder = Builder({
                project: project
            });

            this.win.addChild(builder);
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
        kill: function() {
            if (this.win && typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});