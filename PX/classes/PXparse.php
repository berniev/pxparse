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

abstract class PXparse
{

    /** @var  string */
    protected $path;

    /** @var string */
    protected $file;

    /** @var resource */
    protected $handle = null;

    /** @var string */
    protected $tableName = '';

    const FIELD_TYPES = [
        '01' => 'Alpha',
        '02' => 'Date',
        '03' => 'Short',
        '05' => 'Dollar',
        '06' => 'Number',
        '0c' => 'Memo',
        '0d' => 'Blob'
    ];

    /**
     * @return bool
     */
    public function Open()
    {
        $this->handle = @fopen($this->file, 'r');
        if ($this->handle === false) {
            return false;
        }
        rewind($this->handle);
        return true;
    }

    /**
     * @return string
     */
    public function ReadTableName()
    {
        $tmp = rtrim($this->Raw(78));
        $this->Skip(1);
        return $tmp;
    }

    /**
     *
     * @param int $fieldCount
     *
     * @return array
     */
    public function ReadFieldNames($fieldCount)
    {
        $names = [];
        for ($i = 0; $i < $fieldCount; $i++) {
            $names[$i] = $this->ReadNullTermString();
        }
        return $names;
    }

    /**
     * @param int $fieldCount
     *
     * @return array
     */
    public function ReadFieldNums($fieldCount)
    {
        $nums = [];
        for ($i = 0; $i < $fieldCount; $i++) {
            $nums[$i] = $this->Dec(1);
            $this->Skip(1);
        }
        return $nums;
    }

    /**
     * @param int $fieldCount
     *
     * @return array
     */
    public function ReadFieldSpecs($fieldCount)
    {
        $specs = [];
        for ($i = 0; $i < $fieldCount; $i++) {
            $specs[$i]['type'] = self::FIELD_TYPES[$this->Hex(1)];
            $specs[$i]['len'] = $this->Dec(1);
        }
        return $specs;
    }

    public function Close()
    {
        fclose($this->handle);
    }

    /**
     * @param int $num
     *
     * @return bool|string
     */
    public function Raw($num)
    {
        $read = fread($this->handle, $num);
        if (false === $read || '' == $read) {
            $pos = ftell($this->handle);
            $this->Close();
            echo("<br>" . __METHOD__ . " Failed to read {$num} bytes from {$this->file} at {$pos}. Exiting\n");
            exit;
        }

        return $read;
    }

    /**
     * @param int $num
     *
     * @return string
     */
    public function Hex($num)
    {
        $bin = $this->Raw($num);
        $hex = bin2hex($bin);

        return $hex;
    }

    /**
     * @param int $num
     *
     * @return number
     */
    public function Dec($num)
    {
        $hex = $this->Hex($num);
        $dec = hexdec($hex);
        return $dec;
    }

    private function Clean($str)
    {
        $str = str_replace("'", "''", $str);
        $str = str_replace("\\", "\\\\", $str);
        return $str;
    }

    /**
     * @param string $fldType
     * @param int    $fldLen
     *
     * @return string
     */
    public function GetFieldData($fldType, $fldLen)
    {
        $res = '';
        switch ($fldType) {
            case 'Alpha':
                $res = rtrim($this->ReadString($fldLen));
                $res = $this->Clean($res);
                break;

            case 'Short':
                $res = $this->ReadPxBe2();
                break;

            case 'Number':
            case 'Dollar':
                $res = $this->ReadPxDouble();
                break;

            case 'Date':
                $days = $this->ReadPxBe4();
                if ($days == 0) {
                    $res = null;
                } else {
                    $d = new \DateTime("0001-01-00 + {$days} days");
                    $res = $d->format("Y-m-d");
                }
                break;

            case 'Memo':
                $res = rtrim($this->Raw($fldLen - 10));
                $res = $this->Clean($res);
                $this->Raw(10);
                break;

            case 'Blob':
                $res = rtrim($this->Raw($fldLen - 10));
                $this->Raw(10);
                break;

            case 'U':
                $res = rtrim($this->Raw($fldLen - 10));
                $this->Raw(10);
        }

        return $res;
    }

    /**
     * @param int $len
     *
     * @return string
     */
    public function ReadString($len)
    {
        /* fixed length character string */
        $res = rtrim($this->Raw($len));
        return $res;
    }

    /**
     * @param int $minLen
     *
     * @return string
     */
    public function ReadNullTermString($minLen = 0)
    {
        $res = '';
        $chr = $this->Raw(1);
        $len = 1;
        while ($chr !== "\x00") {
            $res .= $chr;
            $len++;
            $chr = $this->Raw(1);
        }
        if ($len < $minLen) {
            $toGet = $minLen - $len;
            for ($i = 0; $i < $toGet; $i++) {
                $this->Raw(1);
            }
        }
        return $res;
    }

    /**
     * pdox number and dollar types
     *
     * @return number|string
     */
    public function ReadPxDouble()
    {
        /* input: modified big-endian 8-byte (64-bit) double precision floating point */
        $in = $this->Raw(8);
        if (bin2hex($in[0] & "\x80") != '00') {
            // msb is set, strip it
            $inn = $in & "\x7f\xff\xff\xff\xff\xff\xff\xff";
        } elseif (bin2hex($in) != '0000000000000000') {
            $inn = ~$in;
        } else {
            return null;
        }
        $res = unpack('d', (strrev($inn)))[1];
        return $res;
    }

    /**
     * @return int
     */
    public function ReadPxLe2()
    {
        $in = $this->Raw(2);
        $res = unpack('v', ($in))[1];
        return $res;
    }

    /**
     * @return int
     */
    public function ReadPxLe4()
    {
        $in = $this->Raw(4);
        $res = unpack('V', ($in))[1];
        return $res;
    }

    /**
     * @return int
     */
    public function ReadPxBe2()
    {
        // modified big-endian 2-byte (16-bit) signed integer.
        $raw = $this->Raw(2);
        if (bin2hex($raw) == '0000') {
            return null;
        }
        $in = strrev($raw) ^ "\x00\x80";
        $res = unpack('s', $in)[1];
        return $res;
    }

    /**
     * @return string
     */
    public function ReadPxBe4()
    {
        /* 4-byte (32-bit) big-endian. Unsigned long integer */
        $raw = $this->Raw(4);
        if (bin2hex($raw) == '00000000') {
            return null;
        }
        $in = strrev($raw) ^ "\x00\x00\x00\x80";
        $res = unpack('V', ($in))[1];
        return $res;
    }

    public function GetPosn()
    {
        return ftell($this->handle);
    }

    public function DumpPosn()
    {
        $posnD = $this->GetPosn();
        $posnH = dechex($posnD);
        var_dump("Posn:0x{$posnH}({$posnD})");
    }

    /**
     * @param int $posn
     */
    public function SetPosn($posn)
    {
        fseek($this->handle, $posn);
    }

    /**
     * @param int $num
     */
    public function Skip($num)
    {
        fseek($this->handle, $num, SEEK_CUR);
    }

}
