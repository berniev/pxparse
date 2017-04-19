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

abstract class PXparseDataFile extends PXparse
{
    /** @var TableSpecs */
    public $table = null;
    /**
     * @return array
     */
    protected function ParseDataFileHeader()
    {
        $this->table = new TableSpecs;

        /* fixed */

        // 0x00 (0)
        $this->table->recordSize = $this->ReadPxLittleEndian2();
        // 0x02 (2)
        $this->table->headerSize = $this->ReadPxLittleEndian2();
        // 0x04 (4)
        $this->table->fileType = $this->Hex(1);
        // 0x05 (5)
        $this->table->blockSize = $this->Dec(1);
        // 0x06 (6)
        $this->table->numRecords = $this->ReadPxLittleEndian4();
        // 0x0a (10)
        $this->table->numBlocks = $this->ReadPxLittleEndian2();
        // 0x0c (12)
        $this->table->fileBlocks = $this->ReadPxLittleEndian2();
        // 0x0e (14)
        $this->table->firstBlock = $this->ReadPxLittleEndian2(); // always 1
        // 0x10 (16)
        $this->table->lastBlock = $this->ReadPxLittleEndian2();

        // 0x12 (18)
        $this->Raw(15);

        // 0x21 (33)
        $this->table->numFields = $this->tableFieldCount = $this->Dec(1);

        // 0x22 (34)
        $this->Raw(1);

        // 0x4d (77)
        $this->table->numKeyFields = $this->Dec(1);

        // 0x24 (36)
        $this->Hex(41);

        // 0x4d (77)
        $this->table->firstFreeBlockNum = $this->ReadPxLittleEndian2();

        // 0x4f (79)
        $this->Raw(41);

        /* variable */

        // 0x78 (120)
        $specs = $this->ReadFieldSpecs(); // numFields * 2

        $this->Raw(4); // 4

        $this->Raw(4 * $this->table->numFields); // numFields * 2

        $this->table->tmpFile = $this->ReadTableName(); // 79
        $names = $this->ReadFieldNames(); // variable
        $nums = $this->ReadFieldNums(); // numFields * 2

        $this->table->sortOrder = $this->ReadNullTermString(); // variable
        return [$specs, $names, $nums];
    }

}