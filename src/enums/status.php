<?php
namespace SCwriter\Enums;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class SCwriter_Status {
    const QUEUED = 'queued';
    const COMPLETED = 'completed';
    const IN_PROGRESS = 'in_progress';
    const FAILED = 'failed';
    const EXPIRED = 'expired';

    private static $statuses = null;

    private static function initializeStatuses() {
        if (self::$statuses === null) {
            self::$statuses = [
                self::QUEUED => esc_html( __('Queued', 'scwriter') ),
                self::COMPLETED => esc_html( __('Completed', 'scwriter') ),
                self::IN_PROGRESS => esc_html( __('In Progress', 'scwriter') ),
                self::FAILED => esc_html( __('Failed', 'scwriter') ),
                self::EXPIRED => esc_html( __('Expired', 'scwriter') ),
            ];
        }
    }

    public static function getName(string $status): string {
        self::initializeStatuses();
        return self::$statuses[$status] ?? '';
    }

    public static function getAllStatuses(): array {
        self::initializeStatuses();
        return self::$statuses;
    }
}