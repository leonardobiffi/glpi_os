<?php 
include ('../../../inc/includes.php');
include ('../../../config/config.php');
// reference the Dompdf namespace
require_once('../dompdf/autoload.inc.php');
use Dompdf\Dompdf;

global $DB;

$SelPlugin = "SELECT * FROM glpi_plugin_os_config";
$ResPlugin = $DB->query($SelPlugin);
$Plugin = $DB->fetch_assoc($ResPlugin);
$EmpresaPlugin = $Plugin['name'];
$EnderecoPlugin = $Plugin['address'];
$TelefonePlugin = $Plugin['phone'];
$CidadePlugin = $Plugin['city'];
$CorPlugin = $Plugin['color'];
$CorTextoPlugin = $Plugin['textcolor'];
$SelTicket = "SELECT * FROM glpi_tickets WHERE id = '".$_GET['id']."'";
$ResTicket = $DB->query($SelTicket);
$Ticket = $DB->fetch_assoc($ResTicket);
$OsId = $_GET['id'];
$OsNome = $Ticket['name'];
$SelDataInicial = "SELECT date,date_format(date, '%d/%m/%Y %H:%i') AS DataInicio FROM glpi_tickets WHERE id = '".$_GET['id']."'";
$ResDataInicial = $DB->query($SelDataInicial);
$DataInicial = $DB->fetch_assoc($ResDataInicial);
$OsData = $DataInicial['DataInicio'];
$OsDescricao = $Ticket['content'];
$SelDataFinal = "SELECT time_to_resolve,date_format(solvedate, '%d/%m/%Y %H:%i') AS DataFim FROM glpi_tickets WHERE id = '".$_GET['id']."'";
$ResDataFinal = $DB->query($SelDataFinal);
$DataFinal = $DB->fetch_assoc($ResDataFinal);
$OsDataEntrega = $DataFinal['DataFim'];
$OsSolucao = $Ticket['solution'];
$SelTicketUsers = "SELECT * FROM glpi_tickets_users WHERE tickets_id = '".$OsId."'";
$ResTicketUsers = $DB->query($SelTicketUsers);
$TicketUsers = $DB->fetch_assoc($ResTicketUsers);
$OsUserId = $TicketUsers['users_id'];
$SelIdOsResponsavel = "SELECT users_id FROM glpi_tickets_users WHERE tickets_id = '".$OsId."' AND type = 2";
$ResIdOsResponsavel = $DB->query($SelIdOsResponsavel);
$OsResponsavel = "";
while ($IdOsResponsavel = $DB->fetch_assoc($ResIdOsResponsavel)) {
	$SelOsResponsavelName = "SELECT * FROM glpi_users WHERE id = '".$IdOsResponsavel['users_id']."'";
	$ResOsResponsavelName = $DB->query($SelOsResponsavelName);
	$OsResponsavelFull = $DB->fetch_assoc($ResOsResponsavelName);
	$OsResponsavel .= $OsResponsavelFull['firstname']. " " .$OsResponsavelFull['realname']. ", ";
}
if(strlen($OsResponsavel)>2){
	$OsResponsavel = substr($OsResponsavel, 0, strlen($OsResponsavel)-2);
}
$SelAtendimento = "select max(date_format(date_mod, '%d/%m/%Y %H:%i')) as date_mod from glpi_logs where itemtype like 'Ticket' and id_search_option=12 and new_value=15 and items_id=".$OsId;
$ResDtAtendimento = $DB->query($SelAtendimento);
if($ResDtAtendimento){
	$dtatend = $DB->fetch_assoc($ResDtAtendimento);
	if($dtatend){
		$OsDataAtendimento = $dtatend['date_mod'];
	}	
}
$EntidadeId = $Ticket['entities_id'];
$SelEmpresa = "SELECT * FROM glpi_entities WHERE id = '".$EntidadeId."'";
$ResEmpresa = $DB->query($SelEmpresa);
$Empresa = $DB->fetch_assoc($ResEmpresa);
$EntidadeName = $Empresa['name'];
$EntidadeCep = $Empresa['postcode'];
$EntidadeEndereco = $Empresa['address'];
$EntidadeEmail = $Empresa['email'];
$EntidadePhone = $Empresa['phonenumber'];
$EntidadeCnpj = $Empresa['comment'];
$SelEmail = "SELECT * FROM glpi_useremails WHERE users_id = '".$OsUserId."'";
$ResEmail = $DB->query($SelEmail);
$Email = $DB->fetch_assoc($ResEmail);
$UserEmail = $Email['email'];
$SelCustoLista = "SELECT actiontime, sec_to_time(actiontime) AS Hora,name,cost_time,cost_fixed,cost_material,FORMAT(cost_time,2,'de_DE') AS cost_time2, FORMAT(cost_fixed,2,'de_DE') AS cost_fixed2, FORMAT(cost_material,2,'de_DE') AS cost_material2, SUM(cost_material + cost_fixed + cost_time * actiontime/3600) AS CustoItem FROM glpi_ticketcosts WHERE tickets_id = '".$OsId."' GROUP BY id";
$ResCustoLista = $DB->query($SelCustoLista);
$SelCusto = "SELECT SUM(cost_material + cost_fixed + cost_time * actiontime/3600) AS SomaTudo FROM glpi_ticketcosts WHERE tickets_id = '".$OsId."'";
$ResCusto = $DB->query($SelCusto);
$Custo = $DB->fetch_assoc($ResCusto);
$CustoTotal =  $Custo['SomaTudo'];
$CustoTotalFinal = number_format($CustoTotal, 2, ',', ' ');
$SelTempoTotal = "SELECT SUM(actiontime) AS TempoTotal FROM glpi_ticketcosts WHERE tickets_id = '".$OsId."'";
$ResTempoTotal = $DB->query($SelTempoTotal);
$TempoTotal = $DB->fetch_assoc($ResTempoTotal);
$seconds = $TempoTotal['TempoTotal'];
$hours = floor($seconds / 3600);
$seconds -= $hours * 3600;
$minutes = floor($seconds / 60);
$seconds -= $minutes * 60;

