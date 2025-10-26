define('buildspace/widget/grid/cells/GanttDependencyTextBox',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojox/grid/cells/dijit",
    "dijit/form/TextBox"
], function(declare, lang, _Widget, TextBox ){

    function convertItemIdToRowIndex(grid, val, rowIdx)
    {
        var store = grid.store;
        var ret = "";
        store.fetchItemByIdentity({ 'identity' : val,
            onItem : function(rowItem){
                if(rowItem){
                    var itemIndex = grid.getItemIndex(rowItem);
                    if(itemIndex == -1){
                        grid._fetch(rowItem._0, false);
                        itemIndex = grid.getItemIndex(rowItem);
                    }

                    if(itemIndex >=0){
                        ret = itemIndex+1;
                    }
                }
            }
        });
        
        return ret;
    }
    
    return declare("buildspace.widget.grid.cells.GanttDependencyTextBox", dojox.grid.cells._Widget, {
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
                var item = grid.getItem(inRowIndex), value = item[fieldName];

                if(item[fieldName]){
                    value = convertItemIdToRowIndex(grid, item[fieldName][0], inRowIndex);
                }
                
                return value;
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