# Percentiller

[![Build Status](https://travis-ci.org/phperf/percentiller.svg?branch=master)](https://travis-ci.org/phperf/percentiller) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phperf/percentiller/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phperf/percentiller/?branch=master)

Percentiller is a PHP class for capturing and analyzing data distribution. 
Lightweight on CPU and memory consumption.
Approximates percentiles bucketing the data.

## Installation

    composer install phperf/percentiller
    
## Usage

```php
        $stats = new \Phperf\Percentiller();
        
        // approximation level, more buckets - higher deistribution definition for more CPU and memory
        $stats->maxBuckets = 10;
        
        // capture values and meta for 5 items with lowest values
        $stats->captureBottomItems = 5;
        
        // capture values and meta for 3 items with highest values
        $stats->captureTopItems = 3;
        
        .......
        
        // somewhere in data streamer/iterator, replace $event with your data element 
        $stats->add($event->getResponseTime(), $event->getQueryInfo());
        
        .......
        
        // statistics retrieval
        
        // 95% events had response time not greater than:
        $stats->bottomPercentile(0.95);
        
        // 10% events took longer or equal to:
        $stats->topPercentile(0.1);

        // get information on slowest events
        $stats->getTopMetas();

        // get information on slowest events
        $stats->getBottomMetas();
        
        // iterate through distribution
        foreach ($stats->buckets as $bucket) {
            print_r($bucket);
            /*
            Array
            (
                [0] => 41.558                       // minival value
                [1] => 50.95                        // maximal value
                [2] => 137                          // count
                [3] => Array                        // sample meta 
                    (
                        [somedata] => somevalue
                    )
            
            )
            */
        }
        

```
