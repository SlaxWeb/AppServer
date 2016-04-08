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
use swoole_http_request;
use swoole_http_response;

class WebServer
{
    /**
     * Http Server
     *
     * @var swoole_http_server
     */
    protected $_http = null;

    /**
     * Application Bootstrap Location
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Class Constructor
     *
     * Add the reference to the 'swoole_http_server' and the Web Server
     * configuration array to the protected class properties.
     *
     * @param swoole_http_server $http Swoole Http Server
     * @param array $config WebServer config
     */
    public function __construct(swoole_http_server $http, array $config)
    {
        $this->_http = $http;
        $this->_config = $config;

        $this->_http->on("request", [$this, "_onRequest"]);
        $this->_http->on("start", [$this, "_onServerStart"]);
        $this->_http->on("shutdown", [$this, "_onServerShutdown"]);

        $this->_http->set($this->_prepSwooleConfig($config));
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
     * Server Start Handler
     *
     * Write server PID to pid file.
     *
     * @param \swoole_http_server $server Server Object
     * @return void
     */
    public function _onServerStart($server)
    {
        file_put_contents($this->_config["pidFile"], $server->master_pid);
    }

    /**
     * Handle server shutdown
     *
     * Remove the PID file when the server is shutdown.
     *
     * @return void
     */
    public function _onServerShutdown()
    {
        if (file_exists($this->_config["pidFile"])) {
            unlink($this->_config["pidFile"]);
        }
    }

    /**
     * Handle incoming request
     *
     * Forward the request to the application for processing, and set the proper
     * response at the end.
     *
     * @param swoole_http_request $request Incoming Request Object
     * @param swoole_http_response $response Response Object that Swoole will print to requestor
     * @return void
     */
    public function _onRequest(
        swoole_http_request $request,
        swoole_http_response $response
    ) {
        $requestFile = $this->_config["rootDir"]
            . $request->server["request_uri"];

        if (file_exists($requestFile)) {
            // serve static file
            $this->_serveStaticFile($requestFile, $response);
            return;
        }

        // prepare the app
        $app = require $this->_config["bootstrap"];
        $app["requestParams"] = [
            "uri"       =>  $request->server["request_uri"],
            "method"    =>  $request->server["request_method"]
        ];

        // run app code
        $app->run(
            $app["request.service"],
            $app["response.service"]
        );

        // prepare for output
        $headers = $app["response.service"]->headers->allPreserveCase();
        foreach ($headers as $name => $value) {
            $response->header($name, implode(";", $value));
        }
        $response->status($app["response.service"]->getStatusCode());
        $response->end($app["response.service"]->getContent());
    }

    /**
     * Prepare Swoole Configuration
     *
     * Prepare the Swoole configuration from the Web Server configuration, to
     * ensure that the correct array key names are used, and that only the
     * Swoole configuration items are in the array.
     *
     * @param array $config WebServer config
     * @return array
     */
    protected function _prepSwooleConfig(array $config): array
    {
        return [
            "daemonize" =>  $config["daemonize"],
            "log_file"  =>  $config["logFile"]
        ];
    }

    /**
     * Serve static file
     *
     * Load the file and put its contens into the Response object.
     *
     * @param string $file Name of the file
     * @param swoole_http_response $response Response Object that Swoole will print to requestor
     * @return void
     */
    protected function _serveStaticFile(string $file, swoole_http_response $response)
    {
        $mime = mime_content_type($file);
        $content = file_get_contents($file);

        $response->header("Content-Type", $mime);
        $response->end($content);
    }
}
