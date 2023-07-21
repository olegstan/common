<?php
namespace Common\Models\Traits\Catalog\Cbond;


trait CbondAttributeTrait
{
    /**
     * @param $value
     * @return mixed
     */
    public function getFaceunitAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @param $data
     * @return void
     */
    public function setFaceunitAttribute($data)
    {
        if (isset($data)) {
            $this->attributes['faceunit'] = json_encode($data);
        }else{
            $this->attributes['faceunit'] = $data;
        }
    }
}