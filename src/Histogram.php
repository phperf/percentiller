<?php
/**
 * Created by PhpStorm.
 * User: vearutop
 * Date: 02.09.2015
 * Time: 21:13
 */

namespace Phperf;


use Phperf\Histogram\Node;

class Histogram
{
    public $maxBuckets = 10;

    private $buckets = 0;
    private $index;
    private $min;
    private $max;

    public function add($value)
    {
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
                ++$this->index[$this->buckets-1][2];
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
        $min = $this->index[0][2] + $this->index[1][2];
        $position = 0;
        for ($i = 1; $i < $this->buckets - 1; ++$i) {
            $sum = $this->index[$i][2] + $this->index[$i + 1][2];
            //echo implode(':', array($min, $sum, $position, $i)), PHP_EOL;
            if ($sum < $min) {
                $position = $i;
                $min = $sum;
            }
        }
        $this->index[$position][1] = $this->index[$position + 1][1];
        $this->index[$position][2] += $this->index[$position + 1][2];
        unset($this->index[$position + 1]);
        $this->index = array_values($this->index);
        $this->buckets--;
    }
}