define('buildspace/apps/StockOut/WorkArea', [
"dojo/_base/declare",
'dojo/request',
'dijit/layout/ContentPane',
'dijit/layout/BorderContainer',
'dijit/layout/TabContainer',
'buildspace/widget/grid/cells/Formatter',
'dojox/grid/enhanced/plugins/Rearrange',
'buildspace/widget/grid/plugins/FormulatedColumn',
'dijit/Toolbar',
'dijit/form/Button',
'buildspace/widget/grid/Filter',
'./StockOutItemGridContainer',
'./StockOutDialog/StockOutListingDialog',
'dojo/i18n!../../nls/StockOut'],
function(declare, request, ContentPane, BorderContainer, TabContainer, Formatter, Rearrange, FormulatedColumn, Toolbar, Button, Filter, StockOutItemGridContainer, StockOutListingDialog, nls) {
    var ResourceGrid = declare("buildspace.apps.StockOut.ResourceGrid", dojox.grid.EnhancedGrid, {
        style: "border-top:none;",
        keepSelection: true,
        rowSelector: "0px",
        region: 'center',
        stockOutWorkAreaContainer: null,
        project: null,
        constructor: function() {
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this, {});
        },
        canSort: function() {
            return false;
        },
        refreshGrid: function() {
            this.store.save();
            this.store.close();
            this.setStore(this.store);
        }
    });

    return declare('buildspace.apps.StockOut.WorkArea', TabContainer, {
        region: "center",
        style: "padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        project: null,
        gutters: false,
        postCreate: function() {
            var pb, self;
            this.inherited(arguments);
            self = this;
            pb = new buildspace.dialog.indeterminateProgressBar({
                title: nls.processing + "..."
            });

            pb.show().then(function(){
                return request.get("stockOut/getResourceWithStockInsByProject/projectId/" + parseInt(self.project.id), {
                    handleAs: 'json'
                }).then(function(response) {
                    var firstPane;
                    self.createContentPaneTab('main-stockOutResourceList', nls.resource, self.createResourceContainer(response), false);
                    firstPane = dijit.byId('main-stockOutResourceList');
                    self.selectChild(firstPane);
                    pb.hide();
                }, function(error) {
                    pb.hide();
                });
            });
        },
        createResourceContainer: function(store, self) {
            var filter, grid, gridContainer, toolbar;
            if (self == null) {
                self = this;
            }
            store = new dojo.data.ItemFileWriteStore({
                url: "stockOut/getResourceWithStockInsByProject/projectId/" + parseInt(this.project.id),
                clearOnClose: true,
                urlPreventCache: true
            });
            grid = new ResourceGrid({
                structure: self.getResourceGridLayout(),
                store: store,
                project: this.project,
                projectId: parseInt(this.project.id),
                stockOutWorkAreaContainer: self,
                onRowDblClick: function(e) {
                    var resource, tabId, tabPane;
                    resource = this.getItem(e.rowIndex);
                    if (parseInt(resource.id) > 0) {
                        tabId = "main-stockOutResourceList-" + parseInt(resource.id);
                        if (tabPane = dijit.byId(tabId)) {
                            self.selectChild(tabPane);
                        } else {
                            self.createContentPaneTab(tabId, resource.name[0], self.createResourceTradeGridView(resource), true);
                        }
                    }
                }
            });
            filter = new Filter({
                region: 'top',
                grid: grid,
                editableGrid: false,
                filterFields: [{
                    'name': nls.description
                }]
            });
            toolbar = new dijit.Toolbar({
                region: "top",
                style: "padding:2px;border-bottom:none;width:100%;"
            });
            toolbar.addChild(new dijit.form.Button({
                label: nls.manageStockOut,
                iconClass: "icon-16-container icon-16-import",
                onClick: function() {
                    var dialog = new StockOutListingDialog({
                        title: nls.manageStockOut,
                        project: self.project
                    });
                    dialog.show();
                }
            }));
            gridContainer = new BorderContainer({
                region: 'top',
                gutters: false
            });
            gridContainer.addChild(filter);
            gridContainer.addChild(toolbar);
            gridContainer.addChild(grid);
            return gridContainer;
        },
        createResourceTradeGridView: function(resource, self) {
            var borderContainer, controller, controllerPane, grid, masterBorderContainer, stackContainer, stackPane, stackPaneId, store;
            if (self == null) {
                self = this;
            }
            stackPaneId = "stockOutContainerPane-" + parseInt(resource.id);
            stackContainer = dijit.byId(stackPaneId + "-stackContainer");
            if (stackContainer) {
                dijit.byId(stackPaneId + "-stackContainer").destroyRecursive();
            }
            stackContainer = self.stackContainer = new dijit.layout.StackContainer({
                id: stackPaneId + "-stackContainer",
                style: 'width:100%;height:100%;',
                region: "center"
            });
            store = new dojo.data.ItemFileWriteStore({
                url: "stockOut/getResourceTradeWithStockInsByProject/projectId/" + parseInt(self.project.id) + "/resourceId/" + parseInt(resource.id),
                clearOnClose: true,
                urlPreventCache: true
            });
            grid = new ResourceGrid({
                structure: self.getResourceTradeGridLayout(),
                project: self.project,
                projectId: parseInt(self.project.id),
                stockOutWorkAreaContainer: self,
                resource: resource,
                store: store,
                onRowDblClick: function(e) {
                    var resourceTrade;
                    resourceTrade = this.getItem(e.rowIndex);
                    if (parseInt(resourceTrade.id) > 0) {
                        self.createResourceItemGridView(resource, resourceTrade);
                    }
                }
            });
            borderContainer = new dijit.layout.BorderContainer({
                style: "padding:0px;width:100%;height:100%;",
                baseClass: "form",
                gutters: false,
                region: "center"
            });
            borderContainer.addChild(new Filter({
                region: 'top',
                grid: grid,
                editableGrid: false,
                filterFields: [{
                    'description': nls.description
                }]
            }));
            borderContainer.addChild(grid);
            stackPane = new dijit.layout.ContentPane({
                title: resource.name,
                content: borderContainer
            });
            stackContainer.addChild(stackPane);
            controller = new dijit.layout.StackController({
                region: "top",
                containerId: stackPaneId + "-stackContainer"
            });
            controllerPane = new dijit.layout.ContentPane({
                style: "padding: 0px; overflow: hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });
            masterBorderContainer = new dijit.layout.BorderContainer({
                style: "padding:0px;width:100%;height:100%;",
                baseClass: "form",
                gutters: false,
                region: "center"
            });
            masterBorderContainer.addChild(stackContainer);
            masterBorderContainer.addChild(controllerPane);
            dojo.subscribe(stackPaneId + "-stackContainer-selectChild", "", function(page) {
                var children, index, widget, _results;
                widget = dijit.byId(stackPaneId + "-stackContainer");
                if (widget) {
                    children = widget.getChildren();
                    index = dojo.indexOf(children, dijit.byId(page.id)) + 1;
                    _results = [];
                    while (children.length > index) {
                        widget.removeChild(children[index]);
                        children[index].destroyRecursive();
                        _results.push(index = index + 1);
                    }
                    return _results;
                }
            });
            masterBorderContainer.startup();
            return masterBorderContainer;
        },
        createResourceItemGridView: function(resource, resourceTrade, self) {
            var gridContainer;
            if (self == null) {
                self = this;
            }
            gridContainer = new StockOutItemGridContainer({
                project: this.project,
                resource: resource,
                resourceTrade: resourceTrade,
                stockOutWorkAreaContainer: self
            });
            self.makePane(resource, resourceTrade.description[0], gridContainer);
        },
        getResourceGridLayout: function() {
            var formatter = new Formatter;
            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: formatter.rowCountCellFormatter,
                noresize: true
            }, {
                name: nls.description,
                field: "name",
                width: 'auto',
                formatter: formatter.treeCellFormatter,
                noresize: true
            }, {
                name: nls.totalCost,
                field: "total_cost",
                width: '140px',
                styles: 'text-align: right;',
                formatter: formatter.unEditableCurrencyCellFormatter,
                noresize: true
            }];
        },
        getResourceTradeGridLayout: function() {
            var formatter = new Formatter;
            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: formatter.rowCountCellFormatter,
                noresize: true
            }, {
                name: nls.description,
                field: "description",
                width: 'auto',
                formatter: formatter.treeCellFormatter,
                noresize: true
            }, {
                name: nls.totalCost,
                field: "total_cost",
                width: '140px',
                styles: 'text-align: right;',
                formatter: formatter.unEditableCurrencyCellFormatter,
                noresize: true
            }];
        },
        createContentPaneTab: function(id, title, content, closable) {
            var pane = new dijit.layout.ContentPane({
                closable: closable,
                id: id,
                style: "padding:0px;border:0px;margin:0px;overflow:hidden;",
                title: buildspace.truncateString(title, 60),
                content: content
            });
            this.addChild(pane);
            this.selectChild(pane);
        },
        makePane: function(resource, name, content) {
            var pane, stackContainer;
            stackContainer = dijit.byId("stockOutContainerPane-" + parseInt(resource.id) + "-stackContainer");
            pane = new dijit.layout.ContentPane({
                title: buildspace.truncateString(name, 35),
                content: content
            });
            stackContainer.addChild(pane);
            stackContainer.selectChild(pane);
        }
    });
});
