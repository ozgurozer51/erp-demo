<?php
require_once "../../../functions/function.php";
require_once "../../../functions/workcube-data-soft.php";

ini_set('max_input_vars', 50000); // Örnek olarak 5000 olarak ayarlayın

if (!isset($_GET['islem'])) {
    exit("işlem bilgisi girilmedi");
} else {
    $islem = $_GET['islem'];
}


if ($islem == "doseme-kritik-listesini-getir") {

    $doseme_kritik_start_date = $_POST['doseme_kritik_start_date'];
    $doseme_kritik_end_date = $_POST['doseme_kritik_end_date'];

    $doseme_kritik_start_date = date("Y-m-d", strtotime($doseme_kritik_start_date)); // Yıl-Ay-Gün
    $doseme_kritik_end_date = date("Y-m-d", strtotime($doseme_kritik_end_date)); // Yıl-Ay-Gün


    $extra_query_2 = " ";
    $extra_query_3 = " ";
    $extra_query = "  and story_name != 'Mamul' and story_name != 'Selçuklu Depo' and story_name != 'Fabrika 1 Modüler Üretim' and story_name != 'Fabrika 1 Modüler Mamül' and story_name != 'Modüler Mamül' and  story_name != 'Üretim 1' and story_name != 'Sevkiyat 1' and story_name != 'Fabrika 1 Modüler Mamül' ";


    if (isset($_POST['doseme_product_name']) && !empty($_POST['doseme_product_name']) && count($_POST['doseme_product_name']) > 0) {

        $product_names = $_POST['doseme_product_name'];

        // Her bir query parçasını OR ile birleştirmek için bir dizi oluştur
        $product_name_queries = [];
        $product_name_queries_2 = [];
        $product_name_queries_3 = [];

        foreach ($product_names as $product_name) {
            $product_name = trim($product_name); // Ürün adını trimleyerek boşluklardan kurtul
            $product_name_queries[] = "workcube_stock.product_name LIKE '%$product_name%'";
            $product_name_queries_2[] = "product_name LIKE '%$product_name%'";
            $product_name_queries_3[] = "bolgetalepler.urun LIKE '%$product_name%'";
        }

        // Dizi elemanlarını OR ile birleştir
        $extra_query .= " AND (" . implode(" OR ", $product_name_queries) . ")";
        $extra_query_2 .= " AND (" . implode(" OR ", $product_name_queries_2) . ")";
        $extra_query_3 .= " AND (" . implode(" OR ", $product_name_queries_3) . ")";

    }


    if (isset($_POST['soft_kritik_zone_name']) && !empty($_POST['soft_kritik_zone_name']) && $_POST['soft_kritik_zone_name'] != 'ÖZET') {
        $story_id = $_POST['soft_kritik_zone_name'];
        $zone_name_text = $_POST['soft_kritik_zone_name_text'];
        $extra_query .= " and workcube_stock.story_id = '$story_id' ";
        $extra_query_2 .= " and (delivery_zone_name like '%$zone_name_text%' or city_name='$zone_name_text') ";
        $extra_query_3 .= "and ( bolgetalepler.bolge = '$zone_name_text' or bolgetalepler.teslimatbolge='$zone_name_text' )";
    }


    $extra_query_5 = "";

    $extra_query_4 = '';

    if (count($_POST['doseme_factory_name']) > 0 && $_POST['doseme_factory_name'][0] != "TÜMÜ") {
        $fabs = $_POST['doseme_factory_name'];

        // Her bir fabrika için bir koşul oluştur ve OR ile birleştir
        $fab_conditions = [];
        foreach ($fabs as $fab) {
            $fab = trim($fab); // Fabrika adını trimleyerek boşluklardan kurtul
            $fab = addslashes($fab); // SQL enjeksiyonunu önlemek için ekleme yap
            $fab_conditions[] = "envanterliste.a6 = '$fab'";
        }

        $extra_query_4 = "INNER JOIN dosemeuretim.envanterliste ON (envanterliste.a1 = stock_data.urun_adi AND (" . implode(' OR ', $fab_conditions) . "))";
    }

    if (count($_POST['doseme_type_name']) > 0 && $_POST['doseme_type_name'][0] != "TÜMÜ") {
        $types = $_POST['doseme_type_name'];

        // Her bir tip için bir koşul oluştur ve OR ile birleştir
        $type_conditions = [];
        foreach ($types as $type) {
            $type = trim($type); // Tip adını trimleyerek boşluklardan kurtul
            $type = addslashes($type); // SQL enjeksiyonunu önlemek için ekleme yap
            $type_conditions[] = "envanterliste.a8 = '$type'";
        }

        if ($extra_query_4 == '') {
            $extra_query_4 = "INNER JOIN dosemeuretim.envanterliste ON (envanterliste.a1 = stock_data.urun_adi AND (" . implode(' OR ', $type_conditions) . "))";
        } else {
            $extra_query_4 .= " AND (" . implode(' OR ', $type_conditions) . ")";
        }
    }


    $now = new DateTime();
    $interval = new DateInterval('P1M'); // 1 ay ve 15 gün
    $start_date_1_mounth = $now->sub($interval)->format('Y-m-d');


    if ($_POST['soft_kritik_zone_name'] != 'ÖZET') {

    $sql = sql_select("SELECT stock_data.*,
       sales_data.total,
       0 as net,
       stock_data.ggg as amount_sum,
       COALESCE(stock_data.amount - sales_data.into_sales  , 0) as minus,
       COALESCE(stock_data.amount - sales_data.total , 0) as minus_2,
       sales_data.into_sales,
       sales_data.one_mounth_sales,
       COALESCE(ggg  -  sales_data.one_mounth_sales , 0) as missing_one_mounth,
       sales_data_2.auter_sales,
        sum(dosemeuretim.bolgetalepler.kalanadet)  as kalanadet,
       sum(CASE WHEN dosemeuretim.bolgetalepler.plandurum = 'PLANLANMADI' THEN dosemeuretim.bolgetalepler.kalanadet ELSE 0 END) AS planlanmamış_bölge_talebi,
       dosemeuretim.bolgetalepler.hazirdangidenadet,
       sum(dosemeuretim.bolgetalepler.hazirdangidecekadet) - sum(dosemeuretim.bolgetalepler.gonderilenadet) as hazirdangidecekadet,
       dosemeuretim.bolgetalepler.hazirdangidecekadet - dosemeuretim.bolgetalepler.hazirdangidenadet as hazırdan_kalan,      
       sum(dosemeuretim.bolgetalepler.planlananadet) - sum(dosemeuretim.bolgetalepler.uretimiplanlanandansevkedilen) as üretim_planından_kalan,      
       dosemeuretim.bolgetalepler.id as bolge_talep_id,
       soft_mamul_stock_summary.amount   AS soft_mamul_amount
FROM (SELECT DISTINCT modanet_product_name,
                      city_id,
                      id,
                      story_id,
                      sum(amount) as amount,
                      sum(amount) as ggg,
                      stock_code,
                      product_name AS urun_adi,
                      story_name,
                      zone_name_2,
                      zone_name
      FROM workcube_stock
      WHERE (stock_code LIKE '%T.D%'
         OR stock_code LIKE '%U.D%'
         OR stock_code LIKE '%U.B.%'
         OR stock_code LIKE '%U.BS%'
         OR stock_code LIKE '%U.Y%'
         OR stock_code LIKE '%U.BS%' 
         OR stock_code LIKE '%01.150.270%' 
         OR stock_code LIKE '%P.BZ.%' 
         OR stock_code LIKE '%ME.M%' 
         OR stock_code LIKE '%01.151%' 
         OR stock_code LIKE '%MEM.M%')  $extra_query  group by zone_name_2 , stock_code) AS stock_data
         LEFT JOIN
     (SELECT product_name,
             zone_id,
             city_id,
             story_id,
             SUM(CASE WHEN (delivery_date >= '$doseme_kritik_start_date') THEN amount ELSE 0 END) AS total,
             SUM(CASE WHEN (delivery_date BETWEEN '$doseme_kritik_start_date' AND '$doseme_kritik_end_date') THEN amount ELSE 0 END) AS into_sales,
             SUM(CASE WHEN (delivery_date BETWEEN '$start_date_1_mounth' AND '$doseme_kritik_start_date') THEN amount ELSE 0 END) AS one_mounth_sales
      FROM workcube_sales
      where 1=1 $extra_query_2
      GROUP BY product_name,
               story_id) AS sales_data
     ON
                 stock_data.urun_adi = sales_data.product_name AND (stock_data.story_id = sales_data.story_id or (sales_data.story_id = 2 and stock_data.story_id =35 ) or (sales_data.story_id = 35 and stock_data.story_id =2 ) )
         LEFT JOIN
     (SELECT product_name,
             zone_id,
             city_id,
             story_id,
             SUM(CASE
                     WHEN (delivery_date BETWEEN '$doseme_kritik_start_date' AND '$doseme_kritik_end_date') AND zone_id != story_id AND
                          city_id != zone_city_id AND zone_id != story_id THEN amount
                     ELSE 0 END) AS auter_sales
      FROM workcube_sales
          where 1=1 $extra_query_2
      GROUP BY product_name,
               city_name) AS sales_data_2
     ON
                 stock_data.urun_adi = sales_data_2.product_name AND stock_data.story_id = sales_data_2.zone_id 
LEFT JOIN
     workcube_stock soft_mamul_stock_summary
     ON
                 stock_data.urun_adi = soft_mamul_stock_summary.product_name and
                 soft_mamul_stock_summary.story_name = 'Soft Mamül'
    
LEFT JOIN dosemeuretim.bolgetalepler on   ((bolgetalepler.teslimatbolge = stock_data.zone_name_2  ) 
                                          and bolgetalepler.urun = stock_data.urun_adi 
                                          and bolgetalepler.geneldurum='AÇIK' ) 
    
$extra_query_4
     where 1=1  
       GROUP BY
    stock_data.urun_adi, stock_data.story_id , bolgetalepler.urun  ", "modanet");


    }else{


        $sql = sql_select("SELECT stock_data.*,
       sales_data.total,
       0 as net,
       stock_data.ggg as amount_sum,
       COALESCE(stock_data.amount - sales_data.into_sales  , 0) as minus,
       stock_data.ggg - sales_data.total as minus_2,
       sales_data.into_sales,
       sales_data.one_mounth_sales,
       COALESCE(ggg  -  sales_data.one_mounth_sales , 0) as missing_one_mounth,
       '*' as auter_sales,
       sum(dosemeuretim.bolgetalepler.kalanadet) - sum(dosemeuretim.bolgetalepler.gonderilenadet) as kalanadet,
       sum(CASE WHEN dosemeuretim.bolgetalepler.plandurum = 'PLANLANMADI' THEN dosemeuretim.bolgetalepler.kalanadet ELSE 0 END) AS planlanmamış_bölge_talebi,
       dosemeuretim.bolgetalepler.hazirdangidenadet,
       sum(dosemeuretim.bolgetalepler.hazirdangidecekadet) as hazirdangidecekadet,
       dosemeuretim.bolgetalepler.hazirdangidecekadet - dosemeuretim.bolgetalepler.hazirdangidenadet as hazırdan_kalan,      
       sum(dosemeuretim.bolgetalepler.planlananadet) - sum(dosemeuretim.bolgetalepler.uretimiplanlanandansevkedilen) as üretim_planından_kalan,      
       dosemeuretim.bolgetalepler.id as bolge_talep_id,
       soft_mamul_stock_summary.amount   AS soft_mamul_amount
FROM (SELECT DISTINCT modanet_product_name,
                      city_id,
                      id,
                      story_id,
                      sum(amount) as amount,
                      sum(amount) as ggg,
                      stock_code,
                      product_name AS urun_adi,
                      story_name,
                      'TOPLAM!' as  zone_name_2,
                      zone_name
      FROM workcube_stock
      WHERE (stock_code LIKE '%T.D%'
         OR stock_code LIKE '%U.D%'
         OR stock_code LIKE '%U.B.%'
         OR stock_code LIKE '%U.BS%'
         OR stock_code LIKE '%U.Y%'
         OR stock_code LIKE '%U.BS%' 
         OR stock_code LIKE '%01.150.270%' 
         OR stock_code LIKE '%P.BZ.%' 
         OR stock_code LIKE '%ME.M%' 
         OR stock_code LIKE '%01.151%' 
         OR stock_code LIKE '%MEM.M%') 
          $extra_query  group by  stock_code) AS stock_data
         LEFT JOIN
     (SELECT product_name,
             zone_id,
             city_id,
             story_id,
             SUM(CASE WHEN (delivery_date >= '$doseme_kritik_start_date') THEN amount ELSE 0 END) AS total,
             SUM(CASE WHEN (delivery_date BETWEEN '$doseme_kritik_start_date' AND '$doseme_kritik_end_date') THEN amount ELSE 0 END) AS into_sales,
             SUM(CASE WHEN (delivery_date BETWEEN '$start_date_1_mounth' AND '$doseme_kritik_start_date') THEN amount ELSE 0 END) AS one_mounth_sales
      FROM workcube_sales
      where 1=1 $extra_query_2
      GROUP BY product_name) AS sales_data
     ON
                 stock_data.urun_adi = sales_data.product_name 
      
LEFT JOIN
     workcube_stock soft_mamul_stock_summary
     ON
                 stock_data.urun_adi = soft_mamul_stock_summary.product_name and
                 soft_mamul_stock_summary.story_name = 'Soft Mamül'
    
LEFT JOIN dosemeuretim.bolgetalepler on   ( bolgetalepler.urun = stock_data.urun_adi 
                                          and bolgetalepler.geneldurum='AÇIK' ) 
    
$extra_query_4
     where 1=1  
       GROUP BY
    stock_data.urun_adi order by   ", "modanet");


    }


    if ($sql == '') {
        echo "";
        exit();
    }

    echo json_encode($sql);

}

if ($islem == 'döşeme-kritikten-planla') {


    unset($_SESSION['error']);

    $insert = array();
    $insert_plan = array();
    $uniqid = strtotime(date("Y-m-d H:i:s"));

    $product = $_POST['product'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $region = $_POST['region'];


    $date = explode('-', $date);

    $date = $date[2] . '.' . $date[1] . '.' . $date[0];


    $sql = sql_select("select * from envanterliste where a1 = '$product'", "dosemeuretim");

    $group = $sql[0]["a6"];
    $envanter_liste_a1 = $sql[0]["a2"];
    $envanter_liste_workcube = $sql[0]["workcubead"];
    $model = $sql[0]["a3"];
    $product_2 = $sql[0]["a4"];
    $stock_code = $sql[0]["a9"];
    $alternative = $sql[0]["a5"];
    $fabric_code = $sql[0]["a13"];

    if ($alternative == 'X' or $model == '') {
        $gorme = 0;
    } else {
        $gorme = 1;
    }


    $planlamaaciklama = "";
    $talepsaat = date("d.m.Y H:i:s");
    $talepsaat1 = strtotime($talepsaat);


    if ($fabric_code == "") {
        $sql = sql_select("SELECT * FROM urunayrintilar WHERE a1='$envanter_liste_a1' or a2='$envanter_liste_a1' or a7='$envanter_liste_a1'", "dosemeuretim");

        $fabric_code = $sql[0]["a23"];

        if ($fabric_code == "") {
            $fabric_code = "yok";
        }
    }

    $sql = sql_select("SELECT MAX(id) FROM bolgetalepler", "dosemeuretim");
    $hareketnumarasi = "FD" . (($sql[0]["MAX(id)"]) + 1);

    $onaydurum = "1";

    $insert['bolge'] = 'FABRİKA';
    $insert['talepadet'] = $amount;
    $insert['prk'] = '';
    $insert['musteriadi'] = "DEPO " . $region;
    $insert['taleptarihi'] = date("d.m.Y");
    $insert['taleptarihi1'] = $talepsaat1;
    $insert['geneldurum'] = "AÇIK";
    $insert['kalanadet'] = $amount;
    $insert['urun'] = $envanter_liste_workcube;
    $insert['aciklama'] = '';
    $insert['aciklamavega'] = '';
    $insert['tip'] = 'DEPO';
    $insert['teslimatbolge'] = "DEPO";
    $insert['planlananadet'] = $amount;
    $insert['gonderilenadet'] = 0;
    $insert['teslimattarihi'] = $date;
    $insert['hazirdangidecekadet'] = 0;
    $insert['uretimiplanlanandansevkedilen'] = 0;
    $insert['uretimiplanlanandanuretilen'] = 0;
    $insert['hazirdangidenadet'] = 0;
    $insert['kumaskodu'] = $fabric_code;
    $insert['planlamaaciklama'] = '';
    $insert['talepsaat'] = $talepsaat;
    $insert['talepsaat1'] = $talepsaat1;
    $insert['model'] = $model;
    $insert['urun1'] = $product_2;
    $insert['stokkodu'] = $stock_code;
    $insert['teslimattarihi1'] = strtotime($date);
    $insert['belgenotu'] = "";
    $insert['plandurum'] = "PLANLANDI";
    $insert['siparistipi'] = $alternative;
    $insert['hareketnumarasi'] = $hareketnumarasi;
    $insert['grup'] = $group;
    $insert['kayit_id'] = $_SESSION['user_id'];
    $insert['duzeltmedurum'] = $gorme;
    $insert['onaydurum'] = $onaydurum;
    $insert['odemedurum'] = '';
    $insert['process_id'] = $uniqid;
    $insert['insert_userid'] = $_SESSION['user_id'];
    $insert['insert_datetime'] = date("Y-m-d H:i:s");

    $sql = sql_insert("bolgetalepler", $insert, 'dosemeuretim');

    if ($sql) {
        echo message("Sipariş Eklendi", "success");
    } else {
        echo message("Sipariş Eklenmedi.. Hata", "error");
        exit();
    }

    $sql = sql_select("select  * from bolgetalepler where process_id = '$uniqid' order by id desc", "dosemeuretim");

    $talep_tarihi = date("d.m.Y H:i:s");

    $now_datetime = date("d.m.Y H:i:s");
    $insert_plan['bolgetalepler_id'] = $sql[0]["id"];
    $insert_plan['taleptarihi1'] = strtotime($talep_tarihi);
    $insert_plan['planlanan_adet'] = $amount;
    $insert_plan['kalan_adet'] = $amount;
    $insert_plan['depodan'] = 0;
    $insert_plan['onay'] = 0;
    $insert_plan['durum'] = "PLANLANIYOR";
    $insert_plan['uretim_kalan_adet'] = $amount;
    $insert_plan['kumas'] = $fabric_code;
    $insert_plan['model'] = $model;
    $insert_plan['planlanan_tarih'] = $date;
    $insert_plan['planlanan_zaman'] = strtotime($date);
    $insert_plan['tarih_saat'] = $now_datetime;
    $insert_plan['zaman'] = strtotime($now_datetime);
    $insert_plan['insert_userid'] = $_SESSION['user_id'];
    $insert_plan['insert_datetime'] = date("Y-m-d H:i:s");


    $sql = sql_insert("planlananlar", $insert_plan, "dosemeuretim");

    if ($sql) {
        echo message("Planlananlara Eklendi", "extra");
    } else {
        echo message("Planlananlara Eklenemedi.. Hata", "error");
    }

    if (isset($_SESSION['error'])) {
        echo $_SESSION['error'];
    }

}
?>






