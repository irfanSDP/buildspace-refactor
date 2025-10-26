define('buildspace/apps/PostContractReport/Preliminaries/PreliminariesBillManager',[
	'dojo/_base/declare',
	"dojo/dom-style",
	"dojo/when",
	"dojo/currency",
	"dojo/store/Memory",
	"dijit/layout/AccordionContainer",
	"dijit/layout/ContentPane",
	'dojox/grid/EnhancedGrid',
	"buildspace/widget/grid/cells/Formatter",
	'dojo/aspect',
	'dojo/request',
	'./PreliminariesBillGrid',
	'./buildUpQuantitySummary',
	'./buildUpQuantityGrid',
	'./TimeBasedFormDialog',
	'./WorkBasedFormDialog',
	'./lumpSumPercentDialog',
	'dojo/i18n!buildspace/nls/PostContract'],
	function(declare, domStyle, when, currency, Memory, AccordionContainer, ContentPane, EnhancedGrid, GridFormatter, aspect, request, PreliminariesBillGrid, BuildUpQuantitySummary, BuildUpQuantityGrid, TimeBasedFormDialog, WorkBasedFormDialog, LumpSumPercentDialog, nls) {

	var PrelimBillElementContainer = declare('buildspace.apps.PostContractReport.PrelimBillElementContainer', dijit.layout.BorderContainer, {
		style: "padding:0px;border:0px;width:100%;height:100%;",
		gutters: false,
		billId: null,
		rootProject: null,
		bqVersion: 0,
		columnData: null,
		explorer: null,
		currentBillType: null,
		currentSelectedClaimRevision: null,
		currentClaimRevision: null,
		gridEditable: false,
		previewElementStore: null,
		previewItemStore: null,
		constructor: function(args) {
			this.previewElementStore = [];
			this.previewItemStore    = [];

			this.inherited(arguments);
		},
		postCreate: function() {
			this.inherited(arguments);
			var self = this;

			// create new memory object store to all the selected ids
			self.previewElementStore = new Memory({ idProperty: 'id' });
			self.previewItemStore    = new Memory({ idProperty: 'id' });

			self.createElementGrid();

			dojo.subscribe('postContractReportBillGrid' + self.billId + '-stackContainer-selectChild', "", function(page) {
				var widget = dijit.byId('postContractReportBillGrid' + self.billId + '-stackContainer');
				if(widget) {
					var children = widget.getChildren();
					var index = dojo.indexOf(children, dijit.byId(page.id));
					var pageIndex = 0,
						childLength = children.length;

					pageIndex = index = index + 1;

					while(children.length > index) {
						widget.removeChild(children[index]);
						children[index].destroyRecursive(true);
						index = index + 1;
						//remove any add-resource button from stack container if any
						var addResourceCatBtn = dijit.byId('add_resource_category_'+self.billId+'-btn');
						if(addResourceCatBtn)
							addResourceCatBtn.destroy();
					}
				}
			});
		},
		createElementGrid: function() {
			var self = this;

			var stackContainer = dijit.byId('postContractReportBillGrid' + this.billId + '-stackContainer');

			if(stackContainer) {
				dijit.byId('postContractReportBillGrid' + this.billId + '-stackContainer').destroyRecursive();
			}

			stackContainer = this.stackContainer = new dijit.layout.StackContainer({
				style: 'border:0px;width:100%;height:100%;',
				region: "center",
				id: 'postContractReportBillGrid' + this.billId + '-stackContainer'
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
						id: 'postContractReport-element-page-container-' + me.billId,
						gridOpts: {
							gridContainer: self,
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
						containerId: 'postContractReportBillGrid' + me.billId + '-stackContainer'
					});

					var controllerPane = new dijit.layout.ContentPane({
						style: "padding:0px;overflow:hidden;",
						baseClass: 'breadCrumbTrail',
						region: 'top',
						id: 'postContractReportBillGrid'+me.billId+'-controllerPane',
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
							gridContainer: self,
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
											self.createBuildUpQuantityContainer(item, dimensionColumns, billColumnSettingId, billGridStore);
											pb.hide();
										});
									}

									if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID) {
										return;
									} else if ( separatedFieldName[0] != buildspace.apps.PostContractReport.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED && separatedFieldName[0] != buildspace.apps.PostContractReport.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED ) {
										return;
									}

									if ( separatedFieldName[0] == buildspace.apps.PostContractReport.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED ) {
										self.timeBasedClaimDialog(item, self.currentClaimRevision, self.currentSelectedClaimRevision, billGridStore);
									} else if ( separatedFieldName[0] == buildspace.apps.PostContractReport.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED ) {
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
		createBuildUpQuantityContainer: function(item, dimensionColumns, billColumnSettingId, billGridStore) {
			var moduleName = 'billBuildUpQuantity';
			var itemId     = item.id;

			var self = this,
			type = buildspace.constants.QUANTITY_PER_UNIT_ORIGINAL,
			baseContainer = new dijit.layout.BorderContainer({
				style:"padding:0px;width:100%;height:100%;border:0px;outline:none;",
				gutters: false
			}),
			formatter = new GridFormatter(),
			store = new dojo.data.ItemFileWriteStore({
				url: moduleName+"/getBuildUpQuantityItemList/bill_item_id/"+itemId+"/bill_column_setting_id/"+billColumnSettingId+"/type/"+type,
				clearOnClose: true
			}),
			sign = {
				options: [
					buildspace.constants.SIGN_POSITIVE_TEXT,
					buildspace.constants.SIGN_NEGATIVE_TEXT
				],
				values: [
					buildspace.constants.SIGN_POSITIVE,
					buildspace.constants.SIGN_NEGATIVE
				]
			},
			buildUpSummaryWidget = new BuildUpQuantitySummary({
				itemId: itemId,
				billColumnSettingId: billColumnSettingId,
				type: type,
				container: baseContainer,
				billGridStore: billGridStore,
				buildUpGridStore: store,
				_csrf_token: item._csrf_token,
				moduleName: moduleName
			});

			try {
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

				var grid = new BuildUpQuantityGrid({
					region: 'center',
					billColumnSettingId: billColumnSettingId,
					BillItem: item,
					type: type,
					gridOpts: {
						addUrl: moduleName+'/buildUpQuantityItemAdd',
						updateUrl: moduleName+'/buildUpQuantityItemUpdate',
						deleteUrl: moduleName+'/buildUpQuantityItemDelete',
						pasteUrl: moduleName+'/buildUpQuantityItemPaste',
						store: store,
						structure: structure,
						buildUpSummaryWidget: buildUpSummaryWidget,
						currentBillLockedStatus: self.currentBillLockedStatus
					}
				});

				baseContainer.addChild(grid);

			}catch(e){console.log(e);}

			baseContainer.addChild(buildUpSummaryWidget);
			var container = dijit.byId('postContractReportBillGrid' + self.billId + '-stackContainer');

			if(container) {
				var node = document.createElement("div");
				var child = new dojox.layout.ContentPane( {
					title: buildspace.truncateString(item.description, 60)+' ('+nls.buildUpQuantity+' - '+item.uom_symbol+')',
					id: 'buildUpQuantityPage-'+itemId,
					style: "padding:0px;border:0px;",
					executeScripts: true },
				node );
				container.addChild(child);
				child.set('content', baseContainer);
				container.selectChild('buildUpQuantityPage-'+itemId);
			}
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
			var controllerPane = dijit.byId('postContractReportBillGrid'+this.billId+'-controllerPane'),
				stackContainer = dijit.byId('postContractReportBillGrid'+this.billId+'-stackContainer');

			controllerPane.destroyRecursive();
			stackContainer.destroyRecursive();

			this.createElementGrid();
		},
		markedCheckBoxObject: function(grid, selectedRowStore) {
			var store = grid.store;

			selectedRowStore.query().forEach(function(item) {
				if (item.id == buildspace.constants.GRID_LAST_ROW) {
					return;
				}

				store.fetchItemByIdentity({
					identity: item.id,
					onItem: function(node) {
						if ( ! node ) {
							return;
						}

						return grid.rowSelectCell.toggleRow(node._0, true);
					}
				});
			});
		}
	});

	return declare('buildspace.apps.PostContractReport.PrelimBillManager', dijit.layout.TabContainer, {
		region: "center",
		rootProject: null,
		style: "padding:0px;border:0px;margin:0px;width:100%;height:100%;",
		billId: null,
		billLayoutSettingId: null,
		explorer: null,
		nested: true,
		postCreate: function() {
			this.inherited(arguments);
			var prelimBillElementContainer = new PrelimBillElementContainer({
				id: 'bill_element_container_'+this.rootProject.id+'-bill-'+this.billId,
				rootProject: this.rootProject,
				billId: this.billId,
				bqVersion: 0,
				explorer: this.explorer,
				type: this.type
			});

			this.addChild(new ContentPane({
				style: "padding:0px;border:0px;width:100%;height:100%;",
				title: nls.elementTradeList,
				content: prelimBillElementContainer
			}));
		}
	});
});