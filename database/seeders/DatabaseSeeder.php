<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Driver;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin'),
        ]);

        User::factory(2)->create();

        Brand::insert(['code' => 'B0001', 'name' => 'Fancy Bread']);

        Product::insert([
            ['code' => 'P0001', 'name' => 'Floss Bread', 'brand_id' => 1, 'post_tax_price' => 10000, 'pre_tax_price' => 10000 / (1 + 0.11), 'total_items' => 1],
            ['code' => 'P0002', 'name' => 'Milk Cheese Bread', 'brand_id' => 1, 'post_tax_price' => 8500, 'pre_tax_price' => 8500 / (1 + 0.11), 'total_items' => 1],
            ['code' => 'P0003', 'name' => 'Spicy Chicken Bread', 'brand_id' => 1, 'post_tax_price' => 9000, 'pre_tax_price' => 9000 / (1 + 0.11), 'total_items' => 1],
            ['code' => 'P0004', 'name' => 'Pizza Slice', 'brand_id' => 1, 'post_tax_price' => 10000, 'pre_tax_price' => 10000 / (1 + 0.11), 'total_items' => 1],
            ['code' => 'P0005', 'name' => 'Sausage Spicy Bread', 'brand_id' => 1, 'post_tax_price' => 9500, 'pre_tax_price' => 9500 / (1 + 0.11), 'total_items' => 1],
            ['code' => 'P0006', 'name' => 'Pisang Lilit Keju', 'brand_id' => 1, 'post_tax_price' => 8500, 'pre_tax_price' => 8500 / (1 + 0.11), 'total_items' => 1],
            ['code' => 'P0007', 'name' => 'Pisang Lilit Coklat', 'brand_id' => 1, 'post_tax_price' => 8500, 'pre_tax_price' => 8500, 'total_items' => 1],
            ['code' => 'P0008', 'name' => 'Donut Ast', 'brand_id' => 1, 'post_tax_price' => 7500, 'pre_tax_price' => 7500 / (1 + 0.11), 'total_items' => 1],
        ]);

        Category::insert([
            ['name' => 'Bread'],
            ['name' => 'Pizza'],
            ['name' => 'Donut'],
            ['name' => 'Sausage'],
            ['name' => 'Banana'],
            ['name' => 'Cheese'],
            ['name' => 'Milk'],
            ['name' => 'Chocolate'],
            ['name' => 'Chicken'],
            ['name' => 'Spicy'],
        ]);

        ProductCategory::insert([
            ['product_id' => 1, 'category_id' => 1],
            ['product_id' => 2, 'category_id' => 1],
            ['product_id' => 2, 'category_id' => 6],
            ['product_id' => 2, 'category_id' => 7],
            ['product_id' => 3, 'category_id' => 1],
            ['product_id' => 3, 'category_id' => 9],
            ['product_id' => 3, 'category_id' => 10],
            ['product_id' => 4, 'category_id' => 2],
            ['product_id' => 5, 'category_id' => 1],
            ['product_id' => 5, 'category_id' => 4],
            ['product_id' => 5, 'category_id' => 10],
            ['product_id' => 6, 'category_id' => 5],
            ['product_id' => 6, 'category_id' => 6],
            ['product_id' => 7, 'category_id' => 5],
            ['product_id' => 7, 'category_id' => 8],
            ['product_id' => 8, 'category_id' => 3],
        ]);

        Customer::insert([
            [
                'code' => 'C0001',
                'name' => 'V146',
                'short_address' => 'BEBER',
                'full_address' => 'JL.RAYA BEBER RT.03 RW.02 DESA BEBER KECAMATAN BEBER KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0002',
                'name' => 'V624',
                'short_address' => 'CIPERNA 40',
                'full_address' => 'JL.RAYA CIPERNA BLOK TUTUGAN RT.003 RW.004 DESA CIPERNA KEC.TALUN KAB CIREBON'
            ],
            [
                'code' => 'C0003',
                'name' => 'V406',
                'short_address' => 'ARUM SARI',
                'full_address' => 'JL.CENDANA RT.002 RW.008 BUMI ARUM SARI DS.CIREBON GIRANG KEC.TALUN KAB CIREBON'
            ],
            [
                'code' => 'C0004',
                'name' => 'V278',
                'short_address' => 'KUDUKERAS',
                'full_address' => 'JL. RAYA BABAKAN NO.02 RT 02 RW 01 DESA KUDUKERAS KEC BABAKAN KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0005',
                'name' => 'V920',
                'short_address' => 'OTTO ISKANDARDINATA',
                'full_address' => 'JL.OTTO ISKANDARDINATA NO.30 RT.001 RW.003 KEC KLANGENAN KEL KLANGENAN KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0006',
                'name' => 'V707',
                'short_address' => 'RAYA KEBAREPAN',
                'full_address' => 'JL.PANTURA KEBAREPAN BLOK.SIULING RT.019 RW.001 DESA KASUGENGAN LOR KEC.DEPOK KAB CIREBON'
            ],
            [
                'code' => 'C0007',
                'name' => 'V218',
                'short_address' => 'TEGAL WANGI',
                'full_address' => 'JL. RAYA PLERED RT.01 RW.01 DS.TEGAL WANGI KEC.WERU KAB.CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0008',
                'name' => 'V336',
                'short_address' => 'SUKADANA',
                'full_address' => 'JL.PANGERAN SUTAJAYA RT.02 RW.03 DS.SUKADANA KEC.PABUARAN KAB.CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0009',
                'name' => 'V848',
                'short_address' => 'LEUWEUNGGAJAH',
                'full_address' => 'JL.LETJEN S. PARMAN RT.001 RW.003 KECAMATAN CILEDUG KELURAHAN LEUWEUNGGAJAH KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0010',
                'name' => 'V274',
                'short_address' => 'CILEDUG 2',
                'full_address' => 'JL MERDEKA BARAT NO 68 RT 06 RW 03 DESA CILEDUG KULON KEC CILEDUG KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0011',
                'name' => 'V922',
                'short_address' => 'MEGU GEDE',
                'full_address' => 'JL.FATAHILLAH (SUMBER-PLERED) RT.004 RW.001 KEC WERU KEL MEGUGEDE KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0012',
                'name' => 'V597',
                'short_address' => 'WATUBELAH 2',
                'full_address' => 'JL.FATAHILAH BLOK LAPANG RT.006 RW.002 KEL.WATUBELAH KEC.SUMBER KAB CIREBON'
            ],
            [
                'code' => 'C0013',
                'name' => 'V946',
                'short_address' => 'SPBU GEMPOL',
                'full_address' => 'JL.RAYA CIREBON - BANDUNG GEMPOL RT.001 RW.003  KEC GEMPOL KEL GEMPOL KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0014',
                'name' => 'V915',
                'short_address' => 'REST AREA KM 207A 2',
                'full_address' => 'JL.RAYA TOL PALIKANCI KM 207 KEC. MUNDU KEL. SETUPATOK KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0015',
                'name' => 'V485',
                'short_address' => 'REST AREA KM 208B',
                'full_address' => 'JL.TOL PALIKANCI PALIMANAN KM 227 B DS.SETU PATOK KEC. MUNDU KAB CIREBON'
            ],
            [
                'code' => 'C0016',
                'name' => 'V765',
                'short_address' => 'REST AREA KM 228A',
                'full_address' => 'REST AREA KM 228B TOL KANCI PEJAGAN KECAMATAN PABUARAN KELURAHAN JATIRENGGANG KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0017',
                'name' => 'V747',
                'short_address' => 'REST AREA KM 229B',
                'full_address' => 'REST AREA KM 229 TOL KANCI - PEJAGAN DESA JATIRENGGANG KEC.PABUARAN KAB.CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0018',
                'name' => 'V600',
                'short_address' => 'DEWI SARTIKA 10',
                'full_address' => 'JL.RD.DEWI SARTIKA RT.001 RW.001 KEL.TUKMUDAL KEC.SUMBER KAB CIREBON'
            ],
            [
                'code' => 'C0019',
                'name' => 'V684',
                'short_address' => 'RAYA KENANGA',
                'full_address' => 'JL.NYI AGENG SERANG BLOK BONGKARAN RT.004 RW.007 KEL.KENANGA KEC.SUMBER KAB CIREBON'
            ],
            [
                'code' => 'C0020',
                'name' => 'V181',
                'short_address' => 'CAKRA BUANA',
                'full_address' => 'JL. PANGERAN CAKRA BUANA RT 03 RW 06 DESA WANASABA KIDUL KEC TALUN KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0021',
                'name' => 'V035',
                'short_address' => 'SLTN AG TIRTAYASA 2',
                'full_address' => 'JL.SULTAN AGENG TIRTAYASA 2 DS.TUK KEC.KEDAWUNG KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0022',
                'name' => 'V244',
                'short_address' => 'ADIDHARMA',
                'full_address' => 'JL. SUNAN GUNUNG JATI RT 02 RW 01 DESA ADIDHARMA KEC GUNUNG JATI KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0023',
                'name' => 'V449',
                'short_address' => 'WARU DUWUR',
                'full_address' => 'JL.RAYA KANCI TEGAL RT.007 RW.004 BLOK DUSUN II DESA WARUDUWUR KEC. MUNDU KAB CIREBON'
            ],
            [
                'code' => 'C0024',
                'name' => 'V664',
                'short_address' => 'ASTANAJAPURA',
                'full_address' => 'JL. KH.WAHID HASYIM NO.08 RT.001 RW.006 DESA MERTAPADA WETAN KEC.ASTANAJAPURA KAB.CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0025',
                'name' => 'V591',
                'short_address' => 'AKSES TOL CILEDUG',
                'full_address' => 'JL.MERDEKA UTARA BLOK GENGGONG RT.003 RW.004 DESA CILEDUG LOR KEC.CILEDUG  KAB CIREBON'
            ],
            [
                'code' => 'C0026',
                'name' => 'V175',
                'short_address' => 'NYI AGENG SERANG',
                'full_address' => 'JL. NYI AGENG SERANG RT 03 RW 01 DESA SINDANGMEKAR KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0027',
                'name' => 'V211',
                'short_address' => 'RAYA TEGAL GUBUG',
                'full_address' => 'JL.RAYA TEGAL GUBUG BLOK 04 RT.004 RW.008 DS.TEGAL GUBUG KEC.ARJAWINANGUN KAB.CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0028',
                'name' => 'V596',
                'short_address' => 'TUPAREV 70',
                'full_address' => 'JL.PILANG RAYA BLOK KUKUSAN TIMUR RT.006 RW.003 DESA PILANGSARI KEC.KEDAWUNG KAB CIREBON'
            ],
            [
                'code' => 'C0029',
                'name' => 'V616',
                'short_address' => 'PILANGSARI 37',
                'full_address' => 'JL.RAYA PILANGSARI NO.37 RT.002 RW.001 DESA PILANGSARI KEC.KEDAWUNG KAB CIREBON'
            ],
            [
                'code' => 'C0030',
                'name' => 'V477',
                'short_address' => 'TUPAREV 3',
                'full_address' => 'JL.RAYA TUPAREV NO.63 DS.SUTAWINANGUN KEC. KEDAWUNG KAB CIREBON'
            ],
            [
                'code' => 'C0031',
                'name' => 'V037',
                'short_address' => 'PECILON',
                'full_address' => 'JL.PECILON NO.229 DESA SUTAWINANGUN KEC.KEDAWUNG KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0032',
                'name' => 'V603',
                'short_address' => 'CIDENG 12',
                'full_address' => 'JL.CIDENG RAYA NO.12 RT.008 RW.002 DESA KERTAWINANGUN KEC.KEDAWUNG KAB CIREBON'
            ],
            [
                'code' => 'C0033',
                'name' => 'V557',
                'short_address' => 'REST AREA KM 130A',
                'full_address' => 'JL. TOL CIKAMPEK- PALIMANAN REST KM 130A DESA SANCA KEC. GANTAR KAB INDRAMAYU KAB INDRAMAYU'
            ],
            [
                'code' => 'C0034',
                'name' => 'V558',
                'short_address' => 'REST AREA KM 130B',
                'full_address' => 'JL. TOL CIKAMPEK- PALIMANAN REST KM 130B DESA SANCA KEC. GANTAR KAB INDRAMAYU KAB INDRAMAYU'
            ],
            [
                'code' => 'C0035',
                'name' => 'V793',
                'short_address' => 'TEGALURUNG',
                'full_address' => 'JL.RAYA TEGALURUNG BLOK GABLOG RT.003 RW.001 KECAMATAN BALONGAN KELURAHAN TEGALURUNG KAB. INDRAMAYU KAB INDRAMAYU'
            ],
            [
                'code' => 'C0036',
                'name' => 'V763',
                'short_address' => 'SPBU MANISLOR FRC',
                'full_address' => 'JL.RAYA JALAKSANA NO.57 KECAMATAN JALAKSANA KELURAHAN MANISLOR KAB. KUNINGAN KAB KUNINGAN'
            ],
            [
                'code' => 'C0037',
                'name' => 'V738',
                'short_address' => 'KRAMAT MULYA BARU',
                'full_address' => 'JL. SILIWANGI RT 001 RW 001 DESA KRAMAT MULYA KEC.KRAMAT MULYA KAB.KUNINGAN KAB KUNINGAN'
            ],
            [
                'code' => 'C0038',
                'name' => 'V472',
                'short_address' => 'CUT NYAK DIEN',
                'full_address' => 'JL.RAYA CUT NYAK DIEN KEL.CIJOHO KEC.KUNINGAN KAB.KUNINGAN KAB KUNINGAN'
            ],
            [
                'code' => 'C0039',
                'name' => 'V778',
                'short_address' => 'SYECH MANGLAYANG',
                'full_address' => 'JL.SYECH MANGLAYANG KADUGEDE - KUNINGAN DUSUN PAHING RT.018 RW.009 KECAMATAN KADUGEDE KELURAHAN KADUGEDE KAB KUNINGAN'
            ],
            [
                'code' => 'C0040',
                'name' => 'V155',
                'short_address' => 'CIKIJING BARU',
                'full_address' => 'JL. RAYA CIKIJING NO.55 RT.01 RW.01 KEL. CIKIJING KEC. CIKIJING KAB. MAJALENGKA KAB MAJALENGKA'
            ],
            [
                'code' => 'C0041',
                'name' => 'VA11',
                'short_address' => 'REST AREA KM 207A I',
                'full_address' => 'JL.RAYA TOL PALIMANAN KANCI KM 226 DUSUN SIBACIN DESA SETUPATOK KEC.MUNDU KAB CIREBON'
            ],
            [
                'code' => 'C0042',
                'name' => 'V601',
                'short_address' => 'REST KM 164',
                'full_address' => 'JL.TOL CIKAPALI REST KM 164 DESA JATIWANGI KEC.JATIWANGI KAB MAJALENGKA'
            ],
            [
                'code' => 'C0043',
                'name' => 'V602',
                'short_address' => 'REST KM 166',
                'full_address' => 'JL.TOL CIKAPALI REST KM 166 DESA SURAWANGI KEC.JATIWANGI KAB MAJALENGKA'
            ],
            [
                'code' => 'C0044',
                'name' => 'V231',
                'short_address' => 'PENGGUNG',
                'full_address' => 'JL.JEND.SUDIRMAN NO.05 RT.03 RW.07 PENGGUNG SELATAN KEL.KALIJAGA KEC.HARJAMUKTI KOTA CIREBON KOTA CIREBON'
            ],
            [
                'code' => 'C0045',
                'name' => 'V263',
                'short_address' => 'KALITANJUNG',
                'full_address' => 'JL. KALITANJUNG NO. 61 RT 01 RW 04 KEL HARJAMUKTI KEC HARJAMUKTI KOTA CIREBON KOTA CIREBON'
            ],
            [
                'code' => 'C0046',
                'name' => 'V262',
                'short_address' => 'KANGGRAKSAN 2',
                'full_address' => 'JL. KANGRAKSAN NO.29 RT.01 RW.02 KEL. HARJAMUKTI KEC. HARJAMUKTI KOTA CIREBON KOTA CIREBON'
            ],
            [
                'code' => 'C0047',
                'name' => 'V467',
                'short_address' => 'CIREBON SUPER BLOK',
                'full_address' => 'JL.Dr.CIPTO MANGUN KUSUMO KEL.PEKIRINGAN KEC. KESAMBI KAB. CIREBON KOTA CIREBON'
            ],
            [
                'code' => 'C0048',
                'name' => 'V488',
                'short_address' => 'SPBU BIMA',
                'full_address' => 'JL.BRIGJEND DHARSONO RT.003 RW.005 KEL.SUNYARAGI KEC.KESAMBI KOTA CIREBON'
            ],
            [
                'code' => 'C0049',
                'name' => 'V200',
                'short_address' => 'TERMINAL HARJAMUKTI',
                'full_address' => 'JL. AHMAD YANI NO.32 RT 10 RW 03 DUKUH SEMAR  KOTA CIREBON KOTA CIREBON'
            ],
            [
                'code' => 'C0050',
                'name' => 'V260',
                'short_address' => 'CEREMAI 2',
                'full_address' => 'JL. CIREMAI NO. 112 KEL LARANGAN KEC. HARJAMUKTI KOTA CIREBON KOTA CIREBON'
            ],
            [
                'code' => 'C0051',
                'name' => 'V235',
                'short_address' => 'BYPASS',
                'full_address' => 'JL.A.YANI NO.04 RT 01 RW 11 KEL.LARANGAN KEC.HARJAMUKTI KOTA CIREBON KOTA CIREBON'
            ],
            [
                'code' => 'C0052',
                'name' => 'V198',
                'short_address' => 'SUNYARAGI',
                'full_address' => 'JL. EVAKUASI NO. 1 RT. 05 RW. 02 KEL. SUNYARAGI KEC. KESAMBI KOTA CIREBON KOTA CIREBON'
            ],
            [
                'code' => 'C0053',
                'name' => 'V516',
                'short_address' => 'SILIWANGI CIREBON',
                'full_address' => 'JL.SILIWANGI  NO.96 RT 02/ RW 06 KEL.KEBON BARU KEC.KEJAKSAN KOTA CIREBON KOTA CIREBON'
            ],
            [
                'code' => 'C0054',
                'name' => 'V204',
                'short_address' => 'PEMUDA',
                'full_address' => 'JL.PEMUDA NO.19 RT.005 RW.008 KELURAHAN SUNYARAGI KECAMATAN KESAMBI KOTA CIREBON KOTA CIREBON'
            ],
            [
                'code' => 'C0055',
                'name' => 'V627',
                'short_address' => 'JALAN NANAS TEGAL',
                'full_address' => 'JL.NANAS RT.001 RW.004 KEL.KRATON KOTA TEGAL'
            ],
            [
                'code' => 'C0056',
                'name' => 'V583',
                'short_address' => 'DUKUH MAJA',
                'full_address' => 'JL.RAYA JATIBARANG - KETANGGUNGAN DESA DUKUH MAJA KEC.SONGGOM KAB BREBES'
            ],
            [
                'code' => 'C0057',
                'name' => 'V586',
                'short_address' => 'PESANTUNAN',
                'full_address' => 'JL.RAYA BREBES - KLAMPOK RT.004 RW.010 DESA PESANTUNAN KEC.WANASARI KAB.BREBES KAB BREBES'
            ],
            [
                'code' => 'C0058',
                'name' => 'V229',
                'short_address' => 'WANASARI',
                'full_address' => 'JL.RAYA PANTURA NO.08 RT.04 RW.07 DESA KLAMPOK KECAMATAN WANASARI KAB. BREBES KAB BREBES'
            ],
            [
                'code' => 'C0059',
                'name' => 'V702',
                'short_address' => 'PANAWANGAN FRC',
                'full_address' => 'DUSUN MANIS RT.005 RW.006 DESA CINYASAG KEC.PANAWANGAN KAB CIAMIS'
            ],
            [
                'code' => 'C0060',
                'name' => 'V758',
                'short_address' => 'REST AREA KM 275A',
                'full_address' => 'JL.TOL PEJAGAN - PEMALANG KECAMATAN ADIWERNA KELURAHAN PENARUKAN KAB. TEGAL KAB TEGAL'
            ],
            [
                'code' => 'C0061',
                'name' => 'V759',
                'short_address' => 'REST AREA KM 294B',
                'full_address' => 'JL.TOL PEMALANG - PEJAGAN KECAMATAN SURODADI KELURAHAN KERTASARI KAB. TEGAL KAB TEGAL'
            ],
            [
                'code' => 'C0062',
                'name' => 'V832',
                'short_address' => 'REST AREA KM 282B',
                'full_address' => 'JL.TOL PEJAGAN - PEMALANG KECAMATAN TARUB KELURAHAN LEBETENG KAB. TEGAL KAB TEGAL'
            ],
            [
                'code' => 'C0063',
                'name' => 'V408',
                'short_address' => 'KUDAILE',
                'full_address' => 'JL. PROF.MOCH.YAMIN RT.007 RW.002 KEL.KUDAILE KEC.SLAWI KAB.TEGAL KAB TEGAL'
            ],
            [
                'code' => 'C0064',
                'name' => 'V363',
                'short_address' => 'A. YANI SLAWI',
                'full_address' => 'JL  A YANI RT 04/02 KEL. PROCOT KEC. SLAWI TEGAL KAB TEGAL'
            ],
            [
                'code' => 'C0065',
                'name' => 'VB16',
                'short_address' => 'KAPUK CIREBON',
                'full_address' => 'JL. SULTAN AGENG TIRTAYASA NO. 212 RT/RW 004/001 KEC. KEDAWUNG KEL. KEDUNGJAYA KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0066',
                'name' => 'V407',
                'short_address' => 'TEUKU UMAR',
                'full_address' => 'JL.TEUKU UMAR RT.05 RW.04 KEL.PAGONGAN KEC.DUKUH TURI KAB.TEGAL KAB TEGAL'
            ],
            [
                'code' => 'C0067',
                'name' => 'V011',
                'short_address' => 'PERTIGAAN LARANGAN',
                'full_address' => 'JL PERTIGAAN LARANGAN NO.06 RT 06 RW 05 DESA LARANGAN KEC. LOHBENER KAB. INDRAMAYU KAB INDRAMAYU'
            ],
            [
                'code' => 'C0068',
                'name' => 'V233',
                'short_address' => 'MUNDU',
                'full_address' => 'JL. RAYA MUNDU RT 03 RW 03 DESA BANDENGAN KEC MUNDU KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0069',
                'name' => 'V641',
                'short_address' => 'POLSEK LEMAH ABANG',
                'full_address' => 'JL. MT. HARYONO NO.05 DESA LEMAH ABANG KULON KEC LEMAH ABANG KAB. CIREBON KAB CIREBON'
            ],
            [
                'code' => 'C0070',
                'name' => 'VB18',
                'short_address' => 'CSB BARU (CBBU',
                'full_address' => 'JL.Dr.CIPTO MANGUN KUSUMO KEL.PEKIRINGAN KEC. KESAMBI KAB. CIREBON'
            ],
            [
                'code' => 'C0071',
                'name' => 'VA93',
                'short_address' => 'SPBU SUMBERJAYA FRC',
                'full_address' => 'BLOK BANJARSARI RT 03RW 02 KEL. BANJARAN KEC. SUMBERJAYA KAB. MAJALENGKA KAB MAJALENGKA'
            ],
            [
                'code' => 'C0072',
                'name' => 'VB05',
                'short_address' => 'SUMBERJAYA',
                'full_address' => 'BLOK SABTU RT 01 RW 01 KEL. BONGASKULON KEC. SUMBERJAYA KAB. MAJALENGKA KAB MAJALENGKA'
            ],
            [
                'code' => 'C0073',
                'name' => 'VB10',
                'short_address' => 'RAYA KARTINI CRB',
                'full_address' => 'JL. PANCURAN NO. 125 KEC. KEJAKSAN KEL. SUKAPURA KOTA CIREBON 45122 CIREBON'
            ],
            [
                'code' => 'C0074',
                'name' => 'V163',
                'short_address' => 'LURAH',
                'full_address' => 'JL. PANGERAN ANTASARI KM.3 RT 05 RW 02 DESA LURAH KEC PLUMBON KAB. CIREBON KAB CIREBON'
            ],
        ]);

        Vendor::insert([
            ['code' => 'V0001', 'name' => 'Asia'],
            ['code' => 'V0002', 'name' => 'Rumah'],
            ['code' => 'V0003', 'name' => 'Sumber'],
        ]);

        Driver::insert([
            ['name' => 'Pak Edi'],
            ['name' => 'Pak Warno'],
        ]);
    }
}
