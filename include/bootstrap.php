<?php
/**
 * Copyright (C) 2009-2012  Peter Lind
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 * PHP version 5.3+
 *
 * this file inits the framework. It needs a couple of set definitions (both paths should end with '/':
 * - INCLUDE_PATH: the full path to the include folder
 * - PUBLIC_PATH: the full path to the public folder
 * this one is optional, but as parts of the framework use it, best to init it
 * - PUBLIC_URI: the base part of the uri for the public folder
 *
 * @package   Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

bootstrap_settings();
//bootstrap_fix_magic_quotes();
bootstrap_setup_path_constants();

require FRAMEWORK_FOLDER . 'infosys.php';
require FRAMEWORK_FOLDER . 'functions.php';
require FRAMEWORK_FOLDER . 'config.php';
require FRAMEWORK_FOLDER . 'file_config.php';
require FRAMEWORK_FOLDER . 'dic.php';
require FRAMEWORK_FOLDER . 'exception.php';
require FRAMEWORK_FOLDER . 'autoloader.php';

require __DIR__ . '/../vendor/autoload.php';

/**
 * creates a DbSetupTester with required objects
 *
 * @return DbSetupTester
 */
function getDbTester()
{
    return new DbSetupTester([new MySqlTester()]);
    // get other db types working
    //return new DbSetupTester([new MySqlTester(), new PgSqlTester(), new SqliteTester()]);
}

/**
 * creates the Infosys object and required dependencies
 *
 * @return Infosys
 */
function createInfosys()
{
    $autoload = new Autoloader(
        array(
            FRAMEWORK_FOLDER,
            CONTROLLER_FOLDER,
            MODEL_FOLDER,
            HELPER_FOLDER,
            LIB_FOLDER,
        )
    );

    if (!($environment = getenv('ENVIRONMENT'))) {
        $environment = 'test';
    }

    $config = new FileConfig(INCLUDE_PATH . "config.ini", $environment);
    $dic    = new DIC();

    return new Infosys($autoload, $config, $dic, $environment);
}

/**
 * sets session and encoding settings
 *
 * @return void
 */
function bootstrap_settings() {
    ini_set('session.cookie_lifetime', 3600);
    ini_set('session.gc_maxlifetime', 3600);
    mb_internal_encoding("UTF-8");
}

/**
 * hack to disable damage done by
 * magic quotes if they're enabled
 *
 * @return void
 */
function bootstrap_fix_magic_quotes()
{
    // hack: remove damage done by magic quotes
    // see: http://dk2.php.net/manual/en/security.magicquotes.disabling.php
    if (get_magic_quotes_gpc()) {
        $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);

        while (list($key, $val) = each($process)) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);

                if (is_array($v)) {
                    $process[$key][stripslashes($k)] = $v;
                    $process[]                       = &$process[$key][stripslashes($k)];

                } else {
                    $process[$key][stripslashes($k)] = stripslashes($v);
                }

            }

        }

        unset($process);

    }
}

/**
 * sets up path defines
 *
 * @return void
 */
function bootstrap_setup_path_constants() {
    define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/../public/') . '/'); 
    define('INCLUDE_PATH', realpath(dirname(__FILE__)) . '/'); 
    define('ENVIRONMENT', getenv('ENVIRONMENT'));

    /**
     * Define various folder
     */
    define('FRAMEWORK_FOLDER', INCLUDE_PATH . 'framework/');
    define('CACHE_FOLDER', INCLUDE_PATH . 'cache/');
    define('LOGS_FOLDER', INCLUDE_PATH . 'logs/');
    define('CONTROLLER_FOLDER', INCLUDE_PATH . 'controllers/');
    define('MODEL_FOLDER', INCLUDE_PATH . 'models/');
    define('TEMPLATE_FOLDER', INCLUDE_PATH . 'templates/');
    define('HELPER_FOLDER', INCLUDE_PATH . 'helpers/');
    define('LAYOUT_FOLDER', INCLUDE_PATH . 'layouts/');
    define('ENTITY_FOLDER', INCLUDE_PATH . 'entities/');
    define('LIB_FOLDER', INCLUDE_PATH . 'lib/');
    define('MIGRATION_FOLDER', INCLUDE_PATH . 'migrations/');
    define('SIGNUP_FOLDER', INCLUDE_PATH . 'signup/');
}
