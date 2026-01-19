<?php

namespace App\Enums;

enum AccountTypeEnum: string
{
    case ASSET = 'Asset';
    case LIABILITY = 'Liability';
    case EQUITY = 'Equity';
    case INCOME = 'Income';
    case COGS = 'Cost of Goods Sold';
    case EXPENSE = 'Expense';

    public function code(): string
    {
        return match ($this) {
            self::ASSET => '1',
            self::LIABILITY => '2',
            self::EQUITY => '3',
            self::INCOME => '4',
            self::COGS => '5',
            self::EXPENSE => '6',
        };
    }

    public function accountClass(): AccountClassEnum
    {
        return match ($this) {
            self::ASSET, self::COGS, self::EXPENSE => AccountClassEnum::DEBIT,
            self::LIABILITY, self::EQUITY, self::INCOME => AccountClassEnum::CREDIT,
        };
    }
}
