<?php
require_once "../../../controller/functions/function.php";
?>

<div style="display: flex;">
    <input type="text" id="input-fgd98" class="input-fgd98 form-control form-control-sm"
           placeholder="Üretim Numarası Giriniz" style="width: 100%;">

    <input class="easyui-datebox" id="date-tty" value="<?php echo $now_date; ?>" style="width: 250px;">

    <select class="easyui-combobox" id="zone-tty" prompt="Bölge" style="width: 400px;">

    </select>


    <button class="btn btn-sm btn-success" onclick="loadxxs()" style="width: 350px;"><i
                class="fa-solid fa-list"></i> Getir
    </button>

    <button class="btn btn-sm btn-warning" onclick="request_list_load()" style="width: 400px;"><i
                class="fa-solid fa-code-pull-request"></i> Etiketsiz Ürün Ekle
    </button>

    <button class="btn btn-sm btn-secondary" id="close-truck" disabled style="width: 540px;"><i
                class="fa-duotone fa-solid fa-clipboard-list-check"></i> Yüklemeyi Tamamla
    </button>

    <button class="btn btn-sm btn-danger" onclick="delete_all()" style="width: 520px;"><i
                class="fa-solid fa-trash"></i> Tamamlanmayanları Temizle
    </button>

    <button class="btn btn-sm btn-primary" onclick="write_all()">Yazdır</button>


</div>

<!--<div class="table-container container-fluid">-->
<div class="easyui-datagrid" id="sevk-datagrid" style="width: 100%; height: 95%;"></div>
<!---->
<!--</div>-->

<div style="display: none;">
    <div id="register_2"></div>
</div>


<div class="easyui-dialog" id="driver-select" resizable="true" style="width: 65%; height: 40%; padding: 10px;"
     data-options="closed:true"
     modal="true" title="Sevkiyat İşlemini Tamamla">

    <div style="display: flex;">
        <select class="easyui-combobox" id="select-driver" style="width: 300px;">

            <option>Soför Seçiniz</option>

            <?php
            $sql = sql_select("select * from soforler order by sofor asc ", "aracirsaliye");

            foreach ($sql as $value) {
                echo '<option value="' . $value["ID"] . '">' . $value["sofor"] . '</option>';
            }
            ?>
        </select>

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

        <select class="easyui-combobox" id="complated_username" style="width: 200px;">
            <option>Yazıcı Adı Seç</option>
            <option value="Şaban Torun">Şaban Torun</option>
            <option value="Furkan Erdem">Furkan Erdem</option>
            <option value="Batuhan Suluhan">Batuhan Suluhan</option>
            <option value="Fatih Deveci">Fatih Deveci</option>
        </select>

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

        <input class="easyui-textbox" prompt="Mühür No:" id="seal_no" style="width: 200px;">
    </div>

    <br>
    <br>
    <br>

    <input class="easyui-textbox" label="İsim:" id="driver-name" style="width: 400px;">
    <input class="easyui-textbox" label="Telefon:" id="driver-phone" style="width: 400px;">
    <br>
    <input class="easyui-textbox" label="Plaka:" id="driver-plate" style="width: 400px;">
    <input class="easyui-textbox" label="Tc No:" id="driver-tc" style="width: 400px;">


    <footer style="display: flex;">
        <button class="easyui-linkbutton c4" onclick="complate_shipping()" iconCls="icon-save" style="width: 250px;">
            Tamamla
        </button>

    </footer>
</div>


<script>



    $(function () {
        $('#select-driver').combobox({

            onChange: function (newValue, oldValue) {

                var id = $(this).val();

                $.ajax({
                    type: 'POST',
                    url: 'controller/modules/sevkiyat-planlama-stoklar/sevk-kayıt/sevk-kayıt-controller.php?islem=get-driver-data',
                    data: {id: id},
                    success: function (e) {
                        const data = JSON.parse(e); // JSON yanıtını parse et

                        $('#driver-name').textbox('setValue', data[0]['sofor']);
                        $('#driver-tc').textbox('setValue', data[0]['tc_no']);
                        $('#driver-phone').textbox('setValue', data[0]['telefon']);
                        $('#driver-plate').textbox('setValue', data[0]['plaka']);
                    }
                });
            }
        });
    });

    function write_all(){

        var date = $('#date-tty').val();
        var region = $('#zone-tty').combobox('getValue');
        window.open( 'controller/modules/sevkiyat-planlama-stoklar/sevk-kayıt/sevk-kayıt-controller.php?region='+region+'&date='+date+'&islem=print' , "_blank", "width=800,height=800" );


    }

    function request_list_load() {

        var dialog_23vd32 = $('<div>').dialog({
            modal: true,
            width: '20%',
            height: '30%',
            title: 'İhtiyaç Listesi Ekle',
            resizable: true,

            content: '<div style="padding: 10px;"><input label="Açıklama:" multiline="true" class="easyui-textbox" id="request_description_34f"  style="width: 100%; height: 150px;"></div>',

            buttons: [
                {
                    text: 'Kaydet',
                    iconCls: 'icon-save',
                    handler: function () {

                        var description = $('#request_description_34f').val();
                        var region = $('#zone-tty').combobox('getValue');

                        $.ajax({
                            type: 'POST',
                            url: 'controller/modules/sevkiyat-planlama-stoklar/sevk-kayıt/sevk-kayıt-controller.php?islem=ihtiyaç-listesi-ekle',
                            data: {description: description, region: region},
                            success: function (e) {
                                $('#result-message').html(e);
                                dialog_23vd32.dialog('close');
                            }
                        });
                    }
                }
            ],

            onClose: function () {
                dialog_23vd32.dialog('destroy');
            }

        })

    }

