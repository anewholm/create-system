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
        // -ches cannot be settled by suffix: watches => watch but
        // niches => niche. The inflector defaults to the -e reading, so the
        // ones that drop it have to be listed. (This also covers
        // job_batches via the last-component lookup below.)
        'batches'  => 'batch',
        'watches'  => 'watch',
        // Fix offices => offix!, prices => prix!
        'offices'  => 'office',
        'prices'   => 'price',
        'price'    => 'price',
        // We use the academic option in order to differentiate
        'statuses' => 'status',
        'address'  => 'address',
        // -ves where the stem is f, not ve. The inflector offers all three
        // readings (leaves => [leaf, leave, leaff]) and we take the middle one,
        // which is right for glove/sleeve/valve/curve and wrong for these.
        // A closed list, so listing it is the whole fix.
        'leaves'   => 'leaf',
        'shelves'  => 'shelf',
        'wolves'   => 'wolf',
        'calves'   => 'calf',
        'halves'   => 'half',
        'loaves'   => 'loaf',
        'thieves'  => 'thief',
        'selves'   => 'self',
        'elves'    => 'elf',
        'scarves'  => 'scarf',
        'wharves'  => 'wharf',
        'hooves'   => 'hoof',
        'dwarves'  => 'dwarf',
        'sheaves'  => 'sheaf',
        // NOT 'staves': it is the plural of both stave and staff, and nothing
        // here can tell them apart. Left to the default rather than guessed.
    );

    // The only English nouns whose -ives plural really comes from -ife.
    // Everything else ending -ives is an -ive stem: see IVES_RE below.
    protected static $ifeStems = array('knives', 'lives', 'wives');

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

    protected static $camelCache = [];
    protected static $studlyCache = [];
    protected static $snakeCache = [];

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
                // The inflector offers every reading it cannot choose between,
                // shortest first. We normally want the LAST one, because the
                // ambiguity is nearly always a stem that keeps a trailing e:
                // types => [typ, type], sizes => [siz, size].
                //
                // -shes is the exception, and it is an unconditional one. The
                // es there is the whole suffix, added after the sibilant sh,
                // so the shorter reading is always the right one:
                // finishes => [finish, finishe], washes => [wash, washe].
                // No English noun stem ends in -she, so there is no counter-
                // example to weigh: we can simply take the first.
                //
                // NOT extended to -ches, which is genuinely ambiguous and
                // cannot be settled by the suffix alone -- watches => watch
                // but niches => niche. Those stay a matter for
                // $singularExceptions at the top of the class.
                // -sses and -xes need no help: the inflector already returns a
                // single reading for classes, addresses, boxes.
                $option = (isset($singulars[1]) ? 1 : 0);
                if (substr($lower, -4) === 'shes') $option = 0;
                return static::matchCase($singulars[$option], $value);
            }
        }
        return static::matchCase($singular, $value);
    }

    public static function kebab($value)
    {
        return static::snake($value, '-');
    }

    public static function snake($value, $delimiter = '_')
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    public static function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }
}
