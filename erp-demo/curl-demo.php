<?php
require_once("function.php");
ini_set('max_execution_time', 1900);


ini_set('display_errors', 0);

function moduler_envanter($depolar, $urun_listesi, $limit)
{


    if ($limit == "min") {

        foreach ($urun_listesi as $key1 => $item) {
            foreach ($urun_listesi as $key2 => $value) {
                if ($item['product_name'] === $value['product_name'] && $key1 !== $key2 && $item['a2'] != 'KARYOLA') {
                    $urun_listesi[$key1]['fabrika1depo'] = min($item['fabrika1depo'], $value['fabrika1depo']);
                    $urun_listesi[$key1]['grifaburetim'] = min($item['grifaburetim'], $value['grifaburetim']);
                    $urun_listesi[$key1]['mamul'] = min($item['mamul'], $value['mamul']);
                    $urun_listesi[$key1]['altinova'] = min($item['altinova'], $value['altinova']);
                    $urun_listesi[$key1]['fabrika1'] = min($item['fabrika1'], $value['fabrika1']);

                    $urun_listesi[$key2]['fabrika1depo'] = min($item['fabrika1depo'], $value['fabrika1depo']);
                    $urun_listesi[$key2]['grifaburetim'] = min($item['grifaburetim'], $value['grifaburetim']);
                    $urun_listesi[$key2]['mamul'] = min($item['mamul'], $value['mamul']);
                    $urun_listesi[$key2]['altinova'] = min($item['altinova'], $value['altinova']);
                    $urun_listesi[$key2]['fabrika1'] = min($item['fabrika1'], $value['fabrika1']);
                }
            }
        }

    } else {

        foreach ($urun_listesi as $key1 => $item) {
            foreach ($urun_listesi as $key2 => $value) {
                if ($item['product_name'] === $value['product_name'] && $key1 !== $key2 && $item['a2'] != 'KARYOLA') {
                    $urun_listesi[$key1]['fabrika1depo'] = max($item['fabrika1depo'], $value['fabrika1depo']);
                    $urun_listesi[$key1]['grifaburetim'] = max($item['grifaburetim'], $value['grifaburetim']);
                    $urun_listesi[$key1]['mamul'] = max($item['mamul'], $value['mamul']);
                    $urun_listesi[$key1]['mamul_2'] = max($item['mamul_2'], $value['mamul_2']);
                    $urun_listesi[$key1]['altinova'] = max($item['altinova'], $value['altinova']);
                    $urun_listesi[$key1]['fabrika1'] = max($item['fabrika1'], $value['fabrika1']);

                    $urun_listesi[$key2]['fabrika1depo'] = max($item['fabrika1depo'], $value['fabrika1depo']);
                    $urun_listesi[$key2]['grifaburetim'] = max($item['grifaburetim'], $value['grifaburetim']);
                    $urun_listesi[$key2]['mamul'] = max($item['mamul'], $value['mamul']);
                    $urun_listesi[$key2]['mamul_2'] = max($item['mamul_2'], $value['mamul_2']);
                    $urun_listesi[$key2]['altinova'] = max($item['altinova'], $value['altinova']);
                    $urun_listesi[$key2]['fabrika1'] = max($item['fabrika1'], $value['fabrika1']);

                }
            }
        }

    }


    $tarayici = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0';

    $curl = curl_init();
    $adres = 'http://192.168.1.83:81/rapor/workcuberapor.php';
    curl_setopt($curl, CURLOPT_URL, $adres);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT, 170);
    curl_setopt($curl, CURLOPT_USERAGENT, $tarayici);
    curl_setopt($curl, CURLOPT_ENCODING, 'UTF-8'); // Karakter kodlamasını belirtin

    $data = curl_exec($curl);
    curl_close($curl);

    $dom = new DOMDocument();
    @$dom->loadHTML($data);
    $xpath = new DOMXPath($dom);

    $tableRows = $xpath->query('//tr');
    $result = array();

    foreach ($tableRows as $rowIndex => $row) {
        $tdNodes = $xpath->query('td', $row);
        $rowData = array();

        foreach ($tdNodes as $cellIndex => $td) {
            $key = "key_{$cellIndex}";
            $rowData[$key] = $td->nodeValue;
        }

        $result[] = $rowData;
    }


    $parcali_urunler = array();

    foreach ($depolar as $depo) {
        $parcali_urunler_depo = array();

        foreach ($result as $item) {
            if (trim($depo['depo_ucuncu_adi']) === trim($item['key_0']) || trim($depo['depo_ucuncu_adi']) == 'İhracat Merkez Depo') {
                foreach ($urun_listesi as $urun) {


                    if (trim($urun['a4']) === trim($item['key_1'])) {
                        $a5 = trim($urun['product_name']);
                        $a4 = trim($urun['a4']);
                        $key_2 = $item['key_2'];

                        $pos124 = strpos($item['product_name'], "AYNA DAHİL");

                        if ($limit == "min") {

                            if ((!isset($parcali_urunler_depo[$a5]) || $key_2 < $parcali_urunler_depo[$a5]['key_2']) && $urun['a2'] != 'KARYOLA' && $pos124 == false) {

                                if (trim($depo['depo_ucuncu_adi']) == 'İhracat Merkez Depo'){
                                    $item['key_0'] = 'İhracat Merkez Depo';
                                }

                                $parcali_urunler_depo[$a5] = array(
                                    'key_1' => $item['key_1'],
                                    'key_0' => $item['key_0'],
                                    'key_2' => $item['key_2'],
                                    'key_3' => $urun['product_name'],
                                    'key_4' => $urun['mamul'],
                                    'key_5' => $urun['fabrika1'],
                                    'key_6' => $urun['fabrika1depo'],
                                    'key_7' => $urun['grifaburetim'],
                                    'key_8' => $urun['altinova'],
                                    'key_9' => $urun['a3'],
                                    'key_10' => $urun['a7'],
                                    'key_11' => $urun['a8'],
                                    'key_12' => $urun['mamul_2'],
                                    'key_13' => $urun['a2'],
                                    'key_14' => $urun['stokkodu'],
                                    'key_15' => $urun['main_stock_code'],
                                );

                            }

                        } else {

                            if ((!isset($parcali_urunler_depo[$a5]) || $key_2 > $parcali_urunler_depo[$a5]['key_2']) && $urun['a2'] != 'KARYOLA') {
                                $parcali_urunler_depo[$a5] = array(
                                    'key_1' => $item['key_1'],
                                    'key_0' => $item['key_0'],
                                    'key_2' => $item['key_2'],
                                    'key_3' => $urun['product_name'],
                                    'key_4' => $urun['mamul'],
                                    'key_5' => $urun['fabrika1'],
                                    'key_6' => $urun['fabrika1depo'],
                                    'key_7' => $urun['grifaburetim'],
                                    'key_8' => $urun['altinova'],
                                    'key_9' => $urun['a3'],
                                    'key_10' => $urun['a7'],
                                    'key_11' => $urun['a8'],
                                    'key_12' => $urun['mamul_2'],
                                    'key_13' => $urun['a2'],
                                    'key_14' => $urun['stokkodu'],
                                    'key_15' => $urun['main_stock_code'],
                                );
                            }
                        }


                        if ($urun['a2'] == 'KARYOLA') {

                            $parcali_urunler_depo[$a4] = array(
                                'key_1' => $item['key_1'],
                                'key_0' => $item['key_0'],
                                'key_2' => $item['key_2'],
                                'key_3' => $urun['product_name'],
                                'key_4' => $urun['mamul'],
                                'key_5' => $urun['fabrika1'],
                                'key_6' => $urun['fabrika1depo'],
                                'key_7' => $urun['grifaburetim'],
                                'key_8' => $urun['altinova'],
                                'key_9' => $urun['a3'],
                                'key_10' => $urun['a7'],
                                'key_11' => $urun['a8'],
                                'key_12' => $urun['mamul_2'],
                                'key_13' => $urun['a2'],
                                'key_14' => $urun['stokkodu'],
                                'key_15' => $urun['main_stock_code'],                            );
                        }

                    }
                }
            }
        }
        // Her depo için toplanan parçalı ürünleri genel diziye ekleyelim.
        $parcali_urunler = array_merge($parcali_urunler, array_values($parcali_urunler_depo));
    }


    return $parcali_urunler;
}


