<?php
//Recupera Dados

$SiteFuncoes = new SiteFuncoes();

$placet = $_SESSION['MACC'];
$Reuniao = Url::getURL(1);
$DataHoje = date("Y-m-d H:i");
$reLink = $SiteFuncoes->BuscaDadosSite('reunioes','reId',$Reuniao,'reLink');
$rePlenaria = $SiteFuncoes->BuscaDadosSite('reunioes','reId',$Reuniao,'rePlenaria');

//VERIFICO SE O EVENTO É PLENÁRIA
if($rePlenaria=='1'){

//BUSCO DADOS DO OBREIRO
$MACC_Referencia = $glFuncoes->BuscaDados('[G2L GLm Macom Controle]','MACC_Placet',$placet,'MACC_Referencia');  
$MACC_LOJCReferencia = $glFuncoes->BuscaDados('[G2L GLm Macom Controle]','MACC_Placet',$placet,'MACC_LOJCReferencia');
$LOJC_Numero = $glFuncoes->BuscaDados('[G2L GLj Loja Controle]','LOJC_Referencia',$MACC_LOJCReferencia,'LOJC_Numero');

$PleFuncoes = new PleFuncoes();

$condicao = array("prePlacet = '$placet' and prePleId = 4");
$conta = $PleFuncoes->conta('presencas', 'preId', $condicao, NULL);

if ($conta==0){
//GRAVA PRESENCA
$Campos = array("prePleId", "prePlacet", "preLoja", "preRepresenta");
$Valores = array("prePleId" => "4", "prePlacet" => "$placet", "preLoja" => "'$LOJC_Numero'", "preRepresenta" => "$preRepresenta");
$grava = $PleFuncoes->insert("presencas", $Campos, $Valores);
}
	
print "<script>location.href='".$reLink."'; </script>";
exit;
	
}else{
	
//GRAVA PRESENCA NA TABELA - OUTROS EVENTOS
$Campos = array("logPlacet", "logreId", "logDate");
$Valores = array("logPlacet" => "'$placet'", "logreId" => "'$Reuniao'", "logDate" => "'$DataHoje'");
$grava = $SiteFuncoes->insert("log_reunioes", $Campos, $Valores);

print "<script>location.href='".$reLink."'; </script>";
exit;
	
}