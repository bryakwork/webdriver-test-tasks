<?php
/**
 * Created by PhpStorm.
 * User: USER_T
 * Date: 17.08.2018
 * Time: 17:26
 */

namespace myParsers\Pages;


use Facebook\WebDriver\Remote\RemoteWebDriver;

class BasicPage
{
    /**@var RemoteWebDriver */
    protected $driver;
    /**@var string */
    protected $url;

    /**
     * BasicPage constructor.
     * @param $url
     * @param $driver
     */
    public function __construct(string $url, RemoteWebDriver $driver)
    {
        $this->driver = $driver;
        $this->url = $url;
    }

    public function load()
    {
        $this->driver->get($this->url);
    }

    public function close()
    {
        $this->driver->close();
    }

    public function findElement($xpath)
    {

    }

    public function findElements($xpath)
    {

    }

    public function getTitle()
    {

    }

}