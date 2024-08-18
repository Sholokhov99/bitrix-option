<?php

namespace Sholokhov\BitrixOption;

use ReflectionClass;
use ReflectionException;

use Sholokhov\BitrixOption\Attributes\Option;

/**
 * Производит инициализацию параметра модуля на основе объекта.
 * У объекта должен присутстовать атрибут {@see Option}
 */
class AttributeManager extends Manager
{
    /**
     * @param string|object $entity Объект на основе которого происходи определение конфигурация
     * @param string|null $siteID ID сайта, которому должны принадлежит значение параметра
     * @throws Exception\ConfigurationNotFoundException
     * @throws Exception\InvalidValueException
     * @throws ReflectionException
     */
    public function __construct(string|object $entity, ?string $siteID = null)
    {
        $configuration = $this->loadConfiguration($entity);
        $configuration['siteID'] = $siteID;

        parent::__construct($configuration);
    }

    /**
     * Инициализация настроек менеджера конфигураций
     *
     * @param string|object $entity
     * @return array
     * @throws Exception\ConfigurationNotFoundException
     * @throws Exception\InvalidValueException
     * @throws ReflectionException
     */
    private function loadConfiguration(string|object $entity): array
    {
        $configuration = $this->getAttribute($entity);

        if (!($configuration instanceof Option)) {
            throw new Exception\ConfigurationNotFoundException("Missing configuration");
        }

        if ('' === $configuration->module) {
            throw new Exception\InvalidValueException("No module affiliation");
        }

        if ('' === $configuration->name) {
            throw new Exception\InvalidValueException("Parameter name not specified");
        }

        if (!is_subclass_of($configuration->storage, StorageInterface::class)) {
            throw new Exception\InvalidValueException('Storage not implemented "' . StorageInterface::class . "'");
        }

        return [
            'module' => $configuration->module,
            'name' => $configuration->name,
            'storage' => $configuration->storage
        ];
    }

    /**
     * Получение атрибута объекта
     *
     * @throws ReflectionException
     */
    private function getAttribute(string|object $entity): ?Option
    {
        $reflection = new ReflectionClass($entity);
        $attributes = $reflection->getAttributes(Option::class);

        if (empty($attributes)) {
            return null;
        }

        return current($attributes)->newInstance();
    }
}