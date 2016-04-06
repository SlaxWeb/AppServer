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
 */
$configuration["appserver.webserver"] = [
    "host"  =>  "127.0.0.1",
    "port"  =>  9051
];

/*
 * Commands to register to Slaxer
 */
$configuration["component.commands"] = [
    \SlaxWeb\AppServer\ServerCommand::class
];
