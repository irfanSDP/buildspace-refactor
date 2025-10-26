define('buildspace/apps/Tendering/LogDialog',[
    'dojo/_base/declare',
    'dojo/aspect',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/dom",
    'dojo/keys',
    "dojo/_base/array",
    "dojo/dom-style",
    'dojo/currency',
    'dojo/number',
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/Tendering'
], function(declare, aspect, lang, connect, dom, keys, array, domStyle, currency, number, EnhancedGrid, GridFormatter, nls){

    var CustomFormatter = {
        treeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            var level = item.level*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;
            if(item.type < buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        },
        revisionCurrencyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);
            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var currValueField = cell.field.replace('rev_','');
                var currValue = number.parse(item[currValueField]);

                var formattedValue = currency.format(value);
                cellValue = currValue == value ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        }
    };

    var Grid = declare('buildspace.apps.Tendering.LogGrid', EnhancedGrid, {
        project: null,
        company: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        currentRev: null,
        escapeHTMLInData: false,
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var LogGridContainer = declare('buildspace.apps.Tendering.LogGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        gridOpts: {},
        pageId: 0,
        currentRev: null,
        postCreate: function(){
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {project: this.project, currentRev: this.currentRev });

            var grid = this.grid = new Grid(this.gridOpts);
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('BillItemRateLog-'+this.project.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    id: this.pageId,
                    executeScripts: true,
                    content: this
                },node );
                container.addChild(child);
                lang.mixin(child, {grid: grid});
                container.selectChild(this.pageId);
            }
        }
    });

    return declare('buildspace.apps.Tendering.LogDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.projectLog,
        project: null,
        logData: null,
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;
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
            var borderContainer = this.borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;margin:0;width:986px;height:450px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border-right:0px;border-left:0px;border-top:0px;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            array.forEach(this.logData, function(data, i){
                if(data.id > 0){
                    toolbar.addChild(
                        new dijit.form.Button({
                            label: data.revision,
                            style:"outline:none!important;",
                            onClick: dojo.hitch(self, 'selectRevision', data)
                        })
                    );
                    toolbar.addChild(new dijit.ToolbarSeparator());
                }
            });

            borderContainer.addChild(toolbar);

            this.createProjectBreakdownGrid(this.logData[0]);

            return borderContainer;
        },
        selectRevision: function(revision){
            var id = this.project.id;
            var controllerPane = dijit.byId('BillItemRateLog-'+id+'-controllerPane'),
                stackContainer = dijit.byId('BillItemRateLog-'+id+'-stackContainer');

            controllerPane.destroyRecursive();
            stackContainer.destroyRecursive();

            this.createProjectBreakdownGrid(revision);
        },
        createProjectBreakdownGrid: function(revision){
            var self = this;
            var formatter = new GridFormatter();
            var grid = new LogGridContainer({
                id: 'bill_item_rate_log_bill-'+this.project.id,
                stackContainerTitle: nls.resources,
                pageId: 'bill_item_rate_log_bill-'+this.project.id+'-page',
                project: this.project,
                company: this.company,
                currentRev: revision,
                gridOpts: {
                    store: dojo.data.ItemFileWriteStore({
                        url: "tendering/getLogProjectBreakdown/pid/"+this.project.id+"/prid/"+revision.id,
                        clearOnClose: true
                    }),
                    structure: [
                        {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'title', width:'auto', formatter: CustomFormatter.treeCellFormatter },
                        {name: nls.currentAmount, field: 'overall_total_after_markup', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                        {name: revision.revision, field: 'rev_overall_total_after_markup', width:'150px', styles:'text-align:right;', formatter: CustomFormatter.revisionCurrencyCellFormatter}
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.type[0] == buildspace.constants.TYPE_BILL){
                            self.createElementGrid(_item, revision);
                        }
                    }
                }
            });

            var gridContainer = this.makeGridContainer(grid, nls.bills);
            this.borderContainer.addChild(gridContainer);
        },
        createElementGrid: function(bill, revision){
            var self = this,
                formatter = new GridFormatter();

            new LogGridContainer({
                id: 'bill_item_rate_log_element-'+this.project.id,
                stackContainerTitle: bill.title,
                pageId: 'bill_item_rate_log_element-'+this.project.id+'-page',
                project: this.project,
                currentRev: revision,
                gridOpts: {
                    store: dojo.data.ItemFileWriteStore({
                        url: "tendering/getLogElementList/bid/"+bill.id+"/prid/"+revision.id,
                        clearOnClose: true
                    }),
                    structure: [
                        {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto' },
                        {name: nls.grandTotal, field: 'overall_total_after_markup', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                        {name: nls.grandTotal +" "+revision.revision, field: 'rev_overall_total_after_markup', width:'150px', styles:'text-align:right;', formatter: CustomFormatter.revisionCurrencyCellFormatter}
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0 && _item.description[0] !== null){
                            self.createItemGrid(_item, revision);
                        }
                    }
                }
            });
        },
        createItemGrid: function(element, revision){
            var formatter = new GridFormatter();

            new LogGridContainer({
                id: 'bill_item_rate_log_item-'+this.project.id,
                stackContainerTitle: element.description,
                pageId: 'bill_item_rate_log_item-'+this.project.id+'-page',
                project: this.project,
                currentRev: revision,
                gridOpts: {
                    store: dojo.data.ItemFileWriteStore({
                        url: "tendering/getLogItemList/eid/"+element.id+"/prid/"+revision.id,
                        clearOnClose: true
                    }),
                    structure: [
                        {name: nls.billReference,field: 'bill_ref',styles: "text-align:center; color: red;", width: '80px', noresize: true, formatter: formatter.billRefCellFormatter},
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter},
                        {name: nls.unit, field: 'uom_id', width: '50px', styles: 'text-align:center;', formatter: formatter.unitIdCellFormatter, noresize: true},
                        {name: nls.rate, field: 'rate_after_markup', width:'85px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                        {name: nls.grandTotal, field: 'grand_total_after_markup', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                        {name: nls.rate+" "+revision.revision, field: 'rev_rate_after_markup', width:'85px', styles:'text-align:right;', formatter: CustomFormatter.revisionCurrencyCellFormatter},
                        {name: nls.grandTotal+" "+revision.revision, field: 'rev_grand_total_after_markup', width:'120px', styles:'text-align:right;', formatter: CustomFormatter.revisionCurrencyCellFormatter}
                    ]
                }
            });
        },
        makeGridContainer: function(content, title){
            var id = this.project.id;
            var stackContainer = dijit.byId('BillItemRateLog-'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('BillItemRateLog-'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'BillItemRateLog-'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'BillItemRateLog-'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                id: 'BillItemRateLog-'+id+'-controllerPane',
                content: controller
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('BillItemRateLog-'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('BillItemRateLog-'+id+'-stackContainer');
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
});