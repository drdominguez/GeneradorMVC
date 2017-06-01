<?php
    echo "Iniciando creación de vistas...";//Mostramos mensaje para saber cuando empieza
function conectarBD(){//Creamos una funcion para conectarnos a la BD

    $bd = new mysqli("localhost", "iu2016", "iu2016", "IU2016");
    if (mysqli_connect_errno()){
        echo "Fallo al conectar MySQL: " . $mysqli->connect_error();
    }
    return $bd;
}


function listarTablas() //Creamos una funcion para que nos devuelva todas las tablas de la BD
{
    $mysqli2 = conectarBD();
    $sql = 'show full tables from IU2016';
    if (!($resultado = $mysqli2->query($sql))) {
        return 'Error en la consulta sobre la base de datos';
    } else {
        $tables = array();
        while($tabla = $resultado->fetch_array(MYSQLI_ASSOC)){
            array_push($tables,$tabla['Tables_in_IU2016']);
        }
        return $tables;
    }
}

$arrayTablas = listarTablas();//Llamamos a la funcion listarTablas() para que nos devuelva todas las tablas. Le llamamos $arrayTablas
foreach($arrayTablas as $tabla){//Recorremos el array con las vistas
   //Llamamos a la funcion crear vistas
    crearADD($tabla);
    crearSHOWALL($tabla);
  /*  crearSEARCH($tabla);
    crearEDIT($tabla);
    crearDELETE($tabla);
    
    crearSHOWCURRENT($tabla);*/
}
echo "Vistas creadas";

