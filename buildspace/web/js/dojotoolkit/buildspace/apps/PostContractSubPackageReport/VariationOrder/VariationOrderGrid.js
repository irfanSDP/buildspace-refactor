define('buildspace/apps/PostContractSubPackageReport/VariationOrder/VariationOrderGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/html',
    'dojo/aspect',
    'dojo/request',
    'dojo/_base/connect',
    "dojo/number",
    "dojo/dom-style",
    "dijit/focus",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/TooltipDialog",
    "dijit/popup",
    'dojox/grid/EnhancedGrid',
    "dijit/form/DropDownButton",
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    'dijit/PopupMenuItem',
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    './PrintAllSelectedVODialog',
    './PrintAllVOWithClaims',
    './PrintSelectedVOItemsDialog',
    './PrintAllVOItemsWithClaimDialog',
    './PrintSelectedVOItemsWithBuildUpQtyDialog',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, lang, html, aspect, request, connect, number, domStyle, focusUtil, evt, keys, TooltipDialog, popup, EnhancedGrid, DropDownButton, DropDownMenu, MenuItem, PopupMenuItem, Rearrange, FormulatedColumn, IndirectSelection, PrintAllSelectedVODialog, PrintAllVOWithClaims, PrintSelectedVOItemsDialog, PrintAllVOItemsWithClaimDialog, PrintSelectedVOItemsWithBuildUpQtyDialog, GridFormatter, nls) {

    var VariationOrderGrid = declare('buildspace.apps.PostContractSubPackageReport.VariationOrder.VariationOrderEnhancedGrid', EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        selectedItem: null,
        region: 'center',
        subPackage: null,
        locked: false,
        constructor:function(args){
            this.connects = [];
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});

            this.plugins = {indirectSelection: {headerSelector:true, width:"40px", styles:"text-align: center;"}};
        },
        postCreate: function() {
            var self = this, store, myTooltipDialog;
            self.inherited(arguments);

            if ( self.type === 'vo' ) {
                store = self.gridContainer.voSelectedStore;
            } else {
                store = self.gridContainer.voItemSelectedStore;
            }

            aspect.after(self, "_onFetchComplete", function() {
                self.gridContainer.markedCheckBoxObject(self, store);
            });

            this._connects.push(connect.connect(this, 'onCellMouseOver', function(e){
                var item = self.getItem(e.rowIndex),
                    colField = e.cell.field,
                    rowIndex = e.rowIndex;

                var fieldConstantName = colField.replace("-value", "");

                // will show tooltip for formula, if available
                if (typeof item[fieldConstantName+'-has_formula'] === 'undefined' || ! item[fieldConstantName+'-has_formula'][0] ) {
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

            this._connects.push(connect.connect(this, 'onCellMouseOut', function(e){
                if(myTooltipDialog !== undefined){
                    popup.close(myTooltipDialog);
                }
            }));
        },
        startup: function() {
            var self = this;
            self.inherited(arguments);

            this._connects.push(connect.connect(this, 'onCellClick', function(e) {
                if (e.cell.name !== "") {
                    return;
                }

                self.singleCheckBoxSelection(e);
            }));

            this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue) {
                self.toggleAllSelection(newValue);
            }));
        },
        canSort: function(inSortInfo){
            return false;
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
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.PostContractSubPackageReport.VariationOrder.VariationOrderGrid', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        subPackage: null,
        variationOrder: null,
        gridOpts: {},
        locked: false,
        type: null,
        pageId: 0,
        postCreate: function(){
            var self = this, id, stackContainerId;
            this.inherited(arguments);
            lang.mixin(self.gridOpts, {type: self.type, subPackage: self.subPackage, variationOrder: self.variationOrder, locked: self.locked });

            var menu = new DropDownMenu({ style: "display: none;"});

            var grid = this.grid = new VariationOrderGrid(self.gridOpts);
            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

            switch(this.type){
                case 'vo':
                    id = 'variationOrder-'+self.subPackage.id;
                    stackContainerId = 'SP_variationOrder-'+self.subPackage.id;
                    break;
                case 'vo-items':
                    id = 'variationOrder-'+self.subPackage.id+'_'+self.variationOrder.id+'-items';
                    stackContainerId = 'SP_variationOrderItems-'+self.subPackage.id+'_'+self.variationOrder.id;
                    break;
                case 'vo-claims':
                    id = 'variationOrder-'+self.subPackage.id+'_'+self.variationOrder.id+'-claims';
                    break;
                default:
                    throw new Error("type must be set!");
                    break;
            }

            var sortOptions = ['voSummary', 'voSummaryWithClaims', 'voSelectedItems', 'voItemsWithClaim', 'voItemsWithBuildUpQty'];

            dojo.forEach(sortOptions, function(opt) {
                var printPreviewMethod;

                switch(opt) {
                    case 'voSummary':
                        printPreviewMethod = 'openPrintAllSelectedVODialog';
                        break;

                    case 'voSummaryWithClaims':
                        printPreviewMethod = 'openPrintAllVOWithClaims';
                        break;

                    case 'voSelectedItems':
                        printPreviewMethod = 'openPrintAllSelectedVOItems';
                        break;

                    case 'voItemsWithClaim':
                        printPreviewMethod = 'openPrintAllVOItemsWithClaim';
                        break;

                    case 'voItemsWithBuildUpQty':
                        printPreviewMethod = 'openPrintSelectedVOItemsWithBuildUpQty';
                        break;

                }

                menu.addChild(new MenuItem({
                    label: nls[opt],
                    onClick: function() {
                        self[printPreviewMethod](opt);
                    }
                }));
            });

            toolbar.addChild(
                new DropDownButton({
                    label: nls.printPreview,
                    iconClass: "icon-16-container icon-16-print",
                    dropDown: menu
                })
            );

            this.addChild(toolbar);
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            if(this.type !== 'vo-claims'){
                var container = dijit.byId(stackContainerId+'-stackContainer');
                if(container){
                    var node = document.createElement("div");
                    var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId, executeScripts: true },node );
                    container.addChild(child);
                    child.set('content', self);
                    lang.mixin(child, {grid: grid});
                    container.selectChild(self.pageId);
                }
            }
        },
        openPrintAllSelectedVODialog: function() {
            var self = this,
                selectedVOStore = self.gridOpts.gridContainer.voSelectedStore,
                vos = [];

            selectedVOStore.query().forEach(function(item) {
                vos.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractSubPackageVO/getPrintingSelectedVO', {
                handleAs: 'json',
                data: {
                    subPackageId: self.subPackage.id,
                    vo_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(vos))
                }
            }).then(function(data) {
                var dialog = new PrintAllSelectedVODialog({
                    subPackage: self.subPackage,
                    subPackageId: self.subPackage.id,
                    title: nls.voSummary,
                    data: data,
                    selectedItems: vos
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintAllVOWithClaims: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractSubPackageVO/getPrintingVOWithClaims', {
                handleAs: 'json',
                data: {
                    subPackageId: self.subPackage.id
                }
            }).then(function(data) {
                var dialog = new PrintAllVOWithClaims({
                    subPackage: self.subPackage,
                    subPackageId: self.subPackage.id,
                    title: nls.voSummaryWithClaims,
                    data: data
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintAllSelectedVOItems: function() {
            var self = this,
                selectedItemStore = self.gridOpts.gridContainer.voItemSelectedStore,
                items = [];

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractSubPackageVO/getPrintingSelectedVOItemsDialog', {
                handleAs: 'json',
                data: {
                    subPackageId: self.subPackage.id,
                    item_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedVOItemsDialog({
                    subPackage: self.subPackage,
                    subPackageId: self.subPackage.id,
                    title: nls.voSelectedItems,
                    data: data,
                    selectedItems: items
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintAllVOItemsWithClaim: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractSubPackageVO/getPrintingVOItemsWithClaim', {
                handleAs: 'json',
                data: {
                    subPackageId: self.subPackage.id
                }
            }).then(function(data) {
                var dialog = new PrintAllVOItemsWithClaimDialog({
                    subPackage: self.subPackage,
                    subPackageId: self.subPackage.id,
                    title: nls.voItemsWithClaim,
                    data: data
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintSelectedVOItemsWithBuildUpQty: function() {
            var self = this,
                selectedItemStore = self.gridOpts.gridContainer.voItemSelectedStore,
                items = [];

            selectedItemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('postContractSubPackageVO/getPrintingSelectedVOItemsWithBuildUpQty', {
                handleAs: 'json',
                data: {
                    subPackageId: self.subPackage.id,
                    item_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedVOItemsWithBuildUpQtyDialog({
                    subPackage: self.subPackage,
                    subPackageId: self.subPackage.id,
                    title: nls.voItemsWithBuildUpQty,
                    data: data,
                    selectedItems: items
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        }
    });
});