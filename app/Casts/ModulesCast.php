<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class ModulesCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $modules = collect();
        foreach (json_decode($value) as $module) {
            $reflectionClass = new ReflectionClass($module);
            $module = $reflectionClass->newInstance();
            $modules->push($module);
        }
        return $modules;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $modules = [];
        foreach ($value as $module) {
            $modules[] = get_class($module);
        }
        return json_encode($modules);
    }
}
