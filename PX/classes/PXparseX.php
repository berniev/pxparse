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

class PXparseX extends PXparseDataFile
{

    /** @var TableSpecs */
    public $table = [];

    /** @var string */
    public $indexFields = [];

    /** @var string */
    public $indexName = '';

    /** @var SecIndex */
    public $index = null;
    /**
     * @param string $fName
     *
     * @return bool
     */
    public function ParseFile($fName)
    {
        if ( ! $this->Open($fName)) {
            return false;
        }
        list($this->table, $specs, $names, $nums) = $this->ParseDataFileHeader();

        $this->index = new SecIndex;

        $this->index->name = $this->ReadNullTermString();

        $this->Close();

        array_pop($names); // 'Hint'

        $indexFields = [];
        foreach ($names as $name) {
            $indexFields[] = $name;
        }
        if ($names[0] == 'Sec Key') {
            /* single-field secondary key*/
            $indexFields[0] = $this->index->name;
        }
        $this->index->fields = implode(',', $indexFields);

        return true;
    }

    public function Draw()
    {

        echo("<br>{$this->file}");
        echo '<br>FieldCount: ' . $this->tableFieldCount;
        $this->table->Draw();
        echo "<br>";
        echo "<br>Secondary Index and Primary Key  Name: {$this->index->name}  Fields: {$this->index->fields}";
        echo "<br><br>";
    }

}