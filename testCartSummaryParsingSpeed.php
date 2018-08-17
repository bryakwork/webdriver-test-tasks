<?php
/**
 * Created by PhpStorm.
 * User: USER_T
 * Date: 16.08.2018
 * Time: 13:54
 */

use myParsers\CartSummaryParser;
use myParsers\CartSummaryParserHeadless;
use myParsers\CartSummaryParserPhantom;

require 'vendor/autoload.php';

$numberOfTests = 100;

function calculateAverage($arr) {
    $total = 0;
    $count = count($arr);
    foreach ($arr as $value) {
        $total += + $value;
    }
    return ($total/$count);
}

$commonParser = new CartSummaryParser('http://localhost:4444/wd/hub');
$headlessParser = new CartSummaryParserHeadless('http://localhost:4444/wd/hub');
$phantomParser = new CartSummaryParserPhantom('http://localhost:8910');

$headlessResultsTime = [];
$commonResultsTime = [];
$phantomResultsTime = [];

for ($i = 0; $i < $numberOfTests ; $i++) {
    $startTime = microtime(true);
    $headlessParser->parse('http://localhost:8000/data/order_place.html');
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    $headlessResultsTime[] = $executionTime;
}
$headlessParser->quit();
echo "headless - done \n";

for ($i = 0; $i < $numberOfTests ; $i++) {
    $startTime = microtime(true);
    $commonParser->parse('http://localhost:8000/data/order_place.html');
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    $commonResultsTime[] = $executionTime;
}
$commonParser->quit();
echo "common - done \n";

for ($i = 0; $i < $numberOfTests ; $i++) {
    $startTime = microtime(true);
    $phantomParser->parse('http://localhost:8000/data/order_place.html');
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    $phantomResultsTime[] = $executionTime;
}
$phantomParser->quit();
echo "phantom - done \n";

$headlessAverageTime = calculateAverage($headlessResultsTime);
$commonAverageTime = calculateAverage($commonResultsTime);
$phantomAverageTime = calculateAverage($phantomResultsTime);

echo "headless parsing average time: $headlessAverageTime; \n common parsing average time $commonAverageTime; \n phantom parsing average time $phantomAverageTime";
