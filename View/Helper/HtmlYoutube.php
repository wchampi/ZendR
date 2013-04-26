<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_View_Helper_HtmlYoutube extends Zend_View_Helper_HtmlFlash
{
    
    public function htmlYoutube($url, array $attribs = array(), array $params = array(), $content = null)
    {
        $data = null;
        if (strpos($url, '/v/')  === false) {
            if (strpos($url, 'http://www.youtube.com/watch?v=')  !== false) {
                $urlArray = explode('?', $url);
                $urlParams = explode('&', $urlArray[1]);
                $paramsUrl = array();

                foreach ($urlParams as $param) {
                    list($name, $value) = explode('=', $param);
                    $paramsUrl[urldecode($name)][] = urldecode($value);
                }
                $data = 'http://www.youtube.com/v/' . $paramsUrl['v'][0];
            } elseif (strpos($url, 'http://youtu.be/')  !== false) {
                $urlArray = explode('youtu.be/', $url);
                $data = 'http://www.youtube.com/v/' . $urlArray[1];
            }
        } else {
            $data = $url;
        }
        
        $urlArray = explode('/v/', $data);
        $urlArray = explode('?', $urlArray[1]);
        if (!isset($urlArray[0])) {
            return 'Url invalida';
        }
        if (trim($urlArray[0]) == '') {
            return 'Url invalida';
        }
        $codigo = $urlArray[0];
        
        if (isset($attribs['code'])) {
            return $codigo;
        }

        if (isset($attribs['youtube'])) {
            $atribYoutube = $attribs['youtube'];
            unset($attribs['youtube']);
        }

        $attribs = array_merge(
            array(
                'width' => '425',
                'height' => '344'
            ),
            $attribs
        );
        
        if (isset($attribs['image'])) {
            if (isset($attribs['url'])) {
                return 'http://i.ytimg.com/vi/' . $codigo . '/0.jpg';
            } else {
                $noImage = $this->view->baseUrl('zendr/css/graphics/no-image-youtube.jpeg');
                if (isset($attribs['noimage'])) {
                    $noImage = $attribs['noimage'];
                }
                return '<img src="http://i.ytimg.com/vi/' . $codigo . '/0.jpg" '
                    . (isset($attribs['width']) ? ' width="' . $attribs['width'] . '" ' : '')
                    . (isset($attribs['height']) ? ' height="' . $attribs['height'] . '"' : '')
                    . '" onerror="this.src=\''
                    . $noImage . '\'"  />';
            }
        }
        
        $params = array_merge(
            array(
                'allowFullScreen'   => 'true',
                'allowscriptaccess' => 'always'
            ),
            $params
        );

        $data = 'http://www.youtube.com/v/' . $codigo . '?' . $atribYoutube;
        if (isset($attribs['url'])) {
            if (isset($attribs['watch'])) {
                return 'http://www.youtube.com/watch?v=' . $codigo . '?' . $atribYoutube;
            }
            return $data;
        } else {
            return $this->htmlFlash($data, $attribs, $params, $content);
        }
    }
}
