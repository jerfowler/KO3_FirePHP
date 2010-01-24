<?php defined('SYSPATH') OR die('No direct access allowed.');

return array (
	'default' => array(
		'enabled' => TRUE,
		'firephp' => array(
			'maxObjectDepth' => 10,
			'maxArrayDepth' => 20,
			'useNativeJsonEncode' => TRUE,
			'includeLineNumbers' => TRUE
		),
		'database' => array(
			'select' => TRUE,
			'insert' => TRUE,
			'update' => TRUE,
			'rows' => 10,
		),
		'log' => array(
			'file' => array(
				'format' => 'time --- type: body',
				'exclude' => array(
					'FirePHP::LOG',
					'FirePHP::INFO',
					'FirePHP::WARN',
					'FirePHP::ERROR',
					'FirePHP::DUMP',
					'FirePHP::TRACE',
					'FirePHP::TABLE',
					'FirePHP::GROUP_START',
					'FirePHP::GROUP_END'
				)
			),
			'console' => array(
				'format' => 'time --- type: body',
				'exclude' => NULL
			)
		)
	)
);