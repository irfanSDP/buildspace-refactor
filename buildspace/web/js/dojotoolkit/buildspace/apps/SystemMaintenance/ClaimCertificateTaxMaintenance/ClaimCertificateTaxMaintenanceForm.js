define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/enhanced/plugins/Rearrange',
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/Filter',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/ClaimCertificateTaxMaintenance'
],
function(declare, lang, Rearrange, evt, keys, focusUtil, Textarea, FilterToolbar, GridFormatter, nls) {
    var ClaimCertificateMaintenanceGrid = declare('buildspace.apps.ClaimCertificateTaxMaintenance.ClaimCertificateTaxMaintenanceGrid', dojox.grid.EnhancedGrid, {
        region: "center",
        style: "border-top:none;",
        selectedItem: null,
        keepSelection: true,
        rowSelector: '0px',
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);

            this.on("RowContextMenu", function(e){
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
                var item = this.getItem(e.rowIndex);
                if(item && !isNaN(parseInt(item.id[0]))){
                    this.disableToolbarButtons(false);
                }else{
                    this.disableToolbarButtons(true, ['Add']);
                }
            });
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName) {
            var self = this;
            var item = this.getItem(rowIdx);
            var store = this.store;

            if(val !== item[inAttrName][0]) {
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });

                var params = {
                    id: item.id,
                    // accountGroupId: this.accountGroupId,
                    attr_name: inAttrName,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                };

                var url = "systemMaintenance/claimCertificateTaxUpdate";

                if(item && isNaN(parseInt(item.id[0]))){
                    var prevItem = rowIdx > 0 ? this.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0
                    });
                    url = "systemMaintenance/claimCertificateTaxAdd";
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
                                if(!isNaN(parseInt(item.id[0]))) {
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
        dodblclick: function(e){
            this.onRowDblClick(e);
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

                if(!isNaN(parseInt(itemBefore.id[0]))) {
                    prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                    content = {
                        prev_item_id: prevItemId,
                        // accountGroupId: this.accountGroupId,
                        before_id: itemBefore.id,
                        _csrf_token:itemBefore._csrf_token
                    };
                } else {
                    prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                    content = {
                        id: itemBefore.id,
                        // accountGroupId: this.accountGroupId,
                        prev_item_id: prevItemId,
                        _csrf_token:itemBefore._csrf_token
                    };
                }

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'systemMaintenance/claimCertificateTaxAdd',
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
            
            if(this.selection.selectedIndex > -1 && item && !isNaN(parseInt(item.id[0]))) {
                var self = this;
                var pb   = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });

                buildspace.dialog.confirm(nls.deleteCertificateConfirmation, '<div>'+nls.doYouReallywantToDeleteCertificate+' ?</div>', 90, 320, function() {
                    pb.show().then(function() {
                        dojo.xhrPost({
                            url: "systemMaintenance/claimCertificateTaxDelete",
                            content: { id: item.id, _csrf_token: item._csrf_token },
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
                                    buildspace.dialog.alert(nls.cannotDeleteCertificateTitle,nls.cannotDeleteCertificateMsg,90,320);
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
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            var addRowBtn = dijit.byId('AddClaimCertTaxRow-button');
            var deleteRowBtn = dijit.byId('DeleteClaimCertTaxRow-button');

            addRowBtn._setDisabledAttr(isDisable);
            deleteRowBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(label+'ClaimCertTaxRow-button');
                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        }
    });

    return declare('buildspace.apps.ClaimCertificateTaxMaintenance.ClaimCertificateTaxMaintenanceFormContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        postCreate: function() {
            this.inherited(arguments);

            var formatter = new GridFormatter();

            var grid = this.ClaimCertificateMaintenanceGrid = new ClaimCertificateMaintenanceGrid({
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: "systemMaintenance/getClaimCertificateTaxes",
                }),
                structure: [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', editable: true, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'auto'},
                    {name: nls.tax, field: 'tax', width:'150px', editable: true, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true},
                    {name: nls.updatedAt, field: 'updated_at', styles:'text-align:center;', width:'120px'},
                ],
                // accountGroupId: this.accountGroupId,
            });

            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'AddClaimCertTaxRow-button',
                    label: nls.addRow,
                    iconClass: "icon-16-container icon-16-add",
                    disabled: true,
                    onClick: dojo.hitch(grid, 'addRow')
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'DeleteClaimCertTaxRow-button',
                    label: nls.deleteRow,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: true,
                    onClick: dojo.hitch(grid, 'deleteRow')
                })
            );

            var filter  = new FilterToolbar({
                grid: grid,
                region:"top",
                filterFields: [ {name : nls.name}]
            });

            this.addChild(filter);
            this.addChild(toolbar);
            this.addChild(grid);
        },
    });
});