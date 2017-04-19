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

class PXparseDb extends PXparseDataFile
{

    /** @var FieldSpecs[] */
    public $fields = [];

    /**
     * DB and Xxx files have the same header structure
     *
     * @param string $fName
     *
     * @return array|bool
     */
    public function ParseFile($fName)
    {
        if ( ! $this->Open($fName)) {
            return false;
        }

        list($specs, $names, $nums) = $this->ParseDataFileHeader();

        $this->table->name = $fName;

        $this->fields = [];
        for ($i = 0; $i < $this->table->numFields; $i++) {
            $field = new FieldSpecs($names[$i]);
            $field->type = $specs[$i]['type'];
            $field->len = $specs[$i]['len'];
            $field->num = $nums[$i];
            $this->fields[] = $field;
        }
        $this->table->isKeyed = $this->table->fileType == '02' ? 0 : 1;
        if ($this->table->isKeyed) {
            for ($i = 0; $i < $this->table->numKeyFields; $i++) {
                $this->fields[$i]->isKey = 1;
            }
        }

        return [$this->table, $this->fields];
    }

    public function Draw()
    {
        echo("<br>{$this->file}");
        echo '<br>FieldCount: ' . $this->tableFieldCount;
        $this->table->Draw();
        $t = new HtmlTable();
        $t->Draw($this->fields);
        echo "<br><br>";
    }

    public function GetData()
    {
    }

    private function GoToBlock($num)
    {
    }

    private function ReadBlocks()
    {
        if ($this->table->numRecords == 0) {
            return;
        }
        do {
            $nextBlockNum = $this->ReadBlock();
        } while ($nextBlockNum > 0);
    }

    private function ReadBlock()
    {
        $nextBlockNum = $this->ReadPxLittleEndian2();
        $this->raw(2); // prev block num - ignore
        $offsetToLastRecord = $this->ReadPxLittleEndian2();
        $lastRecordStart = $this->Posn() + $offsetToLastRecord;
        $records = [];
        while ($this->Posn() <= $lastRecordStart) {
            $records[] = $this->ReadRecord();
        }
        return $nextBlockNum;
    }

    private function ReadRecord()
    {
        $vals = [];
        foreach ($this->fields as $field) {
            $vals[] = $this->GetFieldData($field->type, $field->len);
        }
        return $vals;
    }
}



