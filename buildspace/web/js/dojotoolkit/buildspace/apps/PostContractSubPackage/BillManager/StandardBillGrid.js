define('buildspace/apps/PostContractSubPackage/BillManager/StandardBillGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dijit/Menu",
    'dojo/number',
    "dijit/CheckedMenuItem",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    "buildspace/widget/grid/cells/Formatter",
    './ApplyToUnitDialog',
    './editBillNoteDialog',
    'dojo/i18n!buildspace/nls/PostContractSubPackage',
    'dojo/on',
    'dojox/grid/_Grid',
    "dojox/widget/PlaceholderMenuItem",
    'buildspace/widget/grid/cells/TextBox'
], function(declare, lang, array, domAttr, Menu, number, MenuCheckedItem, MenuPlugin, Selector, Rearrange, FormulatedColumn, evt, keys, focusUtil, html, xhr, PopupMenuItem, MenuSeparator, GridFormatter, ApplyToUnitDialog, EditBillNoteDialog, nls, on){

    var StandardBillGrid = declare('buildspace.apps.PostContractSubPackage.BillManager.StandardBillGrid', dojox.grid.EnhancedGrid, {
        type: null,
        billId: -1,
        elementId: 0,
        subPackage: null,
        itemId: -1,
        disableEditing: false,
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        keepSelection: true,
        rowSelector: '0px',
        markupSettings: null,
        parentGrid: null,
        elementGridStore: null,
        selectedClaimRevision: null,
        claimRevision: null,
        updateUrl: null,
        typeItem: null,
        project: null,
        constructor:function(args){
            this.type            = args.type;
            this.currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            var formatter = this.formatter = new GridFormatter();

            this.setColumnStructure();
            this.createHeaderCtxMenu();

            this.inherited(arguments);
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
        editableCellDblClick: function(e){
            //do nothing
        },
        setColumnStructure: function(){
            var formatter = this.formatter;

            if(this.type == 'tree'){
                this.structure = {
                    noscroll: false,
                    cells: [
                        [{
                            name: 'No.',
                            field: 'id',
                            width:'30px',
                            styles:'text-align:center;',
                            formatter: formatter.rowCountCellFormatter,
                            rowSpan : 2
                        },{
                            name: nls.billReference,
                            field: 'bill_ref',
                            styles: "text-align:center; color: red;",
                            width: '80px',
                            noresize: true,
                            showInCtxMenu: true,
                            formatter: formatter.billRefCellFormatter,
                            rowSpan:2,
                            hidden: true
                        },{
                            name: nls.description,
                            field: 'description',
                            width:'500px',
                            formatter: formatter.claimTreeCellFormatter,
                            showInCtxMenu: true,
                            rowSpan : 2
                        },{
                            name: nls.type,
                            field: 'type',
                            width:'70px',
                            rowSpan : 2,
                            type: 'dojox.grid.cells.Select',
                            formatter: formatter.typeCellFormatter,
                            styles:'text-align:center;',
                            showInCtxMenu: true,
                            hidden: true
                        },{
                            name: nls.qty,
                            field: 'qty_per_unit',
                            width:'90px',
                            styles:'text-align: right;',
                            formatter: formatter.claimQtyPerUnitCellFormatter,
                            showInCtxMenu: true,
                            noresize: true,
                            rowSpan : 2
                        },{
                            name: nls.unit,
                            field: 'uom_symbol',
                            width:'70px',
                            styles:'text-align: center;',
                            formatter: formatter.unitIdCellFormatter,
                            showInCtxMenu: true,
                            rowSpan : 2
                        },{
                            name: nls.rate,
                            field: 'rate',
                            width:'70px',
                            styles:'text-align: right;',
                            formatter: formatter.claimRateLSCellFormatter,
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.TextBox',
                            showInCtxMenu: true,
                            rowSpan : 2
                        },{
                            name: nls.total,
                            field: 'total_per_unit',
                            width:'90px',
                            styles:'text-align: right;color:blue;',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            showInCtxMenu: true,
                            rowSpan : 2
                        },{
                            name: nls.percent,
                            field: 'prev_percentage',
                            width:'70px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.unEditablePercentageCellFormatter,
                            styles:'text-align: right;'
                        },{
                            name: nls.amount,
                            field: 'prev_amount',
                            width:'110px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            styles:'text-align: right;'
                        },{
                            name: nls.percent,
                            field: 'current_percentage',
                            width:'70px',
                            headerClasses: "typeHeader2",
                            styles:'text-align: right;',
                            formatter: formatter.editableClaimPercentageCellFormatter,
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.TextBox'
                        },{
                            name: nls.amount,
                            field: 'current_amount',
                            width:'110px',
                            headerClasses: "typeHeader2",
                            styles:'text-align: right;',
                            formatter: formatter.claimAmountCellFormatter,
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.TextBox'
                        },{
                            name: nls.percent,
                            field: 'up_to_date_percentage',
                            width:'70px',
                            headerClasses: "typeHeader1",
                            styles:'text-align: right;',
                            formatter: formatter.editableClaimPercentageCellFormatter,
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.TextBox'
                        },{
                            name: nls.qty,
                            field: 'up_to_date_qty',
                            width:'90px',
                            headerClasses: "typeHeader1",
                            styles:'text-align: right;',
                            formatter: formatter.claimAmountCellFormatter,
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.TextBox'
                        },{
                            name: nls.amount,
                            field: 'up_to_date_amount',
                            width:'110px',
                            headerClasses: "typeHeader1",
                            styles:'text-align: right;',
                            formatter: formatter.claimAmountCellFormatter,
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.TextBox'
                        }],
                        [{
                            name: nls.previousClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader1",
                            colSpan : 2
                        },{
                            name: nls.currentClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader2",
                            colSpan : 2
                        },{
                            name: nls.upToDateClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader1",
                            colSpan : 3
                        }]
                    ]
                };
            }else{
                this.structure = {
                    noscroll: false,
                    cells: [
                        [{
                            name: 'No.',
                            field: 'id',
                            width:'30px',
                            styles:'text-align:center;',
                            formatter: formatter.rowCountCellFormatter,
                            noresize: true,
                            rowSpan : 2
                        }, {
                            name: nls.description,
                            field: 'description',
                            width:'auto',
                            noresize: true,
                            formatter: formatter.claimTreeCellFormatter,
                            rowSpan : 2
                        },{
                            name: nls.total,
                            field: 'total_per_unit',
                            width:'90px',
                            styles:'text-align: right;color:blue;',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true,
                            rowSpan : 2
                        },{
                            name: nls.percent,
                            field: 'prev_percentage',
                            width:'70px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.unEditablePercentageCellFormatter,
                            styles:'text-align: right;',
                            noresize: true
                        },{
                            name: nls.amount,
                            field: 'prev_amount',
                            width:'110px',
                            headerClasses: "typeHeader1",
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            styles:'text-align: right;',
                            noresize: true
                        },{
                            name: nls.percent,
                            field: 'current_percentage',
                            width:'70px',
                            headerClasses: "typeHeader2",
                            styles:'text-align: right;',
                            formatter: formatter.unEditablePercentageCellFormatter,
                            noresize: true,
                            cellType: 'buildspace.widget.grid.cells.TextBox'
                        },{
                            name: nls.amount,
                            field: 'current_amount',
                            width:'110px',
                            headerClasses: "typeHeader2",
                            styles:'text-align: right;',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true,
                            cellType: 'buildspace.widget.grid.cells.TextBox'
                        },{
                            name: nls.percent,
                            field: 'up_to_date_percentage',
                            width:'70px',
                            headerClasses: "typeHeader1",
                            styles:'text-align: right;',
                            formatter: formatter.elementClaimEditablePercentageCellFormatter,
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.TextBox'
                        },{
                            name: nls.amount,
                            field: 'up_to_date_amount',
                            width:'110px',
                            headerClasses: "typeHeader1",
                            styles:'text-align: right;',
                            formatter: formatter.elementClaimAmountCellFormatter,
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.TextBox'
                        }],
                        [{
                            name: nls.previousClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader1",
                            colSpan : 2,
                            noresize: true
                        },{
                            name: nls.currentClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader2",
                            colSpan : 2,
                            noresize: true
                        },{
                            name: nls.upToDateClaim,
                            styles:'text-align:center;',
                            headerClasses: "staticHeader typeHeader1",
                            colSpan : 2
                        }]
                    ]
                };
            }
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on("RowContextMenu", function(e){
                self.selection.clear();
                self.selection.setSelected(e.rowIndex, true);
                self.contextMenu(e);
            }, true);
        },
        onStyleRow: function(e) {
            this.inherited(arguments);

            if(e.node.children[0]){
                if(e.node.children[0].children[0].rows.length >= 2){
                    var elemToHide = e.node.children[0].children[0].rows[1],
                        childElement = e.node.children[0].children[0].rows[0].children;

                    elemToHide.parentNode.removeChild(elemToHide);

                    dojo.forEach(childElement, function(child, i){
                        var rowSpan = dojo.attr(child, 'rowSpan');

                        if(!rowSpan || rowSpan < 2)
                            dojo.attr(child, 'rowSpan', 2);
                    });
                }
            }
        },
        createHeaderCtxMenu: function(){
            if (typeof this.structure !== 'undefined') {
                var column = this.structure.cells[0],
                    self = this,
                    menusObject = {
                        headerMenu: new dijit.Menu()
                    };
                dojo.forEach(column, function(data, index){
                    if(data.showInCtxMenu) {
                        menusObject.headerMenu.addChild(new dijit.CheckedMenuItem({
                            label: data.name,
                            checked: (typeof data.hidden === 'undefined' || data.hidden === false) ? true : false,
                            onChange: function(val){

                                var show = false;

                                if (val){
                                    show = true;
                                }

                                self.showHideColumn(show, index);
                            }
                        }));
                    }
                });

                this.plugins = {menus: menusObject};
            }
        },
        showHideColumn: function(show, index) {
            this.beginUpdate();
            this.layout.setColumnVisibility(index, show);
            this.endUpdate();
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;

            if(inCell != undefined){
                var item = this.getItem(inRowIndex),
                    field = inCell.field;

                if(self.type != 'tree'){
                    if(item.total_per_unit === undefined){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }
                }else{
                    if(item.total_per_unit === undefined || item.total_per_unit <= 0){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }
                }

                if(item.id == buildspace.constants.GRID_LAST_ROW || (item.include != undefined && item.include[0] != true ) || self.claimRevision.locked_status || (self.claimRevision.id != self.selectedClaimRevision.id) ){
                    window.setTimeout(function() {
                        self.edit.cancel();
                        self.focus.setFocusIndex(inRowIndex, inCell.index);
                    }, 10);
                    return;
                }else if(field == 'rate'){
                    if(item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE ||
                        (number.parse(item.up_to_date_percentage) || number.parse(item.current_percentage) || number.parse(item.prev_percentage))
                    ){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }
                }
            }

            return this._canEdit;
        },
        contextMenu: function(e){
            var rowCtxMenu = this.rowCtxMenu = new dijit.Menu();
            this.contextMenuItems(e);
            var info       = {target: e.target, coords: e.keyCode !== keys.F10 && "pageX" in e ? {x: e.pageX, y: e.pageY } : null};
            var item       = this.getItem(e.rowIndex);

            if(rowCtxMenu && item && (this.selection.isSelected(e.rowIndex) || e.rowNode && html.hasClass(e.rowNode, 'dojoxGridRowbar'))){
                rowCtxMenu._openMyself(info);
                evt.stop(e);
                return;
            }
        },
        contextMenuItems: function(e){
            var item = this.getItem(e.rowIndex);

            if(item.id > 0 && this.type == 'tree'){
                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.editItemNote,
                    iconClass:"icon-16-container icon-16-note",
                    onClick: dojo.hitch(this,'doEditItemNote', item)
                }));

            }else if(item.id > 0 && this.type != 'tree'){
                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.editTradeNote,
                    iconClass:"icon-16-container icon-16-note",
                    onClick: dojo.hitch(this,'doEditTradeNote', item)
                }));
            }
        },
        doEditItemNote: function (item){
            var editItemNoteDialog = new EditBillNoteDialog({
                item: item,
                billGrid: this,
                title: nls.editItemNote,
                updateUrl: 'billManager/itemNoteUpdate'
            });

            editItemNoteDialog.show();
        },
        doEditTradeNote: function (item){
            var editItemNoteDialog = new EditBillNoteDialog({
                item: item,
                billGrid: this,
                title: nls.editTradeNote,
                updateUrl: 'billManager/elementNoteUpdate'
            });

            editItemNoteDialog.show();
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            if(item[inAttrName][0] != undefined || val !== item[inAttrName][0]){

                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    project_id: self.project.id,
                    sub_package_id: self.subPackage.id,
                    type_ref_id: self.typeItem.id,
                    attr_name: inAttrName,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }
                    store.save();
                };

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: url,
                        content: params,
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                if(item.id > 0){
                                    updateCell(resp.item, store);
                                }
                                var cell = self.getCellByField(inAttrName);
                                window.setTimeout(function() {
                                    self.focus.setFocusIndex(rowIdx, cell.index);
                                }, 10);
                                pb.hide();
                            }
                        },
                        error: function(error) {
                            pb.hide();
                            console.log(error);
                        }
                    });
                });
            }

            self.inherited(arguments);
        }
    });

    return declare('buildspace.apps.PostContractSubPackage.BillManager.StandardBillGridBuilder', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: '',
        rootProject: null,
        subPackage: null,
        billId: -1,
        elementId: 0,
        disableEditing: false,
        itemId: -1,
        rowSelector: null,
        typeItem: null,
        gridOpts: {},
        type: null,
        postCreate: function(){
            var self = this;

            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                subPackage: this.subPackage,
                billId: this.billId,
                typeItem: this.typeItem,
                project: this.rootProject,
                elementId: this.elementId,
                type:this.type,
                region:"center",
                disableEditing: this.disableEditing,
                borderContainerWidget: this
            });

            var grid = this.grid = new StandardBillGrid(this.gridOpts);

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            if(this.type != 'tree'){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        id: this.billId+this.billId+'ApplyRow-button',
                        label: nls.applyToOtherUnit,
                        iconClass: "icon-16-container icon-16-add",
                        onClick: function(){
                            var applyDialog = new ApplyToUnitDialog({
                                rootProject : self.rootProject,
                                subPackage: self.subPackage,
                                typeItem: self.typeItem,
                                billId: self.billId,
                                elementId: self.elementId,
                                url: 'subPackagePostContractStandardBillClaim/applyToOtherUnit'
                            });

                            applyDialog.show();
                        }
                    })
                );

                this.addChild(toolbar);
            }

            var container = dijit.byId('subPackagePostContractStandardBill' + this.billId + '-stackContainer');

            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 45),
                    id: this.pageId,
                    content: this,
                    grid: this.grid
                }, node);
                container.addChild(child);
                container.selectChild(this.pageId);
            }
        }
    });
});