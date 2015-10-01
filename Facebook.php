<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

require_once 'ZendR/Facebook/facebook.php';

class ZendR_Facebook extends Facebook
{
    /**
     *
     * @param string $url
     * @return array|int|null
     */
    public function obtenerTotalLikes($url)
    {
        try {
            $array = false;
            if (is_array($url)) {
                $array = true;

                $urls = array();
                foreach ($url as $uri) {
                    $urls[] = "'" . $uri . "'";
                }
                $url = 'IN(' . implode(',', $urls) . ')';
            } elseif(is_string($url)) {
                $url = "= '" . $url . "'";
            } else {
                throw new Exception('Url invalida');
            }

            $query = "SELECT url, like_count FROM link_stat WHERE url " . $url;
            
            $response = $this->api(array('method' => 'fql.query', 'query' => $query));

            if (isset($response[0])) {
                if (count($response) == 1 && !$array) {
                    return (int)$response[0]['like_count'];
                } else {
                    $likeCounts = array();
                    foreach ($response as $row) {
                        $likeCounts[$row['url']] = $row['like_count'];
                    }
                    return $likeCounts;
                }
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     *
     * @return boolean
     */
    public static function esAgente()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'facebook') !== false) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param array $atribs
     * @return string
     */
    public static function obtenerHtmlPublicar(array $atribs)
    {
        $view = new Zend_View();
        $view->addBasePath(dirname(__FILE__) . '/Facebook');
        $view->assign($atribs);

        return $view->render('publicar.phtml');
    }
}
