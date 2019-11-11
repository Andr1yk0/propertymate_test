<?php


namespace App\DataType;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EnumColumnType extends Type
{
    const ENUM_TYPE = 'columntype';
    const TYPE_TEXT = 'text';
    const TYPE_SELECT = 'select';
    const TYPE_SELECT_MULTIPLE = 'select_multiple';
    const TYPE_DATE = 'date';
    const TYPES = [
        self::TYPE_TEXT,
        self::TYPE_SELECT,
        self::TYPE_SELECT_MULTIPLE,
        self::TYPE_DATE];

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return self::ENUM_TYPE;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if(!in_array($value, self::TYPES)){
            throw new \InvalidArgumentException("Invalid column type");
        }
    }

    public function getName()
    {
        return self::ENUM_TYPE;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

}
