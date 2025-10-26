<?php

use PCK\Helpers\Arrays;

class ArrayHelperTest extends TestCase {

    public function test_haveSameValues()
    {
        $a = [1,2,3];

        self::assertTrue(Arrays::haveSameValues($a));

        $a = [1,2,3];
        $b = [1,2,3];

        self::assertTrue(Arrays::haveSameValues($a, $b));

        $a = [1,2,3];
        $b = [3,1,2];

        self::assertTrue(Arrays::haveSameValues($a, $b));

        $a = [1,2,3];
        $b = [3,1,2];
        $c = [2,3,1];

        self::assertTrue(Arrays::haveSameValues($a, $b, $c));

        $a = [1,2,3,4];
        $b = [3,1,2];
        $c = [2,3,1];

        self::assertFalse(Arrays::haveSameValues($a, $b, $c));

        $a = [1,2,3];
        $b = [3,4,1,2];
        $c = [2,3,1];

        self::assertFalse(Arrays::haveSameValues($a, $b, $c));

        $a = [1,2,3];
        $b = [3,1,2];
        $c = [2,3,4,1];

        self::assertFalse(Arrays::haveSameValues($a, $b, $c));

        $a = [1,2,3];
        $b = [3,1,2];
        $c = [2,3];

        self::assertFalse(Arrays::haveSameValues($a, $b, $c));

        $a = [];
        $b = [1,2,3];
        $c = [1,2,3];

        self::assertFalse(Arrays::haveSameValues($a, $b, $c));

        $a = [1,2,3];
        $b = [];
        $c = [1,2,3];

        self::assertFalse(Arrays::haveSameValues($a, $b, $c));
    }

}
