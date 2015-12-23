<?php
return [
	'mail_domain' => 'http://example.com/#',
	'acmailer_options' => [

		// Default mail service
		'default' => [
			/*
			 * Tells which other mail service configuration to extend.
			 * The default service is usually the one that doesn't extend any other configuration, but others extend
			 * from it.
			 * By default, extends value is null.
			 */
			//'extends' => null,

			/***********
			 * ADAPTER *
			 ***********/

			/*
			 * The mail adapter to be used.
			 * You can define any class implementing Zend\Mail\Transport\TransportInterface,
			 * either the fully qualified class name as string or the instance to be used.
			 *
			 * For standard mail transports, you can use these aliases:
			 *      - sendmail  => Zend\Mail\Transport\Sendmail
			 *      - smtp      => Zend\Mail\Transport\Smtp
			 *      - file      => Zend\Mail\Transport\File
			 *      - null      => Zend\Mail\Transport\Null (<=ZF2.3) or Zend\Mail\Transport\InMemory (>=ZF2.4)
			 *      - in_memory => Zend\Mail\Transport\Null (<=ZF2.3) or Zend\Mail\Transport\InMemory (>=ZF2.4)
			 *
			 * If for some reason you need to use a custom adapter which is complex to be created, you can also define
			 * the name of a service that returns a Zend\Mail\Transport\TransportInterface instance. When the
			 * MailService is created, it will automatically check if this is an existing service.
			 *
			 * Default value is \Zend\Mail\Transport\Sendmail
			 */
			'mail_adapter' => '\Zend\Mail\Transport\Smtp',

			/*
			 * An alias for the 'mail_adapter' option. Use just one or another.
			 */
			//'transport' => '\Zend\Mail\Transport\Sendmail',

			/**********************
			 * SMTP CONFIGURATION *
			 **********************/

			/*
			 * SMTP concrete options that will be used only when the adapter is a Zend\Mail\Transport\Smtp.
			 * This will be ignored otherwise.
			 */
			'smtp_options' => [
				/*
				 * Hostname or IP address of the mail server.
				 *
				 * Default value is localhost
				 */
				'host' => 'localhost',

				/*
				 * Port of the mail server.
				 *
				 * Default value is 25
				 */
				'port' => 1025,

				/*
				 * The connection class used for authentication.
				 * The value can be one of 'smtp', 'plain', 'login' or 'crammd5'.
				 *
				 * Default value is 'smtp'.
				 */
				'connection_class' => 'smtp',

				//'connection_config' => [
				/*
				 * The SMTP authentication identity.
				 * If this is not set, the 'from' option of the message will be used.
				 *
				 * Default value is an empty string
				 */
				//'username' => '',

				/*
				 * The SMTP authentication credential.
				 *
				 * Default value is an empty string
				 */
				//'password' => '',

				/*
				 * This defines the encryption type to be used, 'ssl' or 'tls'.
				 * Boolean false should be used to disable SSL.
				 *
				 * Default value is false
				 */
				//'ssl' => false,
				//],
			],


			/**********************
			 * FILE CONFIGURATION *
			 **********************/

			/*
			 * File concrete options that will be used only when the adapter is a Zend\Mail\Transport\File.
			 * This will be ignored otherwise.
			 */
			'file_options' => [
				/*
				 * This is the folder where the file is going to be saved
				 *
				 * Default value is 'data/mail/output'
				 */
				//'path' => 'data/mail/output',

				/**
				 * A callable that will get the Zend\Mail\Transport\File object as an argument and should return the
				 * filename.
				 *
				 * Default value is null, in which case an empty callable will be used.
				 */
				//'callback' => null,
			],

			/*
			 * A list of mail listeners that will be attached to the mail service when created.
			 * Each element can be either a string or a AcMailer\Event\MailListenerInterface instance.
			 * Each string will be checked as a service first. If a service is not found, it will be checked as a fully
			 * qualified class name and lazily created in that case.
			 * Anything else will throw a AcMailer\Exception\InvalidArgumentException.
			 *
			 * By default, the list of listeners is empty
			 */
			//'mail_listeners' => []
		]

		/*
		 * You can define other service configurations here, with the same structure as in the 'default' block
		 */

	]
];