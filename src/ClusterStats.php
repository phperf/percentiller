<?php
/**
 * Created by PhpStorm.
 * User: vearutop
 * Date: 02.09.2015
 * Time: 21:13
 */

namespace Phperf;

class ClusterStats
{
    public $maxBuckets = 10;

    private $totalBuckets = 0;
    public $min;
    public $max;
    public $totalCount = 0;
    public $arithmeticAverage = 0;
    public $buckets;

    public function add($value)
    {
        $this->arithmeticAverage = $this->arithmeticAverage * ($this->totalCount / ($this->totalCount + 1))
            + $value / ($this->totalCount + 1);

        ++$this->totalCount;

        if (null === $this->buckets) {
            $this->buckets = array(array($value, $value, 1));
            $this->min = $this->max = $value;
            $this->totalBuckets = 1;
        }
        else {
            if ($value < $this->min) {
                $this->min = $value;
                array_unshift($this->buckets, array($value, $value, 1));
                $this->totalBuckets++;
            }
            elseif ($value > $this->max) {
                $this->max = $value;
                array_push($this->buckets, array($value, $value, 1));
                $this->totalBuckets++;
            }
            elseif ($value === $this->min) {
                ++$this->buckets[0][2];
            }
            elseif ($value === $this->max) {
                ++$this->buckets[$this->totalBuckets - 1][2];
            }
            else {
                $this->insert($value);
            }
        }

        if ($this->totalBuckets > $this->maxBuckets) {
            $this->shrink();
        }
    }

    private function insert($value) {
        $left = 0;
        $right = $this->totalBuckets - 1;
        //var_dump($left, $right, $value);
        //print_r($this->index);
        //die('!');
        while ($right - $left > 0) {
            $position = (int)(($right + $left)/2);
            //var_dump($position);
            $item = $this->buckets[$position];
            if ($value < $item[0]) {
                $right = $position - 1;
            }
            elseif ($value > $item[1]) {
                $left = $position + 1;
            }
            else {
                ++$this->buckets[$position][2];
                return;
            }
           // var_dump($left, $right);
            //die('!!');
        }

        $position = $left;
        $item = $this->buckets[$position];
        if ($value < $item[0]) {
            array_splice($this->buckets, $position, 0, array(array($value, $value, 1)));
            ++$this->totalBuckets;
        }
        elseif ($value > $item[1]) {
            array_splice($this->buckets, $position + 1, 0, array(array($value, $value, 1)));
            ++$this->totalBuckets;
        }
        else {
            ++$this->buckets[$position][2];
        }

            //print_r($this->index);
            //die('SSS');

    }


    private function shrink() {
        //echo 's!';
        //print_r($this->index);
        $minPosition = 0;
        $minCount = $this->buckets[$minPosition][2];
        $mergePosition = 1;
        $mergeCount = $this->buckets[$mergePosition][2];

        //echo 's: ', $min, PHP_EOL;
        for ($position = 1; $position < $this->totalBuckets; ++$position) {
            $currentCount = $this->buckets[$position][2];
            if ($position === $this->totalBuckets - 1) {
                $currentMergePosition = $position - 1;
            }
            else {
                $currentMergePosition = $this->buckets[$position + 1][2] < $this->buckets[$position - 1][2]
                    ? $position + 1
                    : $position - 1;
            }
            $currentMergeCount = $this->buckets[$currentMergePosition][2];

            //echo implode(':', $this->index[$i]), PHP_EOL;
            //echo implode(':', array($min, $sum, $position, $i)), PHP_EOL;
            if ($currentCount < $minCount
                || ($currentCount === $minCount
                    && $currentMergeCount < $mergeCount)) {
                $minPosition = $position;
                $minCount = $currentCount;
                $mergePosition = $currentMergePosition;
                $mergeCount = $currentMergeCount;
            }
        }


        //echo 'sh: ', $minPosition, ' + ', $mergePosition, ' with ', $minCount, ' + ', $mergeCount, PHP_EOL;

        if ($mergePosition > $minPosition) {
            $this->buckets[$minPosition][1] = $this->buckets[$mergePosition][1];
        }
        else {
            $this->buckets[$minPosition][0] = $this->buckets[$mergePosition][0];
        }
        $this->buckets[$minPosition][2] += $this->buckets[$mergePosition][2];
        unset($this->buckets[$mergePosition]);
        $this->buckets = array_values($this->buckets);
        $this->totalBuckets--;
    }


    public function bottomPercentile($percentile = 0.9) {
        $currentCount = 0;
        $percentileCount = $this->totalCount * $percentile;
        $bucket = array(0, 0, 0);
        for ($i = 0; $i < count($this->buckets); ++$i) {
            $bucket = $this->buckets[$i];
            $currentCount += $bucket[2];
            if ($currentCount >= $percentileCount) {
                return $bucket[1];
            }
        }
        return $bucket[1];
    }

    public function topPercentile($percentile = 0.9) {
        $currentCount = 0;
        $percentileCount = $this->totalCount * $percentile;
        $bucket = array(0, 0, 0);
        for ($i = count($this->buckets) - 1; $i >= 0; --$i) {
            $bucket = $this->buckets[$i];
            $currentCount += $bucket[2];
            if ($currentCount >= $percentileCount) {
                return $bucket[0];
            }
        }
        return $bucket[0];
    }

}