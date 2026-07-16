<?php namespace Acorn\CreateSystem\Util;

class Str
{
    protected static $inflector;

    protected static $pluralExceptions = array(
        'gps' => 'gps',
        // We use the academic option in order to differentiate
        'status' => 'statuses', 
    );
    protected static $singularExceptions = array(
        'gps' => 'gps',
        'job_batches' => 'job_batch',
        // Fix offices => offix!, prices => prix!
        'offices'  => 'office',
        'prices'   => 'price',
        // We use the academic option in order to differentiate
        'statuses' => 'status',
        'address'  => 'address',
    );

    // Copied and commented from Laravel
    // ~/vendor/laravel/framework/src/Illuminate/Support/Str.php

    /**
     * Attempt to match the case on two strings.
     *
     * @param  string  $value
     * @param  string  $comparison
     * @return string
     */
    protected static function matchCase($value, $comparison)
    {
        $functions = ['mb_strtolower', 'mb_strtoupper', 'ucfirst', 'ucwords'];

        foreach ($functions as $function) {
            if ($function($comparison) === $comparison) {
                return $function($value);
            }
        }

        return $value;
    }

    /**
     * The cache of camel-cased words.
     *
     * @var array
     */
    protected static $camelCache = [];

    /**
     * The cache of studly-cased words.
     *
     * @var array
     */
    protected static $studlyCache = [];

    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     * @return string
     */
    public static function camel($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $words = explode(' ', str_replace(['-', '_'], ' ', $value));

        $studlyWords = array_map(fn ($word) => ucfirst($word), $words);

        return static::$studlyCache[$key] = implode($studlyWords);
    }

    /**
     * Convert the given string to title case.
     *
     * @param  string  $value
     * @return string
     */
    public static function title($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    public static function plural(string $value, $count = 2): string
    {
        if (isset(self::$pluralExceptions[strtolower($value)])) {
            $plurals = array(self::$pluralExceptions[strtolower($value)]);
        } else {
            if (!self::$inflector) self::$inflector = new EnglishInflector();
            $plurals = self::$inflector->pluralize($value);
        }
        return static::matchCase($plurals[0], $value);
    }

    public static function singular(string $value): string
    {
        $lower = strtolower($value);
        if (isset(self::$singularExceptions[$lower])) {
            $singular = self::$singularExceptions[$lower];
        } else {
            // Also check just the last underscore-delimited component (e.g. 'prices' in 'product_prices')
            $parts    = explode('_', $lower);
            $lastPart = end($parts);
            if (isset(self::$singularExceptions[$lastPart])) {
                $parts[\count($parts) - 1] = self::$singularExceptions[$lastPart];
                $singular = implode('_', $parts);
            } else {
                if (!self::$inflector) self::$inflector = new EnglishInflector();
                $singulars = self::$inflector->singularize($value);
                $option    = (isset($singulars[1]) ? 1 : 0);
                return static::matchCase($singulars[$option], $value);
            }
        }
        return static::matchCase($singular, $value);
    }
}
