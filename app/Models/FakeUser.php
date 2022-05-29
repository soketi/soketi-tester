<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class FakeUser extends Authenticatable
{
    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * {@inheritdoc}
     */
    protected $keyType = 'string';

    /**
     * {@inheritdoc}
     */
    public $incrementing = false;
}
