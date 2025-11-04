<?php

// entities/User.php

namespace App\Entities;

interface User {
    public const ID = 'id';
    public const FULL_NAME = 'full_name';
    public const OFFICE = 'office';
    public const EMAIL = 'email';
    public const PASSWORD = 'password';
    public const IS_PROFESSOR = 'is_professor';
    public const IS_LEVEL_COORDINATOR = 'is_level_coordinator';
    public const IS_ADMINISTRATOR = 'is_administrator';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';
}