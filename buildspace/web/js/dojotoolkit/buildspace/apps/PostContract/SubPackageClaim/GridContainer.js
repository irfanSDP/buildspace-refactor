define('buildspace/apps/PostContract/SubPackageClaim/GridContainer',[
    'dojo/_base/declare',
    'dojo/currency',
    'dojo/number',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'buildspace/widget/grid/cells/FormulaTextBox',
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, currency, number, EnhancedGrid, GridFormatter, FormulatedColumn, FormulaTextBox, nls){

    var Grid = declare('buildspace.apps.PostContract/SubPackageClaim/Grid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        project: null,
        keepSelection: true,
        escapeHTMLInData: false,
        constructor: function()
        {
            this.formulatedColumn = FormulatedColumn(this,{});
        },
        canSort: function() {
            return false;
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;
            if(inCell != undefined){
                var item = this.getItem(inRowIndex),
                    field = inCell.field;

                if(item.id[0] <= 0){
                    window.setTimeout(function() {
                        self.edit.cancel();
                        self.focus.setFocusIndex(inRowIndex, inCell.index);
                    }, 10);
                    return false;
                }
            }
            return this._canEdit;
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = this.getItem(rowIdx), store = this.store;

            if(inAttrName !== 'profit_and_attendance_percent') return;

            val = parseFloat(val);

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    pid: this.project.id,
                    sid: item.id,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                };

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }
                };

                var xhrArgs = {
                    url: 'subPackageClaim/updateProfitAndAttendancePercentage',
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            if(item && !isNaN(parseInt(item.id[0]))){
                                updateCell(resp.data, store);
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
                    }
                };

                pb.show().then(function(){
                    dojo.xhrPost(xhrArgs);
                });
            }

            this.inherited(arguments);
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
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

    return declare('buildspace.apps.PostContract.SubPackageClaim.GridContainer', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        grid: null,
        stackContainer: null,
        project: null,
        postCreate: function(){
            this.inherited(arguments);

            this.formatter = new GridFormatter();

            var stackContainer = this.stackContainer = this.createStackContainer();

            var child = new dijit.layout.BorderContainer({
                title: this.title,
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                region: 'center'
            });

            child.addChild(this.createGrid());

            stackContainer.addChild(child);
        },
        exportProjectReport: function(){
            form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", 'budgetReportExport/exportProjectReport/pid/'+this.project.id);
            form.setAttribute("action", 'subPackageClaimExport/export/pid/'+this.project.id);
            form.setAttribute("target", "_self");

            document.body.appendChild(form);

            return form.submit();
        },
        getGridStructure: function(){
            return {
                noscroll: false,
                cells: [
                    [{
                        name: "No",
                        field: "id",
                        width: '30px',
                        styles: 'text-align: center;',
                        formatter: this.formatter.rowCountCellFormatter,
                        rowSpan : 2
                    },{
                        name: nls.description,
                        field: "description",
                        width: 'auto',
                        rowSpan : 2
                    },{
                        name: nls.claimNumber,
                        field: 'claim_no',
                        width:'90px',
                        styles:'text-align: center;',
                        formatter: this.formatter.unEditableIntegerCellFormatter,
                        rowSpan : 2
                    },{
                        name: nls.contractAmount,
                        field: 'contract_amount',
                        width:'90px',
                        styles:'text-align: right;',
                        formatter: this.formatter.unEditableCurrencyCellFormatter,
                        rowSpan : 2
                    },{
                        name: nls.voAmount,
                        field: 'vo_amount',
                        width:'90px',
                        styles:'text-align: right;',
                        formatter: this.formatter.unEditableCurrencyCellFormatter,
                        rowSpan : 2
                    },{
                        name: nls.workDone,
                        field: 'accumulative_work_done',
                        width:'90px',
                        styles:'text-align: right;',
                        formatter: this.formatter.unEditableCurrencyCellFormatter
                    },{
                        name: nls.voWorkDone,
                        field: 'accumulative_vo_work_done',
                        width:'90px',
                        styles:'text-align: right;',
                        formatter: this.formatter.unEditableCurrencyCellFormatter
                    },{
                        name: nls.amountCertified,
                        field: 'amount_certified',
                        width:'90px',
                        styles:'text-align: right;',
                        formatter: this.formatter.unEditableCurrencyCellFormatter,
                        rowSpan : 2
                    },{
                        name: nls.percent,
                        field: 'profit_and_attendance_percent',
                        width:'90px',
                        styles:'text-align: center;',
                        editable:true,
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: this.formatter.editablePercentageCellFormatter,
                    },{
                        name: nls.workDone,
                        field: 'profit_and_attendance_work_done',
                        width:'90px',
                        styles:'text-align: right;',
                        formatter: this.formatter.unEditableCurrencyCellFormatter
                    },{
                        name: nls.voWorkDone,
                        field: 'profit_and_attendance_vo_work_done',
                        width:'90px',
                        styles:'text-align: right;',
                        formatter: this.formatter.unEditableCurrencyCellFormatter
                    },{
                        name: nls.total,
                        field: 'profit_and_attendance_total',
                        width:'90px',
                        styles:'text-align: right;',
                        formatter: this.formatter.unEditableCurrencyCellFormatter
                    }],
                    [{
                        name: nls.accumulative,
                        styles:'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan : 2
                    },{
                        name: nls.profitAndAttendance,
                        styles:'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan : 4
                    }]
                ]
            };
        },
        createStackContainer: function(){
            var self = this;
            var stackContainerId = this.id + '-subPackageClaim-stackContainer';

            var stackContainer = self.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: stackContainerId
            });

            dojo.subscribe(stackContainerId+'-selectChild', "", function(page) {
                var widget = dijit.byId(stackContainerId);
                if(widget) {
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){
                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();

                            index = index + 1;
                        }
                    }
                }
            });

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: stackContainerId
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                id: self.id + '-subPackageClaim-controllerPane',
                content: controller
            });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            return stackContainer;
        },
        createGrid: function(){
            var self = this;

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.export,
                    iconClass: "icon-16-container icon-16-print",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.exportProjectReport();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.project.id+'ReloadGrid-button',
                    label: nls.reload,
                    iconClass: "icon-16-container icon-16-reload",
                    onClick: function(e){
                        self.grid.reload();
                    }
                })
            );

            var projectBudgetGrid = this.grid = Grid({
                project: self.project,
                structure: self.getGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url: 'subPackageClaim/getSubPackages/pid/' + self.project.id
                })
            });

            var gridContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
                region: 'center'
            });

            gridContainer.addChild(toolbar);
            gridContainer.addChild(projectBudgetGrid);

            return gridContainer;
        },
        close: function(){
            this.parentContainer.removeChild(this);
            return this.destroyRecursive();
        }
    });
});