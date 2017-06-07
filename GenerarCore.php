<?php





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
foreach($arrayTablas as $tabla){
    crearModelo($tabla);
    crearControlador($tabla);

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

function obtenerClave($tabla){
     $mysqli2 = conectarBD();
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.key_column_usage WHERE TABLE_NAME='". $tabla ."' AND CONSTRAINT_NAME = 'PRIMARY' ";
     
    if (!($resultado = $mysqli2->query($sql))) {
        return 'Error en la consulta sobre la base de datos';
    } else {
        $finfo = mysqli_fetch_assoc($resultado);
        
        
        return $finfo;
    }

}


function crearControlador($tabla) {

echo "Iniciando creador de controlador " . $tabla . "..."; ?>
<br>

<?php

    $atributos = listarAtributos($tabla);//Cogemos los atributos de la tabla y los pasamos a un array
    $file=fopen("/var/www/html/GeneradorMVC/IUjulio/Controllers/" . strtoupper($tabla) . "_Controller.php","w+");
    $str='<?php 
    session_start(); //solicito trabajar con la session

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
        $str.= 'if(isset($_REQUEST[\'' . $valor->name . '\'])){
                $' . $valor->name . ' = $_REQUEST[\'' . $valor->name . '\'];
         }else{
                $' . $valor->name . '=null;
         }';

    }

    $str.='$accion = $_REQUEST[\'accion\'];

    $' . $tabla .' = new '. $tabla .'_Model(';
    $i=0;
    foreach ($atributos as $valor) {
        if($i==0) {
            $str.= '$' . $valor->name .'';
    7    }else{
            $str.= ',$' . $valor->name .'';
        }
    }
    $str.=');

    return ' . $tabla . ';
}

if (!isset($_REQUEST[\'accion\'])){
    $_REQUEST[\'accion\'] = \'\';
}7
    ';


    $clave=obtenerClave($tabla);
    
    

    $str.='
    Switch ($_REQUEST[\'accion\']) {
        case $strings[\'Insertar\']: 
                if (!$_POST){
                    new ' . $tabla . '_ADD();
                }
                else{
                    $' . $tabla .' = get_data_form();
                    $respuesta = $' . $tabla .'->ADD();
                    new Mensaje($respuesta, \'../Controller/$' . $tabla .'_Controller.php\');
                }
                break;      
            break;
        case $strings[\'Borrar\']: //Borrado de actividades
           if (!$_POST){
                    $' . $tabla .' = new ' . $tabla .'_Model(  $_REQUEST[\'' . $clave['COLUMN_NAME'] .'\']';
                    for(int i=0;i<$atributos.length;i++){
                        if(i==0){
                         $str.=',\'\'';
                        }else{
                         $str.='\'\'';
                        }
                    }
                  $str.=');
                    $valores = $' . $tabla .'->RellenaDatos($_REQUEST[\'' . $clave['COLUMN_NAME'] .'\']);
                    new ' . $tabla .'_DELETE($valores);
                }
                else{
                    $' . $tabla .' = get_data_form();
                    $respuesta = $' . $tabla .'->DELETE();
                    new Mensaje($respuesta, \'../Controller/' . $tabla .'_Controller.php\');
                }
                break;
        case $strings[\'Ver\']: 
                $' . $tabla .' = new ' . $tabla .'_Model($_REQUEST[\'' . $clave['COLUMN_NAME'] .'\']';
                    for(int i=0;i<$atributos.length;i++){
                        if(i==0){
                         $str.=',\'\'';
                        }else{
                         $str.='\'\'';
                        }
                    }
                  $str.=');
                $valores = $' . $tabla .'->RellenaDatos($_REQUEST[\'' . $clave['COLUMN_NAME'] .'\']);
                new ' . $tabla .'_SHOWCURRENT($valores);
                break;
        case $strings[\'Modificar\']: //Modificación de actividades
if (!$_POST){
                    $' . $tabla .' = new ' . $tabla .'_Model($_REQUEST[\'' . $clave['COLUMN_NAME'] .'\']';
                    for(int i=0;i<$atributos.length;i++){
                        if(i==0){
                         $str.=',\'\'';
                        }else{
                         $str.='\'\'';
                        }
                    }
                  $str.=');
                    $valores = $' . $tabla .'->RellenaDatos($_REQUEST[\'' . $clave['COLUMN_NAME'] .'\']);
                    new ' . $tabla .'_EDIT($valores);
                }
                else{
                    
                    $' . $tabla .' = get_data_form();

                    $respuesta = $' . $tabla .'->EDIT();
                    new Mensaje($respuesta, \'../Controller/' . $tabla .'_Controller.php\');
                }
                
                break;
        case $strings[\'Buscar\']: //Consulta de actividades
            if (!$_POST){
                    new ' . $tabla .'_SEARCH();
                }
                else{
                    $' . $tabla .' = get_data_form();
                    $datos = $' . $tabla .'->SEARCH();
                    new ' . $tabla .'_SHOWALL($lista, $datos, \'../index.php\');
                }
                break;
        default:
           if (!$_POST){
                    $' . $tabla .' = new ' . $tabla .'_Model(\'\'';
                    for(int i=0;i<$atributos.length;i++){
                        if(i==0){
                         $str.=',\'\'';
                        }else{
                         $str.='\'\'';
                        }
                    }
                  $str.=');
                }
                else{
                    $' . $tabla .' = get_data_form();
                }
                $datos = $' . $tabla .'->SEARCH();
                new ' . $tabla .'_SHOWALL($lista, $datos);

            }

    }

?>
';

    fwrite($file,$str);
    echo "Controlador " . $tabla ." creado!! "; ?>
    <br>
    <?php
}

function crearModelo($tabla){

    echo "Iniciando creación del modelo..."; ?>
    <br>
    <?php



        $atributos = listarAtributos($tabla);//Cogemos los atributos de la tabla y los pasamos a un array
        $file=fopen("/var/www/html/GeneradorPag/IUjulio/Models/" . $tabla . "_Model.php","w+");

        $str='<?php


class ' . $tabla .'_Model
{';

        foreach ($atributos as $valor) {
            $str.= 'var $'. $valor->name .';';
        }

        $str.='function __construct(';
        $i=0;
        foreach ($atributos as $valor) {
            if($i==0){
            $str.= '$'. $valor->name .';';
        }else{
            $str.= ',$'. $valor->name .';';
        }
        }

        $str.=')
        {

        include \'../Locates/Strings_\'.$_SESSION[\'IDIOMA\'].\'.php\';';
        foreach($atributos as $valor){
            $str.='$this->' . $valor->name .' = $this->' . $valor->name .';
        }
    }
    

    //Conectarse a la BD
    function ConectarBD()
    {
        $this->mysqli = new mysqli("localhost", "iu2016", "iu2016", "IU2016");
        if ($this->mysqli->connect_errno) {
            echo "Fallo al conectar a MySQL: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error;
        }
    }

    //Anadir una actividad
    function ADD()
    {
        $this->ConectarBD();
       if (($this->' . $clave['COLUMN_NAME'] .' <> '')){
        
        $sql = "SELECT * FROM ' . $tabla .' WHERE (' . $clave['COLUMN_NAME'] .' = $this->' . $clave['COLUMN_NAME'] .')";

        if (!$result = $this->mysqli->query($sql)){
            return \'No se ha podido conectar con la base de datos\'; // error en la consulta (no se ha podido conectar con la bd
        }
        else {
            if ($result->num_rows == 0){
                
                $sql = "INSERT INTO ' . $tabla .' (';
                $i=0;
                 foreach ($atributos as $valor) {
                    if($i==0){
                        $str.= $valor->name;
                    }else{
                        $str.= ',' . $valor->name;
                    }
                    $i++;
                 }
                    $str.=') 
                        VALUES (';
                        $i=0;
                 foreach ($atributos as $valor) {
                    if($i==0){
                        $str.= '$this->\'' . $valor->name . '\'';
                    }else{
                        $str.= ',$this->\'' . $valor->name . '\'';
                    }
                    $i++;
                 }
                        $str.=')";
                
                if (!$this->mysqli->query($sql)) {
                    return \'Error en la inserción\';
                }
                else{
                    return \'Inserción realizada con éxito\'; //operacion de insertado correcta
                }
                
            }
            else
                return \'Ya existe en la base de datos\'; // ya existe
        }
    }
    else{
        return \'Introduzca un valor\'; // introduzca un valor para el usuario
    }
}

//funcion de destrucción del objeto: se ejecuta automaticamente
//al finalizar el script
function __destruct()
{

}

   //funcion Consultar: hace una búsqueda en la tabla con
//los datos proporcionados. Si van vacios devuelve todos
function SEARCH()
{
    $sql = "select ';
                $i=0;
                foreach ($atributos as $valor) {
                    if($i==0){
                        $str.= $valor->name;
                    }else{
                        $str.= ',' . $valor->name;
                    }
                    $i++;
                 }
                    $str.='
                from ' . $tabla .' 
                where 
                    (';
                        $i=0;
                 foreach ($atributos as $valor) {
                    if($i==0){
                        $str.= '(' . $valor->name . '\' LIKE \'%$this->' . $valor->name . '%\') &&';
                    }else{
                        $str.= '&& (' . $valor->name . '\' LIKE \'%$this->' . $valor->name . '%\') &&';
                    }
                    $i++;
                 }
                        $str.=')";
    if (!($resultado = $this->mysqli->query($sql))){
        return \'Error en la consulta sobre la base de datos\';
    }
    else{
        return $resultado;
    }
}


function DELETE()
{
    $sql = "SELECT * FROM ' . $tabla .' WHERE (' . $clave['COLUMN_NAME'] .' = $this->' . $clave['COLUMN_NAME'] .')";
    $result = $this->mysqli->query($sql);
    if ($result->num_rows == 1)
    {
        $sql = "DELETE FROM ' . $tabla .' WHERE (' . $clave['COLUMN_NAME'] .' = $this->' . $clave['COLUMN_NAME'] .')";
        $this->mysqli->query($sql);
        return "Borrado correctamente";
    }
    else
        return "No existe en la base de datos";
}

function RellenaDatos()
{
    $sql = "SELECT * FROM ' . $tabla .' WHERE (' . $clave['COLUMN_NAME'] .' = $this->' . $clave['COLUMN_NAME'] .')";
    if (!($resultado = $this->mysqli->query($sql))){
        return \'No existe en la base de datos\'; // 
    }
    else{
        $result = $resultado->fetch_array();
        return $result;
    }
}

function EDIT()
{

    $sql = "SELECT * FROM ' . $tabla .' WHERE (' . $clave['COLUMN_NAME'] .' = $this->' . $clave['COLUMN_NAME'] .')";
    

    $result = $this->mysqli->query($sql);
    
    if ($result->num_rows == 1)
    {
        $sql = "UPDATE ' . $tabla .' SET ';
                        $i=0;
                 foreach ($atributos as $valor) {
                    if($i==0){
                        $str.=  $valor->name . ' = \'$this->' . $valor->name . '\'';
                    }else{
                        $str.=  ',' . $valor->name . ' = \'$this->' . $valor->name . '\'';
                    }
                    $i++;
                 }
                        $str.=' WHERE ( ' . $clave['COLUMN_NAME'] .' = $this->' . $clave['COLUMN_NAME'] .'
                )";
        
        if (!($resultado = $this->mysqli->query($sql))){
            return \'Error en la modificación\'; 
        }
        else{
            return \'Modificado correctamente\';
        }
    }
    else
        return \'No existe en la base de datos\';
}



}//fin de clase

?> 
';

 fwrite($file,$str);
    }

}


?>