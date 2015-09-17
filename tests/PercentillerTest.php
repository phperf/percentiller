<?php

class PercentillerTest extends PHPUnit_Framework_TestCase
{
    public function testRandom() {
        $stats = new \Phperf\Percentiller();
        $stats->maxBuckets = 30;
        $stats->captureBottomItems = 5;
        srand(1);
        for ($i = 0; $i < 4000; ++$i) {
            $item = rand(10000, 300000) / 1000;
            $stats->add($item, array('somedata' => 'somevalue'));
        }

        $this->assertSame($stats->topPercentile(1), $stats->min); // all items are greater or equal than min
        $this->assertSame($stats->bottomPercentile(1), $stats->max);
        $this->assertGreaterThanOrEqual($stats->arithmeticAverage, $stats->bottomPercentile(0.5));
        $this->assertLessThanOrEqual($stats->arithmeticAverage, $stats->topPercentile(0.5));
    }


    public function testFixed() {
        $stats = new \Phperf\Percentiller();
        $stats->maxBuckets = 10;
        $stats->captureBottomItems = 5;
        $stats->captureTopItems = 3;


        $items = array(300, 3, 8, 4, 3, 5, 7, 3, 4, 6, 8, 8, 5, 4, 2, 9, 8, 7, 100, 6, 1, 2, 6, 3, 2, 6, 8, 4, 100, 110,
            1000, 1e6);

        foreach ($items as $index => $value) {
            $stats->add($value, $index);
        }

        $this->assertSame(array(1e6, 1000, 300), $stats->getTopValues());
        $this->assertSame(array(1, 2, 2, 2, 3), $stats->getBottomValues());
        $this->assertSame(array(31, 30, 0), $stats->getTopMetas());
        //$this->assertSame(array(20, 21, 14, 24, 1), $stats->getBottomMetas()); // todo fix for hhvm, php7

        $this->assertSame(110, $stats->topPercentile(0.1)); // 10% values not less than 110
        $this->assertSame(4, $stats->topPercentile(0.7)); // 70% values not less than 4
        $this->assertSame(1, $stats->topPercentile(0.9)); // 90% values not less than 1
        $this->assertSame(1, $stats->topPercentile(1)); // 100% values not less than 1

        $this->assertSame(8, $stats->bottomPercentile(0.7)); // 70% values not greater than 8
        $this->assertSame(300, $stats->bottomPercentile(0.9)); // 90% values not greater than 300
        $this->assertSame(1e6, $stats->bottomPercentile(1)); // 100% values not greater than 1e6
    }

}