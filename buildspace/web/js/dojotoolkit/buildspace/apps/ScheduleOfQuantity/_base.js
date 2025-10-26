define('buildspace/apps/ScheduleOfQuantity/_base', ["dojo/_base/declare",
    'dojo/_base/lang',
    "dojo/aspect",
    "dojo/when",
    'dojo/_base/event',
    'dojo/keys',
    'dojo/_base/html',
    'dijit/PopupMenuItem',
    'dojo/number',
    'dojo/request',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "./grid",
    "./BuildUpQuantityGrid",
    './ImportCubitDialog',
    'buildspace/widget/grid/Filter',
    'buildspace/widget/grid/cells/Formatter',
    'buildspace/apps/ProjectBuilder/Builder',
    'buildspace/apps/Tendering/Builder',
    'dojo/i18n!buildspace/nls/ScheduleOfQuantity'], function(declare, lang, aspect, when, evt, keys, html, PopupMenuItem, number, request,DropDownButton, DropDownMenu, MenuItem, ScheduleOfQuantityGrid, BuildUpQuantityGrid, ImportCubitDialog, FilterToolbar, GridFormatter, ProjectBuilder, Tendering, nls){

    var TabGrid = declare('buildspace.apps.ScheduleOfQuantity.TabGrid', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;border:0px;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: null,
        project: null,
        scheduleOfQuantity: null,
        scheduleOfQuantityTrade: null,
        gridOpts: {},
        type: null,
        postCreate: function(){
            var filterFields;
            this.inherited(arguments);

            lang.mixin(this.gridOpts, { project: this.project, scheduleOfQuantity: this.scheduleOfQuantity, type: this.type, borderContainerWidget: this });

            var grid = this.grid = new ScheduleOfQuantityGrid(this.gridOpts),
                buttonId = this.project.id+"_"+this.scheduleOfQuantity.id+"_"+this.type;

            switch(this.type){
                case "item_grid":
                    filterFields = [
                        {'description': nls.description},
                        {'uom_symbol': nls.unit},
                        {'quantity-final_value': nls.qty}
                    ];
                    break;
                case "trade_grid":
                    filterFields = [
                        {'description':nls.description}
                    ];
                    break;
                default:
                    filterFields = [
                        {'description':nls.description}
                    ];
            }

            if(this.gridOpts.editable){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        id: buttonId+'_AddSOQRow-button',
                        label: nls.addRow,
                        iconClass: "icon-16-container icon-16-add",
                        disabled: grid.selection.selectedIndex > -1 ? false : true,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                grid.addRow(grid.selection.selectedIndex);
                            }
                        }
                    })
                );

                if(this.type == 'item_grid'){
                    toolbar.addChild(new dijit.ToolbarSeparator());
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: buttonId+'_IndentSOQ-button',
                            label: nls.indent,
                            iconClass: "icon-16-container icon-16-indent",
                            disabled: grid.selection.selectedIndex > -1 ? false : true,
                            onClick: function(){
                                if(grid.selection.selectedIndex > -1){
                                    grid.indentOutdent(grid.selection.selectedIndex, 'indent');
                                }
                            }
                        })
                    );
                    toolbar.addChild(new dijit.ToolbarSeparator());
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: buttonId+'_OutdentSOQ-button',
                            label: nls.outdent,
                            iconClass: "icon-16-container icon-16-outdent",
                            disabled: grid.selection.selectedIndex > -1 ? false : true,
                            onClick: function(){
                                if(grid.selection.selectedIndex > -1){
                                    grid.indentOutdent(grid.selection.selectedIndex, 'outdent');
                                }
                            }
                        })
                    );
                }

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: buttonId+'_DeleteSOQRow-button',
                        label: nls.deleteRow,
                        iconClass: "icon-16-container icon-16-delete",
                        disabled: grid.selection.selectedIndex > -1 ? false : true,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                grid.deleteRow(grid.selection.selectedIndex);
                            }
                        }
                    })
                );

                this.addChild(toolbar);
            }

            this.addChild(
                new FilterToolbar({
                    grid: this.grid,
                    region: "top",
                    editableGrid: true,
                    filterFields: filterFields
                })
            );

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('scheduleOfQuantityGrid'+this.project.id+'_'+this.scheduleOfQuantity.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(this.stackContainerTitle, 50),
                    content: this,
                    id: this.pageId,
                    executeScripts: true
                },node );
                container.addChild(child);
                lang.mixin(child, {grid: grid});
                container.selectChild(this.pageId);
            }
        }
    });

    var LinkToCustomFormatter = {
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
        },
        linkQtyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.id > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID &&
                item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY){
                return '<a href="#" onclick="return false;">'+nls.link+'</a>';
            }else{
                cell.customClasses.push('disable-cell');
                return "&nbsp;";
            }
        }
    };

    return declare('buildspace.apps.ScheduleOfQuantity', buildspace.apps._App, {
        type: null,
        win: null,
        project: null,
        canEdit: true,
        init: function(args){
            var project = this.project = args.project;
            this.type = args.type, this.canEdit = args.canEdit;

            var moduleTitle = this.getModuleTitle();

            this.win = new buildspace.widget.Window({
                title: moduleTitle + ' > ' + nls.scheduleOfQuantities+' - '+buildspace.truncateString(project.title, 100),
                onClose: dojo.hitch(this, "kill")
            });

            var tabContainer = this.tabContainer = new dijit.layout.TabContainer({
                style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;"
            });

            tabContainer.addChild(this.createMainTab());

            tabContainer.startup();

            this.win.addChild(tabContainer);
            this.win.show();
            this.win.startup();
        },
        createMainTab: function(){
            var self = this, formatter = new GridFormatter(),
                container = new dijit.layout.BorderContainer({
                    title: nls.scheduleOfQuantities,
                    style:"padding:0px;width:100%;height:100%;",
                    gutters: false
                }),
                mainGrid = new ScheduleOfQuantityGrid({
                    type: 'main_grid',
                    project: this.project,
                    tabContainer: this.tabContainer,
                    editable: this.canEdit,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.title, field: 'title', width:'auto', editable: this.canEdit, cellType:'buildspace.widget.grid.cells.Textarea' },
                        {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
                    ],
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose:true,
                        url:"scheduleOfQuantity/getScheduleOfQuantityList/pid/"+this.project.id
                    }),
                    addUrl: 'scheduleOfQuantity/scheduleOfQuantityAdd',
                    updateUrl: 'scheduleOfQuantity/scheduleOfQuantityUpdate',
                    deleteUrl: 'scheduleOfQuantity/scheduleOfQuantityDelete',
                    pasteUrl: 'scheduleOfQuantity/scheduleOfQuantityPaste',
                    onRowDblClick: function(e){
                        var _item = this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.title[0] !== null){
                            self.createTradeGrid(_item);
                        }
                    }
                });

            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border:0px;padding:2px;overflow:hidden;"});

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backTo + ' ' + this.getModuleTitle(),
                    iconClass: "icon-16-container icon-16-directional_left",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openBuilderWin', this.project)
                })
            );

            if(this.canEdit){
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: this.project.id+'_main_grid_AddSOQRow-button',
                        label: nls.addRow,
                        disabled: true,
                        iconClass: "icon-16-container icon-16-add",
                        style:"outline:none!important;",
                        onClick: function(){
                            if(mainGrid.selection.selectedIndex > -1){
                                mainGrid.addRow(mainGrid.selection.selectedIndex);
                            }
                        }
                    })
                );
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: this.project.id+'_main_grid_DeleteSOQRow-button',
                        label: nls.deleteRow,
                        disabled: true,
                        iconClass: "icon-16-container icon-16-delete",
                        style:"outline:none!important;",
                        onClick: function(){
                            if(mainGrid.selection.selectedIndex > -1){
                                mainGrid.deleteRow(mainGrid.selection.selectedIndex);
                            }
                        }
                    })
                );

                var importDropDownMenu = new DropDownMenu({ style: "display: none;"});

                importDropDownMenu.addChild(new MenuItem({
                    label: nls.importFromCubit,
                    onClick: function(e){
                        if(mainGrid.selection.selectedIndex > -1){
                            var item = mainGrid.getItem(mainGrid.selection.selectedIndex);

                            self.createDialogForImportCubitFile(item, mainGrid);
                        }
                    }
                }));

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(new DropDownButton({
                    id: self.project.id+'_main_grid_ImportDropDownRow-button',
                    label: nls.importFromFiles,
                    iconClass: "icon-16-container icon-16-import",
                    dropDown: importDropDownMenu,
                    disabled: mainGrid.selection.selectedIndex > -1 ? false : true
                }));

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.linkToBillItems,
                        iconClass: "icon-16-container icon-16-link",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, "createLinkToBillItemTab")
                    })
                );
            }


            container.addChild(toolbar);
            container.addChild(mainGrid);

            return container;
        },
        openBuilderWin: function(project){
            this.kill();
            this.project = project;

            var moduleTitle = this.getModuleTitle();

            this.win = new buildspace.widget.Window({
                title: moduleTitle + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
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
        createLinkToBillItemTab: function(){
            var self = this,
                formatter = new GridFormatter(),
                topContainerId = 'SoqLinkToGridTop'+this.project.id,
                centerContainerId = 'SoqLinkToGridCenter'+this.project.id,
                tac = this.tabContainer.getChildren();

            for(var i in tac){
                if(typeof tac[i].lib_info != "object") continue;
                if(tac[i].lib_info.id == this.project.id+"_linkToBilItemsTab"){
                    return this.tabContainer.selectChild(tac[i]);
                }
            }

            var soqGrid = new ScheduleOfQuantityGrid({
                type: 'linkTo_Grid',
                project: this.project,
                editable: false,
                structure: [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.title, field: 'title', width:'auto'},
                    {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
                ],
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"scheduleOfQuantity/getScheduleOfQuantityList/pid/"+this.project.id
                }),
                onRowDblClick: function(e){
                    var _item = this.getItem(e.rowIndex);
                    if(_item.id > 0 && _item.title[0] !== null){
                        self.createLinkToTradeGrid(_item, topContainerId);
                    }
                }
            });

            var billGrid = new ScheduleOfQuantityGrid({
                type: 'linkTo_Grid',
                project: this.project,
                editable: false,
                structure: [
                    {name: 'No.', field: 'count', width:'40px', styles:'text-align:center;', formatter: LinkToCustomFormatter.rowCountCellFormatter },
                    {name: nls.title, field: 'title', width:'auto', formatter: LinkToCustomFormatter.treeCellFormatter},
                    {name: nls.billType, field: 'bill_type', width:'180px', styles:'text-align:center;', formatter: LinkToCustomFormatter.billTypeCellFormatter}
                ],
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"billManagerImportQty/getBillList/pid/"+this.project.id
                }),
                onRowDblClick: function(e){
                    var _item = this.getItem(e.rowIndex);
                    if(_item.id > 0 && _item.title[0] !== null){
                        self.createLinkToBillColumnSettingGrid(_item, centerContainerId);
                    }
                }
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;width:100%;height:100%;",
                gutters: false,
                liveSplitters: true
            });

            borderContainer.addChild(this.createLinkToSoqContainer(topContainerId, nls.scheduleOfQuantities, soqGrid, "top"));
            borderContainer.addChild(this.createLinkToSoqContainer(centerContainerId, nls.bills, billGrid, "center"));

            var pane = new dijit.layout.ContentPane({
                closable: true,
                style: "padding: 0px; overflow: hidden;",
                title: nls.linkToBillItems,
                content: borderContainer
            });

            this.tabContainer.addChild(pane);
            this.tabContainer.selectChild(pane);

            pane.lib_info = {
                name: nls.linkToBillItems,
                id: this.project.id+"_linkToBilItemsTab"
            }
        },
        createLinkToSoqContainer: function(id, title, content, region){
            var stackContainer = dijit.byId(id+'-stackContainer');
            if(stackContainer){
                dijit.byId(id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;',
                region: "center",
                id: id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: content
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: id+'-stackContainer'
            });

            var borderContainer = new dijit.layout.BorderContainer({
                region: region,
                style:"padding:0px;margin:0px;width:100%;height:50%;",
                gutters: false,
                splitter: true
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: "top",
                content: controller
            }));

            dojo.subscribe(id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId(id+'-stackContainer');

                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){

                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyRecursive(true);
                            index = index + 1;
                        }

                        var selectedIndex = page.content.selection.selectedIndex;

                        page.content.store.save();
                        page.content.store.close();

                        var handle = aspect.after(page.content, "_onFetchComplete", function() {
                            handle.remove();
                            if(selectedIndex > -1){
                                this.scrollToRow(selectedIndex);
                                this.selection.setSelected(selectedIndex, true);
                            }
                        });

                        page.content.sort();
                    }
                }
            });

            return borderContainer;
        },
        createLinkToTradeGrid: function(scheduleOfQuantity, stackContainerId){
            var self = this,
                formatter = new GridFormatter(),
                grid = new ScheduleOfQuantityGrid({
                    type: 'linkTo_Grid',
                    project: this.project,
                    editable: false,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto' },
                        {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
                    ],
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose:true,
                        url:"scheduleOfQuantity/getTradeList/sid/"+scheduleOfQuantity.id
                    }),
                    onRowDblClick: function(e){
                        var _item = this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createLinkToItemGrid(_item, stackContainerId);
                        }
                    }
                });

            var container = dijit.byId(stackContainerId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(scheduleOfQuantity.title, 50),
                    content: grid,
                    executeScripts: true
                },node );
                container.addChild(child);
                container.selectChild(child);
            }
        },
        createLinkToItemGrid: function(trade, stackContainerId){
            var formatter = GridFormatter(),
                grid = new ScheduleOfQuantityGrid({
                    id: 'linkToItemGrid_'+this.project.id,
                    type: 'linkTo_ItemGrid',
                    project: this.project,
                    editable: false,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                        {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                        {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                        {name: nls.qty, field: 'quantity-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaNumberCellFormatter}
                    ],
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose:true,
                        url: "scheduleOfQuantity/getItemList/tid/"+trade.id
                    }),
                    onRowDblClick: function(e){
                        var _item = this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            //self.createTradeGrid(_item);
                        }
                    }
                });

            var container = dijit.byId(stackContainerId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(trade.description, 50),
                    content: grid,
                    executeScripts: true
                },node );
                container.addChild(child);
                container.selectChild(child);
            }
        },
        createLinkToBillColumnSettingGrid: function(bill, stackContainerId){
            var self = this,
                formatter = GridFormatter(),
                grid = new ScheduleOfQuantityGrid({
                    type: 'linkTo_Grid',
                    project: this.project,
                    edtable: false,
                    structure: [
                        {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.name, field: 'name', width:'auto'}
                    ],
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose:true,
                        url:"billManagerImportQty/getBillColumnSettingList/bid/"+bill.id
                    }),
                    onRowDblClick: function(e){
                        var _item = this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.name[0] !== null){
                            self.createLinkToBillElementGrid(_item, stackContainerId);
                        }
                    }
                });

            var container = dijit.byId(stackContainerId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(bill.title, 50),
                    content: grid,
                    executeScripts: true
                },node );
                container.addChild(child);
                container.selectChild(child);
            }
        },
        createLinkToBillElementGrid: function(billColumnSetting, stackContainerId){
            var self = this,
                formatter = GridFormatter(),
                grid = new ScheduleOfQuantityGrid({
                    type: 'linkTo_Grid',
                    project: this.project,
                    editable: false,
                    structure: [
                        {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto' }
                    ],
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose:true,
                        url:"billManagerImportQty/getElementList/cid/"+billColumnSetting.id
                    }),
                    onRowDblClick: function(e){
                        var _item = this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createLinkToBillItemGrid(_item, billColumnSetting, stackContainerId);
                        }
                    }
                });

            var container = dijit.byId(stackContainerId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(billColumnSetting.name, 50),
                    content: grid,
                    executeScripts: true
                },node );
                container.addChild(child);
                container.selectChild(child);
            }
        },
        createLinkToBillItemGrid: function(element, billColumnSetting, stackContainerId){
            var structure,
                formatter = GridFormatter();

            if(billColumnSetting.remeasurement_quantity_enabled[0]){
                structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                    {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                    {name: nls.qty+'/'+nls.unit, field: 'quantity_per_unit-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.link+" "+nls.qty, field: 'quantity_import', width:'80px', styles:'text-align:center;', formatter: LinkToCustomFormatter.linkQtyCellFormatter },
                    {name: nls.qty+'/'+nls.unit+" (2)", field: 'quantity_per_unit_remeasurement-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.link+" "+nls.qty+" 2", field: 'quantity_remeasurement_import', width:'80px', styles:'text-align:center;', formatter: LinkToCustomFormatter.linkQtyCellFormatter }
                ];
            }else{
                structure = [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                    {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                    {name: nls.qty+'/'+nls.unit, field: 'quantity_per_unit-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.link+" "+nls.qty, field: 'quantity_import', width:'80px', styles:'text-align:center;', formatter: LinkToCustomFormatter.linkQtyCellFormatter}
                ];
            }

            var grid = new ScheduleOfQuantityGrid({
                type: 'linkTo_BillItemGrid',
                billColumnSetting: billColumnSetting,
                project: this.project,
                editable: false,
                structure: structure,
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"billManagerImportQty/getItemList/eid/"+element.id+"/cid/"+billColumnSetting.id
                })
            });

            var container = dijit.byId(stackContainerId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(element.description, 50),
                    content: grid,
                    executeScripts: true
                },node );
                container.addChild(child);
                container.selectChild(child);
            }
        },
        createTradeGrid: function(scheduleOfQuantity){
            var self = this,
                tac = this.tabContainer.getChildren();

            for(var i in tac){
                if(typeof tac[i].lib_info != "object") continue;
                if(tac[i].lib_info.id == this.project.id+"_"+scheduleOfQuantity.id){
                    return this.tabContainer.selectChild(tac[i]);
                }
            }

            var store = dojo.data.ItemFileWriteStore({
                    url: "scheduleOfQuantity/getTradeList/sid/"+scheduleOfQuantity.id,
                    clearOnClose: true,
                    urlPreventCache: true
                }),
                formatter = new GridFormatter();

            var grid = new TabGrid({
                id: 'soq_trade-page-container-'+scheduleOfQuantity.id,
                type: 'trade_grid',
                stackContainerTitle: scheduleOfQuantity.title,
                pageId: 'page-trade_'+scheduleOfQuantity.id,
                project: this.project,
                scheduleOfQuantity: scheduleOfQuantity,
                gridOpts: {
                    store: store,
                    editable: this.canEdit,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', editable:this.canEdit, cellType:'buildspace.widget.grid.cells.Textarea' },
                        {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
                    ],
                    addUrl:'scheduleOfQuantity/tradeAdd',
                    updateUrl:'scheduleOfQuantity/tradeUpdate',
                    deleteUrl:'scheduleOfQuantity/tradeDelete',
                    pasteUrl:'scheduleOfQuantity/tradePaste',
                    onRowDblClick: function(e){
                        var _item = this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createItemGrid(scheduleOfQuantity, _item);
                        }
                    }
                }
            });

            this.makeTab(this.project.id+"_"+scheduleOfQuantity.id, scheduleOfQuantity.title, grid);
        },
        createItemGrid: function(scheduleOfQuantity, scheduleOfQuantityTrade){
            var self = this,
                hierarchyTypes = {
                    options: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT
                    ],
                    values: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM
                    ]
                },
                store = dojo.data.ItemFileWriteStore({
                    url: "scheduleOfQuantity/getItemList/tid/"+scheduleOfQuantityTrade.id,
                    clearOnClose: true,
                    urlPreventCache: true
                }),
                formatter = new GridFormatter();

            var unitQuery = dojo.xhrGet({
                url: "bqLibrary/getUnits",
                handleAs: "json"
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            pb.show();

            return when(unitQuery, function(uom){
                pb.hide();

                new TabGrid({
                    id: 'soq_item-page-container-'+scheduleOfQuantity.id,
                    type: 'item_grid',
                    stackContainerTitle: scheduleOfQuantityTrade.description,
                    pageId: 'page-item_'+scheduleOfQuantity.id,
                    project: self.project,
                    scheduleOfQuantity: scheduleOfQuantity,
                    gridOpts: {
                        store: store,
                        editable: self.canEdit,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.description, field: 'description', width:'auto', editable:self.canEdit, formatter: formatter.treeCellFormatter, cellType:'buildspace.widget.grid.cells.Textarea' },
                            {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', editable:self.canEdit, cellType:'dojox.grid.cells.Select', options: hierarchyTypes.options, values:hierarchyTypes.values, formatter: formatter.typeCellFormatter },
                            {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', editable:self.canEdit, cellType:'dojox.grid.cells.Select', options: uom.options, values:uom.values, formatter: formatter.unitIdCellFormatter},
                            {name: nls.qty, field: 'quantity-value', width:'100px', styles:'text-align:right;', editable:self.canEdit, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaNumberCellFormatter},
                            {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
                        ],
                        addUrl:'scheduleOfQuantity/itemAdd',
                        updateUrl:'scheduleOfQuantity/itemUpdate',
                        deleteUrl:'scheduleOfQuantity/itemDelete',
                        pasteUrl:'scheduleOfQuantity/itemPaste',
                        indentUrl:'scheduleOfQuantity/itemIndent',
                        outdentUrl:'scheduleOfQuantity/itemOutdent',
                        editableCellDblClick: function(e) {
                            var colField = e.cell.field,
                                rowIndex = e.rowIndex,
                                item = this.getItem(rowIndex);
                            if(colField == "quantity-value" && item.id > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER){
                                if(item.uom_id[0] > 0){
                                    var dimensionColumnQuery = dojo.xhrGet({
                                        url: "billBuildUpQuantity/getDimensionColumnStructure",
                                        content:{uom_id: item.uom_id[0]},
                                        handleAs: "json"
                                    });
                                    var pb = buildspace.dialog.indeterminateProgressBar({
                                        title:nls.pleaseWait+'...'
                                    });
                                    pb.show();
                                    var stackContainerId = this.project.id+'_'+this.scheduleOfQuantity.id;
                                    dimensionColumnQuery.then(function(dimensionColumns){
                                        self.createBuildUpQuantityContainer(item, dimensionColumns, stackContainerId);
                                        pb.hide();
                                    });
                                }else{
                                    buildspace.dialog.alert(nls.buildUpQtyAlert, nls.pleaseSetUOM, 60, 300);
                                }
                            }
                        },
                        customCellDblClick: function(e){
                            if(!self.canEdit){
                                var colField = e.cell.field,
                                    rowIndex = e.rowIndex,
                                    item = this.getItem(rowIndex);
                                if(colField == "quantity-value" && item.id > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER){
                                    if(item.uom_id[0] > 0){
                                        var dimensionColumnQuery = dojo.xhrGet({
                                            url: "billBuildUpQuantity/getDimensionColumnStructure",
                                            content:{uom_id: item.uom_id[0]},
                                            handleAs: "json"
                                        });
                                        var pb = buildspace.dialog.indeterminateProgressBar({
                                            title:nls.pleaseWait+'...'
                                        });
                                        pb.show();
                                        var stackContainerId = this.project.id+'_'+this.scheduleOfQuantity.id;
                                        dimensionColumnQuery.then(function(dimensionColumns){
                                            self.createBuildUpQuantityContainer(item, dimensionColumns, stackContainerId);
                                            pb.hide();
                                        });
                                    }else{
                                        buildspace.dialog.alert(nls.buildUpQtyAlert, nls.pleaseSetUOM, 60, 300);
                                    }
                                }
                            }
                        },
                        canEdit: function(inCell, inRowIndex) {
                            var self = this;
                            var item = this.getItem(inRowIndex);

                            if(!this.editable){
                                return false;
                            }

                            // not allow user to edit imported item from other sources
                            if ( item && item.identifier_type[0] != 1 ) {
                                return false;
                            }

                            if(inCell != undefined) {
                                var field = inCell.field;

                                if ( field === 'type' ){
                                    var nextItem = this.getItem(inRowIndex+1);

                                    if(item.id[0] > 0 && item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && nextItem !== undefined && item.level[0] < nextItem.level[0]) {
                                        window.setTimeout(function() {
                                            self.edit.cancel();
                                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                                        }, 10);
                                        return false;
                                    }
                                }

                                if(item.id[0] > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM && (
                                        field == 'quantity-value' || field == 'uom_id'
                                    )){
                                    window.setTimeout(function() {
                                        self.edit.cancel();
                                        self.focus.setFocusIndex(inRowIndex, inCell.index);
                                    }, 10);
                                    return false;
                                }
                            }

                            return this._canEdit;
                        }
                    }
                });

            },function(error){
                /* got fucked */
            });
        },
        createBuildUpQuantityContainer: function(item, dimensionColumns, stackContainerId){
            var self = this, baseContainer = new dijit.layout.BorderContainer({
                    style:"padding:0;margin:0;width:100%;height:100%;border:none!important;outline:none;",
                    gutters: false
                }),
                aContainer = new dijit.layout.AccordionContainer({
                    id: "acc_"+stackContainerId+"-container",
                    region: "center",
                    style:"padding:0;margin:0;width:100%;height:100%;border:none!important;outline:none;"
                }),
                hasImportedItemsXhr = dojo.xhrGet({
                    url: "scheduleOfQuantity/hasImportedItems/id/"+item.id,
                    handleAs: "json"
                }),
                formatter = new GridFormatter(),
                sign = {options: [
                    buildspace.constants.SIGN_POSITIVE_TEXT,
                    buildspace.constants.SIGN_NEGATIVE_TEXT
                ],values: [
                    buildspace.constants.SIGN_POSITIVE,
                    buildspace.constants.SIGN_NEGATIVE
                ]};

            try{
                var structure = [{
                    name: 'No',
                    field: 'id',
                    styles: "text-align:center;",
                    width: '30px',
                    formatter: formatter.rowCountCellFormatter
                }, {
                    name: nls.description,
                    field: 'description',
                    width: 'auto',
                    editable: this.canEdit,
                    cellType: 'buildspace.widget.grid.cells.Textarea'
                },{
                    name: nls.factor,
                    field: 'factor-value',
                    width:'100px',
                    styles:'text-align:right;',
                    editable: this.canEdit,
                    cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                    formatter: formatter.formulaNumberCellFormatter
                }];

                dojo.forEach(dimensionColumns, function(dimensionColumn){
                    structure.push({
                        name: dimensionColumn.title,
                        field: dimensionColumn.field_name,
                        width:'100px',
                        styles:'text-align:right;',
                        editable: self.canEdit,
                        cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: formatter.formulaNumberCellFormatter
                    });
                });

                structure.push({
                    name: nls.total,
                    field: 'total',
                    width:'100px',
                    styles:'text-align:right;',
                    formatter: formatter.numberCellFormatter
                });

                structure.push({
                    name: nls.sign,
                    field: 'sign',
                    width: '70px',
                    styles: 'text-align:center;',
                    editable: this.canEdit,
                    cellType: 'dojox.grid.cells.Select',
                    options: sign.options,
                    values: sign.values,
                    formatter: formatter.signCellFormatter
                });

                when(hasImportedItemsXhr, function(hasImportedItems){
                    aContainer.addChild(new dijit.layout.ContentPane({
                        title: nls.manualItems+'<span style="color:blue;float:right;">'+number.format(item.editable_total, {places:2})+'&nbsp;'+item.uom_symbol+'</span>',
                        style: "padding:0;border:none!important;",
                        doLayout: false,
                        id: 'accPane-manual_'+item.id,
                        content: new BuildUpQuantityGrid({
                            type: "manual",
                            scheduleOfQuantityItem: item,
                            gridOpts: {
                                editable: self.canEdit,
                                structure: structure,
                                store: new dojo.data.ItemFileWriteStore({
                                    url:"scheduleOfQuantity/getBuildUpItemList/id/"+item.id+"/t/m",
                                    clearOnClose: true
                                }),
                                addUrl: 'scheduleOfQuantity/buildUpItemAdd',
                                updateUrl: 'scheduleOfQuantity/buildUpItemUpdate',
                                deleteUrl: 'scheduleOfQuantity/buildUpItemDelete',
                                pasteUrl: 'scheduleOfQuantity/buildUpItemPaste'
                            }
                        })
                    }));

                    if(hasImportedItems){
                        var childPane = new dijit.layout.ContentPane({
                            title: nls.importedItems+'<span style="color:blue;float:right;">'+number.format(item.non_editable_total, {places:2})+'&nbsp;'+item.uom_symbol+'</span>',
                            style: "padding:0;border:none!important;",
                            doLayout: false,
                            id: 'accPane-imported_'+item.id,
                            content: new BuildUpQuantityGrid({
                                type: "imported",
                                scheduleOfQuantityItem: item,
                                gridOpts: {
                                    editable: self.canEdit,
                                    structure: structure,
                                    store: new dojo.data.ItemFileWriteStore({
                                        url:"scheduleOfQuantity/getBuildUpItemList/id/"+item.id+"/t/i",
                                        clearOnClose: true
                                    })
                                }
                            })
                        });

                        aContainer.addChild(childPane);

                        aContainer.startup();

                        aContainer.selectChild(childPane);
                    }

                    baseContainer.addChild(aContainer);
                    var container = dijit.byId('scheduleOfQuantityGrid' + stackContainerId + '-stackContainer');
                    if(container){
                        var node = document.createElement("div");
                        var child = new dojox.layout.ContentPane( {
                            title: buildspace.truncateString(item.description, 50)+' ('+nls.buildUpQuantity+' - '+item.uom_symbol+')',
                            id: 'buildUpQuantityPage-'+item.id,
                            style: "padding:0;border:none!important;",
                            content: baseContainer,
                            executeScripts: true
                        },node );
                        container.addChild(child);
                        container.selectChild('buildUpQuantityPage-'+item.id);
                    }
                });
            }catch(e){console.log(e);}
        },
        makeTab: function(id, title, content){
            var stackContainer = dijit.byId('scheduleOfQuantityGrid'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('scheduleOfQuantityGrid'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;',
                region: "center",
                id: 'scheduleOfQuantityGrid'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'scheduleOfQuantityGrid'+id+'-stackContainer'
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                gutters: false
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(new dijit.layout.ContentPane({
                style: "padding: 0px; overflow: hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            }));

            var pane = new dijit.layout.ContentPane({
                closable: true,
                style: "padding: 0px; overflow: hidden;",
                title: buildspace.truncateString(title, 30),
                content: borderContainer
            });

            this.tabContainer.addChild(pane);
            this.tabContainer.selectChild(pane);

            dojo.subscribe('scheduleOfQuantityGrid'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('scheduleOfQuantityGrid'+id+'-stackContainer');

                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){

                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyRecursive(true);
                            index = index + 1;
                        }

                        var selectedIndex = page.grid.selection.selectedIndex;

                        page.grid.store.save();
                        page.grid.store.close();

                        var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                            handle.remove();
                            if(selectedIndex > -1){
                                this.scrollToRow(selectedIndex);
                                this.selection.setSelected(selectedIndex, true);
                            }
                        });

                        page.grid.sort();
                    }
                }
            });

            pane.lib_info = {
                name: title,
                id: id
            }
        },
        getModuleTitle: function() {
            var moduleTitle = null;

            switch(this.type){
                case buildspace.constants.STATUS_PRETENDER:
                    moduleTitle = nls.ProjectBuilder;
                    break;
                case buildspace.constants.STATUS_TENDERING:
                    moduleTitle = nls.tendering;
                    break;
                default:
                    moduleTitle = nls.ProjectBuilder;
                    break;
            }

            return moduleTitle;
        },
        createDialogForImportCubitFile: function(item, mainGrid) {
            var pb;

            pb = new buildspace.dialog.indeterminateProgressBar({
                title: "" + nls.pleaseWait + "..."
            });

            pb.show();

            return request('scheduleOfQuantityImport/getImportFilePermission', {
                query: {
                    scheduleOfQuantityId: item.id[0]
                },
                handleAs: 'json'
            }).then(function(response) {
                var dialog;

                pb.hide();

                dialog = new ImportCubitDialog({
                    title: nls.importFromCubit,
                    scheduleOfQuantityId: item.id[0],
                    scheduleOfQuantityGrid: mainGrid,
                    formInfo: response
                });

                return dialog.show();
            }, function(error) {
                return pb.hide();
            });
        },
        kill: function(){
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});