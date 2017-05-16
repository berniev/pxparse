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

namespace PX\classes\Data\Index;

use PX\classes\Data\PXparseDataFile;

class PXparseX extends PXparseDataFile
{

    /**
     * @param string $path
     * @param string $tableName
     */
    public function __Construct($path, $tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @param string $path
     * @param string $tableName
     *
     * @return false|\string[]
     */
    static public function GetXfiles($path, $tableName)
    {
        $xFiles = [];
        $dir_handle = @opendir($path);
        if ( ! $dir_handle) {
            echo("\nCan't open path {$path}");
            return false;
        }
        while ($file = readdir($dir_handle)) {
            if ($file == "." || $file == "..") {
                continue;
            }
            $filename = explode(".", $file);
            if (strtolower($filename[0]) == strtolower($tableName)) {
                $cnt = count($filename);
                $cnt--;
                $ext = $filename[$cnt];
                if (substr((strtolower($ext)), 0, 2) == 'xg') {
                    $xFiles[] = $file;
                }
            }
        }
        closedir($dir_handle);
        return $xFiles;
    }

    /**
     * @param string $fileName
     *
     * @return SecIndex|false
     */
    public function ParseFile($fileName)
    {
        $this->file = $fileName;
        if ( ! $this->Open()) {
            return false;
        }

        $this->ParseDataFileHeader();

        $name = $this->ReadNullTermString();
        $index = new SecIndex($name);
        $this->Close();
        array_pop($this->names); // 'Hint'

        $index->fields = [];
        foreach ($this->names as $name) {
            $index->fields[] = $name;
        }
        if ($this->names[0] == 'Sec Key') {
            /* single-field secondary key */
            // pdox doesn't preserve case of field name but mysql col names are case insensitive so should have no impact
            $index->fields[0] = $index->name;
        }
        return $index;
    }

}