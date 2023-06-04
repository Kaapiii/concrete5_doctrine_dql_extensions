<?php

namespace Concrete\Package\Concrete5DoctrineDqlExtensions;

use Concrete\Core\Package\Package;
use Concrete\Core\Page\Single as PageSingle;
use Concrete\Core\Support\Facade\Log;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Package controller.
 *
 * @author Markus Liechti <markus@liechti.io>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
final class controller extends Package
{
    public const BEBERLEI = 'beberlei';

    public const DOCTRINEEXTENSIONS = 'doctrineextensions';

    public const CONFIG_FILE_NAME = 'mysql.yml';

    protected $appVersionRequired = '8.0.0';

    protected $pkgHandle = 'concrete5_doctrine_dql_extensions';

    protected $pkgVersion = '1.2.0';

    public function getPackageDescription()
    {
        return t('Package adds additional MySQL functions for Doctrine Query Language and QueryBuilder');
    }

    public function getPackageName()
    {
        return t('Doctrine DQL Extensions');
    }

    public function install()
    {
        $pkg = parent::install();
        PageSingle::add('/dashboard/system/doctrine_dql_extensions', $pkg);
    }

    public function on_start()
    {
        if (file_exists($this->getPackagePath() . '/vendor/autoload.php')) {
            require_once $this->getPackagePath() . '/vendor/autoload.php';
        }

        $em = $this->app->make(EntityManagerInterface::class);
        $config = $em->getConfiguration();
        $this->registerDoctrineDqlExtensions($config);
    }

    /**
     * Register Doctrine DQL extensions.
     */
    public function registerDoctrineDqlExtensions(Configuration $config)
    {
        $configSQL = $this->parseDoctrineQueryExtensionConfig();

        $dqlFunctions = $configSQL['doctrine']['orm']['dql'];

        $datetimeFunctions = $dqlFunctions['datetime_functions'];
        $numericFunctions = $dqlFunctions['numeric_functions'];
        $stringFunctions = $dqlFunctions['string_functions'];
        if (count($datetimeFunctions)) {
            foreach ($datetimeFunctions as $name => $class) {
                $config->addCustomDatetimeFunction($name, $class);
            }
        }
        if (count($numericFunctions)) {
            foreach ($numericFunctions as $name => $class) {
                $config->addCustomNumericFunction($name, $class);
            }
        }
        if (count($stringFunctions)) {
            foreach ($stringFunctions as $name => $class) {
                $config->addCustomStringFunction($name, $class);
            }
        }

        return $config;
    }

    /**
     * Get path to MySQL yaml config the vendor
     * It takes into account how the package was installed.
     *
     * @return string
     */
    protected function getMysqlConfig()
    {
        // Path to the mysql config, if package was installed manually, and the
        // package contains a 'vendor' directory
        $localVendorPath = $this->getPackagePath() . DIRECTORY_SEPARATOR . DIRNAME_VENDOR
            . DIRECTORY_SEPARATOR . self::BEBERLEI . DIRECTORY_SEPARATOR
            . self::DOCTRINEEXTENSIONS . DIRECTORY_SEPARATOR . DIRNAME_CONFIG
            . DIRECTORY_SEPARATOR . self::CONFIG_FILE_NAME;

        $globalVendorPath = DIR_BASE_CORE . DIRECTORY_SEPARATOR . DIRNAME_VENDOR
            . DIRECTORY_SEPARATOR . self::BEBERLEI . DIRECTORY_SEPARATOR
            . self::DOCTRINEEXTENSIONS . DIRECTORY_SEPARATOR . DIRNAME_CONFIG
            . DIRECTORY_SEPARATOR . self::CONFIG_FILE_NAME;

        // needed if concrete5 was created with "composer create-project -n concrete5/composer ."
        $globalVendorPathWithComposer = DIR_BASE . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . DIRNAME_VENDOR
            . DIRECTORY_SEPARATOR . self::BEBERLEI . DIRECTORY_SEPARATOR
            . self::DOCTRINEEXTENSIONS . DIRECTORY_SEPARATOR . DIRNAME_CONFIG
            . DIRECTORY_SEPARATOR . self::CONFIG_FILE_NAME;

        if (file_exists($localVendorPath)) {
            $path = $localVendorPath;
        } elseif (file_exists($globalVendorPath)) {
            $path = $globalVendorPath;
        } else {
            $path = $globalVendorPathWithComposer;
        }

        return $path;
    }

    /**
     * Parse yaml config of MySQL doctrine dql extensions.
     *
     * @return array
     */
    protected function parseDoctrineQueryExtensionConfig()
    {
        try {
            $config = Yaml::parse(file_get_contents($this->getMysqlConfig()));
        } catch (ParseException $e) {
            Log::addAlert('Unable to parse the MySQL YAML config file: ' . $e);
        }

        return $config;
    }
}
