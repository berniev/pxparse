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

class PXparseSet extends PXparse
{

    /** @var array */
    public $results = [];

    /*
      todo: where are format, colsize and ordby defined?:

      display formats (CURRENCY, NUMBER & DATE columns only):

      CURRENCY, NUMBER: General, Fixed, Comma (neg in ()), Scientific. And # places. Neg in reverse

      DATE: MM/DD/YY, DD-Mon-YY, DD.MM.YY, YY.MM.DD. YY is YY or YYY or YYYY. MM or DD at extremities are w/o zero pad
     */

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

        /* Header */
        $this->hex(15);

        $endOfData = $this->ReadPxLittleEndian2();

        $this->Hex(44);
        $this->Hex(1);

        $this->tableFieldCount = $this->Dec(1);
        $this->Hex(1);

        $this->Hex(10);

        /* start of settings data */

        $this->results = [];

        for ($i = 0; $i < $this->tableFieldCount; $i++) {
            $res = new Settings;
            $res->posn = "0x" . dechex(ftell($this->handle));
            $this->Hex(12);
            $res->dunno1 = $this->Hex(1);
            $res->dispLen1 = $this->Dec(1);  // one of these is table, the other table display?
            $res->dispLen2 = $this->Dec(1);  // one of these is table, the other table display?
            $res->dunno2 = $this->Hex(1);
            $res->decPlaces = $this->Dec(1);
            $this->Hex(1);

            $this->results[] = $res;
        }
        $this->Hex(6);

        $nums = $this->ReadFieldNums();
        $specs = $this->ReadFieldSpecs();
        $this->ReadTmpName();
        $names = $this->ReadFieldNames();

        foreach ($this->results as $i => $res) {
            $res->num = $nums[$i];
            $res->type = $specs[$i]['type'];
            $res->len = $specs[$i]['len'];
            $res->name = $names[$i];
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

