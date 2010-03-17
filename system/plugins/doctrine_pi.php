<?php
// system/application/plugins/doctrine_pi.php

// load Doctrine library
require_once BASEPATH.'/plugins/doctrine/lib/Doctrine.php';

// load database configuration from CodeIgniter
require_once APPPATH.'/config/database.php';

// this will allow Doctrine to load Model classes automatically
spl_autoload_register(array('Doctrine', 'autoload'));
spl_autoload_register(array('Doctrine', 'modelsAutoload'));

// we load our database connections into Doctrine_Manager
// this loop allows us to use multiple connections later on
$db[$active_group]['dsn'] = $db[$active_group]['dbdriver'] .
                        '://' . $db[$active_group]['username'] .
                        ':' . $db[$active_group]['password'].
                        '@' . $db[$active_group]['hostname'] .
                        '/' . $db[$active_group]['database'];
// Load the Doctrine connection
Doctrine_Manager::connection($db[$active_group]['dsn'], $db[$active_group]['database']);

// CodeIgniter's Model class needs to be loaded
require_once BASEPATH.'/libraries/Model.php';


// (OPTIONAL) CONFIGURATION BELOW

// this will allow us to use "mutators"
Doctrine_Manager::getInstance()->setAttribute(
	Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);

// this sets all table columns to notnull and unsigned (for ints) by default
Doctrine_Manager::getInstance()->setAttribute(
	Doctrine::ATTR_DEFAULT_COLUMN_OPTIONS,
	array('notnull' => true, 'unsigned' => true));

// set the default primary key to be named 'id', integer, 4 bytes
Doctrine_Manager::getInstance()->setAttribute(
	Doctrine::ATTR_DEFAULT_IDENTIFIER_OPTIONS,
	array('name' => 'id', 'type' => 'integer', 'length' => 4));

// conservatively load models
Doctrine_Manager::getInstance()->setAttribute(Doctrine::ATTR_MODEL_LOADING,
	 Doctrine::MODEL_LOADING_CONSERVATIVE);
	 
// telling Doctrine where our models are located
Doctrine::loadModels(APPPATH.'/models');
