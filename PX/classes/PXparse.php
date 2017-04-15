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

    const FIELD_TYPES = [
        '01' => 'Alpha',
        '02' => 'Date',
        '03' => 'Short',
        '05' => 'Dollar',
        '06' => 'Number',
        '0c' => 'Memo',
        '0d' => 'Blob'
    ];

    /** @var  string */
    protected $file;

    /** @var resource */
    protected $handle = null;

    /** @var int */
    public $tableFieldCount = 0;

    /**
     * @param string $fname
     *
     * @return mixed
     */
    abstract public function ParseFile($fname);

    abstract public function Draw();

    /**
     * @param string $file
     * @param bool   $reqd
     *
     * @return bool
     */
    protected function Open($file, $reqd = true)
    {
        $this->file = $file;
        if ( ! file_exists($file)) {
            $lev = $reqd ? 'error' : 'note';
            echo("<br>" . __METHOD__ . " Can't open {$file}\n");
            return false;
        }
        $this->handle = fopen($file, 'r');
        fseek($this->handle, 0);
        return true;
    }

    /**
     * @return string
     */
    protected function ReadTmpName()
    {
        $tmp = rtrim($this->Raw(78));
        $this->Raw(1);
        return $tmp;
    }

    /**
     *
     * @return array
     */
    protected function ReadFieldNames()
    {
        $names = [];
        for ($i = 0; $i < $this->tableFieldCount; $i++) {
            $names[$i] = $this->ReadNullTermString();
        }
        return $names;
    }

    /**
     * @return array
     */
    protected function ReadFieldNums()
    {
        $nums = [];
        for ($i = 0; $i < $this->tableFieldCount; $i++) {
            $nums[$i] = $this->Dec(1);
            $this->Raw(1);
        }
        return $nums;
    }

    /**
     * @return array
     */
    protected function ReadFieldSpecs()
    {
        $specs = [];
        for ($i = 0; $i < $this->tableFieldCount; $i++) {
            $specs[$i]['type'] = self::FIELD_TYPES[$this->Hex(1)];
            $specs[$i]['len'] = $this->Dec(1);
        }
        return $specs;
    }

    protected function Close()
    {
        fclose($this->handle);
    }

    /**
     * @param int $num
     *
     * @return bool|string
     */
    protected function Raw($num)
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
    protected function Hex($num)
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
    protected function Dec($num)
    {
        $hex = $this->Hex($num);
        $dec = hexdec($hex);
        return $dec;
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
                $res = rtrim($this->ReadPxString($fldLen));
                break;

            case 'Short':
                $res = $this->ReadPxBigEndian2();
                break;

            case 'Number':
            case 'Dollar':
                $res = $this->GetPxDouble();
                break;

            case 'Date':
                $days = $this->ReadPxBigEndian4();
                $d = new \DateTime("0001-01-00 +{$days} days");
                $res = $d->format("Y-m-d");
                break;

            case 'Memo':
                $res = rtrim($this->Raw($fldLen + 10));
                break;

            case 'Blob':
                $res = rtrim($this->Raw($fldLen + 10));
                break;

            case 'U':
                $res = rtrim($this->Raw($fldLen + 10));
        }

        return $res;
    }

    /**
     * @return number
     */
    protected function GetPxDouble()
    {
        /* input: modified big-endian 8-byte (64-bit) double precision floating point */
        $in = $this->Raw(8);
        if (bin2hex($in[0] & "\x80") != '00') {
            $inn = $in & "\x7f\xff\xff\xff\xff\xff\xff\xff";
        } elseif (bin2hex($in) != '0000000000000000') {
            $inn = ~$in;
        } else {
            return 0;
        }
        $res = unpack('d', (strrev($inn)))[1];
        return $res;
    }

    /**
     * @param int $len
     *
     * @return string
     */
    protected function ReadPxString($len)
    {
        /* fixed length character string */
        $res = rtrim($this->Raw($len));
        return $res;
    }

    /**
     * @return int
     */
    protected function ReadPxLittleEndian2()
    {
        $in = $this->Raw(2);
        $res = unpack('v', ($in))[1];
        return $res;
    }

    /**
     * @return int
     */
    protected function ReadPxLittleEndian4()
    {
        $in = $this->Raw(4);
        $res = unpack('V', ($in))[1];
        return $res;
    }

    /**
     * @return int
     */
    protected function ReadPxBigEndian2()
    {
        // modified big-endian 2-byte (16-bit) signed integer.
        $in = strrev($this->Raw(2)) ^ "\x00\x80";
        $res = unpack('n', $in)[1];
        return $res;
    }

    /**
     * @return string
     */
    public function ReadPxBigEndian4()
    {
        /* 4-byte (32-bit) big-endian. Unsigned long integer */
        $in = $this->Raw(4);
        $res = unpack('N', ($in))[1];
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

    public function DumpPosn()
    {
        $posnD = ftell($this->handle);
        $posnH = dechex($posnD);
        var_dump("Posn:0x{$posnH}({$posnD})");
    }


}
