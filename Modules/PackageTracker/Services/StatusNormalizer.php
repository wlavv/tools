<?php

namespace Modules\PackageTracker\Services;

class StatusNormalizer
{
    public function normalize(?string $status): string
    {
        $value = strtolower(trim((string) $status));

        if ($value === '') {
            return 'unknown';
        }

        $direct = [
            'pre-transit' => 'label_created',
            'pre_transit' => 'label_created',
            'transit' => 'in_transit',
            'in_transit' => 'in_transit',
            'delivered' => 'delivered',
            'failure' => 'delivery_failed',
            'unknown' => 'unknown',
            'mp' => 'label_created',
            'mv' => 'in_transit',
            'it' => 'in_transit',
            'od' => 'out_for_delivery',
            'd' => 'delivered',
            'x' => 'exception',
            'manifested' => 'label_created',
            'adopted_at_source_branch' => 'collected',
            'sent_from_source_branch' => 'in_transit',
            'out_for_delivery' => 'out_for_delivery',
            'ready_to_pickup' => 'out_for_delivery',
            'collected_from_machine' => 'delivered',
            'returned_to_sender' => 'returned',
        ];

        if (isset($direct[$value])) {
            return $direct[$value];
        }

        return match (true) {
            str_contains($value, 'label') || str_contains($value, 'created') || str_contains($value, 'manifest') || str_contains($value, 'announced') || str_contains($value, 'preavis') => 'label_created',
            str_contains($value, 'collect') || str_contains($value, 'picked') || str_contains($value, 'recolh') || str_contains($value, 'recog') || str_contains($value, 'adopted') => 'collected',
            str_contains($value, 'transit') || str_contains($value, 'route') || str_contains($value, 'sorting') || str_contains($value, 'transport') || str_contains($value, 'exped') || str_contains($value, 'hub') => 'in_transit',
            str_contains($value, 'out for delivery') || str_contains($value, 'delivery today') || str_contains($value, 'distribui') || str_contains($value, 'reparto') || str_contains($value, 'ready to pickup') || str_contains($value, 'pickup') => 'out_for_delivery',
            str_contains($value, 'deliver') || str_contains($value, 'signed') || str_contains($value, 'entreg') || str_contains($value, 'livré') || str_contains($value, 'livre') || str_contains($value, 'collected from machine') => 'delivered',
            str_contains($value, 'failed') || str_contains($value, 'attempt') || str_contains($value, 'ausente') || str_contains($value, 'no entreg') || str_contains($value, 'falha') => 'delivery_failed',
            str_contains($value, 'exception') || str_contains($value, 'problem') || str_contains($value, 'held') || str_contains($value, 'incident') || str_contains($value, 'retido') || str_contains($value, 'customs') => 'exception',
            str_contains($value, 'return') || str_contains($value, 'devol') || str_contains($value, 'retour') => 'returned',
            str_contains($value, 'cancel') || str_contains($value, 'anulado') || str_contains($value, 'annul') => 'cancelled',
            $value === 'pending' => 'pending',
            default => 'unknown',
        };
    }
}
