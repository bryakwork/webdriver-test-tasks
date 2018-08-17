<?php
/**
 * Created by PhpStorm.
 * User: USER_T
 * Date: 14.08.2018
 * Time: 15:43
 */

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

require 'vendor/autoload.php';

function getOrderDetails(RemoteWebDriver $driver)
{
    $orderDetails = [];
    $orderInfo = $driver->findElements(WebDriverBy::xpath("//div[@class='col-6-12 orderInfo']/span"));
    foreach ($orderInfo as $orderDetailElement) {
        $orderText = $orderDetailElement->getText();
        $orderDetailParts = explode(': ', $orderText);
        $orderDetails[$orderDetailParts[0]] = $orderDetailParts[1];
    }
    return $orderDetails;
}

function getShipTo(RemoteWebDriver $driver)
{
    $result = [];
    $shipToNode = $driver->findElements(WebDriverBy::xpath("//div[@class='billingInfo grid']/div[@class='wrapperGrid']/div[@class='col-3-12 addrWrapper']/span"))[0];
    $shippingInfoNodes = $shipToNode->findElements(WebDriverBy::xpath("span"));
    $result['addresantName'] = $shippingInfoNodes[0]->getText();
    array_shift($shippingInfoNodes);
    $address = '';
    foreach ($shippingInfoNodes as $shipToElement) {
        $address .= $shipToElement->getText() . ' ';
    }
    $address = rtrim($address);
    $result['address'] = $address;
    return $result;

}

function getBillTo(RemoteWebDriver $driver)
{
    $result = [];
    $shipToContainerNode = $driver->findElements(WebDriverBy::xpath("//div[@class='billingInfo grid']/div[@class='wrapperGrid']/div[@class='col-3-12 addrWrapper']/span"))[1];
    $shipToDataNodes = $shipToContainerNode->findElements(WebDriverBy::xpath("span"));
    $result['shopName'] = $shipToDataNodes[0]->getText();
    $result['attnName'] = $shipToDataNodes[1]->getText();
    array_shift($shipToDataNodes);
    array_shift($shipToDataNodes);
    $addressString = '';
    foreach ($shipToDataNodes as $shipToElement) {
        $addressString .=  $shipToElement->getText() . ' ' ;
    }
    $addressString = rtrim($addressString);
    $result['address'] = $addressString;
    return $result;

}

function getPaymentMethod(RemoteWebDriver $driver)
{
    $result = [];
    $paymentMethod = $driver->findElements(WebDriverBy::xpath("//div[@class='billingInfo grid']/div[@class='wrapperGrid']/div[@class='col-6-12 orderPayments']//tr[@class='payMethodRp']/td/span"));
    $result['method'] = $paymentMethod[0]->getText();
    $result['amount'] = ltrim($paymentMethod[1]->getText(), '$');
    return $result;
}

function getProductsToShip(RemoteWebDriver $driver)
{
    $result = [];
    $productsToShipNode = $driver->findElement(WebDriverBy::xpath("//div[@class='grid orderItems']"));
    $result['keywords'] = getKeywords($productsToShipNode);
    $result['products'] = getProductsInfo($productsToShipNode);
return $result;
}

function getKeywords(RemoteWebElement $productsToShipNode) {
    $keywords = [];
    $keysElements = $productsToShipNode->findElements(WebDriverBy::xpath("h2/ul[@class='shippingStatusKey']/li[not(text() = 'Key:')]"));
    foreach ($keysElements as $keyElement) {
        $keywords[] = $keyElement->findElement(WebDriverBy::xpath("span"))->getText();
    }
    return $keywords;
}

function getProductsInfo(RemoteWebElement $productsToShipNode) {
    $allProductsInfo = [];
    $productsRows = $productsToShipNode->findElements(WebDriverBy::xpath("table/tbody/tr[not(@class='itemsHeader')]"));
    foreach ($productsRows as $productRow) {
        $productData = [];
        $productFields = $productRow->findElements(WebDriverBy::xpath("td"));
        foreach ($productFields as $field) {
            $fieldName = $field->getAttribute('data-label');
            $fieldValue = $field->getText();
            $productData[$fieldName] = ltrim($fieldValue, '$');
        }
        $allProductsInfo[] = $productData;
    }
    return $allProductsInfo;
}

function getOrderTotalInfo(RemoteWebDriver $driver)
{
    $result = [];
    $orderTotalInfoNode = $driver->findElement(WebDriverBy::xpath("//div[@class='totalsGrid grid']/div[@class='col-5-12']/div[@class='orderTotalinfo']"));
    $result['subtotal'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("h3/span"))->getText(), '$');
    $result['shipping'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("p[@class='shippingPrice']/span"))->getText(), '$');
    $result['tax'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("p[@class='tax']/span"))->getText(), '$');
    $result['saving'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("p[text()='You save: ']/span"))->getText(), '$');

    return $result;
}

$startTime = microtime(true);

$host = 'http://localhost:4444/wd/hub';
try {
    $result = [];
    $options = new ChromeOptions();
    $options->addArguments([
        '--headless',
    ]);
    $capabilities = DesiredCapabilities::chrome();
    $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
    $driver = RemoteWebDriver::create($host, $capabilities);
    $driver->get('http://localhost:8000/data/google.html');
    $result['orderDetails'] = getOrderDetails($driver);
    $result['shipTo'] = getShipTo($driver);
    $result['billTo'] = getBillTo($driver);
    $result['paymentMethod'] = getPaymentMethod($driver);
    $result['productsToShip'] = getProductsToShip($driver);
    $result['orderTotalInfo'] = getOrderTotalInfo($driver);

    print_r($result);
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