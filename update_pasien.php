<?php
	include 'conn.php';
	
	$id_sebaran=$_POST['id_sebaran'];
	$pasien_positif=$_POST['pasien_positif'];

	$query = "UPDATE tb_sebaran SET pasien_positif = '$pasien_positif' WHERE id_sebaran = '$id_sebaran';";
	
	if (!mysqli_query($conn, $query)) {
		echo json_encode(array("error"=>true));
	}
    mysqli_close($conn);
?>