<?php

namespace App\Policies;

use App\Models\InStoreOrder;
use App\Models\User;

class InStoreOrderPolicy
{
   public function createInStoreOrder(User $user, InStoreOrder $inStoreOrder)
   {
        return $user->isEmployee();
   }

   public function manageInStoreOrder(User $user, InStoreOrder $inStoreOrder)
   {
        return $user->isEmployee() && $inStoreOrder->employee_id == $user->id;
   }
}
