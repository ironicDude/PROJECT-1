<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;

class OnPhoneOrder extends Order
{
    use HasFactory;

    use SingleTableInheritanceTrait;
    protected static $singleTableType = 'Phonely';
    protected static $persisted = [];

}
