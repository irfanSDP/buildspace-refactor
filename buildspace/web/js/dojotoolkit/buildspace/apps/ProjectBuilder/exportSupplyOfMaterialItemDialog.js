define('buildspace/apps/ProjectBuilder/exportSupplyOfMaterialItemDialog', [
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/ValidationTextBox",
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/BillManagerExport',
    'dijit/form/ToggleButton'
], function (declare, lang, connect, when, html, dom, keys, domStyle, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ValidationTextBox, GridFormatter, IndirectSelection, nls, ToggleButton) {

    var ExportBillForm = declare('buildspace.apps.ProjectBuilder.ExportSupplierOfMaterialBillForm', [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin], {
        templateString: '<form data-dojo-attach-point="containerNode">' +
        '<table class="table-form">' +
        '<tr>' +
        '<td class="label" style="width:80px;"><label style="display: inline;"></span>' + nls.downloadAs + ' :</label></td>' +
        '<td>' +
        '<input type="text" name="filename" style="padding:2px;width:220px;" data-dojo-type="dijit/form/ValidationTextBox" data-dojo-props="trim:true, propercase:true, maxlength:45, required: true"> .xlsx' +
        '<input type="hidden" name="eids" value="">' +
        '</td>' +
        '</tr>' +
        '</table>' +
        '</form>',
        bill: null,
        elementIds: {},
        withRate: false,
        region: 'center',
        dialogWidget: null,
        style: "outline:none;padding:0;margin:0;border:none;",
        baseClass: "buildspace-form",
        startup: function () {
            this.inherited(arguments);
            this.setFormValues({
                filename: this.bill.title,
                eids: this.elementIds
            });
        },
        submit: function () {
            if (this.validate()) {
                var values = dojo.formToObject(this.id);
                var filename = values.filename.replace(/ /g, '_');

                buildspace.windowOpen('POST', 'supplyOfMaterialExportFile/exportExcelByElement', {
                    filename: filename,
                    id: this.bill.id[0],
                    eids: values.eids,
                    wr: this.withRate,
                    _csrf_token: this.bill._csrf_token[0]
                });

                if (this.dialogWidget) {
                    this.dialogWidget.hide();
                }
            }
        }
    });

    var ExportBillDialog = declare('buildspace.apps.ProjectBuilder.ExportSupplierOfMaterialBillDialog', dijit.Dialog, {
        style: "padding:0px;margin:0;",
        title: nls.downloadExcelFile,
        bill: null,
        elementIds: {},
        withRate: false,
        buildRendering: function () {
            var content = this.createContent();
            content.startup();
            this.content = content;
            this.inherited(arguments);
        },
        postCreate: function () {
            domStyle.set(this.containerNode, {
                padding: "0",
                margin: "0"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function (e) {
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function () {
            this.destroyRecursive();
        },
        createContent: function () {
            var borderContainer = new dijit.layout.BorderContainer({
                    style: "padding:0;width:400px;height:80px;",
                    gutters: false
                }),
                toolbar = new dijit.Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;"
                }),
                form = ExportBillForm({
                    bill: this.bill,
                    elementIds: this.elementIds,
                    withRate: this.withRate,
                    dialogWidget: this
                });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style: "outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.download,
                    iconClass: "icon-16-container icon-16-import",
                    style: "outline:none!important;",
                    onClick: dojo.hitch(form, 'submit')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });

    var ExportItemGrid = declare('buildspace.apps.ProjectBuilder.ExportItemGrid', dojox.grid.EnhancedGrid, {
        type: null,
        selectedItem: null,
        bill: null,
        dialogWidget: null,
        billGrid: null,
        style: "border-top:none;",
        constructor: function (args) {
            this.itemIds = [];
            this.connects = [];
            if (args.type != 'tree') {
                this.plugins = {indirectSelection: {headerSelector: true, width: "20px", styles: "text-align:center;"}}
            }
            this.inherited(arguments);
        },
        canSort: function (inSortInfo) {
            return false;
        },
        postCreate: function () {
            var self = this;
            this.inherited(arguments);
            this.on('RowClick', function (e) {
                var item = self.getItem(e.rowIndex);
                if (self.type == 'tree') {
                    if (item && item.id > 0) {
                        self.disableToolbarButtons(false);
                    } else {
                        self.disableToolbarButtons(true);
                    }
                }
            });

            if (this.type != 'tree') {
                this._connects.push(connect.connect(this, 'onCellClick', function (e) {
                    self.selectTree(e);
                }));
                this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function (newValue) {
                    self.toggleAllSelection(newValue);
                }));
            }
        },
        dodblclick: function (e) {
            this.onRowDblClick(e);
        },
        disableToolbarButtons: function (isDisable) {
            var exportBtn = dijit.byId('ExportItemGrid-' + this.bill.id + 'Export-button');
            exportBtn._setDisabledAttr(isDisable);
        },
        exportBill: function (withRate) {
            if (this.itemIds.length > 0) {
                this.dialogWidget.hide();

                var dialog = new ExportBillDialog({
                    bill: this.bill,
                    elementIds: this.itemIds,
                    withRate: withRate
                });

                dialog.show();
            }
        },
        selectTree: function (e) {
            var rowIndex = e.rowIndex,
                newValue = this.selection.selected[rowIndex],
                item = this.getItem(rowIndex);
            if (item) {
                this.pushItemIdIntoGridArray(item, newValue);
            }
        },
        pushItemIdIntoGridArray: function (item, select) {
            var idx = dojo.indexOf(this.itemIds, item.id[0]);
            if (select) {
                if (idx == -1) {
                    this.itemIds.push(item.id[0]);
                }
            } else {
                if (idx != -1) {
                    this.itemIds.splice(idx, 1);
                }
            }
        },
        toggleAllSelection: function (checked) {
            var grid = this, selection = grid.selection;
            if (checked) {
                selection.selectRange(0, grid.rowCount - 1);
                grid.itemIds = [];
                grid.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if (item.id > 0) {
                                grid.itemIds.push(item.id[0]);
                            }
                        });
                    }
                });
            } else {
                selection.deselectAll();
                grid.itemIds = [];
            }
        },
        destroy: function () {
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var ExportGridContainer = declare('buildspace.apps.ProjectBuilder.ExportItemGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        bill: null,
        withRate: false,
        gridOpts: {},
        postCreate: function () {
            var self = this;
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                type: this.type,
                bill: this.bill,
                region: "center"
            });

            var grid = this.grid = new ExportItemGrid(this.gridOpts);

            if (this.type != 'tree') {
                var toolbar = new dijit.Toolbar({region: "top", style: "padding:2px;border-bottom:none;width:100%;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'ExportItemGrid-' + self.bill.id + 'Export-button',
                        label: nls.export,
                        iconClass: "icon-16-container icon-16-export",
                        onClick: function () {
                            grid.exportBill(self.withRate);
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new ToggleButton({
                        name: "withRate",
                        label: nls.supplyRate,
                        iconClass: "dijitCheckBoxIcon",
                        value: true,
                        checked: false,
                        onChange: function (newVal) {
                            self.withRate = newVal;
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new ToggleButton({
                        name: "exportTypeExcel",
                        label: nls.excel,
                        iconClass: "dijitCheckBoxIcon",
                        value: true,
                        disabled: true,
                        checked: true
                    })
                );

                toolbar.addChild(
                    new ToggleButton({
                        name: "exportTypeCSV",
                        label: nls.csv,
                        iconClass: "dijitCheckBoxIcon",
                        value: true,
                        disabled: true,
                        checked: false
                    })
                );

                toolbar.addChild(
                    new ToggleButton({
                        name: "exportTypeXML",
                        label: nls.xml,
                        iconClass: "dijitCheckBoxIcon",
                        value: true,
                        disabled: true,
                        checked: false
                    })
                );
                self.addChild(toolbar);
            }
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content: grid, region: 'center'}));

            var container = dijit.byId('ProjectBuilder-supply-of-material-item_export_' + this.bill.id + '-stackContainer');
            if (container) {
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(self.stackContainerTitle, 60),
                    id: self.pageId,
                    executeScripts: true
                }, node);
                container.addChild(child);
                child.set('content', self);
                container.selectChild(self.pageId);
            }
        }
    });

    return declare('buildspace.apps.ProjectBuilder.ExportItemDialog', dijit.Dialog, {
        style: "padding:0px;margin:0px;",
        title: nls.exportFromSupplyOfMaterialItemBill,
        selectedItem: null,
        bill: null,
        billGrid: null,
        buildRendering: function () {
            var content = this.createContent();
            content.startup();
            this.content = content;
            this.inherited(arguments);
        },
        postCreate: function () {
            domStyle.set(this.containerNode, {
                padding: "0px",
                margin: "0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function (e) {
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function () {
            this.destroyRecursive();
        },
        createContent: function () {
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style: "padding:0px;width:950px;height:500px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style: "outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "supplyOfMaterial/getElementList/id/" + self.bill.id
                }),
                content = ExportGridContainer({
                    stackContainerTitle: nls.elements,
                    pageId: 'export-page_library-' + this.bill.id,
                    bill: this.bill,
                    gridOpts: {
                        store: store,
                        projectId: this.projectId,
                        dialogWidget: self,
                        structure: [
                            {
                                name: 'No.',
                                field: 'id',
                                width: '30px',
                                styles: 'text-align:center;',
                                formatter: formatter.rowCountCellFormatter
                            },
                            {name: nls.description, field: 'description', width: 'auto'}
                        ],
                        onRowDblClick: function (e) {
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if (_item.id > 0 && _item.description[0] !== null) {
                                self.createItemGrid(_item);
                            }
                        }
                    }
                });
            var gridContainer = this.makeGridContainer(content, nls.elements);
            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        createItemGrid: function (element) {
            var self = this, formatter = GridFormatter();
            var store = new dojo.data.ItemFileWriteStore({
                url: "supplyOfMaterial/getItemList/id/" + element.id + '/bill_id/' + self.bill.id
            });

            ExportGridContainer({
                stackContainerTitle: element.description,
                pageId: 'export-page_item-' + this.bill.id + '_' + element.id,
                bill: this.bill,
                type: 'tree',
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    selectedItem: self.selectedItem,
                    billGrid: self.billGrid,
                    structure: [
                        {
                            name: 'No.',
                            field: 'id',
                            width: '30px',
                            styles: 'text-align:center;',
                            formatter: formatter.rowCountCellFormatter
                        },
                        {
                            name: nls.description,
                            field: 'description',
                            width: 'auto',
                            formatter: formatter.treeCellFormatter
                        },
                        {
                            name: nls.type,
                            field: 'type',
                            width: '70px',
                            styles: 'text-align:center;',
                            formatter: formatter.typeCellFormatter
                        },
                        {
                            name: nls.unit,
                            field: 'uom_id',
                            width: '70px',
                            styles: 'text-align:center;',
                            formatter: formatter.unitIdCellFormatter
                        },
                        {
                            name: nls.supplyRate,
                            field: 'supply_rate',
                            width: '80px',
                            styles: 'text-align:right;',
                            formatter: formatter.currencyCellFormatter
                        }
                    ]
                }
            });
        },
        makeGridContainer: function (content, title) {
            var id = this.bill.id;
            var stackContainer = dijit.byId('ProjectBuilder-supply-of-material-item_export_' + id + '-stackContainer');
            if (stackContainer) {
                dijit.byId('ProjectBuilder-supply-of-material-item_export_' + id + '-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style: 'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'ProjectBuilder-supply-of-material-item_export_' + id + '-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'ProjectBuilder-supply-of-material-item_export_' + id + '-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style: "padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('ProjectBuilder-supply-of-material-item_export_' + id + '-stackContainer-selectChild', "", function (page) {
                var widget = dijit.byId('ProjectBuilder-supply-of-material-item_export_' + id + '-stackContainer');
                if (widget) {
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page.id));
                    index = index + 1;
                    while (children.length > index) {
                        widget.removeChild(children[index]);
                        children[index].destroyRecursive(true);
                        index = index + 1;
                    }
                }
            });

            return borderContainer;
        }
    });
});