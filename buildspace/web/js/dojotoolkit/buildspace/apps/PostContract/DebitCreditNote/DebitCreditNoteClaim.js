define('buildspace/apps/PostContract/DebitCreditNote/DebitCreditNoteClaim', [
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/enhanced/plugins/Rearrange',
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'buildspace/widget/grid/Filter',
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    "buildspace/apps/Attachment/UploadAttachmentContainer",
    'dojo/i18n!buildspace/nls/DebitCreditNote',
],
function (declare, lang, Rearrange, evt, keys, focusUtil, FilterToolbar, EnhancedGrid, GridFormatter, UploadAttachmentContainer, nls) {
    var DebitCreditClaimGrid = declare('buildspace.apps.PostContract.DebitCreditNote.DebitCreditClaimGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        projectId: null,
        accountGroupId: null,
        container: null,
        locked: false,
        constructor: function() {
            this.inherited(arguments);
            this.rearranger = Rearrange(this, {});
        },
        canSort: function (inSortInfo) {
            return false;
        },
        canEdit: function(inCell, inRowIndex){
            if(this.locked) {
                return false;
            }

            if(inCell != undefined) {
                var item = this.getItem(inRowIndex);
                var canEdit = (String(item.id) === 'LAST_ROW') ? true : !item.locked[0];
                
                return canEdit;
            }

            return this._canEdit;
        },
        postCreate: function() {
            this.inherited(arguments);
            var self = this;

            if(!self.locked) {
                this.on("RowContextMenu", function(e){
                    this.selection.clear();
                    var item = this.getItem(e.rowIndex);

                    if(item && item.locked[0]) {
                        return;
                    }

                    this.selection.setSelected(e.rowIndex, true);
                    this.contextMenu(e);
                    if(item && !isNaN(parseInt(String(item.id)))){
                        this.disableToolbarButtons(false);
                    }else{
                        this.disableToolbarButtons(true, ['Add']);
                    }
                }, true);

                this.on('RowClick', function(e){
                    var item = this.getItem(e.rowIndex);

                    if(item && item.locked[0]) {
                        this.disableToolbarButtons(true);
                        return;
                    }

                    if(item && !isNaN(parseInt(String(item.id)))){
                        this.disableToolbarButtons(false);
                    }else{
                        this.disableToolbarButtons(true, ['Add']);
                    }
                });
            }

            this.on('CellClick', function(e) {
                if(e.cell.field === 'attachment') {
                    var item = this.getItem(e.rowIndex);

                    if(!isNaN(String(item.id))) {
                        self.container.addChild(self.addUploadAttachmentContainer(item));
                    }
                }

                if(!this.locked) {
                    if(e.cell.field === 'claim_cert_number') {
                        var item = this.getItem(e.rowIndex);

                        if(!isNaN(String(item.id)) && !String(item.claim_cert_number)) {
                            var pb = buildspace.dialog.indeterminateProgressBar({
                                title:nls.pleaseWait+'...'
                            });

                            pb.show().then(function(){
                                dojo.xhrPost({
                                    url: "debitCreditNote/getOpenClaimCertificate",
                                    content: {
                                        projectStructureId: self.projectId,
                                        accountGroupId: self.accountGroupId,
                                    },
                                    handleAs: 'json',
                                    load: function(data) {
                                        var id = parseInt(String(data.id));
                                        pb.hide();
                                        if(!isNaN(id) && id > 0 ){
                                            var content = '<div>'+nls.attachClaimCertConfirm+'<br /><br /><b>Claim Certificate No.</b> : '+data.version+'</div>';
                                            buildspace.dialog.confirm(nls.confirmation,content,120,380, function(){
                                                self.attachClaimCertificate(id, parseInt(String(item.id)))
                                            });
                                        }else{
                                            buildspace.dialog.alert(nls.noInProgressClaimCertificate, nls.noInProgressClaimCertificateMsg, 100, 300);
                                        }
                                    },
                                    error: function(error) {
                                        pb.hide();
                                    }
                                });
                            });
                        }
                    }
                }
            });
        },
        startup: function(e) {
            this.inherited(arguments);
        },
        dodblclick: function (e) {
            this.onRowDblClick(e);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName) {
            var self = this;
            var item = this.getItem(rowIdx);
            var store = this.store;

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                
                var params = {
                    id: String(item.id),
                    projectStructureId: self.projectId,
                    accountGroupId: self.accountGroupId,
                    attr_name: inAttrName,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null,
                };

                var url = "debitCreditNote/debitCreditClaimUpdate";

                if(item && isNaN(parseInt(String(item.id)))) {
                    var prevItem = rowIdx > 0 ? this.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0,
                    });
                    url = "debitCreditNote/debitCreditClaimAdd";
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
        addRow: function() {
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

                if(!isNaN(parseInt(String(itemBefore.id)))) {
                    prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                    content = {
                        prev_item_id: prevItemId,
                        before_id: itemBefore.id,
                        _csrf_token:itemBefore._csrf_token
                    };
                } else {
                    prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                    content = {
                        id: itemBefore.id,
                        prev_item_id: prevItemId,
                        _csrf_token:itemBefore._csrf_token
                    };
                }

                lang.mixin(content, {
                    projectStructureId: self.projectId,
                    accountGroupId: self.accountGroupId,
                });

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'debitCreditNote/debitCreditClaimAdd',
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
            }
        },
        deleteRow: function() {
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
                    projectStructureId: self.projectId,
                    accountGroupId: self.accountGroupId,
                }
                
                buildspace.dialog.confirm(nls.deleteClaimConfirmation, '<div>'+nls.doYouReallywantToDeleteClaim+' ?</div>', 90, 320, function() {
                    pb.show().then(function() {
                        dojo.xhrPost({
                            url: "debitCreditNote/debitCreditClaimDelete",
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
            if(item && !isNaN(parseInt(item.id[0]))){
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
            var addRowBtn = dijit.byId('AddDebitCreditNoteClaimRow-button');
            var deleteRowBtn = dijit.byId('DeleteDebitCreditNoteClaimRow-button');

            addRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(label+'DebitCreditNoteClaimRow-button');
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
                disableEditing: item.locked[0] || self.locked,
                style:"padding:0;margin:0;border:none;width:100%;height:40%;"
            });

            return container;
        },
        attachClaimCertificate: function(claimCertificateId, itemId) {
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            var store = this.store;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'debitCreditNote/claimCertificateAttach',
                    content: {
                        cid: claimCertificateId,
                        debitCreditNoteClaimId: itemId
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            store.fetchItemByIdentity({ 'identity' : resp.item.id,  onItem : function(item){
                                for(var property in resp.item){
                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(item, property, resp.item[property]);
                                    }
                                }
                                store.save();
                            }});

                            pb.hide();
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        reloadGrid: function() {
            this.store.save();
            this.store.close();
            this._refresh();
        }
    });

    return declare('buildspace.apps.PostContract.DebitCreditNote.DebitCreditNoteClaim', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0px;border:none;width:100%;height:100%;",
        gutters: false,
        projectId: null,
        accountGroupId: null,
        claimCertificate: null,
        stackContainer: null,
        locked: false,
        constructor: function() {
            this.inherited(arguments);
        },
        createGetDebitCreditClaimsURL: function() {
            var url = "debitCreditNote/getDebitCreditClaims/projectStructureId/" + this.projectId + "/accountGroupId/" + this.accountGroupId;

            if(this.claimCertificate) {
                url += "/postContractClaimRevisionId/" + this.claimCertificate.post_contract_claim_revision_id
            }

            return url;
        },
        postCreate: function () {
            this.inherited(arguments);
            var self = this;

            var formatter = new GridFormatter();

            var uploadCellFormatter = {
                blueCellFormatter: function (cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx);
                    return (item && parseInt(String(item.id)) >= 0) ? '<span style="color:blue;">'+item.attachment+'</span>' : null;
                }
            }

            var debitCreditClaimGrid = this.DebitCreditClaimGrid = new DebitCreditClaimGrid({
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: self.createGetDebitCreditClaimsURL(),
                }),
                structure: [
                    { name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    { name: nls.description, field: 'description', editable: true, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'auto', styles:'text-align:left;' },
                    { name: nls.attachment, field: 'attachment', editable: false, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'82px', styles:'text-align:center;', formatter: uploadCellFormatter.blueCellFormatter },
                    { name: nls.claimCertificateNo, field: 'claim_cert_number', editable: false, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'80px', styles:'text-align:center;' },
                    { name: nls.amount, field: 'amount', editable: false, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'160px', styles:'text-align:right;', formatter : formatter.unEditableCurrencyCellFormatter },
                    { name: nls.updatedAt, field: 'updated_at', editable: false, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'120px', styles:'text-align:center;' },
                ],
                projectId: self.projectId,
                accountGroupId: self.accountGroupId,
                claimCertificate: self.claimCertificate,
                onRowDblClick: self.onRowDblClick,
                container: self,
                locked: self.locked,
            });

            if(!this.locked) {
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'AddDebitCreditNoteClaimRow-button',
                        label: nls.addRow,
                        iconClass: "icon-16-container icon-16-add",
                        disabled: true,
                        onClick: dojo.hitch(debitCreditClaimGrid, 'addRow'),
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'DeleteDebitCreditNoteClaimRow-button',
                        label: nls.deleteRow,
                        iconClass: "icon-16-container icon-16-delete",
                        disabled: true,
                        onClick: dojo.hitch(debitCreditClaimGrid, 'deleteRow'),
                    })
                );
            }

            var filter  = new FilterToolbar({
                grid: debitCreditClaimGrid,
                region:"top",
                filterFields: [ {name : nls.description}]
            });

            this.addChild(filter);

            if(!this.locked) {
                this.addChild(toolbar);
            }
            
            this.addChild(debitCreditClaimGrid);
        },
    });
});