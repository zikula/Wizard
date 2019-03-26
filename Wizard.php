<?php

declare(strict_types=1);

/**
 * Copyright Zikula Foundation 2014.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT.
 * @package Zikula
 * @author Craig Heydenburg
 *
 * Please see the LICENSE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Component\Wizard;

use Gedmo\Exception\RuntimeException;
use InvalidArgumentException;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Wizard
 */
class Wizard
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $stagesByName = [];

    /**
     * @var array
     */
    private $stageOrder = [];

    /**
     * @var string
     */
    private $defaultStage;

    /**
     * @var string
     */
    private $currentStageName;

    /**
     * @var YamlFileLoader
     */
    private $yamlFileLoader;

    /**
     * @var string
     */
    private $warning = '';

    /**
     * Constructor.
     *
     * @throws FileLoaderLoadException
     */
    public function __construct(ContainerInterface $container, string $path)
    {
        $this->container = $container;
        if (!empty($path)) {
            $this->loadStagesFromYaml($path);
        } else {
            throw new FileLoaderLoadException('No stage definition file provided.');
        }
    }

    /**
     * Load the stage definitions from $path
     *
     * @throws FileLoaderLoadException
     */
    public function loadStagesFromYaml(string $path): void
    {
        if (!file_exists($path)) {
            throw new FileLoaderLoadException('Stage definition file cannot be found.');
        }
        $pathInfo = pathinfo($path);
        if ($pathInfo['extension'] !== 'yml') {
            throw new FileLoaderLoadException('Stage definition file must include .yml extension.');
        }

        // empty the stages
        $this->stagesByName = [];
        if (!isset($this->yamlFileLoader)) {
            $this->yamlFileLoader = new YamlFileLoader(new FileLocator($pathInfo['dirname']));
        }
        $this->yamlFileLoader->load($pathInfo['basename']);
        $stages = $this->yamlFileLoader->getContent();
        $stages = $stages['stages'];
        foreach ($stages as $key => $stageArray) {
            $this->stagesByName[$key] = $stageArray['class'];
            $this->stageOrder[$stageArray['order']] = $key;
            if (isset($stageArray['default'])) {
                $this->defaultStage = $key;
            }
        }
    }

    /**
     * Get the stage that is the first necessary stage
     */
    public function getCurrentStage(string $name): StageInterface
    {
        // compute the stageClass from Request parameter
        $stageClass = $this->getStageClassName($name);

        // loop each stage until finds the first that is necessary

        do {
            $useCurrentStage = false;
            /** @var StageInterface $currentStage */
            if (!isset($currentStage)) {
                $currentStage = $this->getStageInstance($stageClass);
            }
            $this->currentStageName = $currentStage->getName();
            try {
                $isNecessary = $currentStage->isNecessary();
            } catch (AbortStageException $e) {
                $this->warning = $e->getMessage();
                $isNecessary = true;
            }
            if ($isNecessary) {
                $useCurrentStage = true;
            } else {
                $currentStage = $this->getNextStage();
            }
        } while (false === $useCurrentStage);

        return $currentStage;
    }

    /**
     * Get an instance of the previous stage
     */
    public function getPreviousStage(): StageInterface
    {
        return $this->getSequentialStage('prev');
    }

    /**
     * Get an instance of the next stage
     */
    public function getNextStage(): StageInterface
    {
        return $this->getSequentialStage('next');
    }

    /**
     * Get either previous or next stage
     */
    private function getSequentialStage(string $direction): ?StageInterface
    {
        $dir = in_array($direction, ['prev', 'next']) ? $direction : 'next';
        ksort($this->stageOrder);
        // forward the array pointer to the current index
        while (current($this->stageOrder) !== $this->currentStageName && null !== key($this->stageOrder)) {
            next($this->stageOrder);
        }
        $key = $dir($this->stageOrder);
        if (null !== $key && false !== $key) {
            return $this->getStageInstance($this->stagesByName[$key]);
        }

        return null;
    }

    /**
     * Factory class to instantiate a StageClass
     */
    private function getStageInstance(string $stageClass): StageInterface
    {
        if (!class_exists($stageClass)) {
            throw new RuntimeException('Error: Could not find requested stage class.');
        }
        if (in_array("Zikula\\Component\\Wizard\\InjectContainerInterface", class_implements($stageClass), true)) {
            return new $stageClass($this->container);
        }

        return new $stageClass();
    }

    /**
     * Has the wizard been halted?
     */
    public function isHalted(): bool
    {
        return !empty($this->warning);
    }

    /**
     * Get any warning currently set
     */
    public function getWarning(): string
    {
        return 'WARNING: The Wizard was halted for the following reason. This must be corrected before you can continue. ' . $this->warning;
    }

    /**
     * Match the stage and return the stage classname or default.
     *
     * @throws InvalidArgumentException
     */
    private function getStageClassName(string $name): string
    {
        if (!empty($this->stagesByName[$name])) {
            return $this->stagesByName[$name];
        }
        if (!empty($this->defaultStage) && !empty($this->stagesByName[$this->defaultStage])) {
            return $this->stagesByName[$this->defaultStage];
        }
        throw new InvalidArgumentException('The request stage could not be found and there is no default stage defined.');
    }
}
