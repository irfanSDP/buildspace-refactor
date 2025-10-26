define('buildspace/apps/PostContract/DebitCreditNote/DebitCreditNoteContainer', [
    'dojo/_base/declare',
    'buildspace/widget/grid/Filter',
    "dijit/layout/ContentPane",
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    './DebitCreditNoteClaim',
    './DebitCreditNoteClaimItem',
    'dojo/i18n!buildspace/nls/DebitCreditNote',
],
function (declare, FilterToolbar, ContentPane, EnhancedGrid, GridFormatter, DebitCreditNoteClaim, DebitCreditNoteClaimItem, nls) {
    var AccountGroupSelectionGrid = declare('buildspace.apps.PostContract.DebitCreditNote.AccountGroupSelectionGrid', EnhancedGrid, {
        rootProject: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        constructor: function() {
            this.inherited(arguments);
        },
        canSort: function (inSortInfo) {
            return false;
        },
        postCreate: function() {
            this.inherited(arguments);
        },
        dodblclick: function (e) {
            this.onRowDblClick(e);
        },
        reloadGrid: function() {
            this.store.save();
            this.store.close();
            this._refresh();
        }
    });

    return declare('buildspace.apps.PostContract.DebitCreditNote.DebitCreditNoteContainer', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0px;border:none;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        stackContainer: null,
        claimCertificate: null,
        forVerification: null,
        locked: false,
        debitCreditNoteClaimGrid: null,
        createAccountGroupSelectGridURL: function() {
            var url = "debitCreditNote/getAccountGroups/projectStructureId/" + String(this.rootProject.id);

            if(this.claimCertificate) {
                url += "/postContractClaimRevisionId/" + this.claimCertificate.post_contract_claim_revision_id;
            }
            
            return url;
        },
        postCreate: function () {
            this.inherited(arguments);
            this.createAccountGroupSelectGrid();
        },
        createAccountGroupSelectGrid: function() {
            var self = this;

            var formatter = new GridFormatter();

            this.stackContainer = dijit.byId('debitCreditNote-' + String(this.rootProject.id) + '-stackContainer');

            if (this.stackContainer) {
                dijit.byId('debitCreditNote-' + String(this.rootProject.id) + '-stackContainer').destroyRecursive();
            } 

            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: 'debitCreditNote-' + String(self.rootProject.id) + '-stackContainer',
            });

            var accountGroupSelectionGrid = this.AccountGroupSelectionGrid = new AccountGroupSelectionGrid({
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: self.createAccountGroupSelectGridURL(),
                }),
                structure: [
                    { name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    { name: nls.accountGroup, field: 'name', editable: false, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'auto', formatter:formatter.disabledCellFormatter },
                    { name: nls.amount, field: 'amount', editable: false, cellType: 'buildspace.widget.grid.cells.Textarea', noresize: true, width:'160px', styles:'text-align:right;', formatter : formatter.unEditableCurrencyCellFormatter },
                ],
                onRowDblClick: dojo.hitch(this, 'createDebitCreditNoteClaimWindow'),
            });

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'debitCreditNote-' + String(self.rootProject.id) + '-stackContainer'
            });

            var controllerPane = new ContentPane({
                style: "padding:0px;overflow:hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                id: 'debitCreditNote-' + String(self.rootProject.id) + '-controllerPane',
                content: controller
            });

            var filterToolbar  = new FilterToolbar({
                grid: accountGroupSelectionGrid,
                region:"top",
                filterFields: [ {name : nls.accountGroup}]
            });

            var gridContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                baseClass: 'form',
                gutters: false,
                region: "center"
            });

            gridContainer.addChild(filterToolbar);
            gridContainer.addChild(accountGroupSelectionGrid);

            var stackPane = new dijit.layout.ContentPane({
                title: nls.accountGroup,
                content: gridContainer
            });

            this.stackContainer.addChild(stackPane);

            self.addChild(controllerPane);
            self.addChild(this.stackContainer);

            dojo.subscribe('debitCreditNote-' + String(self.rootProject.id) + '-stackContainer-selectChild', "", function(page){
                var widget = dijit.byId('debitCreditNote-' + String(self.rootProject.id) + '-stackContainer');
                if(widget){
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page.id));
                    index = index+1;
                    while( children.length > index ){
                        widget.removeChild(children[ index ]);
                        children[ index ].destroyRecursive(true);
                        index = index + 1;
                    }
                }
            });
        },
        createDebitCreditNoteClaimWindow: function(e) {
            var self = this;
            var item = this.AccountGroupSelectionGrid.getItem(e.rowIndex);

            if(item.disable == 'No'){
                if(!isNaN(String(item.id))) {
                    var debitCreditNoteClaim = new DebitCreditNoteClaim({
                        projectId: String(self.rootProject.id),
                        accountGroupId: String(item.id),
                        claimCertificate: self.claimCertificate,
                        locked: self.locked,
                        onRowDblClick: function (e) {
                            var me = this;
                            var item = me.getItem(e.rowIndex);
                            self.createDebitCreditNoteClaimItemWindow(item);
                        },
                    });
    
                    self.debitCreditNoteClaimGrid = debitCreditNoteClaim.DebitCreditClaimGrid;
    
                    var stackPane = new ContentPane({
                        title: nls.debitCreditNoteClaims,
                        content: debitCreditNoteClaim,
                    });
    
                    this.stackContainer.addChild(stackPane);
                    this.stackContainer.selectChild(stackPane);
                }
            }
        },
        createDebitCreditNoteClaimItemWindow: function(item) {
            var self = this;

            if(!isNaN(String(item.id))) {
                var debitCreditNoteClaimId = String(item.id);
                var accountGroupId = String(item.account_group_id);

                var debitCreditNoteClaimItem = new DebitCreditNoteClaimItem({
                    accountGroupId: accountGroupId,
                    accountGroupSelectionGrid: self.AccountGroupSelectionGrid,
                    debitCreditNoteClaimGrid: self.debitCreditNoteClaimGrid,
                    debitCreditNoteClaimId: debitCreditNoteClaimId,
                    locked: item.locked[0],
                    lockedForVerify: self.lock,
                });

                var stackPane = new ContentPane({
                    title: nls.debitCreditNoteClaimItems,
                    content: debitCreditNoteClaimItem,
                });

                this.stackContainer.addChild(stackPane);
                this.stackContainer.selectChild(stackPane);
            }
        }
    });
});