/////////////////////***************************************************************************************************
/////////////////////***************************************************************************************************
/////////////////////***************************************************************************************************
/////////////////////***************************************************************************************************
/////////////////////***************************************************************************************************

function moduler_toplam_satis_getir($filtrelenmis_depo, $urun_listesi, $bolge_kritik_baslangic_tarihi)
{

    $data = array(); // Curl özkaynaklarını bir dizi içinde saklayalım

    $yeni_satir = array(
        'id' => 41,
        'sehir_adi' => 'E-TİCARET',
        'depo_ilk_adi' => 'Yeni Depo',
        'depo_ikinci_adi' => 'Yeni Depo 2',
        'depo_ucuncu_adi' => 'Yeni Depo 3',
        'status' => 1,
        'sehir_bozuk_adi' => 'E-TİCARET'
    );

    $filtrelenmis_depo[] = $yeni_satir;

    $yeni_satir = array(
        'id' => 41,
        'sehir_adi' => 'MODALIFE PLASTİK',
        'depo_ilk_adi' => 'Yeni Depo 4',
        'depo_ikinci_adi' => 'Yeni Depo 6',
        'depo_ucuncu_adi' => 'Yeni Depo 5',
        'status' => 1,
        'sehir_bozuk_adi' => 'MODALIFE PLASTİK'
    );
    $filtrelenmis_depo[] = $yeni_satir;


    $filtrelenmis_depo = array_values($filtrelenmis_depo);

// Silinecek değerleri belirle
    $ara_1 = 'Altınova';
    $ara_2 = 'Akyurt Merkez';

// Bulunan indisleri tutacak değişkenleri tanımla
    $altinova_bulunanIndis = -1;
    $akyurt_bulunanIndis = -1;

// Değerin bulunduğu indisleri ara
    foreach ($filtrelenmis_depo as $index => $row) {
        if ($row['depo_ucuncu_adi'] === $ara_1) {
            $altinova_bulunanIndis = $index;
        }

        if ($row['depo_ucuncu_adi'] === $ara_2) {
            $akyurt_bulunanIndis = $index;
        }

        if ($row['sehir_adi'] === "ADANA") {
            $yeni_satir = array(
                'id' => 46,
                'sehir_adi' => 'MERSİN',
                'depo_ilk_adi' => 'Yeni Depo 7',
                'depo_ikinci_adi' => 'Yeni Depo 5',
                'depo_ucuncu_adi' => 'Yeni Depo 9',
                'status' => 1,
                'sehir_bozuk_adi' => 'MERSİN'
            );
            $filtrelenmis_depo[] = $yeni_satir;
        }

    }

// Eğer değer bulunduysa, indisini sil
    if ($altinova_bulunanIndis !== -1 && $akyurt_bulunanIndis !== -1) {
        unset($filtrelenmis_depo[$altinova_bulunanIndis]);
    }

// Yeniden indeksleme yapabilirsiniz
    $filtrelenmis_depo = array_values($filtrelenmis_depo);

    foreach ($filtrelenmis_depo as $item) {

        $sehir = $item['sehir_adi'];
        $curl = curl_init();
        $adres = 'http://192.168.1.83:81/rapor/sor.php';
        curl_setopt($curl, CURLOPT_URL, $adres);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 50);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "program=workcube&gosterme_secenegi=ekran&tarih_tipi=termin&bolge=$sehir&belgetarih1=$bolge_kritik_baslangic_tarihi&belgetarih2=01.01.5000&kelime1= ; &kelime2=");

        $data[] = curl_exec($curl);
        curl_close($curl);

    }


    $dom = new DOMDocument();
    $data = implode("", $data);
    @$dom->loadHTML($data);
    $xpath = new DOMXPath($dom);

    $tableRows = $xpath->query('//tr');
    $result = array();

    foreach ($tableRows as $rowIndex => $row) {

        $tdNodes = $xpath->query('td', $row);
        $rowData = array();

        // Sadece belirli anahtarları alın


        $rowData['key_1'] = $tdNodes->item(1)->nodeValue;

        if ($rowData['key_1'] == "MERSİN") {
            $rowData['key_1'] = "ADANA";
        }

        $rowData['key_6'] = trim($tdNodes->item(6)->nodeValue);
        $rowData['key_7'] = trim($tdNodes->item(7)->nodeValue);
        $rowData['key_10'] = trim($tdNodes->item(10)->nodeValue);
        $rowData['key_11'] = trim($tdNodes->item(11)->nodeValue);
        $rowData['key_12'] = trim($tdNodes->item(12)->nodeValue);

        $result[] = $rowData;

    }

    return $result;
}


