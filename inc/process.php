<?php

$key = '74215fc3b9402b130a384d46365f72b6';

if ($_POST) {

		// sanitize form values
		$origin = $_POST["coj-origin"];
		$target = $_POST["coj-target"];
		$weight = $_POST["coj-weight"];


		// get cost result from RajaOngkir
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://api.rajaongkir.com/starter/cost",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "origin=".$origin."&destination=".$target."&weight=".$weight."&courier=jne",
		  CURLOPT_HTTPHEADER => array(
		    "content-type: application/x-www-form-urlencoded",
		    "key: " . $key
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} 



		$responsecost = json_decode($response, true);

		echo "<div id='coj-outputdata'>";
		echo "<p>Asal Kota : ".$responsecost['rajaongkir']['origin_details']['city_name']."</p>";
		echo "<p>Kota Tujuan : ".$responsecost['rajaongkir']['destination_details']['city_name']."</p>";
		echo "<p>Berat Kiriman : ".$responsecost['rajaongkir']['query']['weight']." gr</p>";
		echo "<p>Jenis Layanan</p>";
		if ($responsecost['rajaongkir']['results'][0]['costs'] <> NULL){
			echo "<table>";
			echo "<tr>";
			echo "<th>Nama Layanan</th>";
			echo "<th>Lama Pengiriman</th>";
			echo "<th>Biaya Jasa</th>";
			echo "</tr>";
			foreach ($responsecost['rajaongkir']['results'][0]['costs'] as $allcost) {
				echo "<tr>";
				echo "<td>" . $allcost['service'] . "</td>";
				foreach ($allcost['cost'] as $costservice) {
					
					if ($costservice['etd'] != ''){
						echo "<td>" . $costservice['etd'] . " (Hari)</td>";
					}else{
						echo "<td>" . $costservice['etd'] . "</td>";
					}
					echo "<td>Rp. " . number_format($costservice['value'], 0, ',', '.') . '</td>';
				}
				echo "</tr>";
			}
			echo "</table>";
		}else{
			echo "<p>Tidak ada layanan pengiriman yang tersedia</p>";
		}
		echo "<p><button id='reset'>Reset</button></p>";
		echo "</div>";
	}
?>