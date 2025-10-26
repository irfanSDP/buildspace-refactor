<?php
class sfBuildspaceExportSubPackageBillXML extends sfBuildspaceExportBillXML
{
	protected $withRate;

    function __construct( $filename = null, $uploadPath = null, $billId, $extension = null, $deleteFile = null, $withRate = false )
    {
    	$this->withRate = $withRate;

		$bill = ProjectStructureTable::getInstance()->find($billId);
		
		$project = ProjectStructureTable::getInstance()->find($bill->root_id);
		
		$sfBillRefGenerator = new sfSubPackageBillReferenceGenerator($bill);
		
		$sfBillRefGenerator->process();
		
		$this->newBillRef = $sfBillRefGenerator->getNewBillRef();
		
		unset($sfBillRefGenerator, $project, $bill);
		
		parent::__construct( $filename, $uploadPath, $billId, $extension, $deleteFile );
    }
	
	public function processItems($items)
    {
        if(count($items) > 0)
        {
            foreach($items as $item)
            {
                $uom = (array_key_exists('UnitOfMeasurement', $item)) ? true : false;
				
				if(array_key_exists($item['id'], $this->newBillRef))
				{
					$item['bill_ref_element_no'] = $this->newBillRef[$item['id']]['elementNo'];
					$item['bill_ref_page_no'] = $this->newBillRef[$item['id']]['pageCount'];
					$item['bill_ref_char'] = $this->newBillRef[$item['id']]['char'];
 				}

                if($uom)
                {
                    $uom = $item['UnitOfMeasurement'];

                    if($uom['id'] && !array_key_exists($uom['id'], $this->usedUnits))
                    {
                        $this->usedUnits[$uom['id']] = $uom;
                    }

                    unset($item['UnitOfMeasurement']);
                }

                $lumpSumpPercent = false;
                $primeCostRate = false;
                $rate = false;
                $typeRefGrandTotal = false;

                //CheckItemType
                switch($item['type'])
                {
                    case BillItem::TYPE_ITEM_PC_RATE:
                        if(array_key_exists('PrimeCostRate', $item))
                        {
                            $primeCostRate = $item['PrimeCostRate'];

                            if(!$this->withRate)
                            {
                                unset($primeCostRate['wastage_percentage'], $primeCostRate['wastage_amount'], $primeCostRate['labour_for_installation'], $primeCostRate['other_cost'], $primeCostRate['profit_percentage'], $primeCostRate['profit_amount']);

                                $primeCostRate['total'] = $primeCostRate['supply_rate'];
                            }

                            $itemRate = $item['rate'] ?? $primeCostRate['total'];

                            $grandTotalType = 0;

                            if($item['BillItemTypeReferences'])
                            {
                                foreach($item['BillItemTypeReferences'] as $type)
                                {
                                    $totalPerUnit = number_format($type['FormulatedColumns'][0]['final_value'] * $itemRate, 2,'.','');

                                    foreach($this->billColumnSettings as $column)
                                    {
                                        if((int) $column->id == $type['bill_column_setting_id'])
                                        {
                                            $grandTotalType += (float)number_format($totalPerUnit * (int) $column->quantity, 2,'.','');
                                        }

                                        unset($column);
                                    }

                                    unset($type);
                                }
                            }

                            $item['grand_total_after_markup'] = $item['grand_total'] = $grandTotalType;

                            $typeRefGrandTotal = true;

                            // PC Rates item always has rates, as supply_rate is always included.
                            $rate = array(
                                'relation_id' => $item['id'],
                                'value'       => $itemRate,
                                'final_value' => $itemRate,
                                'column_name' => BillItem::FORMULATED_COLUMN_RATE
                            );

                            unset($item['PrimeCostRate']);
                            unset($item['rate']);
                        }

                        break;
                    case BillItem::TYPE_ITEM_LUMP_SUM_PERCENT:

                        if(array_key_exists('LumpSumPercentage', $item))
                        {
                            $lumpSumpPercent = $item['LumpSumPercentage'];
                            unset($item['LumpSumPercentage']);
                        }

                        if(array_key_exists('grand_total_after_markup', $item))
                        {
                            unset($item['grand_total_after_markup']);
                        }

                        break;
                    case BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE:
                        //Do nothing for now
                        if(array_key_exists('grand_total_after_markup', $item))
                        {
                            $item['grand_total'] = $item['grand_total_after_markup'];

                            $typeRefGrandTotal = true;

                            $finalRates = $this->getRatesByItemId($item['id']);
                            
                            $rate = array(
                                'relation_id' => $item['id'],
                                'value' => $finalRates,
                                'final_value' => $finalRates,
                                'column_name' => BillItem::FORMULATED_COLUMN_RATE
                            );
                            
                        }

                        if(array_key_exists('LumpSumPercentage', $item))
                        {
                            unset($item['LumpSumPercentage']);
                        }
                        break;
                    case BillItem::TYPE_ITEM_NOT_LISTED:

                        if(array_key_exists('grand_total_after_markup', $item))
                        {
                            unset($item['grand_total_after_markup']);
                        }

                        if(array_key_exists('LumpSumPercentage', $item))
                        {
                            unset($item['LumpSumPercentage']);
                        }

                        if(array_key_exists('uom_id', $item))
                        {
                            unset($item['uom_id']);
                        }

                        if(array_key_exists('BillItemTypeReferences', $item))
                        {
                            unset($item['BillItemTypeReferences']);
                        }

                        if(array_key_exists('description', $item))
                        {
                            unset($item['description']);
                        }

                        break;
                    default:
                        if(!$this->withRate && array_key_exists('grand_total_after_markup', $item))
                        {
                            unset($item['grand_total_after_markup']);
                        }
						
						if(!$this->withRate && array_key_exists('grand_total', $item))
                        {
                            unset($item['grand_total']);
                        }

                        if(count($item['BillItemTypeReferences']))
                        {
                            $grandTotalType = 0;
                            $grandTotalQtyPerType = 0;

                            foreach($item['BillItemTypeReferences'] as $type)
                            {
                                $totalPerUnit = 0;
                                $qtyPerUnit = 0;

                                if( $type['include'] )
                                {
                                    $useOriginalQty = ((string) $this->billColumnSettings->{self::TAG_COLUMN}->use_original_quantity == "1") ? true : false;

                                    foreach($type['FormulatedColumns'] as $formulatedColumn)
                                    {
                                        if(($formulatedColumn['column_name'] == 'quantity_per_unit') && ($useOriginalQty))
                                        {
                                            $qtyPerUnit = $formulatedColumn['final_value'];
                                        }
                                        elseif(($formulatedColumn['column_name'] == 'quantity_per_unit_remeasurement') && (!$useOriginalQty))
                                        {
                                            $qtyPerUnit = $formulatedColumn['final_value'];
                                        }
                                    }

                                    $totalPerUnit = array_key_exists('rate', $item) ? round($qtyPerUnit * $item['rate'], 2) : 0;
                                }
                                
                                foreach($this->billColumnSettings as $column)
                                {
                                    if((int) $column->id == $type['bill_column_setting_id'])
                                    {
                                        $grandTotalType += (float)round($totalPerUnit * (int) $column->quantity, 2);
                                        $grandTotalQtyPerType+= $qtyPerUnit * (int) $column->quantity;
                                    }

                                    unset($column);
                                }

                                unset($type);
                            }

                            $item['grand_total_after_markup'] = $item['grand_total'] = $grandTotalType;
                            $item['grand_total_quantity'] = $grandTotalQtyPerType;
                        }

						if(!$this->withRate)
						{
							if(array_key_exists('rate', $item))
							{
								unset($item['rate']);
							}
						}
						else
						{
							if(array_key_exists('rate', $item))
							{
                                $typeRefGrandTotal = true;
								
								$rate = array(
	                                'relation_id' => $item['id'],
	                                'value' => $item['rate'],
	                                'final_value' => $item['rate'],
	                                'column_name' => BillItem::FORMULATED_COLUMN_RATE
	                            );
								
								unset($item['rate']);
							}
						}

                        if(array_key_exists('LumpSumPercentage', $item))
                        {
                            unset($item['LumpSumPercentage']);
                        }
                        break;
                }

                $typeRefs = (array_key_exists('BillItemTypeReferences', $item)) ? true : false;

                if($typeRefs && count($item['BillItemTypeReferences'] > 0))
                {
                    $typeRefs = $item['BillItemTypeReferences'];

                    unset($item['BillItemTypeReferences']);
                }
                else
                {
                    $typeRefs = false;
                }

                $this->addItemChildren($item);

                if($lumpSumpPercent)
                {
                    $this->addLumpSumpPercentChild( $lumpSumpPercent );
                }

                if($primeCostRate)
                {
                    $this->addPrimeCostRateChild( $primeCostRate );
                }

                if($rate && count($rate))
                {
                    $this->addRateChild( $rate );
                }

                if($typeRefs)
                {
                    $this->processTypeRef($typeRefs, $typeRefGrandTotal);
                }
            }
        }
    }

