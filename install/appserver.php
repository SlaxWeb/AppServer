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
$configuration["appserver.webserver"] = [
    "host"      =>  "127.0.0.1",
    "port"      =>  9051,

    // Changes bellow this commend are not recommended!
    // document root (public directory)
    "rootDir"   =>  "%{pubDir}%",
    // pidfile path
    "pidFile"   =>  "%{appDir}%Cache/appserver.pid",
    // web app bootstrap file
    "bootstrap" =>  "%{appDir}%../bootstrap/web.php"
];

/*
 * Commands to register to Slaxer
 */
$configuration["component.commands"] = [
    \SlaxWeb\AppServer\ServerCommand::class
];
