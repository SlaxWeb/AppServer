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
use SlaxWeb\Bootstrap\Application as App;

class WebServer
{
    /**
     * SlaxWeb Application
     *
     * @var \SlaxWeb\Bootstrap\Application
     */
    protected $app = null;

    /**
     * Http Server
     *
     * @var swoole_http_server
     */
    protected $_http = null;

    /**
     * Logger instance
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger = null;

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
     * @param \SlaxWeb\Bootstrap\Application $app
     */
    public function __construct(swoole_http_server $http, App $app)
    {
        $this->_http = $http;
        $this->app = $app;
        $this->logger = $app["logger.service"]("WebServer");
        $this->_config = $app["webserver.config"];

        $this->_http->on("request", [$this, "_onRequest"]);
        $this->_http->on("start", [$this, "_onServerStart"]);
        $this->_http->on("shutdown", [$this, "_onServerShutdown"]);

        $this->_http->set($this->_prepSwooleConfig($this->_config));

        $this->logger->info("WebServer class initialized");
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

        $this->logger->debug("Web server started under PID <{$server->master_pid}>");
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

        $this->logger->debug("Web server shutting down. PID file removed.");
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
            . ltrim($request->server["request_uri"], "/");

        $this->logger->debug(
            "Received request.",
            ["uri" => $request->server["request_uri"], "requestFile" => $requestFile]
        );

        if (file_exists($requestFile)
            && is_dir($requestFile) === false
            && basename($requestFile) !== $this->_config["frontController"]
        ) {
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

        // copy request data to $_SERVER superglobal
        $this->setRequestData($request);

        // run app code
        $app->run(
            $app["request.service"],
            $app["response.service"]
        );

        $this->logger->info("Request processed by the app, preparing output");

        // prepare for output
        $headers = $app["response.service"]->headers->allPreserveCase();
        $code = $app["output.service"]->getStatusCode()
            ?: $app["response.service"]->getStatusCode();
        $content = $app["output.service"]->render()
            ?: $app["response.service"]->getContent();
        foreach ($headers as $name => $value) {
            $response->header($name, implode(";", $value));
        }
        $response->status($code);
        $response->end($content);

        $this->logger->debug(
            "Request processed and output",
            ["statusCode" => $code, "headers" => $headers, "content" => $content]
        );
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
     * Set Request to superglobal
     *
     * Sets the $_SERVER superglobal values from the swoole request object.
     *
     * @param swoole_http_request $request Incoming Request Object
     * @return void
     */
    protected function setRequestData(swoole_http_request $request)
    {
        $_SERVER["HTTP_ACCEPT_LANGUAGE"] = $request->header["accept-language"] ?? "";
        $_SERVER["HTTP_ACCEPT_ENCODING"] = $request->header["accept-encoding"] ?? "";
        $_SERVER["HTTP_ACCEPT"] = $request->header["accept"] ?? "";
        $_SERVER["HTTP_USER_AGENT"] = $request->header["user-agent"] ?? "";
        $_SERVER["HTTP_UPGRADE_INSECURE_REQUESTS"] = $request->header["upgrade-insecure-requests"] ?? "";
        $_SERVER["HTTP_CACHE_CONTROL"] = $request->header["cache-control"] ?? "";
        $_SERVER["HTTP_CONNECTION"] = $request->header["connection"] ?? "";
        $_SERVER["HTTP_HOST"] = $request->header["host"] ?? "";
        $_SERVER["REQUEST_METHOD"] = $request->server["request_method"] ?? "";
        $_SERVER["REQUEST_URI"] = $request->server["request_uri"] ?? "";
        $_SERVER["SERVER_PROTOCOL"] = $request->server["server_protocol"] ?? "";
        $_SERVER["REMOTE_PORT"] = $request->server["remote_port"] ?? "";
        $_SERVER["REMOTE_ADDR"] = $request->server["remote_addr"] ?? "";
        $_SERVER["SERVER_SOFTWARE"] = $request->server["server_software"] ?? "";
        $_SERVER["SERVER_PORT"] = $request->server["server_port"] ?? "";
        $_SERVER["SERVER_ADDR"] = $request->server["server_addr"] ?? "";
        $_SERVER["REQUEST_TIME_FLOAT"] = $request->server["request_time_float"] ?? "";
        $_SERVER["REQUEST_TIME"] = $request->server["request_time"] ?? "";

        $_GET = $request->get ?? [];
        $_POST = $request->post ?? [];
        $_COOKIE = $request->cookie ?? [];

        $this->logger->debug(
            "Request data has been set",
            ["server" => $_SERVER, "get" => $_GET, "post" => $_POST, "cookie" => $_COOKIE]
        );
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
        $this->logger->debug("Loading static file for request", ["file" => $file]);
        $mime = mime_content_type($file);
        $content = file_get_contents($file);

        $response->header("Content-Type", $mime);
        $response->end($content);

        $this->logger->debug("Static file served", ["file" => $file]);
    }
}
