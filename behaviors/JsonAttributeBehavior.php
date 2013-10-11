<?php
/**
 * JsonAttributeBehavior class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-arbehaviors.behaviors
 */

/**
 * Active record behavior for handling JSON attributes.
 * @property CActiveRecord $owner
 */
class JsonAttributeBehavior extends CActiveRecordBehavior
{
    /**
     * @var array attributes that should be treated as JSON attributes.
     */
    public $attributes = array();

    /**
     * Actions to take before saving the owner of this behavior.
     * @param CModelEvent $event event parameter.
     */
    public function beforeSave($event)
    {
        foreach ($this->attributes as $name) {
            if (!empty($this->owner->$name)) {
                $this->owner->$name = $this->jsonEncodeAttribute($name);
            } else {
                $this->owner->$name = null;
            }
        }
    }

    /**
     * Actions to take after loading the owner of this behavior.
     * @param CEvent $event event parameter.
     */
    public function afterFind($event)
    {
        foreach ($this->attributes as $name) {
            $this->owner->$name = $this->jsonDecodeAttribute($name);
        }
    }

    /**
     * JSON encodes the given attribute on the owner of this behavior
     * @param string $name name of the attribute
     * @param int $options option bitmask.
     * @return string the encoded value.
     */
    public function jsonEncodeAttribute($name, $options = 0)
    {
        return json_encode($this->owner->$name, $options);
    }

    /**
     * JSON decodes the given attribute on the owner of this behavior.
     * @param string $name name of the attribute.
     * @param bool $assoc whether the result should be an associated array (default to true).
     * @param int $depth recursion depth.
     * @return string the decoded value.
     * @throws CException if the value cannot be decoded.
     */
    public function jsonDecodeAttribute($name, $assoc = true, $depth = 512)
    {
        $string = json_decode($this->owner->$name, $assoc, $depth);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CException(sprintf('Failed to decode JSON attribute "%s".', $name));
        }
        return $string;
    }
}