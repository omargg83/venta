<?php
	require_once("db_tienda.php");
	$bdd = new Tienda();
	$pd = $bdd->tiendas_lista();

	echo "<div class='container' style='background-color:".$_SESSION['cfondo']."; '>";
	echo "<br>";
?>

<div class="content table-responsive table-full-width">
	<table id='x_tienda' class='dataTable compact hover row-border' style='font-size:10pt;'>
		<thead>
		<tr>
		<th>Numero</th>
		<th>Nombre</th>
		<th>Ubicación</th>
		<th>Activo</th>

		</thead>
		<tbody>
		<?php
			for($i=0;$i<count($pd);$i++){
				echo "<tr id='".$pd[$i]['id']."'' class='edit-t'>";
					echo "<td>";
					echo "<div class='btn-group'>";
					echo "<button class='btn btn-outline-secondary btn-sm' id='edit_persona' title='Editar' data-lugar='a_tienda/editar'><i class='fas fa-pencil-alt'></i></button>";
					echo "</div>";
					echo "</td>";
					echo "<td>".$pd[$i]["nombre"]."</td>";
					echo "<td>".$pd[$i]["ubicacion"]."</td>";
					echo "<td>";
					if ($pd[$i]["activo"]==1) { echo "Activo"; }
					else { echo "Inactivo"; }
					echo "</td>";
				echo "</tr>";
			}
		?>
		</tbody>
	</table>
</div>

<script>
	$(document).ready( function () {
		lista("x_tienda");
	});
</script>
