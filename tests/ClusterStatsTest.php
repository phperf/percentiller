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
        $h = new \Phperf\ClusterStats();
        $h->maxBuckets = 30;
        srand(1);
        for ($i = 0; $i < 40000; ++$i) {
            $item = rand(10000, 300000) / 1000;
            //echo $item, PHP_EOL;
            $h->add($item);
        }
        //$items = array(300,3,8,4,3,5,7,3,4,6,8,8,5,4,2,9,8,7, 100 ,6,1,2,6,3,2,6,8,4,100,110,1000,1e6);
        //$items = array(9, 6, 6,6 ,7);
        //foreach ($items as $item) {
        //    $h->add($item);
        //}

        foreach ($h->index as $bucket) {
            $bucket []= $bucket[1] - $bucket[0];
            print_r($bucket);
        }
        print_r($h);

    }

}