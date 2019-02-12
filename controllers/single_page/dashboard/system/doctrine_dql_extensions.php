<?php

namespace Concrete\Package\Concrete5DoctrineDqlExtensions\Controller\SinglePage\Dashboard\System;

use Concrete\Core\Package\Package;

/**
 * Behavioral settings controller
 *
 * @author Markus Liechti <markus@liechti.io>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class DoctrineDqlExtensions extends \Concrete\Core\Page\Controller\DashboardPageController{


    const DQL_STING_FUNCTION_KEYS = array(
        'customDatetimeFunctions',
        'customNumericFunctions',
        'customStringFunctions');

    /**
     * Constructor
     * 
     * @param \Concrete\Core\Page\Page $c
     */
    public function __construct(\Concrete\Core\Page\Page $c) {
        parent::__construct($c);
    }
    
    /**
     * Show cateogry Tree
     */
    public function view(){

        $em = $this->app->make('Doctrine\ORM\EntityManager');
        $config = $em->getConfiguration();

        $customFunctions = $this->filterCustomDQLFunctions($config);

        $this->set('customFunctions', $customFunctions);

    }
    
    /**
     * With this function a protected or private 
     * property of a object can be accessed
     * 
     * @param object $obj
     * @param string $prop
     * @return type
     * @throws \ReflectionException
     */
    protected function accessProtected($obj, $prop) {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }

    /**
     * Filter protected ORM config values so only the custom functions are kept
     *
     * @param $config \Doctrine\ORM\Configuration
     * @return array
     */
    protected function filterCustomDQLFunctions($config){
        // Access the private property for \Doctrine\ORM\Config

        try{
            $customFunctions = $this->accessProtected($config, '_attributes');
        }catch(\ReflectionException $e){
            \Log::addAlert('The registered DQL functions could not be retrieved. ' . $e);
        }

        $allowedKeys = self::DQL_STING_FUNCTION_KEYS;
        $filteredFunctions = array_filter($customFunctions, function($key) use ($allowedKeys){
            return in_array($key, $allowedKeys);
        }, ARRAY_FILTER_USE_KEY);

        return $filteredFunctions;
    }
}
