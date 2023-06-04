<?php

namespace Concrete\Package\Concrete5DoctrineDqlExtensions;

use Concrete\Core\Support\Facade\Log;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Single as PageSingle;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Package controller
 *
 * @author Markus Liechti <markus@liechti.io>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Controller extends Package
{
    const BEBERLEI = 'beberlei';
    const DOCTRINEEXTENSIONS = 'doctrineextensions';
    const CONFIG_FILE_NAME = 'mysql.yml';

    protected $pkgHandle          = 'concrete5_doctrine_dql_extensions';
    protected $appVersionRequired = '8.0.0';
    protected $pkgVersion         = '1.1.1';

    public function getPackageDescription()
    {
        return t('Package adds additional MySQL functions for Doctrine2 query language and QueryBuilder');
    }

    public function getPackageName()
    {
        return t('Doctrine2 dql extensions');
    }

    public function install()
    {
        $pkg = parent::install();
        PageSingle::add('/dashboard/system/doctrine_dql_extensions', $pkg);
    }

    public function on_start()
    {
        // register the autoloading
        if (file_exists($this->getPackagePath() . '/vendor/autoload.php')) {
            require $this->getPackagePath() . '/vendor/autoload.php';
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->app->make('Doctrine\ORM\EntityManager');
        /** @var \Doctrine\ORM\Configuration $config */
        $config = $em->getConfiguration();
        try {
            $this->registerDoctrineDqlExtensions($config);
        } catch (\Doctrine\ORM\ORMException $e) {
            Log::addAlert('While adding Doctrine DQL extensions to the EntityManager configuration something went wrong: ' . $e);
        }
    }


    /**
     * Register Doctrine2 dql extensions
     *
     * @param $config \Doctrine\ORM\Configuration
     * @return \Doctrine\ORM\Configuration mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function registerDoctrineDqlExtensions($config)
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
     * Parse yaml config of MySQL doctrine dql extensions
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

    /**
     * Get path to MySQL yaml config the vendor
     * It takes into account how the package was installed
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

        # needed if concrete5 was created with "composer create-project -n concrete5/composer ."
        $globalVendorPathWithComposer = DIR_BASE . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . DIRNAME_VENDOR
            . DIRECTORY_SEPARATOR . self::BEBERLEI . DIRECTORY_SEPARATOR
            . self::DOCTRINEEXTENSIONS . DIRECTORY_SEPARATOR . DIRNAME_CONFIG
            . DIRECTORY_SEPARATOR . self::CONFIG_FILE_NAME;

        if (file_exists($localVendorPath)) {
            $path = $localVendorPath;
        } else if (file_exists($globalVendorPath)) {
            $path = $globalVendorPath;
        } else {
            $path = $globalVendorPathWithComposer;
        }

        return $path;
    }
}
