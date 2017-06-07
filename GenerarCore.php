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
                    new MESSAGE($respuesta, \'../Controller/$' . $tabla .'_Controller.php\');
                }
                break;      
            break;
        case $strings[\'Borrar\']: //Borrado de actividades
           if (!$_POST){
                    $' . $tabla .' = new ' . $tabla .'_Model($_REQUEST[\'' . $clave['COLUMN_NAME'] .'\'],'','','','','','','','','');
                    $valores = $' . $tabla .'->RellenaDatos($_REQUEST[\'' . $clave['COLUMN_NAME'] .'\']);
                    new ' . $tabla .'_DELETE($valores);
                }
                else{
                    $' . $tabla .' = get_data_form();
                    $respuesta = $' . $tabla .'->DELETE();
                    new MESSAGE($respuesta, \'../Controller/' . $tabla .'_Controller.php\');
                }
                break;
        case $strings[\'Ver\']: 
                $' . $tabla .' = new ' . $tabla .'_Model($_REQUEST[\'' . $clave['COLUMN_NAME'] .'\'],\'\','','','','','','','','');
                $valores = $' . $tabla .'->RellenaDatos($_REQUEST[\'' . $clave['COLUMN_NAME'] .'\']);
                new ' . $tabla .'_SHOWCURRENT($valores);
                break;
        case $strings[\'Modificar\']: //Modificación de actividades
if (!$_POST){
                    $' . $tabla .' = new ' . $tabla .'_Model($_REQUEST[\'' . $clave['COLUMN_NAME'] .'\'],\'\',\'\',\'\',\'\','','','','','');
                    $valores = $' . $tabla .'->RellenaDatos($_REQUEST[\'' . $clave['COLUMN_NAME'] .'\']);
                    new ' . $tabla .'_EDIT($valores);
                }
                else{
                    
                    $' . $tabla .' = get_data_form();

                    $respuesta = $' . $tabla .'->EDIT();
                    new MESSAGE($respuesta, \'../Controller/' . $tabla .'_Controller.php\');
                }
                
                break;
        case $strings[\'Buscar\']: //Consulta de actividades
            if (!$_POST){
                    new ' . $tabla .'_SEARCH();
                }
                else{
                    $' . $tabla .' = get_data_form();
                    $datos = $' . $tabla .'->SEARCH();

                    $lista = array('CodigoA','AutoresA','TituloA','TituloR','ISSN','VolumenR','PagIniA','PagFinA','FechaPublicacionR','EstadoA');

                    new ' . $tabla .'_SHOWALL($lista, $datos, \'../index.php\');
                }
                break;
        default:
           if (!$_POST){
                    $' . $tabla .' = new ' . $tabla .'_Model(\'\',\'\',\'\',\'\',\'\',\'\',\'\',\'\',\'\',\'\');
                }
                else{
                    $' . $tabla .' = get_data_form();
                }
                $datos = $' . $tabla .'->SEARCH();
                $lista = array('CodigoA','AutoresA','TituloA','TituloR','ISSN','VolumenR','PagIniA','PagFinA','FechaPublicacionR','EstadoA');
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
        $file=fopen("/var/www/html/GeneradorPag/IUjulio/Models/" . strtoupper($tabla) . "_Model.php","w+");

        $str='<?php


class ' . $tabla .'
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
    function insert()
    {
        $this->ConectarBD();
       if (($this->CodigoA <> '')){
        
        $sql = "SELECT * FROM ' . $tabla .' WHERE (CodigoA = $this->CodigoA)";

        if (!$result = $this->mysqli->query($sql)){
            return \'No se ha podido conectar con la base de datos\'; // error en la consulta (no se ha podido conectar con la bd
        }
        else {
            if ($result->num_rows == 0){
                
                $sql = "INSERT INTO ' . $tabla .' (
                    CodigoA,
                    AutoresA,
                    TituloA,
                    TituloR,
                    ISSN,
                    VolumenR,
                    PagIniA,
                    PagFinA,
                    FechaPublicacionR,
                    EstadoA) 
                        VALUES (
                    $this->CodigoA,
                        \'$this->AutoresA\',
                        \'$this->TituloA\',
                        \'$this->TituloR\',
                        \'$this->ISSN\',
                        \'$this->VolumenR\',
                        \'$this->PagIniA\',
                        \'$this->PagFinA\',
                        \'$this->FechaPublicacionR\',
                        \'$this->EstadoA\')";
                
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



    //Funcion de destruccion del objeto: se ejecuta automaticamente
    function __destruct()
    {

    }

    //Consultar una actividad
    function select_actividad()
    {

        $this->ConectarBD();
        $sql = "SELECT * FROM ACTIVIDAD WHERE ACTIVIDAD_NOMBRE = \'".$this->ACTIVIDAD_NOMBRE."\' OR CATEGORIA_ID = \'". $this->CATEGORIA_ID . "\'";
        $resultado=$this->mysqli->query($sql);

        if (!($resultado = $this->mysqli->query($sql))){
            return \'Error en la consulta sobre la base de datos\';
        }
        else{

            $toret=array();
            $i=0;

            while ($fila= $resultado->fetch_array()) {


                $toret[$i]=$fila;
                $i++;


            }


            return $toret;

        }


    }


    //Realiza el borrado lógico de un usuario.
    function delete_actividad(){

        $this->ConectarBD();

        $sql = "UPDATE ACTIVIDAD SET ACTIVO=\'1\' WHERE ACTIVIDAD_NOMBRE=\'".$this->ACTIVIDAD_NOMBRE."\';";
        if($this->mysqli->query($sql) === TRUE) {
            $sql1="SELECT ACTIVIDAD_ID FROM ACTIVIDAD WHERE ACTIVIDAD_NOMBRE = \'" . $this->ACTIVIDAD_NOMBRE."\';";
            $resultado = $this->mysqli->query($sql1)->fetch_array();
            $sql = "DELETE FROM CALENDARIO WHERE CALENDARIO_ACTIVIDAD   = \'".$resultado[\'ACTIVIDAD_ID\']."\'";
            $this->mysqli->query($sql);
            return "La actividad ha sido borrada correctamente";
        }else
            return "La actividad no existe";
    }


    //Devuelve la información correspondiente a una actividad
    function RellenaDatos()
    {
        $this->ConectarBD();
        $sql = "select * from ACTIVIDAD where ACTIVIDAD_NOMBRE = \'".$this->ACTIVIDAD_NOMBRE."\'";
        if (!($resultado = $this->mysqli->query($sql))){
            return \'Error en la consulta sobre la base de datos\';
        }
        else{
            $result = $resultado->fetch_array();


            return $result;
        }
    }

    //Devuelve la información correspondiente a una actividad
    function RellenaDatosCalendarioActividad()
    {
        $this->ConectarBD();
        $sql1="SELECT ACTIVIDAD_ID FROM ACTIVIDAD WHERE ACTIVIDAD_NOMBRE = \'" . $this->ACTIVIDAD_NOMBRE."\';";
        $resultado = $this->mysqli->query($sql1)->fetch_array();
        $sql = "select * from CALENDARIO where CALENDARIO_ACTIVIDAD = \'".$resultado[\'ACTIVIDAD_ID\']."\'";
        if (!($resultado2 = $this->mysqli->query($sql))){
            return \'Error en la consulta sobre la base de datos\';
        }
        else{
            $result = $resultado2->fetch_array();


            return $result;
        }
    }


    //Modificar la actividad
    function update_actividad($ACTIVIDAD_ID)
    {
        $this->ConectarBD();
        $sql = "select * from ACTIVIDAD where ACTIVIDAD_ID = \'".$ACTIVIDAD_ID."\'";
        $result = $this->mysqli->query($sql);
        if ($result->num_rows == 1)
        {
            $sql ="SELECT ACTIVIDAD_PRECIO FROM ACTIVIDAD WHERE ACTIVIDAD_ID=".$ACTIVIDAD_ID;
            $result = $this->mysqli->query($sql)->fetch_array();
            if($this->ACTIVO==\'Activo\'){
                $this->ACTIVO=0;
            }else{
                $this->ACTIVO=1;
            }
                $sql = "UPDATE ACTIVIDAD SET ACTIVIDAD_NOMBRE =\'".$this->ACTIVIDAD_NOMBRE."\', ACTIVIDAD_PRECIO =\'".$this->ACTIVIDAD_PRECIO."\',ACTIVIDAD_DESCRIPCION =\'".$this->ACTIVIDAD_DESCRIPCION."\',CATEGORIA_ID =\'".$this->CATEGORIA_ID."\',ACTIVO =\'".$this->ACTIVO."\' WHERE ACTIVIDAD_ID =\'".$ACTIVIDAD_ID."\'";
            //echo $sql;
            if (!($resultado = $this->mysqli->query($sql))){
                return "Error en la consulta sobre la base de datos";
            }
            else{
                return "La actividad se ha modificado con exito";
            }
        }
        else
            return "La actividad no existe";
    }

    //Listar todas las actividads
    function ConsultarTodo()
    {
        $this->ConectarBD();
        $sql = "SELECT ACTIVIDAD.ACTIVIDAD_ID,ACTIVIDAD.ACTIVIDAD_NOMBRE,ACTIVIDAD.ACTIVIDAD_PRECIO,ACTIVIDAD.ACTIVIDAD_DESCRIPCION,ACTIVIDAD.CATEGORIA_ID FROM LUGAR ,ACTIVIDAD_ALBERGA_LUGAR ,ACTIVIDAD, CATEGORIA WHERE LUGAR.LUGAR_ID =ACTIVIDAD_ALBERGA_LUGAR.LUGAR_ID AND ACTIVIDAD_ALBERGA_LUGAR.ACTIVIDAD_ID = ACTIVIDAD.ACTIVIDAD_ID AND ACTIVIDAD.CATEGORIA_ID = CATEGORIA.CATEGORIA_ID AND ACTIVIDAD.ACTIVO = \'0\'";

        if (!($resultado = $this->mysqli->query($sql))){
            return \'Error en la consulta sobre la base de datos\';
        }
        else{

            $toret=array();
            $i=0;

            while ($fila= $resultado->fetch_array()) {


                $toret[$i]=$fila;
                $i++;

            }

            return $toret;

        }
    }



    //Listar todas las actividades inactivas
    function ConsultarBorradas()
    {
        $this->ConectarBD();
        $sql = "SELECT ACTIVIDAD.ACTIVIDAD_NOMBRE,ACTIVIDAD.ACTIVIDAD_PRECIO,ACTIVIDAD.ACTIVIDAD_DESCRIPCION,CATEGORIA.CATEGORIA_NOMBRE,LUGAR.LUGAR_NOMBRE FROM LUGAR ,ACTIVIDAD_ALBERGA_LUGAR ,ACTIVIDAD, CATEGORIA WHERE LUGAR.LUGAR_ID =ACTIVIDAD_ALBERGA_LUGAR.LUGAR_ID AND ACTIVIDAD_ALBERGA_LUGAR.ACTIVIDAD_ID = ACTIVIDAD.ACTIVIDAD_ID AND ACTIVIDAD.CATEGORIA_ID = CATEGORIA.CATEGORIA_ID AND ACTIVIDAD.ACTIVO = 1";
        if (!($resultado = $this->mysqli->query($sql))){
            return \'Error en la consulta sobre la base de datos\';
        }
        else{

            $toret=array();
            $i=0;

            while ($fila= $resultado->fetch_array()) {


                $toret[$i]=$fila;
                $i++;


            }


            return $toret;

        }
    }

        function ConsultarClientesActividad(){
        $this->ConectarBD();
        $sql = "SELECT CLIENTE_ID, CLIENTE_NOMBRE, CLIENTE_APELLIDOS, CLIENTE_CORREO FROM CLIENTE WHERE CLIENTE_ID IN (SELECT CLIENTE_ID FROM CLIENTE_INSCRIPCION_ACTIVIDAD WHERE ACTIVIDAD_ID = \'" . $this->ACTIVIDAD_ID . "\')";
        if (!($resultado = $this->mysqli->query($sql))) {
            return \'Error en la consulta sobre la base de datos\';
        } else {

            $toret = array();
            $i = 0;

            while ($fila = $resultado->fetch_array()) {


                $toret[$i] = $fila;
                $i++;
            }

        }
            return $toret;
    }
}
';
    }

}


?>