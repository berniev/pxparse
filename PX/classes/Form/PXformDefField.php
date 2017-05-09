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

namespace PX\classes\Form;

class PXformDefField
{

    /** @var  string */
    public $name;
    /** @var  int */
    public $x;
    /** @var  int */
    public $y;
    /** @var  int */
    public $end;
    /** @var  int */
    public $len;
    /** @var  string */
    public $type;
    /** @var  int */
    public $fieldNum;
    /** @var  string */
    public $detail;
    /** @var  string */
    public $tableColType;
    /** @var  int */
    public $tableColSize;
    /** @var string */
    public $bg = '';
    /** @var string */
    public $fg = '';

    public function SetStyle(array $style)
    {
        $this->fg = $style[0];
        $this->bg = $style[1];
    }
}
