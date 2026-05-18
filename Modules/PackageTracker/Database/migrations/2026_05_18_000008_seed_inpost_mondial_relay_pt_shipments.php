<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private string $clientKey = 'bruno-fernandes';

    public function up(): void
    {
        if (! Schema::hasTable('package_tracker_shipments') || ! Schema::hasTable('package_tracker_carriers')) {
            return;
        }

        $this->ensureClient();
        $this->updateCarrierSettings();

        foreach ($this->rows() as $row) {
            $carrierId = DB::table('package_tracker_carriers')->where('code', $row['carrier_code'])->value('id');

            if (! $carrierId) {
                continue;
            }

            $match = [
                'carrier_id' => $carrierId,
                'tracking_number' => $row['tracking_number'],
            ];

            $payload = [
                'client_key' => $this->clientKey,
                'external_reference' => $row['reference'],
                'order_reference' => $row['reference'],
                'destination_country' => 'PT',
                'status' => 'pending',
                'metadata' => json_encode([
                    'import_source' => 'inpost_mondial_relay_pt_seed_2026_05_18',
                    'firstname' => 'Bruno',
                    'lastname' => 'Fernandes',
                    'carrier_name' => $row['carrier_name'],
                    'language' => 'pt',
                ], JSON_THROW_ON_ERROR),
                'public_tracking_enabled' => true,
                'updated_at' => now(),
            ];

            $exists = DB::table('package_tracker_shipments')->where($match)->exists();

            DB::table('package_tracker_shipments')->updateOrInsert($match, $exists ? $payload : array_merge($payload, [
                'public_token' => Str::random(48),
                'created_at' => now(),
            ]));
        }

        DB::table('package_tracker_shipments')
            ->join('package_tracker_carriers', 'package_tracker_carriers.id', '=', 'package_tracker_shipments.carrier_id')
            ->whereIn('package_tracker_carriers.code', ['inpost', 'mondial_relay'])
            ->whereNull('package_tracker_shipments.destination_country')
            ->update([
                'package_tracker_shipments.destination_country' => 'PT',
                'package_tracker_shipments.updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }

    private function ensureClient(): void
    {
        if (! Schema::hasTable('package_tracker_clients')) {
            return;
        }

        $exists = DB::table('package_tracker_clients')->where('client_key', $this->clientKey)->exists();

        DB::table('package_tracker_clients')->updateOrInsert(['client_key' => $this->clientKey], [
            'name' => 'Bruno Fernandes',
            'public_token' => DB::table('package_tracker_clients')->where('client_key', $this->clientKey)->value('public_token') ?: Str::random(48),
            'is_active' => true,
            'updated_at' => now(),
            ...($exists ? [] : ['created_at' => now()]),
        ]);
    }

    private function updateCarrierSettings(): void
    {
        foreach (['inpost' => 'InPost', 'mondial_relay' => 'Mondial Relay'] as $code => $name) {
            $carrier = DB::table('package_tracker_carriers')->where('code', $code)->first();

            if (! $carrier) {
                continue;
            }

            $settings = json_decode((string) $carrier->settings, true) ?: [];
            $settings['country'] = $settings['country'] ?? 'PT';
            $settings['language'] = $settings['language'] ?? 'pt';

            DB::table('package_tracker_carriers')
                ->where('code', $code)
                ->update([
                    'name' => $carrier->name ?: $name,
                    'settings' => json_encode($settings, JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
        }
    }

    private function rows(): array
    {
        return [
            ['reference' => 'ARMZWNGVV', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099657'],
            ['reference' => 'CZGAVABFD', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099591'],
            ['reference' => 'OTNXHKAOV', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099163'],
            ['reference' => 'DUNIQOGOW', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099117'],
            ['reference' => 'QFRUUMRVA', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099162'],
            ['reference' => 'EWQJZRPFY', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099448'],
            ['reference' => 'JVFUKBSAE', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099178'],
            ['reference' => 'ALHXYGGPH', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099653'],
            ['reference' => 'GFLAGVFBQ', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099377'],
            ['reference' => 'LKMOTJDRL', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099327'],
            ['reference' => 'NOVGDHMJZ', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099434'],
            ['reference' => 'LCWQLNUBI', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099301'],
            ['reference' => 'DZWARQREQ', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099113'],
            ['reference' => 'DQHDCRGJA', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099317'],
            ['reference' => 'YMMLZWABT', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099166'],
            ['reference' => 'RIJKVJAIJ', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099432'],
            ['reference' => 'JJGLLHBVG', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099587'],
            ['reference' => 'VQVZYCMDM', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099125'],
            ['reference' => 'DDAPHXVDC', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099427'],
            ['reference' => 'MQZSTUREH', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96099324'],
            ['reference' => 'FSBDRSNWI', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96098873'],
            ['reference' => 'ZHAQBQHRY', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96098754'],
            ['reference' => 'MQALJTGLK', 'carrier_code' => 'inpost', 'carrier_name' => 'inPost', 'tracking_number' => '96098785'],
            ['reference' => 'DBBRCEIIO', 'carrier_code' => 'inpost', 'carrier_name' => 'inPost', 'tracking_number' => '96098818'],
            ['reference' => 'EALVNPUAK', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96098784'],
            ['reference' => 'TRBXCUDUO', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96098713'],
            ['reference' => 'SVMXICELO', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96098412'],
            ['reference' => 'KOEFDMCCV', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96098398'],
            ['reference' => 'VNYLXDGQL', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96098096'],
            ['reference' => 'CKAOKEWOJ', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96097947'],
            ['reference' => 'RIGLSGQSN', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96098111'],
            ['reference' => 'EZMRHJCLQ', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96098099'],
            ['reference' => 'HUCHIFYJR', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96097916'],
            ['reference' => 'NWEGSKWZY', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96097559'],
            ['reference' => 'VUAKBZMTO', 'carrier_code' => 'mondial_relay', 'carrier_name' => 'Mondial Relay', 'tracking_number' => '96097562'],
        ];
    }
};
