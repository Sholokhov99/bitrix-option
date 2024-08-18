<?php

namespace Sholokhov\BitrixOption\Builder;

use ReflectionException;

use Sholokhov\BitrixOption\Manager;
use Sholokhov\BitrixOption\Exception;
use Sholokhov\BitrixOption\AttributeManager;

class Loader
{
    /**
     * Загрузка настроек
     *
     * @param array{module: string, name: string, siteID: string, storage: string} $config
     * @return Manager
     */
    public static function load(array $config): Manager
    {
        return new Manager($config);
    }

    /**
     * Загрузка настроек на основе объекта
     *
     * @param string|object $entity
     * @param string|null $siteID
     * @return Manager
     * @throws ReflectionException
     * @throws Exception\ConfigurationNotFoundException
     * @throws Exception\InvalidValueException
     */
    public static function loadByEntity(string|object $entity, ?string $siteID = null): Manager
    {
        return new AttributeManager($entity, $siteID);
    }
}