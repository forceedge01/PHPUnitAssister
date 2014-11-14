<?php

namespace PHPUnitAssister\Src\Core;


class Debugger {

	public static function TombStone($date)
	{
		$callers = debug_backtrace();
		$caller = $callers[1]['function'];

		echo "Depreciated method used: '{$caller}', on '{$date}'";
	}

	public static function prex()
    {
        $arguments = func_get_args();

        foreach($arguments as $argument)
        {
            print_r($argument);
        }

        exit();
    }
}