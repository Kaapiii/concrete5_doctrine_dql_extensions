<?php

namespace Concrete\Package\Concrete5DoctrineDqlExtensions\Controller\SinglePage\Dashboard\System;

use Concrete\Core\Page\Controller\DashboardPageController;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Log;
use ReflectionClass;
use ReflectionException;

/**
 * Behavioral settings controller.
 *
 * @author Markus Liechti <markus@liechti.io>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
final class DoctrineDqlExtensions extends DashboardPageController
{
    public const DQL_STING_FUNCTION_KEYS = [
        'customDatetimeFunctions',
        'customNumericFunctions',
        'customStringFunctions',
    ];

    /**
     * Show category Tree.
     */
    public function view(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->app->make(EntityManager::class);
        $config = $em->getConfiguration();
        $customFunctions = $this->filterCustomDQLFunctions($config);
        $this->set('customFunctions', $customFunctions);
    }

    /**
     * With this function a protected or private
     * property of a object can be accessed.
     *
     * @param object $obj
     *
     * @throws ReflectionException
     *
     * @return type
     */
    protected function accessProtected($obj, string $prop): mixed
    {
        $reflection = new ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }

    /**
     * Filter protected ORM config values so only the custom functions are kept.
     */
    protected function filterCustomDQLFunctions(Configuration $config): array
    {
        // Access the private property for \Doctrine\ORM\Config
        try {
            $customFunctions = $this->accessProtected($config, '_attributes');
        } catch (ReflectionException $e) {
            Log::addAlert('The registered DQL functions could not be retrieved. ' . $e);
        }

        $allowedKeys = self::DQL_STING_FUNCTION_KEYS;

        return array_filter(
            $customFunctions,
            static fn ($key) => in_array($key, $allowedKeys),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
