<?php
/**
 * Created by PhpStorm.
 * User: USER_T
 * Date: 13.08.2018
 * Time: 17:58
 */


use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

require 'vendor/autoload.php';

$searchValue = 'amazon';

//    $noImageCapabilities = [
//        'browserName' => 'chrome',
//        'platform' => 'ANY',
//        'chromeOptions' => [
//            'prefs' => [
//                'profile' => [
//                    'default_content_setting_values' => ['images' => 2]
//                ]
//            ]
//        ]
//    ];
//    $driver = RemoteWebDriver::create($host, new DesiredCapabilities($noImageCapabilities));

$startTime = microtime(true);
$host = 'http://localhost:4444/wd/hub';
try {
    $driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
    $driver->get('https://www.google.com');
    $searchForm = $driver->findElement(WebDriverBy::xpath("//input[@id='lst-ib']"))
        ->sendKeys($searchValue)
        ->submit();
    $driver->wait(5)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("//div[@class='g']")));
    $firstLink = $driver->findElement(WebDriverBy::xpath("//cite[@class='iUh30']"))->getText();
    echo $firstLink;
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    echo "\n Execution Time : $executionTime s";
} catch (Throwable $exception) {
    $errorMessage = $exception->getMessage();
    $traceString = print_r($exception->getTrace(), true);
    echo "Error occured: $errorMessage \n";
    echo "Trace: \n $traceString \n";
} finally {
    $driver->quit();
}

