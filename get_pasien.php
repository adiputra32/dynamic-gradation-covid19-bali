<?php
    include "conn.php";

    date_default_timezone_set("Asia/Singapore");
    $query2 = $conn->query("SELECT tanggal FROM tb_sebaran ORDER BY id_sebaran DESC LIMIT 1");
    $row = $query2->fetch_assoc();
    $dateYest = $row['tanggal'];

    $dateNow=date("Y-m-d");
    $diff=date_diff(date_create($dateNow),date_create($dateYest));

    if ($diff->format("%a") > 0){
        $sql = "CREATE TEMPORARY TABLE tb_sebaran_tmp SELECT * FROM tb_sebaran WHERE tanggal = '".$dateYest."';";
        $sql .= "UPDATE tb_sebaran_tmp SET id_sebaran = NULL, tanggal = '".$dateNow."';";
        $sql .= "INSERT INTO tb_sebaran SELECT * FROM tb_sebaran_tmp;";
        $sql .= "DROP TEMPORARY TABLE IF EXISTS tb_sebaran_tmp;";
        $query3 = $conn->multi_query($sql);
    }

    $query = $conn->query("SELECT id_sebaran, kabupaten, pasien_positif, tanggal
                            FROM tb_sebaran
                            JOIN tb_kabupaten ON tb_kabupaten.id_kab = tb_sebaran.id_kabupaten
                            WHERE tanggal = '$dateNow' ;");
    $rows = array();
    if(mysqli_num_rows ( $query ) < 1){
        $rows[0] = array('id_sebaran' => '','kabupaten' => 'kosong','pasien_positif' => '','tanggal' => '');
    } else {
        while ($rowData = mysqli_fetch_array($query)) {
            $rows[] = $rowData;
        }
    }

    print json_encode($rows);
?>