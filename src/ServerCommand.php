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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends Command
{
    /**
     * Operations
     *
     * @var array
     */
    protected $_operations = "start|stop|restart";

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
     * Start Server
     *
     * Start the Web Server as a daemon.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output Command Output Object
     * @return void
     */
    protected function _handleStart(OutputInterface $output)
    {
        $output->writeln("<comment>Soon!</>");
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
