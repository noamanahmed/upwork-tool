<?php
namespace App\Enums;

enum ProposalStatusEnum: int {
    use BaseEnum;

    case ACCEPTED = 1000;
    case DECLINED = 2000;
    case WITHDRAWN = 3000;
    case OFFERED = 4000;
    case ACTIVATED = 5000;
    case ARCHIVED = 6000;
    case HIRED = 7000;
    case PENDING = 8000;

    public static function getPrimaryColorTypes() : Array {
        return [
            self::ACCEPTED,
        ];
    }

    public static function getInfoColorTypes() : Array {
        return [
            self::ACCEPTED,
        ];
    }
    public static function getSuccessColorTypes() : Array {
        return [
            self::ACCEPTED,
        ];
    }
    public static function getErrorColorTypes() : Array {
        return [
            self::ACCEPTED,
        ];
    }
}
