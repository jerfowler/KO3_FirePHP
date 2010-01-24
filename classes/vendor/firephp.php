<?php
/**
 *  KO3 FirePHP and Profiler
 *  Version 0.1
 *  Last changed: 2010-01-23
 *  Based on:
 *  Fire_Profiler by dlib: http://learn.kohanaphp.com/2008/07/21/introducing-fire-profiler/
 *  KO3 conversion by ralf: http://code.goldenzazu.de/fireprofiler.php
 */

// Grab the vendor api library
require Kohana::find_file('vendor', 'FirePHP/FirePHP', $ext = 'class.php');

/**
 *  Main Class
 */
class Vendor_FirePHP extends FirePHP {
    protected $_config = array();

    public function  __construct(array $config=Null)
    {
	parent::__construct();
	$this->_config = Kohana::config('firephp.default');
	if (isset($config)) {
	    $this->_config = Arr::merge($this->_config, $config);
	}
	$this->enabled = Arr::get($this->_config, 'enabled', FALSE);
	$this->setOptions(Arr::get($this->_config, 'firephp', $this->getOptions()));
    }

    final private function  __clone() {
	// Shouldn't need more than one instance of this...
    }

    public function get_config($key, $default = NULL)
    {
	return Arr::path($this->_config, $key, $default);
    }

    public function set_config($key, $value=NULL)
    {
	if (empty($key)) return FALSE;
	if (is_array($key)) {
	    $value = $key;
	} else {
	    if ($this->get_config($key) === $value) return FALSE;
	    // Convert dot-noted key string to an array
	    $keys = explode('.', rtrim($key, '.'));
	    // This will set a value even if it didn't previously exist
	    do {
		$key = array_pop($keys);
		$value = array($key => $value);
	    } while ($keys);
	}
	$this->_config = Arr::merge($this->_config, $value);
	$this->enabled = Arr::get($this->_config, 'enabled', FALSE);
	$this->setOptions(Arr::get($this->_config, 'firephp', $this->getOptions()));
	return TRUE;
    }
    
    // Disable error and exception handling... KO3 does it for us...
    public function registerErrorHandler($throwErrorExceptions=true) {}
    public function registerExceptionHandler() {}
    // Disable assetion handler until I can verify it doesn't break anything...
    public function registerAssertionHandler($convertAssertionErrorsToExceptions=true, $throwAssertionExceptions=false) {}

    public function session()
    {
	return (isset($_SESSION)) ? $this->tabledata($_SESSION, 'Session') : $this;
    }

    public function cookie()
    {
	return $this->tabledata($_COOKIE, 'Cookie');
    }

    public function post()
    {
	return $this->tabledata($_POST, 'Post');
    }

    public function get()
    {
	return $this->tabledata($_GET, 'Get');
    }

    /**
     * Benchmark times and memory usage from the Benchmark library.
     */
    public function database()
    {
	if ($this->enabled) {
	    $this->benchmark('Database');
	}
	return $this;
    }

    /**
     * @param result  object   Database_Result for SELECT, INSERT, UPDATE queries
     */
    public function query($result, $type, $sql)
    {
	if ($this->enabled)
	{
	    $max = $this->get_config('database.rows', 10);
	    if ($type === Database::SELECT)
	    {
		if ($this->get_config('database.select', FALSE))
		{
		    if ($result->count() > 0)
		    {
			$this->tabledata(array_slice($result->as_array(), 0, $max), $sql);
		    }
		    else
			$this->log($sql, $result->count().' '.__('rows'));
		}
	    }
	    elseif ($type === Database::INSERT)
	    {
		if ($this->get_config('database.insert', FALSE))
		{
		    if ($result->count() > 0)
		    {
			$this->tabledata(array_slice($result->as_array(), 0, $max), $sql);
		    }
		    else
			$this->log($sql, $result->count().' '.__('rows'));
		}
	    }
	    elseif ($type === Database::UPDATE) 
	    {
		if ($this->get_config('database.update', FALSE))
		{
		    if ($result->count() > 0)
		    {
			$this->tabledata(array_slice($result->as_array(), 0, $max), $sql);
		    }
		    else
			$this->log($sql, $result->count().' '.__('rows'));
		}
	    }
	    else
		$this->log($sql);
	}
    }

    public function benchmark($table = false)
    {
	if ($this->enabled)
	{
	    foreach (Profiler::groups() as $group => $benchmarks)
	    {
		$tablename = __(ucfirst($group));
		if (empty($table) || strpos($tablename,$table) === 0)
		{
		    $row = array( array(__('Benchmark'),__('Min'),__('Max'), __('Average'),__('Total')) );
		    foreach ($benchmarks as $name => $tokens)
		    {
			$stats = Profiler::stats($tokens);
			$cell = array( $name.' (' . count($tokens).')' );
			foreach (array('min', 'max', 'average', 'total') as $key)
			{
			    $cell[] =  ' ' . number_format($stats[$key]['time'], 6). ' '. __('seconds') ;
			}
			$row[] = $cell;
		    }
		    $cell = array('');
		    foreach (array('min', 'max', 'average', 'total') as $key)
		    {
			$cell[] = ' ' . number_format($stats[$key]['memory'] / 1024, 4) . ' kb';
		    }
		    $row[] = $cell;
		    $this->fb(array($tablename, $row ),FirePHP::TABLE);
		}
	    }

	    if (empty($table) || strpos('Application',$table) === 0)
	    {
		$stats = Profiler::application();
		$tablename = array(__('Application Execution').' ('.$stats['count'].')');
		$row = array(array('','min', 'max', 'average', 'current'));
		$cell = array('Time');
		foreach (array('min', 'max', 'average', 'current') as $key)
		{
		    $cell[] = number_format($stats[$key]['time'], 6) . ' ' .  __('seconds');
		}
		$row[] = $cell;
		$cell = array('Memory');
		foreach (array('min', 'max', 'average', 'current') as $key)
		{
		    $cell[] = number_format($stats[$key]['memory'] / 1024, 4) . ' kb';
		}
		$row[] = $cell;
		$this->fb(array($tablename, $row ),FirePHP::TABLE);
	    }
	}
	return $this;
    }

    public function tabledata($data, $name='') {
	if ($this->enabled && !empty($data)) {
	    $table = array();
	    $table[] = array(__('Key'), __('Value'));
	    foreach($data as $key => $value) {
		if (is_object($value))
		    $value = get_class($value).' [object]';
		$table[] = array($key, $value);
	    }
	    $this->fb(array($name.': ('.count($data).') ',  $table  ),FirePHP::TABLE);
	}
	return $this;
    }

    public static function instance($_config=Null) {
	if( !self::$instance) {
	    self::$instance = new self($_config);
	} else {
	    self::$instance->set_config($_config);
	}
	return self::$instance;
    }


}
