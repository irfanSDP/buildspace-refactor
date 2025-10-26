define('buildspace/apps/PostContract/PostContractClaim/PostContractClaimGrid',[
    'dojo/_base/declare',
    "dojo/_base/connect",
    'dojo/_base/lang',
    'dojo/_base/html',
    "dojo/dom-style",
    "dojo/number",
    "dijit/focus",
    'dojo/_base/event',
    'dojo/keys',
    "dojo/has",
    "dijit/TooltipDialog",
    "dijit/popup",
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/Rearrange",
    "../ImportedClaims/MaterialOnSiteContainer",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, connect, lang, html, domStyle, number, focusUtil, evt, keys, has, TooltipDialog, popup, EnhancedGrid, Rearrange, ImportedMaterialOnSiteContainer, FormulatedColumn, GridFormatter, nls) {

    var PostContractClaimGrid = declare('buildspace.apps.PostContract.PostContractClaim.PostContractClaimGrid', EnhancedGrid, {
        style: "border-top:none;",
        selectedItem: null,
        region: 'center',
        keepSelection: true,
        postContractClaimGridContainer: null,
        postContractClaimItemContainer: null,
        postContractClaimFirstLevelContainer: null,
        store: null,
        project: null,
        type: null,
        withProgressClaim: null,
        itemLevel: null,
        addUrl: null,
        updateUrl: null,
        deleteUrl: null,
        verifierListUrl: null,
        locked: false,
        constructor:function(args){
            this.connects = [];
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this, {});
        },
        canSort: function(inSortInfo){
            return false;
        },
        canEdit: function(inCell, inRowIndex) {
            var self = this;
            if(inCell != undefined){
                var item = this.getItem(inRowIndex),
                    field = inCell.field;

                if ( (field == 'current_percentage-value' || field == 'current_amount-value' || field == 'current_quantity-value' || field == 'up_to_date_percentage-value' || field == 'up_to_date_amount-value' || field == 'up_to_date_quantity-value') && !isNaN(parseInt(item.id[0])) ){
                    if(item.can_claim[0]) return true;
                }

                if(!item.can_be_edited[0] || self.locked) return false;

                if (!isNaN(parseInt(String(item.id))) && !item.can_be_edited[0] && !this.itemLevel){

                    return false;
                }
                else
                {
                    return true;
                }
            }

            return this._canEdit;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            var myTooltipDialog = null;
             this._connects.push(connect.connect(this, 'onCellMouseOver', function(e){
                var item = self.getItem(e.rowIndex),
                    colField = e.cell.field,
                    rowIndex = e.rowIndex;

                var fieldConstantName = colField.replace("-value", "");

                // will show tooltip for formula, if available
                if (!item || !item.hasOwnProperty(fieldConstantName+'-has_formula') || typeof item[fieldConstantName+'-has_formula'] === 'undefined' || ! item[fieldConstantName+'-has_formula'][0] ) {
                    return;
                }

                var formulaValue = item[fieldConstantName+'-value'][0];

                // convert ITEM ID into ROW ID (if available)
                formulaValue = this.formulatedColumn.convertItemIdToRowIndex(formulaValue, rowIndex);

                if(myTooltipDialog === null) {
                    myTooltipDialog = new TooltipDialog({
                        content: formulaValue,
                        onMouseLeave: function() {
                            popup.close(myTooltipDialog);
                        }
                    });

                    popup.open({
                        popup: myTooltipDialog,
                        around: e.cellNode
                    });
                }
            }));

            if(!this.locked){
                this.on("RowContextMenu", function(e){
                    self.selection.clear();
                    var item = self.getItem(e.rowIndex);
                    self.selection.setSelected(e.rowIndex, true);
                    self.contextMenu(e);
                }, true);

                this.on('RowClick', function(e){
                    var item = self.getItem(e.rowIndex);
                    if(item && !isNaN(parseInt(String(item.id))) && !item.can_be_edited[0]){
                        self.disableToolbarButtons(true);
                    }else{
                        self.disableToolbarButtons(false);
                    }
                });
            }

            this.on('CellClick', function(e) {
                var colField = e.cell.field,
                    item = this.getItem(e.rowIndex);

                if (colField == 'attachment' && item.class == 'PostContractClaimItem' ){
                    this.postContractClaimItemContainer.addUploadAttachmentContainer(item);
                }else if(colField == 'attachment' && item.class == 'PostContractClaim'){
                    this.postContractClaimFirstLevelContainer.addUploadAttachmentContainer(item);
                }
            });
        },
        onStyleRow: function(e) {
            this.inherited(arguments);

            if(e.node.children[0] && e.node.children[0].children[0].rows.length >= 2){
                var elemToHide = e.node.children[0].children[0].rows[1],
                    childElement = e.node.children[0].children[0].rows[0].children;

                elemToHide.parentNode.removeChild(elemToHide);

                dojo.forEach(childElement, function(child, i){
                    var rowSpan = dojo.attr(child, 'rowSpan');

                    if(!rowSpan || rowSpan < 2)
                        dojo.attr(child, 'rowSpan', 2);
                });
            }
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        editableCellDblClick: function(e){
            var event;
            if(this._click.length > 1 && has('ie')){
                event = this._click[1];
            }else if(this._click.length > 1 && this._click[0].rowIndex != this._click[1].rowIndex){
                event = this._click[0];
            }else{
                event = e;
            }
            this.focus.setFocusCell(event.cell, event.rowIndex);
            this.onRowClick(event);
            this.onRowDblClick(e);
        },
        dodblclick: function(e){
            if(e.cellNode){
                if(e.cell.editable){
                    this.editableCellDblClick(e);
                }else{
                    this.onCellDblClick(e);
                }
            }else{
                this.onRowDblClick(e);
            }
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = this.getItem(rowIdx), store = this.store;

            var attrNameParsed = inAttrName.replace("-value","");//for any formulated column
            if(inAttrName.indexOf("-value") !== -1){
                val = this.formulatedColumn.convertRowIndexToItemId(val, rowIdx);
            }

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

                var params = {
                    id: item.id,
                    attr_name: attrNameParsed,
                    val: val,
                    relation_id: item.relation_id,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                if(item && isNaN(parseInt(item.id))){
                    var prevItem = rowIdx > 0 ? this.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0,
                        relation_id: item.relation_id,
                        type: this.type
                    });
                    url = this.addUrl;
                }

                var postFunc = function(){

                    var postFuncPb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.pleaseWait+'...'
                    });

                    postFuncPb.show().then(function(){
                        dojo.xhrPost({
                            url: url,
                            content: params,
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success){
                                    if(!isNaN(parseInt(String(item.id)))){
                                        for(var property in resp.data){
                                                if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                                    store.setValue(item, property, resp.data[property]);
                                                }
                                            }
                                    }else{
                                        store.deleteItem(item);
                                        store.save();
                                        dojo.forEach(resp.items, function(item){
                                            store.newItem(item);
                                        });
                                        store.save();
                                    }

                                    if(!item.can_be_edited[0]) self.disableToolbarButtons(true, ["Add", "OmitFromBill"]);

                                    var cell = self.getCellByField(inAttrName);
                                    window.setTimeout(function() {
                                        self.focus.setFocusIndex(rowIdx, cell.index);
                                    }, 10);
                                    postFuncPb.hide();
                                }

                            },
                            error: function(error) {
                                postFuncPb.hide();
                            }
                        });
                    });
                };

                if((attrNameParsed == 'status') && (val == buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PENDING))
                {
                    if(parseInt(self.type) == buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE)
                    {
                        var item = self.selection.getFirstSelected();

                        pb.show().then(function() {
                            dojo.xhrGet({
                                url: 'postContractClaimMaterialOnSite/checkAmountAgainstRetentionSum/pid/' + self.project.id,
                                content: {
                                    id: item.id,
                                },
                                handleAs: 'json',
                                load: function (response) {
                                    if (response.success && response.exceeded) {
                                        buildspace.dialog.confirm(nls.amountExceedsRetentionSum, '<div>'+nls.retentionSum+': ' + response.retentionSum + '. <p>Proceed ?</p></div>', 70, 200, function(){
                                            dojo.xhrGet({
                                                url: self.verifierListUrl,
                                                content: {
                                                    pid: self.project.id
                                                },
                                                handleAs: 'json',
                                                load: function(resp) {
                                                    var count = 0;
                                                    for(var key in resp.items)
                                                    {
                                                        count++;
                                                    }
                                                    if(resp.success){
                                                        buildspace.dialog.confirm(nls.confirmSubmit, '<div>'+nls.numberOfReviewers+': ' + count + '</div>', 60, 200, function(){
                                                            postFunc();
                                                        }, function(){
                                                            store.setValue(item, 'status', buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PREPARING);
                                                        });
                                                    }
                                                    pb.hide();
                                                },
                                                error: function(error) {
                                                    pb.hide();
                                                }
                                            });
                                        }, function(){
                                            store.setValue(item, 'status', buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PREPARING);
                                        });
                                    } else {
                                        dojo.xhrGet({
                                            url: self.verifierListUrl,
                                            content: {
                                                pid: self.project.id
                                            },
                                            handleAs: 'json',
                                            load: function(resp) {
                                                var count = 0;
                                                for(var key in resp.items)
                                                {
                                                    count++;
                                                }
                                                if(resp.success){
                                                    buildspace.dialog.confirm(nls.confirmSubmit, '<div>'+nls.numberOfReviewers+': ' + count + '</div>', 60, 200, function(){
                                                        postFunc();
                                                    }, function(){
                                                        store.setValue(item, 'status', buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PREPARING);
                                                    });
                                                }
                                                pb.hide();
                                            },
                                            error: function(error) {
                                                pb.hide();
                                            }
                                        });
                                    }

                                    pb.hide();
                                },
                                error: function (error) {
                                    pb.hide();
                                }
                            });
                        });
                    }
                    else
                    {
                        pb.show().then(function(){
                            dojo.xhrGet({
                                url: self.verifierListUrl,
                                content: {
                                    pid: self.project.id
                                },
                                handleAs: 'json',
                                load: function(resp) {
                                    var count = 0;
                                    for(var key in resp.items)
                                    {
                                        count++;
                                    }
                                    if(resp.success){
                                        buildspace.dialog.confirm(nls.confirmSubmit, '<div>'+nls.numberOfReviewers+': ' + count + '</div>', 60, 200, function(){
                                            postFunc();
                                        }, function(){
                                            store.setValue(item, 'status', buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PREPARING);
                                        });
                                    }
                                    pb.hide();
                                },
                                error: function(error) {
                                    pb.hide();
                                }
                            });
                        });
                    }

                }
                else
                {
                    postFunc();
                }
            }

            this.inherited(arguments);
        },
        addRow: function(){
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            if(this.selection.selectedIndex > -1){
                var rowIndex = this.selection.selectedIndex;
                var self = this,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.addingRow+'. '+nls.pleaseWait+'...'
                    }),
                    store = this.store,
                    currentSelectedItem = this.getItem(rowIndex),
                    content;

                if(!isNaN(parseInt(currentSelectedItem.id[0]))){
                    content = { current_selected_item_id: currentSelectedItem.id, _csrf_token:currentSelectedItem._csrf_token,type:self.type};
                }else{
                    var prevItemId = (rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                    content = { id: currentSelectedItem.id, prev_item_id: prevItemId, relation_id: currentSelectedItem.relation_id, type:self.type, _csrf_token:currentSelectedItem._csrf_token }
                }

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: self.addUrl,
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
                            pb.hide();
                        },
                        error: function(error) {
                            self.selection.clear();
                            pb.hide();
                        }
                    });
                });
            }
        },
        deleteRow: function(){
            var rowIndex = this.selection.selectedIndex,
                item = this.getItem(rowIndex);

            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            if(this.selection.selectedIndex > -1 && item && !isNaN(parseInt(item.id[0]))) {
                var self = this, title = null, msg = null,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.deleting+'. '+nls.pleaseWait+'...'
                    });

                switch(parseInt(self.type))
                {
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT:
                         title = 'Delete Advance Payment Item';
                         msg   = 'Are you sure you want to delete this Advance Payment Item ?';
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                        title = 'Delete Material On Site Item';
                         msg   = 'Are you sure you want to delete this Material On Site Item ?';
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT:
                        title = 'Delete Deposit Item';
                         msg   = 'Are you sure you want to delete this Deposit Item ?';
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF:
                        title = 'Delete Purchase On Behalf Item';
                         msg   = 'Are you sure you want to delete this Purchase On Behalf Item ?';
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT:
                        title = 'Delete Permit Item';
                         msg   = 'Are you sure you want to delete this Permit Item ?';
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG:
                        title = 'Delete Kong Si Kong Item';
                         msg   = 'Are you sure you want to delete this Kong Si Kong Item ?';
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF:
                        title = 'Delete Work On Behalf Item';
                         msg   = 'Are you sure you want to delete this Work On Behalf Item ?';
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE:
                        title = 'Delete Work On Behalf Backcharge Item';
                         msg   = 'Are you sure you want to delete this WOrk On Behalf Backcharge Item ?';
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY:
                        title = 'Delete Penalty Item';
                         msg   = 'Are you sure you want to delete this Penalty Item ?';
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT:
                        title = 'Delete Water Deposit Item';
                         msg   = 'Are you sure you want to delete this Water Deposit Item ?';
                        break;
                    default:
                        break;
                }

                new buildspace.dialog.confirm(title, msg, 90, 380, function() {

                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: self.deleteUrl,
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
                                }
                                pb.hide();
                                self.selection.clear();

                                window.setTimeout(function() {
                                    self.focus.setFocusIndex(rowIndex, 0);
                                }, 10);
                            },
                            error: function(error) {
                                self.selection.clear();
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

            if(item && !isNaN(parseInt(String(item.id))) && this.itemLevel){

                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.addRow,
                    iconClass:"icon-16-container icon-16-add",
                    onClick: dojo.hitch(this,'addRow')
                }));

                if(item && !isNaN(parseInt(String(item.id)))){
                    this.rowCtxMenu.addChild(new dijit.MenuItem({
                        label: nls.deleteRow,
                        iconClass:"icon-16-container icon-16-delete",
                        onClick: dojo.hitch(this,'deleteRow')
                    }));
                }

                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.cut,
                    iconClass:"icon-16-container icon-16-cut",
                    onClick: dojo.hitch(this,'cutItems')
                }));

                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.paste,
                    iconClass:"icon-16-container icon-16-paste",
                    onClick: dojo.hitch(this,'pasteItem'),
                    disabled: this.selectedItem ? false: true
                }));

                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.indent,
                    iconClass:"icon-16-container icon-16-indent",
                    onClick: dojo.hitch(this,'indentOutdent', 'indent')
                }));
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.outdent,
                    iconClass:"icon-16-container icon-16-outdent",
                    onClick: dojo.hitch(this,'indentOutdent', 'outdent')
                }));
            }

        },
        cutItems: function(){
            this.selectedItem = this.selection.getFirstSelected();
        },
        pasteItem: function(){
            if(this.selectedItem && this.selection.selectedIndex > -1) {
                var self = this,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.pleaseWait+'...'
                    }),
                    store = this.store,
                    rowIndex = this.selection.selectedIndex,
                    targetItem = this.selection.getFirstSelected(),
                    prevItemId = (isNaN(parseInt(targetItem.id[0])) && rowIndex > 0) ? this.getItem(rowIndex-1).id : 0;
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: self.pasteUrl,
                        content: {
                            type: "cut",
                            target_id: targetItem.id,
                            prev_item_id: prevItemId,
                            id: self.selectedItem.id,
                            _csrf_token: self.selectedItem._csrf_token
                        },
                        handleAs: 'json',
                        load: function(resp) {
                            var rowsToMove = [];
                            if(resp.success){
                                var children = resp.c;
                                store.fetchItemByIdentity({ 'identity' : resp.data.id,  onItem : function(item){
                                    var firstRowIdx = self.getItemIndex(item);
                                    rowsToMove.push(firstRowIdx);
                                    for(var x=0, len=children.length; x<len; ++x){
                                        store.fetchItemByIdentity({ 'identity' : children[x].id,  onItem : function(child){
                                            var itemIdx = self.getItemIndex(child);
                                            rowsToMove.push(itemIdx);
                                        }});
                                    }
                                    if(rowsToMove.length > 0){
                                        self.rearranger.moveRows(rowsToMove, rowIndex);
                                    }
                                    var selectRowIndex = (firstRowIdx > rowIndex) ? rowIndex : rowIndex - 1;
                                    self.selectAfterPaste(selectRowIndex, true);
                                }});
                                store.fetchItemByIdentity({ 'identity' : resp.data.id,  onItem : function(item){
                                    for(var property in resp.data){
                                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                            store.setValue(item, property, resp.data[property]);
                                        }
                                    }
                                    store.save();
                                    for(var x=0, len=children.length; x<len; ++x){
                                        store.fetchItemByIdentity({ 'identity' : children[x].id,  onItem : function(child){
                                            for(var property in children[x]){
                                                if(child.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                                    store.setValue(child, property, children[x][property]);
                                                }
                                            }
                                            store.save();
                                        }});
                                    }
                                }});
                            }
                            rowsToMove.length = 0;
                            pb.hide();
                        },
                        error: function(error) {
                            self.selection.clear();
                            self.selectedItem = null;
                            pb.hide();
                        }
                    });
                });
            }
        },
        selectAfterPaste: function (rowIndex, scroll){
            this.selection.clear();
            this.selectedItem = null;
            this.selection.setSelected(rowIndex, true);
            if(scroll){
                this.scrollToRow(((rowIndex - 3) > 0) ? rowIndex - 3 : rowIndex);
            }
        },
        indentOutdent: function(type){
            var rowIndex = this.selection.selectedIndex,
                item = this.getItem(rowIndex);
            if(this.selection.selectedIndex > 0 && item && !isNaN(parseInt(item.id[0]))) {
                var self = this,
                    store = this.store,
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.recalculateRows+'. '+nls.pleaseWait+'...'
                    });
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: self[type+'Url'],
                        content: { id: item.id, _csrf_token: item._csrf_token },
                        handleAs: 'json',
                        load: function(data) {
                            if(data.success){
                                var nextItems = data.c;
                                for(var property in data.item){
                                    if(data.item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(item, property, data.item[property]);
                                    }
                                }
                                for(var x=0, len=nextItems.length; x<len; ++x){
                                    store.fetchItemByIdentity({ 'identity' : nextItems[x].id,  onItem :  function (nextItem) {
                                        for(var property in nextItems[x]){
                                            if(nextItem.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                                store.setValue(nextItem, property, nextItems[x][property]);
                                            }
                                        }
                                    }});
                                }
                                store.save();
                            }
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
        disableToolbarButtons: function(isDisable){
            var id;

            switch(parseInt(this.type)){
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT:
                    id = 'advancePayment-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                    id = 'materialOnSite-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT:
                    id = 'deposit-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF:
                    id = 'purchaseOnBehalf-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT:
                    id = 'permit-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG:
                    id = 'kongSiKong-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF:
                    id = 'workOnBehalf-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE:
                    id = 'workOnBehalfBackcharge-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY:
                    id = 'penalty-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT:
                    id = 'waterDeposit-'+this.project.id;
                    break;
                default:
                    break;
            }

            var addRowBtn = dijit.byId(id + 'AddRow-button'),
                deleteRowBtn = dijit.byId(id + 'DeleteRow-button'),
                indentRowBtn = dijit.byId(id + 'IndentRow-button'),
                outdentRowBtn = dijit.byId(id + 'OutdentRow-button');

            if(addRowBtn){
                addRowBtn._setDisabledAttr(isDisable);
            }

            if(deleteRowBtn){
                deleteRowBtn._setDisabledAttr(isDisable);
            }

            if(indentRowBtn){
                indentRowBtn._setDisabledAttr(isDisable);
            }

            if(outdentRowBtn){
                outdentRowBtn._setDisabledAttr(isDisable);
            }
        },
        reload: function(){
            this.store.close();
            this._refresh();
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        },
        duplicateRowCallback: function () {
            var self = this;
            var item = self.selection.getFirstSelected();
            
            if (!item) return;
            
            if (isNaN(item.id)) return;
            
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'postContractClaimMaterialOnSite/duplicateRow/pid/' + self.project.id,
                    content: {
                        id: parseInt(item.id),
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if (resp.success) {
                            self.reload();
                        }

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        }
    });

    return declare('buildspace.apps.PostContract.PostContractClaim.PostContractClaimGrid', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        project: null,
        postContractClaim: null,
        postContractClaimItemContainer: null,
        postContractClaimFirstLevelContainer: null,
        gridOpts: {},
        itemLevel: null,
        type: null,
        locked: false,
        pageId: 0,
        hideMosToolbar: null,
        postCreate: function(){
            var id, stackContainerId;
            var self = this;
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                type: this.type,
                project: this.project,
                itemLevel: this.itemLevel,
                postContractClaimItemContainer: this.postContractClaimItemContainer,
                postContractClaimFirstLevelContainer: this.postContractClaimFirstLevelContainer,
                locked: this.locked,
            });

            var grid = this.grid = new PostContractClaimGrid(this.gridOpts);

            switch(parseInt(this.type)){
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT:
                    id = 'advancePayment-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                    id = 'materialOnSite-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT:
                    id = 'deposit-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF:
                    id = 'purchaseOnBehalf-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT:
                    id = 'permit-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG:
                    id = 'kongSiKong-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF:
                    id = 'workOnBehalf-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE:
                    id = 'workOnBehalfBackcharge-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY:
                    id = 'penalty-'+this.project.id;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT:
                    id = 'waterDeposit-'+this.project.id;
                    break;
                default:
                    break;
            }

            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

            if((this.itemLevel) && (!this.locked))
            {
                toolbar.addChild(
                    new dijit.form.Button({
                        id: id+'items-AddRow-button',
                        label: nls.addRow,
                        iconClass: "icon-16-container icon-16-add",
                        disabled: false,
                        onClick: dojo.hitch(grid, "addRow")
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: id+'items-DeleteRow-button',
                        label: nls.deleteRow,
                        iconClass: "icon-16-container icon-16-delete",
                        disabled: false,
                        onClick: dojo.hitch(grid, "deleteRow")
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: id+'IndentRow-button',
                        label: nls.indent,
                        iconClass: "icon-16-container icon-16-indent",
                        disabled: grid.selection.selectedIndex < 0,
                        onClick: dojo.hitch(grid, "indentOutdent", 'indent')
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: id+'OutdentRow-button',
                        label: nls.outdent,
                        iconClass: "icon-16-container icon-16-outdent",
                        disabled: grid.selection.selectedIndex < 0,
                        onClick: dojo.hitch(grid, "indentOutdent", 'outdent')
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: id+'items-ReloadGridRow-button',
                        label: nls.reload,
                        iconClass: "icon-16-container icon-16-reload",
                        onClick: function(e){
                            grid.reload();
                        }
                    })
                );
            }
            else if((! this.itemLevel) && (!this.locked))
            {
                toolbar.addChild(
                    new dijit.form.Button({
                        id: id+'AddRow-button',
                        label: nls.addRow,
                        iconClass: "icon-16-container icon-16-add",
                        disabled: false,
                        onClick: dojo.hitch(grid, "addRow")
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: id+'DeleteRow-button',
                        label: nls.deleteRow,
                        iconClass: "icon-16-container icon-16-delete",
                        disabled: false,
                        onClick: dojo.hitch(grid, "deleteRow")
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: id+'ReloadGridRow-button',
                        label: nls.reload,
                        iconClass: "icon-16-container icon-16-reload",
                        onClick: function(e){
                            grid.reload();
                        }
                    })
                );
            }

            if(parseInt(this.type) == buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE) {
                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.importedMaterialOnSite,
                        iconClass: "icon-16-container icon-16-contrast",
                        onClick: dojo.hitch(self, "createImportedMaterialOnSiteContainer")
                    })
                );

                if (!self.hideMosToolbar) {
                    toolbar.addChild(new dijit.ToolbarSeparator());

                    toolbar.addChild(
                        new dijit.form.Button({
                            label: nls.duplicate,
                            iconClass: "icon-16-container icon-16-copy",
                            onClick: dojo.hitch(self.grid, self.grid.duplicateRowCallback)
                        })
                    );
                }

            }

            this.addChild(toolbar);

            this.addChild(grid);
        },
        createImportedMaterialOnSiteContainer: function(){
            var widget = dijit.byId('importedMaterialOnSiteContainer');
            var importedBreakdownContainer;

            var importedBreakdownContainerId = 'importedMaterialOnSiteContainer-materialOnSiteListingGrid';
            importedBreakdownContainer = dijit.byId(importedBreakdownContainerId);
            if(importedBreakdownContainer)
            {
                widget.removeChild(importedBreakdownContainer);
                importedBreakdownContainer.destroy();
            }

            importedBreakdownContainer = new ImportedMaterialOnSiteContainer({
                id: importedBreakdownContainerId,
                title: nls.importedMaterialOnSite,
                region: 'bottom',
                parentContainer: widget,
                project: this.project,
                claimCertificate: this.claimCertificate
            });

            widget.addChild(importedBreakdownContainer);
        },
    });
});
