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

class PXparseVal extends PXparse
{

    /** @var ValueChecks[] */
    public $vals = [];

    /**
     * @param string $fName
     *
     * @return array|false
     */
    public function ParseFile($fName)
    {
        if ( ! $this->Open($fName)) {
            return false;
        }

        /**
         * If first three bytes is 0xef-0xdf-oxbd (unicode substution string) then
         * file has been saved as unicode and is unusable
         */

        /* Header */

        $this->Raw(1); // 0
        $this->Raw(1); // 1 always (?) 09h separator
        $this->Raw(1); // 2
        $this->Raw(1); // 3

        $this->Raw(1); // 4
        $this->Raw(1); // 5
        $this->Raw(1); // 6

        $this->Raw(1); // 7 last field num (not necessarily number of fields)
        $this->Raw(1); // 8 00h

        $genInfoStartAddr = $this->ReadPxLittleEndian2();

        $this->Raw($genInfoStartAddr - 11); // just skip vals data for now

        $this->tableFieldCount = $this->Dec(1); // number of fields
        $this->Raw(1); // 00h

        $this->Raw(1); // ?
        $this->Raw(1); // 00

        $this->Raw(1); // ?
        $this->Raw(1); // ? not always 00

        $this->ReadFieldNums();

        $specs = $this->ReadFieldSpecs();

        $this->ReadTableName();

        $fieldNames = $this->ReadFieldNames();

        for ($i = 0; $i < $this->tableFieldCount; $i++) {
            $specs[$i]['name'] = $fieldNames[$i];
        }
        rewind($this->handle);

        /* start of value checks data */

        $this->Raw(53);

        $this->vals = [];

        while (ftell($this->handle) < $genInfoStartAddr) {

            /* fixed */

            // 0x00
            $num = $this->Dec(1);
            $name = $specs[$num]['name'];
            $type = $specs[$num]['type'];
            $len = $specs[$num]['len'];

            $vals = new ValueChecks($name);
            $vals->num = $num;
            $vals->type = $type;
            $vals->len = $len;
            $vals->posn = "0x" . dechex(ftell($this->handle));

            // 0x01
            $vals->picLen = $this->Dec(1);
            // 0x02
            $vals->reqd = (int)$this->Dec(1);
            // 0x03
            $flags = $this->Hex(1);

            // 0x04
            $this->Raw(2);
            // 0x06
            $vals->hasLookup = $this->Hex(2) == 'e73a' ? 1 : 0;

            // 0x08
            $this->Raw(2);
            // 0x0a
            $vals->hasLookup2 = $this->Hex(2) == 'e73a' ? 1 : 0;

            // 0x0c
            $this->Raw(2);
            // 0x0e
            $vals->hasLoVal = $this->Hex(2) == 'e73a' ? 1 : 0;

            // 0x10
            $this->Raw(2);
            // 0x12
            $vals->hasHiVal = $this->Hex(2) == 'e73a' ? 1 : 0;

            // 0x14
            $this->Raw(2);
            // 0x16
            $vals->hasDef = $this->Hex(2) == 'e73a' ? 1 : 0;

            // 0x18
            $this->Raw(2);
            // 0x1a
            $vals->hasPic = $this->Hex(2) == 'e73a' ? 1 : 0;

            /* variable */

            // 0x1c
            $vals->lookupTable = $vals->hasLookup ? $this->ReadNullTermString(80) : '';
            $vals->loVal = $vals->hasLoVal ? $this->GetFieldData($type, $len) : '';
            $vals->hiVal = $vals->hasHiVal ? $this->GetFieldData($type, $len) : '';
            $vals->def = $vals->hasDef ? $this->GetFieldData($type, $len) : '';
            $vals->pic = $vals->hasPic ? $this->ReadNullTermString() : '';

            $vals->SetFlags($vals->hasLookup, $flags);

            $this->vals[] = $vals;
        }
        $this->Close();
        return $this->vals;
    }

    public function Draw()
    {
        echo("<br>{$this->file}");
        echo '<br>FieldCount: ' . $this->tableFieldCount;
        $t = new HtmlTable;
        $t->Draw($this->vals);
        echo "<br><br>";
    }
}
