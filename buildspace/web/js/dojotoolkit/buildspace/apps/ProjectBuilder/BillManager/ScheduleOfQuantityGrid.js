define('buildspace/apps/ProjectBuilder/BillManager/ScheduleOfQuantityGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/when",
    "dojo/_base/connect",
    'dojo/_base/event',
    'dojo/number',
    'dojo/keys',
    'dojo/aspect',
    'dojo/_base/html',
    "dijit/focus",
    "dojo/dom-style",
    "dijit/TooltipDialog",
    "dijit/popup",
    'dojox/grid/EnhancedGrid',
    "buildspace/apps/ScheduleOfQuantity/BuildUpQuantityGrid",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function(declare, lang, when, connect, evt, number, keys, aspect, html, focusUtil, domStyle, TooltipDialog, popup, EnhancedGrid, SOQBuildUpQuantityGrid, GridFormatter, nls){

    var CustomTooltipDialog = declare('buildspace.apps.ProjectBuilder.BillManager.ScheduleOfQuantityTooltipDialog', TooltipDialog, {
        itemId: -1,
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
            this.inherited(arguments);
        },
        createContent: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;width:580px;height:120px;",
                gutters: false
            }),
                formatter = GridFormatter();

            borderContainer.addChild(new dijit.layout.ContentPane({
                content: dojox.grid.DataGrid({
                    escapeHTMLInData: false,
                    store: new dojo.data.ItemFileReadStore({
                        url: "billBuildUpQuantity/getScheduleOfQuantityInfo/id/"+this.itemId
                    }),
                    structure: [
                        {name: nls.title, field: 'title', width:'auto', formatter: formatter.treeCellFormatter}
                    ],
                    rowSelector: '0px',
                    style: "border:none!important;"
                }),
                style:'padding:0px;margin:0px;width:100%;height:100%;border:0px;',
                region: 'center'
            }));

            return borderContainer;
        }
    });

    var ScheduleOfQuantityGrid = declare('buildspace.apps.ProjectBuilder.BillManager.ScheduleOfQuantityGrid', EnhancedGrid, {
        type: null,
        style: "border:none!important;",
        rowSelector: '0px',
        region: 'center',
        billColumnSettingId: null,
        BillItem: null,
        keepSelection: true,
        qtyType: null,
        stackContainerId: null,
        buildUpSummaryWidget: null,
        disableEditingMode: false,
        constructor:function(args){
            this.connects = [];
        },
        canSort: function(inSortInfo){
            return false;
        },
        canEdit: function(inCell, inRowIndex) {
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            var myTooltipDialog;

            if(!this.disableEditingMode){
                this.on("RowContextMenu", function(e){
                    self.selection.clear();
                    var item = self.getItem(e.rowIndex);
                    self.selection.setSelected(e.rowIndex, true);
                    self.contextMenu(e);
                    if(item && item.id > 0){
                        self.disableToolbarButtons(false);
                    } else {
                        self.disableToolbarButtons(true);
                    }
                }, true);

                this.on('RowClick', function(e){
                    var item = self.getItem(e.rowIndex);
                    if(item && item.id > 0){
                        self.disableToolbarButtons(false);
                    } else {
                        self.disableToolbarButtons(true);
                    }
                });
            }

            this._connects.push(connect.connect(this, 'onCellMouseOver', function(e){
                var item = self.getItem(e.rowIndex);

                if(e.cell.field == "description" && item.id > 0 && item.level[0] == 0){
                    myTooltipDialog = new CustomTooltipDialog({
                        itemId: item.id[0],
                        onMouseLeave: function(){
                            popup.close(myTooltipDialog);
                        }
                    });

                    popup.open({
                        popup: myTooltipDialog,
                        around: e.cellNode
                    });
                } else {
                    var colField = e.cell.field,
                        rowIndex = e.rowIndex;

                    var fieldConstantName = colField.replace("-value", "");

                    // will show tooltip for formula, if available
                    if (typeof item[fieldConstantName + '-has_formula'] === 'undefined' || !item[fieldConstantName + '-has_formula'][0]) {
                        return;
                    }

                    var formulaValue = item[fieldConstantName + '-value'][0];

                    // convert ITEM ID into ROW ID (if available)
                    formulaValue = this.formulatedColumn.convertItemIdToRowIndex(formulaValue, rowIndex);

                    if (myTooltipDialog === null) {
                        myTooltipDialog = new TooltipDialog({
                            content: formulaValue,
                            onMouseLeave: function () {
                                popup.close(myTooltipDialog);
                            }
                        });

                        popup.open({
                            popup: myTooltipDialog,
                            around: e.cellNode
                        });
                    }
                }
            }));

            this._connects.push(connect.connect(this, 'onCellMouseOut', function(e){
                if(myTooltipDialog !== undefined){
                    popup.close(myTooltipDialog);
                }
            }));
        },
        deleteRow: function(rowIndex){
            var self = this, item = self.getItem(rowIndex),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            pb.show();

            var xhrArgs = {
                url: 'billBuildUpQuantity/scheduleOfQuantityDelete',
                content: { id: item.id, bid: this.BillItem.id, bcid: this.billColumnSettingId, type: this.qtyType,  _csrf_token: item._csrf_token },
                handleAs: 'json',
                load: function(data) {
                    if(data.success){

                        self.store.save();
                        self.store.close();
                        self.sort();

                        var handle = aspect.after(self, "_onFetchComplete", function() {
                            handle.remove();
                            rowIndex = rowIndex > self.store._arrayOfAllItems.length ? 0 : rowIndex;
                            this.scrollToRow(rowIndex);
                        });

                        self.updateTotalBuildUp();
                    }
                    pb.hide();
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                },
                error: function(error) {
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                    pb.hide();
                }
            };

            new buildspace.dialog.confirm(nls.unlinkScheduleOfQuantityTitle, nls.unlinkScheduleOfQuantityMsg, 80, 320, function() {
                dojo.xhrPost(xhrArgs);
            }, function() {
                pb.hide();
            });
        },
        contextMenu: function(e){
            if ( this.disableEditingMode ) {
                return false;
            }

            var rowCtxMenu = this.rowCtxMenu = new dijit.Menu();
            this.contextMenuItems(e);
            var info = {target: e.target, coords: e.keyCode !== keys.F10 && "pageX" in e ? {x: e.pageX, y: e.pageY } : null};
            var item = this.getItem(e.rowIndex);
            if(rowCtxMenu && item && item.id > 0 && (this.selection.isSelected(e.rowIndex) || e.rowNode && html.hasClass(e.rowNode, 'dojoxGridRowbar'))){
                rowCtxMenu._openMyself(info);
                evt.stop(e);
                return;
            }
        },
        onRowDblClick: function(e){
            this.inherited(arguments);
            var self = this,
                colField = e.cell.field,
                rowIndex = e.rowIndex,
                item = this.getItem(rowIndex);

            if(colField == 'quantity-value' && item.id > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item['quantity-has_build_up'][0] && item['quantity-final_value'][0] != 0){
                var dimensionColumnQuery = dojo.xhrGet({
                    url: "billBuildUpQuantity/getDimensionColumnStructure",
                    content:{uom_id: item.uom_id[0]},
                    handleAs: "json"
                });
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                pb.show();
                dimensionColumnQuery.then(function(dimensionColumns){
                    self.createBuildUpQuantityContainer(item, dimensionColumns);
                    pb.hide();
                });
            }
        },
        createBuildUpQuantityContainer: function(item, dimensionColumns){
            var self = this,
                baseContainer = new dijit.layout.BorderContainer({
                    style:"padding:0;margin:0;width:100%;height:100%;border:none!important;outline:none;",
                    gutters: false
                }),
                aContainer = new dijit.layout.AccordionContainer({
                    id: "acc_"+this.BillItem.id+"_"+item.id+"-container",
                    region: "center",
                    style:"padding:0;margin:0;width:100%;height:100%;border:none;outline:none;"
                }),
                hasImportedItemsXhr = dojo.xhrGet({
                    url: "scheduleOfQuantity/hasImportedItems/id/"+item.id,
                    handleAs: "json"
                }),
                formatter = new GridFormatter();

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
                    width: 'auto'
                },{
                    name: nls.factor,
                    field: 'factor-value',
                    width:'100px',
                    styles:'text-align:right;',
                    formatter: formatter.formulaNumberCellFormatter
                }];

                dojo.forEach(dimensionColumns, function(dimensionColumn){
                    structure.push({
                        name: dimensionColumn.title,
                        field: dimensionColumn.field_name,
                        width:'100px',
                        styles:'text-align:right;',
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
                   formatter: formatter.signCellFormatter
               });

                when(hasImportedItemsXhr, function(hasImportedItems){
                    aContainer.addChild(new dijit.layout.ContentPane({
                        title: nls.manualItems+'<span style="color:blue;float:right;">'+number.format(item.editable_total, {places:2})+'&nbsp;'+item.uom_symbol+'</span>',
                        style: "padding:0;border:none!important;",
                        doLayout: false,
                        id: 'accPane-SOQManual_'+item.id,
                        content: new SOQBuildUpQuantityGrid({
                            type: "manual",
                            scheduleOfQuantityItem: item,
                            gridOpts: {
                                editable: false,
                                structure: structure,
                                store: new dojo.data.ItemFileWriteStore({
                                    url:"scheduleOfQuantity/getBuildUpItemList/id/"+item.id+"/t/m",
                                    clearOnClose: true
                                })
                            }
                        })
                    }));

                    if(hasImportedItems){
                        var childPane = new dijit.layout.ContentPane({
                            title: nls.importedItems+'<span style="color:blue;float:right;">'+number.format(item.non_editable_total, {places:2})+'&nbsp;'+item.uom_symbol+'</span>',
                            style: "padding:0;border:none!important;",
                            doLayout: false,
                            id: 'accPane-SOQImported_'+item.id,
                            content: new SOQBuildUpQuantityGrid({
                                type: "imported",
                                scheduleOfQuantityItem: item,
                                gridOpts: {
                                    editable: false,
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
                    var container = dijit.byId(self.stackContainerId);
                    if(container){
                        var node = document.createElement("div");
                        var child = new dojox.layout.ContentPane( {
                            title: buildspace.truncateString(item.description, 28)+' ('+nls.scheduleOfQuantity+' - '+item.uom_symbol+')',
                            id: 'SOQBuildUpQuantityPage-'+item.id,
                            style: "padding:0;border:none!important;",
                            content: baseContainer,
                            executeScripts: true
                        },node );
                        container.addChild(child);
                        container.selectChild('SOQBuildUpQuantityPage-'+item.id);
                    }
                });
            }catch(e){console.log(e);}
        },
        contextMenuItems: function(e){
            var item = this.getItem(e.rowIndex);
            if(item && item.id > 0){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(this,'deleteRow', e.rowIndex)
                }));
            }
        },
        disableToolbarButtons: function(isDisable){
            var deleteRowBtn = dijit.byId('SOQGrid-'+this.billColumnSettingId+'_'+this.BillItem.id+'DeleteRow-button');
            deleteRowBtn._setDisabledAttr(isDisable);
        },
        updateTotalBuildUp: function(){
            this.buildUpSummaryWidget.refreshTotalQuantity();
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.ProjectBuilder.BillManager.ScheduleOfQuantityGridContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        billColumnSettingId: null,
        type: null,
        BillItem: null,
        disableEditingMode: false,
        stackContainerId: null,
        gridOpts: {},
        postCreate: function(){
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                disableEditingMode: this.disableEditingMode,
                billColumnSettingId: this.billColumnSettingId,
                BillItem: this.BillItem,
                stackContainerId: this.stackContainerId,
                region:"center"
            });

            var grid = this.grid = new ScheduleOfQuantityGrid(this.gridOpts);

            if(!this.disableEditingMode){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'SOQGrid-'+this.billColumnSettingId+'_'+this.BillItem.id+'DeleteRow-button',
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

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });
});