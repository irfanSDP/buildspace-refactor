define('buildspace/apps/PostContractSubPackage/SubPackageListingGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/Rearrange",
    'buildspace/apps/PostContract/Builder',
    'dojo/i18n!buildspace/nls/PostContractSubPackage'
], function(declare, lang, EnhancedGrid, Rearranger, PostContract, nls){

    var SubPackageListingGrid = declare('buildspace.apps.PostContractSubPackage.SubPackageListingGrid', EnhancedGrid, {
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

    return declare('buildspace.apps.PostContractSubPackage.SubPackageListing', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        title: null,
        gridOpts: {},
        postCreate: function(){
            var project = this.project;

            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                region:"center",
                borderContainerWidget: this
            });

            var grid = this.grid = new SubPackageListingGrid(this.gridOpts);

            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;width:100%;border-bottom:0px;"});

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backToProjectPostContract,
                    iconClass: "icon-16-container icon-16-directional_left",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openBuilderWin', project)
                })
            );

            this.addChild(toolbar);
            this.addChild(grid);
        },
        openBuilderWin: function(project){
            this.kill();
            this.project = project;
            this.win = new buildspace.widget.Window({
                title: nls.postContract + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + ': ' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            this.win.addChild( PostContract({
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
