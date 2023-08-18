<?php

namespace App\Policies;

use App\Models\OnlineOrder;
use App\Models\User;

class OnlineOrderPolicy
{
   public function manage(User $user, OnlineOrder $onlineOrder)
   {
        return $user->id == $onlineOrder->customer_id;
   }

   public function dispatch(User $user, OnlineOrder $onlineOrder)
   {
        return $user->id == $onlineOrder->employee_id;
   }

   public function reject(User $user, OnlineOrder $onlineOrder)
   {
        return $user->id == $onlineOrder->employee_id;
   }
}
