<?php

namespace AppBundle\Managers;

class ProfilingTools {
	/* Things you may want to tweak in here:
	 *  - xhprof_enable() uses a few constants.
	 *  - The values passed to rand() determine the the odds of any particular run being profiled.
	 *  - The MongoDB collection and such.
	 *
	 * I use unsafe writes by default, let's not slow down requests any more than I need to. As a result you will
	 * indubidubly want to ensure that writes are actually working.
	 *
	 * The easiest way to get going is to either include this file in your index.php script, or use php.ini's
	 * auto_prepend_file directive http://php.net/manual/en/ini.core.php#ini.auto-prepend-file
	 */


	/* xhprof_enable()
	 * See: http://php.net/manual/en/xhprof.constants.php
	 *
	 *
	 * XHPROF_FLAGS_NO_BUILTINS
	 *  Omit built in functions from return
	 *  This can be useful to simplify the output, but there's some value in seeing that you've called strpos() 2000 times
	 *
	 * XHPROF_FLAGS_CPU
	 *  Include CPU profiling information in output
	 *
	 * XHPROF_FLAGS_MEMORY (integer)
	 *  Include Memory profiling information in output
	 *
	 *
	 * Use bitwise operators to combine, so XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY to profile CPU and Memory
	 *
	 */
	public static function activateXhprof($flags = null)
	{
		$flags = $flags ?: XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY;
		xhprof_enable($flags);
	}

	/**
	 * @return null|string
	 */
	public static function storeXhprof()
	{
		$xhprof_data = xhprof_disable();

		include_once(__DIR__ . "/../xhprof_lib/utils/xhprof_lib.php");
		include_once(__DIR__ . "/../xhprof_lib/utils/xhprof_runs.php");

		$xhprof_runs = new \XHProfRuns_Default();
		return $xhprof_runs->save_run($xhprof_data, "dev");
	}
}
use MongoDB\Driver\Manager;
