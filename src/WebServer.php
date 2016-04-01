<?php
/**
 * WebServer Class
 *
 * WebServer class provides a WebServer with the help of the Swoole extension.
 * As it handles all requests that are sent towards it, it also requires the
 * Application Instance as it will be routing requests received forward to the
 * Application as normally the index.php file would.
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
namespace SlaxWeb\AppServer;

use swoole_http_server;
use SlaxWeb\Bootstrap\Application;

class WebServer
{
    /**
     * Http Server
     *
     * @var swoole_http_server
     */
    protected $_http = null;

    /**
     * Application Object
     *
     * @var \SlaxWeb\Bootstrap\Application
     */
    protected $_app = null;

    /**
     * Class Constructor
     *
     * Add the reference to the 'swoole_http_server' and the Framework
     * Application Object to the protected class properties.
     *
     * @param swoole_http_server $http Swoole Http Server
     */
    public function __construct(swoole_http_server $http, Application $app)
    {
        $this->_http = $http;
        $this->_app = $app;

        $this->_http->on("request", [$this, "_onRequest"]);
    }

    /**
     * Start the Web Server
     *
     * @return void
     */
    public function start()
    {
        $this->_http->start();
    }

    /**
     * Handle incoming request
     *
     * Forward the request to the application for processing, and set the proper
     * response at the end.
     *
     * @param request $request Incoming Request Object
     * @param response $response Response Object that Swoole will print back
     * @return void
     */
    public function _onRequest($request, $response)
    {
        $this->_app->run(
            $this->_app["request.service"],
            $this->_app["response.service"]
        );
        $response->end($this->_app["response.service"]->send);
    }
}
