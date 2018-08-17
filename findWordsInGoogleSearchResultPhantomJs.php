<?php
/**
 * Created by PhpStorm.
 * User: USER_T
 * Date: 14.08.2018
 * Time: 12:10
 */

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

require('vendor/autoload.php');

$searchTerm = 'news cars 2016-2018';
$searchWords = ['Honda', 'Toyota'];
$numberOfPagesToSearch = 15;

function findWords(array $searchWords, RemoteWebDriver $driver)
{
    $driver->wait(5)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("//div[@class='g']")));
    $searchSelector = buildXpathSelectorfromSearchWords($searchWords);
    $results = $driver->findElements(WebDriverBy::xpath("//h3[@class='r']/a[$searchSelector]/../.."));
    foreach ($results as $result) {
        echo $result->getText();
        echo " : ";
        echo $result->getAttribute('href');
        echo "\n ----------------------------- \n";
    }
}

function buildXpathSelectorfromSearchWords(array $searchWords){
    $result = "";
    foreach ($searchWords as $word) {
        $result .= "contains(text(), '$word') or ";
    }
    $result = rtrim($result, ' or ');
    return $result;
}

function goToPage(int $pageNumber, RemoteWebDriver $driver)
{
    $driver->findElement(WebDriverBy::xpath("//div[@id='navcnt']//a[@aria-label='Page $pageNumber']"))->click();
}

$startTime = microtime(true);
$host = '127.0.0.1:8910';
try {
    $phantomJsCapabilities = [
        'browserName' => 'phantomjs',
        'phantomjs.page.settings.userAgent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:25.0) Gecko/20100101 Firefox/25.0',
    ];
    $driver = RemoteWebDriver::create($host, new DesiredCapabilities($phantomJsCapabilities));
    $document = $driver->get('https://www.google.com');
    $searchForm = $driver->findElement(WebDriverBy::xpath("//input[@id='lst-ib']"))
        ->sendKeys($searchTerm)
        ->submit();
    for ($pageNumber = 1; $pageNumber <= $numberOfPagesToSearch; $pageNumber++) {
        findWords($searchWords, $driver);
        if ($pageNumber < $numberOfPagesToSearch) { //if not the last page that is needed
            goToPage($pageNumber + 1, $driver);
        }
    }
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