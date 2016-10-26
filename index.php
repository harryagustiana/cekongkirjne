<?php
/*
Plugin Name: Cek Ongkir JNE
Plugin URI: https://github.com/harryagustiana/cekongkirjne
Description: Sebuah plugin untuk menampilkan fitur cek biaya ongkos kirim oleh jasa pengiriman JNE berdasarkan kota pengiriman dan kota tujuan yang diperlukan di dalam sebuah laman. Menggunakan API RajaOngkir dalam proses perhitungannya.
Version: 1.0
Author: Harry Agustiana
Author URI: https://harryagustiana.web.id
*/

$key = '74215fc3b9402b130a384d46365f72b6';


function choose_city() {

	global $key;

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
	    "key: " . $key
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

	echo '<div id="result"></div>';
	echo '<form name="coj-inputdata" id="coj-inputdata" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
	echo '<p>';
	echo 'Kota Asal : <br/>';
	echo '<select class="chosen-select" name="coj-origin" required>';
	foreach($responsecity['rajaongkir']['results'] as $origin){
		echo "<option value=" . $origin['city_id'] . ">" . $origin['city_name'] . "</option>";
	}
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo 'Kota Tujuan <br/>';
	echo '<select class="chosen-select" name="coj-target" required>';
	foreach($responsecity['rajaongkir']['results'] as $target){
		echo "<option value=" . $target['city_id'] . ">" . $target['city_name'] . "</option>";
	}
	echo '</select>';
	echo '</p>';
	echo '<p>';
	echo 'Berat Kiriman (Gram) <br/>';
	echo '<input required type="number" name="coj-weight" min="1" size="40" />';
	echo '</p>';
	echo '<p><input type="submit" name="coj-submit" value="Send"></p>';
	echo '</form>';
}

function coj_shortcode() {
	ob_start();
	choose_city();
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

		jQuery(document).on('submit', '#coj-inputdata', function()
		{
			var data = $(this).serialize();

			$.ajax({
				type : 'POST',
				url  : '<?php echo plugin_dir_url( __FILE__ ); ?>inc/process.php',
				data : data,
				success :  function(data)
				{
					$('#coj-inputdata').fadeOut(500).hide();
					$('#result').fadeIn(500).show();
					$('#result').html(data);
				}
			});
			return false;
		});

		jQuery(document).on('click', '#reset', function(){
			$('#coj-inputdata')[0].reset();
			$('#result').html('');
			$('#result').fadeOut(500).hide();
			$('#coj-inputdata').fadeIn(500).show();
		});

		});	
	
</script>

<?php } 

add_action('wp_footer', 'add_this_script_footer'); 

// Enable shortcodes in text widgets
add_filter('widget_text','do_shortcode');


?>