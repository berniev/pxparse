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

use PX\classes\PXparse;

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
        $this->table->recordSize = $this->ReadPxLe2();
        // 0x02 (2)
        $this->table->headerSize = $this->ReadPxLe2();
        // 0x04 (4)
        $this->table->fileType = $this->Hex(1); // 0x00 = keyed, 0x02 = unkeyed
        // 0x05 (5)
        $this->table->blockSize = $this->Dec(1);
        // 0x06 (6)
        $this->table->numRecords = $this->ReadPxLe4();
        // 0x0a (10)
        $this->table->numBlocks = $this->ReadPxLe2();
        // 0x0c (12)
        $this->table->fileBlocks = $this->ReadPxLe2();
        // 0x0e (14)
        $this->table->firstBlock = $this->ReadPxLe2(); // always 1
        // 0x10 (16)
        $this->table->lastBlock = $this->ReadPxLe2();
        // 0x12 (18)
        $this->Skip(2); // unknown
        // 0x14 (20)
        $this->Skip(1); // modifiedFlags1
        // 0x15 (21)
        $this->Skip(1); // indexFieldNumber
        // 0x16 (22)
        $this->Skip(4); // ptr primaryIndexWorkspace
        // 0x1a (26)
        $this->Skip(4); // unknownPtr1A
        // 0x1e (30)
        $this->Skip(2); // indexRoot
        // 0x20 (32)
        $this->Skip(1); // numIndexLevels
        // 0x21 (33)
        $this->table->numFields = $this->tableFieldCount = $this->Dec(1);
        // 0x22 (34)
        $this->Skip(1); // 0x00
        // 0x23 (35)
        $this->table->numKeyFields = $this->ReadPxLe2();
        // 0x25 (37)
        $this->table->encryption1 = $this->ReadPxLe4();
        // 0x29 (41)
        $this->Skip(1); // sortOrder
        // 0x2a (42)
        $this->Skip(1); // modifiedFlags2
        // 0x2b (43)
        $this->Skip(2); // unknown2Bx2C
        // 0x2d (45)
        $this->Skip(1); // changeCount1
        // 0x2e (46)
        $this->table->valSync = $this->Hex(1); // valSync
        // 0x2f (47)
        $this->Skip(1); // unknown2F
        // 0x30 (48)
        $this->Skip(4); // tableNamePtrPtr
        // 0x34 (52)
        $this->Skip(4); // fldInfoPtr
        // 0x38 (56)
        $this->table->writeProtected = $this->Hex(1) !== '00'?1:0; // writeProtected
        // 0x39 (57)
        $this->Skip(1); // fileVersionID
        // 0x3a (58)
        $this->Skip(2); // maxBlocks
        // 0x3c (60)
        $this->Skip(1); // unknown3C
        // 0x3d (61)
        $this->table->numAuxPasswords = $this->Dec(1); // auxPasswords
        // 0x3e (62)
        $this->Skip(2); // unknown3Ex3F
        // 0x40 (64)
        $this->table->isEncrypted = $this->Hex(4) != '00000000' ? 1 : 0; // cryptInfoStartPtr
        // 0x44 (68)
        $this->Skip(4); // cryptInfoEndPtr
        // 0x48 (72)
        $this->Skip(1); // unknown48
        // 0x49 (73)
        $this->Skip(4); // autoInc
        // 0x4d (77)
        $this->table->firstFreeBlockNum = $this->ReadPxLe2();
        // 0x4f (79)
        $this->Skip(1); // indexUpdateRequired
        // 0x50 (80)
        $this->Skip(1); // unknown50
        // 0x51 (81)
        $this->Skip(2); // realHeaderSize
        // 0x53 (83)
        $this->Skip(2); // unknown53x54
        // 0x55 (85)
        $this->Skip(1); // refIntegrity
        // 0x56 (86)
        $this->Skip(2); // unknown56x57
        // 0x58 (88)
        $this->Skip(2); // fileVerID3
        // 0x5a (90)
        $this->Skip(2); // fileVerID4
        // 0x5c (92)
        $this->table->encryption2 = $this->ReadPxLe4();
        // 0x60 (96)
        $this->Skip(4); // fileUpdateTime
        // 0x64 (100)
        $this->Skip(2); // hiFieldID
        // 0x66 (102)
        $this->Skip(2); // hiFieldIDinfo
        // 0x68 (104)
        $this->Skip(2); // sometimesNumFields
        // 0x6a (106)
        $this->Skip(2); // dosCodePage
        // 0x6c (108)
        $this->Skip(4); // unknown6Cx6F
        // 0x70 (112)
        $this->Skip(2); // changeCount4
        // 0x72 (114)
        $this->Skip(6); // unknown72x77

        /* variable */

        // 0x78 (120)
        $specs = $this->ReadFieldSpecs($this->table->numFields); // numFields * 2

        $this->Skip(4); // 4

        $this->Skip(4 * $this->table->numFields); // numFields * 4

        $this->table->tmpFile = $this->ReadTableName(); // 79
        $names = $this->ReadFieldNames($this->table->numFields); // variable
        $nums = $this->ReadFieldNums($this->table->numFields); // numFields * 2

        $this->table->sortOrder = $this->ReadNullTermString(); // variable
        return [$specs, $names, $nums];
    }

}