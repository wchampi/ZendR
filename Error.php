<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Error
{
    public static function prepareMessageModel($model, $code, $message)
    {
        if (!($model instanceof ZendR_Sf_Record)) {
            throw new Exception('Model is not instance of ZendR_Sf_Record');
        }

        if (strpos($message, 'Duplicate') !== false) {
            if (strpos($message, '_idx') !== false) {
                $message = 'Registro Duplicado';
            } else {
                $messageArr = explode("'", $message);
                $atributo = self::_parseAtributo(isset($messageArr[3]) ? $messageArr[3] : '');
                $message = $atributo . ' (' . $messageArr[1] . ') ya se encuentra registrado';
            }
        }
        
        if (strpos($message, 'delete') !== false && strpos($message, 'parent') !== false) {
            $registrosRelacionados = '<br />';
            foreach ($model->getTable()->getRelations() as $relation) {
                if ($relation instanceof  Doctrine_Relation_ForeignKey) {
                    $getRelation = 'get' . ucfirst($relation->getAlias());
                    if ($model->$getRelation()->count() > 0 && !$relation->isCascadeDelete()) {
                        $registrosRelacionados .= '<br /> &nbsp;&nbsp;&nbsp;* <strong>'
                            . $relation->getAlias() . '</strong> Relacionados';
                    }
                }
            }
            $message = ' No se puede eliminar <strong>' . get_class($model) . '</strong> ya que tiene: '
                . $registrosRelacionados;
        }

        return $message;
    }

    private static function _parseAtributo($message)
    {
        return ucwords(str_replace('_', ' ', $message));
    }
}