</script>

<script>


    // `datebox`'ta tarih seçildiğinde `get_region` fonksiyonunu çağır
    $('#date-tty').datebox({
        onSelect: function (date) {
            // Gün, ay ve yıl değerlerini al ve d.m.Y formatına çevir
            const day = String(date.getDate()).padStart(2, '0'); // Gün 2 haneli hale getir
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Ay 2 haneli hale getir
            const year = date.getFullYear();

            // d.m.Y formatına dönüştür
            const formattedDate = `${day}.${month}.${year}`;

            // `get_region` fonksiyonuna bu formatta gönder
            get_region(formattedDate);
        }
    });


    // Sayfa yüklendiğinde veya başka bir durumda tarih stringi ile `get_region` fonksiyonunu çağırabilirsiniz
    var date_23 = $('#date-tty').datebox('getValue');
    get_region(date_23);

    function get_region(date) {
        // d.m.Y formatında gelen tarihi Y-m-d formatına çevir
        const [day, month, year] = date.split('.');
        const formattedDate = `${year}-${month}-${day}`;

        $('#zone-tty').combobox({
            url: 'controller/modules/doseme/doseme-sevkiyat-planla/doseme-sevkiyat-planla-controller.php?islem=get-region-name&date=' + formattedDate,
            valueField: 'zone_name',
            textField: 'zone_name',
        });
    }

</script>


</script>


