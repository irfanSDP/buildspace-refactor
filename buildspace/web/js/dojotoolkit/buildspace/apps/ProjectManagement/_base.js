define(["dojo/_base/declare",
    "dojo/when",
    'dojo/_base/lang',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/Filter',
    "buildspace/widget/grid/cells/Formatter",
    "./Builder",
    'dojo/i18n!buildspace/nls/ProjectManagement'], function(declare, when, lang, EnhancedGrid, FilterToolbar, GridFormatter, Builder, nls){

    var ProjectListing = declare('buildspace.apps.ProjectManagement.ProjectListing', dijit.layout.BorderContainer, {
        style:"padding:0;margin:0;outline:none;width:100%;height:100%;",
        gutters: false,
        gridOpts: {},
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {
                region:"center",
                borderContainerWidget: this,
                canSort: function(inSortInfo){
                    return false;
                }
            });

            var grid = this.grid = new EnhancedGrid(this.gridOpts);

            this.addChild(new FilterToolbar({
                region: 'top',
                grid: grid,
                editableGrid: false,
                filterFields: ['title', 'reference', 'country', 'state']
            }));

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });

    return declare('buildspace.apps.ProjectManagement', buildspace.apps._App, {
        win: null,
        init: function(args){
            var win = this.win = new buildspace.widget.Window({
                title: nls.projectManagement+' > '+nls.projectListing,
                onClose: dojo.hitch(this, "kill")
            });

            var formatter = new GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url:'projectManagement/getProjects'
                });

            this.projectListing = new ProjectListing({
                region: 'center',
                gridOpts: {
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;',formatter: formatter.rowCountCellFormatter, filterable: false},
                        {name: nls.title, field: 'title', width:'auto'},
                        {name: nls.reference, field: 'reference', width:'120px', styles:'text-align: center;', autoComplete: true},
                        {name: nls.country, field: 'country', width:'120px', styles:'text-align: center;'},
                        {name: nls.state, field: 'state', width:'120px', styles:'text-align: center;'}
                    ],
                    store: store,
                    onRowDblClick: dojo.hitch(this, 'projectListingDblClick')
                }
            });

            win.addChild(this.projectListing);
            win.show();
            win.startup();
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
                title: nls.projectManagement + ' > ' + buildspace.truncateString(project.title, 100)  + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            this.win.addChild(new Builder({
                project: project
            }));

            this.win.show();
            this.win.startup();
        },
        kill: function(){
            if (this.win && typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});