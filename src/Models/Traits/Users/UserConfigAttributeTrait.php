<?php

namespace Common\Models\Traits\Users;

trait UserConfigAttributeTrait
{
    /**
     * Атрибут для автоматического парсинга значения колонки `value`.
     *
     * @return array|string
     */
    public function getValueAttribute($value)
    {
        // Если поле `type` соответствует одному из ALL_BROKER_API, парсим JSON
        if (in_array($this->type, self::ALL_BROKER_API)) {
            return json_decode($value, true) ?? [];
        }

        // В остальных случаях возвращаем оригинальное значение
        return $value;
    }

    /**
     * Атрибут для автоматического преобразования массива в JSON при записи.
     *
     * @param array|string $value
     */
    public function setValueAttribute($value): void
    {
        // Если тип из ALL_BROKER_API, преобразуем массив в JSON
        if (is_array($value) && in_array($this->type, self::ALL_BROKER_API)) {
            $this->attributes['value'] = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            // В остальных случаях записываем как есть
            $this->attributes['value'] = $value;
        }
    }
}