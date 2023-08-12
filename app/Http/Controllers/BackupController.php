<?php

namespace App\Http\Controllers;

use App\Policies\BackupPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\CustomResponse;
class BackupController extends Controller
{
    use CustomResponse;
    public function runBackup()
    {
        if(Gate::denies('backup')){
            abort(404);
        }
        try{
            Artisan::call('backup:run');
        } catch(\Exception $e){
            return self::customResponse($e->getMessage(), null, 400);
        }
        return self::customResponse('backup run', true, 200);
    }
}
