<?php
/**
 * Created by PhpStorm.
 * User: USER_T
 * Date: 15.08.2018
 * Time: 11:27
 */

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

require('vendor/autoload.php');

function getItemsList(RemoteWebDriver $driver)
{
    $itemsTableRows = $driver->findElements(WebDriverBy::xpath("//div[@class='itemsList']//tbody/tr"));
    $result = [];
    foreach ($itemsTableRows as $row) {
        $result[] = getItemDataFromRow($row);
    }
    return $result;
}

function getItemDataFromRow(RemoteWebElement $row)
{
    $itemData = [];
    $itemData['name'] = $row->findElements(WebDriverBy::xpath("td[@class='itemCell']/span"))[0]->getText();
    $itemData['notes'] = $row->findElements(WebDriverBy::xpath("td[@class='itemCell']/span"))[1]->getText();
    $itemData['stockStatus'] = $row->findElement(WebDriverBy::xpath("td[@class='stockStatus statusCell']/div/span"))->getAttribute('class');
    $itemData['quantity'] = $row->findElement(WebDriverBy::xpath("td[@class='itemQtyCell']"))->getText();
    $itemData['price'] = ltrim($row->findElement(WebDriverBy::xpath("td[@class='priceCell']"))->getText(), '$');
    return $itemData;
}

function getOrderTotalInfo(RemoteWebDriver $driver)
{
    $result = [];
    $orderTotalInfoNode = $driver->findElement(WebDriverBy::xpath("//div[@class='orderTotalinfo']"));
    $result['discounts'] = $orderTotalInfoNode->findElement(WebDriverBy::xpath("div[@class='discounts']"))->getText();
    $result['subtotal'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("h3[@class='totalHeading rndCnr4 subTotal']/span"))->getText(), '$');
    $result['shippingPrice'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("p[@class='shippingPrice']/span"))->getText(), '$');
    $result['tax'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("p[@class='tax']/span"))->getText(), '$');
    $result['orderTotal'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("h3[@class='totalHeading rndCnr4 orderTotal']/span"))->getText(), '$');
    $result['totalDue'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("h3[@class='totalHeading rndCnr4']/span"))->getText(), '$');
    return $result;
}

function getShippingAddressInfo(RemoteWebDriver $driver)
{
    $result = [];
    $shippingAddressInfoNode = $driver->findElement(WebDriverBy::xpath("//div[@class='col-7-12 shippingAddr']//div[@class='shipAddrReview']"));
    $result['addrName'] = $shippingAddressInfoNode->findElement(WebDriverBy::xpath("span[@class='addrName']"))->getText();
    $addressNodes = $shippingAddressInfoNode->findElements(WebDriverBy::xpath("span[not(@class='addrName')]"));
    $addressString = '';
    foreach ($addressNodes as $node) {
        $addressString .= $node->getText() . ' ';
    }
    $result['address'] = rtrim($addressString);
    return $result;
}

function getShippingMethod(RemoteWebDriver $driver)
{
    return strtolower($driver->findElement(WebDriverBy::xpath("//div[@class='shipMethodReview']"))->getText());
}

function getPaymentDetails(RemoteWebDriver $driver)
{
    $result = [];
    $paymentDetailsNode = $driver->findElement(WebDriverBy::xpath("//div[@class='paymentReview']/div[@class='grid']"));
    $billToInfoNode = $paymentDetailsNode->findElement(WebDriverBy::xpath("//div[@class='billTo']"));
    $addrNameNodes = $billToInfoNode->findElements(WebDriverBy::xpath("span[@class='addrName']"));
    $result['addrName'] = $addrNameNodes[0]->getText() . ' ' . $addrNameNodes[1]->getText();
    $addressNodes = $billToInfoNode->findElements(WebDriverBy::xpath("span[not(@class='addrName') and not(@class='emailAddr')]"));
    $addressString = '';
    foreach  ($addressNodes as $node) {
        $addressString .= $node->getText() .' ';
    }
    $result['billingAddress'] = rtrim($addressString);
    $result['paymentMethod'] = $paymentDetailsNode->findElement(WebDriverBy::xpath("//div[@class='paymentMethodCell']"))->getText();
    $result['paymentAmount'] = ltrim($paymentDetailsNode->findElement(WebDriverBy::xpath("//div[@class='paymentAmountCell']"))->getText(), '$');
    return $result;
}

$host = 'http://localhost:4444/wd/hub';
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
try {
    $document = $driver->get('http://localhost:8000/data/order_place.html');
    $result = [];
    $result['itemsList'] = getItemsList($driver);
    $result['orderTotalInfo'] = getOrderTotalInfo($driver);
    $result['shippingAddressInfo'] = getShippingAddressInfo($driver);
    $result['shippingMethod'] = getShippingMethod($driver);
    $result['paymentDetails'] = getPaymentDetails($driver);

print_r($result);
} catch (Throwable $exception) {
    $errorMessage = $exception->getMessage();
    $traceString = print_r($exception->getTrace(), true);
    echo "Error occured: $errorMessage \n";
    echo "Trace: \n $traceString \n";
} finally {
    $driver->quit();
}