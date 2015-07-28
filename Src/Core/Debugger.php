<?php

namespace PHPUnitAssister\Core;

class Debugger {

	public static function TombStone($date, $newMethod = null)
	{
		$callers = debug_backtrace();
		$caller = $callers[1]['function'];

		echo "Depreciated method used: '{$caller}', on '{$date}'";

        if($newMethod)
        {
            echo " - Use '{$newMethod}' instead";
        }
	}

    public static function pre()
    {
        $arguments = func_get_args();

        foreach($arguments as $argument)
        {
            print_r($argument);
        }
    }

	public static function prex()
    {
        $args = func_get_args();

        foreach($args as $arg)
        {
            self::pre($arg);
        }

        exit();
    }
}