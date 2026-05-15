<?php

namespace App\Constants;

class GeneralConst
{
    public const APP_NAME = 'Bulletinboard';
    // Roles
    public const ADMIN = 0;
    public const USER = 1;
    public const ROLES = [
        self::ADMIN => 'Admin',
        self::USER => 'User',
    ];
    // Lock
    public const UNLOCK = 0;
    public const LOCK = 1;
    public const LOCK_STATUS = [
        self::UNLOCK => 'Admin',
        self::LOCK => 'User',
    ];
    // post status
    public const NOT_ACTIVE = 0;
    public const ACTIVE = 1;
    public const POST_STATUS = [
        self::NOT_ACTIVE => 'Not Active',
        self::ACTIVE => 'Active'
    ];

    public const MAX_UPLOAD_SIZE = 5;

    public const UPLOAD_FILE_TYPES = ['jpg', 'jpeg', 'png'];

    public const MAX_EXCEL_UPLOAD_SIZE = 10;

    public const UPLOAD_EXCEL_FILE_TYPES = ['xlsx', 'xls'];

    public const IGNORED_TABLES = ['migrations', 'personal_access_tokens'];

    public const TABLE_COLUMN_TYPES_TO_HTML_INPUT = [
        'bigint' => 'number',
        'binary' => 'text',
        'boolean' => 'checkbox',
        'char' => 'text',
        'date' => 'date',
        'datetime' => 'date',
        'timestamp' => 'date',
        'decimal' => 'number',
        'double' => 'number',
        'enum' => 'select',
        'float' => 'number',
        'int' => 'number',
        'json' => 'textarea',
        'longtext' => 'textarea',
        'mediumtext' => 'textarea',
        'smallint' => 'number',
        'text' => 'textarea',
        'time' => 'time',
        'timestamp' => 'date',
        'tinyint' => 'number',
        'varchar' => 'text',
    ];

    public const SELECT_OPTIONS_COLUMNS = [
        'role' => self::ROLES,
        'lock_flg' => self::LOCK_STATUS,
        'status' => self::POST_STATUS,
    ];
}
