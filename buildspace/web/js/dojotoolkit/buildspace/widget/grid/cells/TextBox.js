define('buildspace/widget/grid/cells/TextBox',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojox/grid/cells/dijit",
    "dijit/form/TextBox"
], function(declare, lang, _Widget, TextBox ){
    return declare("buildspace.widget.grid.cells.TextBox", dojox.grid.cells._Widget, {
        widgetClass: TextBox,
        getWidgetProps: function(inDatum){
            return lang.mixin({}, this.widgetProps||{}, {
                value: inDatum,
                style:"padding:5px 0 5px 0;margin:0;color:black;"
            });
        }
    });
});