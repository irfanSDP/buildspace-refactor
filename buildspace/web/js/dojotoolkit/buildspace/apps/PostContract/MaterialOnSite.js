define('buildspace/apps/PostContract/MaterialOnSite',[
    'dojo/_base/declare',
    'dojo/aspect',
    'dojo/currency',
    'dojo/number',
    "dojo/when",
    "dijit/layout/ContentPane",
    "./MaterialOnSite/MaterialOnSiteGrid",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'],
    function(declare, aspect, currency, number, when, ContentPane, MaterialOnSiteGrid, GridFormatter, nls) {

    var CustomFormatter = {
        statusCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            if(item.id == buildspace.constants.GRID_LAST_ROW){
                cell.customClasses.push('disable-cell');
                return "&nbsp;";
            }

            if(cellValue == buildspace.apps.PostContract.ProjectStructureConstants.MATERIAL_ON_SITE_CLAIMED){
                cell.customClasses.push('green-cell');
                return nls.claimed.toUpperCase();
            }else{
                cell.customClasses.push('yellow-cell');
                return nls.thisClaim.toUpperCase();
            }
        },
        mosTotalCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            if(item.id == buildspace.constants.GRID_LAST_ROW){
                cell.customClasses.push('disable-cell');
                return "&nbsp;";
            }

            cell.customClasses.push('disable-cell');
            var value = number.parse(cellValue);

            if(value < 0){
                return '<span style="color:#FF0000">'+currency.format(value)+'</span>';
            }else{
                return value == 0 ? "&nbsp;" : '<span style="color:#42b449;">'+currency.format(value)+'</span>';
            }
        },
        currencyCellFormatter: function(cellValue, rowIdx, cell){
            var value = number.parse(cellValue),
                item = this.grid.getItem(rowIdx);

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = currency.format(value);
                cellValue = value >= 0 ? cellValue : '<span style="color:#FF0000">'+cellValue+'</span>';
            }

            if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        qtyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                formattedValue = "&nbsp;";

            if(!isNaN(value) && value != 0 && value != null){
                formattedValue = number.format(value, {places: 2});
            }

            if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                cell.customClasses.push('disable-cell');
            }

            return value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
        }
    };

    return declare('buildspace.apps.PostContract.MaterialOnSite', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;border:none;width:100%;height:100%;",
        region: "center",
        gutters: false,
        rootProject: null,
        postCreate: function() {
            this.inherited(arguments);

            var statusOptions = {
                options: [
                    nls.thisClaim,
                    nls.claimed
                ],
                values: [
                    buildspace.apps.PostContract.ProjectStructureConstants.MATERIAL_ON_SITE_THIS_CLAIM,
                    buildspace.apps.PostContract.ProjectStructureConstants.MATERIAL_ON_SITE_CLAIMED
                ]
            };

            var self = this,
                formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "materialOnSite/getMaterialOnSiteList/pid/"+self.rootProject.id,
                    clearOnClose: true
                }),
                grid = new MaterialOnSiteGrid({
                    id: 'material_on_site-'+self.rootProject.id,
                    stackContainerTitle: nls.scheduleOfRates,
                    pageId: 'material_on_site-'+self.rootProject.id,
                    project: self.rootProject,
                    type: 'vo',
                    gridOpts: {
                        store: store,
                        addUrl: 'materialOnSite/materialOnSiteAdd',
                        updateUrl: 'materialOnSite/materialOnSiteUpdate',
                        deleteUrl: 'materialOnSite/materialOnSiteDelete',
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter},
                            {name: nls.description, field: 'description', width:'auto', editable:true, cellType:'buildspace.widget.grid.cells.Textarea'},
                            {name: nls.reductionPercentage, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', field: 'reduction_percentage', width:'150px', editable:true, styles:'text-align:right;', formatter: formatter.editableClaimPercentageCellFormatter},
                            {name: nls.totalMos, field: 'total', width:'150px', styles:'text-align:right;', formatter: CustomFormatter.mosTotalCellFormatter},
                            {name: nls.totalMosAfterReduction, field: 'total_after_reduction', width:'150px', styles:'text-align:right;', formatter: CustomFormatter.mosTotalCellFormatter},
                            {name: nls.status, field: 'status', width:'80px', styles:'text-align:center;', editable: true, type: 'dojox.grid.cells.Select', options: statusOptions.options, values: statusOptions.values, formatter: CustomFormatter.statusCellFormatter},
                            {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);

                            if(_item.id > 0 && _item.description[0] !== null && _item.description[0].length > 0){
                                self.makeItemGrid(_item);
                            }
                        }
                    }
                });

            var gridContainer = this.makeGridContainer(grid, nls.materialOnSite);

            this.addChild(gridContainer);
        },
        makeItemGrid: function(materialOnSite) {
            var self = this;

            var unitQuery = dojo.xhrGet({
                    url: "materialOnSite/getUnits",
                    handleAs: "json"
                }),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show();

            when(unitQuery, function(uom){
                pb.hide();

                var hierarchyTypes = {
                        options: [
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT,
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT
                        ],
                        values: [
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER,
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM
                        ]
                    },
                    formatter = new GridFormatter(),
                    store = dojo.data.ItemFileWriteStore({
                        url: "materialOnSite/getMaterialOnSiteItemList/id/"+materialOnSite.id,
                        clearOnClose: true
                    }),
                    structure = [{
                        name: 'No',
                        field: 'id',
                        styles: "text-align:center;",
                        width: '30px',
                        formatter: formatter.rowCountCellFormatter,
                        noresize: true
                    },{
                        name: nls.description,
                        field: 'description',
                        width: 'auto',
                        editable: materialOnSite.status[0] == buildspace.apps.PostContract.ProjectStructureConstants.MATERIAL_ON_SITE_THIS_CLAIM,
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        formatter: formatter.treeCellFormatter,
                        noresize: true
                    },{
                        name: nls.type,
                        field: 'type',
                        width: '70px',
                        styles: 'text-align:center;',
                        editable: materialOnSite.status[0] == buildspace.apps.PostContract.ProjectStructureConstants.MATERIAL_ON_SITE_THIS_CLAIM,
                        type: 'dojox.grid.cells.Select',
                        options: hierarchyTypes.options,
                        values: hierarchyTypes.values,
                        formatter: formatter.typeCellFormatter,
                        noresize: true
                    },{
                        name: nls.unit,
                        field: 'uom_id',
                        width: '70px',
                        editable: materialOnSite.status[0] == buildspace.apps.PostContract.ProjectStructureConstants.MATERIAL_ON_SITE_THIS_CLAIM,
                        styles: 'text-align:center;',
                        type: 'dojox.grid.cells.Select',
                        options: uom.options,
                        values: uom.values,
                        formatter: formatter.unitIdCellFormatter,
                        noresize: true
                    },{
                        name: nls.deliveredQty,
                        field: 'delivered_qty',
                        editable: materialOnSite.status[0] == buildspace.apps.PostContract.ProjectStructureConstants.MATERIAL_ON_SITE_THIS_CLAIM,
                        width: '90px',
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        styles: "text-align:right;",
                        formatter: CustomFormatter.qtyCellFormatter,
                        noresize: true
                    },{
                        name: nls.usedQty,
                        field: 'used_qty',
                        editable: materialOnSite.status[0] == buildspace.apps.PostContract.ProjectStructureConstants.MATERIAL_ON_SITE_THIS_CLAIM,
                        width: '90px',
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        styles: "text-align:right;",
                        formatter: CustomFormatter.qtyCellFormatter,
                        noresize: true
                    },{
                        name: nls.balanceQty,
                        field: 'balance_qty',
                        width: '90px',
                        styles: "text-align:right;",
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: formatter.unEditableCurrencyCellFormatter,
                        noresize: true
                    },{
                        name: nls.rate,
                        field: 'rate-value',
                        editable: materialOnSite.status[0] == buildspace.apps.PostContract.ProjectStructureConstants.MATERIAL_ON_SITE_THIS_CLAIM,
                        styles: "text-align:right;",
                        width: '120px',
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: materialOnSite.status[0] == buildspace.apps.PostContract.ProjectStructureConstants.MATERIAL_ON_SITE_THIS_CLAIM ? CustomFormatter.currencyCellFormatter : formatter.unEditableCurrencyCellFormatter,
                        noresize: true
                    },{
                        name: nls.amount,
                        field: 'amount',
                        styles: "text-align:right;",
                        width: '120px',
                        formatter: formatter.unEditableCurrencyCellFormatter,
                        noresize: true
                    }],
                    grid = new MaterialOnSiteGrid({
                        project: self.rootProject,
                        materialOnSite: materialOnSite,
                        type: 'vo-items',
                        locked: materialOnSite.status[0] == buildspace.apps.PostContract.ProjectStructureConstants.MATERIAL_ON_SITE_CLAIMED,
                        gridOpts: {
                            store: store,
                            escapeHTMLInData: false,
                            addUrl: 'materialOnSite/materialOnSiteItemAdd',
                            updateUrl: 'materialOnSite/materialOnSiteItemUpdate',
                            deleteUrl: 'materialOnSite/materialOnSiteItemDelete',
                            indentUrl: 'materialOnSite/materialOnSiteItemIndent',
                            outdentUrl: 'materialOnSite/materialOnSiteItemOutdent',
                            pasteUrl: 'materialOnSite/materialOnSiteItemPaste',
                            structure: structure
                        }
                    });

                var title = materialOnSite.description;
                var pageId = 'material_on_site_items-'+materialOnSite.id+'-'+self.rootProject.id+'-page';

                self.appendNewStack(grid, pageId, title);
            });
        },
        makeGridContainer: function(content, title){
            var id = this.rootProject.id;
            var stackContainer = dijit.byId('materialOnSite-'+id+'-stackContainer');

            if(stackContainer){
                dijit.byId('materialOnSite-'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:none;',
                region: "center",
                id: 'materialOnSite-'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'materialOnSite-'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;margin:0;width:100%;height:100%;border:none;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('materialOnSite-'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('materialOnSite-'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){

                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();
                            index = index + 1;
                        }

                        var selectedIndex = page.grid.selection.selectedIndex;

                        page.grid.store.save();
                        page.grid.store.close();

                        var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                            handle.remove();

                            if(selectedIndex > -1){
                                this.scrollToRow(selectedIndex, true);
                                this.selection.setSelected(selectedIndex, true);
                            }
                        });

                        page.grid.sort();
                    }
                }
            });

            return borderContainer;
        },
        appendNewStack: function(content, pageId, title) {
            var self = this;

            var container = dijit.byId('materialOnSite-'+self.rootProject.id+'-stackContainer');

            if(!container){
                return;
            }

            var node = document.createElement("div");
            var child = new dojox.layout.ContentPane({
                title: buildspace.truncateString(title, 45),
                id: pageId,
                content: content,
                executeScripts: true
            }, node);
            container.addChild(child);
            container.selectChild(pageId);
        }
    });
});