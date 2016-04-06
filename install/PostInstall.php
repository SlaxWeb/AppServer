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
    $dir = "{$app["appDir"]}Config/Component/AppServer/";
    $file = __DIR__ . "/appserver.php";
    if (file_exists($dir) === false) {
        mkdir($dir, 0755, true);
    }

    $exit = 0;
    system("cp {$file} {$dir}", $exit);

    return $exit;
}
