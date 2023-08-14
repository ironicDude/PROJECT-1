<?php

namespace App\Models;

use App\Events\MinimumStockLevelExceeded;
use App\Exceptions\EmptyOrderException;
use App\Exceptions\InShortageException;
use App\Exceptions\NoPrescriptionsException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PrescriptionRequiredException;
use App\Exceptions\ProductAlreadyAddedException;
use App\Exceptions\ProductNotAddedException;
use App\Exceptions\QuantityExceededOrderLimitException;
use App\Notifications\MinimumStockLevelExceededNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;

class InStoreOrder extends Order
{
    use HasFactory;
    use SingleTableInheritanceTrait;
    protected static $singleTableType = 'Storely';
    protected static $persisted = [];


    
}
