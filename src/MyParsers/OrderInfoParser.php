<?php
/**
 * Created by PhpStorm.
 * User: USER_T
 * Date: 15.08.2018
 * Time: 16:54
 */

namespace myParsers;


use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

/**Parser for RockyMountain post-checkout order info page*/
class OrderInfoParser
{
    /**@var RemoteWebDriver */
    protected $driver;

    /**
     * OrderInfoParser constructor.
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
    public function parse(string $pageUrl){
        try {
            $this->driver->get($pageUrl);
            $result = $this->performParsing();
            return $result;
        } catch (\Throwable $exception) {
            $errorMessage = $exception->getMessage();
            $traceString = print_r($exception->getTrace(), true);
            echo "Error occured: $errorMessage \n";
            echo "Trace: \n $traceString \n";
        } finally {
            $this->driver->quit();
        }
    }

    /**
     * @return array
     */
    protected function performParsing() {
        $result = [];
        $result['orderDetails'] = $this->getOrderDetails();
        $result['shipTo'] = $this->getShipTo();
        $result['billTo'] = $this->getBillTo();
        $result['paymentMethod'] = $this->getPaymentMethod();
        $result['productsToShip'] = $this->getProductsToShip();
        $result['orderTotalInfo'] = $this->getOrderTotalInfo();
        return $result;
    }

    /**
     * @return array
     */
    protected function getOrderDetails()
    {
        $orderDetails = [];
        $orderInfo = $this->driver->findElements(WebDriverBy::xpath("//div[@class='col-6-12 orderInfo']/span"));
        foreach ($orderInfo as $orderDetailElement) {
            $orderText = $orderDetailElement->getText();
            $orderDetailParts = explode(': ', $orderText);
            $orderDetails[$orderDetailParts[0]] = $orderDetailParts[1];
        }
        return $orderDetails;
    }

    /**
     * @return array
     */
    protected function getShipTo()
    {
        $result = [];
        $shipToNode = $this->driver->findElements(WebDriverBy::xpath("//div[@class='billingInfo grid']/div[@class='wrapperGrid']/div[@class='col-3-12 addrWrapper']/span"))[0];
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

    /**
     * @return array
     */
    protected function getBillTo()
    {
        $result = [];
        $shipToContainerNode = $this->driver->findElements(WebDriverBy::xpath("//div[@class='billingInfo grid']/div[@class='wrapperGrid']/div[@class='col-3-12 addrWrapper']/span"))[1];
        $shipToDataNodes = $shipToContainerNode->findElements(WebDriverBy::xpath("span"));
        $result['shopName'] = $shipToDataNodes[0]->getText();
        $result['attnName'] = $shipToDataNodes[1]->getText();
        array_shift($shipToDataNodes);
        array_shift($shipToDataNodes);
        $addressString = '';
        foreach ($shipToDataNodes as $shipToElement) {
            $addressString .=  $shipToElement->getText() . " ";
        }
        $addressString = rtrim($addressString);
        $result['address'] = $addressString;
        return $result;
    }

    /**
     * @return array
     */
    protected function getPaymentMethod()
    {
        $result = [];
        $paymentMethod = $this->driver->findElements(WebDriverBy::xpath("//div[@class='billingInfo grid']/div[@class='wrapperGrid']/div[@class='col-6-12 orderPayments']//tr[@class='payMethodRp']/td/span"));
        $result['method'] = $paymentMethod[0]->getText();
        $result['amount'] = ltrim($paymentMethod[1]->getText(), '$');
        return $result;
    }

    /**
     * @return array
     */
    protected function getProductsToShip()
    {
        $result = [];
        $productsToShipNode = $this->driver->findElement(WebDriverBy::xpath("//div[@class='grid orderItems']"));
        $result['keywords'] = $this->getKeywords($productsToShipNode);
        $result['products'] = $this->getProductsInfo($productsToShipNode);
        return $result;
    }

    /**
     * @param RemoteWebElement $productsToShipNode
     * @return array
     */
    protected function getKeywords(RemoteWebElement $productsToShipNode) {
        $keywords = [];
        $keysElements = $productsToShipNode->findElements(WebDriverBy::xpath("h2/ul[@class='shippingStatusKey']/li[not(text() = 'Key:')]"));
        foreach ($keysElements as $keyElement) {
            $keywords[] = $keyElement->findElement(WebDriverBy::xpath("span"))->getText();
        }
        return $keywords;
    }

    /**
     * @param RemoteWebElement $productsToShipNode
     * @return array
     */
    protected function getProductsInfo(RemoteWebElement $productsToShipNode) {
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

    /**
     * @return array
     */
    protected function getOrderTotalInfo()
    {
        $result = [];
        $orderTotalInfoNode = $this->driver->findElement(WebDriverBy::xpath("//div[@class='totalsGrid grid']/div[@class='col-5-12']/div[@class='orderTotalinfo']"));
        $result['subtotal'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("h3/span"))->getText(), '$');
        $result['shipping'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("p[@class='shippingPrice']/span"))->getText(), '$');
        $result['tax'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("p[@class='tax']/span"))->getText(), '$');
        $result['saving'] = ltrim($orderTotalInfoNode->findElement(WebDriverBy::xpath("p[text()='You save: ']/span"))->getText(), '$');

        return $result;
    }
}