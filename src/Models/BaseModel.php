<?php

namespace Common\Models;

use Common\Models\Traits\BaseTrait;
use Common\Models\Traits\DuplicateTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use Sofa\Eloquence\Subquery;

/**
 * Class BaseModel
 */
class BaseModel extends Model
{
    use BaseTrait, DuplicateTrait;

    protected $connection = 'mysql';

    public function __construct(array $attributes = [])
    {
        $this->table = config('database.connections.mysql.database') . '.' . $this->table;
        parent::__construct($attributes);
    }

    /**
     * @return string
     */
    public function getTableWithoutPrefix(): string
    {
        $table = $this->getTable();
        $explodes = explode('.', $table);

        if (count($explodes) === 2) {
            return end($explodes);
        }

        return $table;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getModelRelationshipMethods()
    {
        //can define this at class level
        $relationshipMethods = [
            'hasMany',
            'hasOne',
            'belongsTo',
            'belongsToMany',
        ];

        $modelClass = static::class;
        $reflector = new ReflectionClass($modelClass);
        $path = $reflector->getFileName();
        //lines of the file
        $lines = file($path);
        $methods = $reflector->getMethods();
        $relations = [];
        foreach ($methods as $method) {
            //if its a concrete class method
            if ($method->class == $modelClass) {
                $start = $method->getStartLine();
                $end = $method->getEndLine();
                //loop through lines of the method
                for($i = $start-1; $i<=$end-1; $i++) {
                    // look for text between -> and ( assuming that its on one line
                    preg_match('~\->(.*?)\(~', $lines[$i], $matches);
                    // if there is a match
                    if (count($matches)) {
                        //loop to check if the found text is in relationshipMethods list
                        foreach ($matches as $match) {
                            // if so add it to the output array
                            if (in_array($match, $relationshipMethods)) {
                                $relations[] = [
                                    //function name of the relation definition
                                    'method_name' => $method->name,
                                    //type of relation
                                    'relation' => $match,
                                    //related Class name
                                    'related' => (preg_match('/'.$match.'\((.*?),/', $lines[$i], $related) == 1) ? $related[1] : null,
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $relations;
    }
}