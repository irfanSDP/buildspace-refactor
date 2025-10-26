define('buildspace/apps/PostContractRemeasurement/RemeasurementContainer',[
	'dojo/_base/declare',
	"dojo/aspect",
	'./RemeasurementGrid',
	"buildspace/widget/grid/cells/Formatter",
	'buildspace/apps/ProjectBuilder/BillManager/ScheduleOfQuantityGrid',
	'./buildUpQuantitySummary',
	'./buildUpQuantityGrid',
	'dojo/i18n!buildspace/nls/PostContractRemeasurement'],
	function(declare, aspect, RemeasurementGrid, GridFormatter, ScheduleOfQuantityGrid, BuildUpQuantitySummary, BuildUpQuantityGrid, nls) {

	var RemeasurementContainer = declare('buildspace.apps.PostContractRemeasurement.RemeasurementContainer', dijit.layout.BorderContainer, {
		style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
		gutters: false,
		project: null,
		opt: null,
		postCreate: function() {
			this.inherited(arguments);

			var self = this,
				formatter = new GridFormatter(),
				store = dojo.data.ItemFileWriteStore({
					url: "postContractRemeasurement/getAllBills/pid/" + self.project.id + "/opt/" + self.opt,
					clearOnClose: true
				}),
				grid = new RemeasurementGrid({
					id: 'post-contract-remeasurement-grid-'+self.project.id,
					stackContainerTitle: nls.bill,
					pageId: 'post-contract-remeasurement-grid-'+self.project.id,
					project: self.project,
					gridOpts: {
						store: store,
						structure: [
							{name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
							{name: nls.bill, field: 'title', width:'auto', formatter: formatter.analyzerDescriptionCellFormatter },
							{name: nls.omission, field: 'omission', width:'150px', styles:'text-align:right;color:red;', formatter: formatter.unEditableCurrencyCellFormatter},
							{name: nls.addition, field: 'addition', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
							{name: nls.nettAdditionOmission, field: 'nettAdditionOmission', width:'150px', styles:'text-align:right;color:green;', formatter: formatter.unEditableCurrencyCellFormatter}
						],
						onRowDblClick: function(e){
							var _this = this, _item = _this.getItem(e.rowIndex);
							if(_item.id > 0 && _item.title[0] !== null){
								self.createBillTypesGrid(_item);
							}
						}
					}
				});

			var gridContainer = this.makeGridContainer(grid, nls.bill);
			this.addChild(gridContainer);
		},
		createBillTypesGrid: function(bill) {
			var self = this,
				formatter = new GridFormatter(),
				store = dojo.data.ItemFileWriteStore({
					url: "postContractRemeasurement/getBillTypes/bid/" + bill.id + "/opt/" + self.opt,
					clearOnClose: true
				});

			var grid = new RemeasurementGrid({
				id: 'post-contract-remeasurement-billtype'+ self.project.id,
				stackContainerTitle: bill.title,
				pageId: 'post-contract-remeasurement-billtype'+ self.project.id + '_' + bill.id,
				project: self.project,
				gridOpts: {
					store: store,
					structure: [
						{name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
						{name: nls.description, field: 'name', width:'auto', formatter: formatter.analyzerDescriptionCellFormatter },
						{name: nls.totalUnits, field: 'quantity', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
						{name: nls.omission, field: 'omission', width:'150px', styles:'text-align:right;color:red;', formatter: formatter.unEditableCurrencyCellFormatter},
						{name: nls.addition, field: 'addition', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
						{name: nls.nettAdditionOmission, field: 'nettAdditionOmission', width:'150px', styles:'text-align:right;color:green;', formatter: formatter.unEditableCurrencyCellFormatter}
					],
					onRowDblClick: function(e){
						var _this = this, _item = _this.getItem(e.rowIndex);
						if(_item.id > 0 && _item.name[0] !== null){
							self.createElementGrid(_item);
						}
					}
				}
			});
		},
		createElementGrid: function(billType) {
			var self = this,
				formatter = new GridFormatter(),
				store = dojo.data.ItemFileWriteStore({
					url: 'postContractRemeasurement/getElementList/btId/' + billType.id + "/opt/" + self.opt,
					clearOnClose: true
				});

			var grid = new RemeasurementGrid({
				id: 'post-contract-remeasurement-element-'+ self.project.id,
				stackContainerTitle: billType.name,
				pageId: 'post-contract-remeasurement-element-'+ self.project.id + '_' + billType.id,
				project: self.project,
				gridOpts: {
					store: store,
					structure: [
						{name: 'No.', field: 'id', width:'40px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
						{name: nls.description, field: 'description', width:'auto' },
						{name: nls.omission, field: 'omission', width:'150px', styles:'text-align:right;color:red;', formatter: formatter.unEditableCurrencyCellFormatter},
						{name: nls.addition, field: 'addition', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
						{name: nls.nettAdditionOmission, field: 'nettAdditionOmission', width:'150px', styles:'text-align:right;color:green;', formatter: formatter.unEditableCurrencyCellFormatter}
					],
					onRowDblClick: function(e){
						var _this = this, _item = _this.getItem(e.rowIndex);
						if(_item.id > 0 && _item.description[0] !== null){
							self.createItemGrid(billType, _item);
						}
					}
				}
			});
		},
		createItemGrid: function(billType, element){
			var self = this, formatter = GridFormatter();

			var store = new dojo.data.ItemFileWriteStore({
				url: "postContractRemeasurement/getItemList/btId/" + billType.id + "/elementId/" + element.id + "/opt/" + self.opt,
				clearOnClose: true
			});

			var grid = new RemeasurementGrid({
				id: 'post-contract-remeasurement-item-'+self.project.id,
				stackContainerTitle: element.description,
				pageId: 'post-contract-remeasurement-item-'+self.project.id+'_'+element.id,
				project: self.project,
				gridOpts: {
					store: store,
					type: 'item',
					billTypeId: billType.id,
					updateUrl: 'postContractRemeasurement/remeasurementItemUpdate',
					onRowDblClick: function(e) {
						var colField = e.cell.field,
							rowIndex = e.rowIndex,
							item = this.getItem(rowIndex),
							billGridStore = this.store,
							billColumnSettingId = billType.id
							editable = false;

						if(item.id > 0 && (colField === 'omission-qty_per_unit' || colField === 'addition-qty_per_unit') ) {
							if(item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER &&
								item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N &&
								item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID &&
								item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM &&
								item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE &&
								item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {

								// don't allow other than omission qty per unit field to display build up grid
								if( colField === 'omission-qty_per_unit' && (item['omission-has_build_up'] == undefined || ! item['omission-has_build_up'][0])) {
									return;
								}

								if(item.uom_id[0] > 0) {
									if ( colField === 'addition-qty_per_unit' && ! item.include[0] ) {
										return;
									}

									var dimensionColumnQuery = dojo.xhrPost({
										url: "billBuildUpQuantity/getDimensionColumnStructure",
										content:{uom_id: item.uom_id[0]},
										handleAs: "json"
									});

									if ( colField === 'addition-qty_per_unit' ) {
										editable = true;
									}

									var pb = buildspace.dialog.indeterminateProgressBar({
										title:nls.pleaseWait+'...'
									});

									pb.show();

									dimensionColumnQuery.then(function(dimensionColumns){
										self.createBuildUpQuantityContainer(colField, editable, item, dimensionColumns, billColumnSettingId, billGridStore);
										pb.hide();
									});

								} else {
									buildspace.dialog.alert(nls.buildUpQtyAlert, nls.pleaseSetUOM, 60, 300);
								}
							}
						}
					}
				}
			});
		},
		createBuildUpQuantityContainer: function(colField, editable, item, dimensionColumns, billColumnSettingId, billGridStore) {
			var moduleName, itemId;

			var locked = (editable) ? false : true;

			if ( colField === 'addition-qty_per_unit' ) {
				moduleName = 'postContractRemeasurementBillBuildUpQuantity';
				itemId     = item.post_contract_bill_item_rate_id;
			} else {
				moduleName = 'billBuildUpQuantity';
				itemId     = item.id;
			}

			var self = this,
				type = buildspace.constants.QUANTITY_PER_UNIT_ORIGINAL,
				scheduleOfQtyGrid,
				baseContainer = new dijit.layout.BorderContainer({
					style:"padding:0px;width:100%;height:100%;border:0px;outline:none;",
					gutters: false
				}),
				tabContainer = new dijit.layout.TabContainer({
					nested: true,
					style: "padding:0;border:none;margin:0;width:100%;height:100%;",
					region: 'center'
				}),
				formatter = new GridFormatter(),
				scheduleOfQtyQuery = dojo.xhrGet({
					url: moduleName+'/getLinkInfo/id/'+item.id+'/bcid/'+billColumnSettingId+'/t/'+type,
					handleAs: "json"
				}),
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
					width: 'auto',
					editable: !locked,
					cellType: 'buildspace.widget.grid.cells.Textarea'
				},{
					name: nls.factor,
					field: 'factor-value',
					width:'100px',
					styles:'text-align:right;',
					editable: !locked,
					cellType:'buildspace.widget.grid.cells.FormulaTextBox',
					formatter: formatter.formulaNumberCellFormatter
				}];

				dojo.forEach(dimensionColumns, function(dimensionColumn){
					var column = {
						name: dimensionColumn.title,
						field: dimensionColumn.field_name,
						width:'100px',
						styles:'text-align:right;',
						editable: !locked,
						cellType:'buildspace.widget.grid.cells.FormulaTextBox',
						formatter: formatter.formulaNumberCellFormatter
					};
					structure.push(column);
				});

				structure.push({ // total column
					name: nls.total,
					field: 'total',
					width:'100px',
					styles:'text-align:right;',
					formatter: formatter.numberCellFormatter
				});

				structure.push({
					name: nls.sign,
					field: 'sign',
					width: '70px',
					styles: 'text-align:center;',
					editable: true,
					cellType: 'dojox.grid.cells.Select',
					options: sign.options,
					values: sign.values,
					formatter: formatter.signCellFormatter
				});

				var buildUpSummaryWidget = new BuildUpQuantitySummary({
					itemId: itemId,
					billColumnSettingId: billColumnSettingId,
					type: type,
					container: baseContainer,
					billGridStore: billGridStore,
					buildUpGridStore: store,
					hasLinkedQty: linkInfo.has_linked_qty,
					_csrf_token: item._csrf_token,
					moduleName: moduleName,
					locked: locked
				});

				if(linkInfo.has_linked_qty){
					hasLinkedQty = true;
					scheduleOfQtyGrid = new ScheduleOfQuantityGrid({
						title: nls.scheduleOfQuantities,
						BillItem: item,
						disableEditingMode: true,
						stackContainerId: 'postContractRemeasurement-grid_' + self.project.id + '-stackContainer',
						gridOpts: {
							qtyType: type,
							buildUpSummaryWidget: buildUpSummaryWidget,
							store: new dojo.data.ItemFileWriteStore({
								url: moduleName+"/getScheduleOfQuantities/id/"+item.id+"/bcid/"+billColumnSettingId+"/type/"+type,
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

				tabContainer.addChild(new BuildUpQuantityGrid({
					title: nls.manualQtyItems,
					region: 'center',
					billColumnSettingId: billColumnSettingId,
					BillItem: item,
					type: type,
					locked: locked,
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
				}));

				if(hasLinkedQty){
					tabContainer.addChild(scheduleOfQtyGrid);
				}

				baseContainer.addChild(tabContainer);
				baseContainer.addChild(buildUpSummaryWidget);

				var container = dijit.byId('postContractRemeasurement-grid_' + self.project.id + '-stackContainer');

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

				pb.hide();
			});
		},
		makeGridContainer: function(content, title){
			var id = this.project.id;
			var stackContainer = dijit.byId('postContractRemeasurement-grid_'+id+'-stackContainer');
			if(stackContainer){
				dijit.byId('postContractRemeasurement-grid_'+id+'-stackContainer').destroyRecursive();
			}

			stackContainer = new dijit.layout.StackContainer({
				style:'width:100%;height:100%;border:0px;',
				region: "center",
				id: 'postContractRemeasurement-grid_'+id+'-stackContainer'
			});

			var stackPane = new dijit.layout.ContentPane({
				title: title,
				content: content,
				grid: content.grid
			});

			stackContainer.addChild(stackPane);

			var controller = new dijit.layout.StackController({
				region: "top",
				containerId: 'postContractRemeasurement-grid_'+id+'-stackContainer'
			});

			var controllerPane = new dijit.layout.ContentPane({
				style: "padding:0px;overflow:hidden;",
				class: 'breadCrumbTrail',
				region: 'top',
				content: controller
			});

			var borderContainer = new dijit.layout.BorderContainer({
				style:"padding:0px;width:100%;height:100%;border:0px;",
				gutters: false,
				region: 'center'
			});

			borderContainer.addChild(stackContainer);
			borderContainer.addChild(controllerPane);

			dojo.subscribe('postContractRemeasurement-grid_'+id+'-stackContainer-selectChild',"",function(page){
				var widget = dijit.byId('postContractRemeasurement-grid_'+id+'-stackContainer');
				if(widget){
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

			return borderContainer;
		}
	});

	return RemeasurementContainer;
});