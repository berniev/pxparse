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

class PXformField
{

    /** @var string */
    public $name = null;
    /**
     *  RO | RW
     *
     * @var string
     */
    public $type = null;
    /** @var int */
    public $x = null;
    /** @var int */
    public $y = null;
    /** @var string */
    public $label;
    /** @var  string */
    public $value = null;
    /** @var int */
    public $length = null;

    /**
     * @param string      $name
     * @param string      $type
     * @param int|null    $x
     * @param int|null    $y
     * @param string|null $label
     * @param string|null $value
     * @param int         $length
     */
    public function __Construct(
        $name,
        $type = 'RO',
        $x = null,
        $y = null,
        $label = null,
        $value = null,
        $length = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->x = 0 + $x;
        $this->y = 0 + $y;
        $this->label = $label;
        $this->value = $value;
        $this->length = $length;
    }

    public function ValueParser()
    {
    }

}
