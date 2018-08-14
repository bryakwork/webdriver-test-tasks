<?php
/**
 * Created by PhpStorm.
 * User: USER_T
 * Date: 14.08.2018
 * Time: 11:03
 */

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

require('vendor/autoload.php');

$host = 'http://localhost:4444/wd/hub';
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
$document = $driver->get('https://webpack.js.org/');
$navLinks = $driver->findElements(WebDriverBy::cssSelector('.navigation__link'));
$navLinks[0]->click();
$modulesLink = $driver->findElement(WebDriverBy::cssSelector('.sidebar__inner'))->findElement(WebDriverBy::linkText('Modules'));
$modulesLink->click();
$driver->findElement(WebDriverBy::cssSelector('.page__content'));
sleep(5);
$driver->quit();