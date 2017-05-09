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

class DestSql
{

    /** @var string */
    private $lastError = '';

    /** @var resource */
    private $handle = null;

    /** @var string */
    private $fileName = '';

    public function __Construct($fileName)
    {
        $this->fileName = $fileName;
    }

    public function Open($mode = 'w')
    {
        $this->handle = @fopen($this->fileName, $mode);
        if ($this->handle === false) {
            $this->lastError = "file open failure {$this->fileName}";
            return false;
        }
        return true;
    }

    public function Write($data)
    {
        $res = fwrite($this->handle, $data);
        if ($res === false) {
            $this->lastError = "file write failure {$this->fileName}";
            return false;
        }
        return true;
    }

    public function Close()
    {
        fclose($this->handle);
    }

    public function LastError()
    {
        return $this->lastError;
    }
}