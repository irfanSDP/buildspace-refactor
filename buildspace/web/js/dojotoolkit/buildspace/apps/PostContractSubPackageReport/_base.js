define(["dojo/_base/declare",
    './Builder',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContractSubPackage'],function(declare, Builder, GridFormatter, nls){
    return declare('buildspace.apps.PostContractSubPackageReport', buildspace.apps._App, {
        win: null,
        project: null,
        init: function(args){
            var project = this.project = args.project;

            this.win = new buildspace.widget.Window({
                title: nls.postContractReport + ' > ' + nls.appName+' :: '+buildspace.truncateString(project.title, 100),
                onClose: dojo.hitch(this, "kill")
            });

            var formatter = new GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:'postContractSubPackage/getSubPackageList/projectId/' + project.id
                });

            this.subpackageListing = new buildspace.apps.PostContractSubPackageReport.SubPackageListing({
                region: 'center',
                project: project,
                gridOpts: {
                    structure: [
                        {name: '&nbsp;', field: 'id', width:'30px', styles:'text-align:center;',formatter: formatter.rowCountCellFormatter, filterable: false},
                        {name: nls.title, field: 'title', width:'auto', autoComplete: true},
                        {name: nls.amount, field: 'amount', width:'120px', styles:'text-align: center;', formatter: formatter.unEditableCurrencyCellFormatter}
                    ],
                    store: store,
                    onRowDblClick: dojo.hitch(this, 'subpackageListingDblClick')
                }
            });

            var container = new dijit.layout.BorderContainer({
                style:"padding:0px;margin:0px;outline:none;width:100%;height:100%;",
                gutters: false
            });

            container.addChild(this.subpackageListing);

            this.win.addChild(container);
            this.win.show();
            this.win.startup();
        },
        subpackageListingDblClick: function(e){
            var item = this.subpackageListing.grid.getItem(e.rowIndex);
            if(item.id > 0){
                this.createBuilderWin(item);
            }
        },
        createBuilderWin: function(subPackage){
            this.kill();
            var project = this.project;

            this.win = new buildspace.widget.Window({
                title: nls.postContractReport + ' > ' + nls.appName+' :: '+buildspace.truncateString(project.title, 100) + ' > ' + subPackage.title,
                onClose: dojo.hitch(this, "kill")
            });

            var builder = Builder({
                project: project,
                subPackage: subPackage
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
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});