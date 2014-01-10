<?php
/**
 * ClosureTreeBehavior class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2014-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-arbehaviors.behaviors
 */

/**
 * Active record behavior for handling closure trees.
 * @property CActiveRecord $owner
 */
class ClosureTreeBehavior extends CActiveRecordBehavior
{
    /**
     * @var string the name of the id attribute.
     */
    public $idAttribute = 'id';

    /**
     * @var string the name of the tree model.
     */
    public $treeClass;

    // todo: add runtime caching.

    /**
     * Returns the direct children for the owner.
     * @return CActiveRecord[] the children.
     */
    public function getChildren()
    {
        return $this->getBranch($this->owner->{$this->idAttribute});
    }

    /**
     * Returns the top level items for the owner.
     * @return CActiveRecord[] the items.
     */
    public function getRoot()
    {
        return $this->getBranch(0);
    }

    /**
     * Returns the direct children for the model with the given parent id.
     * @param int $parentId the parent id.
     * @return CActiveRecord[] the children.
     */
    public function getBranch($parentId)
    {
        $criteria = new CDbCriteria();
        $criteria->index = 'descendantId';
        $criteria->addCondition('ancestorId=:parentId AND depth=1');
        $criteria->params[':parentId'] = $parentId;
        $root = CActiveRecord::model($this->treeClass)->findAll($criteria);
        $children = $this->owner->findAllByPk(array_keys($root));
        return $children;
    }

    /**
     * Get the parent of a model.
     * @return CActiveRecord
     */
    public function getParent()
    {
        // Load the "current" level from the tree
        $currentLevel = CActiveRecord::model($this->treeClass)->findByAttributes(
            array('descendantId' => $this->owner->{$this->idAttribute}, 'depth' => 1)
        );

        if ($currentLevel->ancestorId > 0) {
            return CActiveRecord::model(get_class($this->owner))->findByPk($currentLevel->ancestorId);
        } else {
            return null;
        }
    }

    /**
     * Returns the parents for this node.
     * @param bool $includeSelf
     * @return CActiveRecord[] the parents.
     */
    public function getParents($includeSelf = true)
    {
        $parents = array();

        if ($includeSelf) {
            $parents[$this->owner->{$this->idAttribute}] = $this->owner;
        }

        // Try and find a parent
        $parent = $this->getParent();
        if ($parent instanceof CActiveRecord) {
            $parents[$parent->{$this->idAttribute}] = $parent;

            // Fetch any additional ancestry levels
            while ($nextParent = $parent->getParent()) {
                $parents[$nextParent->{$this->idAttribute}] = $nextParent;
                $parent = $nextParent;
            }
        }

        return array_reverse($parents, true);
    }
}