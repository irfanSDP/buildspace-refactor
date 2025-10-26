define('buildspace/apps/PostContractSubPackageReport/SubPackageListingGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/Rearrange",
    'buildspace/apps/PostContractReport/Builder',
    'dojo/i18n!buildspace/nls/PostContractSubPackage'
], function(declare, lang, EnhancedGrid, Rearranger, PostContractReport, nls){

    var SubPackageListingGrid = declare('buildspace.apps.PostContractSubPackageReport.SubPackageListingGrid', EnhancedGrid, {
        keepSelection: true,
        id: 'Post_Contract-subpackage_listing_grid',
        style: "border:none;",
        rowSelector: '0px',
        addUrl: null,
        updateUrl: null,
        deleteUrl: null,
        constructor:function(args){
            this.rearranger = Rearranger(this, {});
        },
        canSort: function(inSortInfo){
            return false;
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        }
    });

    return declare('buildspace.apps.PostContractSubPackageReport.SubPackageListing', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        title: null,
        gridOpts: {},
        postCreate: function(){
            var self = this,
                project = this.project;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { region:"center", borderContainerWidget: self });
            var grid = this.grid = new SubPackageListingGrid(self.gridOpts);

            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;width:100%;border-bottom:0px;"});

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backToProjectPostContractReport,
                    iconClass: "icon-16-container icon-16-directional_left",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openBuilderWin', project)
                })
            );

            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        },
        openBuilderWin: function(project){
            this.kill();
            this.project = project;
            this.win = new buildspace.widget.Window({
                title: nls.postContractReport + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + ': ' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            var builder = new PostContractReport({
                project: project
            });

            this.win.addChild(builder);
            this.win.show();
            this.win.startup();
        },
        kill: function()
        {
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});