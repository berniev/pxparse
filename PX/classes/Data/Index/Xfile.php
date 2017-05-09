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

namespace PX\classes\Data\Index;

class Xfile
{

    public $recordSize = 0;
    public $headerBlockLength = 0;
    public $fieldType = 0; // 8= Sec Index file
    public $dataBlockSize = 0; // in K
    public $numRecords = 0;
    public $blocksInUse = 0;
    public $totalBlocks = 0;
    public $firstDataBlock = 0; // always 1
    public $lastBlock = 0;
    public $numFields = 0;
    public $numKeyFields = 0;
    public $firstFreeBlock = 0;
}