$html = '
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/styles.termo.css" rel="stylesheet" type="text/css">
<link href="css/styles.css" rel="stylesheet" type="text/css">
</head>

<body>
<!-- inicio das tabelas -->
<table style="width:100%; background:#fff; margin:0;" border="0" cellpadding="2" cellspacing="0"> 
<tr>
<td style="padding: 0px !important;" >
<table style="width:100%; background:#fff;" border="1" cellpadding="2" cellspacing="0">
<tr>
<td width="400" colspan="2">
<table style="width:100%;" border="0" cellpadding="0" cellspacing="0">
<!-- tabela do logotipo -->
<tr><td height="119" valign="middle" style="width:25%; text-align:center; margin:auto;"><img src="./img/logo_os.png" width="100" height="100" align="absmiddle"></td>
<!-- tabela do titulo -->
<td style="text-align:center;"><p><font size="4">'.($EmpresaPlugin).'</font></p>
<p><font size="2">'.("$EnderecoPlugin - $CidadePlugin - $TelefonePlugin").'</font></p>
<!-- tabela do titulo segunda linha -->
<p width="131" height="70"><font size="6"> OS Nº &nbsp;<b>'.$OsId.'</font></b></p></tr>
<!-- fecha a tabela de titulo -->
</table></td>

<!-- segunda tabela -->
<tr class="titulo"><td colspan="2"><center><b><font color="black">DADOS DO CLIENTE</font></b></center></td> </tr>
<tr><td width="50%"><b>Empresa: </b>'.($EntidadeName).'</td><td ><b>Telefone: </b>'.($EntidadePhone).'</td></tr>
<tr><td width="50%"><b>Endereço: </b>'.($EntidadeEndereco).'</td><td><b>E-Mail: </b>'.($EntidadeEmail).'</td></tr>
<tr><td width="50%"><b>CNPJ: </b>'.($EntidadeCnpj).'</td><td ><b>CEP: </b>'.($EntidadeCep).'</td></tr>

<!-- tabela OS -->
<tr class="titulo">
    <td colspan="2"><center><b><font color="black">DETALHES DA ORDEM DE SERVIÇO</font></b></center></td>
</tr>
<tr>
    <td width="50%"><b>Título: </b>'.$OsNome.'</td>
    <td width="50%"><b>Responsável: </b>'.$OsResponsavel.'</td>
</tr>
<tr>
    <td width="50%"><b>Hora de Início: </b>'.($OsData).'</td>
    <td><b>Hora de Término: </b>'.($OsDataEntrega).'</td>
</tr>
<tr class="titulo">
    <td colspan="2"><center><b><font color="black">DESCRIÇÃO</font></b></center></td>
</tr>
<tr>
    <td colspan="2" valign="top" style="padding:10px;">'.html_entity_decode($OsDescricao).'</td>
</tr>
<tr class="titulo">
    <td colspan="2"><center><b><font color="black">SOLUÇÃO</font></b></center></td>
</tr>
<tr>
    <td height="5" colspan="2" valign="top" style="padding:10px;">
    <p>'.(( $OsSolucao == null ) ? "<br><hr><br><hr><br><hr><br><hr>" : html_entity_decode($OsSolucao)).'</p>
    </td>
</tr>

<table width="100%" border="0" align="center" cellpadding="2" cellspacing="0">
<tr><td><br/></td></tr>
<tr><td><br/></td></tr>
<tr><td><br/></td></tr>
<tr align="center"><td style="text-align:center;">____________________________________</td><td style="text-align:center;">_____________________________________</td></tr>
<tr align="center"><td style="text-align:center;" >'.($EntidadeName).'</td><td style="text-align:center;" >'.($EmpresaPlugin).'</td></tr>
</table>
</table> 
</body>
</html>';

$dompdf = new Dompdf();
$dompdf->setPaper("A4");

// load the html content
$dompdf->loadHtml($html);
$dompdf->render();

// footer
date_default_timezone_set('America/Campo_Grande');
$date = date("d/m/Y H:i");
$dompdf->getCanvas()->page_text(520, 810, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 8, array(0,0,0));
$dompdf->getCanvas()->page_text(30, 810, "Impresso em ".$date, $font, 8, array(0,0,0));

$dompdf->stream("OS_".str_pad($data['id_ordem_servico'], 4, '0', STR_PAD_LEFT).".pdf",array("Attachment"=> false));

?>