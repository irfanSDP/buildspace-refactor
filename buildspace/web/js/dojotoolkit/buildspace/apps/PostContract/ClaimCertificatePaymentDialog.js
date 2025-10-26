define('buildspace/apps/PostContract/ClaimCertificatePaymentDialog',[
    'dojo/_base/declare',
    'dojo/aspect',
    'dijit/focus',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/dom",
    'dojo/keys',
    "dojo/_base/array",
    "dojo/dom-style",
    'dojo/currency',
    'dojo/number',
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, aspect, focusUtil, lang, connect, dom, keys, array, domStyle, currency, number, EnhancedGrid, GridFormatter, nls){

    var Grid = declare('buildspace.apps.PostContract.ClaimCertificatePaymentGrid', EnhancedGrid, {
        claimCertificate: null,
        claimCertificatesGrid: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = this.getItem(rowIdx), store = this.store;

            if(String(val) !== String(item[inAttrName])){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });

                var params = {
                    id: item.id,
                    attr_name: inAttrName,
                    val: val,
                    cid : this.claimCertificate.id,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                };

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
                        url: "postContract/claimCertificatePaymentUpdate",
                        content: params,
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                if(!isNaN(parseInt(item.id[0]))){
                                    dojo.forEach(resp.items, function(item){
                                        updateCell(item, store);
                                    });
                                }else{
                                    store.deleteItem(item);
                                    store.save();

                                    dojo.forEach(resp.items, function(item){
                                        store.newItem(item);
                                    });
                                    store.save();
                                }

                                self.claimCertificatesGrid.reload();

                                var cell = self.getCellByField(inAttrName);

                                window.setTimeout(function() {
                                    self.selection.select(rowIdx);
                                    self.focus.setFocusIndex(rowIdx, cell.index);
                                }, 10);

                                pb.hide();
                            }
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
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

    return declare('buildspace.apps.PostContract.ClaimCertificatePaymentDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.claimCertificatePayments,
        claimCertificate: null,
        claimCertificatesGrid: null,
        refocus: false,
        autofocus: false,
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
                style:"padding:0;margin:0;width:980px;height:450px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border:none;"
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

            var formatter = new GridFormatter();

            var grid = new Grid({
                claimCertificate: this.claimCertificate,
                claimCertificatesGrid: this.claimCertificatesGrid,
                store: dojo.data.ItemFileWriteStore({
                    url: "postContract/getClaimCertificatePayments/cid/"+this.claimCertificate.id,
                    clearOnClose: true
                }),
                structure: [
                    {name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.paidAmount, field: 'amount', width:'150px', styles:'text-align:right;', formatter: formatter.currencyCellFormatter, editable: true, cellType: 'buildspace.widget.grid.cells.TextBox'},
                    {name: nls.remarks, field: 'remarks', width:'auto', styles:'text-align:left;', cellType: 'buildspace.widget.grid.cells.TextBox', editable: true},
                    {name: nls.created_at, field: 'created_at', width:'120px', styles:'text-align:center;'}
                ]
            });

            borderContainer.addChild(grid);

            return borderContainer;
        }
    });
});