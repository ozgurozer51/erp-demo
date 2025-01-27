<?php
require_once("../../../functions/function.php");
require_once("../../../functions/workcube-data.php");
ini_set('display_errors', 1);
ini_set('error_reporting', 1);


//
//ini_set('display_errors', 0);

ini_set('max_execution_time', 1900);


if (isset($_GET['islem'])) {
    $islem = $_GET['islem'];
} else {
    exit("İslem Bilgisi Belirtilmedi!");
}

$bugunun_tarihi = date('d.m.Y'); // Örnek format: Gün.Ay.Yıl (29.07.2023)
$bugunun_tarihi_2 = date('Y-m-d');


if (isset($_POST['fast'])) {
    $_GET['islem'] = "sevkiyat-listesini-getir-2";
    $islem = "sevkiyat-listesini-getir-2";
}


$dun = DateTime::createFromFormat('d.m.Y', $bugunun_tarihi);
$dun->modify('-1 days');
$dun = $dun->format('d.m.Y');
$dun_unix = strtotime($dun);

$yarin = DateTime::createFromFormat('d.m.Y', $bugunun_tarihi);
$yarin->modify('+2 days');
$yarin = $yarin->format('d.m.Y');
$yarin_unix = strtotime($yarin);


$yarin_2 = DateTime::createFromFormat('Y-m-d', $bugunun_tarihi_2);
$yarin_2->modify('+1 days');
$yarin_2 = $yarin_2->format('Y-m-d');
$yarin_unix_2 = strtotime($yarin_2);

if ($islem == "takim-ad-bilgisi-getir") {

    $sql = sql_select("SELECT a1 FROM envanter GROUP BY a1 ORDER BY a1 asc", "envanter");

    $sql = array_filter($sql, function ($item) {
        return $item['a1'] !== '';
    });

// Manuel olarak veri eklemek için
    $newData = array(
        array('a1' => 'Tümü'),
        array('a1' => 'Mobetto'),
        array('a1' => 'Moduler')
    );

// Verileri birleştirme
    $data = array_merge($newData, array_values($sql));

    echo json_encode($data);

}


