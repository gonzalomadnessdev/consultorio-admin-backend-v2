<?php

namespace App\Utilities;

use Exception;

/*
$importer = new CsvImporter("small.txt",true);
$data = $importer->get();
print_r($data);
*/

class CsvImporter
{
    private $fp;
    private $parse_header;
    private $header;

    private $delimiter;
    private $length;
    private $enclosure;
    private $escape;

    //--------------------------------------------------------------------
    function __construct($file_name, $parse_header=false, $delimiter=";", $length=8000, $enclosure = '"', $escape = "\\",$auto_detect_line_endings=true)
    {
        ini_set('auto_detect_line_endings',$auto_detect_line_endings);
        $this->fp = fopen($file_name, "r");
        $this->parse_header = $parse_header;
        $this->delimiter = $delimiter;
        $this->length = $length;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
        //$this->lines = $lines;

        if ($this->parse_header)
        {
           $this->header = fgetcsv($this->fp, $this->length, $this->delimiter, $this->enclosure, $this->escape);
        }

    }
    //--------------------------------------------------------------------
    function __destruct()
    {
        if ($this->fp)
        {
            fclose($this->fp);
        }
    }
    function getHeader(){
        return $this->header;
    }
    function setHeader($header){
        $this->header=$header;
		$this->parse_header=true;
    }
    function sanitizeRow(&$row){
        if(!empty($row)){
            foreach($row as $key=>$value){
                if(is_string($value)&&$value=="NULL"){
                    $row[$key]=NULL;
                }
            }
        }
        return $row;
    }
    //--------------------------------------------------------------------
    //get CSV as array
    function get($max_lines=0)
    {
        //if $max_lines is set to 0, then get all the data

        $data = array();

        if ($max_lines > 0)
            $line_count = 0;
        else
            $line_count = -1; // so loop limit is ignored

        while ($line_count < $max_lines && ($row = fgetcsv($this->fp, $this->length, $this->delimiter, $this->enclosure, $this->escape)) !== FALSE)
        {
            $this->sanitizeRow($row);

            if ($this->parse_header)
            {
                foreach ($this->header as $i => $heading_i)
                {
                    $row_new[$heading_i] = $row[$i];
                }
                $data[] = $row_new;
            }
            else
            {
                $data[] = $row;
            }

            if ($max_lines > 0)
                $line_count++;
        }
        return $data;
    }
    //--------------------------------------------------------------------
    //stream read CSV an insert bulk
    function getInserBulk($table,$limit=200)
    {
        if(empty($this->header))
            return false;

        $count = 0;
        $data = array();
        $arrSql = array();

        $max_lines = 0;
        $line_count = -1; // so loop limit is ignored

        while ($line_count < $max_lines && ($row = fgetcsv($this->fp, $this->length, $this->delimiter, $this->enclosure, $this->escape)) !== FALSE)
        {
            $this->sanitizeRow($row);

            if(count($row)<>count($this->header)){
                throw new Exception("La cantidad de valores en la fila es ".count($row).", en la cabecera se definieron ".count($this->header));
            }

            $data[] = $row;

            if (++$count % $limit == 0) {

                $arrSql[]=$this->insertBulk($table, $this->header, $data);

                ob_flush();
                flush();

                $data=array();
                $count=0;
            }
        }
        if(!empty($data)){
            $arrSql[]=$this->insertBulk($table, $this->header, $data);
            $data=array();
        }
        return $arrSql;
    }
    public function insertBulk($table, $cols = array(), $data = array()) {
        if (empty($table) || empty($cols) || empty($data)) {
            return false;
        }
        try {

            $query = "INSERT INTO {$table} (" . implode(',', $cols) . ") VALUES ";
            $queryVals = array();
            foreach ($data as $row) {
                foreach ($row as &$rowcol) {
                    if ($rowcol === NULL) {
                        $rowcol = "NULL";
                    } else {
                        //$rowcol = mysql_real_escape_string($rowcol);
                        if (function_exists('addslashes'))
                        {
                            $rowcol= addslashes($rowcol);
                        }
                        else
                        {
                            $rowcol= mysql_real_escape_string($rowcol);
                        }
                        if (!is_int($rowcol)&&!is_float($rowcol)&&!is_numeric($rowcol)&& is_string($rowcol)) {

                            $rowcol="'{$rowcol}'";
                        }

                    }
                }
                $queryVals[] = '(' . implode(',', $row) . ')';
            }
            $query.=implode(',', $queryVals);


            if (isset($_GET["dumpsql"]) && $_GET["dumpsql"] == 1) {
                dump("query");
                dump($query);
            }


            return $query;
        } catch (Exception $e) {

            throw $e;
        }
        return false;
    }

    //--------------------------------------------------------------------

}

?>
