<?php

namespace Synapse;

class I18n
{
    private static string $locale         = 'en';
    private static array $strings         = [];
    private static array $registeredPaths = [];

    /**
     *
     * Set the active locale and load files related to that locale
     *
     * @param string $locale
     *
     */
    public static function setLocale(string $locale): void
    {
        static::$locale = $locale;
        static::load();
    }

    /**
     *
     * Get the current locale
     *
     * @return string
     *
     */
    public static function getLocale(): string
    {
        return static::$locale;
    }

    /**
     *
     * Register a locale path
     *
     * @param string $path
     * @param string $namespace
     *
     */
    public static function registerPath(string $path, string $namespace): void
    {
        self::$registeredPaths[] = ['path' => $path, 'ns' => $namespace];
    }

    /**
     *
     * Translate a message if possible
     *
     * @param string $message
     * @param array $replacements
     * @return string
     *
     */
    public static function translate(string $message, array $replacements = []): string
    {
        $path       = explode('.', $message);
        $stringItem = self::$strings;

        foreach ($path as $num => $item) {
            if (!empty($stringItem[$item])) {
                if (count($path) !== $num + 1) {
                    $stringItem = $stringItem[$item];
                } else {
                    if ($replacements) {
                        return sprintf($stringItem[$item], ...$replacements);
                    }

                    return $stringItem[$item];
                }
            }
        }

        return $message;
    }

    /**
     *
     * Alias of translate method
     *
     * @param string $message
     * @param array $replacements
     * @return string
     *
     */
    public static function t(string $message, array $replacements = []): string
    {
        return static::translate($message, $replacements);
    }

    /**
     *
     * Load all files for the current locale
     *
     */
    private static function load()
    {
        $locale = self::$locale ?? 'en';

        foreach (self::$registeredPaths as $path) {
            include_once "{$path['path']}/{$locale}.php";
            static::$strings[$path['ns']] = $strings;
        }
    }
}