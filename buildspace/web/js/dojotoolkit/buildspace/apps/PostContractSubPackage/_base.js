define(["dojo/_base/declare",
    './Builder',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContractSubPackage'],function(declare, Builder, GridFormatter, nls){
    return declare('buildspace.apps.PostContractSubPackage', buildspace.apps._App, {
        win: null,
        project: null,
        init: function(args){
            var project = this.project = args.project;

            this.win = new buildspace.widget.Window({
                title: nls.postContract + ' > ' + nls.appName+' :: '+buildspace.truncateString(project.title, 100),
                onClose: dojo.hitch(this, "kill")
            });

            var formatter = new GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:'postContractSubPackage/getSubPackageList/projectId/' + project.id
                });

            this.subpackageListing = new buildspace.apps.PostContractSubPackage.SubPackageListing({
                region: 'center',
                project: project,
                gridOpts: {
                    structure: [
                        {name: '&nbsp;', field: 'id', width:'30px', styles:'text-align:center;',formatter: formatter.rowCountCellFormatter, filterable: false},
                        {name: nls.title, field: 'title', width:'auto', autoComplete: true},
                        {name: nls.amount, field: 'amount', width:'120px', styles:'text-align: right;', formatter: formatter.unEditableCurrencyCellFormatter}
                    ],
                    store: store,
                    onRowDblClick: dojo.hitch(this, 'subpackageListingDblClick')
                }
            });

            this.win.addChild(this.subpackageListing);
            this.win.show();
            this.win.startup();
        },
        subpackageListingDblClick: function(e){
            var item = this.subpackageListing.grid.getItem(e.rowIndex);
            if(!isNaN(parseInt(String(item.id)))){
                this.createBuilderWin(item);
            }
        },
        createBuilderWin: function(subPackage){
            this.kill();
            var project = this.project;

            this.win = new buildspace.widget.Window({
                title: nls.postContract + ' > ' + nls.appName+' :: '+buildspace.truncateString(project.title, 100) + ' > ' + subPackage.title,
                onClose: dojo.hitch(this, "kill")
            });

            this.win.addChild(Builder({
                project: project,
                subPackage: subPackage
            }));

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
        kill: function(){
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});
