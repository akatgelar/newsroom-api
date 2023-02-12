<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BlameableObserver
{
    /**
     * @param Model $model
     */
    public function creating(Model $model)
    {
        if (Auth::user()) {
            $model->created_by = Auth::user()->id;
            $model->updated_by = Auth::user()->id;
        }
    }

    /**
     * @param Model $model
     */
    public function updating(Model $model)
    {
        if (Auth::user()) {
            $model->updated_by = Auth::user()->id;
        }
    }
}
