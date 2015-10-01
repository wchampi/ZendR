<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Inflector
{
    public static function variable($column)
    {
        $column = str_replace(' ', '', ucwords(str_replace('_', ' ', $column)));
        return strtolower(substr($column, 0, 1)) . substr($column, 1);
    }

    public static function label($column)
    {
        $search = array('Numero', 'Descripcion', 'Titulo', 'Telefono', 'Creacion', 
            'Actualizacion', 'Direccion', 'Codigo', 'Insercion', 'Album', 'Cotizacion',
            'Division');
        $replace = array('Número', 'Descripción', 'Título', 'Teléfono', 'Creación',
            'Actualización', 'Dirección', 'Código', 'Inserción', 'Álbum', 'Contización',
            'División');
        $label = ucwords(str_replace('_', ' ', $column));
        return str_replace($search, $replace, $label);
    }

    public static function plural($string)
    {
        $search = array('Albumes');
        $replace = array('Álbumes');

        $len = strlen(trim($string));
        $ultimaLetra = substr(trim($string), $len - 1, 1);

        if (in_array($ultimaLetra, array('a', 'e', 'i', 'o', 'u')) || $ultimaLetra == 'p') {
            return self::humanize(str_replace($search, $replace, $string . 's'));
        } else {
            return self::humanize(str_replace($search, $replace, $string . 'es'));
        }
    }

    public static function humanize($string)
    {
        return preg_replace('~(?<=\\w)([A-Z])~', ' - $1', $string);
    }

    public static function colocarBaseUrl($file)
    {
        if (!is_file($file)) {
            throw new Exception('File not found');
        }
        
        $contentHtml = file_get_contents($file);
        
        $dom = new Zend_Dom_Query(file_get_contents($file));

        $results = $dom->query('a');
        foreach ($results as $result) {
            if ($result->getAttribute('href') != '') {
                if (strpos($result->getAttribute('href'), '<?php') !== false) {
                    continue;
                }

                if (strpos($result->getAttribute('href'), 'http') !== false) {
                    continue;
                }

                if (strpos($result->getAttribute('href'), 'javascript') !== false) {
                    continue;
                }

                $search     = 'href="' . $result->getAttribute('href') . '"';
                $replace    = 'href="<?php echo $this->baseUrl(\'' . $result->getAttribute('href') . '\') ?>"';
                $contentHtml = str_replace($search, $replace, $contentHtml);
            }
        }

        $results = $dom->query('img');
        foreach ($results as $result) {
            if ($result->getAttribute('src') != '') {
                if (strpos($result->getAttribute('src'), '<?php') !== false) {
                    continue;
                }

                if (strpos($result->getAttribute('src'), 'http') !== false) {
                    continue;
                }

                if (strpos($result->getAttribute('src'), 'javascript') !== false) {
                    continue;
                }

                $search     = 'src="' . $result->getAttribute('src') . '"';
                $replace    = 'src="<?php echo $this->baseUrl(\'' . $result->getAttribute('src') . '\') ?>"';
                $contentHtml = str_replace($search, $replace, $contentHtml);
            }
        }

        $results = $dom->query('link');
        foreach ($results as $result) {
            if ($result->getAttribute('href') != '') {
                if (strpos($result->getAttribute('href'), '<?php') !== false) {
                    continue;
                }

                if (strpos($result->getAttribute('href'), 'http') !== false) {
                    continue;
                }

                if (strpos($result->getAttribute('href'), 'javascript') !== false) {
                    continue;
                }

                $search     = 'href="' . $result->getAttribute('href') . '"';
                $replace    = 'href="<?php echo $this->baseUrl(\'' . $result->getAttribute('href') . '\') ?>"';
                $contentHtml = str_replace($search, $replace, $contentHtml);
            }
        }

        $results = $dom->query('script');
        foreach ($results as $result) {
            if ($result->getAttribute('src') != '') {
                if (strpos($result->getAttribute('src'), '<?php') !== false) {
                    continue;
                }

                if (strpos($result->getAttribute('src'), 'http') !== false) {
                    continue;
                }

                if (strpos($result->getAttribute('src'), 'javascript') !== false) {
                    continue;
                }

                $search     = 'src="' . $result->getAttribute('src') . '"';
                $replace    = 'src="<?php echo $this->baseUrl(\'' . $result->getAttribute('src') . '\') ?>"';
                $contentHtml = str_replace($search, $replace, $contentHtml);
            }
        }

        $results = $dom->query('input');
        foreach ($results as $result) {
            if ($result->getAttribute('src') != '') {
                if (strpos($result->getAttribute('src'), '<?php') !== false) {
                    continue;
                }

                if (strpos($result->getAttribute('src'), 'http') !== false) {
                    continue;
                }

                if (strpos($result->getAttribute('src'), 'javascript') !== false) {
                    continue;
                }

                $search     = 'src="' . $result->getAttribute('src') . '"';
                $replace    = 'src="<?php echo $this->baseUrl(\'' . $result->getAttribute('src') . '\') ?>"';
                $contentHtml = str_replace($search, $replace, $contentHtml);
            }
        }

        file_put_contents($file, $contentHtml);
    }
}