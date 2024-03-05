<?php
namespace App\Enums;

enum UpWorkStatusEnum: int {
    use BaseEnum;

    case ACTIVE = 1000;
    case INACTIVE = 2000;

     public static function getPrimaryColorTypes() : Array {
        return [
            self::ACTIVE,
        ];
    }
    public static function getInfoColorTypes() : Array {
        return [
            self::INACTIVE,
        ];
    }
}