<script>


    function delete_all() {
        var rowData = $('#sevk-datagrid').datagrid('getRows')[1]; // 1, 0 tabanlı indeks

        var document_number = rowData.document_number;


        $.messager.confirm('Silme İşlemini Onayla', 'Silme İşlemini Onaylıyor musunuz? Tamamlanmayanlar Silinecek', function (r) {
            if (r) {
                $.ajax({
                    type: 'POST',
                    url: 'controller/modules/sevkiyat-planlama-stoklar/sevk-kayıt/sevk-kayıt-controller.php?islem=tamamlanmayan-müşterilerin-listesini-sil-sevkiyat',
                    data: {document_number: document_number},
                    success: function (e) {
                        $('#result-message').html(e);
                        $('#sevk-datagrid').datagrid('reload');
                    }
                });

            }
        });


    }


    function auto_complate(id, name, amount, document_number, product_name) {

        var status = $('#auto-complate-' + id).is(':checked');

        if (status) {
            status = 1;
        } else {
            status = 0;
        }

        $.ajax({
            type: 'POST',
            url: 'controller/modules/sevkiyat-planlama-stoklar/sevk-kayıt/sevk-kayıt-controller.php?islem=manuel-yüklemeyi-tamamla',
            data: {
                id: id,
                status: status,
                name: name,
                amount: amount,
                document_number: document_number,
                product_name: product_name
            },
            success: function (e) {
                $('#result-message').html(e);
                $('#sevk-datagrid').datagrid('reload');
            }
        })
    }


    function auto_complate_2(id, name) {

        var status = $('#auto-complate-2-' + id).is(':checked');

        if (status) {
            status = 1;
        } else {
            status = 0;
        }

        $.ajax({
            type: 'POST',
            url: 'controller/modules/sevkiyat-planlama-stoklar/sevk-kayıt/sevk-kayıt-controller.php?islem=manuel-yüklemeyi-tamamla-2',
            data: {id: id, status: status, name: name},
            success: function (e) {
                $('#result-message').html(e);
                $('#sevk-datagrid').datagrid('reload');
            }
        })
    }


    function loadxxs() {

        $('#close-truck').attr('disabled', true);

        var date = $('#date-tty').val();
        var zone = $('#zone-tty').combobox('getValue');

        $('#sevk-datagrid').datagrid('options').autoLoad = true;
        $('#sevk-datagrid').datagrid('load', {
            zone: zone,
            date: date,
        });

    }


    $('#sevk-datagrid').datagrid({

        singleSelect: true,
        autoLoad: false,
        rownumbers: true,
        nowrap: true,
        fitColumns: true,
        enableFilter: true,
        method: 'post',
        idField: 'id', // ID alanınızı buraya ekleyin
        pagination: true,
        pageSize: 500,
        pageList: [10, 20, 30, 50, 100, 200, 500, 1000, 2000],
        remoteSort: false,
        remoteFilter: false,

        view: detailview,
        detailFormatter: function (index, row) {
            return '<div class="ddv" style="padding:5px 0"></div>';
        },

        onSelect: function (index, row) {

            var rows = $('#sevk-datagrid').datagrid('getRows'); // Tüm satırları al
            for (var i = 0; i < rows.length; i++) {
                // Her satırı daralt
                $('#sevk-datagrid').datagrid('collapseRow', i);
            }

            $('#sevk-datagrid').datagrid('expandRow', index);

        },

        onExpandRow: function (index, row) {
            var ddv = $(this).datagrid('getRowDetail', index).find('div.ddv');
            ddv.panel({
                border: false,
                cache: false,
                href: 'controller/modules/sevkiyat-planlama-stoklar/sevk-kayıt/sevk-kayıt-controller.php?islem=yüklenecekler-detay-getir&id=' + row.id + '&expand=' + auto_expand,
                onLoad: function () {
                    // İçeriğin yüksekliğini al ve ona göre ayarla
                    var contentHeight = ddv.find('.panel-body').outerHeight();
                    ddv.panel('resize', {height: contentHeight});
                    $('#sevk-datagrid').datagrid('fixDetailRowHeight', index);

                }
            });

            auto_expand = true;
            $('#sevk-datagrid').datagrid('fixDetailRowHeight', index);
        },

        onBeforeLoad: function () {
            var opts = $(this).datagrid('options');
            return opts.autoLoad;
        },


        url: 'controller/modules/sevkiyat-planlama-stoklar/sevk-kayıt/sevk-kayıt-controller.php?islem=yüklenecekler-listeyi-getir',


        columns: [[

            {field: 'id', title: 'id', width: 20},
            {field: 'document_number', title: 'Döküman Numarası', width: 25},
            {field: 'order_no', title: 'İş Emri', width: 25},


            <?php if ($_SERVER['REMOTE_ADDR'] == "192.168.143.24"){ ?>
            {
                field: 'production_order_id', title: 'production', width: 50, formatter: function (data, row) {
                    return row.production_order_id + "-" + row.waiting_part_ids;
                }
            },
            <?php } ?>

            {field: 'zone_name', title: 'Bölge', width: 23},
            {field: 'date', title: 'Tarih', width: 23},
            {field: 'product_name', title: 'Ürün', width: 100},
            {field: 'customer_name', title: 'Müşteri', width: 100},
            {field: 'amount', title: 'Adet', width: 20},
            {field: 'complated_amount', title: 'Tamamlanan Adet', width: 20},
            {field: 'emergency', title: 'Acil Durum', width: 50},


            {
                field: 'truck_status', title: 'Yükleme Durumu', width: 50, formatter: function (data, row) {

                    if (row.truck_status == 0) {

                        if (row.reading_part_ids) {
                            return "Beklemede<span  style='display: flex; float: right; width: 50px;  color:white'><i class='fa-solid fa-clock-rotate-left fa-spin ' style='float:right;' '></i>___<i class='fa-solid fa-hourglass-start fa-spin' style='float:right;' '></i></span>";
                        } else {
                            return "Beklemede";
                        }


                    }
                    if (row.truck_status == 1) {
                        return "Tamamlandı";
                    }

                }, styler: function (data, row) {
                    if (row.truck_status == 0) {
                        return 'background-color:red !important; color: white !important;';
                    }
                    if (row.truck_status == 1) {
                        return 'background-color:green !important; color: white !important;';
                    }
                }
            },

            {
                field: 'truck_statuszz',
                title: 'Manuel Tamamla',
                width: 20,
                formatter: function (data, row) {

                    var disable = "";
                    if (row.postpone == 1) {
                        disable = 'disabled';
                    }

                    var status = row.manuel_complate_status == 1 ? 'checked' : '';
                    return "<input " + disable + " " + status + " onclick=\"auto_complate('" + row.id + "' , '" + row.customer_name + "' , '" + row.amount + "'  , '" + row.document_number + "' , '" + row.product_name + "')\"  id='auto-complate-" + row.id + "' class='form-check-input' type='checkbox' />";
                }
            },


            {
                field: 'idd', title: 'Listeden Çıkart', width: 20, formatter: function (data, row) {
                    return "<button id='delete-shipping' customer_name= '" + row.customer_name + "' data-id='" + row.id + "' class='easyui-linkbutton c5'><i class='fa-solid fa-trash'></i> Sil</button>";
                }
            },

            {
                field: 'sfdsdf',
                title: 'Ertele',
                width: 20,
                formatter: function (data, row) {

                    var disable = "";
                    if (row.manuel_complate_status == 1) {
                        disable = 'disabled';
                    }

                    var status = row.postpone == 1 ? 'checked' : '';
                    return "<input " + disable + " " + status + " onclick=\"auto_complate_2('" + row.id + "' , '" + row.customer_name + "' )\"  id='auto-complate-2-" + row.id + "' class='form-check-input' type='checkbox' style='background-color: blue;' />";
                }
            },

        ]],


        rowStyler: function (data, row) {


            if (row.color == 1) {

                function generateHash(str) {
                    let hash = 0;
                    for (let i = 0; i < str.length; i++) {
                        hash = (hash << 5) - hash + str.charCodeAt(i);
                        hash = hash & hash; // 32-bit integer mask
                    }
                    return hash;
                }

// Hash tabanlı renk oluşturma
                const colors = [
                    '#f6a3a9', '#b785ec', '#94dbe3', '#94e06e', '#f0e68c', '#add8e6',
                    '#FFD700', '#20B2AA', '#5d97af', '#ea2185', '#7FFFD4', '#FFB6C1'
                ];

                const hash = generateHash(row.product_name);
                const colorIndex = Math.abs(hash % colors.length); // Hash değerine göre renk seç
                return `background-color: ${colors[colorIndex]};`;

            }


        },


        onLoadSuccess: function () {
            $(this).datagrid('enableFilter');
            $('.easyui-checkbox').checkbox();
            $('.easyui-linkbutton').linkbutton();
        }

    });


    var auto_expand = true;

    function autoExpandRowByOrderId(targetOrderId) {

        auto_expand = false;

        collapseAllRows();
        var rows = $('#sevk-datagrid').datagrid('getRows');
        var lastIndex = rows.length - 1;

        var controlxc = false;
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].production_order_id == targetOrderId) {

                var idToSelect = rows[i].id;

                controlxc = true;

                $('#sevk-datagrid').datagrid('expandRow', i);
                $('#sevk-datagrid').datagrid('selectRecord', idToSelect);
                var rowIndex = $('#sevk-datagrid').datagrid('getRowIndex', idToSelect);

                if (lastIndex > Number(rowIndex) + 8) {

                    $('#sevk-datagrid').datagrid('scrollTo', rowIndex + 8);

                } else {

                    setTimeout(function () {
                        var panel = $('#sevk-datagrid').datagrid('getPanel');
                        var body = panel.find('.datagrid-body');
                        body.scrollTop(body[0].scrollHeight);
                    }, 1000);

                }

            }

        }


        if (controlxc == false) {
            $('#sevk-datagrid').datagrid('reload');
        }


    }


    function checkAllTruckStatus() {
        // DataGrid'deki tüm satırları alın
        var rows = $('#sevk-datagrid').datagrid('getRows');
        var allStatusOne = true; // Kontrol için bir bayrak

        if (rows.length === 0) return; // DataGrid boşsa çalışmasın


        // Her bir satırı kontrol edin
        for (var i = 0; i < rows.length; i++) {
            if (Number(rows[i].truck_status) !== 1 || rows[i].truck_status != 1) {
                allStatusOne = false;
                break;
            }
        }

        // Eğer tüm truck_status değerleri 1 ise, alert göster
        if (allStatusOne) {
            $('#close-truck').attr('disabled', false);
        }

    }

    setInterval(function () {
        checkAllTruckStatus();
    }, 5000);


    function collapseAllRows() {
        var rows = $('#sevk-datagrid').datagrid('getRows'); // Datagrid'deki tüm satırları al
        for (var i = 0; i < rows.length; i++) {
            $('#sevk-datagrid').datagrid('collapseRow', i); // Her satırı daralt
        }
    }


    $('#input-fgd98').change(function () {
        var id = $(this).val();


        var rowData = $('#sevk-datagrid').datagrid('getRows')[1];

        var document_number = rowData.document_number;


        var date = $('#date-tty').val();
        var zone = $('#zone-tty').combobox('getValue');

        const partBeforeDash = id.includes('-') ? id.split('-')[0] : id.split('*')[0];

        $('#input-fgd98').val('');

        $.ajax({
            type: 'POST',
            url: 'controller/modules/sevkiyat-planlama-stoklar/sevk-kayıt/sevk-kayıt-controller.php?islem=uretilenlere-ekle',
            data: {id: id, document_number: document_number, date: date, zone: zone},
            success: function (e) {


                autoExpandRowByOrderId(partBeforeDash);

                var dialog_dom_701 = $('<div>').dialog({
                    title: 'Yanıt',
                    width: '80%',
                    height: '50%',
                    modal: false,
                    content: e
                });

                setTimeout(function () {
                    dialog_dom_701.dialog("destroy");
                }, 1000);

            }
        });
    });


    function complate_shipping() {

        var rowData = $('#sevk-datagrid').datagrid('getRows')[1]; // 1, 0 tabanlı indeks
        var document_number = rowData.document_number;

        var driver_id = $('#select-driver').combobox('getValue');
        var complated_username = $('#complated_username').combobox('getValue');

        var driver_name = $('#driver-name').val();
        var driver_phone = $('#driver-phone').val();
        var driver_tc = $('#driver-tc').val();
        var driver_plate = $('#driver-plate').val();
        var seal_no = $('#seal_no').val();

        $.ajax({
            type: 'POST',
            url: 'controller/modules/sevkiyat-planlama-stoklar/sevk-kayıt/sevk-kayıt-controller.php?islem=sevkiyat-işlemini-kapat',
            data: {
                driver_plate: driver_plate,
                driver_tc: driver_tc,
                driver_name: driver_name,
                driver_phone: driver_phone,
                document_number: document_number,
                driver_id: driver_id,
                seal_no: seal_no,
                complated_username: complated_username
            },

            success: function (e) {

                $('#driver-select').dialog('close');
                $("#close-truck").attr('disabled', 'disabled');
                var audio = new Audio('assests/mp3/complate.mp3');
                audio.play(); // Otomatik çalma

            }
        });
    }

    $("body").off("click", "#close-truck").on("click", "#close-truck", function (e) {
        $('#driver-select').dialog('open');

    });


    $("body").off("click", "#delete-shipping").on("click", "#delete-shipping", function (e) {

        var id = $(this).attr('data-id');
        var name = $(this).attr('customer_name');

        $.messager.confirm('Silme İşlemini Onayla', 'Silmek İşlemini Onaylıyor musunuz?', function (r) {
            if (r) {
                $.ajax({
                    type: 'POST',
                    url: 'controller/modules/sevkiyat-planlama-stoklar/sevk-kayıt/sevk-kayıt-controller.php?islem=delete-shipping',
                    data: {id: id, name: name},
                    success: function (e) {
                        $('#result-message').html(e);
                        $('#sevk-datagrid').datagrid('reload');
                    }
                });
            }
        });

    });


    <?php   if ($_SERVER['REMOTE_ADDR'] != "192.168.143.24"){ ?>

    setInterval(function () {
        // Şu anda focus'ta olan elemanı kontrol et
        var activeElement = document.activeElement;

        // Eğer focus başka bir input elemanında değilse
        if (!$(activeElement).is('input, textarea, select')) {
            $('.input-fgd98').focus();
        }
    }, 3000);

    <?php    } ?>
</script>


<script>

    function click_edit_cls(id, part_id, type) {


        $.ajax({
            type: 'POST',
            url: 'controller/modules/sevkiyat-planlama-stoklar/sevk-kayıt/sevk-kayıt-controller.php?islem=edit-amount-shipping',
            data: {id: id, type: type, part_id: part_id},
            success: function (e) {
                $('#result-message').html(e);

                var selectedRow = $('#sevk-datagrid').datagrid('getSelected'); // Seçili satırı al
                if (selectedRow) {
                    var index = $('#sevk-datagrid').datagrid('getRowIndex', selectedRow); // Satırın indexini al
                }

                var rows = $('#sevk-datagrid').datagrid('getRows'); // Tüm satırları al
                for (var i = 0; i < rows.length; i++) {
                    // Her satırı daralt
                    $('#sevk-datagrid').datagrid('collapseRow', i);
                }

                $('#sevk-datagrid').datagrid('expandRow', index);


            }


        });

    }


</script>