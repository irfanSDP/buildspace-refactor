define('buildspace/widget/grid/cells/FormulaTextBox',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojox/grid/cells/dijit",
    "dijit/form/TextBox"
], function(declare, lang, _Widget, TextBox ){
    return declare("buildspace.widget.grid.cells.FormulaTextBox", dojox.grid.cells._Widget, {
        widgetClass: TextBox,
        createWidget: function(inNode, inDatum, inRowIndex){
            return new this.widgetClass(this.getWidgetProps(this.getCalculatedValue(inRowIndex)), inNode);
        },
        attachWidget: function(inNode, inDatum, inRowIndex){
            this.inherited(arguments);
            this.setValue(inRowIndex, this.getCalculatedValue(inRowIndex));
        },
        getCalculatedValue: function(inRowIndex){
            var grid = this.grid;
            var fieldName = this.field;
            if(inRowIndex != undefined){
                var item = grid.getItem(inRowIndex), converted = item[fieldName];

                if(item[fieldName]){
                    var formulatedColumnPlugin = grid.formulatedColumn;
                    converted = formulatedColumnPlugin.convertItemIdToRowIndex(item[fieldName][0], inRowIndex);
                }
                return converted;
            }
        },
        getWidgetProps: function(inDatum){
            return lang.mixin({}, this.widgetProps||{}, {
                value: inDatum,
                style:"padding:5px 0 5px 0;margin:0;color:black;"
            });
        }
    });
});