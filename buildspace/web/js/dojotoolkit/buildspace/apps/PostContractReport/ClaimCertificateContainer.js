define('buildspace/apps/PostContractReport/ClaimCertificateContainer',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/aspect',
    "dojo/html",
    "dojo/on",
    "dojo/dom",
    'dojo/currency',
    'dojo/number',
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!../PostContract/templates/claimCertificateViewForm.html",
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/PostContract'], function(declare, lang, aspect, html, on, dom, currency, number, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, viewTemplate, EnhancedGrid, GridFormatter, nls){

    var ClaimCertificateViewForm = declare('buildspace.apps.PostContractReport.ClaimCertificateViewForm',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin],{
        templateString: viewTemplate,
        baseClass: "buildspace-form",
        claimCertificate: null,
        claimCertificateContainer: null,
        nls: nls,
        startup: function(){
            this.inherited(arguments);
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            var xhrContent = {
                id: this.claimCertificate.id
            };

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'postContract/claimCertificateInfo',
                    handleAs: 'json',
                    content: xhrContent,
                    load: function(data){
                        self.claimCertificateViewForm.setFormValues(data);
                        html.set(self.accRemarksNode, data.acc_remarks);
                        html.set(self.qsRemarksNode, data.qs_remarks);

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        postCreate: function(){
            this.inherited(arguments);
        }
    });

    var Grid = declare('buildspace.apps.PostContractReport.ClaimCertificateGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        claimCertificateContainer: null,
        constructor:function(args){
            var formatter = new GridFormatter();

            var CustomFormatter = {
                currentViewingCellFormatter: function(cellValue, rowIdx){
                    var item = this.grid.getItem(rowIdx);

                    cellValue = "&nbsp;";

                    if(item && !isNaN(parseInt(item.id[0]))){
                        if(item.current_selected_revision[0]){
                            cellValue = '<div style="margin:auto;" class="icon-16-container icon-16-checkmark2"></div>';
                        }else{
                            cellValue = '<a href="#" onclick="return false;">'+nls.printThisRevision+'</a>';
                        }
                    }

                    return cellValue;
                }
            };

            this.structure = {
                noscroll: false,
                cells: [
                    [{
                        name: nls.claimNumber,
                        field: 'version',
                        width:'80px',
                        styles:'text-align:center;'
                    }, {
                        name: nls.nettPayableAmount,
                        field: 'nett_payable_amount',
                        width: '160px',
                        styles:'text-align:right;',
                        formatter: formatter.unEditableCurrencyCellFormatter
                    },{
                        name: nls.status,
                        field: 'status_txt',
                        width:'150px',
                        styles:'text-align:center;'
                    },{
                        name: nls.paidAmount,
                        field: 'paid_amount',
                        width:'160px',
                        styles:'text-align:right;',
                        formatter: formatter.unEditableCurrencyCellFormatter
                    },{
                        name: nls.balanceAmount,
                        field: 'balance_amount',
                        width:'160px',
                        styles:'text-align:right;',
                        formatter: formatter.unEditableCurrencyCellFormatter
                    },{
                        name: nls.approvalDate,
                        field: 'approval_date',
                        width:'120px',
                        styles:'text-align:center;'
                    },{
                        name: nls.created_at,
                        field: 'created_at',
                        width:'120px',
                        styles:'text-align:center;'
                    },{
                        name: nls.currentPrintingRevision,
                        field: 'current_selected_revision',
                        width:'auto',
                        styles:'text-align:center;',
                        formatter: CustomFormatter.currentViewingCellFormatter
                    }]
                ]
            };

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);

            this.on('RowClick', function(e){
                var item = this.getItem(e.rowIndex),
                    colField = e.cell ? e.cell.field : null;
                if(colField == 'current_selected_revision' && !isNaN(parseInt(item.id[0])) && !item.current_selected_revision[0]){
                    this.setSelectedRevision(item);
                }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        onRowDblClick: function (e) {
            this.inherited(arguments);
            var rowIndex = this.selection.selectedIndex,
                item = this.getItem(rowIndex),
                colField = e.cell ? e.cell.field : null;

            if(this.selection.selectedIndex > -1 && item && !isNaN(parseInt(item.id[0])) && colField != 'current_selected_revision') {
                this.claimCertificateContainer.openClaimCertificateViewForm(item);
            }
        },
        setSelectedRevision: function(claimCertificate){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });

            var params = {
                id: claimCertificate.id,
                _csrf_token: claimCertificate._csrf_token
            };

            var self = this,
                store = this.store;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: "postContract/claimCertificateSetSelectedRevision",
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            dojo.forEach(resp.items, function(data){
                                store.fetchItemByIdentity({ 'identity' : data.id,  onItem : function(item){
                                    store.setValue(item, "current_selected_revision", data["current_selected_revision"]);

                                }});
                            });
                            store.save();

                            self.claimCertificateContainer.closeBillTabs();

                            pb.hide();
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    return declare('buildspace.apps.PostContractReport.ClaimCertificateContainer', dijit.layout.BorderContainer, {
        style: "padding:0;width:100%;margin:0;border:none;height:100%;",
        gutters: false,
        project: null,
        workArea: null,
        postCreate: function() {
            this.inherited(arguments);

            var project = this.project;

            dojo.subscribe('postContractClaimCertificate' + project.id + '-stackContainer-selectChild', "", function (page) {
                var widget = dijit.byId('postContractClaimCertificate' + project.id + '-stackContainer');
                if (widget) {
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page));

                    index = index + 1;

                    if (children.length > index) {
                        while (children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();

                            index = index + 1;
                        }

                        if (page.grid) {
                            var selectedIndex = page.grid.selection.selectedIndex;

                            page.grid.store.save();
                            page.grid.store.close();

                            var handle = aspect.after(page.grid, "_onFetchComplete", function () {
                                handle.remove();
                                if (selectedIndex > -1) {
                                    this.scrollToRow(selectedIndex);
                                    this.selection.setSelected(selectedIndex, true);
                                }
                            });

                            page.grid.sort();
                        }
                    }
                }
            });

            this.grid = new Grid({
                title: nls.claimCertificateList,
                claimCertificateContainer: this,
                store: new dojo.data.ItemFileWriteStore({
                    url:'postContract/getClaimCertificates/pid/'+project.id,
                    handleAs: "json",
                    clearOnClose: true,
                    urlPreventCache: true
                })
            });

            var stackContainer = dijit.byId('postContractClaimCertificate' + project.id + '-stackContainer');

            if (stackContainer) {
                dijit.byId('postContractClaimCertificate' + project.id + '-stackContainer').destroyRecursive();
            }

            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: 'postContractClaimCertificate' + project.id + '-stackContainer'
            });

            stackContainer.addChild(this.grid);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'postContractClaimCertificate' + project.id + '-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                id: 'postContractClaimCertificateGrid' + project.id + '-controllerPane',
                content: controller
            });

            this.addChild(stackContainer);
            this.addChild(controllerPane);
        },
        openClaimCertificateViewForm: function(claimCertificateObj){
            var container = dijit.byId('postContractClaimCertificate' + this.project.id + '-stackContainer');

            if (container) {

                var page = dijit.byId('claimCertificatePage-' + this.project.id);

                if(page){
                    container.removeChild(page);
                    page.destroyRecursive();
                }

                var status = Array.isArray(claimCertificateObj.status) ? claimCertificateObj.status[0] : claimCertificateObj.status;
                var fontColor;
                switch(status){
                    case buildspace.constants.CLAIM_CERTIFICATE_STATUS_APPROVED:
                        fontColor = "#69FA72";
                        break;
                    case buildspace.constants.CLAIM_CERTIFICATE_STATUS_REJECTED:
                        fontColor = "#cc1313";
                        break;
                    default:
                        fontColor = "#F7D76D";
                }
                var formContainer = new dijit.layout.BorderContainer({
                    title: nls.claimCertificate+ " :: "+claimCertificateObj.version+" ( <span style='color:"+fontColor+"!important;'>"+claimCertificateObj.status_txt+"</span> )",
                    id: 'claimCertificatePage-' + this.project.id,
                    region: "center",
                    style: "padding:0;width:100%;margin:0;border:none;height:100%;",
                    gutters: false
                });

                var claimCertificateForm = new ClaimCertificateViewForm({
                    claimCertificate: claimCertificateObj,
                    claimCertificateContainer: this,
                    region: "center",
                    style: "width:100%;border:none;height:100%;overflow:auto;"
                });

                formContainer.addChild(claimCertificateForm);

                container.addChild(formContainer);

                container.selectChild('claimCertificatePage-' + this.project.id);
            }
        },
        removeStackContainerChildren: function(){
            var container = dijit.byId('postContractClaimCertificate' + this.project.id + '-stackContainer');

            if (container) {
                var page = dijit.byId('claimCertificatePage-' + this.project.id);
                if (page) {
                    container.removeChild(page);
                    page.destroyRecursive();
                }
            }
        },
        closeBillTabs: function() {
            return lang.hitch(this.workArea, 'removeBillTab')();
        }
    });
});
