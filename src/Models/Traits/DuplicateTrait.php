<?php
namespace Common\Models\Traits;

use Illuminate\Database\Eloquent\Collection;

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
}