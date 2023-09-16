<?php

use function Disabler\plugin;

require_once( plugin()->logger_dir . 'abstracts/class-plugin-log-levels.php' );
require_once( plugin()->logger_dir . 'interfaces/class-plugin-logger-interface.php' );
require_once( plugin()->logger_dir . 'interfaces/class-plugin-log-handler-interface.php' );
require_once( plugin()->logger_dir . 'abstracts/abstract-plugin-log-handler.php' );
require_once( plugin()->logger_dir . 'class-plugin-log-handler-file.php' );
require_once( plugin()->logger_dir . 'class-plugin-logger.php' );

require_once( plugin()->logger_dir . 'functions-logger.php' );