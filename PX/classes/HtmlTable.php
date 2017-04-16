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

class HtmlTable
{

    /** @var array[] */
    public $rows = [];

    /** @var array */
    public $hrow = [];

    /**
     * @param array $arr
     */
    public function Draw($arr = null)
    {
        if ($arr) {
            $this->rows = $arr;
        }
        if ( ! $this->hrow) {
            $this->hrow = array_keys((array)$arr[0]);
        }
        echo "<table>";
        foreach ($this->hrow as $h) {
            echo "<th>{$h}</th>";
        }
        foreach ($this->rows as $row) {
            echo "<tr>";
            foreach ($row as $field) {
                echo "<td>{$field}</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}
