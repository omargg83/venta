<?php
require_once("db_tienda.php");
$bdd = new Tienda();


	echo "<nav class='navbar navbar-expand-lg navbar-light bg-light '>
	<a class='navbar-brand' ><i class='fas fa-user-check'></i> Tiendas</a>	
	  <button class='navbar-toggler navbar-toggler-right' type='button' data-toggle='collapse' data-target='#navbarSupportedContent' aria-controls='principal' aria-expanded='false' aria-label='Toggle navigation'>
		<span class='navbar-toggler-icon'></span>
	  </button>
		  <div class='collapse navbar-collapse' id='navbarSupportedContent'>
			<ul class='navbar-nav mr-auto'>";
			echo"<li class='nav-item active'><a class='nav-link barranav' title='Mostrar todo' id='lista_comision' data-lugar='a_tienda/lista'><i class='fas fa-list-ul'></i><span>Lista</span></a></li>";
			

			echo"<li class='nav-item active'><a class='nav-link barranav izq' title='Nuevo' id='new_personal' data-lugar='a_tienda/editar'><i class='fas fa-plus'></i><span>Nuevo</span></a></li>";
	
			echo "</ul>";
		echo "
	  </div>
	</nav>";

?>
<div id='trabajo'>
	<?php
		include 'lista.php';
	?>
</div>
	