//**********************************************************************************************************************
//**********************************************************************************************************************
//**********************************************************************************************************************
//**********************************************************************************************************************
//**********************************************************************************************************************
//**********************************************************************************************************************
//**********************************************************************************************************************
//**********************************************************************************************************************
//**********************************************************************************************************************

function toplam_satis_getir($city_name, $product_name, $bolge_kritik_baslangic_tarihi)
{

    $data = array(); // Curl özkaynaklarını bir dizi içinde saklayalım


    $curl = curl_init();
    $adres = 'http://192.168.1.83:81/rapor/sor.php';
    curl_setopt($curl, CURLOPT_URL, $adres);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 50);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "program=workcube&gosterme_secenegi=ekran&tarih_tipi=termin&bolge=$city_name&belgetarih1=$bolge_kritik_baslangic_tarihi&belgetarih2=01.01.5000&kelime1=$product_name&kelime2=");

    $data[] = curl_exec($curl);
    curl_close($curl);


    $dom = new DOMDocument();
    $data = implode("", $data);
    @$dom->loadHTML($data);
    $xpath = new DOMXPath($dom);

    // İlk tr etiketini kaldırma ve JSON formatına dönüştürme
    $firstRow = $xpath->query('//tr[1]');
    if ($firstRow->length > 0) {
        $firstRow->item(0)->parentNode->removeChild($firstRow->item(0));
    }

    $tableRows = $xpath->query('//tr');
    $result = array();


    foreach ($tableRows as $rowIndex => $row) {

        $tdNodes = $xpath->query('td', $row);
        $rowData = array();

        // Sadece belirli anahtarları alın
        $rowData['key_0'] = $tdNodes->item(0)->nodeValue;
        $rowData['key_1'] = $tdNodes->item(1)->nodeValue;
        $rowData['key_2'] = $tdNodes->item(2)->nodeValue;
        $rowData['key_3'] = $tdNodes->item(3)->nodeValue;
        $rowData['key_5'] = $tdNodes->item(5)->nodeValue;
        $rowData['key_6'] = $tdNodes->item(6)->nodeValue;
        $rowData['key_7'] = $tdNodes->item(7)->nodeValue;
        $rowData['key_10'] = $tdNodes->item(10)->nodeValue;
        $rowData['key_11'] = $tdNodes->item(11)->nodeValue;
        $rowData['key_12'] = $tdNodes->item(12)->nodeValue;

        $result[] = $rowData;

    }

    return $result;
}


