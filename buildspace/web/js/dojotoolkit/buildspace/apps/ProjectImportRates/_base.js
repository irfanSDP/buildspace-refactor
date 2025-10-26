define(["../../../dojo/_base/declare",
    'dojo/_base/lang',
    "dojo/aspect",
    "dojo/when",
    "dojo/currency",
    'buildspace/apps/ProjectBuilder/Builder',
    'buildspace/apps/Tendering/Builder',
    './ScheduleOfRateGrid',
    './buildUpSummary',
    './ProjectGrid',
    "buildspace/widget/grid/cells/Formatter",
    'buildspace/widget/grid/Filter',
    'dojo/i18n!buildspace/nls/ProjectImportRates'], function(declare, lang, aspect, when, currency, ProjectBuilder, Tendering, ScheduleOfRateGrid, BuildUpSummary, ProjectGrid, GridFormatter, Filter, nls){

    var ScheduleOfRateGridContainer = declare('buildspace.apps.ProjectImportRates.ScheduleOfRateGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        gridOpts: {},
        pageId: 0,
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            lang.mixin(self.gridOpts, { type: self.type, project: self.project });

            var grid = this.grid = new ScheduleOfRateGrid(self.gridOpts);
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('projectBuilder-import_rates_'+self.project.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dijit.layout.BorderContainer( { gutters: false, title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId},node );
                self.region = 'center';
                var filterFields = [];
                if(self.pageId){
                    if(new RegExp('^import_rate\-page_sor_item\-').test(self.pageId)){
                        filterFields = [
                            {'description':nls.description},
                            {'uom_symbol':nls.unit},
                            {'rate-final_value':nls.rate}
                        ];
                    }
                    if(new RegExp('^import_rate\-page_sor_trade\-').test(self.pageId)){
                        filterFields = [
                            {'description':nls.description}
                        ];
                    }
                }

                child.addChild(new Filter({
                    region: 'top',
                    editableGrid: false,
                    grid: grid,
                    filterFields: filterFields
                }));
                child.addChild(self);
                lang.mixin(child, {grid: grid});
                container.addChild(child);

                container.selectChild(self.pageId);
            }
        }
    });

    var ScheduleOfRateContainer = declare('buildspace.apps.ProjectImportRates.ScheduleOfRateContainer', dijit.layout.BorderContainer, {
        project: null,
        region: 'center',
        style:"padding:0px;margin:0px;width:100%;height:50%;",
        gutters: false,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: "billManagerImportRate/getScheduleOfRates"
                }),
                content = ScheduleOfRateGridContainer({
                    stackContainerTitle: nls.scheduleOfRates,
                    pageId: 'import_rate-page_sor-'+self.project.id,
                    project: self.project,
                    gridOpts: {
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.title, field: 'name', width:'auto' }
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.name[0] !== null){
                                self.createTradeGrid(_item);
                            }
                        }
                    }
                });

            var gridContainer = this.makeGridContainer(content, nls.scheduleOfRates);
            this.addChild(gridContainer);
            gridContainer.startup();
        },
        createTradeGrid: function(scheduleOfRate){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose: true,
                url:"billManagerImportRate/getScheduleOfRateTrades/id/"+scheduleOfRate.id
            });

            var grid = ScheduleOfRateGridContainer({
                stackContainerTitle: scheduleOfRate.name,
                pageId: 'import_rate-page_sor_trade-'+self.project.id+'_'+scheduleOfRate.id,
                project: self.project,
                gridOpts: {
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto' }
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createItemGrid(_item);
                        }
                    }
                }
            });
        },
        createItemGrid: function(trade){
            var self = this, formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url:"billManagerImportRate/getScheduleOfRateItems/id/"+trade.id
                });

            var grid = new ScheduleOfRateGridContainer({
                stackContainerTitle: trade.description,
                pageId: 'import_rate-page_sor_item-'+self.project.id+'_'+trade.id,
                project: self.project,
                type: 'item',
                gridOpts: {
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                        {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                        {name: nls.unit, field: 'uom_id', width:'50px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                        {name: nls.rate, field: 'rate-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                        {name: nls.importRate, field: 'import_rate', width:'100px', styles:'text-align:center;', formatter: formatter.importRateButtonCellFormatter}
                    ],
                    onRowDblClick: function(e){
                        var colField = e.cell.field,
                            rowIndex = e.rowIndex,
                            _item = this.getItem(rowIndex);
                        if(colField != 'import_rate' && _item.id > 0 && _item.description[0] !== null && _item['rate-has_build_up'][0]){
                            self.createBuildUpGrid(_item);
                        }
                    }
                }
            });
        },
        createBuildUpGrid: function(item){
            var self = this,
                baseContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:100%;height:100%;border:0px;outline:none;",
                    gutters: false
                }),
                aContainer = new dijit.layout.AccordionContainer({
                    region: "center",
                    style:"padding:0px;width:100%;height:100%;border:0px;outline:none;"
                }),
                resourceQuery = dojo.xhrGet({
                    url: "scheduleOfRate/resourceList/item_id/"+item.id,
                    handleAs: "json"
                }),
                formatter = new GridFormatter(),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                when(resourceQuery, function(resources){
                    var buildUpSummaryWidget = BuildUpSummary({
                        itemId: item.id,
                        container: baseContainer
                    });
                    dojo.forEach(resources, function(resource){
                        var store = new dojo.data.ItemFileWriteStore({
                            clearOnClose: true,
                            url:"scheduleOfRate/getBuildUpRateItemList/sor_item_id/"+item.id+"/resource_id/"+resource.id
                        });
                        var grid = dojox.grid.EnhancedGrid({
                            store: store,
                            canSort: function(inSortInfo){
                                return false;
                            },
                            structure: [
                                {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                {name: nls.description, field: 'description', width:'auto', formatter: formatter.linkedCellFormatter },
                                {name: nls.number, field: 'number-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                {name: nls.constant, field: 'constant-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                {name: nls.qty, field: 'quantity-value', width:'70px', styles:'text-align:right;', formatter: formatter.formulaNumberCellFormatter},
                                {name: nls.unit, field: 'uom_id', width:'50px', styles:'text-align:center;', formatter: formatter.linkedUnitIdCellFormatter},
                                {name: nls.rate, field: 'rate-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                {name: nls.total, field: 'total', width:'100px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter},
                                {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                {name: nls.lineTotal, field: 'line_total', width:'90px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter}
                            ]
                        });
                        aContainer.addChild(new dijit.layout.ContentPane({
                            title: resource.name+'<span style="color:blue;float:right;">'+buildspace.currencyAbbreviation+' '+currency.format(resource.total_build_up)+'</span>',
                            style: "padding:0px;border:0px;",
                            doLayout: false,
                            id: 'accPane-'+resource.id+'-'+item.id,
                            content: grid
                        }));
                    });

                    baseContainer.addChild(aContainer);
                    baseContainer.addChild(buildUpSummaryWidget);
                    var container = dijit.byId('projectBuilder-import_rates_'+self.project.id+'-stackContainer');
                    if(container){
                        var node = document.createElement("div");
                        var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(item.description, 60)+' ('+nls.buildUpRate+')', id: 'sor_buildUpRatePage-'+item.id, executeScripts: true },node );
                        container.addChild(child);
                        child.set('content', baseContainer);
                        container.selectChild('sor_buildUpRatePage-'+item.id);
                    }
                    pb.hide();
                });
            });
        },
        makeGridContainer: function(content, title){
            var id = this.project.id;
            var stackContainer = dijit.byId('projectBuilder-import_rates_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('projectBuilder-import_rates_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'projectBuilder-import_rates_'+id+'-stackContainer'
            });

            var gridContainer = new dijit.layout.BorderContainer({
                gutters: false
            });

            gridContainer.addChild(
                new Filter({
                    grid: content.grid,
                    region: 'top',
                    editableGrid: false,
                    filterFields:[
                        {'name':nls.title}
                    ]
                })
            );
            content.region = 'center';
            gridContainer.addChild(content);

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: gridContainer,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'projectBuilder-import_rates_'+id+'-stackContainer'
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            }));

            dojo.subscribe('projectBuilder-import_rates_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('projectBuilder-import_rates_'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    if(children.length > index + 1){
                        page.grid.store.save();
                        page.grid.store.close();

                        var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                            handle.remove();
                            this.scrollToRow(this.selection.selectedIndex);
                        });

                        page.grid._refresh();
                    }

                    while(children.length > index+1 ){
                        index = index + 1;
                        widget.removeChild(children[ index ]);
                        children[ index ].destroyRecursive(true);
                    }
                }
            });

            return borderContainer;
        }
    });

    var ProjectGridContainer = declare('buildspace.apps.ProjectImportRates.ProjectGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { type: self.type, project: self.project, region:"center" });

            var grid = this.grid = new ProjectGrid(self.gridOpts);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('projectBuilder-import_rates_project_'+self.project.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dijit.layout.BorderContainer( {gutters: false, title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId},node );
                self.region = 'center';
                var filterFields = [];
                if(self.pageId){
                    if(new RegExp('^import_rate\-page_project_element-').test(self.pageId)){
                        filterFields =[
                            {'description':nls.description}
                        ];
                    }
                    if(new RegExp('^import_rate\-page_project_item\-').test(self.pageId)){
                        filterFields = [
                            {'description':nls.description},
                            {'uom_symbol':nls.unit},
                            {'rate-final_value':nls.rate}
                        ];
                    }
                }
                child.addChild(new Filter({
                    region: 'top',
                    editableGrid: false,
                    grid: grid,
                    filterFields: filterFields
                }));
                child.addChild(self);

                container.addChild(child);
                lang.mixin(child, {grid: grid});
                container.selectChild(self.pageId);
            }
        }
    });

    var ProjectContainer = declare('buildspace.apps.ProjectImportRates.ProjectContainer', dijit.layout.BorderContainer, {
        project: null,
        region: 'bottom',
        style:"padding:0px;margin:0px;width:100%;height:50%;",
        gutters: false,
        splitter: true,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var store = dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: "billManagerImportRate/getProjectBreakdown/id/"+this.project.id
                }),
                content = ProjectGridContainer({
                    stackContainerTitle: nls.scheduleOfRates,
                    pageId: 'import_rate-page_project-'+this.project.id,
                    project: this.project,
                    gridOpts: {
                        store: store,
                        structure: [
                            {name: 'No.', field: 'count', width:'40px', styles:'text-align:center;', formatter: CustomFormatter.rowCountCellFormatter },
                            {name: nls.description, field: 'title', width:'auto', formatter: CustomFormatter.treeCellFormatter},
                            {name: nls.billType, field: 'bill_type', width:'120px', styles:'text-align:center;', formatter: CustomFormatter.billTypeCellFormatter}
                        ],
                        onRowDblClick: function(e){
                            var _this = this, bill = _this.getItem(e.rowIndex);
                            if(bill.id > 0 && bill.title[0] !== null && bill.type[0] == buildspace.constants.TYPE_BILL){
                                self.createElementGrid(bill);
                            }
                        }
                    }
                });

            var gridContainer = this.makeGridContainer(content, buildspace.truncateString(this.project.title, 100));
            this.addChild(gridContainer);
            gridContainer.startup();
        },
        createElementGrid: function(bill){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose: true,
                url:"billManagerImportRate/getElementList/id/"+bill.id
            });

            ProjectGridContainer({
                stackContainerTitle: bill.title,
                pageId: 'import_rate-page_project_element-'+this.project.id+'_'+bill.id,
                project: this.project,
                gridOpts: {
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto' }
                    ],
                    onRowDblClick: function(e){
                        var _this = this, element = _this.getItem(e.rowIndex);
                        if(element.id > 0 && element.description[0] !== null){
                            self.createItemGrid(element);
                        }
                    }
                }
            });
        },
        createItemGrid: function(element){
            var formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url:"billManagerImportRate/getItemList/id/"+element.id
                });

            ProjectGridContainer({
                stackContainerTitle: element.description,
                pageId: 'import_rate-page_project_item-'+this.project.id+'_'+element.id,
                project: this.project,
                type: 'tree',
                gridOpts: {
                    store: store,
                    element: element,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                        {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                        {name: nls.unit, field: 'uom_id', width:'50px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                        {name: nls.rate, field: 'rate-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter}
                    ]
                }
            });
        },
        makeGridContainer: function(content, title){
            var id = this.project.id;
            var stackContainer = dijit.byId('projectBuilder-import_rates_project_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('projectBuilder-import_rates_project_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'projectBuilder-import_rates_project_'+id+'-stackContainer'
            });

            var gridContainer = new dijit.layout.BorderContainer({
                gutters: false
            });

            gridContainer.addChild(
                new Filter({
                    grid: content.grid,
                    region: 'top',
                    editableGrid: false,
                    filterFields:[
                        {'title':nls.description}
                    ]
                })
            );
            content.region = 'center';
            gridContainer.addChild(content);

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: gridContainer,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'projectBuilder-import_rates_project_'+id+'-stackContainer'
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            }));

            dojo.subscribe('projectBuilder-import_rates_project_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('projectBuilder-import_rates_project_'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    if(children.length > index + 1){
                        page.grid.store.save();
                        page.grid.store.close();

                        var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                            handle.remove();
                            this.scrollToRow(this.selection.selectedIndex);
                        });

                        page.grid._refresh();
                    }

                    while(children.length > index+1 ){
                        index = index + 1;
                        widget.removeChild(children[ index ]);
                        children[ index ].destroyRecursive(true);
                    }
                }
            });

            return borderContainer;
        }
    });

    var CustomFormatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return cellValue > 0 ? cellValue : '';
        },
        treeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            var level = item.level*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;
            if(item.type < buildspace.constants.TYPE_BILL){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        },
        billTypeCellFormatter: function(cellValue, rowIdx){
            return buildspace.getBillTypeText(cellValue);
        }
    };

    return declare('buildspace.apps.ProjectImportRates', buildspace.apps._App, {
        type: null,
        win: null,
        project: null,
        init: function(args){
            var project = this.project = args.project;
            var type = this.type = args.type;

            var moduleName = this.getModuleName();

            this.win = new buildspace.widget.Window({
                title: moduleName + ' > ' + nls.importRates+' - '+buildspace.truncateString(project.title, 100),
                onClose: dojo.hitch(this, "kill")
            });

            var container = new dijit.layout.BorderContainer({
                style:"padding:0;width:100%;height:100%;",
                gutters: false,
                liveSplitters: true
            });

            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border:0px;padding:2px;overflow:hidden;"});
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backTo + ' ' + moduleName,
                    iconClass: "icon-16-container icon-16-directional_left",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openBuilderWin', project)
                })
            );

            container.addChild(toolbar);
            container.addChild(ScheduleOfRateContainer({
                project: project
            }));
            container.addChild(ProjectContainer({
                project: project
            }));

            this.win.addChild(container);
            this.win.show();
            this.win.startup();
        },
        openBuilderWin: function(project){
            this.kill();
            this.project = project;

            var moduleName = this.getModuleName();

            this.win = new buildspace.widget.Window({
                title: moduleName + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            var builder = null;

            switch(this.type){
                case buildspace.constants.STATUS_PRETENDER:
                    builder = ProjectBuilder({
                        project: project
                    });
                    break;
                case buildspace.constants.STATUS_TENDERING:
                    builder = Tendering({
                        project: project
                    });
                    break;
                default:
                    builder = ProjectBuilder({
                        project: project
                    });
                    break;
            }

            this.win.addChild(builder);
            this.win.show();
            this.win.startup();
        },
        getModuleName: function() {
            var moduleName = null;

            switch(this.type){
                case buildspace.constants.STATUS_PRETENDER:
                    moduleName = nls.ProjectBuilder;
                    break;
                case buildspace.constants.STATUS_TENDERING:
                    moduleName = nls.tendering;
                    break;
                default:
                    moduleName = nls.ProjectBuilder;
                    break;
            }

            return moduleName;
        },
        kill: function(){
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});