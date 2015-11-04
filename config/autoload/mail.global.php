<?php
return [
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
			'mail_adapter' => '\Zend\Mail\Transport\Sendmail',

			/*
			 * An alias for the 'mail_adapter' option. Use just one or another.
			 */
			//'transport' => '\Zend\Mail\Transport\Sendmail',


			/************************
			 * MESSAGE CONFIGURATION *
			 ************************/

			'message_options' => [
				/*
				 * From email address of the email.
				 *
				 * Default value is an empty string
				 */
				'from' => 'no-reply@oraproject.org',

				/*
				 * From name to be displayed instead of from address.
				 *
				 * Default value is an empty string
				 */
				'from_name' => 'O.R.A.',

				/*
				 * Destination addresses of sent emails.
				 * It can be an email address as string or an array of email addresses.
				 *
				 * Default value is an empty array.
				 */
				//'to' => [],

				/*
				 * Copy destination addresses of sent emails.
				 * It can be an email address as string or an array of email addresses.
				 *
				 * Default value is an empty array
				 */
				//'cc' => [],

				/*
				 * Hidden copy destination addresses of sent emails.
				 * It can be an email address as string or an array of email addresses.
				 *
				 * Default value is an empty array
				 */
				//'bcc' => [],

				/*
				 * Email subject.
				 *
				 * Default value is an empty string
				 */
				//'subject' => '',

				/*
				 * Email body.
				 * It can be a plain-text string, a HTML string or a template that will be computed at runtime.
				 * The 'content' property is used by default.
				 * To force a template configuration to be used, set 'use_template' to true.
				 *
				 * Default content is an empty string.
				 */
				'body' => [
					//'content' => '',
					'charset' => 'utf-8',
					'use_template' => true,

					/*
					 * Defines information to create the email body from a view partial.
					 * It defines template path and template params.
					 * The path will be resolved by a view resolver, so you need to place mail templates inside a view
					 * folder of one of your modules or customize your template map and template path stack.
					 * Params will be a group of key-value pairs.
					 *
					 * The 'children' property allows to define children for the template, in case you want to use
					 * layouts.
					 * You can define any number of children. The key in the array will be used as the 'capture_to'
					 * property when rendering the template.
					 * If you set the key 'content' to the child, you should have something like echo $this->content in
					 * you layout.
					 *
					 * Any child can have its own children, so you can nest views into other views recursively.
					 *
					 * By default no children are used
					 */
					'template' => [
						//    'path'          => 'ac-mailer/mail-templates/layout',
						//    'params'        => [],
						//    'children'      => [
						//        'content'   => [
						//            'path'   => 'ac-mailer/mail-templates/mail',
						//            'params' => [],
						//        ]
						//    ],
						'default_layout' => [
							'path' => 'mail/layout.phtml',
							'params' => [],
							'template_capture_to' => 'content'
						]
					],
				],

				/*
				 * Attachments config.
				 * Allows to define an array of files that will be attached to the message,
				 * or even a directory that will be iterated to attach all found files.
				 * Set directory will only be iterated if 'iterate' property is true and 'path' is a valid
				 * directory.
				 * If 'recursive' is true all nested directories will be iterated too.
				 * If both files and dir are set, all files will be merged without duplication.
				 *
				 * By default the files array is empty and the directory won't be iterated
				 */
				//'attachments' => [
				//    'files' => [],
				//    'dir' => [
				//        'iterate'   => false,
				//        'path'      => 'data/mail/attachments',
				//        'recursive' => false,
				//    ],
				//],
			],


			/**********************
			 * SMTP CONFIGURATION *
			 **********************/

			/*
			 * SMTP concrete options that will be used only when the adapter is a Zend\Mail\Transport\Smtp.
			 * This will be ignored otherwise.
			 */
			//'smtp_options' => [
				/*
				 * Hostname or IP address of the mail server.
				 *
				 * Default value is localhost
				 */
				//'host' => 'localhost',

				/*
				 * Port of the mail server.
				 *
				 * Default value is 25
				 */
				//'port' => 25,

				/*
				 * The connection class used for authentication.
				 * The value can be one of 'smtp', 'plain', 'login' or 'crammd5'.
				 *
				 * Default value is 'smtp'.
				 */
				//'connection_class' => 'smtp',

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
			//],


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