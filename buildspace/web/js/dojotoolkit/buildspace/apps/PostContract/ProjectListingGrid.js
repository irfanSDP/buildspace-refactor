define('buildspace/apps/PostContract/ProjectListingGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/Filter',
    'dojo/i18n!buildspace/nls/Tendering'
], function(declare, lang, EnhancedGrid, FilterToolbar, nls){

    var ProjectListingGrid = declare('buildspace.apps.PostContract.ProjectListingGrid', EnhancedGrid, {
        keepSelection: true,
        id: 'Post_Contract-project_listing_grid',
        style: "border:none;",
        rowSelector: '0px',
        canSort: function(inSortInfo){
            return false;
        },
        canEdit: function(inCell, inRowIndex) {
            return false;
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        }
    });

    return declare('buildspace.apps.PostContract.ProjectListing', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;outline:none;width:100%;height:100%;",
        gutters: false,
        title: null,
        gridOpts: {},
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {
                region:"center",
                borderContainerWidget: this
            });

            var grid = this.grid = new ProjectListingGrid(this.gridOpts);

            this.addChild(new FilterToolbar({
                region: 'top',
                grid: grid,
                editableGrid: false,
                filterFields: ['title', 'reference', 'country', 'state']
            }));

            this.addChild(grid);
        }
    });
});