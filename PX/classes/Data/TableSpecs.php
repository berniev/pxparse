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

namespace PX\classes\Data;

class TableSpecs
{

    /** @var string  */
    public $name = '';

    /** @var int */
    public $recordSize = 0;

    /** @var int */
    public $headerSize = 0;

    /** @var int  */
    public $fileType = 0; // 0=DB-keyed, 1=PX, 2=DB-unkeyed, 3=Xnn-NonIncr, 4=Ynn, 5=Xnn-Incr, 6=XGn-NonIncr, 7=YGn, 8=XGn-Incr

    /** @var int */
    public $isKeyed = 0;

    /** @var int  */
    public $isEncrypted = 0;

    /** @var int */
    public $blockSize = 0;

    /** @var int */
    public $numRecords = 0;

    /** @var int */
    public $numBlocks = 0;

    /** @var int */
    public $fileBlocks = 0;

    /** @var int */
    public $firstBlock = 0;

    /** @var int */
    public $lastBlock = 0;

    /** @var int */
    public $fileVersionId = 0; // 3=3.0, 4=3.5, 9=4.x, 11=5.x, 12=7.x

    /** @var int */
    public $numFields = 0;

    /** @var int */
    public $numKeyFields = 0;

    /** @var int */
    public $firstFreeBlockNum = 0;

    /** @var string */
    public $sortOrder = '';

    /** @var string */
    public $tmpFile = '';

    /** @var int  */
    public $encryption1 = 0;

    /** @var int  */
    public $encryption2 = 0;

    /** @var int  */
    public $writeProtected = 0;

    /** @var string */
    public $valSync = '00';

    /** @var int  */
    public $numAuxPasswords = 0;

}
