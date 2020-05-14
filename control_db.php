<?php
	if (!isset($_SESSION)) { session_start(); }
	if (isset($_REQUEST['function'])){$function=$_REQUEST['function'];}	else{ $function="";}
	if (isset($_REQUEST['ctrl'])){$ctrl=$_REQUEST['ctrl'];}	else{ $ctrl="";}

	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	date_default_timezone_set("America/Mexico_City");

	class Sagyc{
		public $nivel_personal;
		public $nivel_captura;
		public $limite=300;

		public function __construct(){
			date_default_timezone_set("America/Mexico_City");
			$this->Salud = array();
			$this->dbh = new PDO('mysql:host=sagyc.com.mx;dbname=sagycrmr_pventa', "sagyccom_esponda", "esponda123$");
		}
		public function set_names(){
			return $this->dbh->query("SET NAMES 'utf8'");
		}

		public function login(){
			$arreglo=array();
			if(isset($_SESSION['idpersona']) and $_SESSION['autoriza'] == 1) {
				$valor=$_SESSION['idfondo'];
				$arreglo=array('sess'=>"abierta", 'fondo'=>$valor);
			}
			else {
				///////////////////////////login
				$valor=$_SESSION['idfondo'];
				$arreglo=array('sess'=>"cerrada", 'fondo'=>$valor);
				//////////////////////////fin login
			}
			return json_encode($arreglo);
		}
		public function salir(){
			$_SESSION['autoriza'] = 0;
			$_SESSION['idpersona']="";
		}

		public function insert($DbTableName, $values = array()){
			$arreglo=array();
			try{
				self::set_names();
				$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

				foreach ($values as $field => $v)
				$ins[] = ':' . $field;

				$ins = implode(',', $ins);
				$fields = implode(',', array_keys($values));
				$sql="INSERT INTO $DbTableName ($fields) VALUES ($ins)";
				$sth = $this->dbh->prepare($sql);
				foreach ($values as $f => $v){
					$sth->bindValue(':' . $f, $v);
				}
				if ($sth->execute()){
					$arreglo+=array('id'=>$this->lastId = $this->dbh->lastInsertId());
					$arreglo+=array('error'=>0);
					$arreglo+=array('terror'=>'');
					$arreglo+=array('param1'=>'');
					$arreglo+=array('param2'=>'');
					$arreglo+=array('param3'=>'');
					return json_encode($arreglo);
				}
			}
			catch(PDOException $e){
				$arreglo+=array('id'=>0);
				$arreglo+=array('error'=>1);
				$arreglo+=array('terror'=>$e->getMessage());
				return json_encode($arreglo);
			}
		}
		public function update($DbTableName, $id = array(), $values = array()){
			$arreglo=array();
			try{
				self::set_names();
				$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
				$x="";
				$idx="";
				foreach ($id as $field => $v){
					$condicion[] = $field.'= :' . $field."_c";
				}
				$condicion = implode(' and ', $condicion);
				foreach ($values as $field => $v){
					$ins[] = $field.'= :' . $field;
				}
				$ins = implode(',', $ins);

				$sql2="update $DbTableName set $ins where $condicion";
				$sth = $this->dbh->prepare($sql2);
				foreach ($values as $f => $v){
					$sth->bindValue(':' . $f, $v);
				}
				foreach ($id as $f => $v){
					if(strlen($idx)==0){
						$idx=$v;
					}
					$sth->bindValue(':' . $f."_c", $v);
				}
				if($sth->execute()){
					$arreglo+=array('id'=>$idx);
					$arreglo+=array('error'=>0);
					$arreglo+=array('terror'=>'');
					$arreglo+=array('param1'=>'');
					$arreglo+=array('param2'=>'');
					$arreglo+=array('param3'=>'');
					return json_encode($arreglo);
				}
			}
			catch(PDOException $e){
				$arreglo+=array('id'=>0);
				$arreglo+=array('error'=>1);
				$arreglo+=array('terror'=>$e->getMessage());
				return json_encode($arreglo);
			}
		}
		public function borrar($DbTableName, $key, $id){
			$arreglo=array();
			try{
				self::set_names();
				$sql="delete from $DbTableName where $key=$id";
				$sth = $this->dbh->prepare($sql);
				$a=$sth->execute();
				if($a){
					$arreglo+=array('id'=>$id);
					$arreglo+=array('error'=>0);
					$arreglo+=array('terror'=>'');
					$arreglo+=array('param1'=>'');
					$arreglo+=array('param2'=>'');
					$arreglo+=array('param3'=>'');
					return json_encode($arreglo);
				}
				else{
					$arreglo+=array('id'=>$id);
					$arreglo+=array('error'=>1);
					$arreglo+=array('terror'=>$sql.$sth->errorInfo());
					$arreglo+=array('param1'=>'');
					$arreglo+=array('param2'=>'');
					$arreglo+=array('param3'=>'');
					return json_encode($arreglo);
				}
			}
			catch(PDOException $e){
				$arreglo+=array('id'=>0);
				$arreglo+=array('error'=>1);
				$arreglo+=array('terror'=>$e->getMessage());
				return json_encode($arreglo);
			}
		}
		public function general($sql,$key=""){
			try{
				self::set_names();
				$sth = $this->dbh->prepare($sql);
				$sth->execute();
				if(strlen($key)==0){
					return $sth->fetchAll();
				}
				else{
					return $sth->fetch();
				}
			}
			catch(PDOException $e){
				return "Database access FAILED!".$e->getMessage();
			}
		}

		public function acceso2($mail, $pass){
			try{
				self::set_names();
				$sql="SELECT * FROM et_usuario where user=:correo and pass=:pass and activo=1";
				$sth = $this->dbh->prepare($sql);
				$sth->bindValue(":correo",$mail);
				$sth->bindValue(":pass",$pass);
				$sth->execute();
				$res=$sth->fetch();
				return $res;
			}
			catch(PDOException $e){
				return "Database access FAILED!".$e->getMessage();
			}
		}
		public function recuperar2($llave){
			try{
				self::set_names();
				$sql="SELECT * FROM personal where llave=:llave and autoriza=1";

				$sth = $this->dbh->prepare($sql);
				$sth->bindValue(":llave",$llave);
				$sth->execute();
				$res=$sth->fetch();
				return $res;
			}
			catch(PDOException $e){
				return "Database access FAILED!".$e->getMessage();
			}
		}

		public function permiso($idpersona){
			try{
				self::set_names();
				$sql="select * from personal_permiso where idpersona='$idpersona'";
				foreach ($this->dbh->query($sql) as $res){
					$this->accesox[]=$res;
				}
				return $this->accesox;
				$this->dbh=null;
			}
			catch(PDOException $e){
				return "Database access FAILED!".$e->getMessage();
			}
		}
		public function numero($table,$id){
			self::set_names();
			$sql = "SELECT MAX($id) + 1 FROM $table";
			$statement = $this->dbh->prepare($sql);
			$statement->execute();
			$item_id = $statement->fetchColumn();
			return $item_id;
		}
		public function mes($mes,$key=""){
			if ($mes==1){ $mes="Enero";}
			if ($mes==2){ $mes="Febrero";}
			if ($mes==3){ $mes="Marzo";}
			if ($mes==4){ $mes="Abril";}
			if ($mes==5){ $mes="Mayo";}
			if ($mes==6){ $mes="Junio";}
			if ($mes==7){ $mes="Julio";}
			if ($mes==8){ $mes="Agosto";}
			if ($mes==9){ $mes="Septiembre";}
			if ($mes==10){ $mes="Octubre";}
			if ($mes==11){ $mes="Noviembre";}
			if ($mes==12){ $mes="Diciembre";}
			if($key==1){
				$mes=substr($mes,0,3);
			}
			return $mes;
		}

		public function letranum($numero){

				if($numero>0){
				$var=($numero)*100;
				$tmp=strlen($var);
				$tmp2=substr($var,$tmp-2,2);
				$entero=substr($var,0,$tmp-2);

				$num=$entero;
				$fem = true;
				$dec = true;
				$matuni[2]  = "dos";
				$matuni[3]  = "tres";
				$matuni[4]  = "cuatro";
				$matuni[5]  = "cinco";
				$matuni[6]  = "seis";
				$matuni[7]  = "siete";
				$matuni[8]  = "ocho";
				$matuni[9]  = "nueve";
				$matuni[10] = "diez";
				$matuni[11] = "once";
				$matuni[12] = "doce";
				$matuni[13] = "trece";
				$matuni[14] = "catorce";
				$matuni[15] = "quince";
				$matuni[16] = "dieciseis";
				$matuni[17] = "diecisiete";
				$matuni[18] = "dieciocho";
				$matuni[19] = "diecinueve";
				$matuni[20] = "veinte";
				$matunisub[2] = "dos";
				$matunisub[3] = "tres";
				$matunisub[4] = "cuatro";
				$matunisub[5] = "quin";
				$matunisub[6] = "seis";
				$matunisub[7] = "sete";
				$matunisub[8] = "ocho";
				$matunisub[9] = "nove";

				$matdec[2] = "veint";
				$matdec[3] = "treinta";
				$matdec[4] = "cuarenta";
				$matdec[5] = "cincuenta";
				$matdec[6] = "sesenta";
				$matdec[7] = "setenta";
				$matdec[8] = "ochenta";
				$matdec[9] = "noventa";
				$matsub[3]  = 'mill';
				$matsub[5]  = 'bill';
				$matsub[7]  = 'mill';
				$matsub[9]  = 'trill';
				$matsub[11] = 'mill';
				$matsub[13] = 'bill';
				$matsub[15] = 'mill';
				$matmil[4]  = 'millones';
				$matmil[6]  = 'billones';
				$matmil[7]  = 'de billones';
				$matmil[8]  = 'millones de billones';
				$matmil[10] = 'trillones';
				$matmil[11] = 'de trillones';
				$matmil[12] = 'millones de trillones';
				$matmil[13] = 'de trillones';
				$matmil[14] = 'billones de trillones';
				$matmil[15] = 'de billones de trillones';
				$matmil[16] = 'millones de billones de trillones';

				$num = trim((string)@$num);
				if ($num[0] == '-') {
					$neg = 'menos ';
					$num = substr($num, 1);
				}else
					$neg = '';
				while ($num[0] == '0') $num = substr($num, 1);
				if ($num[0] < '1' or $num[0] > 9) $num = '0' . $num;
				$zeros = true;
				$punt = false;
				$ent = '';
				$fra = '';
				for ($c = 0; $c < strlen($num); $c++) {
					$n = $num[$c];
					if (! (strpos(".,'''", $n) === false)) {
						if ($punt) break;
						else{
							$punt = true;
						continue;
						}
					}elseif (! (strpos('0123456789', $n) === false)) {
						if ($punt) {
							if ($n != '0') $zeros = false;
							$fra .= $n;
						}else
						$ent .= $n;
					}else
					break;
				}
				$ent = '     ' . $ent;
				if ($dec and $fra and ! $zeros) {
					$fin = ' coma';
					for ($n = 0; $n < strlen($fra); $n++) {
						if (($s = $fra[$n]) == '0')
							$fin .= ' cero';
						elseif ($s == '1')
							$fin .= $fem ? ' un' : ' un';
						else
							$fin .= ' ' . $matuni[$s];
					}
				}else
					$fin = '';
					if ((int)$ent === 0) return 'Cero ' . $fin;
					$tex = '';
					$sub = 0;
					$mils = 0;
					$neutro = false;
					while ( ($num = substr($ent, -3)) != '   ') {
						$ent = substr($ent, 0, -3);
						if (++$sub < 3 and $fem) {
							$matuni[1] = 'un';
							$subcent = 'os';
						}else{
							$matuni[1] = $neutro ? 'un' : 'uno';
							$subcent = 'os';
						}
					$t = '';
					$n2 = substr($num, 1);

					if ($n2 == '00') {
					}elseif ($n2 < 21)
						$t = ' ' . $matuni[(int)$n2];
					elseif ($n2 < 30) {
						$n3 = $num[2];
						if ($n3 != 0) $t = 'i' . $matuni[$n3];
						$n2 = $num[1];
						$t = ' ' . $matdec[$n2] . $t;
					}else{
						$n3 = $num[2];
						if ($n3 != 0) $t = ' y ' . $matuni[$n3];
						$n2 = $num[1];
						$t = ' ' . $matdec[$n2] . $t;
					}
					$n = $num[0];
					if ($n == 1) {
						$t = ' ciento' . $t;
					}elseif ($n == 5){
						$t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t;
					}elseif ($n != 0){
						$t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t;
					}
					if ($sub == 1) {
					}elseif (! isset($matsub[$sub])) {
						if ($num == 1) {
							$t = ' mil';
						}elseif ($num > 1){
							$t .= ' mil';
						}
					}elseif ($num == 1) {
						$t .= ' ' . $matsub[$sub] . '?n';
					}elseif ($num > 1){
						$t .= ' ' . $matsub[$sub] . 'ones';
					}
					if ($num == '000') $mils ++;
					elseif ($mils != 0) {
						if (isset($matmil[$sub])) $t .= ' ' . $matmil[$sub];
						$mils = 0;
					}
					$neutro = true;
					$tex = $t . $tex;
				}
				$tex = $neg . substr($tex, 1) . $fin;
				$letra= ucfirst($tex)." pesos ".$tmp2;
				$letra =$letra."/100 M.N";
				return $letra;
			}
			else {
				return "";
			}
		}

////////////////////
		public function fondo(){
			if (isset($_REQUEST['imagen'])){$imagen=$_REQUEST['imagen'];}
			$arreglo=array('idfondo'=>$imagen);
			$x=$this->update('et_usuario',array('idusuario'=>$_SESSION['idpersona']), $arreglo);
		}
		public function leerfondo(){
			return $_SESSION['idfondo'];
		}
		public function acceso(){
			//Obtenemos los datos del formulario de acceso
			$userPOST = htmlspecialchars($_REQUEST["userAcceso"]);
			$passPOST = $_REQUEST["passAcceso"];

			$CLAVE = $this->acceso2($userPOST, $passPOST);
			if(is_array($CLAVE)){
				if($userPOST == $CLAVE['user'] and strtoupper($passPOST)==strtoupper($CLAVE['pass'])){
					$_SESSION['autoriza']=1;
					$_SESSION['nombre']=$CLAVE['nombre'];

					$_SESSION['idfondo']=$CLAVE['idfondo'];
					$_SESSION['nick']=$CLAVE['user'];
					$_SESSION['idpersona']=$CLAVE['idusuario'];
					$_SESSION['foto']=$CLAVE['file_foto'];
					$_SESSION['idtienda']=$CLAVE['idtienda'];
					$_SESSION['nivel']=$CLAVE['nivel'];

					$fecha=date("Y-m-d");
					list($anyo,$mes,$dia) = explode("-",$fecha);
					$_SESSION['n_sistema']="J&D";

					$_SESSION['cfondo']="white";
					$_SESSION['hasta']=2019;
					$_SESSION['foco']=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
					$_SESSION['cfondo']="white";

					$arreglo =array();
					$arreglo+=array('idpersonal'=>$_SESSION['idpersona']);
					$arreglo+=array('fecha'=>date("Y-m-d H:i:s"));
					$arreglo+=array('descripcion'=>"Acceso al sistema");
					$this->insert('et_usuarioreg', $arreglo);

					$arr=array();
					$arr=array('acceso'=>1,'idpersona'=>$_SESSION['idpersona']);
					return json_encode($arr);
				}
			}
			else {
				$arr=array();
				$arr=array('acceso'=>0,'idpersona'=>0);
				return json_encode($arr);	return "Usuario o Contraseña incorrecta";
			}
		}
		public function guardar_file(){
			$arreglo =array();
			$x="";
			if (isset($_REQUEST['id'])){$id=$_REQUEST['id'];}
			if (isset($_REQUEST['ruta'])){$ruta=$_REQUEST['ruta'];}
			if (isset($_REQUEST['tipo'])){$tipo=$_REQUEST['tipo'];}
			if (isset($_REQUEST['ext'])){$ext=$_REQUEST['ext'];}
			if (isset($_REQUEST['tabla'])){$tabla=$_REQUEST['tabla'];}
			if (isset($_REQUEST['campo'])){$campo=$_REQUEST['campo'];}
			if (isset($_REQUEST['direccion'])){$direccion=$_REQUEST['direccion'];}
			if (isset($_REQUEST['keyt'])){$keyt=$_REQUEST['keyt'];}
			if($tipo==1){	//////////////update
				$arreglo+=array($campo=>$direccion);
				$x=$this->update($tabla,array($keyt=>$id), $arreglo);
				rename("historial/$direccion", "$ruta/$direccion");
			}
			else{
				$arreglo+=array($campo=>$direccion);
				$arreglo+=array($keyt=>$id);
				$x=$this->insert($tabla, $arreglo);
				rename("historial/$direccion", "$ruta/$direccion");
			}
			return $x;
		}
		public function eliminar_file(){
			$arreglo =array();
			$x="";
			if (isset($_REQUEST['ruta'])){$ruta=$_REQUEST['ruta'];}
			if (isset($_REQUEST['key'])){$key=$_REQUEST['key'];}
			if (isset($_REQUEST['keyt'])){$keyt=$_REQUEST['keyt'];}
			if (isset($_REQUEST['tabla'])){$tabla=$_REQUEST['tabla'];}
			if (isset($_REQUEST['campo'])){$campo=$_REQUEST['campo'];}
			if (isset($_REQUEST['tipo'])){$tipo=$_REQUEST['tipo'];}
			if (isset($_REQUEST['borrafile'])){$borrafile=$_REQUEST['borrafile'];}

			if($borrafile==1){
				if ( file_exists($_REQUEST['ruta']) ) {
					unlink($_REQUEST['ruta']);
				}
				else{
				}
			}
			if($tipo==1){ ////////////////actualizar tabla
				$arreglo+=array($campo=>"");
				$x.=$this->update($tabla,array($keyt=>$key), $arreglo);
			}
			if($tipo==2){
				$x.=$this->borrar($tabla,$keyt,$key);
			}
			return "$x";
		}
		public function subir_file(){
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

				$n_nombre=date("YmdHis")."_".$contarx."_".rand(1,1983).".".$extension;
				$destino="historial/".$n_nombre;

				if(move_uploaded_file($key['tmp_name'],$destino)){
					chmod($destino,0666);
					$arr[$contarx] = array("archivo" => $n_nombre);
				}
				else{

				}
				$contarx++;
			}
			$myJSON = json_encode($arr);
			return $myJSON;
		}
		public function recuperar(){
			$x="";
			require 'librerias15/PHPMailer-5.2-stable/PHPMailerAutoload.php';

			$telefono="";
			if (isset($_REQUEST['telefono'])){$texto=$_REQUEST['telefono'];}
			$sql="select * from personal where correo='$texto'";
			$res=$this->general($sql);
			if(count($res)>0){

				if(strlen($res[0]['correo'])>0){
					$x.=$res[0]['correo'];
					$mail = new PHPMailer;
					$mail->isSMTP();                                      // Set mailer to use SMTP
					$mail->Host = "smtp.gmail.com";						  // Specify main and backup SMTP servers
					$mail->SMTPAuth = true;                               // Enable SMTP authentication
					$mail->Username = "sistema.subsaludpublicahgo@gmail.com";       // SMTP username
					$mail->Password = "TEUFEL123";                       // SMTP password
					$mail->SMTPSecure = "ssl";                            // Enable TLS encryption, `ssl` also accepted
					$mail->Port = 465;                                    // TCP port to connect to
					$mail->CharSet = 'UTF-8';

					$mail->From = "sistema.subsaludpublicahgo@gmail.com";
					$mail->FromName = "Sistema Administrativo de Salud Pública";
					$mail->Subject = "Recuperar contraseña";
					$mail->AltBody = "Contraseña";
					$mail->addAddress($res[0]['correo']);     // Add a recipient
					$mail->addCC('omargg83@gmail.com');

					// $mail->addAddress('ellen@example.com');               // Name is optional
					// $mail->addReplyTo('info@example.com', 'Information');
					// $mail->addCC('cc@example.com');
					// $mail->addBCC('bcc@example.com');

					//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
					//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
					$mail->isHTML(true);                                  // Set email format to HTML

					$numero="pass_".rand(1,6666666);
					$arreglo=array("llave"=>$numero);
					$this->update('personal',array('idpersona'=>$res[0]['idpersona']), $arreglo);

					$texto="Recuperar contraseña <b>Contraseña! $x</b>";
					$texto.="<br><a href='http://spublicahgo.ddns.net/acceso/index.php?llave=$numero'>De click para recuperar la contraseña</a>";
					$mail->Body    = $texto;
					$mail->AltBody = "Recuperar contraseña";

					if(!$mail->send()) {
					    $x.= 'Message could not be sent.';
					    $x.= 'Mailer Error: ' . $mail->ErrorInfo;
					} else {
					   	$x= 'Se envío enlace a su correo';
					}
					return $x;
				}
				else{
					return "no tiene correo registrado en la plantilla";
				}

			}
			else{
				return 0;
			}
		}
		public function password_cambia(){
			if (isset($_REQUEST['usuario'])){$usuario=$_REQUEST['usuario'];}
			if (isset($_REQUEST['pass1'])){$pass1=trim($_REQUEST['pass1']);}
			if (isset($_REQUEST['pass2'])){$pass2=trim($_REQUEST['pass2']);}

			if($pass1==$pass2){
				$arreglo=array('pass'=>$pass1);
				$x=$this->update('personal',array('llave'=>$usuario), $arreglo);
				return 1;
			}
			else{
				return "no coincide";
			}
		}
		public function fondo_carga(){
			$x="";
			$directory="fondo/";
			$dirint = dir($directory);
			$x.= "<ul class='nav navbar-nav navbar-right'>";
				$x.= "<li class='nav-item dropdown'>";
					$x.= "<a class='nav-link dropdown-toggle text-white' href='#' id='navbarDropdown' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'><i class='fas fa-desktop'></i>Fondos</a>";
					$x.= "<div class='dropdown-menu' aria-labelledby='navbarDropdown' style='width: 200px;max-height: 400px !important;overflow: scroll;overflow-x: scroll;overflow-x: hidden;'>";
						while (($archivo = $dirint->read()) !== false){
							if ($archivo != "." && $archivo != ".." && $archivo != "" && substr($archivo,-4)==".jpg"){
								$x.= "<a class='dropdown-item' href='#' id='fondocambia' title='Click para aplicar el fondo'><img src='$directory".$archivo."' alt='Fondo' class='rounded' style='width:140px;height:80px'></a>";
							}
						}
					$x.= "</div>";
				$x.= "</li>";
			$x.= "</ul>";
			$dirint->close();

			return $x;
		}

		public function cantidad_update($id){
			try{
				$sql="select sum(cantidad) as total from bodega where idproducto=$id";
				$sth = $db->dbh->prepare($sql);
				$sth->execute();
				$total=$sth->fetch(PDO::FETCH_OBJ);
				$existencia=$total->total;

				$arreglo =array();
				$arreglo = array('cantidad'=>$existencia);
				$db->update('productos',array('id'=>$id), $arreglo);
			}
			catch(PDOException $e){
				return "Database access FAILED!".$e->getMessage();
			}
		}

	}

	if(strlen($ctrl)>0){
		$db = new Sagyc();
		if(strlen($function)>0){
			echo $db->$function();
		}
	}


	function moneda($valor){
		return "$ ".number_format( $valor, 2, "." , "," );
	}
	function fecha($fecha,$key=""){
		$fecha = new DateTime($fecha);
		if($key==1){
			$mes=$fecha->format('m');
			if ($mes==1){ $mes="Enero";}
			if ($mes==2){ $mes="Febrero";}
			if ($mes==3){ $mes="Marzo";}
			if ($mes==4){ $mes="Abril";}
			if ($mes==5){ $mes="Mayo";}
			if ($mes==6){ $mes="Junio";}
			if ($mes==7){ $mes="Julio";}
			if ($mes==8){ $mes="Agosto";}
			if ($mes==9){ $mes="Septiembre";}
			if ($mes==10){ $mes="Octubre";}
			if ($mes==11){ $mes="Noviembre";}
			if ($mes==12){ $mes="Diciembre";}

			return $fecha->format('d')." de $mes de ".$fecha->format('Y');
		}
		if($key==2){
			return $fecha->format('d-m-Y H:i:s');
		}
		else{
			return $fecha->format('d-m-Y');
		}
	}
?>
