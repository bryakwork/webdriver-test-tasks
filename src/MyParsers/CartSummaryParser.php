<?php

namespace myParsers;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

/** Parser for RockyMountain Cart Summary page */
class CartSummaryParser
{
    /**@var RemoteWebDriver */
    protected $driver;

    /**
     * CartSummaryParser constructor.
     * @param string $host
     */
    public function __construct(string $host)
    {
        $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
    }

    /**
     * @param string $pageUrl
     * @return array
     */
    public function parse(string $pageUrl)
    {
        try {
            $this->driver->get($pageUrl);
            $result = $this->performParsing();
            return $result;
        } catch (\Throwable $exception) {
            $errorMessage = $exception->getMessage();
            $traceString = print_r($exception->getTrace(), true);
            echo "Error occured: $errorMessage \n";
            echo "Trace: \n $traceString \n";
        }
    }

    /**
     * @return void
     */
    public function quit() {
        $this->driver->quit();
    }
    /**
     * @return array
     */
    protected function performParsing(){
        $result = [];
        $result['itemsList'] = $this->getItemsList();
        $result['orderTotalInfo'] = $this->getOrderTotalInfo();
        $result['shippingAddressInfo'] = $this->getShippingAddressInfo();
        $result['shippingMethod'] = $this->getShippingMethod();
        $result['paymentDetails'] = $this->getPaymentDetails();
        return $result;
    }

    /**
     * @return array
     */
    protected function getItemsList()
    {
        $itemsTableRows = $this->driver->findElements(WebDriverBy::xpath("//div[@class='itemsList']//tbody/tr"));
        $result = [];
        foreach ($itemsTableRows as $row) {
            $result[] = $this->getItemDataFromRow($row);
        }
        return $result;
    }

    /**
     * @param RemoteWebElement $row
     * @return array
     */
    protected function getItemDataFromRow(RemoteWebElement $row)
    {
        $itemData = [];
        $itemData['name'] = $row->findElements(WebDriverBy::xpath("td[@class='itemCell']/span"))[0]->getText();
        $itemData['notes'] = $row->findElements(WebDriverBy::xpath("td[@class='itemCell']/span"))[1]->getText();
        $itemData['stockStatus'] = $row->findElement(WebDriverBy::xpath("td[@class='stockStatus statusCell']/div/span"))->getAttribute('class');
        $itemData['quantity'] = $row->findElement(WebDriverBy::xpath("td[@class='itemQtyCell']"))->getText();
        $itemData['price'] = ltrim($row->findElement(WebDriverBy::xpath("td[@class='priceCell']"))->getText(), '$');
        return $itemData;
    }

    /**
     * @return array
     */
    protected function getOrderTotalInfo()
    {
        $result = [];
        $orderTotalInfoNode = $this->driver->findElement(WebDriverBy::xpath("//div[@class='orderTotalinfo']"));
        $result['discounts'] = $orderTotalInfoNode->findElement(WebDriverBy::xpath("div[@class='discounts']"))->getText();
        $result['subtotal'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("h3[@class='totalHeading rndCnr4 subTotal']/span"))->getText(), '$');
        $result['shippingPrice'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("p[@class='shippingPrice']/span"))->getText(), '$');
        $result['tax'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("p[@class='tax']/span"))->getText(), '$');
        $result['orderTotal'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("h3[@class='totalHeading rndCnr4 orderTotal']/span"))->getText(), '$');
        $result['totalDue'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("h3[@class='totalHeading rndCnr4']/span"))->getText(), '$');
        return $result;
    }

    /**
     * @return array
     */
    protected function getShippingAddressInfo()
    {
        $result = [];
        $shippingAddressInfoNode = $this->driver->findElement(WebDriverBy::xpath("//div[@class='col-7-12 shippingAddr']//div[@class='shipAddrReview']"));
        $result['addrName'] = $shippingAddressInfoNode->findElement(WebDriverBy::xpath("span[@class='addrName']"))->getText();
        $addressNodes = $shippingAddressInfoNode->findElements(WebDriverBy::xpath("span[not(@class='addrName')]"));
        $addressString = '';
        foreach ($addressNodes as $node) {
            $addressString .= $node->getText() . ' ';
        }
        $result['address'] = rtrim($addressString);
        return $result;
    }

    /**
     * @return string
     */
    protected function getShippingMethod()
    {
        return strtolower($this->driver->findElement(WebDriverBy::xpath("//div[@class='shipMethodReview']"))->getText());
    }

    /**
     * @return array
     */
    protected function getPaymentDetails()
    {
        $result = [];
        $paymentDetailsNode = $this->driver->findElement(WebDriverBy::xpath("//div[@class='paymentReview']/div[@class='grid']"));
        $billToInfoNode = $paymentDetailsNode->findElement(WebDriverBy::xpath("//div[@class='billTo']"));
        $addrNameNodes = $billToInfoNode->findElements(WebDriverBy::xpath("span[@class='addrName']"));
        $result['addrName'] = $addrNameNodes[0]->getText() . ' ' . $addrNameNodes[1]->getText();
        $addressNodes = $billToInfoNode->findElements(WebDriverBy::xpath("span[not(@class='addrName') and not(@class='emailAddr')]"));
        $addressString = '';
        foreach ($addressNodes as $node) {
            $addressString .= $node->getText() . ' ';
        }
        $result['billingAddress'] = rtrim($addressString);
        $result['paymentMethod'] = $paymentDetailsNode->findElement(WebDriverBy::xpath("//div[@class='paymentMethodCell']"))->getText();
        $result['paymentAmount'] = ltrim($paymentDetailsNode->findElement(WebDriverBy::xpath("//div[@class='paymentAmountCell']"))->getText(), '$');
        return $result;
    }

}