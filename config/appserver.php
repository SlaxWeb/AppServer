<?php
/**
 * AppServer Component Config
 *
 * Application Server Configuration
 *
 * @package   SlaxWeb\AppServer
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.1
 */
/*
 * WebServer Config
 *
 * WebServer initialization replaces %{param}% occurances in all configuration
 * items with the value of application parameter "param".
 */
$configuration["webserver"] = [
    "host"              =>  "0.0.0.0",
    "port"              =>  9051,

    // Changes bellow this commend are not recommended!
    // document root (public directory)
    "rootDir"           =>  "%{pubDir}%",
    // pidfile path
    "pidFile"           =>  "%{appDir}%Cache/appserver.pid",
    // web app bootstrap file
    "bootstrap"         =>  "%{appDir}%../bootstrap/web.php",
    // log file
    "logFile"           =>  "%{appDir}%Logs/WebServer-" . date("Ymd") . ".log",
    // front controller name
    "frontController"   =>  "index.php"
];
