define('buildspace/apps/SubPackage/ResourceFilterDialog',[
    'dojo/_base/declare',
    'dojo/keys',
    "dojo/dom-style",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/SubPackages'
], function(declare, keys, domStyle, GridFormatter, nls){

    var Grid = declare('buildspace.apps.SubPackage.ResourceFilterGrid', dojox.grid.EnhancedGrid, {
        style: "border-top:none;",
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
        }
    });

    return declare('buildspace.apps.SubPackage.ResourceFilterDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.filterRatesByResources,
        subPackage: null,
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;
            this.title = nls.filterRatesByResources+' :: '+buildspace.truncateString(this.subPackage.name, 45);
            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function(e){
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createContent: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:850px;height:350px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border:0px;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "getResourcesBySubPackage/"+this.subPackage.id,
                    clearOnClose: true
                });

            var grid = new Grid({
                region: "center",
                store: store,
                structure: [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.name, field: 'name', width:'auto', formatter:formatter.analyzerDescriptionCellFormatter },
                    {name: nls.total, field: 'total', width:'160px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter}
                ]
            });
            borderContainer.addChild(toolbar);
            borderContainer.addChild(grid);

            return borderContainer;
        }
    });
});