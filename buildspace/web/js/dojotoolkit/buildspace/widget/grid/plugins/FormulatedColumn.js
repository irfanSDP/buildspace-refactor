define("buildspace/widget/grid/plugins/FormulatedColumn", [
    "dojo/_base/kernel",
    "dojo/_base/lang",
    "dojo/_base/declare",
    "dojox/grid/EnhancedGrid",
    "dojox/grid/enhanced/_Plugin"
], function(dojo, lang, declare, EnhancedGrid, _Plugin){

    var FormulatedColumn = declare("buildspace.widget.grid.plugins.FormulatedColumn", _Plugin, {
        // summary:
        // Provides a set of method to handle formula ala excel.

        name: "formulatedColumn",

        constructor: function(grid, args){
            this.grid = grid;
            this.setArgs(args);
        },
        setArgs: function(args){
            this.args = lang.mixin(this.args || {}, args || {});
        },
        destroy: function(){
            this.inherited(arguments);
        },
        convertRowIndexToItemId: function(str, inRowIndex){
            var grid = this.grid;
            var matchedVariable = (typeof str != 'string' || str == null) ? null : str.match(/r\d{1,}/gi);

            if(matchedVariable == null){
                return str;
            }else{
                var formula = str;
                dojo.forEach(matchedVariable, function(entry, i){
                    var indexStr = matchedVariable[i],
                    regEx = new RegExp("r", "ig"),
                    rowIndex = parseInt(indexStr.replace(regEx,""))- 1,
                    regexRowId = new RegExp("\\b"+indexStr+"\\b","gi");
                    var item = grid.getItem(rowIndex);
                    if(item == null){
                        grid._fetch(rowIndex, false);
                        item = grid.getItem(rowIndex);//refetch back the item once grid's view updated
                    }
                    var editable = (item && item.type != undefined && (item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID)) ? false : true;
                    if(item == null || item.id[0] == buildspace.constants.GRID_LAST_ROW || !editable){
                        formula = formula.replace(regexRowId, "#REF!");
                    }else{
                        formula = formula.replace(regexRowId, 'R'+item.id[0]);
                    }
                });
                return formula;
            }
        },
        convertItemIdToRowIndex: function(str, inRowIndex){
            var grid = this.grid, store = grid.store;
            var matchedVariable = (typeof str != 'string' || str == null) ? null : str.match(/r\d{1,}/gi);

            if(matchedVariable == null){
                return str;
            }else{
                var formula = str;

                dojo.forEach(matchedVariable, function(entry, i){
                    var item = matchedVariable[i],
                        regEx = new RegExp("r", "ig"),
                        rowId = item.replace(regEx,"");
                    var regexItemId = new RegExp("\\b"+item+"\\b","gi");
                    store.fetchItemByIdentity({ 'identity' : rowId,
                        onItem : function(rowItem){
                            var itemIndex = grid.getItemIndex(rowItem);
                            if(itemIndex == -1){
                                grid._fetch(rowItem._0, false);
                                itemIndex = grid.getItemIndex(rowItem);
                            }

                            if(itemIndex >=0){
                                var replaceIndex = itemIndex+1;
                                formula = formula.replace(regexItemId, 'R'+replaceIndex);
                            }else{
                                formula = formula.replace(regexItemId, '#REF!');
                            }
                        }
                    });
                });

                return formula;
            }
        }
    });

    EnhancedGrid.registerPlugin(FormulatedColumn/*name:'rearrange'*/);

    return FormulatedColumn;
});