define('buildspace/apps/PostContract/RequestForVariationClaim/RequestForVariationClaimGrid',[
    'dojo/_base/declare',
    "dojo/_base/connect",
    'dojo/_base/lang',
    'dojo/_base/html',
    "dojo/dom-style",
    "dojo/number",
    "dijit/focus",
    'dojo/_base/event',
    'dojo/keys',
    "dojo/has",
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/plugins/FormulatedColumn",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, connect, lang, html, domStyle, number, focusUtil, evt, keys, has, EnhancedGrid, FormulatedColumn, GridFormatter, nls) {

    var Grid = declare('buildspace.apps.PostContract.RequestForVariationClaim.RequestForVariationClaimEnhancedGrid', EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        selectedItem: null,
        region: 'center',
        project: null,
        locked: false,
        keepSelection: true,
        constructor:function(args){
            this.connects = [];
            this.formulatedColumn = FormulatedColumn(this,{});
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
            
            if(!this.locked && this.type == 'requestForVariationClaimItems'){
                var self = this;
                this.on('RowClick', function(e){
                    if(e.cell){
                        var item = this.getItem(e.rowIndex),
                        colField = e.cell.field;

                        if(item && !isNaN(parseInt(String(item.id))) && !item.claim_cert_number[0] && colField=='claim_cert_number'){
                            var pb = buildspace.dialog.indeterminateProgressBar({
                                title:nls.pleaseWait+'...'
                            });

                            pb.show().then(function(){
                                dojo.xhrGet({
                                    url: "requestForVariationClaim/getOpenClaimCertificate",
                                    content: {
                                        pid: self.project.id
                                    },
                                    handleAs: 'json',
                                    load: function(data) {
                                        var id = parseInt(String(data.id));
                                        pb.hide();
                                        if(!isNaN(id) && id > 0 ){
                                            var content = '<div>'+nls.requestForVariationClaimAttachClaimCertConfirm+'<br /><br /><b>Claim Certificate No.</b> : '+data.version+'</div>';
                                            buildspace.dialog.confirm(nls.confirmation,content,120,380, function(){
                                                self.attachClaimCertificate(id, parseInt(String(item.id)))
                                            });
                                        }else{
                                            buildspace.dialog.alert(nls.noInProgressClaimCertificate, nls.noInProgressClaimCertificateMsg, 100, 300);
                                        }
                                    },
                                    error: function(error) {
                                        pb.hide();
                                    }
                                });
                            });
                        }
                    }
                });
            }
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
        canEdit: function(inCell, inRowIndex){
            var self = this;
            if(inCell != undefined){
                var item = this.getItem(inRowIndex);
                if(this.locked || (this.type=="requestForVariationClaimItems" && item && !isNaN(parseInt(String(item.id))) && !item.can_claim[0])){
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

            var attrNameParsed = inAttrName.replace("-value","");//for any formulated column
            if(inAttrName.indexOf("-value") !== -1){
                val = this.formulatedColumn.convertRowIndexToItemId(val, rowIdx);
            }

            if(!this.locked && item && !isNaN(parseInt(String(item.id))) && item.can_claim[0] && val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    attr_name: attrNameParsed,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                };

                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: "requestForVariationClaim/claimItemUpdate",
                        content: params,
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                for(var property in resp.data){
                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(item, property, resp.data[property]);
                                    }
                                }
                                store.save();

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
        editableCellDblClick: function(e){
            var event;
            if(this._click.length > 1 && has('ie')){
                event = this._click[1];
            }else if(this._click.length > 1 && this._click[0].rowIndex != this._click[1].rowIndex){
                event = this._click[0];
            }else{
                event = e;
            }
            this.focus.setFocusCell(event.cell, event.rowIndex);
            this.onRowClick(event);
            this.onRowDblClick(e);
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
        attachClaimCertificate: function(claimCertificateId, itemId){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            var store = this.store;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'requestForVariationClaim/claimCertificateAttach',
                    content: {
                        cid: claimCertificateId,
                        id: itemId
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            store.fetchItemByIdentity({ 'identity' : resp.item.id,  onItem : function(item){
                                for(var property in resp.item){
                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(item, property, resp.item[property]);
                                    }
                                }
                                store.save();
                            }});

                            pb.hide();
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.PostContract.RequestForVariationClaim.RequestForVariationClaimGrid', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        project: null,
        requestForVariationClaim: null,
        gridOpts: {},
        locked: false,
        type: null,
        pageId: 0,
        postCreate: function(){
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                type: this.type,
                project: this.project,
                requestForVariationClaim: this.requestForVariationClaim,
                locked: this.locked
            });

            var grid = this.grid = new Grid(this.gridOpts);
            
            this.addChild(grid);

            var container = dijit.byId('requestForVariationClaim-'+this.project.id+'-stackContainer');
            if(container){
                container.addChild(new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    content: this,
                    grid: grid,
                    id: this.pageId,
                    executeScripts: true
                }));
                container.selectChild(this.pageId);
            }
        }
    });
});
