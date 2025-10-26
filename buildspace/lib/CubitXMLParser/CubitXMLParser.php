<?php

/**
 * Class CubitXMLParser
 *
 * Will be used to parse XML file exported from Cubit
 *
 * Parse the file into array and filter array into tree descendant
 * Return the tree format so that other process class can generate
 * query to insert the information into BuildSpace
 *
 * @property mixed rootTradeContainerId
 * @property mixed rootTradeContainerGlobalId
 */
class CubitXMLParser extends SOQParser
{
    /**
     * Parse the XML file to get information about item's hierarchy and item's information
     */
    public function parseFileIntoArray()
    {
        $data = json_decode($this->getArrayFromFile(), true);

        // check for validity of imported XML file is from Cubit
        $this->checkValidityOfFileParsed($data);

        // will have RootTradeContainer as Project level
        $this->rootTradeContainerId       = $data['Id'];
        $this->rootTradeContainerGlobalId = $data['GlobalId'];

        $nodes = $data['TradeNodes']['TradeNode'];

        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($nodes), RecursiveIteratorIterator::SELF_FIRST);

        $data = array();
        $code = null;

        $hasItemRefCode = (array_key_exists('ItemRefCode', array_key_exists(0,$nodes) ? $nodes[0] : $nodes));

        $cubitKeyMaps = array(
            'GlobalId'    => 'sourceId',
            'ItemRefCode' => 'itemRefId',
            'Description' => 'description',
            'IsTradeItem' => 'isItem'
        );

        $cubitEstimatingKeyMaps = array(
            'Quantity'        => 'qty',
            'QuantityFormula' => 'qty_formula',
            'Unit'            => 'unit'
        );

        if(!$hasItemRefCode)
        {
            $cubitKeyMaps += $cubitEstimatingKeyMaps;
        }

        $levels = array();

        $itemSourceId = null;

        $estimatingComponentDepth = null;
        $buildUpItemDepth = null;

        foreach ($iterator as $key => $value)
        {
            if(empty($value))
                continue;

            if(strtolower($key) == 'code' && !array_key_exists($value.'_'.$iterator->getDepth(), $data))
            {
                $code = $value.'_'.$iterator->getDepth();
                $data[$code] = array();
            }

            if($code && (substr($code, strpos($code, "_") + 1) == $iterator->getDepth()) && array_key_exists($key, $cubitKeyMaps))
            {
                if( ($depth = strpos($code, '_')) !== FALSE )
                    $depth = substr($code, $depth + 1);

                if($depth == 1)
                    $levels = array();

                if(!in_array($depth, $levels))
                    array_push($levels, $depth);

                $data[$code][$cubitKeyMaps[$key]] = $value;

                $sourceIdKey = $hasItemRefCode ? 'itemRefId' : 'sourceId';

                if(array_key_exists($sourceIdKey, $data[$code]))
                {
                    $itemSourceId = $data[$code][$sourceIdKey];

                    $estimatingComponentDepth = null;
                    $buildUpItemDepth = null;//reset buildup depth for each trade items
                }

                if(!array_key_exists('level', $data[$code]))
                {
                    $data[$code]['level'] = array_search($depth, $levels);
                }
            }

            if($code && !empty($itemSourceId))
            {
                if($hasItemRefCode)
                {
                    if(strtolower($key) == 'estimatingcomponent')
                    {
                        $estimatingComponentDepth = $iterator->getDepth();

                        foreach($cubitEstimatingKeyMaps as $estimatingKey => $estimatingVal)
                        {
                            $data[$code][$estimatingVal] = null;
                        }
                    }

                    if(array_key_exists($key, $cubitEstimatingKeyMaps) && !empty($value) && !empty($estimatingComponentDepth) && $iterator->getDepth() == $estimatingComponentDepth+1)
                    {
                        $data[$code][$cubitEstimatingKeyMaps[$key]] = $value;
                    }
                }

                if(strtolower($key) == 'tradeitemresult')
                {
                    $buildUpItemDepth = $iterator->getDepth();
                }

                if(!empty($buildUpItemDepth) && $iterator->getDepth() == $buildUpItemDepth)
                {
                    if(!array_key_exists(0, $value))
                        $buildUpItems[0] = $value;//weird how cubit store buildup info if there is only one build up item
                    else
                        $buildUpItems = $value;

                    foreach($buildUpItems as $buildUpItem)
                    {
                        if(is_array($buildUpItem) && array_key_exists('Factor', $buildUpItem) && array_key_exists('Description', $buildUpItem) && !empty($buildUpItem['Description']))
                        {
                            if(!array_key_exists($itemSourceId, $this->buildUpItemList))
                            {
                                $this->buildUpItemList[$itemSourceId] = array();
                            }

                            $this->buildUpItemList[$itemSourceId][] = $buildUpItem;
                        }
                    }

                }
            }
        }

        $tradeId = null;

        $currentItemKey = null;

        $tempTradeList = array();

        foreach($data as $key => $value)
        {
            if(array_key_exists('sourceId', $value))
            {
                $currentItemKey = $key;
            }

            if(!array_key_exists('sourceId', $value) || !array_key_exists('description', $value))
            {
                if(array_key_exists('unit', $value))
                {
                    $data[$currentItemKey]['unit'] = $value['unit'];
                }

                unset($data[$key]);
                continue;
            }

            if($value['level'] == 0)
            {
                $tempTradeList[] = $value;
                $tradeId = $hasItemRefCode ? $value['itemRefId'] : $value['sourceId'];

                // for case where trade in cubit is an item, we won't unset the data instead we move the level to 1 as it will become first level in SOQ item list
                if(!array_key_exists('isItem', $value) || !$value['isItem'])
                {
                    unset($data[$key]);
                    continue;
                }
            }

            $data[$key]['level'] = $value['level'] > 0 ? $value['level'] - 1 : 0;
            $data[$key]['trade_id'] = $tradeId;
        }

        if($hasItemRefCode)
        {
            foreach($data as $i)
            {
                $i['sourceId'] = $i['itemRefId'];
                unset($i['itemRefId']);
                $this->itemList[$i['sourceId']] = $i;
            }

            foreach($tempTradeList as $t)
            {
                $t['sourceId'] = $t['itemRefId'];
                unset($t['itemRefId']);
                $this->tradeList[$t['sourceId']] = $t;
            }
        }
        else
        {
            foreach($data as $i)
            {
                $this->itemList[$i['sourceId']] = $i;
            }

            foreach($tempTradeList as $t)
            {
                $this->tradeList[$t['sourceId']] = $t;
            }
        }

        unset( $data, $tempTradeList );
    }

    /**
     * Check the validity of the XML file of Cubit
     *
     * @param $data
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function checkValidityOfFileParsed($data)
    {
        if ( !isset( $data['TradeNodes']['TradeNode'] ) )
        {
            throw new InvalidArgumentException('Invalid Cubit XML File Imported.');
        }
    }

}