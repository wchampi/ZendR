<?php

/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */
class ZendR_Util
{
    public static function prepareStringByUrl($string)
    {
        $filter = new Zend_Filter_Alnum();
        $filter->setAllowWhiteSpace(true);
        $stringFilter = $filter->filter(strip_tags($string));
        
        $stringFilter = ZendR_String::parseString($stringFilter)->toLower()->toUTF8();
        
        $stringFilter = preg_replace('[aáàãâä]','a',$stringFilter);
        $stringFilter = preg_replace('[eéèêë]','e',$stringFilter);
        $stringFilter = preg_replace('[iíìîï]','i',$stringFilter);
        $stringFilter = preg_replace('[oóòõôö]','o',$stringFilter);
        $stringFilter = preg_replace('[uúùûü]','u',$stringFilter);
        $stringFilter = preg_replace('[ç]','c',$stringFilter);
        $stringFilter = preg_replace('[ñ]','n',$stringFilter);
        $stringFilter = str_replace(' ', '+', $stringFilter);
        $stringFilter = str_replace("\\", '', $stringFilter);
        
        return $stringFilter;
    }
    
    public static function prepareEditOptionsSelectJqFrid($options)
    {
        $editOptions = array();
        foreach ($options as $key => $value) {
            $editOptions[] = $key . ':' . $value;
        }
        return implode(';', $editOptions);
    }
    
    public static function prepareResponseJqGrid($page, $rowsByPage, $rowsTotal, $rows, $indexId = 'id') 
    {
        if ($rowsTotal > 0) {
            $totalPages = ceil($rowsTotal / $rowsByPage);
        } else {
            $totalPages = 0;
        }

        $response = array(
            'page' => $page,
            'total' => $totalPages,
            'records' => $rowsTotal,
            'rows' => array()
        );
        
        foreach ($rows as $row) {
            $id = $row[$indexId];
            $response['rows'][] = array(
                'id' => $id,
                'cell' => array_values($row),
            );
        }
        
        return $response;
    }
    
    public static function fetchOffset($limit, $page)
    {
        return $limit * ($page - 1);
    }
    
    public static function generateArbitraryCode($numeroLetras = 12)
    {
        $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $cad = "";
        for ($i = 1; $i <= $numeroLetras; $i++) {
            $cad .= substr($str, rand(0, 62), 1);
        }
        return $cad;
    }

    public static function obtenerComision($porcentaje)
    {
        return 100 / (100 - $porcentaje);
    }

    public static function redondeo($valor)
    {
        if (($valor - floor($valor)) < 0.1) {
            $valor = floor($valor);
        } else {
            $valor = ceil($valor);
        }
        return $valor;
    }

    public static function obtenerPathIniString($path)
    {
        $content = 'path = ' . $path;
        $fileIniTmp = tempnam(sys_get_temp_dir(), 'ini');

        file_put_contents($fileIniTmp, $content);

        $iniArray = parse_ini_file($fileIniTmp);

        unlink($fileIniTmp);

        return $iniArray['path'];
    }

    public static function collectionRand($collection, $numReq)
    {
        if (count($collection) > 0) {
            if (is_array($collection)) {
                if (count($collection) < $numReq) {
                    $numReq = count($collection);
                }
                return array_rand($collection, $numReq);
            } else {
                $arrayKeys = array();
                foreach ($collection as $key => $value) {
                    $arrayKeys[] = $key;
                }

                if (count($arrayKeys) < $numReq) {
                    $numReq = count($arrayKeys);
                }

                $arrayKeys = array_rand($arrayKeys, $numReq);

                $arrayValues = array();
                if (is_array($arrayKeys)) {
                    foreach ($arrayKeys as $key) {
                        $arrayValues[] = $collection[$key];
                    }
                } else {
                    $arrayValues[] = $collection[$arrayKeys];
                }
                return $arrayValues;
            }
        }
        return array();
    }

    public static function arraySort($array, $on, $order = SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }
   
}