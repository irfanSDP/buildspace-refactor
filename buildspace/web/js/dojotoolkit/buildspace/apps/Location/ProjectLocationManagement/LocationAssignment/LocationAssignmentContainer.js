define('buildspace/apps/Location/ProjectLocationManagement/LocationAssignment/LocationAssignmentContainer',[
    'dojo/_base/declare',
    "dojo/json",
    "dojo/dom-style",
    'dojo/_base/lang',
    "dojo/aspect",
    'dojo/keys',
    'dojo/query',
    'dojo/on',
    "dojo/dom",
    'dojo/number',
    "dojo/dom-construct",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/locationSelectionForm.html",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'buildspace/widget/grid/Filter',
    "buildspace/widget/grid/cells/Formatter",
    "buildspace/widget/forms/MultiSelectDropDown",
    "../AssignedLocationDialog",
    'dojo/i18n!buildspace/nls/Location'
], function(declare, JSON, domStyle, lang, aspect, keys, query, on, dom, number, domConstruct, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, locationSelectionTemplate, IndirectSelection, Filter, GridFormatter, MultiSelectDropDown, AssignedLocationDialog, nls){

    var LocationSelectionForm = declare("buildspace.apps.ProjectLocationManagement.LocationAssignment.LocationSelectionForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: locationSelectionTemplate,
        baseClass: "buildspace-form",
        region: 'top',
        style: "border:none;padding:5px;overflow:auto;height:128px;",
        project: null,
        nls: nls,
        formData: null,
        postCreate: function(){
            this.inherited(arguments);
        },
        startup: function(){
            this.inherited(arguments);
        },
        addProjectStructureLocationCodeRow: function(locationType, sequence, parentIds){
            sequence = parseInt(sequence);

            var comboBoxLabel,
                headerClassName,
                locationClassName,
                selectionTitleTableRowNode,
                selectionTableRowNode,
                selectInputName;

            if(locationType == buildspace.constants.LOCATION_SEQUENCE_TYPE_TRADE){
                selectionTitleTableRowNode = this.predefinedLocationCodeSelectionTitleTableRow;
                selectionTableRowNode      = this.predefinedLocationCodeSelectionTableRow;
                headerClassName            = "predefinedLocationCodeHeadSelect";
                locationClassName          = "predefinedLocationCodeSelect";
                selectInputName            = "trade";

                switch(sequence)
                {
                    case buildspace.constants.PREDEFINED_LOCATION_CODE_TRADE_LEVEL:
                        comboBoxLabel = nls.trade;
                        break;
                    case buildspace.constants.PREDEFINED_LOCATION_CODE_ELEMENT_LEVEL:
                        comboBoxLabel = nls.element;
                        break;
                    default:
                        comboBoxLabel = sequence > buildspace.constants.PREDEFINED_LOCATION_CODE_SUB_ELEMENT_LEVEL ? nls.subElement+' ('+(sequence - 1)+')' : nls.subElement;
                }

            }else if(locationType == buildspace.constants.LOCATION_SEQUENCE_TYPE_LOCATION){
                selectionTitleTableRowNode = this.projectStructureLocationCodeSelectionTitleTableRow;
                selectionTableRowNode      = this.projectStructureLocationCodeSelectionTableRow;
                headerClassName            = "projectStructureLocationCodeHeadSelect";
                locationClassName          = "projectStructureLocationCodeSelect";
                comboBoxLabel              = nls.location + ' '+ (sequence + 1);
                selectInputName            = "location";
            }

            var seq = 1;
            query("."+headerClassName, this.locationSelectionForm.id).forEach(function(node){
                if(seq > sequence)
                    domConstruct.destroy(node);

                seq++;
            });

            seq = 1;
            query("."+locationClassName, this.locationSelectionForm.id).forEach(function(node){
                if(seq > sequence)
                    domConstruct.destroy(node);

                seq++;
            });

            var self = this;
            var postContent = {
                t: locationType,
                s: sequence,
                pid: this.project.id
            };

            if(parentIds && parentIds.length > 0){
                lang.mixin(postContent, { "parent_id[]": parentIds });
            }

            dojo.xhrPost({
                url: "location/getLocationCodeByLevel",
                content: postContent,
                handleAs: 'json',
                load: function(data) {
                    if(data.items.length > 0){
                        var th = domConstruct.create("th", {
                                'style': 'border:none!important;',
                                'class': headerClassName
                            }, selectionTitleTableRowNode),
                            lbl = domConstruct.create("label", {
                                innerHTML: comboBoxLabel + ': '
                            }, th, 'last');

                        var td = domConstruct.create("td", {
                            'style': 'width:140px;',
                            'class': locationClassName
                        }, selectionTableRowNode);

                        var multiSelectDropDown = new MultiSelectDropDown({
                            valueField: "id",
                            textField: "name",
                            name: selectInputName,
                            style: "padding:2px;width:128px;",
                            dropDownWidth: "240px",
                            storeSort: [{
                                attribute: "priority", descending: false
                            },{
                                attribute: "lft", descending: false
                            },{
                                attribute: "level", descending: false
                            }],
                            dataStore: new dojo.data.ItemFileReadStore({
                                data: data
                            }),
                            onClick: function (e) {
                                var values = this.getSelectedIds();
                                if (values.length > 0) {
                                    self.addProjectStructureLocationCodeRow(locationType, (sequence + 1), values);
                                } else {
                                    self.addProjectStructureLocationCodeRow(locationType, sequence, parentIds);
                                }
                            }
                        });

                        multiSelectDropDown.placeAt(td, 'last');
                    }
                },
                error: function(error) {
                }
            });
        },
        getSelectedData: function(){
            var values = dojo.formToObject(this.locationSelectionForm.id);

            var selectedTradeIds,
                selectedLocationIds;

            if(Array.isArray(values.trade)){
                for(var i=values.trade.length;i >= 0; i--){
                    if (typeof values.trade[i] !== 'undefined' && values.trade[i] && values.trade[i].length > 0) {
                        selectedTradeIds = values.trade[i];
                        break;
                    }
                }
            }else{
                selectedTradeIds = values.trade;
            }

            if(Array.isArray(values.location)){
                for(var x=values.location.length;x >= 0; x--){
                    if (typeof values.location[x] !== 'undefined' && values.location[x] && values.location[x].length > 0) {
                        selectedLocationIds = values.location[x];
                        break;
                    }
                }
            }else{
                selectedLocationIds = values.location;
            }

            return {
                trade: selectedTradeIds,
                location: selectedLocationIds
            }
        },
        onCancel: function(){

        }
    });

    var BillGrid = declare('buildspace.apps.ProjectLocationManagement.LocationAssignment.BillGrid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        element: null,
        region: 'center',
        baseApp: null,
        constructor: function(args){
            if(args.type == 'tree'){
                this.escapeHTMLInData = false;
                this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align:center;"}}
            }
            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);

            this.on('RowClick', function (e) {
                if (e.cell) {
                    var colField = e.cell.field,
                        baseApp = this.baseApp,
                        _item = this.getItem(e.rowIndex);

                    if(this.type == 'tree' && _item && !isNaN(parseInt(_item.id[0])) && _item.has_location[0] && colField == 'has_location') {

                        var pb = buildspace.dialog.indeterminateProgressBar({
                            title:nls.pleaseWait+'...'
                        });

                        pb.show().then(function(){
                            dojo.xhrGet({
                                url: "location/getBillItemLocations/",
                                content: { id: _item.id },
                                handleAs: "json",
                                load: function (data) {
                                    pb.hide();

                                    new AssignedLocationDialog({
                                        baseApp: baseApp,
                                        billItem: _item,
                                        locationAssignments: data
                                    }).show();
                                },
                                error: function (error) {
                                    pb.hide();
                                }
                            });
                        });

                    }
                }
            });
        },
        canSort: function(inSortInfo){
            return false;
        },
        onHeaderCellClick: function(e) {
            if (!dojo.hasClass(e.cell.id, "staticHeader")) {
                e.grid.setSortIndex(e.cell.index);
                e.grid.onHeaderClick(e);
            }
        },
        onHeaderCellMouseOver: function(e) {
            if (!dojo.hasClass(e.cell.id, "staticHeader")) {
                dojo.addClass(e.cellNode, this.cellOverClass);
            }
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
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var BillGridContainer = declare('buildspace.apps.ProjectLocationManagement.LocationAssignment.BillGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        gridOpts: {},
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, { type: this.type, project: this.project, region:"center" });

            var grid = this.grid = new BillGrid(this.gridOpts);
            this.addChild(grid);

            var container = dijit.byId('LocationManagement-location_assignment_'+this.project.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dijit.layout.BorderContainer( {gutters: false, title: buildspace.truncateString(this.stackContainerTitle, 60), id: this.pageId},node );
                this.region = 'center';
                var filterFields = [];
                if(this.pageId){
                    if(new RegExp('^location_assignment\-page_project_element-').test(this.pageId)){
                        filterFields =[
                            {'description':nls.description}
                        ];
                    }
                    if(new RegExp('^location_assignment\-page_project_item\-').test(this.pageId)){
                        filterFields = [
                            {'description':nls.description},
                            {'uom_symbol':nls.unit}
                        ];
                    }
                }
                child.addChild(new Filter({
                    region: 'top',
                    editableGrid: false,
                    grid: grid,
                    filterFields: filterFields
                }));
                child.addChild(this);

                container.addChild(child);
                lang.mixin(child, {grid: grid});
                container.selectChild(this.pageId);
            }
        }
    });

    var BillContainer = declare('buildspace.apps.ProjectLocationManagement.LocationAssignment.BillContainer', dijit.layout.BorderContainer, {
        project: null,
        baseApp: null,
        region: 'center',
        style:"padding:0px;margin:0px;width:100%;",
        gutters: false,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var store = dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: "billManagerImportRate/getProjectBreakdown/id/"+this.project.id
                }),
                content = BillGridContainer({
                    stackContainerTitle: nls.scheduleOfRates,
                    pageId: 'import_rate-page_project-'+this.project.id,
                    project: this.project,
                    gridOpts: {
                        baseApp: this.baseApp,
                        store: store,
                        structure: [
                            {name: 'No.', field: 'count', width:'40px', styles:'text-align:center;', formatter: CustomFormatter.rowCountCellFormatter },
                            {name: nls.description, field: 'title', width:'auto', formatter: CustomFormatter.treeCellFormatter},
                            {name: nls.billType, field: 'bill_type', width:'180px', styles:'text-align:center;', formatter: CustomFormatter.billTypeCellFormatter}
                        ],
                        onRowDblClick: function(e){
                            var _this = this, bill = _this.getItem(e.rowIndex);
                            if(!isNaN(parseInt(bill.id[0])) && bill.title[0] !== null && bill.type[0] == buildspace.constants.TYPE_BILL){
                                self.createElementGrid(bill);
                            }
                        }
                    }
                });

            var gridContainer = this.makeGridContainer(content, buildspace.truncateString(this.project.title, 100));
            this.addChild(gridContainer);
            gridContainer.startup();
        },
        createElementGrid: function(bill){
            var self = this, formatter = GridFormatter();

            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose: true,
                url:"billManagerImportRate/getElementList/id/"+bill.id
            });

            BillGridContainer({
                stackContainerTitle: bill.title,
                pageId: 'location_assignment-page_project_element-'+this.project.id+'_'+bill.id,
                project: this.project,
                gridOpts: {
                    baseApp: this.baseApp,
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto' }
                    ],
                    onRowDblClick: function(e){
                        var _this = this, element = _this.getItem(e.rowIndex);
                        if(!isNaN(parseInt(element.id[0])) && element.description[0] !== null){

                            dojo.xhrGet({
                                url: "billManager/getBillInfo",
                                handleAs: "json",
                                content: {
                                    id: String(bill.id)
                                }
                            }).then(function(billInfo){
                                self.createItemGrid(element, billInfo);
                            });
                        }
                    }
                }
            });
        },
        createItemGrid: function(element, billInfo){
            var formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url:"location/getBillItemList/id/"+element.id
                });

            var typeColumParentCells = [];

            var colCount = 0;

            var structure = {
                noscroll: false,
                width: 57.8,
                cells: [
                    [{
                        name: 'No.',
                        field: 'id',
                        width:'30px',
                        styles:'text-align:center;',
                        formatter: formatter.rowCountCellFormatter,
                        noresize: true,
                        rowSpan: 2
                    },{
                        name: nls.description,
                        field: 'description',
                        width: billInfo.column_settings.length > 1 ? '500px' : 'auto',
                        formatter: formatter.treeCellFormatter,
                        rowSpan: 2
                    },{
                        name: nls.type,
                        field: 'type',
                        width:'100px',
                        styles:'text-align:center;',
                        formatter: formatter.typeCellFormatter,
                        noresize: true,
                        rowSpan: 2
                    },{
                        name: nls.location,
                        field: 'has_location',
                        width:'100px',
                        styles:'text-align:center;',
                        formatter: CustomFormatter.hasLocationCellFormatter,
                        noresize: true,
                        rowSpan: 2
                    },{
                        name: nls.unit,
                        field: 'uom_id',
                        width:'100px',
                        styles:'text-align:center;',
                        formatter: formatter.unitIdCellFormatter,
                        noresize: true,
                        rowSpan: 2
                    }]
                ]
            };

            var typeColumnChildren = [
                {name: nls.prorated+" %", field_name: 'percentage', width: '80px', styles: "text-align:right;", formatter: CustomFormatter.percentageCellFormatter},
                {name: nls.qty, field_name: 'qty', width: '80px', styles: "text-align:right;", formatter: formatter.numberCellFormatter},
                {name: nls.prorated+" "+nls.qty, field_name: 'prorated_qty', width: '80px', styles: "text-align:right;", formatter: formatter.numberCellFormatter}
            ];

            dojo.forEach(billInfo['column_settings'], function(typeColumn){
                colCount++;

                typeColumParentCells.push({
                    name: typeColumn.name + "<br>"+nls.totalUnit+":" + typeColumn.quantity,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader typeHeader"+colCount,
                    colSpan: 3
                });

                for(var i=0;i < typeColumnChildren.length;i++){
                    var cellStructure = {
                        field: String(typeColumn.id)+"-"+typeColumnChildren[i].field_name,
                        columnType: "typeColumn",
                        headerClasses: "typeHeader"+colCount
                    };
                    lang.mixin(cellStructure, typeColumnChildren[i]);
                    structure.cells[0].push(cellStructure);
                }
            });

            structure.cells.push(typeColumParentCells);

            BillGridContainer({
                stackContainerTitle: element.description,
                pageId: 'location_assignment-page_project_item-'+this.project.id+'_'+element.id,
                project: this.project,
                type: 'tree',
                gridOpts: {
                    id: 'location_assignment-project_item_grid-'+this.project.id,
                    baseApp: this.baseApp,
                    store: store,
                    element: element,
                    structure: structure/**/
                }
            });
        },
        makeGridContainer: function(content, title){
            var id = this.project.id;
            var stackContainer = dijit.byId('LocationManagement-location_assignment_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('LocationManagement-location_assignment_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'LocationManagement-location_assignment_'+id+'-stackContainer'
            });

            var gridContainer = new dijit.layout.BorderContainer({
                gutters: false
            });

            gridContainer.addChild(
                new Filter({
                    grid: content.grid,
                    region: 'top',
                    editableGrid: false,
                    filterFields:[
                        {'title':nls.description}
                    ]
                })
            );
            content.region = 'center';
            gridContainer.addChild(content);

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: gridContainer,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'LocationManagement-location_assignment_'+id+'-stackContainer'
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

            dojo.subscribe('LocationManagement-location_assignment_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('LocationManagement-location_assignment_'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    if(children.length > index + 1){
                        page.grid.store.save();
                        page.grid.store.close();

                        var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                            handle.remove();
                            this.scrollToRow(this.selection.selectedIndex);
                        });

                        page.grid._refresh();
                    }

                    while(children.length > index+1 ){
                        index = index + 1;
                        widget.removeChild(children[ index ]);
                        children[ index ].destroyRecursive(true);
                    }
                }
            });

            return borderContainer;
        }
    });

    var CustomFormatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return cellValue > 0 ? cellValue : '';
        },
        treeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            var level = item.level*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;
            if(item.type < buildspace.constants.TYPE_BILL){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        },
        billTypeCellFormatter: function(cellValue, rowIdx){
            return buildspace.getBillTypeText(cellValue);
        },
        hasLocationCellFormatter: function(cellValue, rowIdx, cell){
            if(!cellValue){
                cell.customClasses.push('disable-cell');
            }

            return cellValue ? '<div style="cursor:pointer;color:blue;">'+nls.assigned.toUpperCase()+'</div>' : "";
        },
        percentageCellFormatter: function(cellValue, rowIdx, cell){
            var value = number.parse(cellValue);

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = number.format(value, {places:2})+"%";
                cellValue = value >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            return cellValue;
        }
    };

    return declare('buildspace.apps.Location.ProjectLocationManagement.LocationAssignment.LocationAssignmentContainer', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        project: null,
        baseApp: null,
        postCreate: function(){
            this.inherited(arguments);

            var container = new dijit.layout.BorderContainer({
                region: "center",
                style:"padding:0;width:100%;height:100%;",
                gutters: false,
                liveSplitters: true
            });

            var locationSelectionForm = this.locationSelectionForm = new LocationSelectionForm({
                project: this.project
            });

            container.addChild(locationSelectionForm);

            var billContainer = new BillContainer({
                project: this.project,
                baseApp: this.baseApp
            });

            container.addChild(billContainer);

            var locationSequences = [
                buildspace.constants.LOCATION_SEQUENCE_TYPE_TRADE,
                buildspace.constants.LOCATION_SEQUENCE_TYPE_LOCATION
            ];

            dojo.forEach( locationSequences, function( loc ) {
                locationSelectionForm.addProjectStructureLocationCodeRow(loc, 0, null);
            });

            this.addChild(container);
        },
        save: function(){
            var billItemGrid = dijit.byId('location_assignment-project_item_grid-'+this.project.id);
            if(typeof billItemGrid == 'undefined'){
                buildspace.dialog.alert(nls.noBillItemAlert, nls.pleaseOpenBillItem+'.', 90, 320);
            }else{
                var locationSelectionData = this.locationSelectionForm.getSelectedData();

                if(billItemGrid.selection.getSelected().length > 0 && locationSelectionData.trade && locationSelectionData.location && locationSelectionData.trade.length > 0 && locationSelectionData.location.length > 0){
                    var ids = [];
                    dojo.forEach(billItemGrid.selection.getSelected(), function(item){
                        if(item && !isNaN(parseInt(item.id[0])) &&
                            item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER &&
                            item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                            ids.push(item.id[0]);
                        }
                    });

                    if(ids.length > 0 && billItemGrid.element){
                        var pb = buildspace.dialog.indeterminateProgressBar({
                            title:nls.savingData+'. '+nls.pleaseWait+'...'
                        });
                        var self = this;

                        var trade    = locationSelectionData.trade.split(",");
                        var location = locationSelectionData.location.split(",");

                        pb.show().then(function(){
                            dojo.xhrPost({
                                url: 'location/locationAssignmentUpdate',
                                content: {
                                    "t[]": trade,
                                    "l[]": location,
                                    "bid[]": ids,
                                    pid: self.project.id,
                                    _csrf_token: self.project._csrf_token
                                },
                                handleAs: 'json',
                                load: function(resp) {
                                    if(resp.success){
                                        var store = billItemGrid.store;
                                        dojo.forEach(resp.items, function(node){
                                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(item){
                                                for(var property in node){
                                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                                        store.setValue(item, property, node[property]);
                                                    }
                                                }
                                            }});
                                        });
                                        store.save();
                                    }

                                    self.baseApp.resetBQLocationTab();

                                    pb.hide();
                                    billItemGrid.selection.clear();
                                },
                                error: function(error) {
                                    pb.hide();
                                    billItemGrid.selection.clear();
                                }
                            });
                        });

                    }else{
                        billItemGrid.selection.clear();
                    }
                }else{
                    var title, msg;
                    if(billItemGrid.selection.getSelected().length == 0){
                        title = nls.noItemSelectedAlert;
                        msg   = nls.pleaseSelectItem
                    }else if(!locationSelectionData.trade || locationSelectionData.trade.length == 0){
                        title = nls.noTradeSelectedAlert;
                        msg   = nls.pleaseSelectTrade;
                    }else if(!locationSelectionData.location || locationSelectionData.location.length == 0){
                        title = nls.noLocationSelectedAlert;
                        msg   = nls.pleaseSelectLocation;
                    }

                    buildspace.dialog.alert(title, msg+'.', 90, 320);
                }
            }
        }
    });
});
