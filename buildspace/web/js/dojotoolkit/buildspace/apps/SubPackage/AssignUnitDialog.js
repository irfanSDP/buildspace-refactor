define('buildspace/apps/SubPackage/AssignUnitDialog',[
    'dojo/_base/declare',
    'dojo/aspect',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/layout/ContentPane",
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/SubPackages'
], function(declare, aspect, lang, connect, when, html, dom, keys, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ContentPane, EnhancedGrid, GridFormatter, IndirectSelection, nls){

    var Grid = declare('buildspace.apps.SubPackage.AssignUnitGrid', EnhancedGrid, {
        subPackage: null,
        rootProject: null,
        subPackageGrid: null,
        assignContractorGrid: null,
        bill: null,
        style: "border:none;",
        region: 'center',
        pageId: null,
        dialogObj: null,
        gridType: null,
        keepSelection: true,
        rowSelector: '0px',
        initialLoadSelector: false,
        constructor:function(args){
            if(args.gridType != 'bill'){
                this.counterIds = [];
                this.connects = [];
                this.urlGetDescendantIds = 'subPackage/getTypeUnitDescendants';
                this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align:center;"}};
            }

            this.inherited(arguments);
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            if(this.gridType == 'tree'){
                aspect.after(this, "_onFetchComplete", function() {
                    if ( ! self.initialLoadSelector ) {
                        // use to mark only initiate checkbox auto selection if current status the initial
                        // rendering grid's process
                        self.initialLoadSelector = true;

                        this.store.fetch({query: {selected:true}, queryOptions: {ignoreCase: true}, onComplete: this.markSelectedCheckBoxes, scope: this});
                    }
                });

                this._connects.push(connect.connect(this, 'onCellClick', function(e){
                    self.selectTree(e);
                }));
                this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue){
                    self.toggleAllSelection(newValue);
                }));
            }
        },
        markSelectedCheckBoxes: function(items, request){
            for(var i = 0; i < items.length; i++){
                var itemIndex = items[i]._0;
                this.pushItemIdIntoGridArray(items[i], true);
                this.rowSelectCell.toggleRow(itemIndex, true);
            }
        },
        selectTree: function(e){
            var rowIndex = e.rowIndex,
                newValue = this.selection.selected[rowIndex],
                item = this.getItem(rowIndex), self = this, store = this.store;

            if(item.level == 0){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });
                pb.show();
                var itemIndex = -1;
                dojo.xhrGet({
                    url: this.urlGetDescendantIds,
                    content: {id: item.id},
                    handleAs: 'json',
                    load: function(data) {
                        dojo.forEach(data.items, function(itm){
                            store.fetchItemByIdentity({ 'identity' : itm.id,
                                onItem : function(node){
                                    if(node){
                                        itemIndex = node._0;
                                        if(node.level == 1){
                                            self.pushItemIdIntoGridArray(node, newValue);
                                        }
                                        self.selection[newValue ? 'addToSelection' : 'deselect'](itemIndex);
                                    }
                                }
                            });
                        });
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            }else if(item.level == 1){
                this.pushItemIdIntoGridArray(item, newValue);
            }
        },
        pushItemIdIntoGridArray: function(item, select){
            var grid = this;
            var idx = dojo.indexOf(grid.counterIds, item.id[0]);
            if(select){
                if(idx == -1){
                    if(item.count[0] != -1){
                        grid.counterIds.push(item.id[0]);
                    }
                }
            }else{
                if(idx != -1){
                    grid.counterIds.splice(idx, 1);
                }
            }
        },
        toggleAllSelection:function(checked){
            var grid = this, selection = grid.selection;
            if(checked){
                selection.selectRange(0, grid.rowCount-1);
                grid.counterIds = [];
                grid.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item.count > 0){
                                grid.counterIds.push(item.id[0]);
                            }
                        });
                    }
                });
            }else{
                selection.deselectAll();
                grid.counterIds = [];
            }
        },
        canSort: function(inSortInfo){
            return false;
        },
        reload: function(){
            this.store.close();
            this._refresh();
        },
        assignUnit: function(){
            var self = this,
                counterIds = this.counterIds,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.importing+'. '+nls.pleaseWait+'...'
                });

            pb.show();
            dojo.xhrPost({
                url: 'subPackage/assignUnits',
                content: { id: this.subPackage.id, bid: this.bill.id, ids: [counterIds], _csrf_token: this.subPackage._csrf_token },
                handleAs: 'json',
                load: function(data) {
                    self.assignContractorGrid.store.close();
                    self.assignContractorGrid.sort();

                    self.subPackageGrid.store.save(); //in case it is dirty
                    self.subPackageGrid.store.close();
                    self.subPackageGrid.sort();

                    var pushToPostContractBtn = dijit.byId('SubPackages-'+self.rootProject.id+'-PushToPostContractRow-button');

                    if(pushToPostContractBtn && self.rootProject.status_id[0] == buildspace.constants.STATUS_POSTCONTRACT){
                        pushToPostContractBtn._setDisabledAttr(false);
                    }

                    pb.hide();
                },
                error: function(error) {
                    self.counterIds = [];
                    pb.hide();
                    self.dialogObj.hide();
                }
            });
        }
    });

    var AssignUnitGridContainer = declare('buildspace.apps.SubPackage.AssignUnitGridContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;border:0px;width:100%;height:100%;",
        gutters: false,
        region: 'center',
        url: null,
        subPackage: null,
        rootProject: null,
        subPackageGrid: null,
        assignContractorGrid: null,
        bill: null,
        disableEditing: false,
        gridOpts: {},
        gridType: null,
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {
                gridType: this.gridType,
                bill: this.bill,
                subPackage: this.subPackage,
                rootProject: this.rootProject,
                subPackageGrid: this.subPackageGrid,
                assignContractorGrid: this.assignContractorGrid,
                disableEditing: this.disableEditing,
                region: "center"
            });

            var grid = this.grid = new Grid(this.gridOpts);

            if(this.gridType == 'tree'){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'SubPackages-'+this.subPackage.id+'-ApplyRow-button',
                        label: nls.assignTypesAndUnits,
                        iconClass: "icon-16-container icon-16-save",
                        onClick: function(){
                            grid.assignUnit();
                        }
                    })
                );

                this.addChild(toolbar);
            }

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('SubPackage-assign_unit_'+this.subPackage.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(this.stackContainerTitle, 45), id: this.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', this);
                container.selectChild(this.pageId);
            }
        }
    });

    return declare('buildspace.apps.SubPackage.AssignUnitDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: null,
        disableEditing: false,
        rootProject: null,
        subPackage: null,
        assignContractorGrid: null,
        subPackageGrid: null,
        url: null,
        buildRendering: function(){
            var form = this.createForm();
            form.startup();
            this.content = form;

            this.title = nls.assignTypesAndUnits;

            this.inherited(arguments);
        },
        postCreate: function(){
            this.title = "lalalal";
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
        createForm: function(){
            var self = this,
                formatter = new GridFormatter(),
                borderContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:780px;height:350px;",
                    gutters: false
                }),
                store = dojo.data.ItemFileWriteStore({
                    url: 'subPackage/getBillsToAssignUnit/spid/'+ self.subPackage.id,
                    clearOnClose: true
                });

            var gridContainer = this.makeGridContainer(AssignUnitGridContainer({
                pageId: 'SubPackageAssignUnit-page_bill-' + this.subPackage.id,
                id: 'SubPackageAssignUnit-page_bill-' + this.subPackage.id,
                stackContainerTitle: this.subPackage.name,
                subPackage: this.subPackage,
                disableEditing: this.disableEditing,
                gridType: 'bill',
                gridOpts: {
                    dialogObj: this,
                    structure: [{
                        name: 'No.',
                        field: 'count',
                        width:'30px',
                        styles:'text-align:center;',
                        formatter: formatter.rowCountCellFormatter
                    },{
                        name: nls.title,
                        field: 'title',
                        width:'auto'
                    },{
                        name: nls.selectedUnits,
                        field: 'selected_units',
                        styles: 'text-align: right;',
                        width:'120px'
                    }],
                    store: store,
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id > 0){
                            self.createTypeItemGrid(_item);
                        }
                    }
                }
            }), this.subPackage.name);

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        makeGridContainer: function(content, title){
            var self = this;
            var id = this.subPackage.id;
            var stackContainer = dijit.byId('SubPackage-push_to_post_contract_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('SubPackage-assign_unit_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'SubPackage-assign_unit_'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: buildspace.truncateString(title, 45),
                content: content
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'SubPackage-assign_unit_'+id+'-stackContainer'
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            }));

            dojo.subscribe('SubPackage-assign_unit_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('SubPackage-assign_unit_'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page.id));
                    var pageIndex = 0,
                        childLength = children.length;

                    pageIndex = index = index + 1;

                    while(children.length > index) {
                        widget.removeChild(children[index]);
                        children[index].destroyRecursive(true);
                        index = index + 1;
                    }

                    //We always assume element grid index is 0
                    if (pageIndex == 1 && childLength > (pageIndex))
                    {
                        // based on solution from http://reuben-in-rl.blogspot.com/2012/01/refreshing-dojo-datagrid.html
                        var billGridContainer = dijit.byId('SubPackageAssignUnit-page_bill-' + self.subPackage.id);

                        if (billGridContainer)
                        {
                            billGridContainer.grid.store.save();
                            billGridContainer.grid.store.close();

                            var handle = aspect.after(billGridContainer.grid, "_onFetchComplete", function() {
                                handle.remove();
                                this.scrollToRow(this.selection.selectedIndex);
                            });

                            billGridContainer.grid.sort();
                        }
                    }
                }
            });

            return borderContainer;
        },
        createTypeItemGrid: function(bill){
            var formatter = new GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    url: 'subPackage/getUnitList/bid/' + bill.id + '/sid/' + this.subPackage.id
                });

            AssignUnitGridContainer({
                pageId: 'SubPackageAssignUnit-page_item-' + bill.id,
                stackContainerTitle: bill.title,
                subPackage: this.subPackage,
                rootProject: this.rootProject,
                subPackageGrid: this.subPackageGrid,
                assignContractorGrid: this.assignContractorGrid,
                disableEditing: this.disableEditing,
                bill: bill,
                gridType: 'tree',
                gridOpts: {
                    dialogObj: this,
                    structure: [{
                        name: 'No.',
                        field: 'count',
                        width:'30px',
                        styles:'text-align:center;',
                        formatter: formatter.rowCountCellFormatter
                    }, {
                        name: nls.description,
                        field: 'description',
                        width:'auto',
                        formatter: formatter.typeListTreeCellFormatter
                    }],
                    store: store
                }
            });
        }
    });
});