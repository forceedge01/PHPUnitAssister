<?php

namespace PHPUnitAssister;

class Loader {

    const DISPLAY_LOADED_FILES = true;

    public static function LoadFiles($folder)
    {
        $files = [
            'AssertionAssister',
            'Mocker',
            'TestObjectHandler'
        ];

        foreach($files as $filename)
        {
            require_once __DIR__ . '/Src/Core/' . $filename . '.Class.php';
        }
    }

    public static function LoadExtendedFileByClass($class)
    {
        require_once __DIR__ . '/Src/Extended/' . $class . '.Class.php';
    }
}

Loader::LoadFiles(__DIR__ . '/Src');