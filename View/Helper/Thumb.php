<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */
require_once 'Zend/View/Helper/Abstract.php';
require_once 'ZendR/Thumb/ThumbLib.inc.php';
ini_set('gd.jpeg_ignore_warning', 1);
class ZendR_View_Helper_Thumb extends Zend_View_Helper_Abstract
{

    public function thumb($src, $width, $height = null, $resize = false, $imageNotAvailable = null, $baseUrl = null, $content = false, $pathDirThumb = null)
    {
        try {
            $tipos = explode(".", $src);
            $extension = strtolower($tipos[count($tipos)-1]);
            if (!in_array($extension, array('jpg', 'gif', 'png'))) {
                throw new Exception('type not support');
            }
            
            if (strpos($src, 'http') !== false) {
                $imageTmp = sys_get_temp_dir() . '/' . md5($src) . '.' . $extension;
                file_put_contents($imageTmp, file_get_contents($src));
                $src = $imageTmp;
            }    
            
            if (!is_file($src) && is_file($imageNotAvailable)) {
                $src = $imageNotAvailable;
            }

            $filesize = 0;
            if (is_file($src)) {
                $filesize = filesize($src);
            }
            
            if ($pathDirThumb == '') {
                $pathDirThumb = rtrim(UPLOAD_PATH, '/') . '/_thumb/';
                if (!is_dir($pathDirThumb)) {
                    mkdir($pathDirThumb);
                }
            } else {
                $pathDirThumb .= '/';
            }  
            
            $file = md5(':/' . $resize . '/' . $width . '/' . $height . '/' . $filesize . '/' . $src) . '.' . strtolower($extension);

            $fileThumb = $pathDirThumb . $file;
            if (!file_exists($fileThumb)) {
                $thumb = PhpThumbFactory::create($src);
                
                if ($resize) {
                    if ($height == null) {
                        $height = $width;
                    }
                    $thumb->resize($width, $height);
                } else {
                    $imagesize = getimagesize($src);
                    if ($height == null) {
                        $factor = $width / $imagesize[0];
                        $height = $imagesize[1] * $factor;
                    }
                    $thumb->adaptiveResize($width, $height);
                }
                $thumb->save($fileThumb);
            }

            if ($content) {
                header("Cache-Control: private, max-age=1209600");
                header("Expires: " . date(DATE_RFC822,strtotime(" 15 day")));
                $imagen = 'uploads/_thumb/' . $file;
                $fp = fopen($imagen, "r");
                $etag = md5(serialize(fstat($fp)));
                fclose($fp);
                header('Etag: ' . $etag);
                if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime($imagen) || trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)) {
                    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($imagen)) . ' GMT', true, 304);
                    exit();
                } else {
                    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($imagen)) . ' GMT');
                    switch ($extension) {
                        case 'jpg':
                            header('Content-type: image/jpeg');
                            break;
                        case 'gif':
                            header('Content-type: image/gif');
                            break;
                        case 'png':
                            header('Content-type: image/png');
                            break;
                    }
                    readfile($imagen);
                }
                exit;
            }
            
            if ($baseUrl == null) {
                $src = $this->view->baseUrl('uploads/_thumb/' . $file);
            } else {
                $src = rtrim(rtrim($baseUrl, '/'), "\'") . '/uploads/_thumb/' . $file;
            }
            
        } catch (Exception $e) {
             
        }

        return $src;
    }
}
