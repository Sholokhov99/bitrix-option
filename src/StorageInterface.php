<?php

namespace Sholokhov\BitrixOption;

use Stringable;

interface StorageInterface extends Stringable
{
    /**
     * Преобразование данный в строку
     *
     * @return string
     */
    public function toString(): string;

    /**
     * Заполнение объекта из строки
     *
     * @param string $value
     * @return self
     */
    public static function fromString(string $value): self;
}