<?php
/**
 * Copyright (c) 2011-present, Kogao Software, Inc. All rights reserved.
 * <www.kogaoscript.com>
 */
require('config.php');
require('controller/sass.controller.php');

$compile = new scssc();
$compile->setFormatter(scss_formatter);

$server = new scss_server(scss_folder, null, $compile);
$server->serve();