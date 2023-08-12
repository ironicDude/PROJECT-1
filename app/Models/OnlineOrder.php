<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;

class OnlineOrder extends Order
{
    use HasFactory;
    use SingleTableInheritanceTrait;
    protected static $singleTableType = 'Online';
    protected static $persisted = [];

}
