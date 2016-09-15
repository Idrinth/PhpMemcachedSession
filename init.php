<?php
ini_set('session.serialize_handler','php_serialize');//more useful - could be used outside php
ini_set('session.name','session');//not obviously php
ini_set('idrinth_session.memcache_server','localhost');
ini_set('idrinth_session.memcache_port',11111);
ini_set('idrinth_session.sid_pepper','this is not quite secret');
session_set_save_handler(new Idrinth\PhpMemcachedSession\Controller\Session());