function crearADD($tabla){
    echo "Creando vista ADD ' . $tabla . '...\n";
    $file=fopen("/var/www/html/GeneradorPag/IUjulio/Views/" . strtoupper($tabla) . "_ADD_Vista.php","w+");
    $atributos = listarAtributos($tabla);//Cogemos los atributos de la tabla y los pasamos a un array
   // exit;
    $str='<?php
     class '. strtoupper($tabla) . '_ADD { 
          function __construct(){ 
                $this->render();
          }
                
    function render(){ 
    
?>
	<head><link rel="stylesheet" href="../Styles/styles.css" type="text/css" media="screen" />
		<script type="text/javascript" src="../js/<?php  echo $_SESSION[\'IDIOMA\']?>_validate.js"></script></head>
	<div>
		<p>
			<h2>
<?php
    
    include \'../Locates/Strings_\'.$_SESSION[\'IDIOMA\'].\'.php\';           
        include \'../Functions/' . strtoupper($tabla) . '_DefForm.php\';
        $lista = array(';
        $i=0;
       foreach($atributos as $valor){
           if($i==0){
               $str .= '\'' . $valor->name.'\'';
           }else{
               $str .= ',\'' . $valor->name. '\'';
           }
           $i++;
        }
    $str .= ');

?>
     <title>Añadir</title>
    	</h2>
		</p>
		<p>
			<h1>
			<span class=\"form-title\">
<?php           echo $strings[\'Insertar ' . strtoupper($tabla) . '\'] ?><br>
			</h1>
            <h3>
				<form id="form" name="form" action=\'../Controllers/' . strtoupper($tabla) . '_Controller.php?\' method=\'post\'>
                     <ul class="form-style-1">
<?php
                    createForm($lista,$DefForm,$strings,\'\',true,false);
   
?>
                    <input type=\'submit\' name=\'accion\' onclick="return valida_envia4()" value=<?php
                        echo $strings[\'Continuar\'] ?>>
				    </form>
<?php
				echo \'<a class="form-link" href=\\\'' . strtoupper($tabla) . '_Controller.php\\\'>\'. $strings[\'Volver\'] . \' </a>\';
?>
				<br>

			</h3>
		</p>

	</div>

<?php
} //fin metodo render

}
?>';

    fwrite($file,$str);
    
    crearArrayFormulario($tabla,$atributos);//Llamamos a la funcion crear el array del formulario, y le pasamos la tabla y los atributos
}



function crearSHOWALL($tabla){
 echo "Creando vista SHOWALL ' . $tabla . '...\n";
 $file=fopen("/var/www/html/GeneradorPag/IUjulio/Views/" . strtoupper($tabla) . "_SHOW_ALL_Vista.php","w+");
 $atributos = listarAtributos($tabla);//Cogemos los atributos de la tabla y los pasamos a un array
 $str='<?php
     class '. strtoupper($tabla) . '_DEFAULT { 
        function __construct($array, $volver){
            $this->datos = $array;
            $this->volver = $volver;
            $this->render();
        }
                
    function render(){ 
    
?>
    <head><link rel="stylesheet" href="../Styles/styles.css" type="text/css" media="screen" /></head>
            <p>
                <h2>
                    <?php


                    include \'../Locates/Strings_\'.$_SESSION[\'IDIOMA\'].\'.php\';


                    ?>
                    <div>
                        <?php

                        $lista = array(';
                        $i=0;
       foreach($atributos as $valor){
           if($i==0){
               $str .= '\'' . $valor->name.'\'';
           }else{
               $str .= ',\'' . $valor->name. '\'';
           }
           $i++;
        }
    $str .= ');

?>
    <head>
        <link rel="stylesheet" href="../Styles/styles.css" type="text/css" media="screen" />
        <link rel="stylesheet" type="text/css" href="../Styles/print.css" media="print" />
    </head>
        <div id="wrapper">

            <nav>

        <div class="menu">


    <ul>
        <li><a href="../Functions/Desconectar.php"><?php echo  $strings[\'Cerrar Sesión\']; ?></a></li>
        <li><?php echo $strings[\'Usuario\'].": ". $_SESSION[\'login\']; ?></li>

    </ul>

        <?php echo \'<a href=\\\'\' . $this->volver . "\'>" . $strings[\'Volver\'] . " </a>"; ?></li>
        <a href=\'./' . strtoupper($tabla) . '_Controller.php?accion=<?php echo $strings[\'Consultar\']?>\'><?php echo $strings[\'Consultar\']?></a>
        <a href=\'./' . strtoupper($tabla) . '_Controller.php?accion=<?php echo $strings[\'Insertar\']?>\'><?php echo $strings[\'Insertar\']?></a>
        <a href=\'./' . strtoupper($tabla) . '_Controller.php?accion=<?php echo $strings[\'CONSULTAR BORRADO\']?>\'><?php echo $strings[\'CONSULTAR BORRADO\']?></a>

        </div>
            </nav>
                <table id="btable" border = 1>
                    <tr>
<?php
    foreach($lista as $titulo){

        echo "<th>";

?>
<?php
        echo $strings[$titulo];
?>
        </th>
<?php
        }
?>
        </tr>
<?php
    for ($j=0;$j<count($this->datos);$j++){
        echo "<tr>";
         foreach ($this->datos[$j] as $clave => $valor) {
            for ($i = 0; $i < count($lista); $i++) {
                if ($clave === $lista[$i]) {
?>

<?php

                    echo "<td>";


                    echo $valor;

                    echo "</td>";
                }
            }
        }
?>

        <td>
            <a href=\''. strtoupper($tabla) . '_Controller.php?ACTIVIDAD_NOMBRE=<?php echo $this->datos[$j][\'ACTIVIDAD_NOMBRE\'] . \'&accion=\'.$strings[\'Modificar\']; ?>\'><?php echo $strings[\'Modificar\'] ?></a>
        </td>
        <td>
            <a href=\''. strtoupper($tabla) .'_Controller.php?ACTIVIDAD_NOMBRE=<?php echo $this->datos[$j][\'ACTIVIDAD_NOMBRE\'] . \'&accion=\'.$strings[\'Borrar\']; ?>\'><?php echo $strings[\'Borrar\'] ?></a>
        </td>

        <?php

            echo "<tr>";

    }
?>

        </table>

        </div>
                    <h3>
            <p>
                <?php
                echo \'<a class="form-link" href=\\\'\' . $this->volver . "\'>" . $strings[\'Volver\'] . " </a>";
                ?>
                </h3>
            </p>

        </div>

        <?php
    } //fin metodo render

}';
    fwrite($file,$str);
    
    crearArrayFormulario($tabla,$atributos);//Llamamos a la funcion crear el array del formulario, y le pasamos la tabla y los atributos



}

function crearArrayFormulario($tabla, $atributos){
    echo "Creando formulario ' . $tabla . '\n";

    $file = fopen("/var/www/html/GeneradorPag/IUjulio/Functions/" . strtoupper($tabla) . "_DefForm.php","w+");
        $str = '
        <?php
        

        //Formulario para cada vista.
        $Form = array(' ;
        $i=0;
            foreach ($atributos as $clave) {
                if($i==0) {
                    $str .='
                   '.$i . '=>array(
                   \'name\' => \'' . $clave->name . '\',
                   \'type\' => \'' . calcularType($clave->type) . '\', 
                   \'value\' => \'\',
                   \'min\' => \'\',
                   \'max\' => \'\',
                   \'size\' => \'' . $clave->length . '\',
                   \'required\' => \'true\',
                   \'pattern\' => \'\',
                   \'validation\' => \'\',
                   \'readonly\' => \'false\'  ';
                   $str.= '
                   )';
                }else{
                    $str .=',
                   '.$i.'=>array(
                   \'name\' => \'' . $clave->name . '\',
                   \'type\' => \'' . calcularType($clave->type) . '\', 
                   \'value\' => \'\',
                   \'min\' => \'\',
                   \'max\' => \'\',
                    \'size\' => \'' . $clave->length . '\',
                   \'required\' => \'true\',
                   \'pattern\' => \'\',
                   \'validation\' => \'\',
                   \'readonly\' => \'false\'  ';
                   $str.= '
                   )';
                }
                   $i++;
                }
                $str.='
                );

                $DefForm=$Form;';


            fwrite($file,$str);



}
//*****************************************************************Faltan por añadir*******************************************************************************************
function calcularType($tipo){
    switch ($tipo){
        case 1:
            $toret='number';
            break;
        case 3:
            $toret='number';
            break;
        case 10:
            $toret='date';
            break;
        case 11:
            $toret='time';
            break;
        case 247:
            $toret='select';
            break;
        case 253:
            $toret='text';
            break;
        case 252:
            $toret='text';
            break;
        default:
            $toret='';
    }
    return $toret;
}


function listarAtributos($tabla){
    $mysqli2 = conectarBD();
    $sql = 'SELECT * FROM ' . $tabla . ';';

    if (!($resultado = $mysqli2->query($sql))) {
        return 'Error en la consulta sobre la base de datos';
    } else {
        $finfo = mysqli_fetch_fields($resultado);


        return $finfo;
    }

}


function createForm($listFields, $fieldsDef, $strings, $values, $required, $noedit) {

    foreach ($listFields as $field) { //miro todos los campos que me piden en su orden
        for ($i = 0; $i < count($fieldsDef); $i++) { //recorro todos los campos de la definición de formulario para encontrarlo
            //echo $field . ':' . $fieldsDef[$i]['required'] . '<br>';
            if ($field == $fieldsDef[$i]['name'] || $field == $fieldsDef[$i]['value']) { //si es el que busco
                switch ($fieldsDef[$i]['type']) {
                    case 'text':
                        if (isset($fieldsDef[$i]['texto'])) {
                            $str = "<li>" . $strings[$fieldsDef[$i]['texto']];
                        } else {
                            $str = "<li><label>" . $strings[$fieldsDef[$i]['name']] . "</label>";
                        }
                        $str .= "<input type = '" . $fieldsDef[$i]['type'] . "'";
                        $str .= " name = '" . $fieldsDef[$i]['name'] . "'";
                        $str .= " id = '" . $fieldsDef[$i]['name'] . "'";
                        $str .= " size = '" . $fieldsDef[$i]['size'] . "'";
                        if (isset($values[$fieldsDef[$i]['name']])) {
                            $str .= " value = '" . $values[$fieldsDef[$i]['name']] . "'";
                        } else {
                            $str .= " value = '" . $fieldsDef[$i]['value'] . "'";
                        }
                        if ($fieldsDef[$i]['pattern'] <> '') {
                            $str .= " pattern = '" . $fieldsDef[$i]['pattern'] . "'";
                        }
                        if ($fieldsDef[$i]['validation'] <> '') {
                            $str .= " " . $fieldsDef[$i]['validation'];
                        }
                        if (is_bool($required)) {
                            if (!$required) {
                                $str .= ' ';
                            } else {
                                $str .= ' required ';
                            }
                        } else {
                            if (!isset($required[$field])) {
                                $str .= 'required';
                            } else {
                                $str .= '';
                            }
                        }
                        if (is_bool($noedit)) {
                            if ($noedit) {
                                $str .= ' readonly ';
                            }
                        } else {
                            if (isset($noedit[$field])) {
                                if ($noedit[$field]) {
                                    $str .= ' readonly ';
                                }
                            }
                        }
                        $str .= " ></li>";
                        echo $str;
                        break;
                    case 'date':
                        $str = "<li><label>" . $strings[$fieldsDef[$i]['name']] . "</label>";
                        $str .= "<input type = '" . $fieldsDef[$i]['type'] . "'";
                        $str .= " name = '" . $fieldsDef[$i]['name'] . "'";
                        $str .= " min = '" . $fieldsDef[$i]['min'] . "'";
                        $str .= " max = '" . $fieldsDef[$i]['max'] . "'";
                        if (isset($values[$fieldsDef[$i]['name']])) {
                            $str .= " value = '" . ($values[$fieldsDef[$i]['name']]) . "'";
                        } else {
                            $str .= " value = '" . $fieldsDef[$i]['value'] . "'";
                        }
                        if ($fieldsDef[$i]['pattern'] <> '') {
                            $str .= " pattern = '" . $fieldsDef[$i]['pattern'] . "'";
                        }
                        if ($fieldsDef[$i]['validation'] <> '') {
                            $str .= " " . $fieldsDef[$i]['validation'];
                        }
                        if (is_bool($required)) {
                            if (!$required) {
                                $str .= ' ';
                            } else {
                                $str .= ' required ';
                            }
                        } else {
                            if (isset($required[$field])) {
                                if (!$required[$field]) {
                                    $str .= ' ';
                                } else {
                                    $str -= ' required ';
                                }
                            }
                        }
                        if (is_bool($noedit)) {
                            if ($noedit) {
                                $str .= ' readonly ';
                            }
                        } else {
                            if (isset($noedit[$field])) {
                                if ($noedit[$field]) {
                                    $str .= ' readonly ';
                                }
                            }
                        }
                if($fieldsDef[$i]['name']!=='BLOQUE_FECHA'){
                        $str .= "required" . " ></li>";
                }else{
             $str.=" ></li>";
                }
                        echo $str;
                        break;
                    case 'email':
                        $str = "<li><label>" . $strings[$fieldsDef[$i]['name']] . "</label>";
                        $str .= "<input type = '" . $fieldsDef[$i]['type'] . "'";
                        $str .= " name = '" . $fieldsDef[$i]['name'] . "'";
                        $str .= " size = '" . $fieldsDef[$i]['size'] . "'";
                        if (isset($values[$fieldsDef[$i]['name']])) {
                            $str .= " value = '" . $values[$fieldsDef[$i]['name']] . "'";
                        } else {
                            $str .= " value = '" . $fieldsDef[$i]['value'] . "'";
                        }
                        if ($fieldsDef[$i]['pattern'] <> '') {
                            $str .= " pattern = '" . $fieldsDef[$i]['pattern'] . "'";
                        }
                        if ($fieldsDef[$i]['validation'] <> '') {
                            $str .= " " . $fieldsDef[$i]['validation'];
                        }
                        if (is_bool($required)) {
                            if (!$required) {
                                $str .= ' ';
                            } else {
                                $str .= ' required ';
                            }
                        } else {
                            if (isset($required[$field])) {
                                if (!$required[$field]) {
                                    $str .= ' ';
                                } else {
                                    $str -= ' required ';
                                }
                            }
                        }
                        if (is_bool($noedit)) {
                            if ($noedit) {
                                $str .= ' readonly ';
                            }
                        } else {
                            if (isset($noedit[$field])) {
                                if ($noedit[$field]) {
                                    $str .= ' readonly ';
                                }
                            }
                        }
                        $str .= "required" . " ></li>";
                        echo $str;
                        break;
                    case 'time':
                        $str = "<li><label>" . $strings[$fieldsDef[$i]['name']] . "</label>";
                        $str .= "<input type = '" . $fieldsDef[$i]['type'] . "'";
                        $str .= " name = '" . $fieldsDef[$i]['name'] . "'";
                        if (isset($values[$fieldsDef[$i]['name']])) {
                            $str .= " value = '" . ($values[$fieldsDef[$i]['name']]) . "'";
                        } else {
                            $str .= " value = '" . $fieldsDef[$i]['value'] . "'";
                        }
                        if ($fieldsDef[$i]['pattern'] <> '') {
                            $str .= " pattern = '" . $fieldsDef[$i]['pattern'] . "'";
                        }
                        if ($fieldsDef[$i]['validation'] <> '') {
                            $str .= " " . $fieldsDef[$i]['validation'];
                        }
                        if (is_bool($required)) {
                            if (!$required) {
                                $str .= ' ';
                            } else {
                                $str .= ' required ';
                            }
                        } else {
                            if (isset($required[$field])) {
                                if (!$required[$field]) {
                                    $str .= ' ';
                                } else {
                                    $str -= ' required ';
                                }
                            }
                        }
                        if (is_bool($noedit)) {
                            if ($noedit) {
                                $str .= ' readonly ';
                            }
                        } else {
                            if (isset($noedit[$field])) {
                                if ($noedit[$field]) {
                                    $str .= ' readonly ';
                                }
                            }
                        }
                        $str .= "required" . " ></li>";
                        echo $str;
                        break;
                    case 'url':

                        if (isset($values[$fieldsDef[$i]['name']])) {
                            $str = "<li><label>" . $strings[$fieldsDef[$i]['name']] . "</label>";
                            $str .= "<a target='_blank' href='" . $values[$fieldsDef[$i]['name']] . "'>Ver</a>";
                            $str .= " <br>\n";
                            echo $str;
                        }
                        break;
                    case 'tel':
                        break;
                    case 'password':
                        $str = "<li><label>" . $strings[$fieldsDef[$i]['name']] . "</label>";
                        $str .= "<input type = '" . $fieldsDef[$i]['type'] . "'";
                        $str .= " name = '" . $fieldsDef[$i]['name'] . "'";
                        $str .= " size = '" . $fieldsDef[$i]['size'] . "'";
                        if (isset($values[$fieldsDef[$i]['name']])) {
                            $str .= " value = '" . $values[$fieldsDef[$i]['name']] . "'";
                        } else {
                            $str .= " value = '" . $fieldsDef[$i]['value'] . "'";
                        }
                        if ($fieldsDef[$i]['pattern'] <> '') {
                            $str .= " pattern = '" . $fieldsDef[$i]['pattern'] . "'";
                        }
                        if ($fieldsDef[$i]['validation'] <> '') {
                            $str .= " " . $fieldsDef[$i]['validation'];
                        }
                        if (is_bool($required)) {
                            if (!$required) {
                                $str .= ' ';
                            } else {
                                $str .= ' required ';
                            }
                        } else {
                            if (isset($required[$field])) {
                                if (!$required[$field]) {
                                    $str .= ' ';
                                } else {
                                    $str -= ' required ';
                                }
                            }
                        }
                        if (is_bool($noedit)) {
                            if ($noedit) {
                                $str .= ' readonly ';
                            }
                        } else {
                            if (isset($noedit[$field])) {
                                if ($noedit[$field]) {
                                    $str .= ' readonly ';
                                }
                            }
                        }
                        $str .= "required" . " ></li>";
                        echo $str;
                        break;
                    case 'number':
                        $str = "<li><label>" . $strings[$fieldsDef[$i]['name']] . "</label>";
                        $str .= "<input type = '" . $fieldsDef[$i]['type'] . "'";
                        $str .= " name = '" . $fieldsDef[$i]['name'] . "'";
                        $str .= " min = '" . $fieldsDef[$i]['min'] . "'";
                        $str .= " max = '" . $fieldsDef[$i]['max'] . "'";
                        if (isset($values[$fieldsDef[$i]['name']])) {
                            $str .= " value = '" . $values[$fieldsDef[$i]['name']] . "'";
                        } else {
                            $str .= " value = '" . $fieldsDef[$i]['value'] . "'";
                        }
                        if ($fieldsDef[$i]['pattern'] <> '') {
                            $str .= " pattern = '" . $fieldsDef[$i]['pattern'] . "'";
                        }
                        if ($fieldsDef[$i]['validation'] <> '') {
                            $str .= " " . $fieldsDef[$i]['validation'];
                        }
                        if (is_bool($required)) {
                            if (!$required) {
                                $str .= ' ';
                            } else {
                                $str .= ' required ';
                            }
                        } else {
                            if (isset($required[$field])) {
                                if (!$required[$field]) {
                                    $str .= ' ';
                                } else {
                                    $str -= ' required ';
                                }
                            }
                        }
                        if (is_bool($noedit)) {
                            if ($noedit) {
                                $str .= ' readonly ';
                            }
                        } else {
                            if (isset($noedit[$field])) {
                                if ($noedit[$field]) {
                                    $str .= ' readonly ';
                                }
                            }
                        }
                        $str .= " ></li>";
                        echo $str;
                        break;
                    case 'checkbox':

                        if (isset($strings[$fieldsDef[$i]['value']])) {
                            $str = "<li><label>" . $strings[$fieldsDef[$i]['value']] . "</label>";
                        } else {
                            $str = "<li><label>" . $fieldsDef[$i]['value'] . "</label>";
                        }
                        $str .= "<input type = '" . $fieldsDef[$i]['type'] . "'";
                        $str .= " name = '" . $fieldsDef[$i]['name'] . "'";
                        $str .= " size = '" . $fieldsDef[$i]['size'] . "'";
                        if (isset($values[$fieldsDef[$i]['name']])) {
                            $str .= " value = '" . $values[$fieldsDef[$i]['name']] . "'";
                        } else {
                            $str .= " value = '" . $fieldsDef[$i]['value'] . "'";
                        }
                        if ($fieldsDef[$i]['pattern'] <> '') {
                            $str .= " pattern = '" . $fieldsDef[$i]['pattern'] . "'";
                        }
                        if ($fieldsDef[$i]['validation'] <> '') {
                            $str .= " " . $fieldsDef[$i]['validation'];
                        }
                        if (is_bool($noedit)) {
                            if ($noedit) {
                                $str .= ' readonly ';
                            }
                        } else {
                            if (isset($noedit[$field])) {
                                if ($noedit[$field]) {
                                    $str .= ' readonly ';
                                }
                            }
                        }
                        $str .= " ></li>";
                        echo $str;
                        break;
                    case 'radio':
                        break;
                    case 'file':
                        $str = "<li><label>" . $strings[$fieldsDef[$i]['name']] . "</label>";
                        $str .= "<input type = '" . $fieldsDef[$i]['type'] . "'";
                        $str .= " name = '" . $fieldsDef[$i]['name'] . "'";
                        if (isset($values[$fieldsDef[$i]['name']])) {
                            $str .= " value = '" . $values[$fieldsDef[$i]['name']] . "'";
                        } else {
                            $str .= " value = '" . $fieldsDef[$i]['value'] . "'";
                        }
                        if ($fieldsDef[$i]['pattern'] <> '') {
                            $str .= " pattern = '" . $fieldsDef[$i]['pattern'] . "'";
                        }
                        if ($fieldsDef[$i]['validation'] <> '') {
                            $str .= " " . $fieldsDef[$i]['validation'];
                        }
                        if (is_bool($required)) {
                            if (!$required) {
                                $str .= ' ';
                            } else {
                                $str .= ' required ';
                            }
                        } else {
                            if (isset($required[$field])) {
                                if (!$required[$field]) {
                                    $str .= ' ';
                                } else {
                                    $str -= ' required ';
                                }
                            }
                        }
                        if (is_bool($noedit)) {
                            if ($noedit) {
                                $str .= ' readonly ';
                            }
                        } else {
                            if (isset($noedit[$field])) {
                                if ($noedit[$field]) {
                                    $str .= ' readonly ';
                                }
                            }
                        }
                        $str .= " ></li>";
                        echo $str;
                        break;
                        ;
                    case 'select':
                        $str = "<li><label>" . $strings[$fieldsDef[$i]['name']] . "</label>" . "<select name='" . $fieldsDef[$i]['name'] . "'";
                        if ($noedit || $noedit[$field]) {
                            $str .= ' readonly ';
                        }
                        if ($fieldsDef[$i]['multiple'] == 'true') {
                            $str = $str . " multiple ";
                        }
                        $str = $str . " >";
                        foreach ($fieldsDef[$i]['options'] as $value) {
                            $str1 = "<option value = '" . $value . "'";
                            if (isset($values[$fieldsDef[$i]['name']])) {
                                if ($values[$fieldsDef[$i]['name']] == $value) {
                                    $str1 .= " selected ";
                                }
                            }
                            $str1 .= ">" . $value . "</option>";
                            $str = $str . $str1;
                        }
                        $str = $str . "</select></li>";
                        echo $str;
                        break;
                    case 'textarea':
                        break;
                    default:
                }
            }
        }
    }
}
?>
