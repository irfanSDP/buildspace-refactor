require({
    cache:{
        'url:buildspace/widget/forms/templates/DropdownDialog.html':"<div role=\"presentation\" data-dojo-attach-point=\"containerNode\" tabIndex=\"-1\">\n\t</div>\n"
    }
});
define('buildspace/widget/forms/MultiSelectDropDown',[
    "dojo/_base/declare",
    "dojo/on",
    "dojo/_base/html",
    "dojo/_base/array",
    "dojo/_base/lang",
    "dojo/_base/connect",
    "dojo/aspect",
    "dojo/dom-class",
    "dojo/_base/event",
    "dojo/keys",
    "dijit/popup",
    "dijit/focus",
    'dijit/form/DropDownButton',
    "dijit/layout/ContentPane",
    "dijit/_DialogMixin",
    "dijit/form/_FormMixin",
    "dijit/_TemplatedMixin",
    "dojo/text!./templates/DropdownDialog.html",
    "dojox/grid/enhanced/plugins/filter/FilterLayer",
    "dojox/grid/enhanced/plugins/filter/FilterBuilder",
    "dojox/grid/enhanced/_Plugin",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    "dijit/form/TextBox",
    'dojo/i18n!buildspace/nls/Common'
], function(declare, on, html, array, lang, connect, aspect, domClass, event, keys, popup, focusUtil, DropDownButton, ContentPane, _DialogMixin, _FormMixin, _TemplatedMixin, template, layers, FilterBuilder, _Plugin, IndirectSelectionPlugin, TextBox, nls) {

    var Filter = declare("buildspace.widget.forms.DropDownDialog.grid.plugins.Filter", _Plugin, {
        // name: String
        //		plugin name
        name: "buildspaceDropDownFilter",

        constructor: function(grid, args){
            // summary:
            //		See constructor of dojox.grid.enhanced._Plugin.
            this.grid = grid;
            this.nls = nls;
            this._connects = [];

            args = this.args = lang.isObject(args) ? args : {};

            this.builder = new FilterBuilder();

            var filterLayer =
                new layers.ClientSideFilterLayer({
                    getter: this._clientFilterGetter
                });

            layers.wrap(grid, "_storeLayerFetch", filterLayer);

            if(!this.textBox){
                this.textBox = new TextBox({
                    id: this.grid.id + "_dropDownFilterTextBox",
                    style:"font-weight:normal!important;padding:2px;height:auto;width:auto;display:block;",
                    intermediateChanges: true,
                    onKeyUp: dojo.hitch(this, "doTextBoxFilter")
                });
            }

            this._connects.push(connect.connect(this.grid, "_onDelete", lang.hitch(filterLayer, "invalidate")));

            this.fetchCompleted = false;

            this._connects.push(connect.connect(this.grid.views, 'render', this, '_addWidgetsToHeaders'));
            this._connects.push(connect.connect(this.grid, '_onFetchComplete', this, '_onItemsLoaded'));
        },
        _clientFilterGetter: function(/* data item */ datarow,/* cell */cell, /* int */rowIndex){
            // summary:
            //		Define the grid-specific way to get data from a row.
            //		Argument "cell" is provided by FilterDefDialog when defining filter expressions.
            //		Argument "rowIndex" is provided by FilterLayer when checking a row.
            //		FilterLayer also provides a forth argument: "store", which is grid.store,
            //		but we don't need it here.
            return cell.get(rowIndex, datarow);
        },
        _addWidgetsToHeaders: function(){
            var columnHeaderNodes = dojo.query(
                '.dojoxGridHeader table th',
                this.grid.viewsHeaderNode);

            html.empty(columnHeaderNodes[1].firstChild);

            if(this.fetchCompleted && this.textBox){
                this.textBox.placeAt(columnHeaderNodes[1].firstChild, "replace");
                this.textBox.focus();
            }
        },
        _onItemsLoaded: function(items){
            this.fetchCompleted = true;
            this.grid.focus._colHeadFocusIdx=null;
            this.grid.focus._colHeadNode=null;
        },
        doTextBoxFilter: function(){
            var cell = this.grid.layout.cells[1];

            var obj = {
                    "datatype": "string",
                    "args": null,
                    "isColumn": true
                },
                operands = [lang.mixin({"data": cell}, obj)];

            obj.isColumn = false;
            operands.push(lang.mixin({"data": this.textBox.get("value")}, obj));

            this.grid.layer("filter").filterDef(this.builder.buildExpression({
                "op": "logicall",
                "data": [{
                    "op": "contains",
                    "data": operands
                }]
            }));

            var _this = this;
            var oldVal = this.textBox.get("value");
            var handle = aspect.after(this.grid, "_refresh", function() {
                handle.remove();

                _this.textBox.focus();
                
                var items = _this.grid.layer("filter")._items.length ? _this.grid.layer("filter")._items : this._arrayOfAllItems;

                _this.resetSelectFunc(items);
            });

            setTimeout(lang.hitch(this.grid, "_refresh"), 10);
        },
        resetSelectFunc: function(items){
            var _this = this;
            var allSelected = true;
            for(var key in items){
                var idx = _this.grid.dropDownDialog.getSelectedIds().indexOf(String(items[key].id));
                _this.grid.rowSelectCell.toggleRow(_this.grid.getItemIndex(items[key]), (idx!=-1));

                if(idx == -1){
                    allSelected = false;
                }
            }

            /*
            we reset the grid count to the total of all items instead of total of filtered items.
            This is to make sure that the select all check box will not be checked if the total of selected items exceed the number of filtered items
             */
            this.grid.rowCount = this.grid.store._arrayOfAllItems.length;

            if(!allSelected){
                var selector = dojo.byId(this.grid.id + "_rowSelector_-1");
                if(selector){
                    html.toggleClass(selector, "dijitCheckBoxChecked", false);
                    selector.setAttribute("aria-checked", false);
                }
            }
        },
        destroy: function(){
            this.grid.unwrap("filter");
            array.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var DropDownDialog = declare("buildspace.widget.forms.DropDownDialog", [ContentPane, _TemplatedMixin, _FormMixin, _DialogMixin], {
        title: "",
        doLayout: false,
        autofocus: false,
        baseClass: "multiSelectDropDown",
        _firstFocusItem: null,
        _lastFocusItem: null,
        templateString: template,
        isResetDataStore:  false,
        constructor: function(args){
            this.dropDownButton = args.dropDownButton;
            this.store = this.dropDownButton.dataStore;
            this.sortOrder = this.dropDownButton.storeSort;

            dojox.grid.EnhancedGrid.registerPlugin(Filter);

            this.grid = new dojox.grid.EnhancedGrid({
                sortInitialOrder: this.sortOrder,
                store: this.store,
                structure: [{'name': '', 'field': 'name', 'width': 'auto'}],
                style: "border:none;padding:0;margin:0;",
                canSort: function(){return false;},
                dropDownDialog: this,
                plugins: {
                    indirectSelection: {
                        headerSelector:true,
                        width:"20px",
                        styles:"text-align:center;"
                    },
                    buildspaceDropDownFilter: {}
                }
            });
        },
        postCreate: function(){
            this.inherited(arguments);

            var _this = this;
            this._connects = [];
            this._selectedIds = [];

            this.addChild(this.grid);

            this._connects.push(connect.connect(this.grid, 'onCellClick', function(e){
                _this._onCheckboxClicked(e.rowIndex);
            }));

            this._connects.push(connect.connect(this.grid.rowSelectCell, 'toggleAllSelection', function(rowIndex){
                _this.toggleSelectAll(this.getValue(-1), false);
            }));

            this._lastFocusItem = this._firstFocusItem = dojo.byId(this.grid.id + "_dropDownFilterTextBox");
        },
        getSelectedIds: function(){
            return this._selectedIds;
        },
        _onCheckboxClicked: function (rowIndex) {
            var _this = this;
            var grid = this.grid;
            var selectedItem = grid.getItem(rowIndex);
            var check = true;

            if(selectedItem){
                var idx = _this._selectedIds.indexOf(String(selectedItem.id));

                if( idx > -1){
                    check = false;
                    _this._selectedIds.splice(idx, 1);
                }else{
                    _this._selectedIds.push(String(selectedItem.id));
                }
            }

            grid.rowSelectCell.toggleRow(rowIndex, check);

            _this._updateContent(false);
        },
        toggleSelectAll: function (isChecked, disableDefer) {
            if(isChecked){
                var items = this.grid.layer("filter")._items.length ? this.grid.layer("filter")._items : this.grid.store._arrayOfAllItems;

                if(items.length){
                    var _this = this;
                    this._selectedIds = new Array(items.length);
                    dojo.forEach(items, function(selectedItem, idx){
                        _this._selectedIds[idx] = String(selectedItem.id);
                    });
                }
            }else{
                this._selectedIds = [];

                this.grid.selection.deselectAll();

                // need to rerun this 'uncheck' codes because if this function is not called through
                // this.grid.rowSelectCell.toggleAllSelection callback then we need to manually uncheck the select all checkbox
                var selector = dojo.byId(this.grid.id + "_rowSelector_-1");
                if(selector){
                    html.toggleClass(selector, "dijitCheckBoxChecked", false);
                    selector.setAttribute("aria-checked", false);
                }
            }

            this._updateContent(disableDefer);
        },
        _updateContent: function(disableDefer){
            this.dropDownButton._hidden.value = this._selectedIds.join(',');

            this.dropDownButton.set("label", lang.replace(nls.multiSelectLabelText, {num: this._selectedIds.length}));

            if(!disableDefer)
                this.dropDownButton.defer("onClick");

            setTimeout(lang.hitch(this, 'focus'), 10);
        },
        orient: function(/*DomNode*/ node, /*String*/ aroundCorner, /*String*/ tooltipCorner){
            // summary:
            //      Configure widget to be displayed in given position relative to the button.
            //      This is called from the dijit.popup code, and should not be called
            //      directly.
            // tags:
            //      protected

            // Note: intentionally not using dijitTooltip class since that sets position:absolute, which
            // confuses dijit/popup trying to get the size of the tooltip.
            var newC = {
                "MR-ML": "dijitTooltipRight",
                "ML-MR": "dijitTooltipLeft",
                "TM-BM": "dijitTooltipAbove",
                "BM-TM": "dijitTooltipBelow",
                "BL-TL": "dijitTooltipBelow dijitTooltipABLeft",
                "TL-BL": "dijitTooltipAbove dijitTooltipABLeft",
                "BR-TR": "dijitTooltipBelow dijitTooltipABRight",
                "TR-BR": "dijitTooltipAbove dijitTooltipABRight",
                "BR-BL": "dijitTooltipRight",
                "BL-BR": "dijitTooltipLeft"
            }[aroundCorner + "-" + tooltipCorner];

            domClass.replace(this.domNode, newC, this._currentOrientClass || "");
            this._currentOrientClass = newC;
        },
        focus: function(){
            // summary:
            //      Focus on first field
            this._getFocusItems();
            focusUtil.focus(this._firstFocusItem);
        },
        onOpen: function(/*Object*/ pos){
            this.orient(this.domNode,pos.aroundCorner, pos.corner);

            if(this.grid && this.isResetDataStore){
                this.grid.selection.deselectAll();

                this.grid.layer("filter").filterDef(null);

                var filterTxtBox = dijit.byId(this.grid.id + "_dropDownFilterTextBox");
                if(filterTxtBox){
                    filterTxtBox.set("value", null);
                }

                this.grid.store.close();
                this.grid.set("store", this.dropDownButton.dataStore);
                this.grid._refresh();

                var items = this.grid.layer("filter")._items.length ? this.grid.layer("filter")._items : this.grid.store._arrayOfAllItems;

                if(items.length && this._selectedIds.length){
                    var _this = this;
                    for(var i=0; i < this._selectedIds.length; i++){
                        for(var x=0; x < items.length; x++){
                            if(String(items[x].id) == String(_this._selectedIds[i])){
                                _this.grid.rowSelectCell.toggleRow(_this.grid.getItemIndex(items[x]), true);
                            }
                        }
                    }
                }

                this._updateContent(true);

                this.isResetDataStore = false;
            }

            this._onShow(); // lazy load trigger  (TODO: shouldn't we load before positioning?)
        },
        _getFocusItems: function(){
            this._lastFocusItem = this._firstFocusItem = dojo.byId(this.grid.id + "_dropDownFilterTextBox");
        },
        onClose: function(){
            this.onHide();
        },
        close: function(){
            this.defer("onCancel");
        },
        destroy: function(){
            this.grid = null;
            this.dropDownButton = null;
            array.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return dojo.declare("buildspace.widget.forms.MultiSelectDropDown", [DropDownButton], {
        dataStore: null, // DS - MUST be provided at construction
        storeSort: null,
        dataCarrierId: "",  // this can specify the id of a hidden field to use to store the selected values.  Optional - if not specified a new hidden field will be created.
        dropDownWidth: null, //specify custom dropdown width. Defaults to dropdown button width
        _hidden: null,
        baseClass: "multiSelectDropDown",
        postMixInProperties: function () {
            this.inherited(arguments);
            this.label =  lang.replace(nls.multiSelectLabelText, {num: 0});

            if (!this.dataStore)
                throw "Data store must be provided";

            if(!this.storeSort)
                this.storeSort = [{ attribute: this.textField}];

            var width = (this.dropDownWidth) ? "width:"+this.dropDownWidth+";" : "";

            this.dropDown = new DropDownDialog({
                dropDownButton: this,
                className: "multiSelectDropDown",
                style: "border:none;"+width
            });
        },
        postCreate: function () {
            this.inherited(arguments);

            if (this.dataCarrierId) {
                this._hidden = dojo.byId(this.dataCarrierId);
            } else {
                var hid = this._hidden = document.createElement("input");
                hid.type = "hidden";
                hid.name = this.name;
                hid.value = "";
                dojo.place(hid, this.domNode);
            }
        },
        resetDataStore: function(store){
            this.dataStore = store;
            this.dropDown.store = this.dataStore;
            this.dropDown.isResetDataStore = true;

            var items = store.hasOwnProperty('_jsonData') ? store._jsonData.items : [];
            var newSelectedIds = [];

            if(items.length){
                var selectedIds = this.getSelectedIds();

                for(var x=0; x < items.length; x++){
                    var idx = selectedIds.indexOf(String(items[x].id));
                    if(idx !== -1){
                        newSelectedIds.push(String(items[x].id));
                    }
                }
            }

            this.dropDown._selectedIds = newSelectedIds;

            this.dropDown._updateContent(true);
        },
        focus: function () {
            // ignore those, because they prevent clicking within the filter box
        },
        getSelectedIds: function(){
            return this.dropDown.getSelectedIds();
        },
        _onClick: function(e){
            event.stop(e);
        },
        onClick: function(e){
        }
    });
});
