<?php
namespace SCwriter\Enums;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class SCwriter_Frequency {
    const NONE = 'none';
    const HOURLY = 'hourly';
    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const BIWEEKLY = 'biweekly';
    const MONTHLY = 'monthly';
    
    public static function getNames() : array {
        $frequencies = [
            self::NONE => __('None', 'scwriter'),
            self::HOURLY => __('Hourly', 'scwriter'),
            self::DAILY => __('Daily', 'scwriter'),
            self::WEEKLY => __('Weekly', 'scwriter'),
            self::BIWEEKLY => __('Biweekly', 'scwriter'),
            self::MONTHLY => __('Monthly', 'scwriter'),
        ];
        return $frequencies;
    }

    public static function getName( string $frequency ) : string {
        $frequencies = self::getNames();

        return $frequencies[$frequency] ?? '';
    }
}