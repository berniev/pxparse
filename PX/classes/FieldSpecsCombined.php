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

namespace PX\classes;

class FieldSpecsCombined
{

    /* from DB */

    /** @var string */
    public $name = '';

    /** @var int */
    public $type = 0;

    /** @var int */
    public $len = 0;

    /** @var int */
    public $isKey = 0;

    /** @var int */
    public $num = 0;

    /* from VAL */

    /** @var int */
    public $required = 0;

    /** @var string */
    public $default = '';

    /** @var string */
    public $picture = '';

    /** @var string */
    public $lookupTable = '';

    /** @var string */
    public $loVal = '';

    /** @var string */
    public $hiVal = '';

    /** @var string */
    public $fillType = '';

    /** @var int */
    public $autoFill = 0;

    /** @var int */
    public $autoLookup = 0;

    /** @var int */
    public $autoPic = 0;

    /* from SET */

    /** @var string */
    public $dunno1 = '';

    /** @var int */
    public $defDispLen = 0;  // one of these is table, the other table display?

    /** @var int */
    public $useDispLen = 0;  // one of these is table, the other table display?

    /** @var int */
    public $dunno2 = 0;

    /** @var int */
    public $decPlaces = 0;

}