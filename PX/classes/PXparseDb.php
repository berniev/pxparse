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
 *
 * Just to get primary key field names
 */

namespace PX\classes;

class PXparseDb extends PXparse
{

    /** @var TableSpecs */
    public $table = [];

    /** @var FieldSpecs */
    public $fields = [];

    public function ParseFile($fName)
    {
        if ( ! $this->Open($fName, false)) {
            return false;
        }
        $table = new TableSpecs;

        $table->recordSize = $this->ReadPxLittleEndian2(); // 0x00,0x01
        $table->headerSize = $this->ReadPxLittleEndian2(); // 0x02,0x03
        $table->isKeyed = $this->Hex(1) == '02' ? 0 : 1; // 0x04
        $table->blockSize = $this->Dec(1); // 0x05
        $table->numRecords = $this->ReadPxLittleEndian4(); // 0x06-0x09
        $table->numBlocks = $this->ReadPxLittleEndian2(); // 0x0a,0x0b
        $table->fileBlocks = $this->ReadPxLittleEndian2(); // 0x0c,0x0d
        $table->firstBlock = $this->ReadPxLittleEndian2(); // 0x0e,0x0f always 1
        $table->lastBlock = $this->ReadPxLittleEndian2(); // 0x10,0x11

        $this->Raw(15);

        $table->numFields = $this->tableFieldCount = $this->Dec(1); // 0x21

        $this->Raw(1); // 0x22

        $table->numKeyFields = $this->Dec(1); // 0x23

        $table->firstFreeBlockNum = $this->ReadPxLittleEndian2(); // 0x4d

        $this->Hex(82);

        $specs = $this->ReadFieldSpecs();

        $this->Raw(4);
        $this->Raw(4 * $table->numFields);

        $table->tmpFile = $this->ReadTmpName();
        $names = $this->ReadFieldNames();
        $nums = $this->ReadFieldNums();

        $table->sortOrder = $this->ReadPxString(8);

        $this->fields = [];
        for ($i = 0; $i < $table->numFields; $i++) {
            $field = new FieldSpecs;
            $field->name = $names[$i];
            $field->type = $specs[$i]['type'];
            $field->len = $specs[$i]['len'];
            //$field->num = $nums[$i];
            $this->fields[] = $field;
        }
        if ($table->isKeyed) {
            for ($i = 0; $i < $table->numKeyFields; $i++) {
                $this->fields[$i]->isKey = 1;
            }
        }
        $this->table = $table;
        return [$this->table, $this->fields];
    }

     public function Draw()
    {
        echo("<br>{$this->file}");
        echo '<br>FieldCount: ' . $this->tableFieldCount;
        $t = new HtmlTable;
        $t->Draw([$this->table]);
        $t = new HtmlTable;
        $t->Draw((array)$this->fields);
        echo "<br><br>";
    }

}


