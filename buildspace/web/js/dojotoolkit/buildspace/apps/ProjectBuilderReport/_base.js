define(["dojo/_base/declare", './Builder', "buildspace/widget/grid/cells/Formatter", 'dojo/i18n!buildspace/nls/ProjectBuilder'], function(declare, Builder, GridFormatter, nls){
	return declare('buildspace.apps.ProjectBuilderReport', buildspace.apps._App, {
		win: null,
		init: function(args){
			this.win = new buildspace.widget.Window({
				title: nls.ProjectBuilderReport + ' > ' + nls.projectList,
				onClose: dojo.hitch(this, "kill")
			});

            var formatter = GridFormatter();

            this.projectListing = new buildspace.apps.ProjectBuilderReport.ProjectListing({
				style:"padding:0px;margin:0px;outline:none;width:100%;height:100%;",
				region: 'center',
				gridOpts: {
					structure: [
						{name: '&nbsp;', field: 'id', width:'30px', styles:'text-align:center;',formatter: formatter.rowCountCellFormatter, filterable: false},
						{name: nls.title, field: 'title', width:'auto', autoComplete: true},
						{name: nls.reference, field: 'reference', width:'120px', styles:'text-align: center;', autoComplete: true},
						{name: nls.country, field: 'country', width:'100px', styles:'text-align: center;', autoComplete: true},
						{name: nls.state, field: 'state', width:'120px', styles:'text-align: center;', autoComplete: true},
						{name: nls.created_by, field: 'created_by', styles:'text-align: center;', width:'140px'},
						{name: nls.created_at, field: 'created_at', width:'120px', styles:'text-align: center;', filterable: false}
					],
					store: new dojo.data.ItemFileWriteStore({
                        url:'projectBuilder/getProjects'
                    }),
					onRowDblClick: dojo.hitch(this, 'projectListingDblClick')
				}
			});

			this.win.addChild(this.projectListing);
			this.win.show();
			this.win.startup();
		},
		projectListingDblClick: function(e){
			var item = this.projectListing.grid.getItem(e.rowIndex);
			if(item.id > 0) {
				this.createBuilderWin(item);
			}
		},
		createBuilderWin: function(project){
			this.kill();
			this.project = project;
			this.win = new buildspace.widget.Window({
				title: nls.ProjectBuilderReport + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
				onClose: dojo.hitch(this, "kill")
			});

			var builder = new Builder({
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
		kill: function()
		{
			if (this.win && typeof(this.win) != "undefined"){
                this.win.close();
            }
		}
	});
});