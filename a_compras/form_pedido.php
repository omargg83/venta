<?php
	require_once("db_.php");
	$bdd = new Compra();

	$id = $_REQUEST['id'];
	$compra = $bdd->compra($id);
	$pedido = $bdd->compras_pedido($id);


	echo "<table class='table table-sm'>";
	echo "<tr>
	<th>-</th>
	<th>Codigo</th>
	<th>Nombre</th>
	<th><center>Cantidad</center></th>
	<th>Unidad</th>
	</tr>";
	$gtotal=0;
	$idpaquete=0;
	$contar=1;
	$estado=$compra['estado'];

	for($i=0;$i<count($pedido);$i++){
		echo "<tr id='".$pedido[$i]['id']."' class='edit-t'>";
		echo "<td>";
		if($estado=="Activa"){
			echo "<button class='btn btn-outline-secondary btn-sm' id='eliminar_comision' data-lugar='a_compras/db_' data-destino='a_compras/form_pedido' data-id='".$pedido[$i]['id']."' data-iddest='$id' data-funcion='borrar_producto' data-div='pedidos'><i class='far fa-trash-alt'></i></i></button>";
		}
		echo "</td>";
		echo "<td>".$pedido[$i]['codigo']."</td>";
		echo "<td>".$pedido[$i]['nombre']."</td>";
		echo "<td align='center'>".$pedido[$i]['cantidad_oc']."</td>";
		echo "<td align='center'>".$pedido[$i]['unidad']."</td>";
		echo "</tr>";
	}
?>
