<?php

namespace Concrete\Package\Concrete5DoctrineDqlExtensions;

use Doctrine\Common\EventManager;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Package controller
 *
 * @author Markus Liechti <markus@liechti.io>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Controller extends \Concrete\Core\Package\Package
{

    protected $pkgHandle = 'concrete5_doctrine_dql_extensions';
    protected $appVersionRequired = '8.0.0';
    protected $pkgVersion = '0.2.0';

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
        \Concrete\Core\Page\Single::add('/dashboard/system/doctrine_dql_extensions', $pkg);
    }

    public function on_start()
    {
        // register the autoloading
        if (file_exists($this->getPackagePath() . '/vendor/autoload.php')) {
            require $this->getPackagePath() . '/vendor/autoload.php';
        }

        $em = $this->app->make('Doctrine\ORM\EntityManager');
        $config = $em->getConfiguration();
        $this->registerDoctrineDqlExtensions($config);
    }

    /**
     * Register Doctrine2 dql extensions
     *
     * @param EventManager $evm
     * @param Reader $cachedAnnotationReader
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
            //printf("Unable to parse the YAML string: %s", $e->getMessage());
        }
        return $config;
    }

    /**
     * Get path to MySQL yaml config
     * 
     * Search eather in {package-root}/vendor or in {root}/concrete5/vendor
     * 
     * @return string
     */
    protected function getMysqlConfig()
    {

        $pathPackage = $this->getPackagePath() . DIRECTORY_SEPARATOR . 'vendor'
                . DIRECTORY_SEPARATOR . 'beberlei' . DIRECTORY_SEPARATOR
                . 'DoctrineExtensions' . DIRECTORY_SEPARATOR . 'config'
                . DIRECTORY_SEPARATOR . 'mysql.yml';

        $pathVendor = DIR_BASE . DIRECTORY_SEPARATOR . DIRNAME_CORE
                . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR
                . 'beberlei' . DIRECTORY_SEPARATOR
                . 'DoctrineExtensions' . DIRECTORY_SEPARATOR . 'config'
                . DIRECTORY_SEPARATOR . 'mysql.yml';

        if (file_exists($pathPackage)) {
            $path = $pathPackage;
        } elseif (file_exists($pathVendor)) {
            $path = $pathVendor;
        } else {
            // @todo handle error
        }

        return $path;
    }

}
