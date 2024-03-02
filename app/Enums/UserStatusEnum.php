<?php
namespace App\Enums;

enum UserStatusEnum: int {
    use BaseEnum;

    case BLOCKED = 9999999;
    case EMAIL_UNVERIFIED = 1000;
    case ACTIVE = 800;
    case INACTIVE = 500;

    public static function getPrimaryColorTypes() : Array {
        return [
            self::INACTIVE,
        ];
    }

    public static function getInfoColorTypes() : Array {
        return [
            self::EMAIL_UNVERIFIED,
        ];
    }
    public static function getSuccessColorTypes() : Array {
        return [
            self::ACTIVE,
        ];
    }
    public static function getErrorColorTypes() : Array {
        return [
            self::BLOCKED,
        ];
    }
}
