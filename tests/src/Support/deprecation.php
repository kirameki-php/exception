<?php declare(strict_types=1);

require 'setup.php';

trigger_error('test deprecation', E_USER_DEPRECATED);
