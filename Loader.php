<?php

namespace PHPUnitAssister;

class Loader {

    const DISPLAY_LOADED_FILES = true;
    const BOOSTRAP_FILE = 'bootstrap.cache.php';

    public static function getBoostrapPath()
    {
        return __DIR__ . '/' . self::BOOSTRAP_FILE;
    }

    public static function getFiles()
    {
        $files = [
            'Debugger',
            'AssertionAssister',
            'Mocker',
            'TestObjectHandler'
        ];

        return $files;
    }

    public static function LoadExtendedFileByClass($class)
    {
        require_once __DIR__ . '/Src/Extensions/' . $class . '.Class.php';
    }

    public static function createBoostrap($folder)
    {
        $files = self::getFiles();

        ob_start();
        foreach($files as $file)
        {
            echo php_strip_whitespace($folder . '/Core/' . $file . '.Class.php');
        }

        $contents = self::sanitizeContentsForOnePHPFile(ob_get_clean());

        file_put_contents(self::getBoostrapPath(), $contents);
    }

    public static function sanitizeContentsForOnePHPFile($contents)
    {
        $removedMultiplePHPSymbols = str_replace(['<?php', '<?'], '', $contents);

        return '<?php ' . $removedMultiplePHPSymbols;
    }

    public static function LoadBootstrap()
    {
        if(! file_exists(self::getBoostrapPath()))
        {
            self::createBoostrap(__DIR__ . '/Src');
        }

        require_once self::getBoostrapPath();
    }
}

Loader::LoadBootstrap();