	public function processTypeRef( $typeRefs , $typeRefGrandTotal = false)
    {
        $this->createTypeRefTag();

        foreach($typeRefs as $typeRef)
        {
            $typeFc = (array_key_exists('FormulatedColumns', $typeRef)) ? true : false;

            if(!$typeRefGrandTotal)
            {
            	if(array_key_exists('grand_total', $typeRef))
                {
                    unset($typeRef['grand_total']);
                }
				
				if(array_key_exists('grand_total_after_markup', $typeRef))
                {
                    unset($typeRef['grand_total_after_markup']);
                }
            }

            if($typeFc && count($typeRef['FormulatedColumns'] > 0))
            {
                $typeFc = $typeRef['FormulatedColumns'];

                unset($typeRef['FormulatedColumns']);
            }
            else
            {
                $typeFc = false;
            }

            $this->addTypeRefChildren( $typeRef );

            $columnName = (array_key_exists($typeRef['bill_column_setting_id'], $this->columnName)) ? $this->columnName[$typeRef['bill_column_setting_id']] : null;

            $count = 0;
            
            foreach($typeFc as $fc)
            {
                if($fc['column_name'] == $columnName)
                {
                    $this->createQtyTag( $fc, $count );

                    $count++;
                }
            }
        }
    }
}
