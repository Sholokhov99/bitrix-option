<?php

namespace Sholokhov\BitrixOption;

use Exception;
use Throwable;
use TypeError;
use InvalidArgumentException;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;

/**
 * Позволяет подгружать управлять конфигурациями модуля в рамках таблицы b_option_site.
 */
class Manager
{
    /**
     * Хранилище настроек
     *
     * @var StorageInterface|null
     */
    private ?StorageInterface $storage = null;

    /**
     * Конфигурация  загрузки
     *
     * @var array{module: string, name: string, siteID: string, storage: string}
     */
    private readonly array $configuration;

    /**
     * @param array{module: string, name: string, siteID: string, storage: string} $config
     */
    public function __construct(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * Получение значения параметра в формате объекта
     *
     * @return StorageInterface
     */
    public function get(): StorageInterface
    {
        return $this->storage ??= $this->load();
    }

    /**
     * Установка значения параметра
     *
     * @param StorageInterface $storage
     * @return Result
     * @throws Exception
     */
    public function set(StorageInterface $storage): Result
    {
        $this->storage = $storage;
        return $this->save();
    }

    /**
     * Удаление настроек
     *
     * @return void
     * @throws ArgumentNullException
     */
    public function remove(): void
    {
        $filter = [
            'name' => $this->getName(),
            'site_id' => $this->getSiteID()
        ];

        Option::delete($this->getModule(), $filter);
        $this->storage = null;
    }

    /**
     * Сохранение настроек
     *
     * @return Result
     * @throws Exception
     */
    public function save(): Result
    {
        $result = new Result();
        $value = $this?->storage->toString() ?: '';

        try {
            Option::set($this->getModule(), $this->getName(), $value, $this->getSiteID());
        } catch (Throwable $throwable) {
            $result->addError(new Error($throwable->getMessage(), $throwable->getCode()));
        }

        return $result;
    }

    /**
     * Загрузка значения параметра
     *
     * Если в хранилище {@see self::$config::$storage()} не зафиксированы изменения,
     * то они будут утеряны
     *
     * @return StorageInterface
     */
    public function load(): StorageInterface
    {
        return $this->storage = $this->search();
    }

    /**
     * ID модуль, которому принадлежат настройки
     *
     * @return string
     */
    public function getModule(): string
    {
        return $this->configuration['module'];
    }

    /**
     * Название параметра
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->configuration['name'];
    }

    /**
     * ID сайта, которому принадлежат настройки
     *
     * @return string
     */
    public function getSiteID(): string
    {
        return $this->configuration['siteID'];
    }

    /**
     * Поиск значения параметра
     *
     * @return StorageInterface
     */
    private function search(): StorageInterface
    {
        $value = Option::get($this->getModule(), $this->getName(), '', $this->getSiteID());
        return $this->configuration['storage']::fromString($value);
    }

    /**
     * Производит установку конфигураций загрузчика
     * Если конфигурации не пройдут проверку, то будет вызвано исключение
     *
     * @param array{module: string, name: string, siteID: string, storage: StorageInterface} $config
     * @return void
     */
    private function setConfig(array $config): void
    {
        $module = trim($config['module']);
        $name = trim($config['name']);
        $siteID = trim($config['siteID']) ?: SITE_ID;
        $storage = $config['storage'];

        if ('' === $module) {
            throw new InvalidArgumentException('Model ID not specified');
        }

        if ('' === $name) {
            throw new InvalidArgumentException('Parameter name not specified');
        }

        if (!is_string($storage)) {
            throw new TypeError('The storage is not a string');
        }

        if (!is_subclass_of($storage, StorageInterface::class)) {
            throw new InvalidArgumentException('The storage does not implement the StorageInterface interface');
        }

        $this->configuration = compact('module', 'name', 'siteID', 'storage');
    }
}