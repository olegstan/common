<?php

namespace Common\Models\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait BaseTrait
{
    /**
     * @var bool
     */
    public static $uniqueHash = true;

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    /**
     * @param $field
     * @return int
     */
    public static function getUniqueId($field)
    {
        $id = static::max($field);

        if($id)
        {
            return $id + 1;
        }

        return 1000;
    }

    /**
     * @param $field
     * @param int $count
     * @return string
     */
    public static function getUniqueHash($field, $count = 32)
    {
        if (self::$uniqueHash) {
            $hash = Str::random($count);
            $item = static::where($field, $hash)->first();
            if ($item) {
                return self::getUniqueHash($field, $count);
            } else {
                return $hash;
            }
        } else {
            $item = static::orderBy('id', 'desc')->limit(1)->first();
            if ($item)
            {
                return $field . '_' . $item->id;
            }

            return $field . '_' . 1;
        }
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted_at !== null;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->is_active ? true : false;
    }

    /**
     * @param $attr
     * @return bool
     */
    public function hasAttribute($attr)
    {
        return array_key_exists($attr, $this->attributes);
    }

    /**
     * @param $field
     * @param array $values
     */
    public function fieldSwitch($field, $values = [0, 1])
    {
        if ($this->{$field} === $values[0]) {
            $this->update([$field => $values[1]]);
        } else {
            $this->update([$field => $values[0]]);
        }
    }

    /**
     *
     * @param array $options
     */
    public function saveQuietly(array $options = [])
    {
        $dispatcher = self::getEventDispatcher();

        // disabling the events
        self::unsetEventDispatcher();

        // perform the operation you want
        $this->save();

        // enabling the event dispatcher
        self::setEventDispatcher($dispatcher);
    }

    public function newQuery()
    {
        return parent::newQuery();
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where($this->table . '.is_active', 1);
    }

    /**
     * @param $query
     * @param $value
     * @param $field
     * @param $condition
     */
    public function scopeWhereAbs($query, $field, $condition, $value)
    {
        $query->whereRaw('ABS(`' . $field . '`) ' . $condition . ' ?', $value);
    }

    /**
     * @param $field
     * @return int
     */
    public function getDiffSecond($field)
    {
        return Carbon::now()->diffInSeconds($this->{$field});
    }

    /**
     * @param $value
     * @param int $round
     * @return float
     */
    public function round($value, $round = 10)
    {
        return round($value, $round);
    }

    /**
     * @param string $related
     * @param null $foreignKey
     * @param null $ownerKey
     * @param null $relation
     * @return BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        return parent::belongsTo($related, $foreignKey, $ownerKey, $relation)->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * @param $query
     * @param $build
     * @param $alias
     */
    public function scopeAddSubQuery($query, $build, $alias)
    {
        $sub = new Subquery(
            $build,
            $alias
        );
        $query->addSelect($sub)->addBinding($sub->getBindings(), 'select');
    }

    /**
     * @param $query
     * @param $date
     * @param string $field
     * @param string $format
     */
    public function scopeDateStart($query, $date, $field = 'created_at', $format = 'd.m.Y')
    {
        $query->whereDate($field, '>=', $this->convertToDate($date, $format)->endOfDay());
    }

    /**
     * @param $query
     * @param $date
     * @param string $field
     * @param string $format
     */
    public function scopeDateEnd($query, $date, $field = 'created_at', $format = 'd.m.Y')
    {
        $query->whereDate($field, '<=', $this->convertToDate($date, $format)->endOfDay());
    }

    /**
     * @param $query
     * @param array $filters
     * @param string $field
     * @param string $format
     * @return mixed
     */
    public function scopePeriod($query, array $filters, $field = 'created_at', $format = 'd.m.Y')
    {
        if (! (count($filters) >= 2)) {
            return $query;
        }

        if (key_exists('startDate', $filters) && key_exists('endDate', $filters)) {
            list($startDate, $endDate) = [$filters['startDate'], $filters['endDate']];
        } else {
            list($startDate, $endDate) = $filters;
        }

        if ($startDate && $endDate) {
            $query->whereDate($field, '<=', $this->convertToDate($endDate, $format)->endOfDay())
                ->whereDate($field, '>=', $this->convertToDate($startDate, $format)->startOfDay());
        }

        return $query;
    }

    /**
     * @param string $time
     * @param string $format
     * @return Carbon
     */
    public static function convertToDate(string $time, string $format = 'Y-m-d'): Carbon
    {
        return Carbon::createFromFormat($format, $time);
    }

    /**
     * @param string $time
     * @param string $format
     * @return Carbon
     */
    public static function convertToDateTime(string $time, string $format = 'Y-m-d H:i:s'): Carbon
    {
        return Carbon::createFromFormat($format, $time);
    }

    /**
     * @param $field
     * @param int $length
     * @return string
     */
    public static function getUniqueHashImg($field, $length = 15)
    {
        return self::getUniqueHash($field, $length);
    }

    /**
     * @param $field
     * @param $value
     */
    public function setDate($field, $value)
    {
        if($value instanceof Carbon)
        {
            $this->attributes[$field] = $value;
        }else if(is_string($value) && !empty($value)){
            $this->attributes[$field] = Carbon::parse($value);
        }else if(is_null($value) && empty($value)){
            $this->attributes[$field] = null;
        }
    }

    /**
     * @param $key
     */
    public function unsetRelation($key)
    {
        $relations = $this->getRelations();
        if(isset($relations[$key]))
        {
            unset($relations[$key]);
            $this->setRelations($relations);
        }
    }
}