<?
/*
Plugin Name: Custom BackGround
Plugin URI: http://reflexoesdigitais.com.br
Description: Now you can add difference backgrounds for each category
Author: Diego Cox, Eduardo Lobo e Ricardo Lanes
Version: 1.0
Author URI: http://blogfarm.com.br
*/

add_action('admin_menu', 'custom_background');

function custom_background(){
	if (function_exists('add_menu_page')) {
		add_menu_page('Custom BackGround', 'Custom BackGround', 'custom_bg', 'custom_background/background.php', 'custom_bg');
	}
}
// Set 'manage_home' Capabilities
$role = get_role('editor');
if(!$role->has_cap('custom_bg')) {
	$role->add_cap('custom_bg');
}
function custom_bg() {
	global $wpdb, $current_blog, $cat;
	$blog = $current_blog->blog_id;
	$query = "select ter.name, tax.term_id from wp_".$blog."_terms as ter, wp_".$blog."_term_taxonomy as tax where ter.term_id = tax.term_id and tax.taxonomy = 'category'";
	$rs = $wpdb->get_results($query,ARRAY_N);
	
	echo '<div class="wrap">';
	echo 	'<h2>Configura&ccedil;&atilde;o</h2>';
	echo '</div>';
	
	echo "<form name='enquetes' method='POST' action='".$_SERVER["PHP_SELF"]."?page=custom_background/background.php' enctype='multipart/form-data'>";
	echo 'Categoria: <select name="categorias">';
	echo  '<option value="0">Home</option>';
	for($i=0; $i < count($rs); $i++ ){
		echo  '<option value="'.$rs[$i][1].'">'.$rs[$i][0].'</option>';
	} 
	echo '</select><br />';

	echo 'Background Cor: <input name="cor" type="text"><br />';
	echo "Background Imagem: <input type='file' name='imagem' value='Imagem'><br /><br />";
	echo "<input type='submit' name='enviar' value='Relacionar'>";
	echo "</form>";

	if($_POST) {
		$category = $_POST['categorias'];
		$cor = $_POST['cor'];
		$imagem = $_FILES['imagem']['name'];
		if($_FILES['imagem']['name'] || $cor) {
			move_uploaded_file ( $_FILES['imagem']['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/wp-content/plugins/custom_background/imagem/".$imagem);
			$insertDados = "INSERT INTO background values(NULL, '$category', '$cor', '$imagem' )";
			$wpdb->query($insertDados);
			echo "<h3>Dados inseridos no banco com sucesso.</h3>";
		} else {
			echo "<h3>Seus dados n&atilde;o foram inseridos, todos os campos devem ser preenchidos.</h3>";	
		}	
	}
	if($_GET['excluir']) {
		$cod = $_GET['excluir'];
		$wpdb->query("DELETE FROM background WHERE cd_background = '$cod'"); 
		echo "<h3>Dados apagados com sucesso.</h3>";
	}
	
	$query = "SELECT ter.name, bg.cd_cor, bg.imagem, bg.cd_background FROM wp_".$blog."_terms AS ter, wp_".$blog."_term_taxonomy AS tax, background AS bg WHERE tax.term_id = bg.cd_categoria AND ter.term_id = tax.term_id AND tax.taxonomy = 'category'";
	//	SELECT ter.name, bg.cd_cor, bg.imagem, bg.cd_background FROM wp_".$blog."_terms ter, background as bg WHERE ter.term_id = bg.cd_categoria";
	$rs = $wpdb->get_results($query,ARRAY_N);
	for($i=0; $i<count($rs); $i++ ) {
		echo "<strong>Categoria: </strong>".$rs[$i][0]." - <strong>Cor: </strong>".$rs[$i][1]." - <strong>Imagem: </strong>".$rs[$i][2]." - "."<a href='?page=custom_background/background.php&excluir=".$rs[$i][3]." '>DELETE</a><br />";   
	} 
}

function getBackground($cat) {
	global $wpdb;
	$query = "SELECT * FROM background WHERE cd_categoria = '$cat' limit 1";
	$rs = $wpdb->get_results($query,ARRAY_N);
	if($rs) {
	$cor = $rs[0][2];
	$img = $rs[0][3];

	echo '
		<style>
		#mundo {
			background: '.$cor.' url(\'/wp-content/plugins/custom_background/imagem/'.$img.'\') center top no-repeat;
		}
		</style>';
	}
}
?>
