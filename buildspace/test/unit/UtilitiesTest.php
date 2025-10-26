<?php
require_once dirname(__FILE__).'/../bootstrap/unit.php';

//Test for arrayChunkSPL
function generateArray($arrSize, $arrItemSize)
{
    $output = [];
    for($i=0;$i<$arrSize;$i++)
    {
        $output[$i] = [];
        for($j=0;$j<$arrItemSize;$j++)
        {
            $output[$i][$j] = $i+1;
        }
    }
    return $output;
}

function testArrayChunkSPL($arrSize, $chunkSize, $arrItemSize)
{
    $t = new lime_test();

    $arr = SplFixedArray::fromArray(generateArray($arrSize,$arrItemSize));

    $chunks = Utilities::arrayChunkSPL($arr,$chunkSize);

    foreach($chunks as $chunk)
    {
        if($chunk->count()>$chunkSize) $t->fail("A chunk is too large ({$arrSize})");
    }

    $noOfChunks = ceil($arrSize/$chunkSize);

    $t->is($chunks->count(), $noOfChunks);

    $lastItemIndexOfLastChunk = ($arrSize%$chunkSize)-1;

    if($lastItemIndexOfLastChunk == -1) $lastItemIndexOfLastChunk = $chunkSize-1;

    if($chunks[$noOfChunks-1]->count() == 0)
    {
        $t->fail("Chunk is empty ({$arrSize})");
    }

    if($chunks[$noOfChunks-1]->offsetExists($lastItemIndexOfLastChunk))
    {
        $t->is($chunks[$noOfChunks-1][$lastItemIndexOfLastChunk], [$arrSize,$arrSize,$arrSize]);
    }
    else
    {
        $t->fail("There is a missing item ({$arrSize})");
    }

    if(array_key_exists($lastItemIndexOfLastChunk+1, $chunks[$noOfChunks-1]))
    {
        $t->fail("There is an extra item ({$arrSize})");
    }
}

for($i=1;$i<=11;$i++)
{
    testArrayChunkSPL($i, 2, 3);
}


