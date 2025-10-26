define('buildspace/widget/grid/cells/Select',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/data/ItemFileReadStore",
    "dojox/grid/cells/dijit",
    "dijit/form/Select"
], function(declare, lang, ItemFileReadStore, _Widget, Select ){
    return declare("buildspace.widget.grid.cells.Select", dojox.grid.cells._Widget, {
        widgetClass: Select,
        getWidgetProps: function(inDatum){
            var store = ItemFileReadStore({data:this.storeObj});
            return lang.mixin({}, this.widgetProps||{}, {
                value: inDatum,
                maxHeight: 200,
                store: store
            });
        }
    });
});