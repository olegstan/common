<?php
namespace Common\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait DuplicateTrait
 * @package App\Models\Traits
 */
trait DuplicateTrait
{
    /**
     * @param $to
     */
    public function duplicateRelations($to)
    {
        foreach ($this->getRelations() as $relationName => $relation)
        {
            $objects = $this->{$relationName};

            if($objects !== null)
            {
                if ($objects instanceof Collection)
                {
                    foreach ($objects as $object)
                    {
                        $newObject = $object->replication($relationName, $to);

                        $object->duplicateRelations($newObject);
                    }
                } else {
                    $object = $objects;
                    $newObject = $object->replication($relationName, $to);

                    $object->duplicateRelations($newObject);
                }
            }
        }
    }

    /**
     * @param $relationName
     * @param $to
     * @return mixed
     */
    private function replication($relationName, $to)
    {
        $newRelation = $this->replicate();

        return $to->{$relationName}()->create($newRelation->toArray());
    }

    /**
     * @param array $relations
     * @return \Common\Models\BaseModel
     */
    public function replicateWithRelationsForProcessing(array $relations)
    {
        // Создаем копию текущей модели
        $newModel = $this->replicate();

        // Парсим связи и копируем их
        foreach ($relations as $relation) {
            $this->replicateRelation($newModel, $relation);
        }

        return $newModel;
    }

    /**
     * @param Model $newModel
     * @param $relation
     */
    protected function replicateRelation(Model $newModel, $relation)
    {
        $relationParts = explode('.', $relation);
        $mainRelation = array_shift($relationParts);
        $nestedRelations = implode('.', $relationParts);

        $relatedItems = $this->$mainRelation;

        if ($relatedItems instanceof Collection) {
            $newCollection = new Collection();
            foreach ($relatedItems as $relatedItem) {
                $newRelatedItem = $relatedItem->replicate();
                if ($nestedRelations) {
                    $relatedItem->replicateRelation($newRelatedItem, $nestedRelations);
                }
                $newCollection->push($newRelatedItem);
            }
            $newModel->setRelation($mainRelation, $newCollection);
        } elseif ($relatedItems instanceof Model) {
            $newRelatedItem = $relatedItems->replicate();
            if ($nestedRelations) {
                $relatedItems->replicateRelation($newRelatedItem, $nestedRelations);
            }
            $newModel->setRelation($mainRelation, $newRelatedItem);
        }
    }
}