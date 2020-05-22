<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1" name="viewport">
	<meta content="ie=edge" http-equiv="X-UA-Compatible">
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.4.0/dist/leaflet.css" />
	<link crossorigin="anonymous" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" rel="stylesheet">
	<link crossorigin="" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" rel="stylesheet">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
	<link rel="stylesheet" href="css.css"> 
    <script crossorigin="" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script type="text/javascript" src="jquery-1.11.1.min.js" ></script>
	<script src="https://unpkg.com/leaflet-kmz@latest/dist/leaflet-kmz.js"></script>
	<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
	<title>1705551071 | COVID-19 Bali</title>
</head>

<body>
	<div id="update-form" style="display: none;">
		<div id="update-body">
			<input type="hidden" id="id_sebaran">
			<p style="flex: 25%; padding: 15px; margin-bottom: 0; background: #f2f2f2; border-radius: 5px 0 0 5px;">Pasien Positif</p>
			<input type="number" placeholder="Jumlah Pasien Positif" id="pasien_positif" style="padding-left: 10px; padding-right: 10px; flex: 47%; ">
			<button class="button button3" id="btn-update" onclick="update()" style="flex: 20%;">Update</button>
			<button class="button" onclick="document.getElementById('update-form').style.display = 'none'" style="flex: 8%;"><i class="fa fa-close"></i></button>
		</div>
	</div>

	<div id="info_positif">
		<table id="table_positif">
			<tr>
				<th>Kabupaten</th>
                <th>Pasien Positif</th>
                <th>Tanggal</th>
				<th>Action</th>
			</tr>
		</table>
	</div>

	<div id="setting">
		<div id="update-body">
			<p style="flex: 25%; text-align: left; padding: 12px; margin-bottom: 0; background: #f2f2f2; border-radius: 5px 0 0 0;">Maksimum Kepekatan</p>
			<div style="background: #eeeeee; width: 1px;"></div>
			<input id="max-grad" type="number" style="background: #f2f2f2; padding-left: 10px; padding-right: 10px; width: 100px; border-radius: 0 5px 0 0;">
		</div>
		<hr style="margin: 0;">
		<div id="update-body">
			<p style="flex: 25%; text-align: left; padding: 12px; margin-bottom: 0; background: #f2f2f2;">Warna</p>
			<div style="background: #eeeeee; width: 1px;"></div>
			<input id="color-grad" type="color" style="height: auto; width: 100px; ">
		</div>
		<div id="update-body">
			<button class="button button3" id="btn-max-grad" style="text-align: center; flex: 100%; border-radius: 0 0 5px 5px;">Update</button>
		</div>
	</div>

	<div id="map">
	</div>

	<div id="snackbar">Berhasil! Data pasien positif telah diperbarui</div>

	<script>
	       var popup = L.popup();
		   var mymap = L.map('map').setView([-8.655924, 115.216934], 9);
		   var layerGroup = L.layerGroup().addTo(mymap);
		   var arr_pasien = [];
		   var arr_pos = [];
		   var arr_name = [];
	       var countId = 0;
		   var max_pos = 0;
		   var cur_pos = 0;
		   var kmzParser = null;
		   var color = '#000';

			$(document).ready(function(){
				// add map into layer
				L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
					attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
					maxZoom: 18,
					id: 'mapbox/streets-v11',
					tileSize: 512,
					zoomOffset: -1,
					accessToken: 'pk.eyJ1IjoiYWRpbWVydGEiLCJhIjoiY2s2dm03dzE4MDNhZTNrcW15NWtmd3RtMCJ9.rkSmf6WG-lHvVIeQpABNBw'
				}).addTo(mymap);

				// fullscreen button
				mymap.addControl(new L.Control.Fullscreen());

				// get postive patients
				$.ajax({
					url: "./get_pasien.php",
					type: "get",
					dataType: 'json',
					success: function (msg, status, jqXHR){
						if(msg[0].kabupaten == 'kosong'){
							// show toast while copying data from yesterday
							snackbar.className = "show";
							snackbar.innerHTML = "Data hari ini telah diperbarui. Silakan reload halaman untuk melihat data";
							document.getElementById("update-form").style.display = "none";
							setTimeout(function(){ snackbar.className = snackbar.className.replace("show", ""); }, 3000);

						} else {
							// showing data
							arr_pasien = msg;
							for (var i = 0; i < arr_pasien.length; i++) {
								if (arr_pasien[i].kabupaten.indexOf(name) >= 0) {
									arr_pos.push(arr_pasien[i].pasien_positif);
								}
							}

							// set max patient to max gradation
							max_pos = Math.max.apply(Math,arr_pos);
							$('#max-grad').val(max_pos);
							$.each(msg, function(i,obj){
								var id = msg[i].id_sebaran;
								var kab = msg[i].kabupaten;
								var pos = msg[i].pasien_positif;
								var tgl = msg[i].tanggal;
								setTableCoordinates(id,kab,pos,tgl);
							});
						}
					}
				});

				// load kmz
				kmz();
				kmzParser.load('bali.kmz');
				function kmz() {
					kmzParser = new L.KMZParser({
						onKMZLoaded: function(layer, name) {
							control.addBaseLayer(layer, name);
							var layers = layer.getLayers()[0].getLayers();
							layers.forEach(function(layer2, index){
								var name  = layer2.feature.properties.name;
								name = name.toUpperCase();
								arr_name = name;
								for (var i = 0; i < arr_pasien.length; i++) {
									if (arr_pasien[i].kabupaten.indexOf(name) >= 0) {

										// change fill color for each regency
										layer2.setStyle({opacity:'1',color:'#fff',fillOpacity:''+(arr_pasien[i].pasien_positif * (1 / max_pos)),fillColor:''+color});
										break;
									}
								}
							});

							// add kmz into layer
							layer.addTo(layerGroup);
						}
					});
				}
				var control = L.control.layers(null, null, { collapsed:true }).addTo(mymap);

				// update color and max color gradation 
				$('#btn-max-grad').on('click', function(e) {  
					layerGroup.clearLayers();
					max_pos = parseInt($('#max-grad').val());
					color = document.getElementById('color-grad').value;
					kmz();
					kmzParser.load('bali.kmz');
					console.log(max_pos);
				});

				// update color and max color gradation 
				$('#btn-update').on('click', function(e) {  
					layerGroup.clearLayers();
					max_pos = parseInt($('#max-grad').val());
					color = document.getElementById('color-grad').value;
					kmz();
					kmzParser.load('bali.kmz');
					console.log(max_pos);
				});
								
				// create table in info_positif
				function setTableCoordinates(id,kab,pos,tgl){
					var table = document.getElementById("table_positif");
					var row = table.insertRow(-1);
					var cell1 = row.insertCell(0);
					var cell2 = row.insertCell(1);
					var cell3 = row.insertCell(2);
					var cell4 = row.insertCell(3);

					row.id = 'row';
					cell1.innerHTML = kab;
					cell2.id = 'pos-'+id;
					cell2.innerHTML = pos;
					cell3.innerHTML = tgl;
					cell4.innerHTML = "<button id='"+id+"' name='"+pos+"' onClick='showUpdate(this.id,this.name)' class='button button3'>Update<\/button>";
				}

				// hide kmz controll layer
				$('.leaflet-control-layers').hide();	
			});
           
			// show patient update form
			function showUpdate(id,pos){
				document.getElementById("update-form").style.display = "block";
				document.getElementById("id_sebaran").value = id;
				document.getElementById("pasien_positif").value = pos;
			}

			// update patient positif for a regency
	       	function update(){
				$(document).ready(function(){
					var id_sebaran = document.getElementById('id_sebaran').value;
					var pasien_positif = document.getElementById('pasien_positif').value;
					var snackbar = document.getElementById("snackbar");

					$.ajax({
						url: "./update_pasien.php",
						type: "post",
						data: {"id_sebaran" : id_sebaran, "pasien_positif" : pasien_positif},
						success: function (msg, status, jqXHR){
							document.getElementById("pos-"+id_sebaran).innerHTML = pasien_positif;
							document.getElementById(id_sebaran).name = pasien_positif;
							// show toast
							snackbar.className = "show";
							document.getElementById("update-form").style.display = "none";
							setTimeout(function(){ snackbar.className = snackbar.className.replace("show", ""); }, 3000);
						}
					});
				});
	     	}
	</script>
</body>
</html>