<?php

namespace Sholokhov\BitrixOption\Attributes;

use Attribute;

/**
 * Производит описание хранения значения параметра
 * Используется, для автоматического определения и подгрузки параметров модукля
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Option
{
    /**
     * @param string $module ID модуля которому принадлежит модель
     * @param string $name Наименование параметра настройки
     * @param string $storage Хранилище настроек
     */
    public function __construct(
        public readonly string $module,
        public readonly string $name,
        public readonly string $storage
    )
    {

    }
}