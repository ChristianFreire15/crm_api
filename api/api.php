<?php

    include_once("./conexion.php");

    $json = file_get_contents('php://input');

    $data = json_decode($json);

    $parametros = array();

    $validacionParametros = true;

    if($data){

        foreach ($data as $row => $value) {
            array_push($parametros, $row);           
        }

        $module = consultarParametro("module", $parametros, $data, "module");
        $fields = consultarParametro("fields", $parametros, $data, "module");
        $limit = consultarParametro("limit", $parametros, $data, "module");
        $relationship = consultarParametro("relationship", $parametros, $data, "module");
        
        if(!$module){
            $responseData = "Módulo vacío";
            $validacionParametros = false;
        } else if(!$fields){
            $responseData = "Campos vacío";
            $validacionParametros = false;
        } else if(!$limit){
            $responseData = "Límite vacío";
            $validacionParametros = false;
        } else if($relationship){

            $parametrosRelationship = array();

            foreach ($data->relationship as $row => $value) {
                array_push($parametrosRelationship, $row);           
            }

            $relationshipModule = consultarParametro("module", $parametrosRelationship, $data, "relationship");
            $relationshipFields = consultarParametro("fields", $parametrosRelationship, $data, "relationship");

            if(!$relationshipModule){
                $responseData = "Módulo relacionado vacío";
                $validacionParametros = false;
            } else if(!$relationshipFields){
                $responseData = "Campos relacionados vacío";
                $validacionParametros = false;
            }
            
        }

        if($validacionParametros == true){

            /*----- Consultar modulo -----*/
                $validacionModulo = false;

                $resultModulo = consultarModulo($module);

                if($resultModulo){

                    $statusModule = $resultModulo['status'];
                    $searchModule = $resultModulo['search'];
                    $dataModule = $resultModulo['data'];

                    if($searchModule == true){
                        $validacionModulo = true;
                    } else {

                        $validacionModulo = false;
                        $resultData = array(
                            "status" => "200",
                            "search" => false,
                            "data" => $dataModule,
                        );
    
                        echo json_encode($resultData);

                    }

                } else {

                    $validacionModulo = false;
                    $resultData = array(
                        "status" => "200",
                        "search" => false,
                        "data" => "Error al consultar el modulo seleccionado",
                    );
    
                    echo json_encode($resultData);

                }
            /*----- Consultar modulo -----*/

            /*----- Consultar modulo relacionado -----*/
                $validacionModuloRelacionado = false;
                
                if($relationship){

                    $resultModuloRelacionado = consultarModulo($relationshipModule);

                    if($resultModuloRelacionado){

                        $statusModuleRelacionado = $resultModuloRelacionado['status'];
                        $searchModuleRelacionado = $resultModuloRelacionado['search'];
                        $dataModuleRelacionado = $resultModuloRelacionado['data'];

                        if($searchModuleRelacionado == true){
                            $validacionModuloRelacionado = true;
                        } else {

                            $validacionModuloRelacionado = false;
                            $resultData = array(
                                "status" => "200",
                                "search" => false,
                                "data" => $dataModuleRelacionado,
                            );
        
                            echo json_encode($resultData);
                        
                        }

                    } else {

                        $validacionModuloRelacionado = false;

                        $resultData = array(
                            "status" => "200",
                            "search" => false,
                            "data" => "Error al consultar el modulo relacionado",
                        );
        
                        echo json_encode($resultData);
                        
                    }

                } else {
                    $validacionModuloRelacionado = true;
                }
            /*----- Consultar modulo relacionado -----*/

            /*----- Consultar tablas -----*/
                if($validacionModulo == true && $validacionModuloRelacionado == true){

                    $limitConsulta = "LIMIT " . $limit;
                    $moduleSearch = $dataModule;
                    $moduleCustom = $moduleSearch . "_cstm";

                    $tablaCustom ="SELECT COUNT(TABLE_NAME) as count, TABLE_NAME  
                        FROM information_schema.TABLES 
                        WHERE TABLE_SCHEMA = 'crmgrupoexpansionv6'
                        AND TABLE_NAME = '$moduleCustom';
                    ";
    
                    $consultarTablaCustom = mysqli_query($con, $tablaCustom);
                    $rowTablaCustom = mysqli_fetch_array($consultarTablaCustom);
                    $countTablaCustom = $rowTablaCustom['count'];

                    if($countTablaCustom != "0"){
                        $tablaConsultar = "$moduleSearch a ";
                        $tablaConsultar .= "INNER JOIN $moduleCustom ac ON a.id = ac.id_c";
                    } else {
                        $tablaConsultar = "$moduleSearch a";
                    }

                    //Obtener los campos a consultar
                    $fieldsList = explode(",",$fields);

                    $validacionModulos = false;

                    if($relationship){

                        $moduleRelationshipSearch = $dataModuleRelacionado;

                        if($moduleSearch == $moduleRelationshipSearch){

                            $validacionModulos = false;

                            $resultData = array(
                                "status" => "200",
                                "search" => false,
                                "data" => "Error el modulo relacionado no debe ser igual al modulo principal",
                            );
            
                            echo json_encode($resultData);

                        } else {

                            $moduloRelacionado = $moduleSearch . "_" . $moduleRelationshipSearch;

                            $tablaRelacionada ="SELECT COUNT(TABLE_NAME) as count, TABLE_NAME as table_name 
                                FROM information_schema.TABLES 
                                WHERE TABLE_SCHEMA = 'crmgrupoexpansionv6'
                                AND TABLE_NAME LIKE '$moduloRelacionado%';
                            ";
    
                            $consultarTablaRelacionada = mysqli_query($con, $tablaRelacionada);
                            $rowTablaRelacionada = mysqli_fetch_array($consultarTablaRelacionada);
                            $countTablaRelacionada = $rowTablaRelacionada['count'];

                            if($countTablaRelacionada != "0"){

                                $validacionModulos = true;
                                $nombreTablaRelacionada = $rowTablaRelacionada['table_name'];

                                $fieldsRelationshipTable ="SHOW COLUMNS FROM $nombreTablaRelacionada";
                                $queryFields = mysqli_query($con,$fieldsRelationshipTable);

                                while ($rowFields = mysqli_fetch_array($queryFields)) {
                                    $field = $rowFields[0];
                                    $columnasTablaAsociada[] = $field;
                                }

                                if($nombreTablaRelacionada == "accounts_opportunities"){
                                    $tablaIda = $columnasTablaAsociada[2];
                                    $tablaIdb = $columnasTablaAsociada[1];
                                } else {
                                    $tablaIda = $columnasTablaAsociada[3];
                                    $tablaIdb = $columnasTablaAsociada[4];
                                }

                                $filterRelationship = "INNER JOIN $nombreTablaRelacionada ar ON a.id = ar.$tablaIda ";
                                $filterRelationship .= "INNER JOIN $moduleRelationshipSearch r ON ar.$tablaIdb = r.id ";

                                $moduleRelationshipCustom = $moduleRelationshipSearch . "_cstm";

                                $tablaRelacionadaCustom ="SELECT COUNT(TABLE_NAME) as count, TABLE_NAME  
                                    FROM information_schema.TABLES 
                                    WHERE TABLE_SCHEMA = 'crmgrupoexpansionv6'
                                    AND TABLE_NAME = '$moduleRelationshipCustom';
                                ";
    
                                $consultarTablaRelacionadaCustom = mysqli_query($con, $tablaRelacionadaCustom);
                                $rowTablaRelacionadaCustom = mysqli_fetch_array($consultarTablaRelacionadaCustom);
                                $countTablaRelacionadaCustom = $rowTablaRelacionadaCustom['count'];

                                if($countTablaRelacionadaCustom != "0"){
                                    $filterRelationship .= "INNER JOIN $moduleRelationshipCustom rc ON r.id = rc.id_c";
                                }

                                //Obtener los campos relacionados a consultar
                                $fieldsRelationshipList = explode(",",$relationshipFields);

                                $validateRelationshipFields = ",";

                                foreach ($fieldsRelationshipList as $row) {

                                    $field = trim($row);
                                   
                                    $checkField = substr($row, -2);
        
                                    if($checkField == "_c"){
                                        $field = "rc.$field,";
                                    } else {
                                        $field = "r.$field,";
                                    }
        
                                    $validateRelationshipFields .= $field;

                                }

                                $fieldsRelationshipSearch = substr($validateRelationshipFields, 0, -1);

                            } else {

                                $validacionModulos = false;
                                $resultData = array(
                                    "status" => "200",
                                    "search" => false,
                                    "data" => "Error el consultar tabla relacionada",
                                );
                
                                echo json_encode($resultData);

                            }

                        }
                    
                    } else {
                        $validacionModulos = true;
                        $filterRelationship = "";
                        $fieldsRelationshipSearch = "";
                    }

                    if($validacionModulos == true){

                        $validateFields = "";

                        foreach ($fieldsList as $row) {

                            $field = trim($row);
                           
                            $checkField = substr($row, -2);

                            if($checkField == "_c"){
                                $field = "ac.$field,";
                            } else {
                                $field = "a.$field,";
                            }

                            $validateFields .= $field;
                        }

                        $fieldsSearch = substr($validateFields, 0, -1);

                        $query = "SELECT $fieldsSearch $fieldsRelationshipSearch FROM $tablaConsultar 
                            $filterRelationship WHERE !a.deleted AND !r.deleted $limitConsulta
                        ";
                        
                        $queryResult = mysqli_query($con,$query);

                        $resultRows = array();
                        $i = 0;

                        while ($rowResult = mysqli_fetch_array($queryResult)) {

                            $j = 0;

                            foreach ($fieldsList as $row) {
                                $resultRows[$i][trim($row)] = trim($rowResult[$j]);
                                $j++;
                            }

                            foreach ($fieldsRelationshipList as $row) {
                                $resultRows[$i]["relationship"][trim($row)] = trim($rowResult[$j]);
                                $j++;
                            }

                            $i++;
                            
                        }

                        if(!$queryResult){
                            $result = array(
                                "status" => "500",
                                "search" => false,
                                "data" => "Error al obtener los datos",
                            );    
                        } else {
                            $result = array(
                                "status" => "200",
                                "search" => true,
                                "data" => $resultRows,
                            );    
                        }

                        echo json_encode($result);
                    
                    }

                }
            /*----- Consultar tablas -----*/

        } else {
            $resultData = array(
                "status" => "200",
                "search" => false,
                "data" => $responseData,
            );

            echo json_encode($resultData);
        }

    } else {
        $resultData = array(
            "status" => "200",
            "search" => false,
            "data" => "Error al enviar los parametros de consulta",
        );

        echo json_encode($resultData);
    }

    /*----- Funciones -----*/
        function consultarParametro($buscar, $parametros, $data, $tipo)
        {

            if($tipo == "module"){

                if($buscar == "relationship"){

                    $parametroValidacion = array_search($buscar, $parametros);
                    $parametro = $parametroValidacion;
                    return $parametro;

                } else {

                    $parametroValidacion = array_search($buscar, $parametros);

                    if($parametroValidacion != ""){
                        $parametro = $data->$buscar;
                    } else {
                        $parametro = "";
                    }

                    return $parametro;

                }
            
            } else if($tipo == "relationship"){

                $parametroValidacion = array_search($buscar, $parametros);

                if($parametroValidacion != ""){
                    $parametro = $data->relationship->$buscar;
                } else {
                    $parametro = "";
                }

                return $parametro;

            }
        }

        function consultarModulo($modulo){

            $url = "https://democrm.contactvox.com/crmgrupoexpansionv6/index.php?entryPoint=ApiCrm&type=search&module=$modulo";

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result_request_data = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($result_request_data, true);

            if($result){
                $statusModule = $result['status'];
                $searchModule = $result['search_module'];
                $dataModule = $result['data'];
            } else {
                $statusModule = "500";
                $searchModule = false;
                $dataModule = "Error al consultar el módulo";
            }

            return array(
                "status" => $statusModule,
                "search" => $searchModule,
                "data" => $dataModule,
            );

        }
    /*----- Funciones -----*/
        



    
   


?>