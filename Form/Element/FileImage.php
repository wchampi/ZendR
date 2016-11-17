<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Element_FileImage extends ZendR_Form_Element_File
{
    private $_optionsJq = array(
            'bgiframe'  => 'true',
            'autoOpen'  => 'false',
            'width'     => '"auto"',
        );

    private $_title = '';

    private $_width     = null;
    private $_height    = null;
    private $_maxWidth  = null;
    private $_maxHeight = null;

    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function getImageName()
    {
        return $this->_fileName;
    }

    public function addOptionJq($key, $value)
    {
        $this->_optionsJq[$key] = $value;
        return $this;
    }

    public function removeOptionJq($key)
    {
        if (isset ($this->_optionsJq[$key])) {
            unset ($this->_optionsJq[$key]);
        }
        return $this;
    }

    public function setOptionsJq(array $options)
    {
        $this->_optionsJq = $options;
        return $this;
    }
    
    public function render(Zend_View_Interface $view = null)
    {
        $viewDefault = new Zend_View();

        $destination    = realpath($this->getDestination());
        $baseDir        = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
        $destinationUrl = str_replace('\\', '/', str_replace($baseDir, '', $destination));

        $options = array();
        foreach ($this->_optionsJq as $key => $value) {
            $options[] = $key . ':' . $value;
        }
        
        $html = parent::render($view);
        if ($this->_fileName != '') {
            $html = '<script type="text/javascript">//<![CDATA[' . "\n"
                . '$(function() {'
                    . '$("#' . $this->getId() . '-image").dialog({'
                        . implode(',', $options)
                    . '});'
                    . '$("#' . $this->getId() . '").after('
                        . '\'<a href="javascript:void(0)" id="' . $this->getId() . '-a-href" >'
                            . '<img src="' . $viewDefault->baseUrl('zendr/css/graphics/ico_search.gif') . '" border="0" alt="verImagen" width="15px" \/>'
                            . '&nbsp;&nbsp;&nbsp;ver Imagen'
                        . '<\/a>\''
                    . ');'
                    . '$("#' . $this->getId() . '-a-href").click(function () {'
                        . '$("#' . $this->getId() . '-image").dialog("open");'
                    . '});'
                . '});'
            . '//]]></script>'
            . $html
            . '<div id="' . $this->getId() . '-image" style="display:none" title="' . $this->_title . '">'
                . '<img id="' . $this->getId() . '-img" src="' . $viewDefault->baseUrl($destinationUrl . '/' . $this->_fileName) . '" alt="' . $this->_fileName . '" />'
            . '</div>';
        }

        return $html;
    }

    public function resizing($newImage, $newWidth, $newHeight, $calidad = 85)
    {
        $image = $this->getFileName(null, true);

        if (!file_exists($image)) {
            return false;
        }

        $size   = getimagesize($image);
        $width  = $size[0];
        $height = $size[1];

        $newValue = $newImage;
        $newImage = $this->getDestination() . '/' . $newImage;
		if(!file_exists($newImage)){
			if($newWidth == 0 || $newWidth == ""){
				$newWidth = round($width * ($newHeight / $height));
			}
			if($newHeight == 0 || $newWidth == ""){
				$newHeight = round($height * ($newWidth / $width));
			}
			$width = $newWidth;
			$height = $newHeight;
			switch($this->getExtension()){
				case "gif":
					$img = imagecreatefromgif($image);
 					$thumb = imagecreatetruecolor($newWidth,$newHeight);
 					imagecopyresampled($thumb,$img,0,0,0,0,$newWidth, $newHeight,imagesx($img),imagesy($img));
 					imagegif($thumb,$newImage);
				break;
				case "png":
					$img = imagecreatefrompng($image);
 					$thumb = imagecreatetruecolor($newWidth,$newHeight);
 					imagecopyresampled($thumb,$img,0,0,0,0,$newWidth, $newHeight,imagesx($img),imagesy($img));
 					imagegif($thumb,$newImage);
				break;
				case "jpg":
					$img = imagecreatefromjpeg($image);
 					$thumb = imagecreatetruecolor($newWidth,$newHeight);
 					imagecopyresampled($thumb,$img,0,0,0,0,$newWidth, $newHeight,imagesx($img),imagesy($img));
 					imagejpeg($thumb,$newImage,$calidad);
				break;
				case "jpeg":
					$img = imagecreatefromjpeg($image);
 					$thumb = imagecreatetruecolor($newWidth,$newHeight);
 					imagecopyresampled($thumb,$img,0,0,0,0,$newWidth, $newHeight,imagesx($img),imagesy($img));
 					imagejpeg($thumb,$newImage,$calidad);
				break;
			}

            $this->_value = $newValue;
            return true;
		} 
        return false;
	}

    /**
     *
     * @param int $width
     * @param int $height
     */
    public function setFixedWidthHeight($width, $height)
    {
        $this->_width   = $width;
        $this->_height  = $height;
        return $this;
    }

    /**
     *
     * @param int $maxWidth
     * @param int $maxHeight 
     */
    public function setMaxWidthHeight($maxWidth, $maxHeight)
    {
        $this->_maxWidth   = $width;
        $this->_maxHeight  = $height;
        return $this;
    }

    public function  isValid($value, $context = null)
    {
        if (parent::isValid($value, $context)) {
            if ($this->getValue() != '' && $this->getFileName(null)) {
                if ($this->_width !== null) {
                    list ($width, $height) = getimagesize($this->getDestination() . '/' . $this->getValue());

                    if ($this->_width != $width || $this->_height != $height) {
                        $this->_messages[] = 'Tamaño debe ser ' . $this->_width . 'px x ' . $this->_height . 'px';
                        return false;
                    }
                }
                
                if ($this->_maxWidth !== null) {
                    list ($width, $height) = getimagesize($this->getDestination() . '/' . $this->getValue());
                    if ($width > $this->_maxWidth || $height > $this->_maxHeight) {
                        $this->_messages[] = 'Máximo permitido: ' . $this->_maxWidth . 'px x ' . $this->_maxHeight . 'px';
                        return false;
                    }
                }
            }
                    
            return true;
        }

        return false;
    }
}