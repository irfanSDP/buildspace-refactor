define('buildspace/apps/Editor/TenderAlternativeListDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/on",
    "dijit/form/Form",
    "dojox/form/manager/_Mixin",
    "dojox/form/manager/_NodeMixin",
    "dojox/form/manager/_ValueMixin",
    "dojox/form/manager/_DisplayMixin",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/ValidationTextBox",
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    'buildspace/widget/grid/Filter',
    'buildspace/apps/PageGenerator/GeneratorDialog',
    'buildspace/apps/Editor/PrintFinalBQDialog',
    'dojo/i18n!buildspace/nls/TenderAlternative'
], function(declare, lang, connect, keys, domStyle, on, Form, _ManagerMixin, _ManagerNodeMixin, _ManagerValueMixin, _ManagerDisplayMixin, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ValidationTextBox, EnhancedGrid, GridFormatter, Filter, GeneratorDialog, PrintFinalBQDialog, nls){

    var Grid = declare('buildspace.apps.Editor.TenderAlternativeListGrid', EnhancedGrid, {
        style: "border-top:none;",
        containerDialog: null,
        project: null,
        workArea: null,
        type: 'printPdf',
        opt: null,
        region: 'center',
        postCreate: function(){
            this.inherited(arguments);
            var project = this.project;
            var opt = this.opt;
            var self = this;

            this.on("RowClick", function(e){
                var colField = e.cell.field,
                    rowIndex = e.rowIndex,
                     _item = this.getItem(rowIndex);

                if (_item && parseInt(String(_item.id)) > 0) {
                    switch(self.type){
                        case 'printProjectSummary':
                            self.printProjectSummaryPdf(_item, opt);
                            break;
                        case 'printFinalBQ':
                            self.openPrintFinalBQ(_item, opt);
                            break;
                    }
                }
            }, true);
        },
        canSort: function(inSortInfo){
            return false;
        },
        printProjectSummaryPdf: function(tenderAlternative, opt){
            window.open('tenderAlternativeProjectSummary/'+parseInt(String(tenderAlternative.id))+'/'+String(this.project._csrf_token)+'/'+opt, '_blank');
            return window.focus();
        },
        openPrintFinalBQ: function(tenderAlternative, opt){
            var withPrice = opt.withPrice;
            var t = withPrice ? nls.withPrice : nls.withoutPrice;
            var d = new PrintFinalBQDialog({
                title: nls.printFinalBQ+' ('+t+')',
                project: this.project,
                tenderAlternative: tenderAlternative,
                withPrice: withPrice
            });

            d.show();
        }
    });

    var GridContainer = declare('buildspace.apps.Editor.TenderAlternativeListGridContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        containerDialog: null,
        project: null,
        workArea: null,
        type: 'printPdf',
        opt: null,
        postCreate: function(){
            this.inherited(arguments);

            var formatter = new GridFormatter();
            var Formatter = {
                rowCountCellFormatter: function (cellValue, rowIdx) {
                    return cellValue > 0 ? cellValue : '';
                },
                treeCellFormatter: function (cellValue, rowIdx) {
                    var item = this.grid.getItem(rowIdx);
                    var level = parseInt(String(item.level)) * 16;
                    cellValue = cellValue == null ? '&nbsp' : cellValue;
                    if (parseInt(String(item.type)) < buildspace.constants.TYPE_BILL) {
                        cellValue = '<b>' + cellValue + '</b>';
                    }
                    cellValue = '<div class="treeNode" style="padding-left:' + level + 'px;"><div class="treeContent">' + cellValue + '&nbsp;</div></div>';
                    return cellValue;
                }
            };

            var grid = this.grid = new Grid({
                containerDialog: this.containerDialog,
                project: this.project,
                workArea: this.workArea,
                type: this.type,
                opt: this.opt,
                structure: [{
                    name: 'No.',
                    field: 'count',
                    width: '30px',
                    styles: 'text-align:center;',
                    formatter: Formatter.rowCountCellFormatter
                },{
                    name: nls.description, field: 'title', width: 'auto', formatter: Formatter.treeCellFormatter
                },{
                    name: nls.overallTotal,
                    field: 'overall_total',
                    width: '150px',
                    styles: 'text-align: right;',
                    formatter: formatter.unEditableCurrencyCellFormatter
                }],
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: "getTenderAlternatives/" + parseInt(String(this.project.id))
                })
            });

            this.addChild(grid);

            this.addChild(new Filter({
                region: 'top',
                editableGrid: false,
                grid: grid,
                filterFields: [
                    {'title':nls.description}
                ]
            }));
        },
        save: function(){
            this.grid.save();
        }
    });

    return declare('buildspace.apps.Editor.TenderAlternativeListDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        project: null,
        workArea: null,
        type: 'printPdf',
        opt: null,
        title: nls.tenderAlternatives,
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;

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
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createContent: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:820px;height:380px;",
                gutters: false
            });

            var gridContainer = new GridContainer({
                region: "center",
                containerDialog: this,
                project: this.project,
                workArea: this.workArea,
                type: this.type,
                opt: this.opt
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

            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        }
    });
});