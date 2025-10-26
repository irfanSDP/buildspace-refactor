define('buildspace/apps/PostContractSubPackage/VariationOrder/VariationOrderClaim',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/html',
    "dijit/focus",
    'dojo/_base/event',
    'dojo/keys',
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, lang, html, focusUtil, evt, keys, EnhancedGrid, GridFormatter, nls){
    var IN_PROGRESS = 1;
    var LOCKED = 2;
    var CustomFormatter = {
        descriptionCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            cell.customClasses.push('disable-cell');
            if(item.id > 0){
                return nls.version+' '+nls.no+' '+cellValue;
            }else{
                return "&nbsp;";
            }
        },
        currentViewingCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.id > 0){
                if(cellValue){
                    return '<span class="icon-16-container icon-16-checkmark2" style="margin:0px auto; display:block;"></span>';
                }else{
                    return '<a href="#" onclick="return false;">'+nls.printThisRevision+'</a>';
                }
            }else{
                cell.customClasses.push('disable-cell');
                return "&nbsp;";
            }
        },
        statusCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(!item.can_be_edited[0]){
                cell.customClasses.push('disable-cell');
            }
            if(item.id > 0){
                if(parseInt(cellValue) == IN_PROGRESS){
                    return nls.addendumProgressing;
                }else{
                    return nls.addendumLocked;
                }
            }else{
                return "&nbsp;";
            }
        }
    };

    var VariationOrderClaimGrid = declare('buildspace.apps.PostContractSubPackage.VariationOrder.VariationOrderClaimEnhancedGrid', EnhancedGrid, {
        style: "border-top:none;",
        rowSelector: '0px',
        variationOrder: null,
        variationOrderItemContainer: null,
        region: 'center',
        constructor:function(args){
            var formatter = new GridFormatter(),
                statusOptions = {
                    options: [
                        nls.addendumProgressing,
                        nls.addendumLocked
                    ],
                    values: [
                        IN_PROGRESS,
                        LOCKED
                    ]
                };
            this.store = new dojo.data.ItemFileWriteStore({
                url:"variationOrder/getSpClaimList/id/"+args.variationOrder.id,
                clearOnClose: true
            });
            this.structure = [
                {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                {name: nls.claimVersion, field: 'revision', width:'auto', formatter: CustomFormatter.descriptionCellFormatter },
                {name: nls.currentPrintingRevision, field: 'is_viewing', width:'140px', styles:'text-align: center;', formatter: CustomFormatter.currentViewingCellFormatter},
                {name: nls.status, field: 'status', width:'120px', styles:'text-align: center;', editable:true, cellType:'dojox.grid.cells.Select', options: statusOptions.options, values:statusOptions.values, formatter: CustomFormatter.statusCellFormatter},
                {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
            ];
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex);
                if(item && item.id > 0 && item.can_be_deleted[0]){
                    self.disableToolbarButtons(false);
                }else{
                    self.disableToolbarButtons(true);
                }
            });

            this.on('CellClick', function(e) {
                var colField = e.cell.field,
                    item = self.getItem(e.rowIndex);

                if (colField == 'is_viewing' && item && item.id > 0 && !item.is_viewing[0]){
                    self.changeCurrentViewingRevision(item);
                }
            });
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;
            if(inCell != undefined){
                var item = this.getItem(inRowIndex),
                    field = inCell.field;

                if(!item.can_be_edited[0]){
                    window.setTimeout(function() {
                        self.edit.cancel();
                        self.focus.setFocusIndex(inRowIndex, inCell.index);
                    }, 10);
                    return;
                }
            }
            return this._canEdit;
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this,
                item = self.getItem(rowIdx),
                store = self.store,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            if(val !== item[inAttrName][0] && item.id > 0){
                pb.show();
                dojo.xhrPost({
                    url: "variationOrder/spClaimUpdate",
                    content: {
                        id: item.id,
                        attr_name: inAttrName,
                        val: val,
                        _csrf_token: item._csrf_token ? item._csrf_token : null
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            for(var property in resp.data){
                                if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                    store.setValue(item, property, resp.data[property]);
                                }
                            }
                            store.save();

                            if(item.status == LOCKED){
                                dijit.byId('variationOrderClaim-'+self.variationOrder.id+'AddRow-button')._setDisabledAttr(false);
                            }else if(item.status == IN_PROGRESS){
                                dijit.byId('variationOrderClaim-'+self.variationOrder.id+'AddRow-button')._setDisabledAttr(true);
                            }

                            var cell = self.getCellByField(inAttrName);
                            window.setTimeout(function() {
                                self.focus.setFocusIndex(rowIdx, cell.index);
                            }, 10);

                            var claimQuery = dojo.xhrGet({
                                url: "variationOrder/getSpClaimStatus",
                                content: {id: self.variationOrder.id},
                                handleAs: "json"
                            });
                            claimQuery.then(function(status){
                                var unitQuery = dojo.xhrGet({
                                    url: "variationOrder/getUnits",
                                    handleAs: "json"
                                });
                                unitQuery.then(function(uom){
                                    self.variationOrderItemContainer.createVariationOrderItemGrid(uom, status, false);
                                });
                            });
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            }
            self.inherited(arguments);
        },
        addNewClaim: function(_csrf_token){
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.addingRow+'. '+nls.pleaseWait+'...'
                }),
                store = self.store;

            pb.show();

            var claimQuery = dojo.xhrGet({
                url: "variationOrder/getSpClaimStatus",
                content: {id: self.variationOrder.id},
                handleAs: "json"
            });

            claimQuery.then(function(status){
                var msg = status.count == 0 ? nls.confirmAddFirstNewClaimTitleMsg :  nls.confirmAddNewClaimTitleMsg;

                new buildspace.dialog.confirm(nls.addNewClaim, msg, 80, 320, function() {
                    dojo.xhrPost({
                        url: "variationOrder/spClaimAdd",
                        content: { id: self.variationOrder.id, _csrf_token:_csrf_token },
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                store.fetchItemByIdentity({ 'identity' : buildspace.constants.GRID_LAST_ROW,  onItem : function(defaultLastRowItem){
                                    if(defaultLastRowItem){
                                        store.deleteItem(defaultLastRowItem);
                                    }
                                }});

                                dojo.forEach(resp.prev_claims, function(node){
                                    store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(item){
                                        for(var property in node){
                                            if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                                store.setValue(item, property, node[property]);
                                            }
                                        }
                                    }});
                                });

                                store.newItem(resp.item);
                                store.save();
                                dijit.byId('variationOrderClaim-'+self.variationOrder.id+'AddRow-button')._setDisabledAttr(true);

                                var unitQuery = dojo.xhrGet({
                                    url: "variationOrder/getUnits",
                                    handleAs: "json"
                                });
                                unitQuery.then(function(uom){
                                    dojo.xhrGet({//requery status to get the latest number of claim revisions
                                        url: "variationOrder/getSpClaimStatus",
                                        content: {id: self.variationOrder.id},
                                        handleAs: "json",
                                        load: function(status){
                                            self.variationOrderItemContainer.createVariationOrderItemGrid(uom, status, false);
                                        }
                                    });
                                });
                            }
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                            pb.hide();
                        },
                        error: function(error) {
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                            pb.hide();
                        }
                    });
                }, function() {
                    pb.hide();
                });
            });
        },
        deleteRow: function(rowIndex){
            var self = this,
                item = self.getItem(rowIndex),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            pb.show();

            var xhrArgs = {
                url: "variationOrder/spClaimDelete",
                content: { id: item.id, _csrf_token: item._csrf_token },
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        var store = self.store;

                        store.deleteItem(item);
                        store.save();

                        if(resp.data.prev_item > 0){
                            store.fetchItemByIdentity({ 'identity' : resp.data.prev_item,  onItem : function(prevItem){
                                if(prevItem){
                                    store.setValue(prevItem, "is_viewing", true);
                                    store.setValue(prevItem, "can_be_edited", true);
                                    store.setValue(prevItem, "can_be_deleted", true);
                                    store.save();
                                }
                            }});
                        }

                        if(resp.data.default_last_row){
                            store.newItem(resp.data.default_last_row);
                            store.save();
                        }

                        dijit.byId('variationOrderClaim-'+self.variationOrder.id+'AddRow-button')._setDisabledAttr(false);

                        var claimQuery = dojo.xhrGet({
                            url: "variationOrder/getSpClaimStatus",
                            content: {id: self.variationOrder.id},
                            handleAs: "json"
                        });
                        claimQuery.then(function(status){
                            var unitQuery = dojo.xhrGet({
                                url: "variationOrder/getUnits",
                                handleAs: "json"
                            });
                            unitQuery.then(function(uom){
                                self.variationOrderItemContainer.createVariationOrderItemGrid(uom, status, false);
                            });
                        });
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

            new buildspace.dialog.confirm(nls.deleteVariationOrderClaimDialogBoxTitle, nls.deleteVariationOrderClaimDialogBoxMsg, 80, 320, function() {
                dojo.xhrPost(xhrArgs);
            }, function() {
                pb.hide();
            });
        },
        changeCurrentViewingRevision: function(item){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            pb.show();

            dojo.xhrPost({
                url: "variationOrder/viewSpClaimRevision",
                content: { id: item.id, _csrf_token: item._csrf_token },
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        var store = self.store;

                        dojo.forEach(resp.items, function(node){
                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(item){
                                for(var property in node){
                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(item, property, node[property]);
                                    }
                                }
                            }});
                        });
                        store.save();

                        var claimQuery = dojo.xhrGet({
                            url: "variationOrder/getSpClaimStatus",
                            content: {id: self.variationOrder.id},
                            handleAs: "json"
                        });
                        claimQuery.then(function(status){
                            var unitQuery = dojo.xhrGet({
                                url: "variationOrder/getUnits",
                                handleAs: "json"
                            });
                            unitQuery.then(function(uom){
                                self.variationOrderItemContainer.createVariationOrderItemGrid(uom, status, false);
                            });
                        });
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
            });
        },
        disableToolbarButtons: function(isDisable){
            dijit.byId('variationOrderClaim-'+this.variationOrder.id+'DeleteRow-button')._setDisabledAttr(isDisable);
        }
    });

    return declare('buildspace.apps.PostContractSubPackage.VariationOrder.VariationOrderClaim', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        variationOrder: null,
        variationOrderItemContainer: null,
        claimStatus: null,
        postCreate: function(){
            this.inherited(arguments);
            var _csrf_token = this.claimStatus._csrf_token,
                grid = new VariationOrderClaimGrid({
                    variationOrder: this.variationOrder,
                    variationOrderItemContainer: this.variationOrderItemContainer
                }),
                toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    id: 'variationOrderClaim-'+this.variationOrder.id+'AddRow-button',
                    label: nls.addNewClaim,
                    iconClass: "icon-16-container icon-16-add",
                    disabled: (this.variationOrder.is_approved[0] == "true" && this.claimStatus.can_add_new_claim) ? false : true,
                    onClick: function(){
                        grid.addNewClaim(_csrf_token);
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    id: 'variationOrderClaim-'+this.variationOrder.id+'DeleteRow-button',
                    label: nls.deleteRow,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(){
                        if(grid.selection.selectedIndex > -1){
                            var item = grid.getItem(grid.selection.selectedIndex);
                            if(item.can_be_deleted[0]){
                                grid.deleteRow(grid.selection.selectedIndex);
                            }
                        }
                    }
                })
            );

            this.addChild(toolbar);
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });
});