/////*******************************************************************************************************************
/////*******************************************************************************************************************
/////*******************************************************************************************************************
/////*******************************************************************************************************************
/////*******************************************************************************************************************
/////*******************************************************************************************************************
/////*******************************************************************************************************************


function aralik_teslimat_dis_satis_detayi($zone_name, $workcube_teslimat_deposu_adi, $takim_adi, $start_date, $end_date, $tip)
{

    $curl = curl_init();
    $adres = 'http://192.168.1.83:81/rapor/sor.php';
    curl_setopt($curl, CURLOPT_URL, $adres);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_POST, true);
//    curl_setopt($curl, CURLOPT_TIMEOUT, 50);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "program=workcube&excel=1&gosterme_secenegi=ekran&tarih_tipi=termin&bolge=$zone_name&belgetarih1=$start_date&belgetarih2=$end_date&kelime1=$takim_adi&kelime2=");

    $data = curl_exec($curl);
    curl_close($curl);

    $dom = new DOMDocument();
    @$dom->loadHTML($data);
    $xpath = new DOMXPath($dom);

    // İlk tr etiketini kaldırma ve JSON formatına dönüştürme
    $firstRow = $xpath->query('//tr[1]');
    if ($firstRow->length > 0) {
        $firstRow->item(0)->parentNode->removeChild($firstRow->item(0));
    }

    $tableRows = $xpath->query('//tr');

    foreach ($tableRows as $rowIndex => $row) {
        $tdNodes = $xpath->query('td', $row);
        $rowData = array();

        // Sadece belirli anahtarları alın
        $rowData['key_0'] = $tdNodes->item(0)->nodeValue;
        $rowData['key_1'] = $tdNodes->item(1)->nodeValue;
        $rowData['key_4'] = $tdNodes->item(4)->nodeValue;
        $rowData['key_5'] = $tdNodes->item(5)->nodeValue;
        $rowData['key_6'] = $tdNodes->item(6)->nodeValue;
        $rowData['key_7'] = $tdNodes->item(7)->nodeValue;
        $rowData['key_8'] = $tdNodes->item(8)->nodeValue;
        $rowData['key_10'] = $tdNodes->item(10)->nodeValue;
        $rowData['key_11'] = $tdNodes->item(11)->nodeValue;
        $rowData['key_12'] = $tdNodes->item(12)->nodeValue;

        if ($tip == "ic") {
            if ($rowData['key_11'] === $workcube_teslimat_deposu_adi) {
                $result[] = $rowData;
            }
        } elseif ($tip == "dis") {
            if ($rowData['key_11'] != $workcube_teslimat_deposu_adi) {
                $result[] = $rowData;
            }
        }
    }

    return $result;

}