if ($islem == "bolge-kritik-listesini-getir") {

    $urun_listesi_ek_sorgu = " WHERE ( ";

    if (isset($_POST['takim_adi']) && !empty($_POST['takim_adi'])) {
        $takim_adi = $_POST['takim_adi'];
        $teams = $_POST['takim_adi'];

        foreach ($takim_adi as $index => $item) {

            if ($item == "Mobetto") {
                $urun_listesi_ek_sorgu .= " a2 LIKE '%MOBETTO%' ";
            } elseif ($item == "Moduler") {
                $urun_listesi_ek_sorgu .= " a2 NOT LIKE '%MOBETTO%' ";
            } else {
                $urun_listesi_ek_sorgu .= " a1 LIKE '%$item%' ";
            }

            // Son elemandan sonra "or" ifadesi eklenmez
            if ($index !== count($takim_adi) - 1) {
                $urun_listesi_ek_sorgu .= " OR ";
            }

        }

        $urun_listesi_ek_sorgu .= " ) ";
    }


    $tur_ek_sorgu = "";
    if (isset($_POST['bolge_kritik_tur_adi_list']) && !empty($_POST['bolge_kritik_tur_adi_list'])) {

        $tur_ek_sorgu = " and ( ";
        $bolge_kritik_tur_adi_list = $_POST['bolge_kritik_tur_adi_list'];

        foreach ($bolge_kritik_tur_adi_list as $index => $item) {
            $tur_ek_sorgu .= " a3 LIKE '%$item%' ";

            // Son elemandan sonra "or" ifadesi eklenmez
            if ($index !== count($bolge_kritik_tur_adi_list) - 1) {
                $tur_ek_sorgu .= " OR ";
            }
        }

        $tur_ek_sorgu .= "  ) ";
    }


    $unite_bilgisi_ek_sorgu = "";
    if (isset($_POST['bolge_kritik_unite']) && !empty($_POST['bolge_kritik_unite'])) {
        $bolge_kritik_unite = $_POST['bolge_kritik_unite'];

        if ($bolge_kritik_unite != "") {
            $unite_bilgisi_ek_sorgu = "  AND a2 like '%$bolge_kritik_unite%'";
        }
    }

    $filtrelenmis_depo = array();
    $depolar = sql_select("select zone_name as sehir_adi , bozuk_ad as sehir_bozuk_adi , workcube_sales_name as depo_ikinci_adi , workcube_story_name as depo_ucuncu_adi from stories where  story_type = 5 order by zone_name asc", "modanet");

    if (isset($_POST['bolge_adi'])) {
        $bolge_adi = $_POST['bolge_adi'];

        foreach ($depolar as $item) {
            if (in_array($item['depo_ucuncu_adi'], $bolge_adi)) {
                $filtrelenmis_depo[] = $item;
            }
        }

    } else {
        $filtrelenmis_depo = $depolar;
    }

    if (isset($_POST['tip_adi']) && !empty($_POST['tip_adi'])) {
        $tip_adi = $_POST['tip_adi'];
    }

    if (isset($_POST['bolge_kritik_bitis_tarihi_2'])) {
        $bolge_kritik_bitis_tarihi = $_POST['bolge_kritik_bitis_tarihi_2'];
        $bolge_kritik_bitis_tarihi_new = DateTime::createFromFormat('d.m.Y', $bolge_kritik_bitis_tarihi);
        $bolge_kritik_bitis_tarihi_new = $bolge_kritik_bitis_tarihi_new->format('Y-m-d');
    }


    if (isset($_POST['bolge_kritik_baslangic_tarihi_2'])) {
        $bolge_kritik_baslangic_tarihi = $_POST['bolge_kritik_baslangic_tarihi_2'];
        $bolge_kritik_baslangic_tarihi_new = DateTime::createFromFormat('d.m.Y', $bolge_kritik_baslangic_tarihi);
        $bolge_kritik_baslangic_tarihi_new = $bolge_kritik_baslangic_tarihi_new->format('Y-m-d');
    }

    if (isset($_POST['bolge_kritik_unite']) && !empty($_POST['bolge_kritik_unite'])) {
        $tur_ek_sorgu = "";
        $urun_listesi_ek_sorgu = "";
        $unite_bilgisi_ek_sorgu = " where a4 like '%$_POST[bolge_kritik_unite]%' ";
    }

    $start_unix = strtotime($bolge_kritik_baslangic_tarihi);
    $end_unix = strtotime($bolge_kritik_bitis_tarihi);

    $urun_listesi = sql_select("select a1 , a2 , a3 , a4 , product_name , a6 , a7 , a8 , mamul ,mamul_2 ,envanter , fabrika1 , envanter , fabrika1depo, grifaburetim , altinova , planlamaisimlersatis , stokkodu , main_stock_code from envanter $urun_listesi_ek_sorgu  $tur_ek_sorgu   $unite_bilgisi_ek_sorgu and ( sistemdurum2='ACIK' OR sistemdurumunitepaket='ACIK' ) order by a4 asc ", "envanter");


//    $toplam_satis = moduler_toplam_satis_getir($filtrelenmis_depo, $urun_listesi, $bolge_kritik_baslangic_tarihi);


    $depo_birinci_adı_kolonu = array_column($filtrelenmis_depo, 'depo_ikinci_adi');
    $sonuc = implode("','", $depo_birinci_adı_kolonu);

    $zone_name_1 = array_column($filtrelenmis_depo, 'sehir_adi');
    $sonuc_4 = implode("','", $zone_name_1);

    $product_1 = array_column($urun_listesi, 'a4');
    $sonuc_2 = implode("','", $product_1);

    $product_2 = array_column($urun_listesi, 'product_name');
    $sonuc_3 = implode("','", $product_2);

    $stock_code = array_column($urun_listesi, 'stokkodu');
    $sonuc_5 = implode("','", $stock_code);

    $product_3 = array_column($urun_listesi, 'a6');
    $sonuc_7 = implode("','", $product_3);

    $product_4 = array_column($urun_listesi, 'a7');
    $sonuc_8 = implode("','", $product_4);

    $product_5 = array_column($urun_listesi, 'a8');
    $sonuc_9 = implode("','", $product_4);

    $main_stock_code = array_column($urun_listesi, 'main_stock_code');
    $sonuc_6 = implode("','", $main_stock_code);

    $date_xxc = DateTime::createFromFormat('d.m.Y', $bolge_kritik_baslangic_tarihi);

    $formattedDate = $date_xxc->format('Y-m-d');

    $toplam_satis = sql_select("select city_name as key_1  , product_name as key_7 , amount as key_10 , delivery_warehouse as key_11 , delivery_date as key_12   , delivery_zone_name , stock_code
from workcube_sales 
where ( product_name in ('$sonuc_3') or product_name in ('$sonuc_2') or product_name in ('$sonuc_7') or product_name in ('$sonuc_8') or product_name in ('$sonuc_9') or stock_code in ('$sonuc_5') or stock_code in ('$main_stock_code') ) and  (delivery_warehouse in ('$sonuc') or city_name in ('$sonuc_4')) and delivery_date >= '$formattedDate' ", "modanet");


//****************************************************

    $envanter_bilgileri = moduler_envanter($filtrelenmis_depo, $urun_listesi, $_POST['limit']);


// Başlangıç tarihini ve bitiş tarihini oluşturun
    $tarih_baslangic = DateTime::createFromFormat('d.m.Y', $bolge_kritik_baslangic_tarihi);
    $tarih_bitis = DateTime::createFromFormat('d.m.Y', $bolge_kritik_bitis_tarihi);

// Tarihleri SQL formatına dönüştürün
    $tarih_baslangic->modify('-1 day');

    $tarih_baslangic_sql = $tarih_baslangic->format('Y-m-d');
    $tarih_bitis_sql = $tarih_bitis->format('Y-m-d');


// SQL sorgusu
    $dagitimlar = sql_select("SELECT * FROM dagilimlar WHERE sending_date >= '$tarih_baslangic_sql' AND sending_date <= '$tarih_bitis_sql'", "envanter");


    $bolge_talepler_moduler = sql_select("select * from bolgetalep where durum='ACIK' and tip='TALEP'  group by bolge , urun order by id desc ", "envanter");
    $bolge_dagilim_moduler = sql_select("select * from bolgetalep where durum='ACIK' and tip='DAGILIM' group by bolge , urun order by id desc", "envanter");

    $bolge_dagilim_mobetto = sql_select("select * from bolgetalepmobetto where durum='ACIK' and tip='DAGILIM'", "envanter");
    $bolge_talepler_mobetto = sql_select("select * from bolgetalepmobetto where durum='ACIK' and tip='TALEP'", "envanter");

    $sevkiyata_hazir_adet = 0;
    foreach ($filtrelenmis_depo as $depo) {

        $zone_name = $depo['sehir_adi'];

        $envanter_miktari = null;
        $day_calc = 0;
        $day_limit = 0;
        $urun_takim_adi = "ürün adı bulunamadı";
        $urun_eski_ad = "";
        $toplam = 0;
        $toplam_aralik = 0;
        $toplam_dis_satis = 0;
        $acik_bolge_dagilim = 0;
        $acik_bolge_talep = 0;
        $sevk_listesinde_olanlar = 0;
        $sevk_listesinde_olanlar_toplam = 0;
        $mavi_fab_uretim_envanteri = 0;
        $moduler_mamul_adeti = 0;
        $acik_bolge_talep_id = 0;
        $acik_bolge_dagilim_id = 0;
        $mobetto_mu = "";
        $acik_bolge_dagilim_aciklama = "";
        $acik_bolge_talep_aciklama = "";

        foreach ($envanter_bilgileri as $envanter) {

            if (trim($envanter['key_0']) === trim($depo['depo_ucuncu_adi'])) {
                // Depoya ait bir envanter bulundu
                $envanter_miktari = $envanter['key_2'];

                $day_calc = $envanter['key_2'];
                $day_calc_control = false;

                $karyola_mı = $envanter['key_13'];

                $urun_takim_adi = trim($envanter['key_3']);
                $stock_code = trim($envanter['key_14']);

                $urun_eski_ad = $envanter['key_1'];
                $mamul = $envanter['key_4'];
                $mavi_fabrika_uretim = $envanter['key_5'];
                $fabrika_1depo = $envanter['key_6'];
                $gri_fab_uretim = $envanter['key_7'];
                $altinova = $envanter['key_8'];
                $mobetto_mu = $envanter['key_9'];
                $mamul2 = $envanter['key_12'];

                $sevkiyata_hazir_adet = $envanter['key_4'];
                $urun_takim_adi_2 = $envanter['key_10'];
                $urun_takim_adi_3 = $envanter['key_11'];
                $main_stock_code = $envanter['main_stock_code'];

                $toplam = 0;

                foreach ($toplam_satis as $data) {

                    if (trim($depo['depo_ikinci_adi']) != trim($data['key_11']) && trim($data['key_1'] == trim($depo['sehir_adi'])) && (trim($urun_takim_adi) === trim($data['key_7']) or trim($stock_code) === trim($data['stock_code'])  or trim($main_stock_code) === trim($data['stock_code'])  )) {
                        $toplam_dis_satis += $data['key_10'];
                    }

                    if (($data['key_11'] === $depo['depo_ikinci_adi']) && (trim($urun_takim_adi) === trim($data['key_7']) or trim($stock_code) === trim($data['stock_code']) or trim($main_stock_code) === trim($data['stock_code'])  )) {

                        $toplam_aralik += $data['key_10'];

                        if ($day_calc_control == false) {
                            $day_calc = $day_calc - $data['key_10'];
                        }

                        if ($day_calc <= 0) {
                            // DateTime sınıfını kullanarak tarihleri oluşturun
                            $date1 = new DateTime($bolge_kritik_baslangic_tarihi_new);
                            $date2 = new DateTime($data['key_12']);

                            // İki tarih arasındaki farkı hesaplayın
                            $interval = $date1->diff($date2);

                            // Gün farkını alın
                            $day_limit = $interval->days;


                            // Pozitif bir fark için $interval->invert kontrol edebilirsiniz
                            if ($interval->invert == 1) {
                                $day_limit = -$day_limit;
                            }
                            $day_calc_control = true;
                        }


                    }

///****************************************************************************************************************************************************************************************************************************************

                    if ((trim($depo['sehir_adi']) == trim($data['key_1']) || $data['key_11'] == $depo['depo_ikinci_adi']) && (trim($urun_takim_adi) == trim($data['key_7'])) && $depo['sehir_bozuk_adi'] == "ANKARA" && $mobetto_mu != "MOBETTO" && $depo['depo_ucuncu_adi'] == "Akyurt Merkez") {
                        $toplam_aralik += $data['key_10'];
                        if (trim($depo['depo_ikinci_adi']) != trim($data['key_11'])) {
                            $toplam_dis_satis += $data['key_10'];
                        }

                    } elseif (((trim($depo['sehir_adi']) === trim($data['key_1']) || $data['key_11'] === $depo['depo_ikinci_adi']) && (trim($urun_takim_adi) === trim($data['key_7'])) && $depo['sehir_bozuk_adi'] === "ANKARA" && $mobetto_mu === "MOBETTO" && $depo['depo_ucuncu_adi'] === "Altınova")) {
                        $toplam_aralik += $data['key_10'];
                        if (trim($depo['depo_ikinci_adi']) != trim($data['key_11'])) {
                            $toplam_dis_satis += $data['key_10'];
                        }
                    }

///****************************************************************************************************************************************************************************************************************************************
                    if ($data['key_11'] == $depo['depo_ikinci_adi'] && ( trim($urun_takim_adi_3) == trim($data['key_7']) or trim($urun_takim_adi_2) == trim($data['key_7']) or  trim($urun_takim_adi) == trim($data['key_7']) or trim($stock_code) == trim($data['stock_code']))   or trim($main_stock_code) === trim($data['stock_code'])  ) {
                        $delivery_date = $data['key_12'];

                        if ($delivery_date <= $bolge_kritik_bitis_tarihi_new && $delivery_date >= $bolge_kritik_baslangic_tarihi_new) {
                            $toplam += $data['key_10'];
                        }

                    }

                }

                foreach ($dagitimlar as $b_dagilim) {

                    if (((trim($depo['sehir_bozuk_adi']) === trim($b_dagilim['bolge']) or trim($depo['sehir_adi']) === trim($b_dagilim['bolge'])) && (trim($urun_takim_adi) === trim($b_dagilim['urun']) || trim($urun_eski_ad) === trim($b_dagilim['urun']))) && $depo['sehir_bozuk_adi'] != "ANKARA") {
                        $sevk_listesinde_olanlar += $b_dagilim['adet'];
                    }

                    if (((trim($depo['sehir_bozuk_adi']) === trim($b_dagilim['bolge']) or trim($depo['sehir_adi']) === trim($b_dagilim['bolge'])) && (trim($urun_takim_adi) === trim($b_dagilim['urun']) || trim($urun_eski_ad) === trim($b_dagilim['urun']))) && $mobetto_mu != "MOBETTO" && $depo['sehir_bozuk_adi'] === "ANKARA" && $depo['depo_ucuncu_adi'] === "Akyurt Merkez") {
                        $sevk_listesinde_olanlar += $b_dagilim['adet'];

                    } else if (((trim($depo['sehir_bozuk_adi']) === trim($b_dagilim['bolge']) or trim($depo['sehir_adi']) === trim($b_dagilim['bolge'])) && (trim($urun_takim_adi) === trim($b_dagilim['urun']) || trim($urun_eski_ad) === trim($b_dagilim['urun']))) && $mobetto_mu === "MOBETTO" && $depo['sehir_bozuk_adi'] === "ANKARA" && $depo['depo_ucuncu_adi'] === "Altınova") {
                        $sevk_listesinde_olanlar += $b_dagilim['adet'];
                    }

                    if (trim($urun_takim_adi) === trim($b_dagilim['urun']) || trim($urun_eski_ad) === trim($b_dagilim['urun'])) {

                        if ($b_dagilim['sending_date'] >= $bugunun_tarihi_2 ) {
                            $sevk_listesinde_olanlar_toplam += $b_dagilim['adet'];
                        }

                    }
                }

                if ($mobetto_mu === "MOBETTO") {

                    foreach ($bolge_dagilim_mobetto as $dagilim) {
                        if ((trim($depo['sehir_bozuk_adi']) === trim($dagilim['bolge']) or trim($depo['sehir_adi']) === trim($dagilim['bolge'])) && (trim($urun_takim_adi) === trim($dagilim['urun']) || trim($urun_eski_ad) === trim($dagilim['urun']))) {
                            $acik_bolge_dagilim += $dagilim['kalan'];
                            $acik_bolge_dagilim_id = $dagilim['id'];
                            $acik_bolge_dagilim_aciklama = $dagilim['aciklama'];
                        }
                    }

                    foreach ($bolge_talepler_mobetto as $talep) {
                        if ((trim($depo['sehir_bozuk_adi']) === trim($talep['bolge']) or trim($depo['sehir_adi']) === trim($talep['bolge'])) && (trim($urun_takim_adi) === trim($talep['urun']) || trim($urun_eski_ad) === trim($talep['urun']))) {
                            $acik_bolge_talep += $talep['kalan'];
                            $acik_bolge_talep_id = $talep['id'];
                            $acik_bolge_talep_aciklama = $talep['aciklama'];
                        }
                    }

                } else {

                    foreach ($bolge_dagilim_moduler as $dagilim) {
                        if ((trim($depo['sehir_bozuk_adi']) === trim($dagilim['bolge'])) && (trim($urun_takim_adi) === trim($dagilim['urun']) || trim($urun_eski_ad) === trim($dagilim['urun']))) {
                            $acik_bolge_dagilim += $dagilim['kalan'];
                            $acik_bolge_dagilim_id = $dagilim['id'];
                            $acik_bolge_dagilim_aciklama = $dagilim['aciklama'];
                        }
                    }

                    foreach ($bolge_talepler_moduler as $talep) {
                        if ((trim($depo['sehir_bozuk_adi']) === trim($talep['bolge']) or trim($depo['sehir_adi']) === trim($talep['bolge'])) && (trim($urun_takim_adi) === trim($talep['urun']) || trim($urun_eski_ad) === trim($dagilim['urun']))) {
                            $acik_bolge_talep += $talep['kalan'];
                            $acik_bolge_talep_id = $talep['id'];
                            $acik_bolge_talep_aciklama = $talep['aciklama'];
                        }
                    }

                }

                if ($envanter_miktari - $toplam < 0) {
                    $eksik = $envanter_miktari - $toplam;
                } else {
                    $eksik = 0;
                }

                $kullanilabilir_envanter = $sevkiyata_hazir_adet - $sevk_listesinde_olanlar_toplam;


                $depo_ad = $depo['depo_ucuncu_adi'];
                if ($depo['depo_ucuncu_adi'] == 'Mamul') {
                    $depo_ad = "E-TİCARET";
                }

                if ($karyola_mı == "KARYOLA") {
                    $urun_takim_adi = $urun_eski_ad;
                }

                // Depoya ait envanter miktarını result dizisine ekleyin
                $result[] = array(
                    'day_limit' => $day_limit,
                    'zone' => $zone_name,
                    'depo_ucuncu_adi' => $depo_ad,
                    'depo_ikinci_adi' => $depo['depo_ikinci_adi'],
                    'sehir_adi' => $depo['sehir_adi'],
                    'envanter_miktari' => $envanter_miktari,
                    'urun_adi' => $urun_takim_adi,
                    'eski_ad' => $urun_eski_ad,
                    'toplam_aralik' => $toplam,
                    'toplam_satis' => $toplam_aralik,
                    'acik_bolge_dagilim' => $acik_bolge_dagilim,
                    'acik_bolge_talep' => $acik_bolge_talep,
                    'sevk_listesinde_olanlar' => $sevk_listesinde_olanlar,
                    'mavi_fab_envanter' => $mamul,
                    'mavi_fab_uretim_envanter' => $mavi_fabrika_uretim,
                    'mamul_2' => $mamul2,
                    'fabrika_1depo' => $fabrika_1depo,
                    'gri_fab_uretim' => $gri_fab_uretim,
                    'altinova' => $altinova,
                    'acik_bolge_talep_id' => $acik_bolge_talep_id,
                    'acik_bolge_dagilim_id' => $acik_bolge_dagilim_id,
                    'toplam_dis_satis' => $toplam_dis_satis,
                    'eksik' => $eksik,
                    'mobetto_mu' => $mobetto_mu,
                    'acik_bolge_dagilim_aciklama' => $acik_bolge_dagilim_aciklama,
                    'kullanilabilir_envanter' => $kullanilabilir_envanter,
                    'acik_bolge_talep_aciklama' => $acik_bolge_talep_aciklama
                );

                $day_limit = 0;
                $day_calc = 0;
                $depo_ad = "";
                $sevkiyata_hazir_adet = 0;
                $kullanilabilir_envanter = 0;
                $acik_bolge_dagilim_aciklama = "";
                $acik_bolge_talep_aciklama = "";
                $acik_bolge_dagilim = 0;
                $acik_bolge_talep = 0;
                $toplam = 0;
                $toplam_dis_satis = 0;
                $toplam_aralik = 0;
                $toplam_bolge_talep = 0;
                $sevk_listesinde_olanlar = 0;
                $sevk_listesinde_olanlar_toplam = 0;
                $moduler_mamul_adeti = 0;
                $acik_bolge_dagilim_id = '';
                $acik_bolge_talep_id = '';
                $mobetto_mu = "";
                $stock_code = "";

            }
        }
    }

    $filteredData = array();
    $smallestInventory = array();

    $filteredResult = array_values($result);
    $eksik_olanlar = array();
    if ($tip_adi === "EKSİKLER") {
        foreach ($filteredResult as $item) {
            $eksik_mi = $item['envanter_miktari'] - $item['toplam_satis'];
            if ($eksik_mi < 0) {
                $eksik_olanlar[] = $item; // Eksik olanları $eksik_olanlar dizisine ekleyin
            }
        }
        $filteredResult = array_values($eksik_olanlar);
    }


    if ($filteredResult == '') {
        echo "";
        exit();
    }

    echo json_encode($filteredResult);


}


//**************************************
//**************************************
//**************************************
//**************************************
//**************************************
//**************************************
//**************************************
//**************************************
//**************************************
//**************************************
//**************************************


if ($islem == "sevkiyat-listesini-getir-2") {

    ini_set('display_errors', 1);
    ini_set('error_reporting', 1);

    $today = date("Y-m-d");

    $extra_query = "";
    if (isset($_POST['takim_adi']) && count($_POST['takim_adi']) > 0 && $_POST['takim_adi'][0] != 'Tümü' && empty($_POST['bolge_kritik_unite'])) {
        $extra_query .= " and ( ";
        foreach ($_POST['takim_adi'] as $takim) {
            $takim_adi = '%' . $takim . '%'; // Takım adını LIKE için hazırla

            // Her takım adı için LIKE koşulunu oluştur
            $extra_query .= "  workcube_stock.product_name LIKE '$takim_adi' OR ";
        }
        $extra_query = rtrim($extra_query, " OR "); // Sonundaki " OR " ifadesini kaldır
        $extra_query .= " ) ";
    }

    if (isset($_POST['bolge_kritik_unite']) && strlen($_POST['bolge_kritik_unite']) > 0) {
        $unite = $_POST['bolge_kritik_unite'];
        $extra_query .= " and ( workcube_stock.product_name like '%$unite%' or workcube_stock.modanet_product_name like '%$unite%')  ";
    }

    if ($_POST['moduler'] == 'true' && $_POST['mobetto'] == 'false') {
        $extra_query .= " and workcube_stock.type !='MOBETTO' ";
    } elseif ($_POST['moduler'] == 'false' && $_POST['mobetto'] == 'true') {
        $extra_query .= " and workcube_stock.type ='MOBETTO' ";
    }


    $zones = "";
    if (isset($_POST['bolge_adi']) && !empty($_POST['bolge_adi']) && count($_POST['bolge_adi']) > 0) {
        $extra_query .= " and ( ";
        $zones .= " ( ";
        $zone_name = $_POST['bolge_adi'];

        foreach ($zone_name as $index => $item) {
            $extra_query .= " workcube_stock.story_name LIKE '%$item%' ";
            $zones .= " workcube_stock.story_name LIKE '%$item%' ";

            if ($index !== count($zone_name) - 1) {
                $extra_query .= " OR ";
                $zones .= " OR ";
            }
        }

        $extra_query .= "  ) ";
        $zones .= "  ) ";
    }

    if ($_POST['tip_adi'] == 'EKSİKLER') {
        $extra_query .= " HAVING  eksik < 0 and ";
    }


//
//    exit($extra_query);

    $bolge_kritik_baslangic_tarihi_2 = $_POST['bolge_kritik_baslangic_tarihi_2'];
    $bolge_kritik_bitis_tarihi_2 = $_POST['bolge_kritik_bitis_tarihi_2'];

    $one_day_ago = date("Y-m-d", strtotime($bolge_kritik_baslangic_tarihi_2 . ' -1 day'));


    $bolge_kritik_baslangic_tarihi_2 = date("Y-m-d", strtotime($bolge_kritik_baslangic_tarihi_2));
    $bolge_kritik_bitis_tarihi_2 = date("Y-m-d", strtotime($bolge_kritik_bitis_tarihi_2));


    if ($_POST['limit'] == 'max') {
        $limit = 'max';
    } else {
        $limit = 'min';
    }

    $sql = sql_select("SELECT stock_data.*,
       COALESCE(sales_data.total , 0 ) AS total,
       COALESCE(sales_data.into_sales , 0) AS into_sales,
       COALESCE(sales_data_2.auter_sales , 0) as auter_sales ,
       stock_data.story_name as depo_ucuncu_adi,
       stock_data.story_name as depo_ikinci_adi,
       zone_name_2 as zone ,
       stock_data.zone_name as sehir_adi,
       COALESCE(stock_data.amount ,0) as envanter_miktari,
       stock_data.modanet_product_name as urun_adi,
       stock_data.product_name as eski_ad,
       COALESCE(sales_data.into_sales , 0) as toplam_aralik,
       COALESCE(sales_data.total , 0) as toplam_satis,
       COALESCE(altınova.altınova_stock , 0) as altinova,
       COALESCE(sales_data_2.auter_sales , 0)   as toplam_dis_satis,
       COALESCE(modüler_mamul_stock_summary.moduler_mamul_stock , 0) AS mavi_fab_envanter,
       modüler_mamul_stock_summary.moduler_mamul_stock - COALESCE(distribution.toplam_sevk , 0) as kullanilabilir_envanter,
       COALESCE(gri_fab_uretim.min_amount1 , 0)                      AS gri_fab_uretim,
       COALESCE(mavi_fab_uretim_envanter.miktar , 0)                 AS mavi_fab_uretim_envanter,
       COALESCE(fabrika_1_modüler_mamül.moduler_mamul_stock , 0 )    AS fabrika_1depo,
       COALESCE(modüler_mamül.mamul , 0 )    AS mamul_2,
       COALESCE(distribution.adet , 0 )     AS sevk_listesinde_olanlar,
       COALESCE(request_4.kalan , request_5.kalan)      AS  acik_bolge_talep,
       COALESCE(request_4.id , request_5.id )    AS  acik_bolge_talep_id,
       COALESCE(NULLIF(request_4.aciklama, ''), NULLIF(request_5.aciklama, ''), '') as acik_bolge_talep_aciklama,
       COALESCE(request_2.kalan , request_3.kalan)        AS  acik_bolge_dagilim,
       COALESCE(request_2.id , request_3.id)     AS  acik_bolge_dagilim_id,
       COALESCE(NULLIF(request_2.aciklama, ''), NULLIF(request_3.aciklama, ''), '') as acik_bolge_dagilim_aciklama,
        type as mobetto_mu,
       CASE
           WHEN (min_amount - into_sales) < 0 THEN (min_amount - into_sales)
           ELSE 0
           END                                         AS eksik
FROM (SELECT DISTINCT modanet_product_name,
                      city_id,
                      type,
                      story_id,
                      $limit(amount)  AS min_amount,
                      amount,
                      stock_code,
                      main_stock_code,
                      story_name,
                      product_name,
                      zone_name,
                      zone_name_2
      FROM workcube_stock
      WHERE (stock_code LIKE '%P.M.%' OR stock_code LIKE '%U.M.%') and product_name != '' and modanet_product_name not like '%AYNA DAHİL%'
        AND story_name NOT IN
            ('Fabrika 1 Modüler Mamül','Modüler Mamül','Fabrika 1 Modüler Üretim','Sevkiyat 1','Mamul','Üretim 1') 
            $extra_query
      
      GROUP BY modanet_product_name, story_id) AS stock_data
         LEFT JOIN (SELECT product_name,
                           zone_id,
                           city_id,
                           story_id,
                           stock_code,
                           SUM(CASE WHEN delivery_date >= '$bolge_kritik_baslangic_tarihi_2' THEN amount ELSE 0 END)   AS total,
                           SUM(CASE
                                   WHEN delivery_date BETWEEN '$bolge_kritik_baslangic_tarihi_2' AND '$bolge_kritik_bitis_tarihi_2' THEN amount
                                   ELSE 0 END)                                                                       AS into_sales
                                                                                    
                    FROM workcube_sales
                    GROUP BY product_name, story_id) AS sales_data
                   ON (stock_data.main_stock_code = sales_data.stock_code)
                       AND (stock_data.story_id = sales_data.story_id OR
                            (sales_data.story_id IN (2, 35) AND stock_data.story_id IN (2, 35)))
        
         LEFT JOIN (SELECT product_name,
                           zone_id,
                           city_id,
                           story_id,
                           stock_code,
                           SUM( CASE
                                   WHEN delivery_date BETWEEN '$bolge_kritik_baslangic_tarihi_2' AND '$bolge_kritik_bitis_tarihi_2' AND zone_id != story_id AND
                                        city_id != zone_city_id THEN amount
                                   ELSE 0 END ) AS auter_sales
                    FROM workcube_sales
                    GROUP BY product_name , city_name) AS sales_data_2
             
                   ON stock_data.main_stock_code = sales_data_2.stock_code
                       AND (stock_data.story_id = sales_data_2.zone_id OR
                            (sales_data_2.zone_id IN (2, 35) AND stock_data.story_id IN (2, 35)))
         LEFT JOIN (SELECT modanet_product_name, stock_code , $limit(amount) AS moduler_mamul_stock
                    FROM workcube_stock
                    WHERE story_name = 'Modüler Mamül'
                    GROUP BY modanet_product_name) AS modüler_mamul_stock_summary
                   ON stock_data.main_stock_code = modüler_mamul_stock_summary.stock_code
         LEFT JOIN (SELECT modanet_product_name, $limit(amount) AS altınova_stock , main_stock_code
                    FROM workcube_stock
                    WHERE story_name = 'Altınova'
                    GROUP BY modanet_product_name) AS altınova
                   ON stock_data.main_stock_code = altınova.main_stock_code
     
     
         LEFT JOIN (SELECT modanet_product_name, $limit(amount) AS miktar , main_stock_code
                    FROM workcube_stock
                    WHERE story_name = 'Üretim 1'
                    GROUP BY modanet_product_name) AS mavi_fab_uretim_envanter	
                   ON stock_data.main_stock_code = mavi_fab_uretim_envanter.main_stock_code
        
         LEFT JOIN (SELECT modanet_product_name, $limit(amount) AS moduler_mamul_stock , main_stock_code
                    FROM workcube_stock
                    WHERE story_name = 'Fabrika 1 Modüler Mamül'
                    GROUP BY modanet_product_name) AS fabrika_1_modüler_mamül
                   ON stock_data.main_stock_code = fabrika_1_modüler_mamül.main_stock_code
    
         LEFT JOIN ( SELECT modanet_product_name, $limit(amount) AS mamul , main_stock_code
                    FROM workcube_stock
                    WHERE story_name = 'Mamul'
                    GROUP BY modanet_product_name ) AS modüler_mamül
                   ON stock_data.main_stock_code = modüler_mamül.main_stock_code
    
         LEFT JOIN (SELECT modanet_product_name, $limit(amount) AS min_amount1 , main_stock_code
                    FROM workcube_stock
                    WHERE story_name = 'Fabrika 1 Modüler Üretim'
                    GROUP BY modanet_product_name) AS gri_fab_uretim
                   ON stock_data.main_stock_code = gri_fab_uretim.main_stock_code

         LEFT JOIN (SELECT * , sum(adet) as toplam_sevk 
                    FROM envanter.dagilimlar
                    where envanter.dagilimlar.sending_date >= '$one_day_ago' and   envanter.dagilimlar.sending_date <= '$bolge_kritik_bitis_tarihi_2'
                    GROUP BY urun) AS distribution
                   ON stock_data.modanet_product_name = distribution.urun  and distribution.bolge = stock_data.zone_name

            inner JOIN (SELECT *  
                    FROM stories
                    where story_type='5' or zone_name = 'E-TİCARET'    
                    ) AS story
                   ON stock_data.story_id = story.id 

               LEFT JOIN envanter.bolgetalep request_2
                   ON stock_data.modanet_product_name = request_2.urun and (request_2.bolge = story.zone_name or request_2.bolge = story.bozuk_ad) and request_2.tip='DAGILIM' and request_2.durum='ACIK'
              
               LEFT JOIN envanter.bolgetalepmobetto request_3
                   ON stock_data.modanet_product_name = request_3.urun and (request_3.bolge = story.zone_name or request_3.bolge = story.bozuk_ad) and request_3.tip='DAGILIM' and request_3.durum='ACIK'
 
               LEFT JOIN envanter.bolgetalep request_4
                   ON stock_data.modanet_product_name = request_4.urun and (request_4.bolge = story.zone_name or request_4.bolge = story.bozuk_ad) and request_4.tip='TALEP' and request_4.durum='ACIK'
              
               LEFT JOIN envanter.bolgetalepmobetto request_5
                   ON stock_data.modanet_product_name = request_5.urun and (request_5.bolge = story.zone_name or request_5.bolge = story.bozuk_ad) and request_5.tip='TALEP' and request_5.durum='ACIK'
 
where stock_data.modanet_product_name != ''  

GROUP BY stock_data.story_name, stock_data.modanet_product_name", "modanet");

    if ($sql == '') {
        echo "";
        exit();
    }

    echo json_encode($sql);

}

if ($islem == "urun-gonderimi-ekle") {

    $ip = $_SERVER['REMOTE_ADDR'];

    $urun = $_POST['urun'];
    $bolge = $_POST['bolge'];
    $bolge_adi = $_POST['bolge_adi'];
    $tarih = $_POST['tarih'];
    $adet = $_POST['adet'];
    $aciklama = $_POST['aciklama'];
    $mobetto_mu = $_POST['mobetto_mu'];

    $tarih1 = strtotime($_POST['tarih']);

    $aciklama = $_POST['aciklama'];

    $id = $_POST['talep-id'];
    $talep_adet = $_POST['talep-adet'];
    $dagilim_adet = $_POST['dagilim-adet'];

    unset($_POST);


//******************
//******************
//******************
//******************
//******************


    if ($talep_adet <= 0 && $dagilim_adet <= 0) {

        $_POST['insert_userid'] = $_SESSION['user_id'];
        $_POST['insert_datetime'] = $now_datetime;
        $_POST['urun'] = $urun;
        $_POST['bolge'] = $bolge_adi;
        $_POST['adet'] = $adet;
        $_POST['tarih'] = $tarih;

        $date = $tarih;

        $tarih .= " 02:00:00";
        $_POST['tarih1'] = strtotime($tarih);

        $dateObj = date_create_from_format('d.m.Y', $date);
        $formattedDate = date_format($dateObj, 'Y-m-d');

        $_POST['sending_date'] = $formattedDate;

        $_POST['isleyentarih'] = date("d.m.y");
        $_POST['insert_date'] = date("Y-m-d");
        $_POST['aciklama'] = $aciklama;
        $_POST['ip'] = $ip;
        $_POST['id1'] = $id;


        if ($mobetto_mu == 'MOBETTO') {
            $_POST['kategori'] = 'MOBETTO';
        } else {
            $_POST['kategori'] = 'MODÜLER';
        }

        $sql_insert = sql_insert("dagilimlar", $_POST, "envanter");

        if ($sql_insert > 0) { ?>
            <script>

                alertify.set('notifier', 'position', 'top-left');


                alertify.warning('Sevkiyat Listesine Eklendi');
                alertify.success('Sevk İşlemi Tamamlandı');
            </script>
        <?php } else { ?>
            <script>

                alertify.set('notifier', 'position', 'top-left');


                alertify.error('HATA. Sevk İşlemi Tamamlanamadı');
            </script>
        <?php }


        exit();
    }


//*******************************************
//*******************************************
//*******************************************
//*******************************************
//*******************************************
//*******************************************
//*******************************************

    if ($mobetto_mu == 'MOBETTO') {
        $table_name = 'bolgetalepmobetto';
    } else {
        $table_name = 'bolgetalep';
    }

    $sql = sql_select("SELECT * FROM $table_name WHERE id=$id", "envanter");

    $giden_toplam_adet = $adet + $sql[0]['giden'];

    $_POST['giden'] = $giden_toplam_adet;
    $_POST['kalan'] = $sql[0]['adet'] - $giden_toplam_adet;


    if ($_POST['kalan'] < 0 || $_POST['kalan'] == 0) {
        $_POST['durum'] = "TAMAMLANDI";
    }


    $sql_update = sql_update($table_name, 'id', $id, $_POST, 'envanter');

    unset($_POST);

    $_POST['insert_userid'] = $_SESSION['user_id'];
    $_POST['insert_datetime'] = $now_datetime;
    $_POST['urun'] = $urun;
    $_POST['bolge'] = $bolge_adi;
    $_POST['adet'] = $adet;
    $_POST['tarih'] = $tarih;

    $date = $tarih;

    $tarih .= " 02:00:00";
    $_POST['tarih1'] = strtotime($tarih);

    $dateObj = date_create_from_format('d.m.Y', $date);
    $formattedDate = date_format($dateObj, 'Y-m-d');

    $_POST['sending_date'] = $formattedDate;

    $_POST['isleyentarih'] = date("d.m.y");
    $_POST['insert_date'] = date("Y-m-d");
    $_POST['aciklama'] = $aciklama;
    $_POST['ip'] = $ip;
    $_POST['id1'] = $id;


    if ($mobetto_mu == 'MOBETTO') {
        $_POST['kategori'] = 'MOBETTO';
    } else {
        $_POST['kategori'] = 'MODÜLER';
    }

    $sql_insert = sql_insert("dagilimlar", $_POST, "envanter");


    if ($sql[0]['adet'] - $giden_toplam_adet < 0) { ?>
        <script>
            alertify.set('notifier', 'position', 'top-left');
            alertify.warning('Talep Miktarından Fazlası Gönderildi');
        </script>
    <?php } else { ?>
        <script>
            alertify.set('notifier', 'position', 'top-left');
            alertify.success('Sevk İşlemi Tamamlandı');
        </script>
    <?php }

}


if ($islem == "sevkiyat-listesini-getir") {

    $ext_query = "";


    if (isset($_POST['shipment_list_date_start'])) {
        $start_date = $_POST['shipment_list_date_start'];
        $start_date = DateTime::createFromFormat('d.m.Y', $start_date);
        $start_date = $start_date->format('Y-m-d');
        $ext_query .= " and dagilimlar.sending_date >= '$start_date' ";
    }

    if (isset($_POST['shipment_list_date_end'])) {
        $end_date = $_POST['shipment_list_date_end'];
        $end_date = DateTime::createFromFormat('d.m.Y', $end_date);
        $end_date = $end_date->format('Y-m-d');
        $ext_query .= " and dagilimlar.sending_date <= '$end_date' ";
    }


    if (isset($_POST['shipment_list_zone'])) {
        $shipment_list_zone = $_POST['shipment_list_zone'];
        $ext_query .= " and dagilimlar.bolge like '%$shipment_list_zone%' ";
    }


    if (isset($_POST['shipent_list_product_name'])) {
        $shipent_list_product_name = $_POST['shipent_list_product_name'];
        $ext_query .= " and dagilimlar.urun like '%$shipent_list_product_name%' ";
    }

    if (isset($_POST['shipment_list_department'])) {
        $shipment_list_department = $_POST['shipment_list_department'];

        if ($shipment_list_department == 'MOBETTO') {
            $ext_query .= " and envanter.a3 like '%MOBETTO%' ";
        } elseif ($shipment_list_department == 'MODULER') {
            $ext_query .= " and envanter.a3 NOT like '%MOBETTO%' ";
        }
    }


    if (isset($_POST['shipment_list_car_no'])) {
        $shipment_list_car_no = $_POST['shipment_list_car_no'];
        $ext_query .= " and dagilimlar.arackod like '%$shipment_list_car_no%' ";
    }

    if (isset($_POST['shipment_description'])) {
        $shipment_description = $_POST['shipment_description'];
        $ext_query .= " and dagilimlar.aciklama like '%$shipment_description%' ";
    }


    $sql = sql_select("
SELECT volume AS total_volume,
       kilo,
       product_name,
       bolge,
       tarih,
       aciklama,
       count,
       id,
       sum(adet) as adet,
       stokkodu
FROM (SELECT (olculer.en * olculer.boy * olculer.yukseklik ) * adet  AS volume,
             SUM(olculer.kilo) * adet                                 AS kilo,
             dagilimlar.bolge,
             dagilimlar.tarih,
             dagilimlar.id,
             count(envanter.a4) * sum(adet) as count,
             sum(dagilimlar.adet) as adet,
             dagilimlar.urun,
             dagilimlar.aciklama,
             envanter.product_name,
             envanter.stokkodu
      FROM envanter.envanter
               INNER JOIN
           palet_ekran.olculer ON palet_ekran.olculer.paket = envanter.a4
               INNER JOIN
           envanter.dagilimlar ON envanter.dagilimlar.urun = envanter.product_name or envanter.dagilimlar.urun = envanter.a4 or envanter.dagilimlar.urun = envanter.a7 or envanter.dagilimlar.urun = envanter.a8
      WHERE 1=1 $ext_query
      GROUP BY dagilimlar.urun, dagilimlar.bolge, dagilimlar.tarih) AS subquery
GROUP BY urun, bolge, tarih;
", "envanter");



    echo json_encode($sql);

}
if ($islem == "talepleri-getir") {


    $ext_query = " 1=1 ";

    if (isset($_POST['zone_name']) && !empty($_POST['zone_name'])) {
        if ($_POST['zone_name'] != 'Tümü') {
            $ext_query .= " and bolge = '$_POST[zone_name]' ";
        }
    }

    if (isset($_POST['product_name']) && !empty($_POST['product_name'])) {

        $product_name = sql_select("select * from envanter where a4 like  '%$_POST[product_name]%' or product_name like  '%$_POST[product_name]%' or a7 like  '%$_POST[product_name]%' or a8 like '%$_POST[product_name]%' group by a4", "envanter");

        if (isset($product_name[0]['product_name']) && !empty($product_name[0]['product_name'])) {
            $product_name_1 = $product_name[0]['product_name'];
        } else {
            $product_name_1 = "XXXPX";
        }

        if (isset($product_name[0]['a6']) && !empty($product_name[0]['a6'])) {
            $product_name_2 = $product_name[0]['a6'];
        } else {
            $product_name_2 = "XXXPX";
        }

        if (isset($product_name[0]['a7']) && !empty($product_name[0]['a7'])) {
            $product_name_3 = $product_name[0]['a7'];
        } else {
            $product_name_3 = "XXXPX";
        }

        if (isset($product_name[0]['a8']) && !empty($product_name[0]['a8'])) {
            $product_name_4 = $product_name[0]['a8'];
        } else {
            $product_name_4 = "XXXPX";
        }

        if (isset($product_name[0]['a4']) && !empty($product_name[0]['a4'])) {
            $product_name_5 = $product_name[0]['a4'];
        } else {
            $product_name_5 = "XXXPX";
        }


        if (isset($product_name[1]['a4']) && !empty($product_name[1]['a4'])) {
            $product_name_6 = $product_name[1]['a4'];
        } else {
            $product_name_6 = "XXXPX";
        }


        if (isset($product_name[2]['a4']) && !empty($product_name[2]['a4'])) {
            $product_name_7 = $product_name[2]['a4'];
        } else {
            $product_name_7 = "XXXPX";
        }

        if (isset($product_name[3]['a4']) && !empty($product_name[3]['a4'])) {
            $product_name_8 = $product_name[3]['a4'];
        } else {
            $product_name_8 = "XXXPX";
        }

        if (isset($product_name[4]['a4']) && !empty($product_name[4]['a4'])) {
            $product_name_9 = $product_name[4]['a4'];
        } else {
            $product_name_9 = "XXXPX";
        }

        if (isset($product_name[5]['a4']) && !empty($product_name[5]['a4'])) {
            $product_name_10 = $product_name[5]['a4'];
        } else {
            $product_name_10 = "XXXPX";
        }

        $ext_query .= " and ( urun like '%$_POST[product_name]%' or urun like '%$product_name_10%' or urun like '%$product_name_9%' or urun like '%$product_name_8%' or urun like '%$product_name_7%' or urun like '%$product_name_6%' or urun like '%$product_name_5%' or urun like '%$product_name_1%' or urun like '%$product_name_2%' or urun like '%$product_name_3%' or urun like '%$product_name_4%' )";


    }

    if (isset($_POST['type']) && !empty($_POST['type'])) {

        if ($_POST['type'] != 'Tümü') {
            $ext_query .= " and tip = '$_POST[type]' ";
        }
    }

    if (isset($_POST['type_2']) && !empty($_POST['type_2'])) {


        if ($_POST['type_2'] == "Dış") {
            $ext_query .= " and acan is not null ";
        }

    }

    if (isset($_POST['description']) && !empty($_POST['description'])) {
        $ext_query .= " and aciklama = '$_POST[description]' ";
    }

    if (isset($_POST['status']) && !empty($_POST['status'])) {

        if ($_POST['status'] != "Tümü") {
            $ext_query .= " and durum = '$_POST[status]' ";
        }


    }

    if (isset($_POST['department']) && !empty($_POST['department'])) {

        if ($_POST['department'] == "Mobetto") {
            $table_name .= " bolgetalepmobetto ";
        } else {
            $table_name .= " bolgetalep ";
        }

    }

    if (isset($_POST['start']) && !empty($_POST['start'])) {

        $_POST['start'] = DateTime::createFromFormat('d.m.Y', $_POST['start']);
        $_POST['start'] = $_POST['start']->format('Y-m-d');
        $ext_query .= " and process_date >= '$_POST[start]' ";

    }

    if (isset($_POST['end']) && !empty($_POST['end'])) {

        $_POST['end'] = DateTime::createFromFormat('d.m.Y', $_POST['end']);
        $_POST['end'] = $_POST['end']->format('Y-m-d');
        $ext_query .= " and process_date <= '$_POST[end]' ";

    }

    if (strpos($ext_query, ' and') === 0) {
        $ext_query = substr($ext_query, 4); // İlk dört karakteri kaldırır.
    }

    if (strpos($ext_query, ' and') === 0) {
        $ext_query = substr($ext_query, 4); // İlk dört karakteri kaldırır.
    }

    $sql = sql_select(" select * , '$table_name' as department from $table_name where $ext_query  order by id desc limit 5000", "envanter");


    echo json_encode($sql);

}


if ($islem == "talepleri-toplu-sil") {

    $ids = $_POST['ids'];
    $deps = $_POST['department'];

    $i = 0;
    foreach ($ids as $id) {


        $dep = $deps[$i];
        $sql = sql_delete("$dep", "$id", "envanter");

        $i++;

        ?>
        <script>
            alertify.success('Silme İşlemi Başarılı');
        </script>
        <?php
    }


}


if ($islem == "talepleri-toplu-sil-2") {

    $id = $_POST['id'];
    $dep = trim($_POST['dep']);

    $sql = sql_delete("$dep", "$id", "envanter");

    ?>
    <script>
        alertify.success('Silme İşlemi Başarılı');
    </script>
    <?php


}


if ($islem == "talepleri-güncelle") {

    $id = $_POST['id'];
    $dep = trim($_POST['dep']);
    $description = trim($_POST['description']);


    $amount = $_POST['amount'];
    $kalan = $_POST['remainder'];
    $giden = $_POST['sended'];


    unset($_POST['dep']);
    unset($_POST['id']);
    unset($_POST['description']);

    if ($giden === '') {
        $giden = 0;
    }

    if ($kalan === '') {
        $kalan = 0;
    }

    unset($_POST['sended']);
    unset($_POST['amount']);
    unset($_POST['remainder']);

    $_POST['adet'] = $amount;
    $_POST['aciklama'] = $description;
    $_POST['kalan'] = (float)$amount - (float)$giden;


    if ($_POST['kalan'] <= 0) {
        $_POST['kalan'] = 0;
        $_POST['durum'] = "TAMAMLANDI";
    } else {
        $_POST['durum'] = "ACIK";
    }

    $_POST['update_datetime'] = date('Y-m-d H:i:s');
    $_POST['update_userid'] = $_SESSION['user_id'];

    $sql = sql_update("$dep", "id", "$id", $_POST, "envanter");

    ?>


    <script>
        alertify.success('Güncelleme İşlemi Başarılı');
    </script>
    <?php


}
if ($islem == 'get-envanter-list') {

    $ext_query = "";
    if (isset($_POST['product_name']) && !empty($_POST['product_name'])) {
        $product_name = $_POST['product_name'];
        $ext_query .= " and a4 like '%$product_name%' or product_name like '%$product_name%' or a7 like '%$product_name%' or a8 like '%$product_name%' ";
    }


    if (isset($_POST['set']) && !empty($_POST['set'])) {
        $set = $_POST['set'];
        $ext_query .= " and a1 like '%$set%' ";
    }

    if (isset($_POST['type']) && !empty($_POST['type'])) {
        $type = $_POST['type'];
        $ext_query .= " and a2 like '%$type%' ";
    }

    if (isset($_POST['type_2']) && !empty($_POST['type_2'])) {
        $type_2 = $_POST['type_2'];
        $ext_query .= " and a2 like '%$type_2%' ";
    }

    $sql = sql_select("select * from envanter where  1=1 $ext_query", "envanter");

    echo json_encode($sql);

}

if ($islem == 'envanter-fabrika-sistem-paket-ayarla') {

    $id = $_POST['id'];
    $status = $_POST['status'];

    $array = array();
    if ($status == 1) {
        $array['sistemdurum1'] = 'ACIK';
    } else {
        $array['sistemdurum1'] = 'KAPALI';
    }


    $sql = sql_update("envanter", "id", $id, $array, "envanter");

    if ($sql) {
        echo message("Güncelleme İşlemi Başarılı", 'success');
    }

}

if ($islem == 'envanter-bölge-sistem-paket-ayarla') {

    $id = $_POST['id'];
    $status = $_POST['status'];

    $array = array();
    if ($status == 1) {
        $array['sistemdurum2'] = 'ACIK';
    } else {
        $array['sistemdurum2'] = 'KAPALI';
    }

    $sql = sql_update("envanter", "id", $id, $array, "envanter");

    if ($sql) {
        echo message("Güncelleme İşlemi Başarılı", 'success');
    }

}

if ($islem == 'envanter-bölge-paketin-ünite-içinde-yer-alması-ayarla') {

    $id = $_POST['id'];
    $status = $_POST['status'];

    $array = array();
    if ($status == 1) {
        $array['sistemdurumunitepaket'] = 'ACIK';
    } else {
        $array['sistemdurumunitepaket'] = 'KAPALI';
    }

    $sql = sql_update("envanter", "id", $id, $array, "envanter");

    if ($sql) {
        echo message("Güncelleme İşlemi Başarılı", 'success');
    }

}

if ($islem == "envanter-bölge-sistem-ünite-ayarla") {

    $id = $_POST['id'];
    $status = $_POST['status'];

    $array = array();
    if ($status == 1) {
        $array['sevkiyatsistemdurum'] = 'ACIK';
    } else {
        $array['sevkiyatsistemdurum'] = 'KAPALI';
    }

    $sql = sql_update("envanter", "id", $id, $array, "envanter");

    if ($sql) {
        echo message("Güncelleme İşlemi Başarılı", 'success');
    }

}


if ($islem == "envanter-fabrika-sistem-ünite-ayarla") {

    $id = $_POST['id'];
    $status = $_POST['status'];

    $array = array();
    if ($status == 1) {
        $array['sevkiyatistemdurumfabrika'] = 'ACIK';
    } else {
        $array['sevkiyatistemdurumfabrika'] = 'KAPALI';
    }

    $sql = sql_update("envanter", "id", $id, $array, "envanter");

    if ($sql) {
        echo message("Güncelleme İşlemi Başarılı", 'success');
    }

}
if ($islem == 'get-product-list-modular') {


    $ext_query = "";

    if (isset($_POST['type']) && !empty($_POST['type'])) {
        $ext_query .= " and type like '" . $_POST['type'] . "' ";
    }


    if (isset($_POST['group']) && !empty($_POST['group'])) {
        $ext_query .= " and group_name like '" . $_POST['group'] . "' ";
    }

    if (isset($_POST['team']) && !empty($_POST['team'])) {
        $ext_query .= " and team like '" . $_POST['team'] . "' ";
    }

    if (isset($_POST['product_name']) && !empty($_POST['product_name'])) {
        $ext_query .= " and  (product_name like '" . $_POST['product_name'] . "' or part_name like '" . $_POST['product_name'] . "') ";
    }

    if (isset($_POST['stock']) && !empty($_POST['stock'])) {
        $ext_query .= " and  (stock_code like '" . $_POST['stock'] . "' or main_stock_code like '" . $_POST['stock'] . "') ";
    }


    $sql = sql_select("select * from moduler_inventory where  1=1 $ext_query", "modanet");
    echo json_encode($sql);

}if ($islem ==  'change-value-moduler-inventory'){

    $id = $_POST['id'];
    unset($_POST['id']);
    $name = $_POST['name'];
    $value = trim($_POST['value']);

    $array = [
        $name => $value ,
        'update_userid' => $_SESSION['user_id'] ,
        'update_datetime' => date('Y-m-d H:i:s')
    ];

    $sql = sql_update("moduler_inventory", "id", $id, $array, "modanet");

    if ($sql != false) {
        echo message("Güncellendi", "success");
    }

    echo $_SESSION['error'];




}if ($islem == 'delete-product-moduler'){

    $id = $_POST['id'];
    $sql = sql_delete("moduler_inventory",  $id , "modanet");

    if ($sql != false) {
        echo message("Silindi", "warning");
    }

}if ($islem == 'envanter-ad-düzenle'){


    $id = $_POST['id'];
    unset($_POST['id']);
    $name = $_POST['name'];
    $value = trim($_POST['value']);

    $array = [
        $name => $value
    ];
    $sql = sql_update("envanter", "id", $id, $array, "envanter");

    if ($sql != false) {
        echo message("Güncellendi", "success");
    }
}

?>




