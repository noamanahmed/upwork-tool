<?php
namespace App\Enums;

enum UserTypeEnum: int {
    use BaseEnum;

    case SUPER_ADMIN = 1;
    case ADMIN = 1000;
    case EMPLOYEE = 2000;
    case READ_ONLY = 5000;
}
