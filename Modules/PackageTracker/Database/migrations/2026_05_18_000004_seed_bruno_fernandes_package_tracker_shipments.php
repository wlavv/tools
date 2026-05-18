<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    private string $clientKey = 'bruno-fernandes';

    public function up(): void
    {
        if (! Schema::hasTable('package_tracker_shipments') || ! Schema::hasTable('package_tracker_carriers')) {
            return;
        }

        $this->ensureClient();

        foreach ($this->rows() as $row) {
            $carrierCode = $this->carrierCode($row['carrier_name']);
            $trackingNumber = trim($row['tracking_number']);

            // Excel-exported scientific notation loses the original tracking number.
            if ($trackingNumber === '' || str_contains(strtoupper($trackingNumber), 'E+')) {
                continue;
            }

            $carrierId = DB::table('package_tracker_carriers')
                ->where('code', $carrierCode)
                ->value('id');

            if (! $carrierId) {
                continue;
            }

            $match = [
                'carrier_id' => $carrierId,
                'tracking_number' => $trackingNumber,
            ];

            $payload = [
                'client_key' => $this->clientKey,
                'external_reference' => $row['reference'],
                'order_reference' => $row['reference'],
                'customer_email' => null,
                'destination_country' => 'PT',
                'status' => 'pending',
                'metadata' => json_encode([
                    'import_source' => 'bruno_fernandes_seed_2026_05_18',
                    'firstname' => $row['firstname'],
                    'lastname' => $row['lastname'],
                    'carrier_name' => $row['carrier_name'],
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
    }

    public function down(): void
    {
        if (! Schema::hasTable('package_tracker_shipments')) {
            return;
        }

        DB::table('package_tracker_shipments')
            ->where('client_key', $this->clientKey)
            ->whereIn('external_reference', array_values(array_unique(array_column($this->rows(), 'reference'))))
            ->delete();
    }

    private function ensureClient(): void
    {
        if (! Schema::hasTable('package_tracker_clients')) {
            return;
        }

        $exists = DB::table('package_tracker_clients')->where('client_key', $this->clientKey)->exists();

        DB::table('package_tracker_clients')->updateOrInsert(['client_key' => $this->clientKey], [
            'name' => 'Bruno Fernandes',
            'contact_email' => null,
            'public_token' => DB::table('package_tracker_clients')->where('client_key', $this->clientKey)->value('public_token') ?: Str::random(48),
            'is_active' => true,
            'theme' => null,
            'updated_at' => now(),
            ...($exists ? [] : ['created_at' => now()]),
        ]);
    }

    private function carrierCode(string $carrierName): string
    {
        return match (strtolower(trim($carrierName))) {
            'ups' => 'ups',
            'dpd' => 'dpd',
            'nacex' => 'nacex',
            'inpost' => 'inpost',
            'mondial relay' => 'mondial_relay',
            default => Str::slug($carrierName, '_'),
        };
    }

    private function rows(): array
    {
        $raw = <<<'TSV'
reference	firstname	lastname	carrier_name	tracking_number
UNITBORAV	Bruno	Fernandes	UPS	1Z72794RDK17027794
IHCTATPEC	Bruno	Fernandes	UPS	1Z72794RD912388776
ZKSEUBLMA	Bruno	Fernandes	DPD	09682493063435G
VEOKHXAES	Bruno	Fernandes	UPS	1Z72794RDK04044745
LOSOFAITG	Bruno	Fernandes	DPD	09682493063441K
ARMZWNGVV	Bruno	Fernandes	Mondial Relay	96099657
UELJLYAIL	Bruno	Fernandes	UPS	1Z72794RDK02661108
KQXHDSRHJ	Bruno	Fernandes	NACEX	7490/11248020
KQXHDSRHJ	Bruno	Fernandes	NACEX	7490/11248033
KXSZABWJU	Bruno	Fernandes	DPD	09682493063426I
YWPUAZTRB	Bruno	Fernandes	UPS	1Z72794RDK25042823
BWMQYPIXU	Bruno	Fernandes	UPS	1Z72794RDK34436257
CZGAVABFD	Bruno	Fernandes	Mondial Relay	96099591
SRWQRTIPQ	Bruno	Fernandes	UPS	1Z72794RDK05735069
OTNXHKAOV	Bruno	Fernandes	Mondial Relay	96099163
DUNIQOGOW	Bruno	Fernandes	Mondial Relay	96099117
NWMVTHWAT	Bruno	Fernandes	DPD	09682493063424M
DBLBUGKDY	Bruno	Fernandes	DPD	09682493063443G
LPBLMQHHG	Bruno	Fernandes	UPS	1Z72794RDK21385774
RJQCATUMQ	Bruno	Fernandes	UPS	1Z72794RDK03869679
OXUXIYIMN	Bruno	Fernandes	NACEX	7490/11248154
BSDBVHVZQ	Bruno	Fernandes	DPD	9.68249E+13
QFRUUMRVA	Bruno	Fernandes	Mondial Relay	96099162
DELLNFMBX	Bruno	Fernandes	UPS	1Z72794RDK30760207
ZFTIULTBM	Bruno	Fernandes	UPS	1Z72794RDK04632154
EWQJZRPFY	Bruno	Fernandes	Mondial Relay	96099448
JVFUKBSAE	Bruno	Fernandes	Mondial Relay	96099178
ALHXYGGPH	Bruno	Fernandes	Mondial Relay	96099653
WLAYLNBUT	Bruno	Fernandes	UPS	1Z72794RDK35493729
ZLXBKQZSM	Bruno	Fernandes	UPS	1Z72794RDK03869937
GFLAGVFBQ	Bruno	Fernandes	Mondial Relay	96099377
FMJSNHOXX	Bruno	Fernandes	DPD	09682493063425K
GRDOVVBLI	Bruno	Fernandes	DPD	09682493063442I
LKMOTJDRL	Bruno	Fernandes	Mondial Relay	96099327
TLTCSZSFA	Bruno	Fernandes	NACEX	7490/11248126
PMSWKJCSI	Bruno	Fernandes	UPS	1Z72794RDK03924886
BUFIXDHOO	Bruno	Fernandes	UPS	1Z72794RDK13579731
NOVGDHMJZ	Bruno	Fernandes	Mondial Relay	96099434
QJTGISYLB	Bruno	Fernandes	UPS	1Z72794RDK18279789
ZWOJQITLP	Bruno	Fernandes	DPD	09682493063437C
ZWOJQITLP	Bruno	Fernandes	DPD	09682493063438A
XLVIACEXD	Bruno	Fernandes	DPD	09682493063444E
HXWLXQGWI	Bruno	Fernandes	UPS	1Z72794RDK37549157
IUYRNOVHU	Bruno	Fernandes	UPS	1Z72794RD937455898
FAENTIAMX	Bruno	Fernandes	UPS	1Z72794RD906197216
QMWMRBNJF	Bruno	Fernandes	UPS	1Z72794RDK27582031
HDZECUDLH	Bruno	Fernandes	DPD	9.68249E+13
VJAIBZZGS	Bruno	Fernandes	NACEX	7490/11248054
VJAIBZZGS	Bruno	Fernandes	NACEX	7490/11248057
IUBPPOBPS	Bruno	Fernandes	UPS	1Z72794RDK00364004
NWUKWQJNR	Bruno	Fernandes	UPS	1Z72794RDK15428168
LCWQLNUBI	Bruno	Fernandes	Mondial Relay	96099301
DZWARQREQ	Bruno	Fernandes	Mondial Relay	96099113
DQHDCRGJA	Bruno	Fernandes	Mondial Relay	96099317
YMMLZWABT	Bruno	Fernandes	Mondial Relay	96099166
GAQYCSEHQ	Bruno	Fernandes	DPD	09682493063434I
RIJKVJAIJ	Bruno	Fernandes	Mondial Relay	96099432
KOILAXYLE	Bruno	Fernandes	UPS	1Z72794RDK37367844
SBZQEFIVV	Bruno	Fernandes	DPD	09682493063436E
AAHUEEIZF	Bruno	Fernandes	DPD	09682493063440M
ULYOKCVPB	Bruno	Fernandes	UPS	1Z72794RDK02871720
PHFQOOPIK	Bruno	Fernandes	UPS	1Z72794RD939697089
JJGLLHBVG	Bruno	Fernandes	Mondial Relay	96099587
VQVZYCMDM	Bruno	Fernandes	Mondial Relay	96099125
TQCBPMHED	Bruno	Fernandes	UPS	1Z72794RDK06985592
CFEPEZECX	Bruno	Fernandes	UPS	1Z72794RD923077304
DDAPHXVDC	Bruno	Fernandes	Mondial Relay	96099427
XQHRRCXED	Bruno	Fernandes	UPS	1Z72794RD909818209
RBYPOCVRK	Bruno	Fernandes	UPS	1Z72794RDK39330165
ZECZWMURT	Bruno	Fernandes	NACEX	7490/11248164
TOIDIFOYD	Bruno	Fernandes	UPS	1Z72794R0435957318
DXDXILGQW	Bruno	Fernandes	UPS	1Z72794RDK31383266
DKITDJEFD	Bruno	Fernandes	UPS	1Z72794RDK11229989
DYPPAVXVT	Bruno	Fernandes	UPS	1Z72794RDK00262543
DNGZNVDNU	Bruno	Fernandes	DPD	09682493063433K
MQZSTUREH	Bruno	Fernandes	Mondial Relay	96099324
HEIQJYLLJ	Bruno	Fernandes	NACEX	7490/11247910
CHPNQLOKQ	Bruno	Fernandes	UPS	1Z72794RDK32851114
FSBDRSNWI	Bruno	Fernandes	Mondial Relay	96098873
QNCXNZVEP	Bruno	Fernandes	UPS	1Z72794RDK14953520
RPOKDUNOZ	Bruno	Fernandes	NACEX	7490/11247427
XXIYOBIEY	Bruno	Fernandes	NACEX	7490/11247429
KEVEHWUJC	Bruno	Fernandes	UPS	1Z72794RD909410578
JUBEDOGUU	Bruno	Fernandes	UPS	1Z72794RDK30303102
BCRSBRGIP	Bruno	Fernandes	UPS	1Z72794RDK29493697
VOJEGZVUO	Bruno	Fernandes	UPS	1Z72794RDK13307917
YAQVOCCPO	Bruno	Fernandes	NACEX	7490/11247326
SHFDMIESZ	Bruno	Fernandes	DPD	09682493063415O
AJEANTRJP	Bruno	Fernandes	NACEX	7490/11247352
LAZJEFDQC	Bruno	Fernandes	UPS	1Z72794RDK32866671
UWASEBVZR	Bruno	Fernandes	NACEX	7490/11247358
EEHGIGKMP	Bruno	Fernandes	UPS	1Z72794RDK13261449
ZHAQBQHRY	Bruno	Fernandes	Mondial Relay	96098754
TRLZTRRMV	Bruno	Fernandes	NACEX	7490/11247361
WGXMXARQM	Bruno	Fernandes	DPD	09682493063410Y
AJKCRJGPF	Bruno	Fernandes	DPD	09682493063409K
SZANZEVRP	Bruno	Fernandes	DPD	9.68249E+13
SZANZEVRP	Bruno	Fernandes	DPD	09682493063408M
DKHUNOQDG	Bruno	Fernandes	UPS	1Z72794RDK34522056
YYRBWKVLM	Bruno	Fernandes	UPS	1Z72794RDK29856732
WRKLNOARY	Bruno	Fernandes	NACEX	7490/11247219
BAOYTGGNH	Bruno	Fernandes	NACEX	7490/11247302
UEVTQLGOM	Bruno	Fernandes	UPS	1Z72794R0407969582
WTCJCKMUP	Bruno	Fernandes	NACEX	7490/11247347
ZYZBLHDRO	Bruno	Fernandes	NACEX	7490/11247296
THOVGAFGQ	Bruno	Fernandes	UPS	1Z72794RDK00883757
KSXDAPJHC	Bruno	Fernandes	NACEX	7490/11247172
DWRURBHOP	Bruno	Fernandes	UPS	1Z72794RDK38226888
HMPAYVSGA	Bruno	Fernandes	DPD	09682493063411W
RCXHHKFVG	Bruno	Fernandes	NACEX	7490/11247285
YOZILXQQT	Bruno	Fernandes	UPS	1Z72794RDK06926904
MQALJTGLK	Bruno	Fernandes	inPost	96098785
EOULSJLHL	Bruno	Fernandes	UPS	1Z72794RD924481786
DBBRCEIIO	Bruno	Fernandes	inPost	96098818
DVXIAGVPR	Bruno	Fernandes	UPS	1Z72794RDK18294682
YAGKFBEXA	Bruno	Fernandes	DPD	09682493063418I
EALVNPUAK	Bruno	Fernandes	Mondial Relay	96098784
LVOQEPZZM	Bruno	Fernandes	NACEX	7490/11247232
GNAIGQGWC	Bruno	Fernandes	NACEX	7490/11247324
MPRWBLLDQ	Bruno	Fernandes	DPD	09682493063417K
HEOMAFVZT	Bruno	Fernandes	NACEX	7490/11247166
RICQQQHHU	Bruno	Fernandes	UPS	1Z72794R0429004626
RICQQQHHU	Bruno	Fernandes	UPS	1Z72794R0435771830
VHHWLVMQV	Bruno	Fernandes	DPD	09682493063406Q
TRBXCUDUO	Bruno	Fernandes	Mondial Relay	96098713
UJWGJWRMV	Bruno	Fernandes	DPD	09682493063416M
FVMWREXXB	Bruno	Fernandes	NACEX	7490/11247366
FGVJNEVGC	Bruno	Fernandes	UPS	1Z72794RDK13854497
CJYMJJMNG	Bruno	Fernandes	NACEX	7490/11246903
RZJYXYRCM	Bruno	Fernandes	UPS	1Z72794RDK35584916
YEGIRVDTT	Bruno	Fernandes	NACEX	7490/11246881
AVBHFNTWL	Bruno	Fernandes	UPS	1Z72794RDK13929531
XGAKTQSAJ	Bruno	Fernandes	NACEX	7490/11246895
OYTEYEIVY	Bruno	Fernandes	UPS	1Z72794RDK07275320
QHDSFVRJR	Bruno	Fernandes	DPD	09682493063404U
VHJKRNZAJ	Bruno	Fernandes	UPS	1Z72794RDK35008477
DMBFZCGIG	Bruno	Fernandes	NACEX	7490/11246723
VHZYJMGQV	Bruno	Fernandes	NACEX	7490/11246811
IUMMKEPRG	Bruno	Fernandes	UPS	1Z72794RDK29500393
EKLBIOHAI	Bruno	Fernandes	NACEX	7490/11246711
KLSKCHCYF	Bruno	Fernandes	DPD	09682493063390A
MXDOURIDV	Bruno	Fernandes	NACEX	7490/11246694
IVJMCEPQM	Bruno	Fernandes	UPS	1Z72794RDK15594434
SVMXICELO	Bruno	Fernandes	Mondial Relay	96098412
BRWHBZWXU	Bruno	Fernandes	DPD	9.68249E+13
VVJVDFMGA	Bruno	Fernandes	NACEX	7490/11246708
NSQBDBDZJ	Bruno	Fernandes	DPD	9.68249E+13
QITEKGTIK	Bruno	Fernandes	UPS	1Z72794RD920968900
KOEFDMCCV	Bruno	Fernandes	Mondial Relay	96098398
NYHFZATRP	Bruno	Fernandes	UPS	1Z72794RDK21206421
OPIVYHUTH	Bruno	Fernandes	NACEX	7490/11246785
TIQOOZKZQ	Bruno	Fernandes	UPS	1Z72794RDK21003444
YUTLFCNIT	Bruno	Fernandes	UPS	1Z72794RDK35930863
TPKBTVYEM	Bruno	Fernandes	UPS	1Z72794RDK11304487
VODWWRQIT	Bruno	Fernandes	UPS	1Z72794RDK30971588
CPWJULEWJ	Bruno	Fernandes	DPD	09682493063389V
VMRUSOQMX	Bruno	Fernandes	NACEX	7490/11246848
ETDQYUVVI	Bruno	Fernandes	NACEX	7490/11246813
ETDQYUVVI	Bruno	Fernandes	NACEX	7490/11246815
DQDHNHRFB	Bruno	Fernandes	UPS	1Z72794RDK27897326
LLLCGYAFO	Bruno	Fernandes	UPS	1Z72794RDK19818140
VJRFFYLDX	Bruno	Fernandes	NACEX	7490/11246243
AOGBFJEEJ	Bruno	Fernandes	UPS	1Z72794RD916919337
DHHEORBGK	Bruno	Fernandes	UPS	1Z72794RDK21013657
TGWGJGXCG	Bruno	Fernandes	NACEX	7490/11246103
ACSLOOJOF	Bruno	Fernandes	UPS	1Z72794RDK20071435
YSVFJCMBW	Bruno	Fernandes	UPS	1Z72794RDK11837125
XKKUIQVMZ	Bruno	Fernandes	UPS	1Z72794RDK29390271
UPHZLBXND	Bruno	Fernandes	UPS	1Z72794RDK19236466
MQYIUXAIM	Bruno	Fernandes	DPD	09682493063361K
PECFMOXHW	Bruno	Fernandes	NACEX	7490/11246072
VNYLXDGQL	Bruno	Fernandes	Mondial Relay	96098096
XAUXXQSNY	Bruno	Fernandes	UPS	1Z72794RDK25635611
EECWMKNSZ	Bruno	Fernandes	NACEX	7490/11246166
ZTYIDJYZA	Bruno	Fernandes	UPS	1Z72794RDK33648226
CKAOKEWOJ	Bruno	Fernandes	Mondial Relay	96097947
RSMPIWIFB	Bruno	Fernandes	DPD	9.68249E+13
RIGLSGQSN	Bruno	Fernandes	Mondial Relay	96098111
PAFVPAWZD	Bruno	Fernandes	DPD	09682493063358A
PAFVPAWZD	Bruno	Fernandes	DPD	9.68249E+13
XXXNBONJM	Bruno	Fernandes	NACEX	7490/11246169
JHYIYYGDC	Bruno	Fernandes	NACEX	7490/11246208
EZMRHJCLQ	Bruno	Fernandes	Mondial Relay	96098099
DFQLELCTP	Bruno	Fernandes	UPS	1Z72794RDK01778502
EBJIIHZDT	Bruno	Fernandes	DPD	9.68249E+13
LJMEAEPWZ	Bruno	Fernandes	DPD	09682493063366A
RUYDHKNEC	Bruno	Fernandes	UPS	1Z72794RDK39206488
HUCHIFYJR	Bruno	Fernandes	Mondial Relay	96097916
XEHHEZCAY	Bruno	Fernandes	UPS	1Z72794RDK19895512
NWEGSKWZY	Bruno	Fernandes	Mondial Relay	96097559
VUAKBZMTO	Bruno	Fernandes	Mondial Relay	96097562
ADNSDZAPQ	Bruno	Fernandes	NACEX	7490/11245583
ZKILPARFO	Bruno	Fernandes	UPS	1Z72794RD907692410
TQVNPGUSE	Bruno	Fernandes	DPD	09682493063349C
GQAGIFPGB	Bruno	Fernandes	DPD	09682493063355G
GQAGIFPGB	Bruno	Fernandes	DPD	09682493063356E
GQAGIFPGB	Bruno	Fernandes	DPD	09682493063357C
FFEUPFHOL	Bruno	Fernandes	DPD	09682493063353K
PDUIPIJZO	Bruno	Fernandes	DPD	09682493063350Q
RHYEGZDTK	Bruno	Fernandes	DPD	09682493063345K
TSV;

        $lines = preg_split('/\R/', trim($raw));
        $headers = str_getcsv(array_shift($lines), "\t");

        return array_values(array_filter(array_map(function (string $line) use ($headers) {
            if (trim($line) === '') {
                return null;
            }

            return array_combine($headers, str_getcsv($line, "\t"));
        }, $lines)));
    }
};
