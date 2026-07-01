<?php

namespace App\Http\Resources;

trait HasTranslatableFields
{
    /**
     * Map translatable fields based on user role.
     */
    protected function mapTranslatable(array $fields, bool $isAdmin = false): array
    {
        $result = [];
        foreach ($fields as $field) {
            $result[$field] = $isAdmin
                ? $this->getTranslations($field)
                : $this->$field;
        }

        return $result;
    }
}
