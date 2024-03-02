<?php
namespace App\Enums;

use Illuminate\Support\Str;
use ReflectionEnum;

trait BaseEnum {

    const DEFAULT_COLOR_TYPE = 'success';

    public static function toValues(): array {
        return array_values(static::toArray());
    }

    public static function toArray(): array {
        $output = [];
        foreach( (new ReflectionEnum(static::class))->getCases() as $case)
        {
            $output[] = $case->getValue();
        }
        return $output;
    }

    public static function dropdown(): array
    {
       $array = static::toArray();
       $output = [];
       foreach($array as $key => $enum)
       {
            $output[] = [
                'key' => (int) $enum->value,
                'value' => static::translate(str_replace('_',' ',$enum->name)),
                'color' => self::getColorType($enum->value)
            ];
       }
       return $output;
    }
    public static function fromName(string $name): string
    {
        foreach (static::cases() as $status) {
            if( $name === $status->name ){
                return $status->value;
            }
        }
        throw new \ValueError("$name is not a valid backing value for enum " . self::class );
    }

    public static function getKeyfromValue(int $value): string
    {

        foreach (self::cases() as $status) {
            if( $value === $status->value ){
                return static::translate($status->name);
            }
        }
        throw new \ValueError("$value is not a valid backing value for enum " . self::class );
    }
    public static function getLowerCaseKeyfromValue(int $value): string
    {
       return strtolower(static::getKeyfromValue($value));
    }

    public static function getPrettyKeyfromValue(int $value): string
    {
       return Str::title(str_replace('_',' ',strtolower(static::getKeyfromValue($value))));
    }

    public static function getModuleName() : string
    {
        $module = str(str_replace(__NAMESPACE__ . '\\', '', __CLASS__))->snake();
        $firstUnderscorePos = strpos($module, '_');
        return str(substr($module, 0, $firstUnderscorePos))->plural();
    }
    public static function getEnumType() : string
    {
        $module = str(str_replace(__NAMESPACE__ . '\\', '', __CLASS__))->snake();
        $firstUnderscorePos = strpos($module, '_') + 1;
        $enum = str_replace(substr($module, 0, $firstUnderscorePos),'',$module);
        $lastUnderscorePos = strrpos($enum, '_');
        $type = str_replace(substr($enum, $lastUnderscorePos, strlen($enum)),'',$enum);
        return $type;
    }

    public static function translate(string $key) : string
    {
        $translations = app('translations');
        $translationKey = 'dashboard.modules.'.static::getModuleName().'.enums.'.static::getEnumType().'.'.str($key)->lower()->snake();
        if(empty($translations)) return $key;
        return $translations->firstWhere('key',$translationKey)->value ?? $translationKey;
    }
    public static function getDefaultColorTypes() : Array {
        return [

        ];
    }
    public static function getPrimaryColorTypes() : Array {
        return self::toValues();
    }
    public static function getSecondaryColorTypes() : Array {
        return [

        ];
    }
    public static function getSuccessColorTypes() : Array {
        return [

        ];
    }
    public static function getInfoColorTypes() : Array {
        return [

        ];
    }
    public static function getWarningColorTypes() : Array {
        return [

        ];
    }
    public static function getErrorColorTypes() : Array {
        return [

        ];
    }
    public static function getColorType(int $value) : string
    {
        if(static::matchesColorType($value,static::getDefaultColorTypes())) return 'default';
        if(static::matchesColorType($value,static::getPrimaryColorTypes())) return 'primary';
        if(static::matchesColorType($value,static::getSecondaryColorTypes())) return 'secondary';
        if(static::matchesColorType($value,static::getSuccessColorTypes())) return 'success';
        if(static::matchesColorType($value,static::getInfoColorTypes())) return 'info';
        if(static::matchesColorType($value,static::getWarningColorTypes())) return 'warning';
        if(static::matchesColorType($value,static::getErrorColorTypes())) return 'error';

        return static::DEFAULT_COLOR_TYPE;
    }

    public static function matchesColorType($value,$arrayOfEnums = [])
    {
        foreach($arrayOfEnums as $enum)
        {

            if($enum->value === $value) return true;
        }
        return false;
    }
}
