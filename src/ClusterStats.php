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

    private $buckets = 0;
    private $min;
    private $max;
    private $totalCount = 0;
    private $arithmeticAverage = 0;
    public $index;

    public function add($value)
    {
        $this->arithmeticAverage = $this->arithmeticAverage * ($this->totalCount / ($this->totalCount + 1))
            + $value / ($this->totalCount + 1);

        ++$this->totalCount;

        if (null === $this->index) {
            $this->index = array(array($value, $value, 1));
            $this->min = $this->max = $value;
            $this->buckets = 1;
        }
        else {
            if ($value < $this->min) {
                $this->min = $value;
                array_unshift($this->index, array($value, $value, 1));
                $this->buckets++;
            }
            elseif ($value > $this->max) {
                $this->max = $value;
                array_push($this->index, array($value, $value, 1));
                $this->buckets++;
            }
            elseif ($value === $this->min) {
                ++$this->index[0][2];
            }
            elseif ($value === $this->max) {
                ++$this->index[$this->buckets - 1][2];
            }
            else {
                $this->insert($value);
            }
        }

        if ($this->buckets > $this->maxBuckets) {
            $this->shrink();
        }
    }

    private function insert($value) {
        $left = 0;
        $right = $this->buckets - 1;
        //var_dump($left, $right, $value);
        //print_r($this->index);
        //die('!');
        while ($right - $left > 0) {
            $position = (int)(($right + $left)/2);
            //var_dump($position);
            $item = $this->index[$position];
            if ($value < $item[0]) {
                $right = $position - 1;
            }
            elseif ($value > $item[1]) {
                $left = $position + 1;
            }
            else {
                ++$this->index[$position][2];
                return;
            }
           // var_dump($left, $right);
            //die('!!');
        }

        $position = $left;
        $item = $this->index[$position];
        if ($value < $item[0]) {
            array_splice($this->index, $position, 0, array(array($value, $value, 1)));
            ++$this->buckets;
        }
        elseif ($value > $item[1]) {
            array_splice($this->index, $position + 1, 0, array(array($value, $value, 1)));
            ++$this->buckets;
        }
        else {
            ++$this->index[$position][2];
        }

            //print_r($this->index);
            //die('SSS');

    }


    public function shrink() {
        //echo 's!';
        //print_r($this->index);
        $minPosition = 0;
        $minCount = $this->index[$minPosition][2];
        $mergePosition = 1;
        $mergeCount = $this->index[$mergePosition][2];

        //echo 's: ', $min, PHP_EOL;
        for ($position = 1; $position < $this->buckets; ++$position) {
            $currentCount = $this->index[$position][2];
            if ($position === $this->buckets - 1) {
                $currentMergePosition = $position - 1;
            }
            else {
                $currentMergePosition = $this->index[$position + 1][2] < $this->index[$position - 1][2]
                    ? $position + 1
                    : $position - 1;
            }
            $currentMergeCount = $this->index[$currentMergePosition][2];

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
            $this->index[$minPosition][1] = $this->index[$mergePosition][1];
        }
        else {
            $this->index[$minPosition][0] = $this->index[$mergePosition][0];
        }
        $this->index[$minPosition][2] += $this->index[$mergePosition][2];
        unset($this->index[$mergePosition]);
        $this->index = array_values($this->index);
        $this->buckets--;
    }
}