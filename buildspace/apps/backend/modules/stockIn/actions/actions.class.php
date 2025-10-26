<?php

/**
 * stockIn actions.
 *
 * @package    buildspace
 * @subpackage stockIn
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class stockInActions extends BaseActions {

	public function executeGetStockInProjectList(sfWebRequest $request)
	{
		$this->forward404Unless($request->isXmlHttpRequest());

		$projects = PurchaseOrderTable::getProjectsThatHasPurchaseOrderRecord();

		$projects[] = array(
			'id'         => Constants::GRID_LAST_ROW,
			'title'      => null,
			'status'     => null,
			'status_id'  => null,
			'state'      => null,
			'country'    => null,
			'created_by' => null,
			'created_at' => null,
		);

		$data = array(
			'identifier' => 'id',
			'items'      => $projects,
		);

		return $this->renderJson($data);
	}

	public function executeGetStockInInvoiceList(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pId'))
		);

		$data = array();

		$invoices       = StockInInvoiceTable::getInvoicesByProject($project);
		$invoicesTotals = StockInInvoiceItemTable::getInvoicesTotalByProject($project);
		$form           = new BaseForm();

		foreach ( $invoices as $invoice )
		{
			$invoiceTotal = 0;

			if ( isset( $invoicesTotals[$invoice['id']] ) )
			{
				$invoiceTotal = Utilities::prelimRounding($invoicesTotals[$invoice['id']]);
			}

			$data[] = array(
				'id'            => $invoice['id'],
				'invoice_no'    => $invoice['invoice_no'],
				'selected_po'   => $invoice['PurchaseOrder']['po_prefix'] . Utilities::generatePurchaseOrderReferenceNo($invoice['PurchaseOrder']['po_count']),
				'supplier_name' => empty( $invoice['company_name'] ) ? '-' : $invoice['company_name'],
				'created_by'    => $invoice['creator_name'],
				'invoice_date'  => is_null($invoice['invoice_date']) ? null : date('d/m/Y', strtotime($invoice['invoice_date'])),
				'invoice_total' => $invoiceTotal,
				'_csrf_token'   => $form->getCSRFToken(),
			);

			unset( $invoice );
		}

		unset( $invoices );

		$data[] = array(
			'id'            => Constants::GRID_LAST_ROW,
			'invoice_no'    => null,
			'selected_po'   => null,
			'supplier_name' => null,
			'created_by'    => null,
			'invoice_date'  => null,
			'_csrf_token'   => $form->getCSRFToken(),
		);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $data,
		));
	}

	public function executeGetInvoiceFormInformation(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pId'))
		);

		$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'));
		$invoice = $invoice ? : new StockInInvoice();

		$form   = new StockInInvoiceForm($invoice);
		$object = $form->getObject();

		$data['formInformation'] = array(
			'stock_in_invoice[project_structure_id]'   => $object->project_structure_id ? : $project->id,
			'stock_in_invoice[purchase_order_id]'      => (string) $object->purchase_order_id,
			'stock_in_invoice[selected_supplier_name]' => $object->PurchaseOrder->PurchaseOrderSupplier->Company->name ? : '-',
			'stock_in_invoice[selected_po_name]'       => $object->PurchaseOrder->getGeneratedReferenceNumber(),
			'stock_in_invoice[invoice_no]'             => $object->invoice_no,
			'stock_in_invoice[invoice_date]'           => $object->invoice_date ? date('Y-m-d', strtotime($object->invoice_date)) : date('Y-m-d'),
			'stock_in_invoice[invoice_date_text]'      => date('d/m/Y', strtotime($object->invoice_date)),
			'stock_in_invoice[term_type]'              => (string) $object->term_type,
			'stock_in_invoice[term_type_text]'         => StockInInvoiceTable::getTermTypeText($object->term_type),
			'stock_in_invoice[_csrf_token]'            => $form->getCSRFToken(),
		);

		$data['formInformation']['attachmentDownloadFileName'] = $object->invoice_upload;
		$data['formInformation']['attachmentDownloadLink']     = null;
		$data['formInformation']['attachmentRemoveLink']       = null;

		if ( !empty( $object->invoice_upload ) )
		{
			sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url' ));

			$downloadPath = self::getUploadDirForInvoice();

			$data['formInformation']['attachmentDownloadLink'] = public_path("{$downloadPath}/{$object->invoice_upload}", true);
			$data['formInformation']['attachmentRemoveLink']   = public_path("stockIn/removeInvoiceUploadedFile/invoiceId/{$invoice->id}", true);
		}

		$data['poDropDown']   = PurchaseOrderTable::generateDropDownSelectionsByProject($project);
		$data['termDropDown'] = StockInInvoiceTable::generateDropDownSelectionsForTerms();

		return $this->renderJson($data);
	}

	public function executeDeleteInvoice(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'))
		);

		$errorMsg = array();

		try
		{
			$invoice->delete();

			$success = true;
		}
		catch (Exception $e)
		{
			$errorMsg = $e->getMessage();
			$success  = false;
		}

		$data = array( 'success' => $success, 'errorMsg' => $errorMsg );

		return $this->renderJson($data);
	}

	public function executeGetSupplierNameBySelectedPurchaseOrder(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$purchaseOrder = Doctrine_Core::getTable('PurchaseOrder')->find($request->getParameter('poId'))
		);

		$companyName = empty( $purchaseOrder->PurchaseOrderSupplier->Company->name ) ? '-' : $purchaseOrder->PurchaseOrderSupplier->Company->name;

		$data['supplierName'] = $companyName;

		return $this->renderJson($data);
	}

	public function executeSaveInvoiceFormInformation(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('POST') and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pId'))
		);

		$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'));
		$invoice = $invoice ? : new StockInInvoice();

		$form = new StockInInvoiceForm($invoice);

		if ( $this->isFormValid($request, $form) )
		{
			$invoice = $form->save();

			$id      = $invoice->getId();
			$success = true;
			$errors  = array();
		}
		else
		{
			$id      = $request->getPostParameter('invoiceId');
			$errors  = $form->getErrors();
			$success = false;
		}

		$data = array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors );

		return $this->renderJson($data);
	}

	public function executeUploadFileForInvoice(sfWebRequest $request)
	{
		sfConfig::set('sf_web_debug', false);

		$this->forward404Unless(
			$request->isMethod('POST') and
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'))
		);

		$success   = false;
		$errors    = array();
		$uploadDir = self::getFullUploadDirForInvoice();

		$form = new StockInInvoiceUploadForm();

		if ( $this->isFormValid($request, $form) )
		{
			foreach ( $request->getFiles() as $fileArray )
			{
				$uploadedFile     = $fileArray['fileUpload'];
				$currentFileName  = explode('.', $uploadedFile["name"]);
				$fileExtension    = pathinfo($uploadedFile["name"], PATHINFO_EXTENSION);
				$uploadedFileName = sha1($currentFileName[0] . microtime() . mt_rand()) . '.' . $fileExtension;

				try
				{
					if ( !is_dir($uploadDir) )
					{
						mkdir($uploadDir, 0777);
					}

					move_uploaded_file($uploadedFile["tmp_name"], $uploadDir . DIRECTORY_SEPARATOR . $uploadedFileName);

					// will always overwrite the existing uploaded file name if available
					$invoice->invoice_upload = $uploadedFileName;
					$invoice->save();

					$success = true;

					break;
				}
				catch (Exception $e)
				{
					break;
				}
			}
		}

		$data = array( 'success' => $success, 'errorMsgs' => $errors );

		if ( !empty( $invoice->invoice_upload ) )
		{
			sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url' ));

			$downloadPath = self::getUploadDirForInvoice();

			$data['attachmentDownloadFileName'] = $invoice->invoice_upload;
			$data['attachmentDownloadLink']     = public_path("{$downloadPath}/{$invoice->invoice_upload}", true);
			$data['attachmentRemoveLink']       = public_path("stockIn/removeInvoiceUploadedFile/invoiceId/{$invoice->id}", true);
		}

		return $this->renderJson($data);
	}

	public function executeRemoveInvoiceUploadedFile(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'))
		);

		$filePath = self::getFullUploadDirForInvoice();

		unlink("{$filePath}/{$invoice->invoice_upload}");

		$invoice->deleteAttachedFile();

		$data = array( 'success' => true );

		return $this->renderJson($data);
	}

	public function executeGetInvoiceItemListings(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'))
		);

		$items              = array();
		$invoiceItemIds     = array();
		$invoiceFromDbItems = array();

		$invoiceItems = StockInInvoiceItemTable::getItemListingByStockInInvoice($invoice);

		foreach ( $invoiceItems as $invoiceItem )
		{
			$invoiceItemIds[$invoiceItem['resource_item_id']] = $invoiceItem['resource_item_id'];

			$quantity           = number_format((float) $invoiceItem['quantity'], 2, '.', '');
			$rates              = number_format((float) $invoiceItem['rates'], 2, '.', '');
			$totalWithoutTax    = number_format((float) $invoiceItem['total_without_tax'], 2, '.', '');
			$total              = number_format((float) $invoiceItem['total'], 2, '.', '');
			$discountPercentage = number_format((float) $invoiceItem['discount_percentage'], 2, '.', '');
			$taxPercentage      = number_format((float) $invoiceItem['tax_percentage'], 2, '.', '');

			$invoiceFromDbItems[$invoiceItem['resource_item_id']] = array(
				'stockInItemId'       => $invoiceItem['id'],
				'quantity'            => $quantity,
				'rates'               => $rates,
				'discount_percentage' => $discountPercentage,
				'tax_percentage'      => $taxPercentage,
				'total_without_tax'   => $totalWithoutTax,
				'total'               => $total,
				'stockInItemRemarkId' => $invoiceItem['remark_id'],
				'remarks'             => $invoiceItem['remark'],
			);
		}

		if ( !empty( $invoiceItemIds ) )
		{
			$doItemQuantities = StockInDeliveryOrderItemQuantityTable::getItemQuantitiesByStockInInvoice($invoice);

			$items = StockInInvoiceItemTable::getHierarchyInvoiceItemListingFromResourceLibraryByStockInItemIds($invoiceItemIds, $invoiceFromDbItems, $doItemQuantities);
		}

		// empty row
		$items[] = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => null,
			'uom'         => null,
			'remarks'     => null,
		);

		$form = new BaseForm();

		// assign csrf token to each available item
		foreach ( $items as $key => $item )
		{
			$items[$key]['_csrf_token'] = $form->getCSRFToken();
		}

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items,
		));
	}

	public function executeUpdateItemInformation(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$request->hasParameter('field_name') and
			$invoiceItem = Doctrine_Core::getTable('StockInInvoiceItem')->findOneBy('idAndstock_in_invoice_id', array(
				$request->getPostParameter('stockInItemId'),
				$request->getPostParameter('invoiceId')
			))
		);

		$success   = false;
		$errorMsg  = array();
		$items     = array();
		$fieldName = $request->getParameter('field_name');

		$allowedFields = array(
			StockInInvoiceItem::QUANTITY, StockInInvoiceItem::RATES,
			StockInInvoiceItem::DISCOUNT_PERCENTAGE, StockInInvoiceItem::TAX_PERCENTAGE,
		);

		try
		{
			if ( !in_array($fieldName, $allowedFields) )
			{
				throw new InvalidArgumentException('Invalid field submitted !');
			}

			$value = ( is_numeric($request->getPostParameter('val')) ) ? $request->getPostParameter('val') : 0;

			$invoiceItem->{$fieldName} = number_format((float) $value, 2, '.', '');
			$invoiceItem->save();

			$doQuantity = StockInDeliveryOrderItemQuantityTable::getItemQuantitiesByStockInInvoiceItem($invoiceItem);

			$success = true;

			$items[] = array(
				'id'                  => $invoiceItem->resource_item_id,
				'quantity'            => $invoiceItem->quantity,
				'doQuantity'          => $doQuantity,
				'balanceQuantity'     => StockInInvoiceItemTable::calculateBalanceQuantity($invoiceItem->quantity, $doQuantity),
				'rates'               => $invoiceItem->rates,
				'discount_percentage' => $invoiceItem->discount_percentage,
				'tax_percentage'      => $invoiceItem->tax_percentage,
				'total_without_tax'   => $invoiceItem->total_without_tax,
				'total'               => $invoiceItem->total,
			);
		}
		catch (Exception $e)
		{
			$errorMsg = $e->getMessage();
		}

		$data = array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items );

		return $this->renderJson($data);
	}

	public function executeDeleteInvoiceItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->isMethod('post') AND
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId')) AND
			$resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('resourceItemId'))
		);

		$items    = array();
		$errorMsg = array();

		try
		{
			$affectedNodes = StockInInvoiceItemTable::deleteLikeResourceLibraryTree($invoice, $resourceItem);
			$success       = true;
		}
		catch (Exception $e)
		{
			$errorMsg      = $e->getMessage();
			$affectedNodes = array();
			$success       = false;
		}

		$data = array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items, 'affected_nodes' => $affectedNodes );

		return $this->renderJson($data);
	}

	public function executeGetPreviousRFQItemRemarks(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$resourceItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('resourceItemId'))
		);

		$pdo         = $resourceItem->getTable()->getConnection()->getDbh();
		$stockInItem = Doctrine_Core::getTable('StockInInvoiceItem')->find($request->getParameter('stockInItemId'));

		$stmt = $pdo->prepare("SELECT DISTINCT(rfqri.id), rfqri.description FROM " . RFQItemRemarkTable::getInstance()->getTableName() . " rfqri
		JOIN " . StockInInvoiceItemTable::getInstance()->getTableName() . " siii ON rfqri.resource_item_id = siii.resource_item_id
		WHERE siii.resource_item_id = " . $resourceItem->id . "
		AND rfqri.deleted_at IS NULL
		ORDER BY rfqri.id DESC");

		$stmt->execute(array());

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$form = new BaseForm();

		foreach ( $results as $key => $result )
		{
			$selected = ( $result['id'] == $stockInItem->remark_id ) ? true : false;

			$results[$key]['selected']    = $selected;
			$results[$key]['_csrf_token'] = $form->getCSRFToken();
		}

		$emptyRow = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => '',
		);

		array_push($results, $emptyRow);

		$data = array(
			'identifier' => 'id',
			'items'      => array_values($results),
		);

		return $this->renderJson($data);
	}

	public function executeUpdateRFQItemRemark(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$stockInItem = Doctrine_Core::getTable('StockInInvoiceItem')->find($request->getParameter('stockInItemId'))
		);

		$resourceItemId = $request->getPostParameter('resourceItemId');
		$description    = $request->getPostParameter('rfq_item_remark[description]');

		$rfqItemRemark = Doctrine_Core::getTable('RFQItemRemark')->findOneBy('resource_item_idAnddescription', array( $resourceItemId, $description ));
		$rfqItemRemark = ( $rfqItemRemark ) ? $rfqItemRemark : new RFQItemRemark();

		$form = new RFQItemRemarkForm($rfqItemRemark);

		if ( $this->isFormValid($request, $form) )
		{
			$rfqItemRemark = $form->save();
			$id            = $rfqItemRemark->getId();
			$success       = true;
			$errors        = array();
		}
		else
		{
			$id      = $request->getPostParameter('rfqItemRemarkId');
			$errors  = $form->getErrors();
			$success = false;
		}

		$data = array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors );

		return $this->renderJson($data);
	}

	public function executeUpdateSelectedPreviousRFQItemRemark(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$stockInItem = Doctrine_Core::getTable('StockInInvoiceItem')->find($request->getParameter('stockInItemId'))
		);

		$data = array();

		$resourceItemId = $request->getPostParameter('rfq_item_remark[resource_item_id]');
		$description    = $request->getPostParameter('rfq_item_remark[description]');

		$this->forward404Unless($rfqItemRemark = Doctrine_Core::getTable('RFQItemRemark')->findOneBy('resource_item_idAnddescription', array( $resourceItemId, $description )));

		$form = new RFQItemRemarkForm($rfqItemRemark);

		if ( $this->isFormValid($request, $form) )
		{
			$id      = $rfqItemRemark->id;
			$success = true;
			$errors  = array();

			$itemDesc = $stockInItem->ResourceItem->description;

			if ( $stockInItem->remark_id == $id )
			{
				$stockInItem->remark_id = null;

				$data = array(
					'stockInItemRemarkId' => $id,
					'description'         => $itemDesc,
				);
			}
			else
			{
				$stockInItem->remark_id = $id;

				$data = array(
					'stockInItemRemarkId' => $id,
					'description'         => \StockInInvoiceItem::remarksFormatter($itemDesc, $form->getObject()->description),
				);
			}

			$stockInItem->save();
		}
		else
		{
			$id      = $request->getPostParameter('stockInItemRemarkId');
			$errors  = $form->getErrors();
			$success = false;
		}

		$data = array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors, 'data' => $data );

		return $this->renderJson($data);
	}

	public function executeGetResourceItemList(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId')) AND
			$trade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('tradeId'))
		);

		$pdo           = $trade->getTable()->getConnection()->getDbh();
		$resourceItems = array();
		$poItemsId     = array();
		$form          = new BaseForm();

		// get associated Stock In Invoice's Item listing
		$poItems = StockInInvoiceItemTable::getItemListingByStockInInvoice($invoice);

		foreach ( $poItems as $poItem )
		{
			$poItemsId[] = $poItem['resource_item_id'];
		}

		$stmt = $pdo->prepare("SELECT i.resource_trade_id, i.id FROM " . ResourceItemTable::getInstance()->getTableName() . " i
		WHERE i.id = i.root_id AND i.resource_trade_id = " . $trade->id . " AND i.deleted_at IS NULL ORDER BY i.priority");

		$stmt->execute(array());

		$roots = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

		if ( array_key_exists($trade->id, $roots) )
		{
			$notInQuery = '';
			$rootIds    = $roots[$trade->id];

			if ( !empty( $poItemsId ) )
			{
				$notInQuery = 'AND c.id NOT IN (' . implode(', ', $poItemsId) . ')';
			}

			$stmt = $pdo->prepare("SELECT DISTINCT c.id, c.description, c.type::text, uom.id as uom_id, c.lft, c.level,
			c.resource_trade_id, c.level, c.updated_at, uom.symbol AS uom_symbol,
			c.priority, c.lft, c.level
			FROM " . ResourceItemTable::getInstance()->getTableName() . " p
			JOIN " . ResourceItemTable::getInstance()->getTableName() . " c
			ON c.lft BETWEEN p.lft AND p.rgt
			LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id
			AND uom.deleted_at IS NULL WHERE p.id IN (" . implode(',', $rootIds) . ") " . $notInQuery . " AND p.id = c.root_id
			AND c.deleted_at IS NULL AND p.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level ASC");

			$stmt->execute(array());

			$resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		$defaultLastRow = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => '',
			'type'        => (string) ResourceItem::TYPE_WORK_ITEM,
			'uom_symbol'  => '',
			'uom_id'      => '-1',
			'relation_id' => $trade->id,
			'updated_at'  => '-',
			'level'       => 0,
			'_csrf_token' => $form->getCSRFToken()
		);

		array_push($resourceItems, $defaultLastRow);

		$data = array(
			'identifier' => 'id',
			'items'      => $resourceItems
		);

		return $this->renderJson($data);
	}

	public function executeCopyResourceItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->isMethod('post') AND
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'))
		);

		$errorMsg = null;
		$items    = array();
		$conn     = $invoice->getTable()->getConnection();

		try
		{
			$conn->beginTransaction();

			$ids = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));

			$invoice->copyResourceItems($ids);

			$success = true;

			$conn->commit();
		}
		catch (Exception $e)
		{
			$conn->rollback();

			$errorMsg = $e->getMessage();
			$success  = false;
		}

		$data = array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items );

		return $this->renderJson($data);
	}

	public function executeGetDeliveryOrderListings(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'))
		);

		$deliveryOrders = StockInDeliveryOrderTable::getRecordsByStockInInvoice($invoice);

		$deliveryOrders[] = array(
			'id'                  => Constants::GRID_LAST_ROW,
			'delivery_order_no'   => null,
			'delivery_order_date' => null,
		);

		sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url' ));

		$downloadPath = self::getUploadDirForDeliveryOrder();
		$form         = new BaseForm();

		foreach ( $deliveryOrders as $key => $deliveryOrder )
		{
			$deliveryOrders[$key]['delivery_order_date'] = is_null($deliveryOrder['delivery_order_date']) ? null : date('d/m/Y', strtotime($deliveryOrder['delivery_order_date']));
			$deliveryOrders[$key]['_csrf_token']         = $form->getCSRFToken();
			$deliveryOrders[$key]['upload_file']         = ( $deliveryOrder['id'] > 0 ) ? '<a href="#">Upload</a>' : null;
			$deliveryOrders[$key]['download_file']       = ( $deliveryOrder['id'] > 0 ) ? '-' : null;
			$deliveryOrders[$key]['remove_file']         = ( $deliveryOrder['id'] > 0 ) ? '-' : null;
			$deliveryOrders[$key]['remove_file_url']     = null;

			if ( !empty( $deliveryOrder['delivery_order_upload'] ) )
			{
				$downloadURL = public_path("{$downloadPath}/{$deliveryOrder['delivery_order_upload']}", true);
				$removeURL   = public_path("stockIn/removeDeliveryOrderFileAttachment/deliveryOrderId/{$deliveryOrder['id']}", true);

				$deliveryOrders[$key]['file_name']       = $deliveryOrder['delivery_order_upload'];
				$deliveryOrders[$key]['download_file']   = "<a href=\"{$downloadURL}\" download>Download</a>";
				$deliveryOrders[$key]['remove_file']     = "<a href='#'>Remove</a>";
				$deliveryOrders[$key]['remove_file_url'] = $removeURL;
			}

			unset( $deliveryOrder );
		}

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $deliveryOrders,
		));
	}

	public function executeGetDeliveryOrderFormInformation(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'))
		);

		$deliveryOrder = Doctrine_Core::getTable('StockInDeliveryOrder')->find($request->getParameter('deliveryOrderId'));
		$deliveryOrder = $deliveryOrder ? : new StockInDeliveryOrder();

		$form   = new StockInDeliveryOrderForm($deliveryOrder);
		$object = $form->getObject();

		$data['formInformation'] = array(
			'stock_in_delivery_order[stock_in_invoice_id]'      => $object->stock_in_invoice_id ? : $invoice->id,
			'stock_in_delivery_order[delivery_order_no]'        => $object->delivery_order_no,
			'stock_in_delivery_order[delivery_order_date]'      => $object->delivery_order_date ? date('Y-m-d', strtotime($object->delivery_order_date)) : date('Y-m-d'),
			'stock_in_delivery_order[delivery_order_date_text]' => date('d/m/Y', strtotime($object->delivery_order_date)),
			'stock_in_delivery_order[_csrf_token]'              => $form->getCSRFToken(),
		);

		return $this->renderJson($data);
	}

	public function executeSaveDeliveryOrderFormInformation(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('POST') and
			$invoice = Doctrine_Core::getTable('StockInInvoice')->find($request->getParameter('invoiceId'))
		);

		$deliveryOrder = Doctrine_Core::getTable('StockInDeliveryOrder')->find($request->getParameter('deliveryOrderId'));
		$deliveryOrder = $deliveryOrder ? $deliveryOrder : new StockInDeliveryOrder();

		$form = new StockInDeliveryOrderForm($deliveryOrder);

		if ( $this->isFormValid($request, $form) )
		{
			$deliveryOrder = $form->save();

			$id      = $deliveryOrder->getId();
			$success = true;
			$errors  = array();
		}
		else
		{
			$id      = $request->getPostParameter('deliveryOrderId');
			$errors  = $form->getErrors();
			$success = false;
		}

		$data = array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors );

		return $this->renderJson($data);
	}

	public function executeDeleteDeliveryOrder(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$deliveryOrder = Doctrine_Core::getTable('StockInDeliveryOrder')->find($request->getParameter('deliveryOrderId'))
		);

		$errorMsg = array();

		try
		{
			$deliveryOrder->delete();

			$success = true;
		}
		catch (Exception $e)
		{
			$errorMsg = $e->getMessage();
			$success  = false;
		}

		$data = array( 'success' => $success, 'errorMsg' => $errorMsg );

		return $this->renderJson($data);
	}

	public function executeUploadFileForDeliveryOrder(sfWebRequest $request)
	{
		sfConfig::set('sf_web_debug', false);

		$this->forward404Unless(
			$request->isMethod('POST') and
			$deliveryOrder = Doctrine_Core::getTable('StockInDeliveryOrder')->find($request->getParameter('deliveryOrderId'))
		);

		$success   = false;
		$errors    = array();
		$uploadDir = self::getFullUploadDirForDeliveryOrder();

		$form = new StockInDeliveryOrderUploadForm();

		if ( $this->isFormValid($request, $form) )
		{
			foreach ( $request->getFiles() as $fileArray )
			{
				$uploadedFile     = $fileArray['fileUpload'];
				$currentFileName  = explode('.', $uploadedFile["name"]);
				$fileExtension    = pathinfo($uploadedFile["name"], PATHINFO_EXTENSION);
				$uploadedFileName = sha1($currentFileName[0] . microtime() . mt_rand()) . '.' . $fileExtension;

				try
				{
					if ( !is_dir($uploadDir) )
					{
						mkdir($uploadDir, 0777);
					}

					move_uploaded_file($uploadedFile["tmp_name"], $uploadDir . DIRECTORY_SEPARATOR . $uploadedFileName);

					// will always overwrite the existing uploaded file name if available
					$deliveryOrder->delivery_order_upload = $uploadedFileName;
					$deliveryOrder->save();

					$success = true;

					break;
				}
				catch (Exception $e)
				{
					break;
				}
			}
		}

		$data = array( 'success' => $success, 'errorMsgs' => $errors );

		return $this->renderJson($data);
	}

	public function executeRemoveDeliveryOrderFileAttachment(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('POST') and
			$deliveryOrder = Doctrine_Core::getTable('StockInDeliveryOrder')->find($request->getParameter('deliveryOrderId'))
		);

		$filePath = self::getFullUploadDirForDeliveryOrder();

		unlink("{$filePath}/{$deliveryOrder->delivery_order_upload}");

		$deliveryOrder->deleteAttachedFile();

		$data = array( 'success' => true );

		return $this->renderJson($data);
	}

	public function executeGetDeliveryOrderItemQuantities(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$deliveryOrder = Doctrine_Core::getTable('StockInDeliveryOrder')->find($request->getParameter('deliveryOrderId'))
		);

		$items              = array();
		$invoiceItemIds     = array();
		$invoiceFromDbItems = array();

		$deliveryOrderItems = StockInDeliveryOrderItemQuantityTable::getItemQuantitiesByStockInDeliveryOrder($deliveryOrder);

		foreach ( $deliveryOrderItems as $deliveryOrderItem )
		{
			$invoiceItemIds[$deliveryOrderItem['resource_item_id']] = $deliveryOrderItem['resource_item_id'];

			$invoiceQuantity = number_format((float) $deliveryOrderItem['invoice_quantity'], 2, '.', '');
			$doQuantity      = number_format((float) $deliveryOrderItem['delivery_order_quantity'], 2, '.', '');

			$invoiceFromDbItems[$deliveryOrderItem['resource_item_id']] = array(
				'stockInItemId'       => $deliveryOrderItem['id'],
				'qtyId'               => $deliveryOrderItem['qtyid'],
				'invoiceQuantity'     => $invoiceQuantity,
				'doQuantity'          => $doQuantity,
				'stockInItemRemarkId' => $deliveryOrderItem['remark_id'],
				'remarks'             => $deliveryOrderItem['remark'],
			);
		}

		if ( !empty( $invoiceItemIds ) )
		{
			$items = StockInInvoiceItemTable::getHierarchyDeliveryOrderItemListingFromResourceLibraryByStockInItemIds($invoiceItemIds, $invoiceFromDbItems);
		}

		// empty row
		$items[] = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => null,
			'uom'         => null,
			'remarks'     => null,
		);

		$form = new BaseForm();

		// assign csrf token to each available item
		foreach ( $items as $key => $item )
		{
			$items[$key]['_csrf_token'] = $form->getCSRFToken();
		}

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items,
		));
	}

	public function executeUpdateDeliveryOrderItemQuantity(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('POST') and
			$deliveringOrder = Doctrine_Core::getTable('StockInDeliveryOrder')->find($request->getParameter('deliveryOrderId')) and
			$invoiceItem = Doctrine_Core::getTable('StockInInvoiceItem')->find($request->getParameter('stockInItemId'))
		);

		$items   = array();
		$itemQty = Doctrine_Core::getTable('StockInDeliveryOrderItemQuantity')->find($request->getParameter('qtyId'));
		$itemQty = $itemQty ? : new StockInDeliveryOrderItemQuantity();

		try
		{
			$val = $request->getPostParameter('val', 0);
			$val = empty( $val ) ? 0 : $val;

			if ( $itemQty->isNew() )
			{
				$itemQty->stock_in_delivery_order_id = $deliveringOrder->id;
				$itemQty->stock_in_invoice_item_id   = $invoiceItem->id;
			}

			$itemQty->quantity = $val;
			$itemQty->save();

			$id      = $itemQty->id;
			$success = true;
			$errors  = array();

			$items[] = array(
				'qtyId'      => $id,
				'doQuantity' => number_format((float) $val, 2, '.', ''),
			);
		}
		catch (Exception $e)
		{
			$id      = $request->getPostParameter('qtyId');
			$errors  = $e;
			$success = false;
		}

		$data = array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors, 'items' => $items );

		return $this->renderJson($data);
	}

	public static function getFullUploadDirForInvoice()
	{
		return sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . self::getUploadDirForInvoice();
	}

	public static function getUploadDirForInvoice()
	{
		return 'uploads' . DIRECTORY_SEPARATOR . 'stock_in_invoice';
	}

	public static function getFullUploadDirForDeliveryOrder()
	{
		return sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . self::getUploadDirForDeliveryOrder();
	}

	public static function getUploadDirForDeliveryOrder()
	{
		return 'uploads' . DIRECTORY_SEPARATOR . 'stock_in_delivery_order';
	}

}