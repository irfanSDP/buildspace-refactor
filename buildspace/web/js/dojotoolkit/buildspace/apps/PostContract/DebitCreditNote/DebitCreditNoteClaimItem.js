define('buildspace/apps/PostContract/DebitCreditNote/DebitCreditNoteClaimItem', [
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/enhanced/plugins/Rearrange',
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'buildspace/widget/grid/Filter',
    'buildspace/widget/grid/cells/DateTextBox',
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    "buildspace/apps/Attachment/UploadAttachmentContainer",
    'dojo/i18n!buildspace/nls/DebitCreditNote',
],
function (declare, lang, Rearrange, evt, keys, focusUtil,  FilterToolbar, DateTextBox, EnhancedGrid, GridFormatter, UploadAttachmentContainer, nls) {
    var DebitCreditClaimItemGrid = declare('buildspace.apps.PostContract.DebitCreditNote.DebitCreditClaimItemGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        accountGroupId: null,
        debitCreditNoteClaimId: null,
        container: null,
        locked: null,
        lockedForVerify: null,
        debitCreditNoteClaimGrid: null,
        accountGroupSelectionGrid: null,
        constructor: function() {
            this.inherited(arguments);
            this.rearranger = Rearrange(this, {});
        },
        canSort: function (inSortInfo) {
            return false;
        },
        canEdit: function() {
            return !this.locked && !this.lockedForVerify;
        },
        postCreate: function() {
            this.inherited(arguments);
            var self = this;

            if(!self.lockedForVerify) {
                this.on("RowContextMenu", function(e){
                    if(self.locked) {
                        return;
                    }
                    
                    this.selection.clear();
                    var item = this.getItem(e.rowIndex);
                    this.selection.setSelected(e.rowIndex, true);
                    this.contextMenu(e);
                    if(item && !isNaN(parseInt(item.id[0]))){
                        this.disableToolbarButtons(false);
                    }else{
                        this.disableToolbarButtons(true, ['Add']);
                    }
                }, true);

                this.on('RowClick', function(e){
                    if(self.locked) {
                        return;
                    }

                    var item = this.getItem(e.rowIndex);
                    if(item && !isNaN(parseInt(String(item.id)))){
                        this.disableToolbarButtons(false);
                    }else{
                        this.disableToolbarButtons(true, ['Add']);
                    }
                });
            }

            this.on('CellClick', function(e) {
                if(e.cell.field === 'attachment') {
                    var item = self.getItem(e.rowIndex);

                    if(!isNaN(String(item.id))) {
                        self.container.addChild(self.addUploadAttachmentContainer(item));
                    }
                }
            });
        },
        startup: function(e) {
            this.inherited(arguments);
            if(this.locked) {
                this.disableToolbarButtons(true);
            }
        },
        dodblclick: function (e) {
            this.onRowDblClick(e);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName) {
            var self = this;
            var item = this.getItem(rowIdx);
            var store = this.store;

            if(val !== item[inAttrName][0]) {
                if(inAttrName === 'account_code_id' || inAttrName === 'uom_id') {
                    if(val < 0) {
                        return;
                    }
                }

                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                
                var params = {
                    id: String(item.id),
                    debitCreditNoteClaimId: self.debitCreditNoteClaimId,
                    accountGroupId: self.accountGroupId,
                    attr_name: inAttrName,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null,
                };
            
                var url = "debitCreditNote/debitCreditNoteClaimItemUpdate";

                if(item && isNaN(parseInt(String(item.id[0])))) {
                    var prevItem = rowIdx > 0 ? this.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0,
                    });
                    url = "debitCreditNote/debitCreditNoteClaimItemAdd";
                }

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }

                    store.save();
                };

                pb.show().then(function() {
                    dojo.xhrPost({
                        url: url,
                        content: params,
                        handleAs: 'json',
                        load:function(resp) {
                            if(resp.success) {
                                if(!isNaN(parseInt(String(item.id)))) {
                                    updateCell(resp.data, store);
                                } else {
                                    store.deleteItem(item);
                                    store.save();
                                    dojo.forEach(resp.items, function(item){
                                        store.newItem(item);
                                    });
                                    store.save();
                                }

                                var cell = self.getCellByField(inAttrName);
                                window.setTimeout(function() {
                                    self.focus.setFocusIndex(rowIdx, cell.index);
                                }, 10);
                                self.debitCreditNoteClaimGrid.reloadGrid();
                                self.accountGroupSelectionGrid.reloadGrid();
                                pb.hide();
                            }
                        },
                        error:function(error) {
                            pb.hide();
                        }
                    });
                });
            }

            this.inherited(arguments);
        },
        addRow: function(e) {
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            if(this.selection.selectedIndex > -1) {
                var rowIndex = this.selection.selectedIndex;
                var self = this;
                var prevItemId;
                var content;
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var store = this.store;
                var itemBefore = this.getItem(rowIndex);
            }

            if(!isNaN(parseInt(String(itemBefore.id)))) {
                prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                content = {
                    prev_item_id: String(prevItemId),
                    before_id: String(itemBefore.id),
                    _csrf_token:itemBefore._csrf_token
                };
            } else {
                prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                content = {
                    id: String(itemBefore.id),
                    prev_item_id: String(prevItemId),
                    _csrf_token:itemBefore._csrf_token
                };
            }

            lang.mixin(content, {
                accountGroupId: self.accountGroupId,
                debitCreditNoteClaimId: self.debitCreditNoteClaimId,
            });

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'debitCreditNote/debitCreditNoteClaimItemAdd',
                    content: content,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            dojo.forEach(resp.items,function(data){
                                if(data.id > 0){
                                    var item = store.newItem(data);
                                    store.save();
                                    var itemIdx = self.getItemIndex(item);
                                    self.rearranger.moveRows([itemIdx], rowIndex);
                                    self.selection.clear();
                                }
                            });
                        }
                        window.setTimeout(function() {
                            self.selection.setSelected(rowIndex, true);
                            self.focus.setFocusIndex(rowIndex, 1);
                        },30);
                        pb.hide();
                    },
                    error: function(error) {
                        self.selection.clear();
                        self.disableToolbarButtons(true);
                        pb.hide();
                    }
                });
            });
        },
        deleteRow: function(e) {
            var rowIndex = this.selection.selectedIndex;
            var item     = this.getItem(rowIndex);

            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            if(this.selection.selectedIndex > -1 && item && !isNaN(parseInt(String(item.id)))) {
                var self = this;
                var pb   = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });
                
                var content = {
                    id: item.id,
                    _csrf_token: item._csrf_token,
                    accountGroupId: self.accountGroupId,
                    debitCreditNoteClaimId: self.debitCreditNoteClaimId,
                }

                buildspace.dialog.confirm(nls.deleteClaimItemConfirmation, '<div>'+nls.doYouReallywantToDeleteClaimItem+' ?</div>', 90, 320, function() {
                    pb.show().then(function() {
                        dojo.xhrPost({
                            url: "debitCreditNote/debitCreditClaimItemDelete",
                            content: content,
                            handleAs: 'json',
                            load: function(data) {
                                if(data.success){
                                    var items = data.items;
                                    var store = self.store;

                                    for(var i=0, len=items.length; i<len; ++i){
                                        store.fetchItemByIdentity({ 'identity' : items[i].id,  onItem : function(itm){
                                            store.deleteItem(itm);
                                            store.save();
                                        }});
                                    }

                                    items.length = 0;
                                    
                                    self.selection.clear();
                                    window.setTimeout(function() {
                                        self.focus.setFocusIndex(rowIndex, 0);
                                    }, 10);
                                    self.disableToolbarButtons(true);
                                }else{
                                    // buildspace.dialog.alert(nls.cannotDeleteAccountGroupTitle,nls.cannotDeleteAccountGroupMsg,90,320);
                                }

                                pb.hide();

                                self.selectedItem = null;
                                self.pasteOp = null;
                            },
                            error: function(error) {
                                self.selection.clear();
                                self.disableToolbarButtons(true);
                                self.selectedItem = null;
                                self.pasteOp = null;
                                pb.hide();
                            }
                        });
                    });
                });
            }
        },
        contextMenu: function(e){
            var rowCtxMenu = this.rowCtxMenu = new dijit.Menu();
            this.contextMenuItems(e);
            var info = {target: e.target, coords: e.keyCode !== keys.F10 && "pageX" in e ? {x: e.pageX, y: e.pageY } : null};
            var item = this.getItem(e.rowIndex);
            if(rowCtxMenu && item && (this.selection.isSelected(e.rowIndex) || e.rowNode && html.hasClass(e.rowNode, 'dojoxGridRowbar'))){
                rowCtxMenu._openMyself(info);
                evt.stop(e);
                return;
            }
        },
        contextMenuItems: function(e){
            var item = this.getItem(e.rowIndex);
            if(item && !isNaN(parseInt(String(item.id)))) {
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.addRow,
                    iconClass:"icon-16-container icon-16-add",
                    onClick: dojo.hitch(this, "addRow")
                }));
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(this, "deleteRow")
                }));
            }
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            var addRowBtn = dijit.byId('AddDebitCreditNoteClaimItemRow-button');
            var deleteRowBtn = dijit.byId('DeleteDebitCreditNoteClaimItemRow-button');
            addRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(label + 'DebitCreditNoteClaimItemRow-button');
                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        },
        addUploadAttachmentContainer: function(item){
            var self = this;
            var id = 'project-'+self.projectId+'-uploadAttachment';
            var container = dijit.byId(id);

            if(container){
                self.container.removeChild(container);
                container.destroy();
            }

            container = new UploadAttachmentContainer({
                id: id,
                region: 'bottom',
                item: item,
                disableEditing: self.locked || self.lockedForVerify,
                style:"padding:0;margin:0;border:none;width:100%;height:40%;"
            });

            return container;
        }
    });

    return declare('buildspace.apps.PostContract.DebitCreditNote.DebitCreditNoteClaimItem', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0px;border:none;width:100%;height:100%;",
        gutters: false,
        accountGroupId: null,
        debitCreditNoteClaimId: null,
        locked: null,
        lockedForVerify: null,
        debitCreditNoteClaimGrid: null,
        accountGroupSelectionGrid: null,
        constructor: function() {
            this.inherited(arguments);
        },
        postCreate: function () {
            this.inherited(arguments);
            var self = this;
            var formatter = new GridFormatter();

            var uploadCellFormatter = {
                blueCellFormatter: function (cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx);
                    return item.id >= 0 ? '<span style="color:blue;">'+item.attachment+'</span>' : null;
                }
            }

            pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'debitCreditNote/getAccountCodes',
                    handleAs: 'json',
                    content: { accountGroupId: self.accountGroupId },
                }).then(function(accountCodes){
                    dojo.xhrPost({
                        url: 'variationOrder/getUnits',
                        handleAs: 'json',
                        content: {},
                    }).then(function(uom) {
                        try{
                        var debitCreditClaimItemGrid = this.DebitCreditClaimItemGrid = new DebitCreditClaimItemGrid({
                            store: new dojo.data.ItemFileWriteStore({
                                clearOnClose: true,
                                url: "debitCreditNote/getDebitCreditClaimItems/debitCreditNoteClaimId/" + self.debitCreditNoteClaimId
                            }),
                            structure: [
                                { name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                { name: nls.description, field: 'account_code_id', editable: true, cellType: 'dojox.grid.cells.Select', options: accountCodes.options, values: accountCodes.values, noresize: true, width:'auto', styles:'text-align:left;' },
                                { name: nls.invoiceNumber, field: 'invoice_number', editable: true, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'120px', styles:'text-align:center;' },
                                { name: nls.invoiceDate, field: 'invoice_date', editable: true, cellType: 'buildspace.widget.grid.cells.DateTextBox', noresize: true, width:'100px', styles:'text-align:center;' },
                                { name: nls.dueDate, field: 'due_date', editable: true, cellType: 'buildspace.widget.grid.cells.DateTextBox', noresize: true, width:'100px', styles:'text-align:center;' },
                                { name: nls.attachment, field: 'attachment', editable: false, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'82px', styles:'text-align:center;', formatter: uploadCellFormatter.blueCellFormatter },
                                { name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', editable:true, cellType: 'dojox.grid.cells.Select', options: uom.options, values: uom.values },
                                { name: nls.quantity, field: 'quantity', editable: true, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'80px', styles:'text-align:center;', formatter: formatter.numberCellFormatter },
                                { name: nls.rate, field: 'rate', editable: true, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'120px', styles:'text-align:right;', formatter:formatter.currencyCellFormatter },
                                { name: nls.amount, field: 'amount', editable: false, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'160px', styles:'text-align:right;', formatter : formatter.unEditableCurrencyCellFormatter },
                                { name: nls.remark, field: 'remarks', editable: true, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'150px', styles:'text-align:center;' },
                            ],
                            debitCreditNoteClaimId: self.debitCreditNoteClaimId,
                            accountGroupId: self.accountGroupId,
                            container: self,
                            locked: self.locked,
                            lockedForVerify: self.lockedForVerify,
                            accountGroupSelectionGrid: self.accountGroupSelectionGrid,
                            debitCreditNoteClaimGrid: self.debitCreditNoteClaimGrid,
                        });

                        var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                        toolbar.addChild(
                            new dijit.form.Button({
                                id: 'AddDebitCreditNoteClaimItemRow-button',
                                label: nls.addRow,
                                iconClass: "icon-16-container icon-16-add",
                                disabled: true,
                                onClick: dojo.hitch(debitCreditClaimItemGrid, 'addRow'),
                            })
                        );
            
                        toolbar.addChild(
                            new dijit.form.Button({
                                id: 'DeleteDebitCreditNoteClaimItemRow-button',
                                label: nls.deleteRow,
                                iconClass: "icon-16-container icon-16-delete",
                                disabled: true,
                                onClick: dojo.hitch(debitCreditClaimItemGrid, 'deleteRow'),
                            })
                        );
            
                        var filter  = new FilterToolbar({
                            grid: debitCreditClaimItemGrid,
                            region:"top",
                            filterFields: [ {name : nls.description}]
                        });
            
                        self.addChild(filter);

                        if(!self.lockedForVerify) {
                            self.addChild(toolbar);
                        }

                        self.addChild(debitCreditClaimItemGrid);
    
                        pb.hide();
                        }
                        catch(err) {
                            console.log(err.message);
                        }
                    });
                });
            });
        },
    });
});