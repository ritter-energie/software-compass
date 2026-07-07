<?php

declare(strict_types=1);

namespace App\Shared\Support;

/** Resolves translated labels for governance review status values. */
final class GovernanceStatusLabel {
    public static function from(string $status): string {
        return match ($status) {
            'open' => Translator::translate('governance.status.open'),
            'in_progress' => Translator::translate('governance.status.in_progress'),
            'approved' => Translator::translate('governance.status.approved'),
            'rejected' => Translator::translate('governance.status.rejected'),
            'needs_changes' => Translator::translate('governance.status.needs_changes'),
            default => $status,
        };
    }
}
