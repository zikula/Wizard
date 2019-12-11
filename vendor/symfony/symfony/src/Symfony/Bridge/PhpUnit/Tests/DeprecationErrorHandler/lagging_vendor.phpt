--TEST--
Test DeprecationErrorHandler in weak vendors mode on vendor file
--FILE--
<?php

$k = 'SYMFONY_DEPRECATIONS_HELPER';
putenv($k.'='.$_SERVER[$k] = $_ENV[$k] = 'max[self]=0&max[direct]=0');
putenv('ANSICON');
putenv('ConEmuANSI');
putenv('TERM');

$vendor = __DIR__;
while (!file_exists($vendor.'/vendor')) {
    $vendor = dirname($vendor);
}
define('PHPUNIT_COMPOSER_INSTALL', $vendor.'/vendor/autoload.php');
require PHPUNIT_COMPOSER_INSTALL;
require_once __DIR__.'/../../bootstrap.php';
eval(<<<'EOPHP'
namespace PHPUnit\Util;

class Test
{
    public static function getGroups()
    {
        return array();
    }
}
EOPHP
);
require __DIR__.'/fake_vendor/autoload.php';
require __DIR__.'/fake_vendor/acme/outdated-lib/outdated_file.php';

?>
--EXPECTF--
Remaining indirect deprecation notices (1)

  1x: deprecatedApi is deprecated! You should stop relying on it!
    1x in SomeService::deprecatedApi from acme\lib
