<?php
/*
Plugin Name: Cek Ongkir JNE
Plugin URI: https://github.com/harryagustiana/cekongkirjne
Description: Sebuah plugin untuk menampilkan fitur cek biaya ongkos kirim oleh jasa pengiriman JNE berdasarkan kota pengiriman dan kota tujuan yang diperlukan di dalam sebuah laman. Menggunakan API RajaOngkir dalam proses perhitungannya.
Version: 1.0
Author: Harry Agustiana
Author URI: https://harryagustiana.web.id
*/


function choose_city() {
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://api.rajaongkir.com/starter/city",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
	    "key: 74215fc3b9402b130a384d46365f72b6"
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} 
	// else {
	//   echo $response;
	// }

	$responsecity = json_decode($response, true);

	//var_dump($responsecity['rajaongkir']['results']);


	echo '<form name="coj-inputdata" id="coj-inputdata" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
	echo '<p>';
	echo 'Kota Asal : <br/>';
	echo '<select class="chosen-select" name="coj-origin">';
	foreach($responsecity['rajaongkir']['results'] as $origin){
		echo "<option value=" . $origin['city_id'] . ">" . $origin['city_name'] . "</option>";
	}
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo 'Kota Tujuan <br/>';
	echo '<select class="chosen-select" name="coj-target">';
	foreach($responsecity['rajaongkir']['results'] as $target){
		echo "<option value=" . $target['city_id'] . ">" . $target['city_name'] . "</option>";
	}
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo 'Berat Kiriman (Gram) <br/>';
	echo '<input type="number" name="coj-weight" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["coj-weight"] ) ? esc_attr( $_POST["coj-weight"] ) : '' ) . '" size="40" />';
	echo '</p>';
	echo '<p><input type="submit" name="coj-submit" value="Send"></p>';
	echo '</form>';
}

function output_result() {

	


	// if the submit button is clicked, send the email
	if ( isset( $_POST['coj-submit'] ) ) {

		// sanitize form values
		$origin    = sanitize_text_field( $_POST["coj-origin"] );
		$target   = sanitize_text_field( $_POST["coj-target"] );
		$weight = sanitize_text_field( $_POST["coj-weight"] );


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
		    "key: 74215fc3b9402b130a384d46365f72b6"
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
		echo "<p><button onClick='window.history.back()'>Hitung ulang ongkos kirim</button></p>";
		echo "</div>";
	}
}

function coj_shortcode() {
	ob_start();
	choose_city();
	output_result();

	return ob_get_clean();
}

add_shortcode( 'cek_ongkir_jne', 'coj_shortcode' );


?>

<?php


function add_this_script_footer(){ ?>

<link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ); ?>assets/chosen/chosen.css">
<script type="text/javascript" src="<?php echo includes_url();?>/js/jquery/jquery.js"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ); ?>assets/chosen/chosen.jquery.js" type="text/javascript"></script>
<script>
	jQuery.noConflict();
	jQuery(document).ready(function($) { 
		$(".chosen-select").chosen(); 

		function resetForms() {
		    window.history.back();
		}
	});	
</script>

<?php } 

add_action('wp_footer', 'add_this_script_footer'); ?>