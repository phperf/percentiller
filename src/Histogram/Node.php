<?php
/**
 * Created by PhpStorm.
 * User: vearutop
 * Date: 02.09.2015
 * Time: 21:29
 */

namespace Phperf\Histogram;


class Node
{
    public $min;
    public $max;
    public $count = 1;

    /** @var  Node */
    public $less;
    /** @var  Node */
    public $greater;

    public function __construct($min, $max = null) {
        if (null === $max) {
            $max = $min;
        }
        $this->min = $min;
        $this->max = $max;
    }

    public function push($value) {
        if (($value >= $this->min) && ($value <= $this->max)) {
            $this->count++;
        }
        elseif ($value < $this->min) {
            if (null === $this->less) {
                $this->less = new Node($value);
            }
            else {
                if ($this->less->max >= $value) {
                    $this->less->push($value);
                }
                else {
                    $less = new Node($value);
                    $less->less = $this->less;
                    $this->less = $less;
                }
            }
        }
        elseif ($value > $this->max) {
            if (null === $this->greater) {
                $this->greater = new Node($value);
            }
            else {
                if ($this->greater->min <= $value) {
                    $this->greater->push($value);
                }
                else {
                    $greater = new Node($value);
                    $greater->greater = $this->greater;
                    $this->greater = $greater;
                }
            }
        }
    }
}