define('buildspace/apps/Tendering/checkPublishRequirementDialog',[
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
    "buildspace/widget/grid/cells/Formatter",
    "dijit/form/DropDownButton",
    "dijit/Menu",
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dijit/PopupMenuItem",
    'buildspace/apps/AssignUser/assignGroupProjectGrid',
    'buildspace/apps/Tendering/newPostContractFormDialog',
    'dojo/i18n!buildspace/nls/CheckPublishRequirement'
], function(declare, lang, connect, when, html, dom, keys, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ContentPane, GridFormatter, DropDownButton, Menu, DropDownMenu, MenuItem, PopupMenuItem, AssignGroupProjectGrid, NewPostContractFormDialog, nls){

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return cellValue > 0 ? cellValue : '';
        },
        treeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            var level = parseInt(String(item.level))*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;
            if(item.type < buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        }
    };

    var CheckPublishRequirementGrid = declare('buildspace.apps.Tendering.CheckPublishRequirementGrid', dojox.grid.EnhancedGrid, {
        type: null,
        selectedItem: null,
        dialogWidget: null,
        escapeHTMLInData: false,
        gridData: null,
        style: "border-top:none;",
        constructor: function(args){
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
        },
        destroy: function(){
            this.inherited(arguments);
        }
    });

    var CheckPublishRequirementGridContainer = declare('buildspace.apps.Tendering.CheckPublishRequirementGridContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        region: "center",
        gridOpts: {},
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, { region:"center" });
            var grid = this.grid = new CheckPublishRequirementGrid(this.gridOpts);

            this.addChild(grid);
        }
    });

    return declare('buildspace.apps.Tendering.CheckPublishRequirementDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.checkPublishRequirement,
        rootProject: null,
        builderObj: null,
        tempPublishToPostContractOptions: {},
        assignGroupProjectGridSavedState: false,
        data: null,
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
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            dojo.forEach(this._connects, connect.disconnect);
            this.destroyRecursive();
        },
        createForm            : function(){
            var self = this;

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:680px;height:240px;",
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
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.assignUsersForPostContract,
                    iconClass: "icon-16-container icon-16-add",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'assignUserPermission')
                })
            );

            var postContractItemList = new Menu();

            // Contractor Rates.
            var contractorRatesItemList = new Menu();

            contractorRatesItemList.addChild(new MenuItem({
                label: nls.withNotListedItem,
                onClick: function(){
                    self.createPostContractForm(false, false);
                }
            }));
            contractorRatesItemList.addChild(new MenuItem({
                label: nls.withoutNotListedItem,
                onClick: function(){
                    self.createPostContractForm(false, true);
                }
            }));

            postContractItemList.addChild(new PopupMenuItem({
                label: (parseInt(String(this.rootProject.tender_type_id)) == buildspace.constants.TENDER_TYPE_TENDERED) ? nls.useContractorRates : nls.useRationalizedRate,
                disabled: (self.data.requirementSuccess) ? false : true,
                popup: contractorRatesItemList
            }));

            // Estimate Rates.
            var estimateRatesItemList = new Menu();

            estimateRatesItemList.addChild(new MenuItem({
                label: nls.withNotListedItem,
                onClick: function(){
                    self.createPostContractForm(true, false);
                }
            }));
            estimateRatesItemList.addChild(new MenuItem({
                label: nls.withoutNotListedItem,
                onClick: function(){
                    self.createPostContractForm(true, true);
                }
            }));

            postContractItemList.addChild(new PopupMenuItem({
                label: parseInt(String(this.rootProject.tender_type_id)) == buildspace.constants.TENDER_TYPE_TENDERED ? nls.useEstimationRate : nls.useOriginalRate,
                disabled: (self.data.requirementSuccess) ? false : true,
                popup: estimateRatesItemList
            }));

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new DropDownButton({
                    label: nls.publish,
                    iconClass: "icon-16-container icon-16-export",
                    style:"outline:none!important;",
                    dropDown: postContractItemList
                })
            );

            var msgBorderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:400px;height:100px;",
                gutters: false,
                region: 'top'
            });

            var msgContainer = new ContentPane({
                content: nls.checkRequirementMsg,
                region: "center",
                style:"padding:20px 30px 20px 30px; text-align:center"
            });

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    data: self.data.items
                });

            var content = CheckPublishRequirementGridContainer({
                rootProject: self.rootProject,
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    structure: [
                        {name: 'No.', field: 'count', width:'30px', styles:'text-align:center;', formatter: Formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'notice', width:'auto', styles:'padding:3px 3px 3px 3px;' },
                        {name: nls.status, field: 'success', styles:'text-align:center;', width:'70px', formatter: formatter.boolIconCellFormatter }
                    ]
                }
            });

            msgBorderContainer.addChild(toolbar);
            msgBorderContainer.addChild(msgContainer);

            borderContainer.addChild(msgBorderContainer);
            borderContainer.addChild(content);

            return borderContainer;
        },
        assignUserPermission  : function() {
            var self = this;
            var assignGroupProjectGrid = new AssignGroupProjectGrid( {
                rootProject: this.rootProject,
                sysName: 'PostContract',
                projectStatus: buildspace.constants.USER_PERMISSION_STATUS_POST_CONTRACT
            } );
            assignGroupProjectGrid.show();
            assignGroupProjectGrid.selectGroup();

            this._connects.push(connect.connect(assignGroupProjectGrid, 'save', function(){
                self.assignGroupProjectGridSavedState = true;
            }));
            this._connects.push(connect.connect(assignGroupProjectGrid, 'isAdminUpdate', function(){
                self.assignGroupProjectGridSavedState = true;
            }));
        },
        createPostContractForm: function(useOriginalRate, withoutNotListedItem){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "tendering/checkIfCanSubmitNewPostContractForm/",
                    content: { project_id: self.rootProject.id },
                    handleAs: "json"
                }).then(function(resp){
                    if(resp.canSubmit){
                        self.tempPublishToPostContractOptions = {
                            useOriginalRate: useOriginalRate,
                            withoutNotListedItem: withoutNotListedItem,
                            postContractType: buildspace.constants.POST_CONTRACT_TYPE_NEW
                        };

                        var dialog = new NewPostContractFormDialog({
                            rootProject: self.rootProject,
                            useOriginalRate: useOriginalRate,
                            withoutNotListedItem: withoutNotListedItem,
                            checkPublishRequirementDialog: self
                        });
                        dialog.show();
                    }else{
                        buildspace.dialog.alert(nls.warning, resp.errorMessage, 80, 320, function(){});
                    }
                    pb.hide();
                });
            });
        },
        sendNewPostContractFormForApproval: function(){
            var self = this,
                builderObj = self.builderObj,
                projectBreakdown = dijit.byId('main-project_breakdown'),
                project = self.rootProject,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.publishing+'. '+nls.pleaseWait+'...'
                });

            self.hide();

            var useOriginalRate = self.tempPublishToPostContractOptions.useOriginalRate;
            var withoutNotListedItem = self.tempPublishToPostContractOptions.withoutNotListedItem;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: "tendering/sendNewPostContractFormForApproval",
                    content: {
                        id: project.id,
                        usersAssignedManually: self.assignGroupProjectGridSavedState,
                        use_original_rate: useOriginalRate,
                        withoutNotListedItem : withoutNotListedItem
                    },
                    handleAs: 'json',
                    load: function(resp) {

                        projectBreakdown.grid.disableButtonsAfterPublish();

                        projectBreakdown.rootProject.tendering_module_locked = [resp.tendering_module_locked];

                        builderObj.disableButtonsAfterPublish();

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        }
    });
});
