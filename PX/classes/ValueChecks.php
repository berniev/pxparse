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

class ValueChecks
{

    const lookupFills = ['curr-priv', 'all-priv', 'curr-help', 'all-help'];

    /**
     * @var string
     */
    public $posn = '';
    /**
     * @var int
     */
    public $num = 0;
    /**
     * @var string
     */
    public $name = '';
    /**
     * @var string
     */
    public $type = ''; //'Alpha'|'Number'|'Dollar'|'Short'|'Date'
    /**
     * @var int
     */
    public $len = 0;
    /**
     * @var int
     */
    public $hasLookup2 = 0;
    /**
     * @var int
     */
    public $hasPic = 0;
    /**
     * @var int
     */
    public $picLen = 0; // incl null terminator
    /**
     * @var string
     */
    public $pic = '';
    /**
     * @var int
     */
    public $reqd = 0;
    /**
     * @var int
     */
    public $hasLookup = 0;
    /**
     * @var string
     */
    public $lookupTable = '';
    /**
     * @var int
     */
    public $hasLoVal = 0;
    /**
     * @var null
     */
    public $loVal = null;
    /**
     * @var int
     */
    public $hasHiVal = 0;
    /**
     * @var string
     */
    public $hiVal = '';
    /**
     * @var int
     */
    public $hasDef = 0;
    /**
     * @var string
     */
    public $def = '';
    /**
     * @var string
     */
    public $flags = ''; // '00'=Curr-Priv, '01'=All-Pvt, '02'=Curr-Help, '03'=All-Help '08'=auto-fill '04'=auto-picture '10'=auto-lookup
    /**
     * @var string
     */
    public $fill = '';
    /**
     * @var int
     */
    public $autoPic = 0;
    /**
     * @var int
     */
    public $autoFill = 0;
    /**
     * @var int
     */
    public $autoLookup = 0;

    /**
     * @param int    $hasLookup
     * @param string $flags (hex)
     */
    public function SetFlags($hasLookup, $flags)
    {
        $this->flags = $flags;
        $flags = hexdec($flags);
        $this->autoPic = ($flags & 0x04) ? 1 : 0;
        $this->autoFill = ($flags & 0x08) ? 1 : 0;
        $this->autoLookup = ($flags & 0x10) ? 1 : 0;
        $this->fill = $hasLookup ? self::lookupFills[$flags & 0x03] : '';
    }
}
