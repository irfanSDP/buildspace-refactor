<?php

use PCK\Users\User;
use PCK\Projects\Project;
use PCK\RequestForVariation\RequestForVariation;
use PCK\RequestForVariation\RequestForVariationRepository;
use PCK\Buildspace\VariationOrder;
use PCK\Buildspace\VariationOrderItem;

class RequestForVariationCostEstimateController extends BaseController {

    private $requestForVariationRepository;
    private $requestForVariationCostEstimateImportForm;

    public function __construct(RequestForVariationRepository $requestForVariationRepository, \PCK\Forms\RequestForVariationCostEstimateImportForm $requestForVariationCostEstimateImportForm)
    {
        $this->requestForVariationRepository             = $requestForVariationRepository;
        $this->requestForVariationCostEstimateImportForm = $requestForVariationCostEstimateImportForm;
    }

    public function getCostEstimateList(Project $project, RequestForVariation $requestForVariation)
    {
        $variationOrderItems = $this->requestForVariationRepository->getVariationOrderItems($requestForVariation);

        $variationOrderItems[] = [
            'id'                 => -1,
            'bill_ref'           => '',
            'description'        => '',
            'type'               => VariationOrderItem::TYPE_WORK_ITEM,
            'uom_id'             => -1,
            'uom_symbol'         => '',
            'reference_quantity' => 0,
            'reference_rate'     => 0,
            'reference_amount'   => 0,
            'priority'           => -1,
            'remarks'            => null,
        ];

        return Response::json($variationOrderItems);
    }

