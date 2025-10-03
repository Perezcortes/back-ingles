<?php

// entities/SessionUser.php

namespace App\Entities;

interface SessionUser {
    public const ID = 'id';
    public const IP = 'ip';
    public const ID_USER = 'id_user';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';
}