<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

trait HasTranslatableFields
{
    /**
     * Map translatable fields based on user role.
     * 
     * @param array $fields
     * @param bool $isAdmin
     * @return array
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
