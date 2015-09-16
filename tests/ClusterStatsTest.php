<?php

/**
 * Created by PhpStorm.
 * User: vearutop
 * Date: 02.09.2015
 * Time: 21:13
 */
class ClusterStatsTest extends PHPUnit_Framework_TestCase
{
    public function testOne() {
        $cStats = new \Phperf\ClusterStats();
        $cStats->maxBuckets = 30;
        srand(1);
        for ($i = 0; $i < 4000; ++$i) {
            $item = rand(10000, 300000) / 1000;
            //echo $item, PHP_EOL;
            $cStats->add($item);
        }
        //$items = array(300,3,8,4,3,5,7,3,4,6,8,8,5,4,2,9,8,7, 100 ,6,1,2,6,3,2,6,8,4,100,110,1000,1e6);
        //$items = array(9, 6, 6,6 ,7);
        //foreach ($items as $item) {
        //    $h->add($item);
        //}

        foreach ($cStats->buckets as $bucket) {
            $bucket []= $bucket[1] - $bucket[0];
            //print_r($bucket);
        }

        $this->assertSame($cStats->topPercentile(1), $cStats->min); // all items are greater or equal than min
        $this->assertSame($cStats->bottomPercentile(1), $cStats->max);
        $this->assertGreaterThanOrEqual($cStats->arithmeticAverage, $cStats->bottomPercentile(0.5));
        $this->assertLessThanOrEqual($cStats->arithmeticAverage, $cStats->topPercentile(0.5));

        /*
        var_dump($cStats->bottomPercentile(1));
        var_dump($cStats->bottomPercentile(0.99));
        var_dump($cStats->bottomPercentile(0.95));
        var_dump($cStats->bottomPercentile(0.90));
        var_dump($cStats->bottomPercentile(0.5));
        var_dump($cStats->bottomPercentile(0));
        var_dump('---');

        var_dump($cStats->topPercentile(1));
        var_dump($cStats->topPercentile(0.95));
        var_dump($cStats->topPercentile(0.5));
        var_dump($cStats->topPercentile(0));

        */


        //print_r($h);

    }

}