function aralik_teslimat_detayi($depo, $depo_ikinci_adi, $takim_adi, $start_date, $end_date, $tip)
{

    $sql_control_alternative_names = sql_select("select * from envanter where product_name = '$takim_adi'", "envanter");

    $takim_adi_2 = isset($sql_control_alternative_names[0]['a7']) ? $sql_control_alternative_names[0]['a7'] : null;
    $takim_adi_3 = isset($sql_control_alternative_names[0]['a8']) ? $sql_control_alternative_names[0]['a8'] : null;

    $control_cell = $takim_adi;

    if (isset($takim_adi_2) && !empty($takim_adi_2)) {
        $control_cell .= ";" . $takim_adi_2;
    }

    if (isset($takim_adi_3) && !empty($takim_adi_3)) {
        $control_cell .= ";" . $takim_adi_3;
    }


    $curl = curl_init();
    $adres = 'http://192.168.1.83:81/rapor/sor.php';
    curl_setopt($curl, CURLOPT_URL, $adres);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_POST, true);
//    curl_setopt($curl, CURLOPT_TIMEOUT, 50);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "program=workcube&excel=1&gosterme_secenegi=ekran&tarih_tipi=termin&bolge=$depo&belgetarih1=$start_date&belgetarih2=$end_date&kelime1=$control_cell&kelime2=");

    $data = curl_exec($curl);
    curl_close($curl);

    $dom = new DOMDocument();
    @$dom->loadHTML($data);
    $xpath = new DOMXPath($dom);

    // İlk tr etiketini kaldırma ve JSON formatına dönüştürme
    $firstRow = $xpath->query('//tr[1]');
    if ($firstRow->length > 0) {
        $firstRow->item(0)->parentNode->removeChild($firstRow->item(0));
    }

    $tableRows = $xpath->query('//tr');


    foreach ($tableRows as $rowIndex => $row) {
        $tdNodes = $xpath->query('td', $row);
        $rowData = array();

        // Sadece belirli anahtarları alın
        $rowData['key_0'] = $tdNodes->item(0)->nodeValue;
        $rowData['key_1'] = $tdNodes->item(1)->nodeValue;
        $rowData['key_4'] = $tdNodes->item(4)->nodeValue;
        $rowData['key_5'] = $tdNodes->item(5)->nodeValue;
        $rowData['key_6'] = $tdNodes->item(6)->nodeValue;
        $rowData['key_7'] = $tdNodes->item(7)->nodeValue;
        $rowData['key_8'] = $tdNodes->item(8)->nodeValue;
        $rowData['key_10'] = $tdNodes->item(10)->nodeValue;
        $rowData['key_11'] = $tdNodes->item(11)->nodeValue;
        $rowData['key_12'] = $tdNodes->item(12)->nodeValue;

        if ($tip == "ic") {
            if ($rowData['key_11'] === $depo_ikinci_adi) {
                $result[] = $rowData;
            }
        } elseif ($tip == "dis") {
            if ($rowData['key_11'] != $depo_ikinci_adi) {
                $result[] = $rowData;
            }
        }
    }

    return $result;

}


