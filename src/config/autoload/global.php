<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
	'doctrine' => array(
		'connection' => array(
			'orm_default' => array(
				'params' => array(
					'host'			=> getenv('DB_HOSTNAME'),
					'port'			=> getenv('DB_PORT'),
					'user'			=> getenv('DB_USERNAME'),
					'password'		=> getenv('DB_PASSWORD'),
					'dbname'		=> getenv('DB_NAME'),
					'driverOptions' => array(
						\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
					),
				)
			)
		)
	),
	'zendoauth2' => array(
		'google' => array(
			'client_id'     => getenv('GOOGLE_CLIENT_ID'),
			'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
			'redirect_uri'  => getenv('GOOGLE_REDIRECT_URI'),
		),
		'linkedin' => array(
			'client_id'     => getenv('LINKEDIN_CLIENT_ID'),
			'client_secret' => getenv('LINKEDIN_CLIENT_SECRET'),
			'redirect_uri'  => getenv('LINKEDIN_REDIRECT_URI'),
		)
	),
	'kanbanize' => array(
		'apikey'			=> getenv('KANBANIZE_APIKEY'),
		'url'				=> getenv('KANBANIZE_URL'),
	)
);