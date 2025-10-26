define('buildspace/apps/ProjectManagement/ClaimUpdateDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    'dojo/currency',
    'dojo/number',
    "dojo/dom-style",
    "dojox/grid/EnhancedGrid",
    'buildspace/widget/grid/cells/Textarea',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ProjectManagement'
], function(declare, lang, html, dom, keys, currency, number, domStyle, EnhancedGrid, cellTextarea, GridFormatter, nls){

    var ClaimUpdateGrid = declare('buildspace.apps.ProjectManagement.ClaimUpdateGrid', EnhancedGrid, {
        escapeHTMLInData: false,
        style: "border-top:none;",
        keepSelection: true,
        scheduleTaskItemBillItem: null,
        tagBillItemGrid: null,
        type: null,
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this,
                item = this.getItem(rowIdx),
                store = this.store,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            if(val !== item[inAttrName][0] && item.id > 0){
                var params = {
                    id: item.id[0],
                    attr_name: inAttrName,
                    val: val,
                    t: this.type == 'up_to_date_claim_percentage' ? 'p' : 'a',
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = "projectManagement/updateClaimByUnit";

                var cell = this.getCellByField(inAttrName);

                pb.show();
                dojo.xhrPost({
                    url: url,
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            if(self.tagBillItemGrid){
                                self.tagBillItemGrid.store.save();
                                self.tagBillItemGrid.store.close();
                                self.tagBillItemGrid.sort();
                            }
                            pb.hide();
                        }

                        window.setTimeout(function() {
                            self.focus.setFocusIndex(rowIdx, cell.index);
                        }, 10);
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            }

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        canEdit: function(inCell, inRowIndex){
            if(inCell != undefined){
                var item = this.getItem(inRowIndex);
                if (item && item.id > 0){
                    return this._canEdit;
                }
            }
            return false;
        }
    });

    return declare('buildspace.apps.ProjectManagement.ClaimUpdateDialog', dijit.Dialog, {
        style:"padding:0;margin:0;",
        scheduleTaskItemBillItem: null,
        tagBillItemGrid: null,
        type: null,
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
                style:"padding:0;margin:0;width:480px;height:280px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border-bottom:none;"
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

            var CustomCellFormatter = {
                claimAmountCellFormatter: function(cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx),
                        formattedValue,
                        val = '&nbsp;';

                    if(item.hasOwnProperty('value') && item.id > 0){
                        var value = number.parse(item.value[0]);
                        if(isNaN(value) || value == 0 || value == null){
                            formattedValue = "&nbsp;";
                        }else{
                            formattedValue = currency.format(value);
                        }

                        val = value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';

                        if(item.id == buildspace.constants.GRID_LAST_ROW ){
                            cell.customClasses.push('disable-cell');
                            val = '&nbsp;';
                        }
                    }
                    return val;
                },
                claimPercentageCellFormatter: function(cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx),
                        val = '&nbsp;';

                    if (item && item.hasOwnProperty('type') && item.type < 1){
                        cell.customClasses.push('invalidTypeItemCell');
                    }

                    if(item.hasOwnProperty('value') && item.id > 0){
                        var value = number.parse(item.value[0]);
                        if(isNaN(value) || value == 0 || value == null){
                            val = "&nbsp;";
                        }else{
                            var formattedValue = number.format(value, {places:2})+"%";
                            val = value >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
                        }

                        if(item.id == buildspace.constants.GRID_LAST_ROW ){
                            cell.customClasses.push('disable-cell');
                            val = '&nbsp;';
                        }
                    }

                    return val;
                }
            };

            var t = this.type == 'up_to_date_claim_percentage' ? 'p' : 'a';
            borderContainer.addChild(ClaimUpdateGrid({
                region: "center",
                scheduleTaskItemBillItem: this.scheduleTaskItemBillItem,
                tagBillItemGrid: this.tagBillItemGrid,
                type: this.type,
                store: dojo.data.ItemFileWriteStore({
                    url: "projectManagement/getTaggedUnitList/id/"+this.scheduleTaskItemBillItem.id+"/t/"+t,
                    clearOnClose: true
                }),
                structure: [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.typeListTreeCellFormatter },
                    {
                        field:'value',
                        name: this.type == 'up_to_date_claim_percentage' ? '%' : nls.amount,
                        width: this.type == 'up_to_date_claim_percentage' ? '70px' : '120px',
                        styles: "text-align:right;",
                        editable: true,
                        cellType:'buildspace.widget.grid.cells.Textarea',
                        formatter: this.type == 'up_to_date_claim_percentage' ? CustomCellFormatter.claimPercentageCellFormatter : CustomCellFormatter.claimAmountCellFormatter
                    }
                ]
            }));

            return borderContainer;
        }
    });
});