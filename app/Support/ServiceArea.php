<?php

namespace App\Support;

final class ServiceArea
{
    /**
     * @return list<array{'@type': string, name: string}>
     */
    public static function places(): array
    {
        return [
            ['@type' => 'City', 'name' => 'Enschede'],
            ['@type' => 'City', 'name' => 'Hengelo'],
            ['@type' => 'City', 'name' => 'Almelo'],
            ['@type' => 'AdministrativeArea', 'name' => 'Twente'],
            ['@type' => 'Country', 'name' => 'Netherlands'],
        ];
    }
}
