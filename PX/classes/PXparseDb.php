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

    /** @var TableSpecs */
    public $table = [];

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

        list($this->table, $specs, $names, $nums) = $this->ParseDataFileHeader();

        $this->table->name = $fName;

        $this->fields = [];
        for ($i = 0; $i < $this->table->numFields; $i++) {
            $field = new FieldSpecs;
            $field->name = $names[$i];
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

}


