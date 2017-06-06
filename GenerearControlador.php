<?php
echo "Iniciando creador de controlador " . $tabla . "...";
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

function crearControlador($tabla) {

    $atributos = listarAtributos($tabla);//Cogemos los atributos de la tabla y los pasamos a un array
    $file=fopen("/var/www/html/GeneradorPag/IUjulio/Controllers/" . strtoupper($tabla) . "_Controller.php","w+");
    $str='<?php

include \'../Models/' . strtoupper($tabla) . '_Model.php\';
include \'../Locates/Strings_Castellano.php\';
include \'../Functions/LibraryFunctions.php\';
include \'../Views/MENSAJE_Vista.php\';

if (!IsAuthenticated()){
    header(\'Location:../index.php\');
}
include \'../Locates/Strings_\'.$_SESSION[\'IDIOMA\'].\'.php\';

//Genera los includes
$includes=generarIncludes();
for ($z=0;$z<count($includes);$z++){
    include $includes[$z];
}

include \'../Views/' . strtoupper($tabla) . '_SHOW_CURRENT_Vista.php\';
include \'../Views/' . strtoupper($tabla) . '_SEARCH_Vista.php\';

function get_data_form(){

//Recoge la información del formulario
';

    foreach ($atributos as $valor) {
        $str.= 'if(isset($_REQUEST[\'' . $valor . '\'])){
                $' . $valor . ' = $_REQUEST[\'' . $valor . '\'];
         }else{
                $' . $valor . '=null;
         }';

    }

    $str.='$accion = $_REQUEST[\'accion\'];

    $' . $tabla .' = new '. $tabla .'(';
    $i=0;
    foreach ($atributos as $valor) {
        if($i==0) {
            $str.= '$' . $valor .'';
        }else{
            $str.= ',$' . $valor .'';
        }
    }
    $str.=');

    return ' . $tabla . ';
}

if (!isset($_REQUEST[\'accion\'])){
    $_REQUEST[\'accion\'] = \'\';
}
    ';
    $clave=obtenerClave($tabla);

    $str.='
    Switch ($_REQUEST[\'accion\']) {
        case $strings[\'Continuar\']:
        case $strings[\'Insertar\']: 
            if (!isset($_REQUEST[\''. $clave .'\'])) {

                    if (!tienePermisos(\''. $tabla .'_Add\')) {
                        new Mensaje(\'No tienes los permisos necesarios\', \''. $tabla .'_Controller.php\');
                    } else {
                        new '. $tabla .'_ADD();
                    }

            } else {

                if (!isset($_REQUEST[\'ACTIVIDAD_BLOQUE\'])) {
                    $actividad = get_data_form();

                    new Actividad_Add_Horas($actividad);
                } else {
                    $actividad = get_data_form();

                    $respuesta = $actividad->insert_actividad();
                    new Mensaje($respuesta, \'ACTIVIDAD_Controller.php\');
                }

            }
            break;
        case $strings[\'Borrar\']: //Borrado de actividades
            if (!isset($_REQUEST[\'ACTIVIDAD_ID\'])) {
                $actividad = new actividad( $_REQUEST[\'ACTIVIDAD_NOMBRE\'], \'\', \'\',\'\',\'\',\'\',\'\',null,\'\',\'\');
                $valores = $actividad->RellenaDatos();
                if (!tienePermisos(\'Actividad_Delete\')) {
                    new Mensaje(\'No tienes los permisos necesarios\', \'ACTIVIDAD_Controller.php\');
                } else {
                    new Actividad_Delete($valores, \'ACTIVIDAD_Controller.php\');
                }
            } else {


                $actividad = get_data_form();
                $respuesta = $actividad->delete_actividad();
                new Mensaje($respuesta, \'ACTIVIDAD_Controller.php\');
            }
            break;
        case $strings[\'Ver\']: 
            
                $actividad = new actividad( $_REQUEST[\'ACTIVIDAD_NOMBRE\'], \'\', \'\',\'\',\'\',\'\',\'\',null,\'\',\'\');
                $valores = $actividad->RellenaDatos();
                if (!tienePermisos(\'Actividad_Delete\')) {
                    new Mensaje(\'No tienes los permisos necesarios\', \'ACTIVIDAD_Controller.php\');
                } else {
                    new ACTIVIDAD_show_current($valores, \'ACTIVIDAD_Controller.php\');
                }
            
            break;
        case $strings[\'Modificar\']: //Modificación de actividades

            if (!isset($_REQUEST[\'ACTIVIDAD_ID\'])) {

                $actividad = new actividad( $_REQUEST[\'ACTIVIDAD_NOMBRE\'], \'\', \'\',\'\',\'\',\'\',\'\',null,\'\',\'\');
                $valores = $actividad->RellenaDatos();
                $valores2 = $actividad->RellenaDatosCalendarioActividad();
                if (!tienePermisos(\'ACTIVIDAD_Edit\')) {
                    new Mensaje(\'No tienes los permisos necesarios\', \'ACTIVIDAD_Controller.php\');
                } else {
                    new Actividad_Edit($valores, $valores2, \'ACTIVIDAD_Controller.php\');
                }
            } else {
                

                $actividad = get_data_form();

                $respuesta = $actividad->update_actividad($_REQUEST[\'ACTIVIDAD_ID\']);
                new Mensaje($respuesta, \'ACTIVIDAD_Controller.php\');

            }
            break;
        case $strings[\'Consultar\']: //Consulta de actividades
            if (!isset($_REQUEST[\'ACTIVIDAD_NOMBRE\'])) {
                new ACTIVIDAD_Show();
            } else {
                $actividad = get_data_form();
                $datos = $actividad->select_actividad();
                if (!tienePermisos(\'ACTIVIDAD_Show\')) {
                    new Mensaje(\'No tienes los permisos necesarios\', \'ACTIVIDAD_Controller.php\');
                } else {

                    new Actividad_default($datos, \'ACTIVIDAD_Controller.php\');
                }
            }
            break;
        case $strings[\'CONSULTAR BORRADO\']: //Consulta de actividades ocultas
            if (!isset($_REQUEST[\'ACTIVIDAD_NOMBRE\'])) {
                $actividad = new actividad(\'\', \'\',\'\',\'\',\'\',\'\',\'\',null,\'\',\'\');
            } else {
                $actividad = get_data_form();
            }
            
            $datos = $actividad->ConsultarBorradas();
            
            if (!tienePermisos(\'Actividad_default_borradas\')) {
                new Mensaje(\'No tienes los permisos necesarios\', \'ACTIVIDAD_Controller.php\');
            } else {
                new Actividad_default_borradas($datos, \'ACTIVIDAD_Controller.php\');
            }
            break;
        default:
            //La vista por defecto lista todas las actividades
            if (!isset($_REQUEST[\'ACTIVIDAD_NOMBRE\'])) {
                $actividad = new actividad(\'\', \'\',\'\',\'\',\'\',\'\',\'\',null,\'\',\'\');
            } else {
                $actividad = get_data_form();
            }
            $datos = $actividad->ConsultarTodo();
            
            if (!tienePermisos(\'ACTIVIDAD_DEFAULT\')) {
                new Mensaje(\'No tienes los permisos necesarios\', \'../Views/DEFAULT_Vista.php\');
            } else {
                new ACTIVIDAD_DEFAULT($datos, \'../Views/DEFAULT_Vista.php\');

            }

    }

?>
';

    fwrite($file,$str);
}




?>