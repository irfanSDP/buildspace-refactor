define('buildspace/apps/Location/ProjectLocationManagement/ProgressClaim/ProgressClaimContainer',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/dom-construct",
    "dojo/dom-style",
    "dojo/keys",
    'dojo/number',
    "dijit/Menu",
    "dijit/CheckedMenuItem",
    "dijit/layout/ContentPane",
    "dojox/form/manager/_Mixin",
    "dojox/form/manager/_NodeMixin",
    "dojox/form/manager/_ValueMixin",
    "dojox/form/manager/_DisplayMixin",
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    'dojox/grid/EnhancedGrid',
    'dojo/data/ItemFileWriteStore',
    "buildspace/widget/grid/cells/Formatter",
    'buildspace/widget/grid/cells/TextBox',
    "buildspace/widget/grid/plugins/LocationFilter",
    "dojox/grid/enhanced/plugins/Menu",
    'dojo/i18n!buildspace/nls/Location'
], function(declare, lang, domConstruct, domStyle, keys, number, Menu, CheckedMenuItem, ContentPane, _ManagerMixin, _ManagerNodeMixin, _ManagerValueMixin, _ManagerDisplayMixin, Form, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, EnhancedGrid, ItemFileWriteStore, GridFormatter, gridCellTxtBox, LocationFilterPlugin, MenuPlugin, nls){

    var Grid = declare('buildspace.apps.ProjectLocationManagement.ProgressClaim.Grid', EnhancedGrid, {
        style: "border-top:none;",
        project: null,
        region: 'center',
        filterParams: [],
        postCreate: function(){
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = this.getItem(rowIdx), store = this.store;

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    pid: this.project.id,
                    id: item.id,
                    attr_name: inAttrName,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = "location/progressClaimUpdate";

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
                                updateCell(resp.data, store);
                                var cell = self.getCellByField(inAttrName);
                                window.setTimeout(function() {
                                    self.focus.setFocusIndex(rowIdx, cell.index);
                                }, 10);
                            }
                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }

            this.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            if(inCell != undefined){
                var item = this.getItem(inRowIndex);
                if(item && inCell.editable && !item.has_prorate_qty[0]){
                    var self = this;
                    window.setTimeout(function() {
                        self.edit.cancel();
                        self.focus.setFocusIndex(inRowIndex, inCell.index);
                    }, 10);
                    return false;
                }
            }

            return this._canEdit;
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
        showHideColumn: function(show, index) {
            this.beginUpdate();
            this.layout.setColumnVisibility(index, show);
            this.endUpdate();
        }
    });

    var ExportToExcelForm = declare('buildspace.apps.Location.ProjectLocationManagement.ProgressClaim.ExportToExcelForm', [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        _ManagerMixin,
        _ManagerNodeMixin,
        _ManagerValueMixin,
        _ManagerDisplayMixin], {
        templateString: '<form data-dojo-attach-point="containerNode">' +
        '<table class="table-form">' +
        '<tr>' +
        '<td class="label" style="width:80px;"><label style="display: inline;"></span>'+nls.exportAs+' :</label></td>' +
        '<td>' +
        '<input type="text" name="filename" style="padding:2px;width:220px;" data-dojo-type="dijit/form/ValidationTextBox" data-dojo-props="trim:true, propercase:true, maxlength:45, required: true"> .xlsx' +
        '</td>' +
        '</tr>' +
        '</table>' +
        '</form>',
        project: null,
        region: 'center',
        dialogWidget: null,
        style: "outline:none;padding:0px;margin:0px;border:none;",
        baseClass: "buildspace-form",
        params: [],
        startup: function(){
            this.inherited(arguments);
            this.setFormValues({filename: this.project.title + " - Progress Claims"});
            var form = this.containerNode;

            var params = this.params;
            for(var i in params)
            {
                var field = document.createElement("input");
                field.setAttribute("name", i);
                field.setAttribute("value", params[i]);
                field.setAttribute('type', 'hidden');
                form.appendChild(field);
            }

            var csrfField = document.createElement("input");
            csrfField.setAttribute("name", '_csrf_token');
            csrfField.setAttribute("value", this.project._csrf_token);
            csrfField.setAttribute('type', 'hidden');
            form.appendChild(csrfField);
        },
        submit: function(){
            var self = this;
            var values = dojo.formToObject(this.id);
            if(this.validate()){
                buildspace.windowOpen('POST', 'location/exportProgressClaims/pid/'+this.project.id, values);
                if(self.dialogWidget){
                    self.dialogWidget.hide();
                }
            }
        }
    });

    var ExportExcelDialog = declare('buildspace.apps.Location.ProjectLocationManagement.ProgressClaim.ExportExcelDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.exportToExcel,
        project: null,
        params: [],
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
                    style:"padding:0px;width:400px;height:80px;",
                    gutters: false
                }),
                toolbar = new dijit.Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;"
                }),
                form = ExportToExcelForm({
                    project: this.project,
                    params: this.params,
                    dialogWidget: this
                });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.export,
                    iconClass: "icon-16-container icon-16-export",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(form, 'submit')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });

    return declare('buildspace.apps.Location.ProjectLocationManagement.ProgressClaim.ProgressClaimContainer', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        project: null,
        baseApp: null,
        postCreate: function(){
            this.inherited(arguments);

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            var self = this;
            pb.show().then(function(){
                dojo.xhrGet({
                    url: "location/getOpenClaimRevision",
                    handleAs: "json",
                    preventCache: true,
                    content: {
                        pid: self.project.id
                    },
                    load: function(openClaimRevision){
                        pb.hide();
                        if(openClaimRevision && openClaimRevision.locked_status){
                            self.renderContent(openClaimRevision);
                        }else{
                            pb.show().then(function(){
                                dojo.xhrGet({
                                    url: "location/getLocationCodeLevels",
                                    handleAs: "json",
                                    preventCache: true,
                                    content: {
                                        pid: self.project.id
                                    },
                                    load: function(data){
                                        self.renderContent(openClaimRevision, data);
                                        pb.hide();
                                    },
                                    error: function(error){
                                        pb.hide();
                                    }
                                });
                            });
                        }
                    },
                    error: function(error){
                        pb.hide();
                    }
                });
            });
        },
        renderContent: function(openClaimRevision, data){
            var self = this;
            var mainContent = dijit.byId(this.project.id+"-ProgressClaim-main_content");
            if(mainContent){
                mainContent.destroyRecursive();
            }

            if(openClaimRevision && openClaimRevision.locked_status){
                mainContent = new ContentPane({
                    id: this.project.id+"-ProgressClaim-main_content",
                    style: "padding:0px;border:0px;height:auto;",
                    doLayout: false,
                    content: '<div style="text-align:center;"><p><h1>'+nls.noOpenClaimRevisionMsg+'</h1></p></div>',
                    region: "center"
                });
            }else{
                var formatter = new GridFormatter();

                var customFormatter = {
                    percentageCellFormatter: function(cellValue, rowIdx, cell){
                        var value = number.parse(cellValue);

                        if(isNaN(value) || value == 0 || value == null){
                            cellValue = "&nbsp;";
                        }else{
                            var formattedValue = number.format(value, {places:2})+"%";
                            cellValue = value >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
                        }

                        return cellValue;
                    },
                    locationCellFormatter:  function(cellValue, rowIdx, cell){
                        if(cellValue && cellValue.length > 0){
                            return cellValue;
                        } else {
                            cell.customClasses.push('disable-cell');
                            return "&nbsp;";
                        }
                    }
                };

                var firstRowCells = [
                    {name: 'No.', field: 'count', width: '30px', styles: 'text-align:center;', rowSpan: 2, formatter: formatter.rowCountCellFormatter},
                    {name: nls.bill, field: 'bill_title', width:'280px', rowSpan: 2, hidden: true, showInCtxMenu: true},
                    {name: nls.columnType, field: 'column_name', width:'120px', styles:'text-align:center;', rowSpan: 2, hidden: true, showInCtxMenu: true},
                    {name: nls.columnUnit, field: 'column_unit', width:'120px', styles:'text-align:center;', rowSpan: 2, hidden: true, showInCtxMenu: true}
                ];

                var filterPluginColumns = [];

                filterPluginColumns.push({
                    'idx':2,
                    'widgetName':'dropdown',
                    'type': buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_TYPE,
                    'data': data.column_types
                });
                filterPluginColumns.push({
                    'idx':3,
                    'widgetName':'dropdown',
                    'type': buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_UNIT,
                    'data': data.column_units
                });

                var cellIdx = 3;

                for(var i = 0; i < parseInt(data.predefined_location_codes.length); i++){
                    var title;
                    switch(i) {
                        case buildspace.constants.PREDEFINED_LOCATION_CODE_TRADE_LEVEL:
                            title = nls.trade;
                            break;
                        case buildspace.constants.PREDEFINED_LOCATION_CODE_ELEMENT_LEVEL:
                            title = nls.element;
                            break;
                        default:
                            title = i > buildspace.constants.PREDEFINED_LOCATION_CODE_SUB_ELEMENT_LEVEL ? nls.subElement+' ('+(i - 1)+')' : nls.subElement;
                    }

                    firstRowCells.push({
                        name: title,
                        field: i+'-predefined_location_code',
                        width:'180px',
                        formatter: customFormatter.locationCellFormatter,
                        styles:'text-align:center;',
                        rowSpan: 2
                    });

                    cellIdx++;

                    filterPluginColumns.push({
                        'idx':cellIdx,
                        'widgetName':'dropdown',
                        'type': buildspace.constants.LOCATION_SEQUENCE_TYPE_TRADE,
                        'data': data.predefined_location_codes[i],
                        'level': i
                    });
                }

                for(var l = 0; l < parseInt(data.project_structure_location_codes.length); l++){
                    firstRowCells.push({
                        name: nls.location + ' '+ (l + 1),
                        field: l+'-project_structure_location_code',
                        width:'180px',
                        formatter: customFormatter.locationCellFormatter,
                        styles:'text-align:center;',
                        rowSpan: 2
                    });

                    cellIdx++;

                    filterPluginColumns.push({
                        'idx':cellIdx,
                        'widgetName':'dropdown',
                        'type': buildspace.constants.LOCATION_SEQUENCE_TYPE_LOCATION,
                        'data': data.project_structure_location_codes[l],
                        'level': l
                    });
                }

                firstRowCells.push({
                    name: nls.billItem,
                    field: 'description',
                    width: (parseInt(data.project_structure_location_codes.length) > 0 && parseInt(data.predefined_location_codes.length) > 0) ? '640px' : 'auto',
                    rowSpan: 2
                });

                cellIdx +=1;

                filterPluginColumns.push({
                    'idx':cellIdx,
                    'widgetName':'textbox'
                });

                firstRowCells.push({
                    name: nls.unit,
                    field: 'uom',
                    width:'70px',
                    styles:'text-align:center;',
                    rowSpan: 2,
                    noresize: true
                });

                firstRowCells.push({
                    name: nls.prorated+" "+nls.qty,
                    field: 'prorated_qty',
                    width:'80px',
                    styles:'text-align:right;',
                    formatter: formatter.numberCellFormatter,
                    rowSpan: 2,
                    noresize: true
                });

                var headerMenu = new Menu();

                dojo.forEach(firstRowCells, function(cell, index){
                    if(cell.hasOwnProperty('showInCtxMenu') && cell.showInCtxMenu){
                        headerMenu.addChild(new CheckedMenuItem({
                            label: cell.name,
                            checked: (!cell.hasOwnProperty('hidden') || cell.hidden === false),
                            onChange: lang.hitch(self, "showHideHeaderColumn", (index))
                        }));
                    }
                });

                firstRowCells.push({
                    name: '%',
                    field: 'previous_percentage',
                    headerClasses: "typeHeader1",
                    styles: "text-align:right;",
                    width: '60px',
                    formatter: formatter.unEditablePercentageCellFormatter,
                    noresize: true
                });
                firstRowCells.push({
                    name: nls.qty,
                    field: 'previous_quantity',
                    headerClasses: "typeHeader1",
                    styles: "text-align:right;",
                    width: '90px',
                    formatter: formatter.unEditableNumberCellFormatter,
                    noresize: true
                });
                firstRowCells.push({
                    name: '%',
                    field: 'current_percentage',
                    headerClasses: "typeHeader2",
                    styles: "text-align:right;",
                    width: '60px',
                    editable: true,
                    cellType: 'buildspace.widget.grid.cells.TextBox',
                    formatter: formatter.editablePercentageCellFormatter,
                    noresize: true
                });
                firstRowCells.push({
                    name: nls.qty,
                    field: 'current_quantity',
                    headerClasses: "typeHeader2",
                    styles: "text-align:right;",
                    width: '90px',
                    editable: true,
                    cellType: 'buildspace.widget.grid.cells.TextBox',
                    formatter: formatter.currencyCellFormatter,
                    noresize: true
                });
                firstRowCells.push({
                    name: '%',
                    field: 'up_to_date_percentage',
                    headerClasses: "typeHeader1",
                    styles: "text-align:right;",
                    width: '60px',
                    editable: true,
                    cellType: 'buildspace.widget.grid.cells.TextBox',
                    formatter: formatter.editablePercentageCellFormatter,
                    noresize: true
                });
                firstRowCells.push({
                    name: nls.qty,
                    field: 'up_to_date_quantity',
                    headerClasses: "typeHeader1",
                    styles: "text-align:right;",
                    width: '90px',
                    editable: true,
                    cellType: 'buildspace.widget.grid.cells.TextBox',
                    formatter: formatter.currencyCellFormatter,
                    noresize: true
                });

                var gridStructure = {
                    cells: [
                        firstRowCells,
                        [{
                            name: nls.previousClaim,
                            field: 'id',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader typeHeader1",
                            noresize: true,
                            colSpan: 2
                        },{
                            name: nls.currentClaim,
                            field: 'id',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader typeHeader2",
                            noresize: true,
                            colSpan: 2
                        },{
                            name: nls.upToDateClaim,
                            field: 'id',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader typeHeader1",
                            noresize: true,
                            colSpan: 2
                        }]
                    ]
                }

                this.grid = mainContent = new Grid({
                    id: this.project.id+"-ProgressClaim-main_content",
                    project: this.project,
                    structure: gridStructure,
                    plugins: {
                        menus: {
                            headerMenu: headerMenu
                        },
                        buildspaceLocationFilter: {
                            columns: filterPluginColumns,
                            gridStoreUrl: "location/getBillByLocations/pid/"+this.project.id,
                            dropDownFilterUrl: "location/getLocationCodeLevels/pid/"+this.project.id,
                            manualFilter: true
                        }
                    },
                    store: new ItemFileWriteStore({
                        url: "location/getBillByLocations/pid/"+this.project.id,
                        clearOnClose: true
                    })
                });
            }

            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border-bottom:none;padding:2px;width:100%;"});

            toolbar.addChild(new dijit.form.Button({
                label: nls.exportToExcel,
                iconClass: "icon-16-container icon-16-spreadsheet",
                style:"outline:none!important;",
                onClick: function(e) {
                    ExportExcelDialog({
                        project: self.project,
                        params: self.grid.filterParams
                    }).show();
                }
            }));

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.filterLocations,
                    iconClass: "icon-16-container icon-16-zoom",
                    style:"outline:none!important;",
                    onClick: lang.hitch(this, "filterLocations")
                })
            );

            this.addChild(toolbar);
            this.addChild(mainContent);
        },
        filterLocations: function(){
            this.grid.manualFilter();
        },
        showHideHeaderColumn: function(index, show){
            this.grid.showHideColumn(show, index)
        }
    });
});
