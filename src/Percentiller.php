<?php

namespace Phperf;

class Percentiller
{
    public $maxBuckets = 10;

    private $totalBuckets = 0;
    public $min;
    public $max;
    public $totalCount = 0;
    public $arithmeticAverage = 0;
    public $buckets;


    public $captureTopItems = 0;
    private $topMetas = array();
    private $topValues = array();
    private $topCount = 0;
    private $topLast = null;

    public $captureBottomItems = 0;
    private $bottomMetas = array();
    private $bottomValues = array();
    private $bottomCount = 0;
    private $bottomLast = null;

    private $sampleId = 0;

    public function add($value, $meta = false)
    {
        $this->arithmeticAverage = $this->arithmeticAverage * ($this->totalCount / ($this->totalCount + 1))
            + $value / ($this->totalCount + 1);

        ++$this->totalCount;


        if ($this->captureTopItems) {
            if ($this->topCount < $this->captureTopItems || $this->topLast === null || $this->topLast < $value) {
                ++$this->sampleId;
                $this->topMetas [$this->sampleId]= $meta;
                $this->topValues [$this->sampleId]= $value;
                ++$this->topCount;

                if ($this->topCount > $this->captureTopItems) {
                    arsort($this->topValues);
                    end($this->topValues);
                    $sampleId = key($this->topValues);
                    unset($this->topValues[$sampleId]);
                    unset($this->topMetas[$sampleId]);
                    --$this->topCount;
                }
            }
        }


        if ($this->captureBottomItems) {
            if ($this->bottomCount < $this->captureBottomItems || $this->bottomLast === null || $this->bottomLast > $value) {
                ++$this->sampleId;
                $this->bottomMetas [$this->sampleId]= $meta;
                $this->bottomValues [$this->sampleId]= $value;
                ++$this->bottomCount;

                if ($this->bottomCount > $this->captureBottomItems) {
                    asort($this->bottomValues);
                    end($this->bottomValues);
                    $sampleId = key($this->bottomValues);
                    unset($this->bottomValues[$sampleId]);
                    unset($this->bottomMetas[$sampleId]);
                    --$this->bottomCount;
                }
            }
        }


        if (null === $this->buckets) {
            $this->buckets = array(array($value, $value, 1, $meta));
            $this->min = $this->max = $value;
            $this->totalBuckets = 1;
        }
        else {
            if ($value < $this->min) {
                $this->min = $value;
                array_unshift($this->buckets, array($value, $value, 1, $meta));
                $this->totalBuckets++;
            }
            elseif ($value > $this->max) {
                $this->max = $value;
                array_push($this->buckets, array($value, $value, 1, $meta));
                $this->totalBuckets++;
            }
            elseif ($value === $this->min) {
                ++$this->buckets[0][2];
            }
            elseif ($value === $this->max) {
                ++$this->buckets[$this->totalBuckets - 1][2];
            }
            else {
                $this->insert($value, $meta);
            }
        }

        if ($this->totalBuckets > $this->maxBuckets) {
            $this->shrink();
        }
    }

    private function insert($value, $meta = false) {
        $left = 0;
        $right = $this->totalBuckets - 1;
        while ($right - $left > 0) {
            $position = (int)(($right + $left)/2);
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
        }

        $position = $left;
        $item = $this->buckets[$position];
        if ($value < $item[0]) {
            array_splice($this->buckets, $position, 0, array(array($value, $value, 1, $meta)));
            ++$this->totalBuckets;
        }
        elseif ($value > $item[1]) {
            array_splice($this->buckets, $position + 1, 0, array(array($value, $value, 1, $meta)));
            ++$this->totalBuckets;
        }
        else {
            ++$this->buckets[$position][2];
        }
    }


    private function shrink() {
        //echo 's!';
        //print_r($this->index);
        $minPosition = 0;
        $minCount = $this->buckets[$minPosition][2];
        $mergePosition = 1;
        $mergeCount = $this->buckets[$mergePosition][2];

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
            if ($currentCount < $minCount
                || ($currentCount === $minCount
                    && $currentMergeCount < $mergeCount)) {
                $minPosition = $position;
                $minCount = $currentCount;
                $mergePosition = $currentMergePosition;
                $mergeCount = $currentMergeCount;
            }
        }

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

    public function getTopValues() {
        return array_values($this->topValues);
    }

    public function getTopMetas() {
        $result = array();
        foreach ($this->topValues as $sampleId => $tmp) {
            $result []= $this->topMetas[$sampleId];
        }
        return $result;
    }

    public function getBottomValues() {
        return array_values($this->bottomValues);
    }

    public function getBottomMetas() {
        $result = array();
        foreach ($this->bottomValues as $sampleId => $tmp) {
            $result []= $this->bottomMetas[$sampleId];
        }
        return $result;
    }

}