function sales_6_week($filtrelenmis_depo, $products, $start_time, $end_time)
{

    $takim_adi = "";

    if (count($products) > 1) {
        foreach ($products as $item) {
            $takim_adi .= $item['product_name'] . ';';
        }

        $uzunluk = strlen($takim_adi); // Dizenin uzunluğunu alın
        $takim_adi = substr($takim_adi, 0, $uzunluk - 1); // Son karakteri kaldırın

    } else {
        $takim_adi .= $products[0]['product_name'];
    }


    $mh = curl_multi_init(); // Multi-cURL işlemi oluştur

    $ch_list = array(); // Curl özkaynaklarını bir dizi içinde saklayalım

    if (in_array("Tümü", $filtrelenmis_depo)) {
        $all = true;
    } else {
        $all = false;
    }

    if (count($filtrelenmis_depo) < 7 && $all != true) {
        foreach ($filtrelenmis_depo as $item) {
            $sehir = $item['workcube_city_name'];

            $ch = curl_init();
            $adres = 'http://192.168.1.83:81/rapor/sor.php';
            curl_setopt($ch, CURLOPT_URL, $adres);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "program=workcube&gosterme_secenegi=ekran&tarih_tipi=termin&bolge=$sehir&belgetarih1=$start_time$start_time&belgetarih2=$end_time&kelime1=$takim_adi&kelime2=");

            curl_multi_add_handle($mh, $ch); // İstekleri multi-cURL'e ekle
            $ch_list[] = $ch; // Curl özkaynağını listeye ekle
        }
    } else {
        $ch = curl_init();
        $adres = 'http://192.168.1.83:81/rapor/sor.php';
        curl_setopt($ch, CURLOPT_URL, $adres);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "program=workcube&gosterme_secenegi=ekran&tarih_tipi=termin&bolge=tumu&belgetarih1=$start_time&belgetarih2=$end_time&kelime1=$takim_adi&kelime2=");
        curl_multi_add_handle($mh, $ch); // İstekleri multi-cURL'e ekle
        $ch_list[] = $ch; // Curl özkaynağını listeye ekle
    }

    $active = null;
    do {
        $mrc = curl_multi_exec($mh, $active); // İstekleri eşzamanlı olarak çalıştır
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active && $mrc == CURLM_OK) {
        if (curl_multi_select($mh) != -1) {
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }

    $data = ''; // Tüm verileri tutmak için boş bir string
    foreach ($ch_list as $ch) {
        $data .= curl_multi_getcontent($ch); // Her isteğin sonucunu $data'ya ekle
        curl_multi_remove_handle($mh, $ch); // İşlemi kapat
    }

    curl_multi_close($mh); // Multi-cURL işlemi kapat

    $dom = new DOMDocument();
    @$dom->loadHTML($data);
    $xpath = new DOMXPath($dom);

    // İlk tr etiketini kaldırma ve JSON formatına dönüştürme
    $firstRow = $xpath->query('//tr[1]');
    if ($firstRow->length > 0) {
        $firstRow->item(0)->parentNode->removeChild($firstRow->item(0));
    }

    $tableRows = $xpath->query('//tr');
    $result = array();

    foreach ($tableRows as $rowIndex => $row) {
        $tdNodes = $xpath->query('td', $row);
        $rowData = array();

        // Sadece belirli anahtarları alın
        $rowData['key_0'] = $tdNodes->item(0)->nodeValue;
        $rowData['key_1'] = $tdNodes->item(1)->nodeValue;
        $rowData['key_7'] = $tdNodes->item(7)->nodeValue;

        $rowData['key_10'] = $tdNodes->item(10)->nodeValue;
        $rowData['key_11'] = $tdNodes->item(11)->nodeValue;
        $rowData['key_12'] = $tdNodes->item(12)->nodeValue;

        $result[] = $rowData;

    }
    return $result;
}


