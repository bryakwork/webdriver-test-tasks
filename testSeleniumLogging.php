<?php
/**
 * Created by PhpStorm.
 * User: USER_T
 * Date: 06.09.2018
 * Time: 13:24
 */

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

require('vendor/autoload.php');
$host = 'http://localhost:4444/wd/hub';
$options = new ChromeOptions();
$options->addArguments([
   '--verbose',
]);
$capabilities = DesiredCapabilities::chrome();
$capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
$driver = RemoteWebDriver::create($host, $capabilities);
// do something
$document = $driver->get('https://webpack.js.org/');
$navLinks = $driver->findElements(WebDriverBy::cssSelector('.navigation__link'));
$navLinks[0]->click();
$modulesLink = $driver->findElement(WebDriverBy::cssSelector('.sidebar__inner'))->findElement(WebDriverBy::linkText('Modules'));
$modulesLink->click();
//that`s how to get logs
//WARNING: logs are erased after request
print_r($driver->manage()->getLog('browser'));// browser console
print_r($driver->manage()->getLog('server'));// webDriver actions
$driver->quit();