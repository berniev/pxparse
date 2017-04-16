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

use PX\classes\PXparseDb;
use PX\classes\PXparseSet;
use PX\classes\PXparseVal;
use PX\classes\PXparseX;

include 'loader.php';

$fileName = 'ordinvlx';
$parser = new PXparseDb;
$res = $parser->ParseFile("testfiles/{$fileName}.DB");
if ($res) {
    $parser->Draw();
}
/*
$parser = new PXparseSet;
$res = $parser->ParseFile("testfiles/{$fileName}.SET");
if ($res) {
    $parser->Draw();
}

$parser = new PXparseVal;
$res = $parser->ParseFile("testfiles/{$fileName}.VAL");
if ($res) {
    $parser->Draw();
}
*/
$parser = new PXparseX;
$res = $parser->ParseFile("testfiles/{$fileName}.xg0");
if ($res) {
    $parser->Draw();
}
$parser = new PXparseX;
$res = $parser->ParseFile("testfiles/{$fileName}.xg2");
if ($res) {
    $parser->Draw();
}
$parser = new PXparseX;
$res = $parser->ParseFile("testfiles/clients.xg0");
if ($res) {
    $parser->Draw();
}