function sales_6_week_2($filtrelenmis_depo, $products, $start_time, $end_time)
{

    $takim_adi = "";

    if (count($products) > 1) {
        foreach ($products as $item) {
            $takim_adi .= $item['product_name'] . ';';
        }

        $uzunluk = strlen($takim_adi); // Dizenin uzunluğunu alın
        $takim_adi = substr($takim_adi, 0, $uzunluk - 1); // Son karakteri kaldırın

    } else {
        $takim_adi .= $products[0]['product_name'];
    }

    $mh = curl_multi_init(); // Multi-cURL işlemi oluştur

    $ch_list = array(); // Curl özkaynaklarını bir dizi içinde saklayalım

    if (in_array("Tümü", $filtrelenmis_depo)) {
        $all = true;
    } else {
        $all = false;
    }

    if (count($filtrelenmis_depo) < 7 && $all != true) {

        foreach ($filtrelenmis_depo as $item) {
            $sehir = $item['workcube_city_name'];

            $ch = curl_init();
            $adres = 'http://192.168.1.83:81/rapor/sor.php';
            curl_setopt($ch, CURLOPT_URL, $adres);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "program=workcube&excel=1&gosterme_secenegi=ekran&tarih_tipi=siparis&bolge=$sehir&belgetarih1=$start_time&belgetarih2=$end_time&kelime1=$takim_adi&kelime2=");

            curl_multi_add_handle($mh, $ch); // İstekleri multi-cURL'e ekle
            $ch_list[] = $ch; // Curl özkaynağını listeye ekle
        }

    } else {

        $ch = curl_init();
        $adres = 'http://192.168.1.83:81/rapor/sor.php';
        curl_setopt($ch, CURLOPT_URL, $adres);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "program=workcube&gosterme_secenegi=ekran&tarih_tipi=siparis&bolge=tumu&belgetarih1=$start_time&belgetarih2=$end_time&kelime1=$takim_adi&kelime2=");
        curl_multi_add_handle($mh, $ch); // İstekleri multi-cURL'e ekle
        $ch_list[] = $ch; // Curl özkaynağını listeye ekle
    }

    $active = null;
    do {
        $mrc = curl_multi_exec($mh, $active); // İstekleri eşzamanlı olarak çalıştır
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active && $mrc == CURLM_OK) {
        if (curl_multi_select($mh) != -1) {
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }

    $data = ''; // Tüm verileri tutmak için boş bir string
    foreach ($ch_list as $ch) {
        $data .= curl_multi_getcontent($ch); // Her isteğin sonucunu $data'ya ekle
        curl_multi_remove_handle($mh, $ch); // İşlemi kapat
    }

    curl_multi_close($mh); // Multi-cURL işlemi kapat

    $dom = new DOMDocument();
    @$dom->loadHTML($data);
    $xpath = new DOMXPath($dom);

    // İlk tr etiketini kaldırma ve JSON formatına dönüştürme
    $firstRow = $xpath->query('//tr[1]');
    if ($firstRow->length > 0) {
        $firstRow->item(0)->parentNode->removeChild($firstRow->item(0));
    }

    $tableRows = $xpath->query('//tr');
    $result = array();

    foreach ($tableRows as $rowIndex => $row) {
        $tdNodes = $xpath->query('td', $row);
        $rowData = array();

        // Sadece belirli anahtarları alın
        $rowData['key_0'] = $tdNodes->item(0)->nodeValue;
        $rowData['key_1'] = $tdNodes->item(1)->nodeValue;
        $rowData['key_7'] = $tdNodes->item(7)->nodeValue;

        $rowData['key_10'] = $tdNodes->item(10)->nodeValue;
        $rowData['key_11'] = $tdNodes->item(11)->nodeValue;
        $rowData['key_12'] = $tdNodes->item(12)->nodeValue;

        $result[] = $rowData;

    }
    return $result;
}


