define('buildspace/apps/PostContractReport/BillManager/ApplyToUnitDialog',[
    'dojo/_base/declare',
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
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, lang, connect, when, html, dom, keys, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ContentPane, EnhancedGrid, GridFormatter, IndirectSelection, nls){

    var Grid = declare('buildspace.apps.PostContractReport.BillManager.ApplyGrid', EnhancedGrid, {
            rootProject: null,
            style: "border:none;",
            region: 'center',
            pageId: null,
            billId: null,
            dialogObj: null,
            columnSettingId: null,
            url: null,
            typeItem: null,
            keepSelection: true,
            rowSelector: '0px',
            constructor:function(args)
            {
                var formatter = new GridFormatter();

                this.structure = {
                    noscroll: false,
                    cells: [
                        [{
                            name: 'No.',
                            field: 'count',
                            width:'30px',
                            styles:'text-align:center;',
                            formatter: formatter.rowCountCellFormatter
                        }, {
                            name: nls.description,
                            field: 'description',
                            width:'auto'
                        }]
                    ]
                };

                this.counterIds = [];
                this.connects = [];
                this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align:center;"}};

                this.inherited(arguments);
            },
            postCreate: function(){
                var self = this;
                self.inherited(arguments);

                this._connects.push(connect.connect(this, 'onCellClick', function(e){
                    self.selectTree(e);
                }));
                this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue){
                    self.toggleAllSelection(newValue);
                }));
            },
            selectTree: function(e){
                var rowIndex = e.rowIndex,
                    newValue = this.selection.selected[rowIndex],
                    item = this.getItem(rowIndex);
                if(item){
                    this.pushItemIdIntoGridArray(item, newValue);

                    if(this.counterIds.length > 0)
                    {
                        this.dialogObj.saveBtn.set('disabled', false);
                    }
                    else
                    {
                        this.dialogObj.saveBtn.set('disabled', true);
                    }
                }
            },
            pushItemIdIntoGridArray: function(item, select){
                var grid = this;
                var idx = dojo.indexOf(grid.counterIds, item.count[0]);
                if(select){
                    if(idx == -1){
                        if(item.count[0] != -1)
                        {
                            grid.counterIds.push(item.count[0]);
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
                                    grid.counterIds.push(item.count[0]);
                                }
                            });
                        }
                    });
                }else{
                    selection.deselectAll();
                    grid.counterIds = [];
                }

                if(grid.counterIds.length > 0)
                {
                    grid.dialogObj.saveBtn.set('disabled', false);
                }
                else
                {
                    grid.dialogObj.saveBtn.set('disabled', true);
                }

            },
            canSort: function(inSortInfo){
                return false;
            },
            reload: function(){
                this.store.close();
                this._refresh();
            }
        });

    return declare('buildspace.apps.PostContractReport.BillManager.ApplyDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.applyToOtherUnit,
        rootProject: null,
        typeItem: null,
        billId: null,
        elementId: null,
        url: null,
        buildRendering: function(){
            var form = this.createForm();
            form.startup();
            this.content = form;
            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function(e){
            key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createForm: function(){
            var self = this;

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:580px;height:380px;",
                gutters: false
            });

            var msgBorderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;height:90px;",
                gutters: false,
                region: 'top'
            });

            var store = dojo.data.ItemFileWriteStore({
                url: 'postContractStandardBillClaim/getUnitList/id/' + self.typeItem.relation_id + '/exc_count/' + self.typeItem.count,
                clearOnClose: true
            });

            var grid = new Grid({
                dialogObj: self,
                title: self.title,
                rootProject: self.rootProject,
                store: store,
                columnSettingId: self.typeItem.relation_id,
                typeItem: self.typeItem,
                url: self.url
            });

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

            msgBorderContainer.addChild(toolbar);

            msgBorderContainer.addChild(new ContentPane({
                content: nls.applyNote,
                region: "center",
                style:"padding:20px 30px 20px 30px; text-align:center"
            }));

            borderContainer.addChild(msgBorderContainer);
            borderContainer.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            return borderContainer;
        }
    });
});
