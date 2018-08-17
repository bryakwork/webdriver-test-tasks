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

$searchValue = 'westworld watch online';

$host = '127.0.0.1:8910';
$startTime = microtime(true);
try {
    $phantomJsCapabilities = array(
        'browserName' => 'phantomjs',
        'phantomjs.page.settings.userAgent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:25.0) Gecko/20100101 Firefox/25.0',
    );
    $driver = RemoteWebDriver::create($host, new DesiredCapabilities($phantomJsCapabilities));
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