function custamer_sales_information_singular($start_date, $end_date, $city_name)
{
    $curl = curl_init();
    $adres = 'http://192.168.1.83:81/rapor/sor.php';
    curl_setopt($curl, CURLOPT_URL, $adres);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "program=workcube&excel=1&gosterme_secenegi=ekran&tarih_tipi=termin&bolge=$city_name&belgetarih1=$start_date&belgetarih2=$end_date&kelime1=;&kelime2=");

    $data = curl_exec($curl);
    curl_close($curl);

    $dom = new DOMDocument();
    @$dom->loadHTML($data);
    $xpath = new DOMXPath($dom);

    // İlk tr etiketini kaldırma ve JSON formatına dönüştürme
    $firstRow = $xpath->query('//tr[1]');
    if ($firstRow->length > 0) {
        $firstRow->item(0)->parentNode->removeChild($firstRow->item(0));
    }

    $tableRows = $xpath->query('//tr');
    $result = array();

    foreach ($tableRows as $rowIndex => $row) {
        $tdNodes = $xpath->query('td', $row);
        $rowData = array();

        $rowData['key_1'] = $tdNodes->item(1)->nodeValue;
        $rowData['key_6'] = $tdNodes->item(6)->nodeValue;
        $rowData['key_7'] = $tdNodes->item(7)->nodeValue;
        $rowData['key_10'] = $tdNodes->item(10)->nodeValue;
        $rowData['key_11'] = $tdNodes->item(11)->nodeValue;
        $rowData['key_12'] = $tdNodes->item(12)->nodeValue;

        $result[] = $rowData;

    }
    return $result;
}


function story_information_singular($story)
{

    $tarayici = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0';

    $curl = curl_init();
    $adres = 'http://192.168.1.83:81/rapor/workcuberapor3.php';
    curl_setopt($curl, CURLOPT_URL, $adres);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT, 170);
    curl_setopt($curl, CURLOPT_USERAGENT, $tarayici);
    curl_setopt($curl, CURLOPT_ENCODING, 'UTF-8'); // Karakter kodlamasını belirtin

    $data = curl_exec($curl);
    curl_close($curl);

    $dom = new DOMDocument();
    @$dom->loadHTML($data);
    $xpath = new DOMXPath($dom);

    $tableRows = $xpath->query('//tr');
    $result = array();

    foreach ($tableRows as $rowIndex => $row) {
        $tdNodes = $xpath->query('td', $row);
        $rowData = array();

        foreach ($tdNodes as $cellIndex => $td) {
            $key = "key_{$cellIndex}";
            $rowData[$key] = $td->nodeValue;
        }

        if ($story === $rowData['key_0']) {
            $result[] = $rowData;
        }

    }

    return $result;
}


