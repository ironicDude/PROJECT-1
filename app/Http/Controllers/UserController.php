<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //call the deactivate method of User on the use with this specific id
    public function deactivate(Request $request):void
    {
        $user_id = $request->input('user_id');
        $user = User::find($user_id);
        $user->deactivate();
    } //end of deactivate
    
}
