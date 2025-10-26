define(["../../../dojo/_base/declare",
	"dojo/when",
	'buildspace/apps/PostContractReport/Builder',
	'dojo/i18n!buildspace/nls/PostContractRemeasurement'
	], function(declare, when, PostContractBuilder, nls) {

	return declare('buildspace.apps.PostContractRemeasurementReport', buildspace.apps._App, {
		win: null,
		type: null,
		opt: null,
		project: null,
		init: function(args){
			this.type = args.type;
			var project = this.project = args.project,
				opt = this.opt = args.opt,
				container = new dijit.layout.BorderContainer({
					style:"padding:0;width:100%;height:100%;",
					gutters: false,
					liveSplitters: true
				});

			var win = this.win = new buildspace.widget.Window({
				title: nls.postContractReport + ' > ' + nls.remeasureProvisional + ' (' + nls[opt] + ')' + ' - ' + buildspace.truncateString(project.title, 100),
				onClose: dojo.hitch(this, "kill")
			});

			var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border-top:0px;padding:2px;overflow:hidden;"});

			toolbar.addChild(
				new dijit.form.Button({
					label: nls.backTo + ' ' + nls.postContractReport,
					iconClass: "icon-16-container icon-16-directional_left",
					style:"outline:none!important;",
					onClick: dojo.hitch(this, 'openBuilderWin', project)
				})
			);

			var remeasurementContainer = new buildspace.apps.PostContractRemeasurementReport.RemeasurementContainer({
				project: project,
				region: "center",
				opt: opt
			});

			remeasurementContainer.startup();

			container.addChild(toolbar);
			container.addChild(remeasurementContainer);

			win.addChild(container);
			win.show();
			win.startup();
		},
		openBuilderWin: function(project){
			this.kill();
			this.project = project;
			this.opt = null;
			this.win = new buildspace.widget.Window({
				title: nls.postContractReport + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
				onClose: dojo.hitch(this, "kill")
			});

			var builder;

			builder = new PostContractBuilder({
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