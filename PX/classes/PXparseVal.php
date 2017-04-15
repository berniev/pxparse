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
    public $results = [];

    /**
     * @param string $fName
     *
     * @return array|false
     */
    public function ParseFile($fName)
    {
        if ( ! $this->Open($fName, false)) {
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

        $nums = $this->ReadFieldNums();

        $specs = $this->ReadFieldSpecs();

        $tmpName = $this->ReadTmpName();

        $fieldNames = $this->ReadFieldNames();

        for ($i = 0; $i < $this->tableFieldCount; $i++) {
            $specs[$i]['name'] = $fieldNames[$i];
        }
        rewind($this->handle);

        /* start of value checks data */

        $this->Raw(53);

        $this->results = [];
        while (ftell($this->handle) < $genInfoStartAddr) {

            $res = new ValueChecks;

            /* fixed size */

            $res->num = $this->Dec(1); // 1 // Field Number
            $res->name = $specs[$res->num]['name'];
            $res->type = $specs[$res->num]['type'];
            $res->len = $specs[$res->num]['len'];
            $res->posn = "0x" . dechex(ftell($this->handle));

            $res->picLen = $this->Dec(1); // 2
            $res->reqd = (int)$this->Dec(1); // 3 //
            $flags = $this->Hex(1); // 4

            $this->Raw(2); // 5,6 //
            $res->hasLookup = $this->Hex(2) == 'e73a' ? 1 : 0; // 7,8

            $this->Raw(2); // 9,10
            $res->hasLookup2 = $this->Hex(2) == 'e73a' ? 1 : 0; // 11,12

            $this->Raw(2); // 13,14
            $res->hasLoVal = $this->Hex(2) == 'e73a' ? 1 : 0; // 15,16

            $this->Raw(2); // 17,18
            $res->hasHiVal = $this->Hex(2) == 'e73a' ? 1 : 0; // 19,20

            $this->Raw(2); // 21,22
            $res->hasDef = $this->Hex(2) == 'e73a' ? 1 : 0; //  23,24

            $this->Raw(2); // 25,26
            $res->hasPic = $this->Hex(2) == 'e73a' ? 1 : 0; // 27,28

            $res->SetFlags($res->hasLookup, $flags);

            /* variable size */

            $res->lookupTable = $res->hasLookup ? $this->ReadNullTermString(80) : '';
            $res->loVal = $res->hasLoVal ? $this->GetFieldData($res->type, $res->len) : '';
            $res->hiVal = $res->hasHiVal ? $this->GetFieldData($res->type, $res->len) : '';
            $res->def = $res->hasDef ? $this->GetFieldData($res->type, $res->len) : '';
            $res->pic = $res->hasPic ? $this->ReadNullTermString() : '';

            $this->results[] = $res;
        }
        $this->Close();
        return $this->results;
    }

    public function Draw()
    {
        echo("<br>{$this->file}");
        echo '<br>FieldCount: ' . $this->tableFieldCount;
        $t = new HtmlTable;
        $t->Draw($this->results);
        echo "<br><br>";
    }
}
