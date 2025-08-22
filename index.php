<?php

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */
$minPhpVersion = '8.1'; // If you update this, don't forget to update spark.
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION
    );

    die($message);
}

/*
 *---------------------------------------------------------------
 * LOAD OUR PATHS CONFIG FILE
 *---------------------------------------------------------------
 */
require_once __DIR__ . '/app/Config/Paths.php';

$paths = new Config\Paths();

/*
 *---------------------------------------------------------------
 * LOAD THE FRAMEWORK BOOTSTRAP FILE
 *---------------------------------------------------------------
 */
require rtrim($paths->systemDirectory, '/\\') . DIRECTORY_SEPARATOR . 'Boot.php';

exit(CodeIgniter\Boot::bootWeb($paths));
