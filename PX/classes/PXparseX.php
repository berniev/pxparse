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

class PXparseX extends PXparse
{

    /** @var TableSpecs */
    public $table = [];

    /** @var [] */
    public $indexFields = [];

    /** @var FieldSpecs[] */
    public $primaryKeyFields = [];

    /** @var array */
    public $results = [];

    /** @var string */
    public $indexName = '';

    public function ParseFile($fName)
    {
        if ( ! $this->Open($fName, false)) {
            return false;
        }
        list($this->table, $specs, $names, $nums) = $this->ParseDataHeader();
        $this->indexName = $this->ReadNullTermString();

        array_pop($names); // 'Hint'
        $this->indexFields = [];
        foreach ($names as $name) {
            $this->indexFields[] = $name;
        }
        if ($names[0] == 'Sec Key') {
            /* single-field secondary key*/
            $this->indexFields[0] = $this->indexName;
        }
        $this->Close();

        $this->results = [$this->table, $this->indexFields];
        return [$this->results];
    }

    public function Draw()
    {

        echo("<br>{$this->file}");
        echo '<br>FieldCount: ' . $this->tableFieldCount;
        $this->table->Draw();
        echo "<br>";
        echo "<br>Secondary Index and Primary Key Fields: " . implode(', ', $this->indexFields);
        echo "<br><br>";
    }

}