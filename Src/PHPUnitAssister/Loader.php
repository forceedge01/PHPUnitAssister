<?php

namespace PHPUnitAssister;

class Loader {

    const DISPLAY_LOADED_FILES = true;

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
        require_once __DIR__ . '/Extensions/' . $class . '.php';
    }

    public static function load()
    {
        foreach(self::getFiles() as $file) {
            require_once __DIR__ . '/Core/' . $file . '.php';
        }
    }
}

Loader::load();