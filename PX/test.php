<?php
/**
 * Copyright 2017 Bernie van't Hof
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Sample test file
 */

namespace PX;

use PX\classes\ParseTables;

include 'loader.php';

$dB = 'testparse';
$tableNames = [];
$path = './testfiles/';
//$path = '/Users/bernievanthof/archives/dos_19_apr_2016/DRIVE_C/PDOXDATA/';
$parser = new ParseTables($tableNames, $path);
$parser->setDestDb($dB);
$parser->Parse();