define('buildspace/apps/ResourceLibraryReport/rfqRateSelectionGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojo/_base/connect",
    "dojo/aspect",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/ResourceLibrary',
    "dojox/grid/enhanced/plugins/Filter"
], function(declare, lang, array, domAttr, connect, aspect, Menu, Selector, Rearrange, FormulatedColumn, evt, keys, focusUtil, html, xhr, PopupMenuItem, Textarea, FormulaTextBox, DropDownButton, DropDownMenu, MenuItem, IndirectSelection, nls, Filter) {

    var ResourceLibraryRFQRateSelectionGrid = declare('buildspace.apps.ResourceLibraryReport.ResourceLibraryRFQRateSelectionGrid', dojox.grid.EnhancedGrid, {
        type: null,
        libraryId: 0,
        tradeId: 0,
        itemId: 0,
        rfqRateSelectionId: -1,
        newRfqRateSelectionId: -1,
        currentSelectedSortingType: 1,
        currentSelectedSortingTypeText: null,
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        keepSelection: true,
        rowSelector: '0px',
        formInfo: null,
        previousRFQRatesId: null,
        constructor:function(args){
            this.rearranger                     = Rearrange(this, {});
            this.formulatedColumn               = FormulatedColumn(this,{});
            this.currentSelectedSortingType     = args.formInfo.sorting_type;
            this.currentSelectedSortingTypeText = args.formInfo.sorting_type_text;

            this.itemIds  = [];
            this.connects = [];

            this.plugins = {
                indirectSelection: {
                    headerSelector: true,
                    width: "20px",
                    styles: "text-align: center;"
                },
                filter: {
                    closeFilterbarButton: false,
                    ruleCount: 5,
                    itemsName: "id"
                }
            };

            return this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            aspect.after(this, "_onFetchComplete", function() {
                return self.markedCheckBoxObject(self.formInfo.previousRFQRatesId, true);
            });

            this._connects.push(connect.connect(this, 'onCellClick', function(e) {
                if (e.cell.name !== "") {
                    return false;
                }

                return self.selectTree(e);
            }));

            this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue) {
                self.toggleAllSelection(newValue);
            }));
        },
        selectTree: function(e){
            var rowIndex = e.rowIndex,
                newValue = this.selection.selected[rowIndex],
                item = this.getItem(rowIndex);

            if(item) {
                this.pushItemIdIntoGridArray(item, newValue);

                this.saveRateAssignment();
            }
        },
        toggleAllSelection: function(checked) {
            var grid = this, selection = grid.selection;

            if (checked) {
                selection.selectRange(0, grid.rowCount-1);
                grid.itemIds = [];
                grid.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item.id > 0){
                                grid.itemIds.push(item.id[0]);
                            }
                        });
                    }
                });
            } else {
                selection.deselectAll();
                grid.itemIds = [];
            }

            grid.saveRateAssignment();
        },
        pushItemIdIntoGridArray: function(item, select){
            var grid = this;
            var idx = dojo.indexOf(grid.itemIds, item.id[0]);
            if(select){
                if(idx == -1){
                    grid.itemIds.push(item.id[0]);
                }
            }else{
                if(idx != -1){
                    grid.itemIds.splice(idx, 1);
                }
            }
        },
        markedCheckBoxObject: function(items, newValue) {
            var itemIndex, key, self, store, _results;

            self      = this;
            store     = this.store;
            itemIndex = -1;
            _results  = [];

            for (key in items) {
                _results.push(store.fetchItemByIdentity({ identity: key, onItem: function(node) {
                    itemIndex = node._0;
                    self.pushItemIdIntoGridArray(node, newValue);
                    return self.selection.setSelected(itemIndex, newValue);
                }}));
            }

            return _results;
        },
        sortBy: function(opt) {
            var self = this;

            switch(opt) {
                case 'rateAverage':
                    var sortBy = buildspace.constants.RESOURCE_RATE_SORT_AVERAGE;
                    break;
                case 'rateHighest':
                    var sortBy = buildspace.constants.RESOURCE_RATE_SORT_HIGHEST;
                    break;
                case 'rateLowest':
                    var sortBy = buildspace.constants.RESOURCE_RATE_SORT_LOWEST;
                    break;
                case 'rateMedian':
                    var sortBy = buildspace.constants.RESOURCE_RATE_SORT_MEDIAN;
                    break;
            }

            self.currentSelectedSortingType = sortBy;

            self.saveRateAssignment();
        },
        saveRateAssignment: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.applying+'. '+nls.pleaseWait+'...'
            });

            pb.show();

            var formValues = {
                "resourceItemId": self.itemId,
                "resource_item_selected_rate[resource_item_id]": self.itemId,
                "resource_item_selected_rate[sorting_type]": self.currentSelectedSortingType,
                "resource_item_selected_rate[rfq_item_rates_list][]": self.itemIds,
                "resource_item_selected_rate[_csrf_token]": self.formInfo["_csrf_token"]
            }

            var xhrArgs = {
                url: 'resourceLibrary/updateSelectedRatesFromRFQ',
                content: formValues,
                handleAs: 'json',
                load: function(data) {
                    if(data.success){
                        self.updateToolbarLabelDescription(data.rateDisplayType, data.newRate);
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            };

            dojo.xhrPost(xhrArgs);
        },
        updateToolbarLabelDescription: function(rateDisplayType, newRate) {
            var self = this;

            var currentSelectedSortingTypeSelectionLabel = dijit.byId('currentRateDisplayTypeSelectionLabel-'+self.libraryId);
            var currentSelectedRatesLabel                = dijit.byId('currentRateDisplayLabel-'+self.libraryId);

            if ( currentSelectedSortingTypeSelectionLabel ) {
                currentSelectedSortingTypeSelectionLabel.set('label', nls.currentRateDisplayType + ": <strong style=\"color: blue;\">" + rateDisplayType + "</strong>");
            }

            if ( currentSelectedRatesLabel ) {
                currentSelectedRatesLabel.set('label', nls.currentRateDisplay + ": <strong style=\"color: blue;\">" + newRate + "</strong>");
            }
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.ResourceLibraryReport.grid', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;border:0px;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: null,
        libraryId: 0,
        tradeId: 0,
        gridOpts: {},
        type: null,
        postCreate: function() {
            this.inherited(arguments);

            var self = this;

            lang.mixin(self.gridOpts, { libraryId: self.libraryId, tradeId: self.tradeId, type:self.type, region:"center", borderContainerWidget: self });

            var grid        = this.grid = new ResourceLibraryRFQRateSelectionGrid(self.gridOpts);
            var toolbar     = new dijit.Toolbar({region: "top", style:"padding:3px;border-bottom:none;width:100%;"});
            var sortOptions = ['rateAverage', 'rateHighest', 'rateLowest', 'rateMedian'];
            var menu        = new DropDownMenu({ style: "display: none;"});

            dojo.forEach(sortOptions, function(opt) {
                var menuItem = new MenuItem({
                    label: nls[opt],
                    onClick: function(){
                        grid.sortBy(opt);
                    }
                });

                menu.addChild(menuItem);
            });

            toolbar.addChild(
                new DropDownButton({
                    id: 'currentRateDisplayTypeSelectionLabel-'+self.libraryId,
                    label: nls.currentRateDisplayType + ": <strong style=\"color: blue;\">" + self.gridOpts.formInfo.sorting_type_text + "</strong>",
                    name: "sortBy",
                    dropDown: menu
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    id: 'currentRateDisplayLabel-'+self.libraryId,
                    label: nls.currentRateDisplay + ": <strong style=\"color: blue;\">" + self.gridOpts.formInfo.previousRFQRate + "</strong>",
                    showLabel: true,
                    style: "cursor: default;"
                })
            );

            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('resourceLibraryReportGrid'+self.libraryId+'-stackContainer');
            if (container) {
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId, executeScripts: true }, node );
                container.addChild(child);
                child.set('content', self);
                container.selectChild(self.pageId);
            }
        }
    });
});