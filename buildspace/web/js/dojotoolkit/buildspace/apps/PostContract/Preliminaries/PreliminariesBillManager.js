define('buildspace/apps/PostContract/Preliminaries/PreliminariesBillManager',[
	'dojo/_base/declare',
	"dojo/dom-style",
	"dojo/when",
	"dojo/currency",
	"dijit/layout/AccordionContainer",
	"dijit/layout/ContentPane",
	'dojox/grid/EnhancedGrid',
	"buildspace/widget/grid/cells/Formatter",
    "buildspace/apps/ProjectBuilder/BillManager/ScheduleOfQuantityGrid",
    'dojo/aspect',
	'dojo/request',
	'./PreliminariesBillGrid',
	'./buildUpQuantitySummary',
	'./buildUpQuantityGrid',
	'./TimeBasedFormDialog',
	'./WorkBasedFormDialog',
	'./lumpSumPercentDialog',
	'dojo/i18n!buildspace/nls/PostContract'],
	function(declare, domStyle, when, currency, AccordionContainer, ContentPane, EnhancedGrid, GridFormatter, ScheduleOfQuantityGrid, aspect, request, PreliminariesBillGrid, BuildUpQuantitySummary, BuildUpQuantityGrid, TimeBasedFormDialog, WorkBasedFormDialog, LumpSumPercentDialog, nls) {

	return declare('buildspace.apps.PostContract.PrelimBillManager', dijit.layout.BorderContainer, {
		style: "padding:0;margin:0;border:none;width:100%;height:100%;",
		gutters: false,
		billId: null,
		rootProject: null,
		bqVersion: 0,
		columnData: null,
		currentBillType: null,
		currentSelectedClaimRevision: null,
		currentClaimRevision: null,
		gridEditable: true,
		postCreate: function() {
            this.inherited(arguments);
            var self = this;

            this.createElementGrid();

            dojo.subscribe('billGrid' + self.billId + '-stackContainer-selectChild', "", function(page) {
                var widget = dijit.byId('billGrid' + self.billId + '-stackContainer');
                if(widget) {
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page));

                    index = index + 1;

                    if(children.length > index){
                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();

                            index = index + 1;
                        }

                        if(page.grid){
                            var selectedIndex = page.grid.selection.selectedIndex;

                            page.grid.store.save();
                            page.grid.store.close();

                            var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                                handle.remove();
                                if(selectedIndex > -1){
                                    this.scrollToRow(selectedIndex);
                                    this.selection.setSelected(selectedIndex, true);
                                }
                            });

                            page.grid.sort();
                        }
                    }
                }
            });
		},
		createElementGrid: function() {
			var self = this;

			var stackContainer = dijit.byId('billGrid' + this.billId + '-stackContainer');

			if(stackContainer) {
				dijit.byId('billGrid' + this.billId + '-stackContainer').destroyRecursive();
			}

			stackContainer = this.stackContainer = new dijit.layout.StackContainer({
				style: 'border:0px;width:100%;height:100%;',
				region: "center",
				id: 'billGrid' + this.billId + '-stackContainer'
			});

			var billInfoQuery = dojo.xhrGet({
				url: "postContract/getBillInfo",
				handleAs: "json",
				content: {
					id: this.billId
				}
			}),
			me = this;

			billInfoQuery.then(function(billInfo) {
				try {
					var url = 'postContractPreliminaries/getElementList';

					me.currentBillType              = billInfo.bill_type.type;
					me.currentSelectedClaimRevision = billInfo.current_selected_claim_project_revision_status;
					me.currentClaimRevision         = billInfo.claim_project_revision_status;

					if ( me.currentClaimRevision.locked_status || me.currentClaimRevision.id != me.currentSelectedClaimRevision.id ) {
						me.gridEditable = false;
					}

					var store = dojo.data.ItemFileWriteStore({
						url: url + '/id/' + self.billId,
						clearOnClose: true,
						urlPreventCache: true
					});

					var grid = new PreliminariesBillGrid({
						stackContainerTitle: "Element",
						billId: me.billId,
						rootProject: me.rootProject,
						pageId: 'element-page-' + me.billId,
						id: 'element-page-container-' + me.billId,
						gridOpts: {
							store: store,
							typeColumns : billInfo.column_settings,
							markupSettings: billInfo.markup_settings,
							bqCSRFToken: billInfo.bqCSRFToken,
							currentBillType: me.currentBillType,
							currentSelectedClaimRevision: me.currentSelectedClaimRevision,
							currentClaimRevision: me.currentClaimRevision,
							currentGridType: 'element',
							gridEditable: me.gridEditable,
							onRowDblClick: function(e) {
								var self = this,
									item = self.getItem(e.rowIndex);
								if(item.id[0] > 0 && item.description[0] !== null && item.description[0] !== '') {
									me.createItemGrid(item, billInfo, grid);
								}
							}
						}
					});

					var controller = new dijit.layout.StackController({
						region: "top",
						containerId: 'billGrid' + me.billId + '-stackContainer'
					});

					var controllerPane = new dijit.layout.ContentPane({
						style: "padding:0px;overflow:hidden;",
						baseClass: 'breadCrumbTrail',
						region: 'top',
						id: 'billGrid'+me.billId+'-controllerPane',
						content: controller
					});

					me.addChild(stackContainer);
					me.addChild(controllerPane);
				}
				catch(e){
					console.debug(e);
				}
			});
		},
		createItemGrid: function(element, billInfo, elementGridStore) {
			var url = 'postContractPreliminaries/getItemList', self = this,
				hierarchyTypes = {
					options: [
						buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR_TEXT,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL_TEXT,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE_TEXT,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY_TEXT,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED_TEXT,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_TEXT,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT_TEXT,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE_TEXT,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID_TEXT
					],
					values: [
						buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE,
						buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID
					]
				},
				hierarchyTypesForHead = {
					options: [
						buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT
					],
					values: [
						buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER
					]
				},
				unitQuery = dojo.xhrGet({
					url: "billManager/getUnits/billId/"+ self.billId,
					handleAs: "json"
				}),
				pb = buildspace.dialog.indeterminateProgressBar({
					title:nls.pleaseWait+'...'
				}),
				typeColumns;

			pb.show();

			unitQuery.then(function(uom){
				return uom;
			});

			var store = new dojo.data.ItemFileWriteStore({
				url: url + '/id/' + element.id + "/bill_id/" + self.billId,
				clearOnClose: true
			});

			return when(unitQuery, function(uom){
				pb.hide();
				try{
					var grid = new PreliminariesBillGrid({
						stackContainerTitle: element.description,
						billId: self.billId,
						rootProject: self.rootProject,
						id: 'item-page-container-' + self.billId,
						elementId: element.id,
						pageId: 'item-page-' + self.billId,
						type: 'tree',
						gridOpts: {
							store: store,
							escapeHTMLInData: false,
							typeColumns : billInfo.column_settings,
							markupSettings: billInfo.markup_settings,
							elementGridStore: elementGridStore,
							hierarchyTypes: hierarchyTypes,
							hierarchyTypesForHead: hierarchyTypesForHead,
							unitOfMeasurements: uom,
							currentBillType: self.currentBillType,
							currentSelectedClaimRevision: self.currentSelectedClaimRevision,
							currentClaimRevision: self.currentClaimRevision,
							currentGridType: 'item',
							gridEditable: self.gridEditable,
							onRowDblClick: function(e) {
								var colField = e.cell.field,
									rowIndex = e.rowIndex,
									item = this.getItem(rowIndex),
									billGridStore = this.store,
									separatedFieldName = colField.split('-'),
									lockLumpSumFormInput = (self.gridEditable) ? false : true;

								if (item.id[0] > 0) {
									if ( item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT && colField === 'rate' ) {
										if ( item['previousClaim-amount'][0] != 0 ) {
											lockLumpSumFormInput = true;
										}

										var lumpSumPercentDialog = new LumpSumPercentDialog({
											itemObj: item,
											billGridStore: billGridStore,
											elementGridStore: elementGridStore,
											disableEditingMode: lockLumpSumFormInput
										});

										return lumpSumPercentDialog.show();
									} else if ( colField === 'qty-qty_per_unit' && item['qty-has_build_up'][0] ) {
										var billColumnSettingId = item['qty-column_id'][0];

										var dimensionColumnQuery = dojo.xhrPost({
											url: "billBuildUpQuantity/getDimensionColumnStructure",
											content:{uom_id: item.uom_id[0]},
											handleAs: "json"
										});

										var pb = buildspace.dialog.indeterminateProgressBar({
											title:nls.pleaseWait+'...'
										});

										pb.show();

										dimensionColumnQuery.then(function(dimensionColumns){
											self.createBuildUpQuantityContainer(item, dimensionColumns, billColumnSettingId);
											pb.hide();
										});
									}

									if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID) {
										return;
									} else if ( separatedFieldName[0] != buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED && separatedFieldName[0] != buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED ) {
										return;
									}

									if ( separatedFieldName[0] == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED ) {
										self.timeBasedClaimDialog(item, self.currentClaimRevision, self.currentSelectedClaimRevision, billGridStore);
									} else if ( separatedFieldName[0] == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED ) {
										self.workBasedClaimDialog(item, self.currentClaimRevision, self.currentSelectedClaimRevision, billGridStore);
									}
								}
							}
						}
					});
				}catch(e){console.debug(e)}
			},function(error){
				/* got fucked */
			});
		},
        createBuildUpQuantityContainer: function(item, dimensionColumns, billColumnSettingId) {
            var moduleName = 'billBuildUpQuantity';

            var self = this,
                scheduleOfQtyGrid,
                type = buildspace.constants.QUANTITY_PER_UNIT_ORIGINAL,
                baseContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:100%;height:100%;border:0px;outline:none;",
                    gutters: false
                }),
                formatter = new GridFormatter(),
                tabContainer = new dijit.layout.TabContainer({
                    nested: true,
                    style: "padding:0;border:none;margin:0;width:100%;height:100%;",
                    region: 'center'
                }),
                store = new dojo.data.ItemFileWriteStore({
                    url: moduleName+"/getBuildUpQuantityItemList/bill_item_id/"+item.id+"/bill_column_setting_id/"+billColumnSettingId+"/type/"+type,
                    clearOnClose: true
                }),
                scheduleOfQtyQuery = dojo.xhrGet({
                    url: "billBuildUpQuantity/getLinkInfo/id/"+item.id+"/bcid/"+billColumnSettingId+"/t/"+type,
                    handleAs: "json"
                }),
                hasLinkedQty = false,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show();

            scheduleOfQtyQuery.then(function(linkInfo){
                var structure = [{
                    name: 'No',
                    field: 'id',
                    styles: "text-align:center;",
                    width: '30px',
                    formatter: formatter.rowCountCellFormatter
                }, {
                    name: nls.description,
                    field: 'description',
                    width: 'auto'
                },{
                    name: nls.factor,
                    field: 'factor-value',
                    width:'100px',
                    styles:'text-align:right;',
                    formatter: formatter.formulaNumberCellFormatter
                }];

                dojo.forEach(dimensionColumns, function(dimensionColumn){
                    var column = {
                        name: dimensionColumn.title,
                        field: dimensionColumn.field_name,
                        width:'100px',
                        styles:'text-align:right;',
                        formatter: formatter.formulaNumberCellFormatter
                    };
                    structure.push(column);
                });

                var totalColumn = {
                    name: nls.total,
                    field: 'total',
                    width:'100px',
                    styles:'text-align:right;',
                    formatter: formatter.numberCellFormatter
                };

                structure.push(totalColumn);

                var signColumn = {
                    name: nls.sign,
                    field: 'sign',
                    width: '70px',
                    styles: 'text-align:center;',
                    formatter: formatter.signCellFormatter
                };

                structure.push(signColumn);

                var buildUpSummaryWidget = new BuildUpQuantitySummary({
                    itemId: item.id,
                    billColumnSettingId: billColumnSettingId,
                    type: type,
                    hasLinkedQty: linkInfo.has_linked_qty,
                    container: baseContainer,
                    _csrf_token: item._csrf_token,
                    moduleName: moduleName,
                    editable: false
                });

                if(linkInfo.has_linked_qty){
                    hasLinkedQty = true;
                    scheduleOfQtyGrid = ScheduleOfQuantityGrid({
                        title: nls.scheduleOfQuantities,
                        BillItem: item,
                        billColumnSettingId: billColumnSettingId,
                        disableEditingMode: true,
                        stackContainerId: 'billGrid' + self.billId + '-stackContainer',
                        gridOpts: {
                            qtyType: type,
                            buildUpSummaryWidget: buildUpSummaryWidget,
                            store: new dojo.data.ItemFileWriteStore({
                                url:"billBuildUpQuantity/getScheduleOfQuantities/id/"+item.id+"/bcid/"+billColumnSettingId+"/type/"+type,
                                clearOnClose: true
                            }),
                            structure: [
                                {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                                {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                                {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                                {name: nls.qty, field: 'quantity-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaNumberCellFormatter}
                            ]
                        }
                    });
                }

                var manualGrid = new dojox.grid.EnhancedGrid({
                    title: nls.manualQtyItems,
                    region: 'center',
                    store: store,
                    structure: structure
                });

                tabContainer.addChild(manualGrid);

                if(hasLinkedQty){
                    tabContainer.addChild(scheduleOfQtyGrid);
                }

                baseContainer.addChild(tabContainer);
                baseContainer.addChild(buildUpSummaryWidget);

                var container = dijit.byId('billGrid' + self.billId + '-stackContainer');

                if(container) {
                    var node = document.createElement("div");
                    var child = new dojox.layout.ContentPane( {
                            title: buildspace.truncateString(item.description, 60)+' ('+nls.buildUpQuantity+' - '+item.uom_symbol+')',
                            id: 'buildUpQuantityPage-'+item.id,
                            style: "padding:0px;border:0px;",
                            content: baseContainer,
                            grid: hasLinkedQty ? scheduleOfQtyGrid.grid : null,
                            executeScripts: true },
                        node );
                    container.addChild(child);
                    container.selectChild('buildUpQuantityPage-'+item.id);
                }

                pb.hide();
            });
        },
		timeBasedClaimDialog: function(item, currentClaimRevision, currentSelectedClaimRevision, billGridStore) {
			var pb, self;
			self = this;

			pb = new buildspace.dialog.indeterminateProgressBar({
				title: "" + nls.processing + "..."
			});

			pb.show();

			request.get('postContractPreliminaries/getTimeBasedInformation', {
				query: {
					id: item.post_contract_bill_item_rate_id,
					bill_id: self.billId
				},
				handleAs: 'json'
			}).then(function(response) {
				var dialog = new TimeBasedFormDialog({
					itemId: item.post_contract_bill_item_rate_id,
					bill_id: self.billId,
					formInfo: response.form,
					item: item,
					billGridStore: billGridStore,
					currentClaimRevision: currentClaimRevision,
					gridEditable: self.gridEditable
				});

				dialog.show();

				return pb.hide();
			}, function(error) {
				return pb.hide();
			});
		},
		workBasedClaimDialog: function(item, currentClaimRevision, currentSelectedClaimRevision, billGridStore) {
			var pb, self;
			self = this;

			pb = new buildspace.dialog.indeterminateProgressBar({
				title: "" + nls.processing + "..."
			});

			pb.show();

			request.get('postContractPreliminaries/getWorkBasedInformation', {
				query: {
					id: item.post_contract_bill_item_rate_id,
					bill_id: self.billId
				},
				handleAs: 'json'
			}).then(function(response) {
				var dialog = new WorkBasedFormDialog({
					itemId: item.post_contract_bill_item_rate_id,
					bill_id: self.billId,
					formInfo: response.form,
					item: item,
					billGridStore: billGridStore,
					currentClaimRevision: currentClaimRevision,
					gridEditable: self.gridEditable
				});

				dialog.show();

				return pb.hide();
			}, function(error) {
				return pb.hide();
			});
		},
        reconstructBillContainer: function() {
            var controllerPane = dijit.byId('billGrid'+this.billId+'-controllerPane'),
                stackContainer = dijit.byId('billGrid'+this.billId+'-stackContainer');

            controllerPane.destroyRecursive();
            stackContainer.destroyRecursive();

            this.createElementGrid();
        }
	});
});