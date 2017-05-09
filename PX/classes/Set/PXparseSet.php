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

namespace PX\classes\Set;

use PX\classes\PXparse;

class PXparseSet extends PXparse
{

    /** @var Settings[] */
    public $settings = [];

    /*
      todo: where are format, colsize and ordby defined?:

      display formats (CURRENCY, NUMBER & DATE columns only):

      CURRENCY, NUMBER: General, Fixed, Comma (neg in ()), Scientific. And # places. Neg in reverse

      DATE: MM/DD/YY, DD-Mon-YY, DD.MM.YY, YY.MM.DD. YY is YY or YYY or YYYY. MM or DD at extremities are w/o zero pad
     */

    /**
     * PXparseSet constructor.
     *
     * @param string $path
     * @param string $tableName
     */
    public function __Construct($path, $tableName)
    {
        $this->tableName = $tableName;
        $this->file = "{$path}{$tableName}.set";
    }

    /**
     * @return array|false
     */
    public function ParseFile()
    {
        if ( ! $this->Open()) {
            return false;
        }

        /* fixed */

        // 0x00
        $this->Skip(15);

        // 0x0f
        $this->ReadPxLe2(); // end of data

        // 0x11
        $this->Skip(44);
        // 0x47
        $this->Skip(1);

        // 0x48
        $tableFieldCount = $this->Dec(1);
        // 0x49
        $this->Skip(1); // 00

        //0x50
        $this->Skip(10);

        /* start of settings data */

        $this->settings = [];

        /* variable */

        // 0x5a
        for ($i = 0; $i < $tableFieldCount; $i++) {
            $set = new Settings;
            $this->settings[] = $set;

            $set->posn = "0x" . dechex($this->GetPosn());
            $this->Skip(12);
            $set->dunno1 = $this->Hex(1);
            $set->defDispLen = $this->Dec(1);
            $set->useDispLen = $this->Dec(1);
            $set->dunno2 = $this->Hex(1);
            $set->decPlaces = $this->Dec(1);
            $this->Hex(1);
        }
        $this->Hex(6);

        $nums = $this->ReadFieldNums($tableFieldCount);
        $specs = $this->ReadFieldSpecs($tableFieldCount);
        $this->ReadTableName();
        $names = $this->ReadFieldNames($tableFieldCount);

        foreach ($this->settings as $i => $set) {
            $set->num = $nums[$i];
            $set->type = $specs[$i]['type'];
            $set->len = $specs[$i]['len'];
            $set->name = $names[$i];
        }
        $this->Close();
        return $this->settings;
    }
}

