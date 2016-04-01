<?php
/**
 * Service Provider Class
 *
 * Expose the Application Server Objects as Services to the Dependency Injection
 * Container.
 *
 * This is highly experimental and might brake and/or change without notice
 * throughout the development.
 *
 * @package   SlaxWeb\AppServer
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.1
 */
namespace SlaxWeb\AppServer\Service;

use swoole_http_server;

class Provider implements \Pimple\ServiceProviderInterface
{
    /**
     * Register services
     *
     * Method called by the Pimple\Container when this Service Provider is
     * registered.
     *
     * @param \Pimple\Container $container Dependency Injection Container
     * @return void
     */
    public function register(\Pimple\Container $container)
    {
        $container["webserver.service"] = function (\Pimple\Container $cont) {
            return new \SlaxWeb\AppServer\WebServer(
                new swoole_http_server(
                    $cont["webserver.address"],
                    $cont["webserver.port"]
                ),
                $cont["webserver.bootstrapPath"]
            );
        };
    }
}
