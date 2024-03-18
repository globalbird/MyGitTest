<?php

namespace App\Models;

use CodeIgniter\Model;


class Pages extends Model
{
    protected $table            = 'posts';
    protected $primaryKey       = 'id';
    protected $protectFields    = true;
    protected $allowedFields    = [];

    protected bool $allowEmptyInserts = false;

}
