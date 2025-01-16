<?php declare(strict_types=1);

require 'setup.php';

trigger_error('test error');

file_put_contents('/tmp/error.txt', '1');
