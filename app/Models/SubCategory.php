<?php

namespace App\Models;

use CodeIgniter\Model;
use \Mberecall\CI_Slugify\SlugService;
use \Mberecall\Sluggable\CI_Slugify;

class SubCategory extends Model
{
    protected $DBGroup          = "default";
    protected $table            = 'sub_categories';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'name','slug','parent_cat','description','ordering'
    ];

    // Check from here to troubleshoot Alternative Slug Model Additions:: https://youtu.be/1v0kwsEboJo?t=1588
    // https://github.com/mberecall/ci4-slugify

    // protected $beforeInsert = ['setSlug'];
    // protected $beforeUpdate = ['setSlug'];

    // public function setSlug($data)
    // {
    //     $slugify = new CI_Slugify($this);
    //     $data = $slugify->getSlug($data,'name');
    //     return $data;
    // }


}
