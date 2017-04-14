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
    
    /**
     * @var Concrete\Core\Package\Package 
     */
    private $package;
    
    
    /**
     * Constructor
     * 
     * @param \Concrete\Core\Page\Page $c
     */
    public function __construct(\Concrete\Core\Page\Page $c) {
        parent::__construct($c);
        $this->package = Package::getByHandle('concrete5_doctrine_query_extensions');
    }
    
    /**
     * Show cateogry Tree
     */
    public function view(){

        $em = $this->app->make('Doctrine\ORM\EntityManager');
        $config = $em->getConfiguration();
        
        // Access the private property for \Doctrine\ORM\Config
        $customStringFunctions = $this->accessProtected($config, '_attributes');
        $this->set('customStringFunctions', $customStringFunctions);

    }
    
    /**
     * With this function a protected or private 
     * property of a object can be accessed
     * 
     * @param object $obj
     * @param string $prop
     * @return type
     */
    protected function accessProtected($obj, $prop) {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }
}
