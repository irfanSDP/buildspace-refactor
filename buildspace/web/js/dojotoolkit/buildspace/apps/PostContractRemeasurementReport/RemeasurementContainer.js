define('buildspace/apps/PostContractRemeasurementReport/RemeasurementContainer',[
	'dojo/_base/declare',
	"dojo/aspect",
	'dojo/request',
	'dojo/store/Memory',
	'./RemeasurementGrid',
	"buildspace/widget/grid/cells/Formatter",
	'buildspace/apps/ProjectBuilder/BillManager/ScheduleOfQuantityGrid',
	'./buildUpQuantitySummary',
	'./buildUpQuantityGrid',
	'dojo/i18n!buildspace/nls/PostContractRemeasurement'],
	function(declare, aspect, request, Memory, RemeasurementGrid, GridFormatter, ScheduleOfQuantityGrid, BuildUpQuantitySummary, BuildUpQuantityGrid, nls) {

	return declare('buildspace.apps.PostContractRemeasurementReport.RemeasurementContainer', dijit.layout.BorderContainer, {
		style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
		gutters: false,
		project: null,
		opt: null,
		selectedTypeStore: [],
		selectedElementStore: [],
		selectedItemStore: [],
		postCreate: function() {
			this.inherited(arguments);

			var self = this,
				formatter = new GridFormatter(),
				store = dojo.data.ItemFileWriteStore({
					url: "postContractRemeasurement/getAllBills/pid/" + self.project.id + "/opt/" + self.opt,
					clearOnClose: true
				}),
				grid = new RemeasurementGrid({
					id: 'post-contract-remeasurement-report-grid-'+self.project.id,
					stackContainerTitle: nls.bill,
					pageId: 'post-contract-remeasurement-report-grid-'+self.project.id,
					project: self.project,
					gridOpts: {
						type: 'bill',
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

			this.selectedTypeStore    = new Memory({ idProperty: 'id' });
			this.selectedElementStore = [];
			this.selectedItemStore    = [];

			var grid = new RemeasurementGrid({
				id: 'post-contract-remeasurement-report-billtype'+ self.project.id,
				stackContainerTitle: bill.title,
				pageId: 'post-contract-remeasurement-report-billtype'+ self.project.id + '_' + bill.id,
				project: self.project,
				gridOpts: {
					bill: bill,
					gridContainer: self,
					type: 'billTypes',
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
							self.createElementGrid(_item, bill);
						}
					},
					singleCheckBoxSelection: function(e) {
						var self = this,
							rowIndex = e.rowIndex,
							checked = this.selection.selected[rowIndex],
							item = this.getItem(rowIndex);

						// used to store removeable selection
						self.removedIds = [];

						if ( checked ) {
							self.gridContainer.selectedTypeStore.put({ id: item.id[0] });

							return self.getAffectedElementsAndItemsByTypes(item, 'add');
						} else {
							self.gridContainer.selectedTypeStore.remove(item.id[0]);

							self.removedIds.push(item.id[0]);

							return self.getAffectedElementsAndItemsByTypes(item, 'remove');
						}
					},
					toggleAllSelection: function(checked) {
						var self = this, selection = this.selection, storeName;

						// used to store removeable selection
						self.removedIds = [];

						if (checked) {
							selection.selectRange(0, self.rowCount-1);
							self.store.fetch({
								onComplete: function (items) {
									dojo.forEach(items, function (item, index) {
										if(item.id > 0) {
											self.gridContainer.selectedTypeStore.put({ id: item.id[0] });
										}
									});
								}
							});

							return self.getAffectedElementsAndItemsByTypes(null , 'add');
						} else {
							selection.deselectAll();

							self.store.fetch({
								onComplete: function (items) {
									dojo.forEach(items, function (item, index) {
										if(item.id > 0) {
											self.gridContainer.selectedTypeStore.remove(item.id[0]);

											self.removedIds.push(item.id[0]);
										}
									});
								}
							});

							return self.getAffectedElementsAndItemsByTypes(null, 'remove');
						}
					},
					getAffectedElementsAndItemsByTypes: function(item, type) {
						var self = this,
							types = [];

						var pb = buildspace.dialog.indeterminateProgressBar({
							title: nls.pleaseWait+'...'
						});

						pb.show();

						if (type === 'add') {
							self.gridContainer.selectedTypeStore.query().forEach(function(item) {
								types.push(item.id);
							});
						} else {
							for (var typeKeyIndex in self.removedIds) {
								types.push(self.removedIds[typeKeyIndex]);
							}
						}

						request.post('postContractRemeasurementReport/getElementsAndItemsByTypes', {
							handleAs: 'json',
							data: {
								bill_id: bill.id,
								type_ids: JSON.stringify(self.gridContainer.arrayUnique(types)),
								opt: self.gridContainer.opt
							}
						}).then(function(data) {
							// create default data store for checkbox selection
							for (var typeId in data) {
								if ( ! self.gridContainer.selectedElementStore[typeId] ) {
									self.gridContainer.selectedElementStore[typeId] = new Memory({ idProperty: 'id' });
								}

								if ( ! self.gridContainer.selectedItemStore[typeId] ) {
									self.gridContainer.selectedItemStore[typeId] = new Memory({ idProperty: 'id' });
								}
							}

							if ( type === 'add' ) {
								for (var typeId in data) {
									for (var elementId in data[typeId]) {
										self.gridContainer.selectedElementStore[typeId].put({ id: elementId });

										for (var itemIdIndex in data[typeId][elementId]) {
											self.gridContainer.selectedItemStore[typeId].put({ id: data[typeId][elementId][itemIdIndex] });
										}
									}
								}
							} else {
								for (var typeId in data) {
									for (var elementId in data[typeId]) {
										self.gridContainer.selectedElementStore[typeId].remove(elementId);

										for (var itemIdIndex in data[typeId][elementId]) {
											self.gridContainer.selectedItemStore[typeId].remove(data[typeId][elementId][itemIdIndex]);
										}
									}
								}
							}

							pb.hide();
						}, function(error) {
							pb.hide();
							console.log(error);
						});
					}
				}
			});
		},
		createElementGrid: function(billType, bill) {
			var self = this,
				formatter = new GridFormatter(),
				store = dojo.data.ItemFileWriteStore({
					url: 'postContractRemeasurement/getElementList/btId/' + billType.id + "/opt/" + self.opt,
					clearOnClose: true
				});

			if ( ! self.selectedElementStore[billType.id] ) {
				self.selectedElementStore[billType.id] = new Memory({ idProperty: 'id' });
			}

			if ( ! self.selectedItemStore[billType.id] ) {
				self.selectedItemStore[billType.id] = new Memory({ idProperty: 'id' });
			}

			var grid = new RemeasurementGrid({
				id: 'post-contract-remeasurement-report-element-'+ self.project.id,
				stackContainerTitle: billType.name,
				pageId: 'post-contract-remeasurement-report-element-'+ self.project.id + '_' + billType.id,
				project: self.project,
				gridOpts: {
					gridContainer: self,
					bill: bill,
					billTypeId: billType.id,
					type: 'element',
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
							self.createItemGrid(bill, billType, _item);
						}
					},
					singleCheckBoxSelection: function(e) {
						var self = this,
							rowIndex = e.rowIndex,
							checked = this.selection.selected[rowIndex],
							item = this.getItem(rowIndex);

						// used to store removeable selection
						self.removedIds = [];

						if ( checked ) {
							self.gridContainer.selectedElementStore[self.billTypeId].put({ id: item.id[0] });

							return self.getAffectedTypesAndItemsByTypes(item, 'add');
						} else {
							self.gridContainer.selectedElementStore[self.billTypeId].remove(item.id[0]);

							self.removedIds.push(item.id[0]);

							return self.getAffectedTypesAndItemsByTypes(item, 'remove');
						}
					},
					toggleAllSelection: function(checked) {
						var self = this, selection = this.selection, storeName;

						// used to store removeable selection
						self.removedIds = [];

						if (checked) {
							selection.selectRange(0, self.rowCount-1);
							self.store.fetch({
								onComplete: function (items) {
									dojo.forEach(items, function (item, index) {
										if(item.id[0] > 0) {
											self.gridContainer.selectedElementStore[self.billTypeId].put({ id: item.id[0] });
										}
									});
								}
							});

							return self.getAffectedTypesAndItemsByTypes(null , 'add');
						} else {
							selection.deselectAll();

							self.store.fetch({
								onComplete: function (items) {
									dojo.forEach(items, function (item, index) {
										if(item.id[0] > 0) {
											self.gridContainer.selectedElementStore[self.billTypeId].remove(item.id[0]);

											self.removedIds.push(item.id[0]);
										}
									});
								}
							});

							return self.getAffectedTypesAndItemsByTypes(null, 'remove');
						}
					},
					getAffectedTypesAndItemsByTypes: function(item, type) {
						var self = this,
							elements = [];

						var pb = buildspace.dialog.indeterminateProgressBar({
							title: nls.pleaseWait+'...'
						});

						pb.show();

						if (type === 'add') {
							self.gridContainer.selectedElementStore[self.billTypeId].query().forEach(function(item) {
								elements.push(item.id);
							});
						} else {
							for (var elementKeyIndex in self.removedIds) {
								elements.push(self.removedIds[elementKeyIndex]);
							}
						}

						request.post('postContractRemeasurementReport/getTypesAndItemsByElement', {
							handleAs: 'json',
							data: {
								bill_type_id: self.billTypeId,
								element_ids: JSON.stringify(self.gridContainer.arrayUnique(elements)),
								opt: self.gridContainer.opt
							}
						}).then(function(data) {
							var typeGrid = dijit.byId('post-contract-remeasurement-report-billtype'+ self.project.id);

							if ( type === 'add' ) {
								for (var typeId in data) {
									for (var elementId in data[typeId]) {
										for (var itemIdIndex in data[typeId][elementId]) {
											self.gridContainer.selectedItemStore[typeId].put({ id: data[typeId][elementId][itemIdIndex] });
										}
									}

									// select checked type selection if element is selected
									typeGrid.grid.store.fetchItemByIdentity({
										identity: typeId,
										onItem: function(node) {
											if ( ! node ) {
												return;
											}

											if ( self.gridContainer.selectedElementStore[typeId].data.length > 0 ) {
												self.gridContainer.selectedTypeStore.put({ id: typeId });

												return typeGrid.grid.rowSelectCell.toggleRow(node._0, true);
											}
										}
									});
								}
							} else {
								for (var typeId in data) {
									for (var elementId in data[typeId]) {
										self.gridContainer.selectedElementStore[typeId].remove(elementId);

										for (var itemIdIndex in data[typeId][elementId]) {
											self.gridContainer.selectedItemStore[typeId].remove(data[typeId][elementId][itemIdIndex]);
										}
									}

									// unselect checked type selection if no element is selected
									typeGrid.grid.store.fetchItemByIdentity({
										identity: typeId,
										onItem: function(node) {
											if ( ! node ) {
												return;
											}

											if ( self.gridContainer.selectedElementStore[typeId].data.length == 0 ) {
												self.gridContainer.selectedTypeStore.remove(typeId);

												return typeGrid.grid.rowSelectCell.toggleRow(node._0, false);
											}
										}
									});
								}
							}

							pb.hide();
						}, function(error) {
							pb.hide();
							console.log(error);
						});
					}
				}
			});
		},
		createItemGrid: function(bill, billType, element){
			var self = this, formatter = GridFormatter();

			var store = new dojo.data.ItemFileWriteStore({
				url: "postContractRemeasurement/getItemList/btId/" + billType.id + "/elementId/" + element.id + "/opt/" + self.opt,
				clearOnClose: true
			});

			var grid = new RemeasurementGrid({
				id: 'post-contract-remeasurement-report-item-'+self.project.id,
				stackContainerTitle: element.description,
				pageId: 'post-contract-remeasurement-report-item-'+self.project.id+'_'+element.id,
				project: self.project,
				gridOpts: {
					gridContainer: self,
					bill: bill,
					billTypeId: billType.id,
					store: store,
					type: 'item',
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
					},
					singleCheckBoxSelection: function(e) {
						var self = this,
							rowIndex = e.rowIndex,
							checked = this.selection.selected[rowIndex],
							item = this.getItem(rowIndex);

						// used to store removeable selection
						self.removedIds = [];

						if ( checked ) {
							self.gridContainer.selectedItemStore[self.billTypeId].put({ id: item.id[0] });

							return self.getAffectedTypesAndElementsByItems(item, 'add');
						} else {
							self.gridContainer.selectedItemStore[self.billTypeId].remove(item.id[0]);

							self.removedIds.push(item.id[0]);

							return self.getAffectedTypesAndElementsByItems(item, 'remove');
						}
					},
					toggleAllSelection: function(checked) {
						var self = this, selection = this.selection, storeName;

						// used to store removeable selection
						self.removedIds = [];

						if (checked) {
							selection.selectRange(0, self.rowCount-1);
							self.store.fetch({
								onComplete: function (items) {
									dojo.forEach(items, function (item, index) {
										if(item.id[0] > 0) {
											self.gridContainer.selectedItemStore[self.billTypeId].put({ id: item.id[0] });
										}
									});
								}
							});

							return self.getAffectedTypesAndElementsByItems(null , 'add');
						} else {
							selection.deselectAll();

							self.store.fetch({
								onComplete: function (items) {
									dojo.forEach(items, function (item, index) {
										if(item.id[0] > 0) {
											self.gridContainer.selectedItemStore[self.billTypeId].remove(item.id[0]);

											self.removedIds.push(item.id[0]);
										}
									});
								}
							});

							return self.getAffectedTypesAndElementsByItems(null, 'remove');
						}
					},
					getAffectedTypesAndElementsByItems: function(item, type) {
						var self = this,
							items = [];

						var pb = buildspace.dialog.indeterminateProgressBar({
							title: nls.pleaseWait+'...'
						});

						pb.show();

						if (type === 'add') {
							self.gridContainer.selectedItemStore[self.billTypeId].query().forEach(function(item) {
								items.push(item.id);
							});
						} else {
							for (var itemKeyIndex in self.removedIds) {
								items.push(self.removedIds[itemKeyIndex]);
							}
						}

						request.post('postContractRemeasurementReport/getTypesAndElementsByItem', {
							handleAs: 'json',
							data: {
								bill_type_id: self.billTypeId,
								item_ids: JSON.stringify(self.gridContainer.arrayUnique(items)),
								opt: self.gridContainer.opt
							}
						}).then(function(data) {
							var typeGrid    = dijit.byId('post-contract-remeasurement-report-billtype'+ self.project.id);
							var elementGrid = dijit.byId('post-contract-remeasurement-report-element-'+ self.project.id);

							if ( type === 'add' ) {
								for (var typeId in data) {
									for (var elementId in data[typeId]) {
										for (var itemIdIndex in data[typeId][elementId]) {
											self.gridContainer.selectedItemStore[typeId].put({ id: data[typeId][elementId][itemIdIndex] });
										}

										// select checked type element if item is selected
										elementGrid.grid.store.fetchItemByIdentity({
											identity: elementId,
											onItem: function(node) {
												if ( ! node ) {
													return;
												}

												if ( self.gridContainer.selectedItemStore[typeId].data.length > 0 ) {
													self.gridContainer.selectedElementStore[typeId].put({ id: elementId });

													return elementGrid.grid.rowSelectCell.toggleRow(node._0, true);
												}
											}
										});
									}

									// select checked type selection if element is selected
									typeGrid.grid.store.fetchItemByIdentity({
										identity: typeId,
										onItem: function(node) {
											if ( ! node ) {
												return;
											}

											if ( self.gridContainer.selectedElementStore[typeId].data.length > 0 ) {
												self.gridContainer.selectedTypeStore.put({ id: typeId });

												return typeGrid.grid.rowSelectCell.toggleRow(node._0, true);
											}
										}
									});
								}
							} else {
								if ( item ) {
									self.gridContainer.selectedItemStore[self.billTypeId].remove(item.id[0]);
								}

								for (var typeId in data) {
									for (var elementId in data[typeId]) {
										// unselect checked element selection if no item is selected
										elementGrid.grid.store.fetchItemByIdentity({
											identity: elementId,
											onItem: function(node) {
												if ( ! node ) {
													return;
												}

												if ( self.gridContainer.selectedItemStore[typeId].data.length == 0 ) {
													self.gridContainer.selectedElementStore[typeId].remove(elementId);

													return elementGrid.grid.rowSelectCell.toggleRow(node._0, false);
												}
											}
										});
									}

									// unselect checked type selection if no element is selected
									typeGrid.grid.store.fetchItemByIdentity({
										identity: typeId,
										onItem: function(node) {
											if ( ! node ) {
												return;
											}

											if ( self.gridContainer.selectedElementStore[typeId].data.length == 0 ) {
												self.gridContainer.selectedTypeStore.remove(typeId);

												return typeGrid.grid.rowSelectCell.toggleRow(node._0, false);
											}
										}
									});
								}
							}

							pb.hide();
						}, function(error) {
							pb.hide();
							console.log(error);
						});
					}
				}
			});
		},
		createBuildUpQuantityContainer: function(colField, editable, item, dimensionColumns, billColumnSettingId, billGridStore) {
			var moduleName, itemId;

			var locked = true;

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
					cellType: 'buildspace.widget.grid.cells.Textarea'
				},{
					name: nls.factor,
					field: 'factor-value',
					width:'100px',
					styles:'text-align:right;',
					cellType:'buildspace.widget.grid.cells.FormulaTextBox',
					formatter: formatter.formulaNumberCellFormatter
				}];

				dojo.forEach(dimensionColumns, function(dimensionColumn){
					var column = {
						name: dimensionColumn.title,
						field: dimensionColumn.field_name,
						width:'100px',
						styles:'text-align:right;',
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
						stackContainerId: 'postContractReportRemeasurement-grid_' + self.project.id + '-stackContainer',
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

				var container = dijit.byId('postContractReportRemeasurement-grid_' + self.project.id + '-stackContainer');

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
			var stackContainer = dijit.byId('postContractReportRemeasurement-grid_'+id+'-stackContainer');
			if(stackContainer){
				dijit.byId('postContractReportRemeasurement-grid_'+id+'-stackContainer').destroyRecursive();
			}

			stackContainer = new dijit.layout.StackContainer({
				style:'width:100%;height:100%;border:0px;',
				region: "center",
				id: 'postContractReportRemeasurement-grid_'+id+'-stackContainer'
			});

			var stackPane = new dijit.layout.ContentPane({
				title: title,
				content: content,
				grid: content.grid
			});

			stackContainer.addChild(stackPane);

			var controller = new dijit.layout.StackController({
				region: "top",
				containerId: 'postContractReportRemeasurement-grid_'+id+'-stackContainer'
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

			dojo.subscribe('postContractReportRemeasurement-grid_'+id+'-stackContainer-selectChild',"",function(page){
				var widget = dijit.byId('postContractReportRemeasurement-grid_'+id+'-stackContainer');
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
					}
				}
			});

			return borderContainer;
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
		},
		arrayUnique: function(array) {
			return array.reverse().filter(function (e, i, arr) {
				return arr.indexOf(e, i+1) === -1;
			}).reverse();
		}
	});
});