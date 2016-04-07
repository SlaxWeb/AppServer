<?php
/**
 * Server CLI Command
 *
 * Provides control over the built-in server, to start, stop, and restart the
 * server.
 *
 * @package   SlaxWeb\AppServer
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.1
 */
namespace SlaxWeb\AppServer;

use SlaxWeb\Bootstrap\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command
{
    /**
     * Framework Instance
     *
     * @var \SlaxWeb\Bootstrap\Application
     */
    protected $_app = null;

    /**
     * Operations
     *
     * @var array
     */
    protected $_operations = "start|stop|restart";

    /**
     * Command init
     *
     * Put Framework Instance into local protected property.
     *
     * @param \SlaxWeb\Bootstrap\Application $app Framework Instance
     */
    public function init(Application $app)
    {
        $this->_app = $app;
    }

    /**
     * Configure the command
     *
     * Prepare the command for inclussion into the CLI Application Slaxer.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName("server")
            ->setDescription("SlaxWeb Framework Server Management")
            ->addArgument(
                "operation",
                InputArgument::REQUIRED,
                "The operation to execute: {{$this->_operations}}"
            );
    }

    /**
     * Execute the command
     *
     * Start, Stop, or Restart the server based on the input argument. If an
     * unknown operation is supplied, an error is displayed.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input Command Input Object
     * @param \Symfony\Component\Console\Output\OutputInterface $output Command Output Object
     * @return void
     *
     * @todo Run PostInstall.php script after package has been installed.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = strtolower($input->getArgument("operation"));
        if (in_array($operation, explode("|", $this->_operations)) === false) {
            $output->writeln(
                "<error>Unknown operation. Possible operations: {{$this->_operations}}"
            );
        }

        $operation = "_handle" . ucfirst($operation);
        $this->{$operation}($output);
    }

    /**
     * Prepare config
     *
     * Replace all placeholders in configuration items with values from the
     * application properties with same name. Recursive.
     *
     * @param array $config Configuration array
     * @return array
     */
    protected function _prepConfig(array $config): array
    {
        foreach ($config as &$item) {
            if (is_array($item)) {
                $item = $this->_prepConfig($item);
                continue;
            }

            $item = preg_replace_callback("~%{(.*)}%~", function ($matches) {
                return $this->_app[$matches[1]] ?? $matches[1];
            }, $item);
        }
        unset($item);
        return $config;
    }

    /**
     * Start Server
     *
     * Start the Web Server as a daemon.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output Command Output Object
     * @return void
     */
    protected function _handleStart(OutputInterface $output)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln("<comment>Loading config</>");
        }
        $config = $this->_prepConfig(
            $this->_app["config.service"]["appserver.webserver"]
        );
        $this->_app["webserver.config"] = $config;
        $this->_app["webserver.config"]["daemonize"] = true;
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln("<comment>Config loaded</>");
        }

        $output->writeln("<comment>Check server running ...</>");
        if (file_exists($this->_app["webserver.config"]["pidFile"])) {
            $output->writeln("<error>Server already running, use 'stop' or 'restart'</>");
            return;
        }

        $output->writeln("<comment>Starting Web Server ...</>");
        /*
         * @todo: Put provider class into component provideres configuration
         * item, when framework will load them in that fashion as commands
         */
        $this->_app->register(new \SlaxWeb\AppServer\Service\Provider);
        $this->_app["webserver.service"]->start();
        $output->writeln("<comment>OK</>");
    }

    /**
     * Stop Server
     *
     * Stop the Web Server.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output Command Output Object
     * @return void
     */
    protected function _handleStop(OutputInterface $output)
    {
        $output->writeln("<comment>Soon!</>");
    }

    /**
     * Restart Server
     *
     * Restart the Web Server by calling '_handleStop' and then '_handleStart'
     * immediately.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output Command Output Object
     * @return void
     */
    protected function _handleRestart(OutputInterface $output)
    {
        $output->writeln("<comment>Soon!</>");
    }
}