    public function itemAdd()
    {
        $inputs = Input::all();

        $rfvId               = (int)$inputs['rfv_id'];
        $requestForVariation = RequestForVariation::findOrFail($rfvId);

        if( array_key_exists('before_id', $inputs) )
        {
            $id       = (int)$inputs['before_id'];
            $nextItem = VariationOrderItem::findOrFail($id);
            $data     = [];
        }
        else
        {
            $nextItem   = null;
            $itemId     = (int)$inputs['id'];
            $prevItemId = (int)$inputs['prev_item_id'];
            $fieldName  = array_key_exists('field', $inputs) ? $inputs['field'] : null;
            $fieldValue = array_key_exists('val', $inputs) ? $inputs['val'] : null;

            $data = [
                'id'           => $itemId,
                'field'        => $fieldName,
                'value'        => $fieldValue,
                'prev_item_id' => $prevItemId
            ];
        }

        $variationOrderItem = $requestForVariation->costEstimateItemAdd($data, $nextItem);

        $pdo = \DB::connection('buildspace')->getPdo();

        $buildspaceProjectId = $requestForVariation->project->getBsProjectMainInformation()->project_structure_id;

        $stmt = $pdo->prepare("SELECT i.id, i.bill_ref, i.description, i.type, i.reference_quantity, i.reference_rate, i.reference_amount, i.priority, i.remarks,
            uom.id AS uom_id, uom.symbol AS uom_symbol
            FROM bs_variation_order_items i
            LEFT JOIN bs_unit_of_measurements uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE i.id = " . $variationOrderItem->id . "
            AND i.is_from_rfv IS TRUE AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $variationOrderId = $variationOrderItem->variation_order_id;

        $variationOrderItem = $stmt->fetch(PDO::FETCH_ASSOC);

        $result['item'] = $variationOrderItem;

        $stmt = $pdo->prepare("SELECT i.id, i.priority
            FROM bs_variation_order_items i
            WHERE i.variation_order_id = " . $variationOrderId . "
            AND i.priority > " . $variationOrderItem['priority'] . "
            AND i.is_from_rfv IS TRUE AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $result['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result['items'][] = [
            'id'                 => -1,
            'bill_ref'           => '',
            'description'        => '',
            'type'               => VariationOrderItem::TYPE_WORK_ITEM,
            'uom_id'             => -1,
            'uom_symbol'         => '',
            'reference_rate'     => 0,
            'reference_quantity' => 0,
            'reference_amount'   => 0,
            'priority'           => -1,
            'remarks'            => null,
        ];

        $result['nett_omission_addition'] = $requestForVariation->getAdjustedNettOmissionAddition();

        return Response::json($result);
    }

    public function itemUpdate()
    {
        $inputs = Input::all();

        $rfvId      = (int)$inputs['rfv_id'];
        $itemId     = (int)$inputs['id'];
        $prevItemId = (int)$inputs['prev_item_id'];
        $fieldName  = $inputs['field'];
        $fieldValue = array_key_exists('val', $inputs) ? $inputs['val'] : null;

        $requestForVariation = RequestForVariation::findOrFail($rfvId);
        $variationOrderItem  = VariationOrderItem::findOrFail($itemId);

        $columns = [
            'bill_ref',
            'description',
            'type',
            'uom_id',
            'reference_rate',
            'reference_quantity',
            'remarks',
        ];

        if( in_array($fieldName, $columns) )
        {
            if( $fieldName == 'uom_id' )
            {
                $fieldValue = ( (int)$fieldValue > 0 ) ? $fieldValue : null;
            }
            elseif( $fieldName == 'type' )
            {
                $fieldValue = empty( $fieldValue ) ? $variationOrderItem->type : $fieldValue;
            }

            if( $fieldName == 'reference_rate' or $fieldName == 'reference_quantity' )
            {
                $fieldValue                       = is_numeric($fieldValue) ? $fieldValue : 0;
                $variationOrderItem->{$fieldName} = number_format($fieldValue, 2, '.', '');
            }
            else
            {
                $variationOrderItem->{$fieldName} = trim($fieldValue);
            }
        }

        $variationOrderItem->save();

        $pdo = \DB::connection('buildspace')->getPdo();

        $buildspaceProjectId = $requestForVariation->project->getBsProjectMainInformation()->project_structure_id;

        $stmt = $pdo->prepare("SELECT i.id, i.bill_ref, i.description, i.type, i.reference_quantity, i.reference_rate, i.reference_amount, i.priority, i.remarks,
            uom.id AS uom_id, uom.symbol AS uom_symbol
            FROM bs_variation_order_items i
            LEFT JOIN bs_unit_of_measurements uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE i.id = " . $variationOrderItem->id . "
            AND i.is_from_rfv IS TRUE AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $variationOrderItem = $stmt->fetch(PDO::FETCH_ASSOC);

        $variationOrderItem['uom_id']     = $variationOrderItem['uom_id'] > 0 ? $variationOrderItem['uom_id'] : -1;
        $variationOrderItem['uom_symbol'] = $variationOrderItem['uom_id'] > 0 ? $variationOrderItem['uom_symbol'] : '';

        $result['item']                   = $variationOrderItem;
        $result['items'][]                = $variationOrderItem;
        $result['nett_omission_addition'] = $requestForVariation->getAdjustedNettOmissionAddition();

        return Response::json($result);
    }

    public function itemDelete()
    {
        $inputs = Input::all();

        $itemId             = (int)$inputs['id'];
        $variationOrderItem = VariationOrderItem::findOrFail($itemId);

        $variationOrderId = $variationOrderItem->variation_order_id;
        $priority         = $variationOrderItem->priority;

        $variationOrderItem->delete();

        $pdo = \DB::connection('buildspace')->getPdo();

        $stmt = $pdo->prepare("SELECT i.id, i.priority
            FROM bs_variation_order_items i
            WHERE i.variation_order_id = " . $variationOrderId . "
            AND i.priority >= " . $priority . "
            AND i.is_from_rfv IS TRUE AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $result['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result['items'][] = [
            'id'                 => -1,
            'bill_ref'           => '',
            'description'        => '',
            'type'               => VariationOrderItem::TYPE_WORK_ITEM,
            'uom_id'             => -1,
            'uom_symbol'         => '',
            'reference_rate'     => 0,
            'reference_quantity' => 0,
            'reference_amount'   => 0,
            'priority'           => -1
        ];

        $variationOrder = VariationOrder::findOrFail($variationOrderId);

        $result['nett_omission_addition'] = $variationOrder->getRequestForVariation()->getAdjustedNettOmissionAddition();

        return Response::json($result);
    }

    public function import(Project $project, RequestForVariation $requestForVariation)
    {
        $this->requestForVariationCostEstimateImportForm->validate(Input::all());

        $transaction = new \PCK\Helpers\DBTransaction(array( 'buildspace' ));

        try
        {
            $transaction->begin();

            $costEstimatesFile = Input::file('cost_estimates');

            $spreadsheet = \PCK\Helpers\SpreadsheetHelper::loadSpreadsheet($costEstimatesFile->getPathname());

            if( Input::get('remove_previous_data') ) $this->requestForVariationRepository->flushData($requestForVariation);

            $allSheetData = array();

            foreach($spreadsheet->getAllSheets() as $sheet)
            {
                $allSheetData = array_merge($allSheetData, $sheet->toArray());
            }

            $this->requestForVariationRepository->addData($requestForVariation, $sheet->toArray());

            $transaction->commit();

            \Flash::success(trans('files.importSuccess'));
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            \Log::error("Unable to import Request For Variation Cost Estimate. RequestForVariation id: [$requestForVariation->id]. CostEstimatesFile extension: [{$costEstimatesFile->getClientOriginalExtension()}]. Exception: {$e->getMessage()}, StackTrace: {$e->getTraceAsString()}");

            \Flash::error(trans('files.importFailure'));
        }

        return Redirect::back();
    }
}
