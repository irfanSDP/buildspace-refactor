define('buildspace/widget/grid/cells/DateTextBox',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojox/grid/cells/dijit",
    "dijit/form/DateTextBox"
], function(declare, lang,_Widget, DateTextBox){
    return declare("buildspace.widget.grid.cells.DateTextBox", dojox.grid.cells._Widget, {
        widgetClass: DateTextBox,
        createWidget: function(inNode, inDatum, inRowIndex) {
            var grid = this.grid;
            var widget = new this.widgetClass(this.getWidgetProps(inDatum), inNode);

            setTimeout(function() {
                grid.views.renormalizeRow(inRowIndex);
                grid.scroller.rowHeightChanged(inRowIndex, true);
            }, 0);

            return widget;
        },
        formatEditing: function(inDatum, inRowIndex) {
            this.editingRowIndex = inRowIndex;

            this.needFormatNode(inDatum, inRowIndex);

            return "<div></div>";
        },
        getWidgetProps: function(inDatum) {
            var self = this, grid = this.grid;

            return lang.mixin({}, this.widgetProps||{}, {
                value: inDatum,
                style:"padding:5px 0;margin:0;color:black;",
                constraints: {
                    datePattern: 'yyyy-MM-dd'
                },
                onFocus: function(e) {
                    this.focusNode.select();

                    setTimeout(function() {
                        grid.rowHeightChanged(self.editingRowIndex);
                    }, 10);
                },
                onInput: function(e) {
                    setTimeout(function() {
                        grid.rowHeightChanged(self.editingRowIndex);
                    }, 10);
                }
            });
        }
    });
});