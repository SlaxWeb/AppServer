<?php
/**
 * Component Post Install
 *
 * Adds the command to the config file after the package has been installed.
 *
 * @package   SlaxWeb\AppServer
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.1
 */
use SlaxWeb\Bootstrap\Application;

function run(Application $app)
{
    mkdir("{$app["appDir"]}/Component/AppServer/");
    return system("cp appserver.php {$app["appDir"]}/Component/AppServer/");
}
