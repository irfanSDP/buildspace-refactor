define('buildspace/apps/PostContract/VariationOrder/VariationOrderClaim',[
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
            if(item && !isNaN(parseInt(String(item.id)))){
                return nls.version+' '+nls.no+' '+cellValue;
            }else{
                return "&nbsp;";
            }
        },
        currentViewingCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item && !isNaN(parseInt(String(item.id)))){
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
            if(item && !isNaN(parseInt(String(item.id)))){
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

    var VariationOrderClaimGrid = declare('buildspace.apps.PostContract.VariationOrder.VariationOrderClaimEnhancedGrid', EnhancedGrid, {
        style: "border-top:none;",
        rowSelector: '0px',
        project: null,
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

            var gridStructure = [
                {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                {name: nls.claimVersion, field: 'revision', width:'auto', formatter: CustomFormatter.descriptionCellFormatter }
            ];

            if(parseInt(String(args.project.post_contract_type_id)) == buildspace.constants.POST_CONTRACT_TYPE_NEW) {
                gridStructure.push({
                    name: nls.claimCertificateNumber,
                    field: 'claim_cert_number',
                    styles:'text-align:center;',
                    width:'100px',
                    noresize: true
                });
            }

            var standardColumns = [
                {name: nls.currentPrintingRevision, field: 'is_viewing', width:'140px', styles:'text-align: center;', formatter: CustomFormatter.currentViewingCellFormatter},
                {name: nls.status, field: 'status', width:'120px', styles:'text-align: center;', editable:true, cellType:'dojox.grid.cells.Select', options: statusOptions.options, values:statusOptions.values, formatter: CustomFormatter.statusCellFormatter}
            ];

            dojo.forEach(standardColumns, function(standardColumn){
                gridStructure.push(standardColumn);
            });

            this.structure = gridStructure;
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);

            this.on('RowClick', function(e){
                var item = this.getItem(e.rowIndex);
                if(item && !isNaN(parseInt(String(item.id))) && item.can_be_deleted[0]){
                    this.disableToolbarButtons(false);
                }else{
                    this.disableToolbarButtons(true);
                }
            });

            this.on('CellClick', function(e) {
                var colField = e.cell.field,
                    item = this.getItem(e.rowIndex);

                if (colField == 'is_viewing' && item && !isNaN(parseInt(String(item.id))) && !item.is_viewing[0]){
                    this.changeCurrentViewingRevision(item);
                }
            });
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;
            if(inCell != undefined){
                var item = this.getItem(inRowIndex),
                    field = inCell.field;

                if((!item.can_be_edited[0]) || self.locked){
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
                item = this.getItem(rowIdx),
                store = this.store;

            var postFunc = function(){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: "variationOrder/claimUpdate",
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
                                    url: "variationOrder/getClaimStatus",
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
                });
            };

            if(val !== item[inAttrName][0] && !isNaN(parseInt(String(item.id)))){
                if(inAttrName == 'status' && val == LOCKED) {
                    buildspace.dialog.confirm(nls.confirmSubmit, nls.cannotBeUndone, 60, 200, function(){
                        postFunc();
                    }, function(){
                        store.setValue(item, 'status', IN_PROGRESS);
                    });
                }
                else
                {
                    postFunc();
                }
            }

            this.inherited(arguments);
        },
        addNewClaim: function(_csrf_token){
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.addingRow+'. '+nls.pleaseWait+'...'
                }),
                store = this.store;

            dojo.xhrGet({
                url: "variationOrder/getClaimStatus",
                content: {id: self.variationOrder.id},
                handleAs: "json"
            }).then(function(status){
                var msg = status.count == 0 ? nls.confirmAddFirstNewClaimTitleMsg :  nls.confirmAddNewClaimTitleMsg;

                new buildspace.dialog.confirm(nls.addNewClaim, msg, 90, 380, function() {
                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: "variationOrder/claimAdd",
                            content: { id: self.variationOrder.id, _csrf_token:_csrf_token },
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success){
                                    store.close();
                                    self._refresh();

                                    dijit.byId('variationOrderClaim-'+self.variationOrder.id+'AddRow-button')._setDisabledAttr(true);

                                    dojo.xhrGet({
                                        url: "variationOrder/getUnits",
                                        handleAs: "json"
                                    }).then(function(uom){
                                        dojo.xhrGet({//requery status to get the latest number of claim revisions
                                            url: "variationOrder/getClaimStatus",
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
                    });
                }, function() {
                    //
                });
            });
        },
        deleteRow: function(){
            var rowIndex = this.selection.selectedIndex,
                item = this.getItem(rowIndex);

            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            if(this.selection.selectedIndex > -1 && item && !isNaN(parseInt(item.id[0])) && item.can_be_deleted[0]) {
                var self = this,
                    store = this.store,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.deleting+'. '+nls.pleaseWait+'...'
                    });

                var xhrArgs = {
                    url: "variationOrder/claimDelete",
                    content: { id: item.id, _csrf_token: item._csrf_token },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            store.close();
                            self._refresh();

                            dijit.byId('variationOrderClaim-'+self.variationOrder.id+'AddRow-button')._setDisabledAttr(false);

                            dojo.xhrGet({
                                url: "variationOrder/getClaimStatus",
                                content: {id: self.variationOrder.id},
                                handleAs: "json"
                            }).then(function(status){
                                dojo.xhrGet({
                                    url: "variationOrder/getUnits",
                                    handleAs: "json"
                                }).then(function(uom){
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

                new buildspace.dialog.confirm(nls.deleteVariationOrderClaimDialogBoxTitle, nls.deleteVariationOrderClaimDialogBoxMsg, 90, 380, function() {
                    pb.show().then(function(){
                        dojo.xhrPost(xhrArgs);
                    });
                }, function() {
                    //
                });
            }
        },
        changeCurrentViewingRevision: function(item){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: "variationOrder/viewClaimRevision",
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

                            dojo.xhrGet({
                                url: "variationOrder/getClaimStatus",
                                content: {id: self.variationOrder.id},
                                handleAs: "json"
                            }).then(function(status){
                                dojo.xhrGet({
                                    url: "variationOrder/getUnits",
                                    handleAs: "json"
                                }).then(function(uom){
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
            });
        },
        disableToolbarButtons: function(isDisable){
            var deleteButton = dijit.byId('variationOrderClaim-'+this.variationOrder.id+'DeleteRow-button');
            if(deleteButton) deleteButton._setDisabledAttr(isDisable);
        }
    });

    return declare('buildspace.apps.PostContract.VariationOrder.VariationOrderClaim', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;border:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        variationOrder: null,
        variationOrderItemContainer: null,
        claimStatus: null,
        claimCertificate: null,
        locked: false,
        postCreate: function(){
            this.inherited(arguments);

            var url = "variationOrder/getClaimList/id/"+this.variationOrder.id;

            if(this.claimCertificate) url += "/claimRevision/"+this.claimCertificate.post_contract_claim_revision_id;

            var store = new dojo.data.ItemFileWriteStore({
                url:url,
                clearOnClose: true
            });

            var _csrf_token = this.claimStatus._csrf_token,
                grid = new VariationOrderClaimGrid({
                    project: this.project,
                    variationOrder: this.variationOrder,
                    variationOrderItemContainer: this.variationOrderItemContainer,
                    claimCertificate: this.claimCertificate,
                    store: store,
                    locked: this.locked
                });

            if(!this.locked)
            {
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'variationOrderClaim-'+this.variationOrder.id+'AddRow-button',
                        label: nls.addNewClaim,
                        iconClass: "icon-16-container icon-16-add",
                        disabled: (this.variationOrder.is_approved[0] == "true" && this.claimStatus.can_add_new_claim) ? false : true,
                        onClick: lang.hitch(grid, "addNewClaim", _csrf_token)
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'variationOrderClaim-'+this.variationOrder.id+'DeleteRow-button',
                        label: nls.deleteRow,
                        iconClass: "icon-16-container icon-16-delete",
                        disabled: grid.selection.selectedIndex > -1 ? false : true,
                        onClick: lang.hitch(grid, "deleteRow")
                    })
                );
            }

            if(toolbar) this.addChild(toolbar);
            this.addChild(grid);
        }
    });
});
