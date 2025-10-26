define('buildspace/apps/ViewTendererReport/AssignContractorDialog',[
    'dojo/_base/declare',
    'dojo/aspect',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/currency",
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/ViewTenderer'
], function(declare, aspect, lang, connect, html, dom, keys, domStyle, Currency, GridFormatter, IndirectSelection, nls) {

    var ContractorGrid = declare('buildspace.apps.ViewTendererReport.ContractorGrid', dojox.grid.EnhancedGrid, {
        builderContainer: null,
        project: null,
        style: "border-top:none;",
        region: 'center',
        constructor: function(args) {
            this.inherited(arguments);

            this.plugins = {
                indirectSelection: {
                    headerSelector: true,
                    width: "20px",
                    styles: "text-align:center;"
                }
            };
        },
        canSort: function(inSortInfo) {
            return false;
        },
        onStyleRow: function(e) {
            this.inherited(arguments);

            if(e.node.children[0])
            {
                if(e.node.children[0].children[0].rows.length >= 2)
                {
                    var elemToHide = e.node.children[0].children[0].rows[1],
                        childElement = e.node.children[0].children[0].rows[0].children;

                    elemToHide.parentNode.removeChild(elemToHide);

                    dojo.forEach(childElement, function(child, i)
                    {
                        var rowSpan = dojo.attr(child, 'rowSpan');

                        if(!rowSpan || rowSpan < 2)
                            dojo.attr(child, 'rowSpan', 2);
                    });
                }
            }
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            // this.store.fetch({
            //     onComplete: function (items) {
            //         dojo.forEach(items, function (item, index) {

            //             if(item.awarded[0])
            //             {
            //                 var diff = item.adjusted_total[0] - item.total[0];

            //                 if(diff > 0)
            //                 {
            //                     diff = '<span style="color:blue">' + Currency.format(diff) + '</span>';
            //                 }
            //                 else if(diff < 0)
            //                 {
            //                     diff = '<span style="color:red">' + Currency.format(diff) + '</span>';
            //                 }
            //                 else
            //                 {
            //                     diff = Currency.format(diff);
            //                 }

            //                 self.structure.cells[1][0].name = nls.diff+': ' + diff;
            //                 self.set('structure', self.structure);
            //             }
            //         });
            //     }
            // });

            aspect.after(self, "_onFetchComplete", function() {
                self.markedCheckBoxObject(self, self.builderContainer.selectedTenderers);
            });
        },
        startup: function() {
            var self = this;
            self.inherited(arguments);

            this._connects.push(connect.connect(this, 'onCellClick', function(e) {
                if (e.cell.name !== "") {
                    return;
                }

                self.singleCheckBoxSelection(e);
            }));

            this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue) {
                self.toggleAllSelection(newValue);
            }));
        },
        singleCheckBoxSelection: function(e) {
            var self = this,
                item = this.getItem(e.rowIndex);

            if ( this.selection.isSelected(e.rowIndex) ) {
                self.builderContainer.selectedTenderers.put({ id: item.id[0] });
            } else {
                self.builderContainer.selectedTenderers.remove(item.id[0]);
            }
        },
        toggleAllSelection: function(checked) {
            var self = this, selection = this.selection;

            selection.selectRange(0, self.rowCount-1);

            if (checked) {
                self.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item.id > 0) {
                                self.builderContainer.selectedTenderers.put({ id: item.id[0] });
                            }
                        });
                    }
                });
            } else {
                selection.deselectAll();

                self.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item.id > 0) {
                                self.builderContainer.selectedTenderers.remove(item.id[0]);
                            }
                        });
                    }
                });
            }
        },
        markedCheckBoxObject: function(grid, selectedRowStore) {
            var store = grid.store;

            selectedRowStore.query().forEach(function(item) {
                if (item.id == buildspace.constants.GRID_LAST_ROW) {
                    return;
                }

                store.fetchItemByIdentity({
                    identity: item.id,
                    onItem: function(node) {
                        if ( ! node ) {
                            return;
                        }

                        return grid.rowSelectCell.toggleRow(node._0, true);
                    }
                });
            });
        },
        destroy: function() {
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var ContractorGridContainer = declare('buildspace.apps.ViewTendererReport.ContractorGridContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;border:0px;width:100%;height:100%;",
        gutters: false,
        region: 'center',
        project: null,
        grid: null,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, {project: self.project, region:"center"});
            var grid = this.grid = new ContractorGrid(self.gridOpts);

            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });

    return declare('buildspace.apps.ViewTendererReport.AssignContractorDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.assignContractors,
        builderContainer: null,
        project: null,
        formValues: [],
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;
            this.title = nls.assignContractors+' :: '+buildspace.truncateString(this.project.title, 45);
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
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:850px;height:350px;",
                gutters: false
            }),formatter = new GridFormatter();

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border:none;"
            });

            var store = new dojo.data.ItemFileWriteStore({
                    url:"viewTenderer/getContractors/id/"+this.project.id,
                    clearOnClose: true
                }),
                contractorGrid = new ContractorGridContainer({
                    project: this.project,
                    gridOpts: {
                        id: 'viewTenderer-contractorGrid_'+this.project.id,
                        store: store,
                        structure: {
                            cells: [
                                [{
                                    name: 'No.',
                                    field: 'id',
                                    width:'30px',
                                    styles:'text-align:center;',
                                    formatter: formatter.rowCountCellFormatter,
                                    rowSpan: 2
                                },{
                                    name: nls.name,
                                    field: 'name',
                                    width:'auto',
                                    rowSpan: 2
                                },{
                                    name: nls.originalTotal,
                                    field: 'total', width:'120px',
                                    headerClasses: "typeHeader1",
                                    styles:'text-align:right;',
                                    formatter: formatter.unEditableCurrencyCellFormatter
                                },{
                                    name: nls.adjustedTotal,
                                    field: 'adjusted_total',
                                    headerClasses: "typeHeader1",
                                    width:'120px', styles:'text-align:right;',
                                    formatter: formatter.unEditableCurrencyCellFormatter
                                }],
                                [{
                                    colSpan: 2,
                                    headerClasses: "staticHeader typeHeader1",
                                    headerId: 1,
                                    hidden: false,
                                    name: nls.diff + ": " + Currency.format(0),
                                    styles: "text-align:center;"
                                }]
                            ]
                        },
                        builderContainer: self.builderContainer
                    }
                });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(contractorGrid);

            return borderContainer;
        }
    });
});