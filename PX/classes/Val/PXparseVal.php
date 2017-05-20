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

namespace PX\classes\Val;

use PX\classes\PXparse;

class PXparseVal extends PXparse
{

    /** @var ValueChecks[] */
    public $vals = [];

    /**
     * PXparseVal constructor.
     *
     * @param string $path
     * @param string $tableName
     */
    public function __Construct($path, $tableName)
    {
        $this->tableName = $tableName;
        $this->file = "{$path}{$tableName}.val";
    }

    /**
     * @return array|false
     */
    public function ParseFile()
    {
        if ( ! $this->Open()) {
            return false;
        }

        /* Header */

        // 0x00
        $this->Raw(1);
        // 0x01
        $this->Raw(1); // file type 0x09=VAL?
        // 0x02
        $this->Raw(1);
        // 0x03
        $this->Raw(1);
        // 0x04
        $this->Raw(1);
        // 0x05
        $this->Raw(1);
        // 0x06
        $this->Raw(1);
        // 0x06
        $this->Raw(1);
        // 0x07
        $this->Raw(1); // last field num (not necessarily number of fields)

        // 0x08
        $genInfoStartAddr = $this->ReadPxLe2();

        $this->SetPosn($genInfoStartAddr);

        $tableFieldCount = $this->Dec(1); // number of fields
        $this->Raw(1); // 00h

        $this->Raw(1); // ?
        $this->Raw(1); // 00

        $this->Raw(1); // ?
        $this->Raw(1); // ? not always 00

        $nums = $this->ReadFieldNums($tableFieldCount);

        $specs = $this->ReadFieldSpecs($tableFieldCount);

        $this->ReadTableName();

        $fieldNames = $this->ReadFieldNames($tableFieldCount);

        for ($i = 0; $i < $tableFieldCount; $i++) {
            $specs[$i]['name'] = $fieldNames[$i];
        }
        /* back to start of value checks data */

        $this->SetPosn(0x35);

        $this->vals = [];

        /* one vals block per field that has val checks */
        while ($this->GetPosn() < $genInfoStartAddr - 8) {
            /* one old file had extra bytes, dirty fix - 8 */
            $vals = new ValueChecks;
            $vals->posn = "0x" . dechex($this->GetPosn());

            /* fixed */

            // 0x00
            $vals->num = $this->Dec(1);
            $vals->name = $specs[$vals->num]['name'];
            $vals->type = $specs[$vals->num]['type'];
            $vals->len = $specs[$vals->num]['len'];

            // 0x01
            $vals->picLen = $this->Dec(1);
            // 0x02
            $vals->reqd = (int)$this->Dec(1);
            // 0x03
            $flags = $this->Hex(1);

            // 0x04
            $vals->hasLookup = $this->Hex(4) != '00000000' ? 1 : 0;

            // 0x08
            $vals->hasLookup2 = $this->Hex(4) != '00000000' ? 1 : 0;

            // 0x0c
            $vals->hasLoVal = $this->Hex(4) != '00000000' ? 1 : 0;

            // 0x10
            $vals->hasHiVal = $this->Hex(4) != '00000000' ? 1 : 0;

            // 0x14
            $vals->hasDef = $this->Hex(4) != '00000000' ? 1 : 0;

            // 0x18
            $vals->hasPic = $this->Hex(4) != '00000000' ? 1 : 0;

            /* variable */

            // 0x1c
            $vals->lookupTable = $vals->hasLookup ? $this->ReadNullTermString(80) : '';
            $vals->loVal = $vals->hasLoVal ? $this->GetFieldData($vals->type, $vals->len) : '';
            $vals->hiVal = $vals->hasHiVal ? $this->GetFieldData($vals->type, $vals->len) : '';
            $vals->def = $vals->hasDef ? $this->GetFieldData($vals->type, $vals->len) : '';
            $vals->pic = $vals->hasPic ? $this->ReadNullTermString() : '';

            $vals->SetFlags($flags);

            $this->vals[] = $vals;
        }
        $this->Close();
        return $this->vals;
    }
}
