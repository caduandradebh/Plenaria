<?php

//Recupera Dados

$SiteFuncoes = new SiteFuncoes(); //BANDO DE DADOS DO SITE / REUNIÕES COMUNS // EXEMPLO ESCOLA, SECRETARIAS

$placet = $_SESSION['NumLoj'];
$Reuniao = Url::getURL(1);
$DataHoje = date("Y-m-d H:i");
$reLink = $SiteFuncoes->BuscaDadosSite('reunioes','reId',$Reuniao,'reLink');
$rePlenaria = $SiteFuncoes->BuscaDadosSite('reunioes','reId',$Reuniao,'rePlenaria');

//VERIFICO SE O EVENTO É PLENÁRIA
if($rePlenaria=='1'){
	
	
//Recupera Dados
$loja = $_SESSION['NumLoj'];
	
$PleFuncoes = new PleFuncoes();//Sistema da Plenaria MYSQL
$glFuncoes = new glFuncoes();//Sistema G2L SQL SERVER

//VERIFICO SE JÁ REGISTROU A PRESENÇA
$condicao = array("preLoja = '$loja' and prePleId = 4");
$conta = $PleFuncoes->conta('presencas', 'preId', $condicao, NULL);
	
//PRESENÇA JÁ REGISTRADA E ENCAMINHO PARA O LINK DO ZOOM.
if ($conta>0){
	print "<script>location.href='".$reLink."'; </script>";
exit;
}else{

//GRAVA PRESENCA
$Campos = array("prePleId", "prePlacet", "preLoja", "preRepresenta");
$Valores = array("prePleId" => "4", "prePlacet" => "0", "preLoja" => "'$loja'", "preRepresenta" => "'1'");
$grava = $PleFuncoes->insert("presencas", $Campos, $Valores);

print "<script>location.href='".$reLink."'; </script>";
exit;
	
}
	
}else{
	
//GRAVA PRESENCA NA TABELA - OUTROS EVENTOS
$Campos = array("logPlacet", "logreId", "logDate", "logLojaMacom");
$Valores = array("logPlacet" => "'$placet'", "logreId" => "'$Reuniao'", "logDate" => "'$DataHoje'", "logLojaMacom" => "'1'");
$grava = $SiteFuncoes->insert("log_reunioes", $Campos, $Valores);	

}


