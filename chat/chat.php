<?php
	if (!isset($_SESSION)) { session_start(); }
	if (isset($_REQUEST['function'])){$function=$_REQUEST['function'];}	else{ $function="";}

	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	class Chat{
		private $accesox;
		public function __construct(){
			date_default_timezone_set("America/Chicago");
			$this->Salud = array();
			$this->dbh = new PDO('mysql:host=sagyc.com.mx;dbname=sagycrmr_smhidalgo', "sagyccom_esponda", "esponda123$");
		}
		public function set_names(){
			return $this->dbh->query("SET NAMES 'utf8'");
		}
		public function general($sql){
			try{
				self::set_names();
				$this->geral=array();
				foreach ($this->dbh->query($sql) as $res){
					$this->geral[]=$res;
				}
				return $this->geral;
				$this->dbh=null;
			}
			catch(PDOException $e){
				return "Database access FAILED!".$e->getMessage();
			}
		}
		public function conectado(){
			try{
				self::set_names();
				$sql="select * from chat_conectados where idpersona='".$_SESSION['idpersona']."'";
				$stmt= $this->dbh->query($sql);
				if($stmt->rowCount()==0){
					$fecha=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));

					if(strlen($_SESSION['nick'])>0){
						$nick=$_SESSION['nick'];
					}
					else{
						$nick=$_SESSION['nombre'];
					}
					$sql="insert into chat_conectados (idpersona, ultima, foto, nick, nombre) values ('".$_SESSION['idpersona']."', '$fecha', '".$_SESSION['foto']."', '$nick', '".$_SESSION['nombre']."')";
					$stmt= $this->dbh->query($sql);
					$_SESSION["tchat"]=$fecha;
				}
				else{
					try{
						if(strlen($_SESSION['nick'])>0){
							$nick=$_SESSION['nick'];
						}
						else{
							$nick=$_SESSION['nombre'];
						}
						$fecha=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
						$sql="update chat_conectados set ultima='$fecha', foto='".$_SESSION['foto']."', nick='$nick', nombre='".$_SESSION['nombre']."' where idpersona='".$_SESSION['idpersona']."'";
						$conec= $this->dbh->query($sql);
						$_SESSION["tchat"]=$fecha;
					}
					catch(PDOException $e){
						return "Database access FAILED!".$e->getMessage();
					}
				}
			}
			catch(PDOException $e){
				return "Database access FAILED!".$e->getMessage();
			}
		}
	}

	if(strlen($function)>0){
		echo $function();
	}

	function inicia(){
		$chat = new Chat();
		//$fecha2=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"))-600000;
		// $sql="delete from chat_conectados where ultima<'$fecha2'";
		// $row=$chat->general($sql);
		$_SESSION["carga"]=1;

		$x="<li class='nav-item dropdown'>";
			$x.= "<a class='nav-link dropdown-toggle' id='navbarDropdown' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
			  <i class='fab fa-rocketchat fa-spin' style='color:#96ff57 !important;'></i> Chat";
			$x.= "</a>";

			$x.= "<div id='myUL' class='dropdown-menu' aria-labelledby='navbarDropdown' style='width:200px;max-height:400px !important; overflow: scroll; overflow-x: hidden;'>";
			$x.="<div class='row'><div class='col-12'><input type='text' id='myInput' placeholder='Buscar..' title='Buscar' class='form-control' autocomplete='off'></div></div>";
				$x.="<div id='conecta_x'>";
				$x.= "</div>";
			$x.= "</div>";
		$x.= "</li>";


		return $x;
	}
	function conectados(){
		$chat = new Chat();
		$chat->conectado();
		$x="";
		$fecha2=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"))-100;
		$sql="select chat_conectados.idpersona,chat_conectados.nick,chat_conectados.foto, chat_conectados.nombre, chat_conectados.ultima from chat_conectados
		where chat_conectados.idpersona!='".$_SESSION['idpersona']."' order by chat_conectados.ultima desc";
		$row=$chat->general($sql);
		for($i=0;$i<count($row);$i++){
			$x.= "<a class='dropdown-item' onclick='register_popup(\"".$row[$i]['idpersona']."\")' title='".$row[$i]['nombre']."'>";
			if(($row[$i]['ultima']-$fecha2)>0){
				$x.= "<i class='fab fa-rocketchat' style='color:black;'></i>";
			}
			else{
				$x.="<i class='fas fa-user-clock'></i>";
			}
			$x.=$row[$i]['nick'];
			$x.= "</a>";
		}

		return $x;
	}
	function carga(){
		$chat = new Chat();
		$idpersona=$_REQUEST['id'];
		$x="";
		///////////////////////////////////////////////////////
			$fecha=mktime(0,0,0,date("m"),date("d"),date("Y"))-100;
			$sql="select * from chat_conectados where idpersona='$idpersona'";
			$pers=$chat->general($sql);

			$x.="<div id='opcion_$idpersona' class='opcionbox'>";

			$arreglo=array();
			$directory="../chat/smileys/";
			$dirint = dir($directory);
			$contar=0;
			while (($archivo = $dirint->read()) !== false){
				if ($archivo != "." && $archivo != ".." && $archivo != "" && (substr($archivo,-4)==".png" || substr($archivo,-4)==".gif")){
					$arreglo[$contar]=$directory.$archivo;
					$x.="<img src='chat/smileys/$archivo' width='20px' class='emojiimg' data-id='$idpersona' data-lugar='chat/smileys/$archivo'>";
					$contar++;
				}
			}

			$x.="</div>";

			$x.= "<div class='card-header' id='head$idpersona'>";
				$x.= "<div class='row'>";
					$x.= "<div class='col-2'>";
						if(strlen($pers[0]['foto'])>0){
							$x.="<img src='a_personal/papeles/".trim($pers[0]['foto'])."' width='30px' width='40px'/>";
						}
						else{
							$x.="<img src='chat/Screenshot_1.png' width='30px' width='40px'/>";
						}
					$x.="</div>";
					$x.= "<div class='col-6'>";
						$x.= "<label style='width:100px !important;height:23px;color:white;font-size:10pt;overflow:hidden;white-space:nowrap;text-overflow: ellipsis;' title='".$pers[0]['nick']."'>".$pers[0]['nick']."</label>";
					$x.= "</div>";
					$x.= "<div class='col-4'>";
						$x.= "<div class='btn-group'>";
							$x.= "<a id='min$idpersona' class='btn btn-outline-secondary btn-sm' onclick='minimizar(\"$idpersona\")'><i class='fas fa-window-minimize'></i></a>";
							$x.= "<a id='max$idpersona' style='display:none;'  class='btn btn-outline-secondary btn-sm' onclick='maximizar(\"$idpersona\")'><i class='fas fa-window-maximize'></i></a>";
							$x.= "<a class='btn btn-outline-secondary btn-sm' onclick='close_popup(\"$idpersona\")'><i class='fas fa-times-circle'></i></a>";
						$x.= "</div>";
					$x.= "</div>";
				$x.= "</div>";
			$x.= "</div>";

			$x.= "<div class='card-body contenido'  id='contenido$idpersona'
			ondragenter='return enter(event)'
			ondragover='return over(event)'
			ondragleave='return leave(event)'
			ondrop='return drop(event,".$idpersona.")'>";

			$sql="select * from chat where ((de='".$_SESSION['idpersona']."' and para='$idpersona') or (de='$idpersona' and para='".$_SESSION['idpersona']."')) order by idchat asc";
			$men=$chat->general($sql);
			$refresh="";
			for($i=0;$i<count($men);$i++){
				if($men[$i]['de']==$_SESSION['idpersona']){
					$x.= "<div class='b2'>";
					$x.= "<b>Yo:</b>";
					$refresh="para".$men[$i]['paran'];
					$nick=$men[$i]['paran'];
				}
				else{
					$x.= "<div class='burbuja'>";
					$x.= "<b>".$men[$i]['den'].":</b>";
					$refresh="para".$men[$i]['den'];
					$nick=$men[$i]['den'];
				}
				//$x.= "<br>".smiley($men[$i]['mensaje']);
				$x.= "<br>".$men[$i]['mensaje'];
				$x.= "<span id='horachat'>";
				$x.= fecha($men[$i]['envio']);
				$x.= "</span></div>";
				$_SESSION["tchat"]=$men[$i]['envio'];
			}
			$x.= "</div>";

			$x.= "<div class='card-footer popup-messages-footer' id='mensajex$idpersona'>";
				$x.= "<div class='row'>";
					$x.= "<div class='col-sm-12'>";
						$x.="<div contenteditable='true' class='mensaje_chat' data-para='$idpersona' id='mensaje_$idpersona' name='mensaje_$idpersona' onclick='leido($idpersona)'>";
					$x.="</div>";

					$x.= "</div>";
					$x.= "</div>";
					$x.= "<div class='row'>";
						$x.= "<div class='col-sm-12 btn-footer'>";
							$x.= "<div class='btn-group' role='group' aria-label='Basic example' style='font-color:white;'>";

								$x.= "<button title='Mandar' class='btn btn-outline-secondary btn-sm' onclick='mensaje_manda(document.getElementById(\"mensaje_$idpersona\").value,\"$idpersona\")'><i class='fas fa-location-arrow'></i></button>";

								$x.= "<button class='btn btn-outline-secondary btn-sm emoji' data-id='$idpersona'><i class='far fa-smile-wink'></i></button>";

								$x.= "<button class='btn btn-outline-secondary btn-sm btn-file'>";
								$x.= "<i class='fas fa-paperclip'></i><input class='form-control' type='file'
										id='subechat_$idpersona'
										name='subechat_$idpersona'
										data-control='subechat_$idpersona'
										data-ruta='tmp'
										data-funcion='carga_archivo'
										data-urlx='chat/chat.php'
										data-id='".$idpersona."'
										data-iddest='$idpersona'
										data-divdest='trabajo'
										data-dest='a_comite/editar.php?id='
										>";

								//$x.= "<button type='button' class='btn btn-outline-secondary btn-sm' data-toggle='modal' data-target='#myModal' id='fileup_foto' data-ruta='a_personal/papeles/' data-tabla='personal' data-campo='file_foto' data-tipo='1' data-id='$id' data-keyt='idpersona' data-destino='a_personal/editar' data-proceso='chat/chat' data-iddest='$id' data-ext='.jpg,.png' title='Subir foto'><i class='fas fa-cloud-upload-alt'></i></button>";

								$x.= "</button>";




								// $x.= "<a href='#' title='Like' class='btn btn-outline-secondary btn-sm' onclick='xajax_mensaje_manda(\":0001\",\"$idpersona\")'><i class='em em---1'></i></a>";
								// $x.= "<a href='#' title='Unlike' class='btn btn-outline-secondary btn-sm' onclick='xajax_mensaje_manda(\":0002\",\"$idpersona\")'><i class='em em--1'></i></a>";
								// $x.= "<a href='#' title='Emoticons' class='btn btn-outline-secondary btn-sm' onclick='xajax_opciones(\"$idpersona\",\"1\")'><i class='far fa-smile'></i></a>";

							$x.= "</div>";
						$x.= "</div>";
					$x.= "</div>";
				$x.= "</div>";
			$x.= "</div>";
		return $x;
	}
	function manda(){
		$chat = new Chat();
		$x="";
		$mensaje=$_REQUEST['texto'];
		$idpersona=$_REQUEST['id'];
		$tam=strlen(trim($mensaje));
		if($tam>0){

			// $text = html_entity_decode($mensaje);
			// $text = " ".$text;
			// $text = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)','<a href="\\1" target=_blank>\\1</a>', $text);
			// $text = eregi_replace('(((f|ht){1}tps://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)','<a href="\\1" target=_blank>\\1</a>', $text);
			// $text = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)','\\1<a href="http://\\2" target=_blank>\\2</a>', $text);
			// //$mensaje=smiley($text);

			if(strlen($mensaje)>0){
				$fecha=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$sql="insert into chat (de,para,mensaje,envio,leido,den) values ('".$_SESSION['idpersona']."','$idpersona','$mensaje','$fecha','0','".$_SESSION['nick']."')";
				$resp=$chat->general($sql);
				$x.= "<div class='b2'>";
				$x.= "<b>Yo:</b>";
				$x.= "<br>".$mensaje;
				$x.= "<span id='horachat'>$fecha";
				$x.= date('Y-m-d H:i:s',$fecha);
				$x.= "</span></div>";
			}
		}
		return $x;
	}
	function nuevos(){
		$chat = new Chat();
		$arr=array();
		if($_SESSION["carga"]==1){
			$_SESSION["carga"]=0;
			$sql="select * from chat where para='".$_SESSION['idpersona']."' and (envio>'".$_SESSION["tchat"]."' or leido=0) order by envio asc,idchat asc";
		}
		else{
			$sql="select * from chat where para='".$_SESSION['idpersona']."' and (envio>'".$_SESSION["tchat"]."' and leido=0) order by envio asc,idchat asc";
		}
		$men=$chat->general($sql);
		if(isset($_SESSION['idpersona']) and count($men)>0){
			for($i=0;$i<count($men);$i++){
				$x="<div class='burbuja'>";
				$x.="<b>".$men[$i]['den'].":</b>";
				$x.="<br>".$men[$i]['mensaje'];
				$x.="<span id='horachat'>";
				$x.=date('Y-m-d H:i:s',$men[$i]['envio']);
				$x.="</span></div>";
				$arr[$i] = array('para' => $men[$i]['de'],'texto' => $x,'de' => $_SESSION['idpersona']);
				$_SESSION["tchat"]=$men[$i]['envio'];
			}
			$myJSON = json_encode($arr);
			echo $myJSON;
		}
	}
	function leido(){
		$chat = new Chat();
		$arr=array();
		$idpersona=$_REQUEST['id'];
		$sql="update chat set leido=1 where para='".$_SESSION['idpersona']."' and de='$idpersona'";
		$chat->general($sql);
	}
	function carga_archivo(){
		$chat = new Chat();
		$x="";
		$arreglo =array();
		if (isset($_REQUEST['id'])){$id=$_REQUEST['id'];}
		if (isset($_REQUEST['archivo'])){$archivo=$_REQUEST['archivo'];}
		if (isset($_REQUEST['original'])){$original=$_REQUEST['original'];}
		$file="";
		$info = new SplFileInfo($archivo);
		$ext=$info->getExtension();

		if($ext=="jpg" or $ext=="png"){
			$file.="<img src=\'chat/tmp/$archivo\' width=\'100%\' />";
		}
		$fecha=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
		$file.="<a class=\'htipo1\' href=\'chat/tmp/".$archivo."\' target=\'_blank\'><i class=\'fas fa-paperclip\'></i>".$original."</a>";
		$sql="insert into chat (de,para,mensaje,envio,den,leido) values ('".$_SESSION['idpersona']."','$id','$file','$fecha','".$_SESSION['nick']."','0')";
		$resp=$chat->general($sql);

		$x.= "<div class='b2'>";
		$x.= "<b>Yo:</b>";
		if($ext=="jpg" or $ext=="png"){
			$x.="<img src='chat/tmp/$archivo' width='100%' />";
		}
		$x.= "<br><a href='chat/tmp/$archivo' target='_blank'><i class='fas fa-paperclip'></i>$original";
		$x.="</a>";
		$x.= "<span id='horachat'>$fecha";
		$x.= date('Y-m-d H:i:s',$fecha);
		$x.= "</span></div>";
		return $x;
	}
	function subir_archivo(){
		$id=$_GET['id'];
		$ruta=$_GET['ruta'];
		$contarx=0;
		$arr=array();

		foreach ($_FILES as $key){
			$extension = pathinfo($key['name'], PATHINFO_EXTENSION);
			$n = $key['name'];
			$s = $key['size'];
			$string = trim($n);
			$string = str_replace( $extension,"", $string);
			$string = str_replace( array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string );
			$string = str_replace( array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string );
			$string = str_replace( array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string );
			$string = str_replace( array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string );
			$string = str_replace( array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string );
			$string = str_replace( array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string );
			$string = str_replace( array(' '), array('_'), $string);
			$string = str_replace(array("\\","¨","º","-","~","#","@","|","!","\"","·","$","%","&","/","(",")","?","'","¡","¿","[","^","`","]","+","}","{","¨","´",">","<",";",",",":","."),'', $string );
			$string.=".".$extension;

			$n_nombre=$id."_".$contarx."_".$string;
			$destino=$ruta."/".$n_nombre;

			if(move_uploaded_file($key['tmp_name'],$destino)){
				chmod($destino,0666);
				$arr[$contarx] = array("archivo" => $n_nombre,"original" => $n);
			}
			else{

			}
			$contarx++;
		}
		$myJSON = json_encode($arr);
		return $myJSON;
	}
	function fecha($fecha){
		$fecha = new DateTime(date('Y-m-d H:i:s',$fecha));
		return $fecha->format('d-m-Y h:i:s');
	}

?>
