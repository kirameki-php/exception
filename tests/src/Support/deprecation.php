<?php declare(strict_types=1);

require 'setup.php';

trigger_error('test deprecation', E_USER_DEPRECATED);

file_put_contents('/tmp/deprecation.txt', '1');
