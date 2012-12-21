<?php

function printActMain($mes){

global $game;

$game->player->turnlog = $game->readGameLog($game->player->id);

if(preg_match('/^k.*/',$mes)) {
	$mes = "<font color=\"blue\"><b>".trim($mes,"k")."</b></font><br>\n";
} else if($mes != ""){
	$mes = "<font color=\"red\"><b>ERROR　".$mes."</b></font><br>\n";
}

print <<< DOC_END
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<script type="text/javascript" src="cauldron.js"></script>
<script type="text/javascript">

DOC_END;
$i=0;
print("matname = new Array(7);\n");
foreach($game->matdata as $data){
	print("matname[".$i."] = \"".$data["name"]."\";\n");
	$i++;
}
print("matnum = new Array(");
$str = "";
foreach($game->player->material as $mat){
	$str = $str.$mat.",";
}
print(rtrim($str,",").");\n");

print <<< DOC_END
</script>
</head>
<body>
<form name="logoutbox" action="main.php" method="POST">
<input type="hidden" name="mode" value="logout">
</form>
<a href="javascript:void(0)" onclick="document.logoutbox.submit();">ログアウト</a><br>
<a href="./readme.txt" target="_">readme!</a><br><br>

DOC_END;

if($game->player->pagemode == "perspective"){
print("<form name=\"actbox\" action=\"main.php\" method=\"POST\">");
print <<< DOC_END
<input type="submit" value="戻る"><br>
<input type="hidden" name="mode" value="select">
<input type="hidden" name="act" value="back">
</form>

DOC_END;
printPerspective();
print "</body>\n</html>";
exit;
}

print $game->day."日目";
if($game->rainy) print "(雨)";
else print "(晴れ)";
print "　月齢:".$game->moon;
if($game->moon == 0) print "(新月)";
else if($game->moon == 4) print "(満月)";
print "<br>\n";
print "所持金:".$game->player->gold."G<br>\n";
$dp = $game->daily[0];
print "本日の日替わり：".$game->potdata[$dp]["name"]."のポーション(".$game->potdata[$dp]["buy"]."G)<br><br>\n";
if($mes != "") print $mes."<br>\n";

if(preg_match('/end([0-9&]+)/', $game->status, $match)){
	$winners = preg_split('/,/', $match[1]);
	$winflag = FALSE;
	foreach($winners as $w){
		if($w == $game->player->no) $winflag = TRUE;
	}
	if($winflag){
		print("<h2>あなたは『エリクサー』の発明者として認められました！</h2>\n");
	} else {
		print("<h2>".$game->userdata[$match[1]]->name."が『エリクサー』の調合に成功しました！</h2>\n");
	}
} else {

if($game->player->pagemode == "usepotion"){
	printActPotion();
} else {
switch($game->player->action){
	case "action":
		printActNone();
		break;
	case "gather":
		printActGather();
		break;
	case "compound":
		printActCompound();
		break;
	case "shop":
		printActShop();
		break;
	case "end":
		printActEnd();
		break;
	default:
		print "error:printActMain<br>\n";
		break;
}
}

	print("ターンを終了したプレイヤー：");
	foreach($game->userdata as $u){
		if($u->action == "end") print $u->name."　";
	}
	print("<br>\nターンを終了していないプレイヤー：");
	foreach($game->userdata as $u){
		if($u->action != "end") print $u->name."　";
	}
}

print <<< DOC_END
<br><br>
<table border=1>
<tr>
<td><b>素材</b></td>
<td><b>ポーション</b></td>
</tr><tr>
<td valign="top">

DOC_END;
printMaterials();
print("</td>\n<td valign=\"top\">\n");
printPotions();

print <<< DOC_END
</td>
</tr>
</table>
DOC_END;
printPotionTable();
print <<< DOC_END
<table>
<tr><td>今日</td><td>昨日</td></tr>
<tr>
<td valign="top">
DOC_END;

printPlayerLog("gamelog.log");
print "</td><td valign=\"top\">";
printPlayerLog("prevlog.log");

print <<< DOC_END
</td>
</tr>
</table>
DOC_END;

print("</body>\n</html>");

}

function printActNone(){

global $game;

print("<form name=\"actbox\" action=\"main.php\" method=\"POST\">");
if(!$game->checkUgly()) print "<input type=\"radio\" name=\"act\" value=\"shop\">街へ行く<br>";
else print "<input type=\"radio\" name=\"act\" disabled>(醜悪の呪いのせいで街に行けません)<br>";
print "<input type=\"radio\" name=\"act\" value=\"compound\">調合<br>";
if(!$game->rainy) print "<input type=\"radio\" name=\"act\" value=\"gather\">素材集めに行く<br>";
else print "<input type=\"radio\" name=\"act\" disabled>(雨の日は素材集めはできません)<br>";

printOption();

print <<< DOC_END
<input type="radio" name="act" value="end">ターン終了<br>
<input type="submit" value="実行">
<input type="hidden" name="mode" value="select">
</form>
DOC_END;

}

function printActGather(){

global $game;

print("素材集めに出ています...<br><br>");
print("<form name=\"actbox\" action=\"main.php\" method=\"POST\">");

printOption();

print <<< DOC_END
<input type="radio" name="act" value="end">ターン終了<br>
<input type="submit" value="実行">
<input type="hidden" name="mode" value="select">
</form>
DOC_END;

}

function printActShop(){

global $game;

print <<< DOC_END
<form name="actbox" action="main.php" method="POST">
<input type="radio" name="act" value="buy">買い物をする<br>
<select id="buybox" name="buybox">
<option value="none">--選択--

DOC_END;

$buy = "buy";
$sell = "sell";
if($game->player->nego) {$buy = "sprice"; $sell = "sprice";}
if($game->moon == 0) $buy = "sprice";
for($i=0;$i<4;$i++){
	print("<option value=5".$i.">".$game->matdata[$i]["name"]."(".$game->matdata[$i][$buy]."G)\n");
}
for($i=0;$i<2;$i++){
	$dp[$i] = $game->daily[$i];
	print "<option value=".$dp[$i].">".$game->potdata[$dp[$i]]["name"]."のポーション(".$game->potdata[$dp[$i]][$buy]."G)\n";
}

for($i=0;$i<2;$i++){
	if($game->player->caul[$i] < MAX_CAULDRON){
		if($game->player->caul[$i] == 0){
			print "<option value=\"10".$i."\">コンロを拡張する(".PRICE_CAULDRON1."G)\n";
		} else if($game->player->caul[$i] == 1) {
			print "<option value=\"10".$i."\">鍋".($i+1)."を大きくする(".PRICE_CAULDRON2."G)\n";
		} else {
			print "<option value=\"10".$i."\">鍋".($i+1)."を大きくする(".PRICE_CAULDRON3."G)\n";
		}
	}
}
print <<< DOC_END
</select>
<input type="text" name="buyint" size="2" value="1">個<br>
<input type="radio" name="act" value="sell">持ち物を売る<br>
<select id="sellbox" name="sellbox">
<option value="none">--選択--

DOC_END;

for($i=0;$i<MATERIAL_NO;$i++){
	if($game->player->material[$i] > 0){
		if($game->matdata[$i][$sell] == 0) continue;
		print("<option value=5".$i.">".$game->matdata[$i]["name"]."(".$game->matdata[$i][$sell]."G)\n");
	}
}
for($i=0;$i<POTION_NO;$i++){
	if($game->player->potion[$i] > 0){
		if($game->potdata[$i][$sell] == 0) continue;
		print("<option value=".$i.">".$game->potdata[$i]["name"]."のポーション(".$game->potdata[$i][$sell]."G)\n");
	}
}

print <<< DOC_END
</select>
<input type="text" name="sellint" size="2" value="1">個<br>
<input type="radio" name="act" value="order">素材集めを依頼<br>
依頼料=収穫量*5G 収穫量:
<input type="text" name="order" size="2" value="1"><br>
DOC_END;

printOption();

print <<<DOC_END
<input type="radio" name="act" value="end">ターン終了<br>
<input type="submit" value="実行">
<input type="hidden" name="mode" value="select">
</form>

DOC_END;
}

function printActCompound(){

global $game;

$mats = $game->player->getMaterial();

print "<form name=\"actbox\" action=\"main.php\" method=\"POST\">\n";

if($game->player->checkCauldronNum()){

if($game->player->pagemode == "mixpotion"){
	$type = array("火","水","土","風");
	$value = array("1","2","3");
	$potnum = 0;
	
	print "<select id=\"selectcaul\" name=\"selectc\">\n<option value=\"0\">鍋1";
	if($game->player->caul[1] > 0) print("<option value=\"1\">鍋2");
	print("</select>\n<br>\n");

	for($i=0;$i<POTION_NO;$i++){
		if($game->player->potion[$i] > 0){
			print("<input type=\"checkbox\" name=\"pot".$i."\">".$game->potdata[$i]["name"]."(".$type[$game->potdata[$i]["type"]]."-".$value[$game->potdata[$i]["value"]].")<br>\n");
			$potnum++;
		}
	}
	if($potnum == 0) print("<input type=\"radio\" name=\"act\" disabled>ポーションがひとつもありません<br>\n");
	else print("<input type=\"radio\" name=\"act\" value=\"comp_pot\" checked>これらのポーションで調合する<br>\n");
	print("<input type=\"radio\" name=\"act\" value=\"back\">戻る<br>");
} else {
print <<<DOC_END
<input type="radio" name="act" value="comp_mat" checked>材料から調合する<br>
<select id="selectcaul" name="selectc">
<option value="0">鍋1

DOC_END;

if($game->player->caul[1] > 0) {
	print("<option value=\"1\">鍋2");
}

print("</select>\n<br>\n");
print <<<DOC_END
<select id="mat1" name="mat1">
<option value="none">素材を選択してください

DOC_END;
	
	for($i=0;$i<MATERIAL_NO;$i++){
		if($i != MIMIZU && $mats[$i] > 0){
			print("<option value=".$i.">".$game->matdata[$i]["name"]."\n");
		}
	}
	print("</select>\n<br><select id=\"mat2\" name=\"mat2\">\n<option value=\"none\">素材を選択してください\n");

	for($i=0;$i<MATERIAL_NO;$i++){
		if($i != MIMIZU && $mats[$i] > 0){
			print("<option value=".$i.">".$game->matdata[$i]["name"]."\n");
		}
	}
	if($mats[5] > 0){
		for($i=0;$i<5;$i++){
			print("<option value=1".$i.">".$game->matdata[5]["name"]."(".$game->matdata[$i]["name"]."として)\n");
		}
	}
	print("</select>\n");

	if($game->player->caul[0] == 3 || $game->player->caul[1] == 3){
		print "<input type=\"checkbox\" name=\"double\">二倍の分量で調合<br>\n";
	} else {print "<br>\n";}

	if($game->hasPotion())
	print("\n\n<input type=\"radio\" name=\"act\" value=\"mixpotion\">ポーション同士の調合<br>");
}
} else {
	print ("<input type=\"radio\" name=\"act\" disabled>鍋は全て使用中です<br>");
}

printOption();

print <<< DOC_END
<input type="radio" name="act" value="end">ターン終了<br>
<input type="submit" value="実行">
<input type="hidden" name="mode" value="select">
</form>
DOC_END;

printCauldron();
print "<br>\n";

}

function printActPotion() {

global $game;

print "<form name=\"actbox\" action=\"main.php\" method=\"POST\">\n";
print "<select name=\"potno\">\n<option value=\"none\">--選択--\n";

for($i=0;$i<POTION_NO;$i++){
	if($game->player->potion[$i] > 0) {
		print "<option value=".$i.">".$game->potdata[$i]["name"]."\n";
	}
}

print <<<DOC_END
</select><br>
<input type="radio" name="act" value="potion" checked>使用する<br>
<input type="radio" name="act" value="back">戻る<br>
<input type="submit" value="実行">
<input type="hidden" name="mode" value="select">
</form>

DOC_END;

}

function printActEnd(){

global $game;

switch($game->player->selectact){
	case "gather":
		$action = "今日は素材集めを行いました。<br>\n";
		break;
	case "shop":
		$action = "今日は街に出掛けました。<br>\n";
		break;
	case "compound":
		$action = "今日は調合を行いました。<br>\n";
		break;
	case "none":
		$action = "今日は何もしませんでした。<br>\n";
		break;	
	default:
		$action = "error printActEnd (".$game->player["selectact"].")<br>\n";
		break;

}

print($action."<br>\n");
print <<<DOC_END
（既にターンを終了しています）<br><br>
<form name="actbox" action="main.php" method="POST">
<input type="submit" value="更新">
<input type="hidden" name="mode" value="reload">
</form>
DOC_END;
}

#鍋の表示
function printCauldron(){

global $game;

$size = array("","小","大","特大");
print("<table border=1>\n<tr>\n");
print("<td><b>鍋1(".$size[$game->player->caul[0]].")</b></td>\n");
if($game->player->caul[1] > 0){
	print("<td><b>鍋2(".$size[$game->player->caul[1]].")</b></td>\n");
}
print "</tr>\n<tr>\n";
$i=0;
$str = array("","");
foreach($game->player->turnlog as $l){
	if($i >= 2) break;
	if(preg_match('/compound\(([0-9]),([01]),([0-9]+),([0-9]+)\)/',$l,$match)){
		$str[$match[1]] = $str[$match[1]]."<td>\n".$game->matdata[$match[3]]["name"]."<br>";
		if($match[4] >= 10){
			$str[$match[1]] = $str[$match[1]]."(".$game->matdata[intval($match[4])-10]["name"].")\n";
		} else {
			$str[$match[1]] = $str[$match[1]].$game->matdata[$match[4]]["name"]."\n";
		}
		if($match[2] == "1"){
			$str[$match[1]] = $str[$match[1]]."<br>(二倍)\n";
		}
		$str[$match[1]] = $str[$match[1]]."</td>\n";
		$i++;
	} else if(preg_match('/compound\(([0-9]),(.+)\)/',$l,$match)){
		switch($match[2]){
			case "beat":
				$matno = BEAT;
				break;
			case "primeval":
				$matno = PRIMEVAL;
				break;
			case "elixir":
				$matno = ELIXIR;
		}
		$str[$match[1]] = $str[$match[1]]."<td>\n(".$game->matdata[$matno]["name"].")<br>\n</td>\n";
		$i++;
	}
}


for($i=0;$i<2;$i++){
	if($str[$i] == "" && $game->player->caul[$i] > 0) print("<td>(空き)</td>\n");
	else if($game->player->caul[$i] <= 0) {}
	else print($str[$i]);
}
print("</tr>\n</table>\n");

}

#調合表の表示
function printPotionTable(){
	
global $game;

$type = array("火","水","土","風");
$value = array("1","2","3");

print("\n<br>\n<b>調合表</b>\n<br>\n<table border=2>\n<tr>\n<td></td>\n");
for($i=0;$i<5;$i++){
	print("<td>".$game->matdata[$i]["name"]."</td>\n");
}
print("</tr>\n");
for($i=0;$i<5;$i++){
	print("<tr>\n<td>".$game->matdata[$i]["name"]."</td>\n");
	for($j=0;$j<5;$j++){
		if(!isset($game->player->ptable[$i][$j])){
			print("<td>？</td>\n");
			continue;
		}
		if($game->potiontable[$i][$j] == 100){
			print("<td>失敗</td>\n");
		} else {
			$potno = $game->potiontable[$i][$j];
			print("<td>".$game->potdata[$potno]["name"]."(".$type[$game->potdata[$potno]["type"]]."-".$value[$game->potdata[$potno]["value"]].")</td>\n");
		}
	}
	print("</tr>\n");
}
print("</table>\n");

}

#素材の表示
function printMaterials(){

	global $game;

	for($i=0;$i<MATERIAL_NO;$i++){
		if(isset($game->player->material[$i]) && $game->player->material[$i] != 0){
			echo($game->matdata[$i]["name"]."*".$game->player->material[$i]."<br>\n");
		}
	}
}

#ポーションの表示
function printPotions(){

	global $game;

	$type = array("火","水","土","風");
	$value = array("1","2","3");
	
	for($i=0;$i<POTION_NO;$i++){
		if(isset($game->player->potion[$i]) && $game->player->potion[$i] != 0){
			echo($game->potdata[$i]["name"]."(".$type[$game->potdata[$i]["type"]]."-".$value[$game->potdata[$i]["value"]].")*".$game->player->potion[$i]."<br>\n");
		}
	}
}

#透視モード
function printPerspective(){

global $game;
$larray = array();
foreach($game->readGameLogAll() as $l) {
	if(preg_match('/^(.+):(.+)$/',$l,$match)){
		$tarray = makeLog($match[2],"",array());
		if($tarray[0] != "") array_push($larray,$game->getUserName($match[1]).":".$tarray[0]);
	}
}
foreach($larray as $l) print $l."<br>\n";

}

#プレイヤーログ
function printPlayerLog($file){

global $game;

if($file == "prevlog.log") $st = "yes";
else $st = "today";

$fp = fopen($file, "r");
$logdata = array();
while($line = fgets($fp)) {
	array_push($logdata, $line);
}
fclose($fp);
$printlog = exLog($logdata,$game->player->id,$st);
foreach($printlog as $l){print $l."<br>\n";}

}

#ログイン画面
function printLogin($err){

global $game;

if($err != ""){
	$err = "<font color=\"red\"><b>ERROR　".$err."</b></font><br>\n";
}

print <<<DOC_END
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<body>
<a href="./readme.txt" target="_">はじめにお読みください</a><br><br>

DOC_END;

print $err."<br>\n";

print <<<DOC_END
ログイン
<form name="ibox" action="./main.php" method="POST">
ID<input type="text" name="id"><br>
pass<input type="password" name="pass"><br>
<input type="hidden" name="mode" value="login">
<input type="submit" value="ログイン">
</form><br>

DOC_END;

if($game->status == "0") {

print <<<DOC_END
新規登録
<form name="sbox" action="./main.php" method="POST">
ID(半角英数4〜16文字)<br><input type="text" name="id"><br>
名前(1〜16文字)<br><input type="text" name="name"><br>
pass(半角英数4〜16文字)<br><input type="password" name="pass"><br>
<input type="hidden" name="mode" value="signup">
<input type="submit" value="登録">
</form>

DOC_END;
} else {
print ("※現在、新規登録は受け付けていません\n");
}

print "</body>\n</html>\n";
return;
}

#管理者ログイン画面
function printAdmin(){

print <<<DOC_END
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<body>
<form name="adbox" action="./main.php" method="POST">
<input type="password" name="pass"><br>
<input type="hidden" name="mode" value="admin">
<input type="submit" value="管理者モード">
</form><br>
</body>
</html>

DOC_END;
}

#管理者モード画面
function printAdminMode(){

print <<<DOC_END
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<body>
<form name="adbox" action="./main.php" method="POST">
スタート<input type="radio" name="act" value="start"><br>
リセット<input type="radio" name="act" value="reset"><br>
<input type="hidden" name="mode" value="adminmode">
<input type="submit" value="実行">
</form><br>
</body>
</html>

DOC_END;
}

#ログをあれこれする
function exLog($logdata,$id,$st){

global $game;

$rarray = array();
$mats = array();
foreach($logdata as $l){
	$temp = "";
	#うわさのログ
	if(preg_match('/^uwasa>(.+)\.(.+)\(([0-9]+)\)$/', $l, $match)) {
		switch($match[2]){
			case "table":
				$temp = "噂：".$game->getUserName($match[1])."の調合表は".$match[3]."箇所埋まっている";
				break;
			case "gold":
				$temp = "噂：".$game->getUserName($match[1])."は".$match[3]."G持っている";
				#echo $temp;
				break;
			case "use":
				$temp = "噂：".$game->getUserName($match[1])."は昨日、".$game->potdata[$match[3]]["name"]."のポーションを使った";
				break;
		}
		$action = "";
	#自分以外のログ
	} else if(!preg_match('/^'.$id.':(.+)/', $l, $match)){
		if($l == "result\n"){
			$temp = "-----";
		} else if(preg_match('/^(.+):action\.(.+)/', $l, $match) && $st != "today") {
			$uname = $game->getUserName($match[1]);
			switch($match[2]){
				case "gather":
					$temp = $uname."は素材集めに行ったようです";
					break;
				case "shop":
					$temp = $uname."は街へ出掛けたようです";
					break;
				case "compound":
					$temp = $uname."は調合を行ったようです";
					break;
			}
		} else if(preg_match('/^(.+):get material\(([0-9])\)/', $l, $match) && $st != "today") {
			$uname = $game->getUserName($match[1]);
			if($match[2] == PRIMEVAL || $match[2] == BEAT || $match[2] == ELIXIR){
				$temp = $uname."は".$game->matdata[$match[2]]["name"]."の調合に成功したようです";
			}
		} else if(preg_match('/^(.+):use potion\(([0-9]+)\)/', $l, $match) && $st != "today") {
			if($match[2] == BUKIYOU || $match[2] == SYUAKU) $temp = "誰かが".$game->potdata[$match[2]]["name"]."のポーションを使用したようです";
		}
		$action = "";
		
	#自分のログ
	} else {
		$action = $match[1];
	}
	
	if(isset($action) && $action != ""){
		$tarray = makeLog($action,$st,$mats);
		$temp = $tarray[0];
		$mats = $tarray[1];
	}
	if($temp == "") continue;
	array_push($rarray,$temp);
}

if(sizeof($mats) > 0){
	for($i=0;$i<MATERIAL_NO;$i++){
		if(isset($mats[$i])){
			array_push($rarray,$game->matdata[$i]["name"]."*".$mats[$i]."を入手");
		}
	}
}

return $rarray;
}

#ログ作成
function makeLog($action,$st,$mats){

global $game;
	$temp = "";
	if(preg_match('/get material\(([0-9]+)\)/',$action,$match)){
		if(isset($mats[$match[1]])) {$mats[$match[1]] += 1;}
		else  {$mats[$match[1]] = 1;}
	} else if(preg_match('/get potion\(([0-9]+),([0-9]+)\)/',$action,$match)){
		$temp = $game->potdata[$match[1]]["name"]."のポーション*".$match[2]."を入手";
	} else if(preg_match('/compound (.+)\(([0-9]+),([0-9]+)\)/',$action,$match)){
		$mat1 = $game->matdata[$match[2]]["name"];
		if($match[3] >= 10){
			$mat2 = "(".$game->matdata[$match[3]-10]["name"].")";
		} else {
			$mat2 = $game->matdata[$match[3]]["name"];
		}
		if($match[1] == "success"){$mes = "成功";}
		else if($match[1] == "fail"){$mes = "失敗";}
		$temp = $mes.":".$mat1."+".$mat2;
	} else if(preg_match('/order\(([0-9]+)\)/',$action,$match)) {
		$temp = "素材集めを依頼(".$match[1].")";
	} else if(preg_match('/^action\.(.+)/',$action,$match)) {
		switch($match[1]){
			case "gather":
				if($st == "today") $temp = "素材集めに行くことにしました";
				else $temp = "素材集めに行きました";
				break;
			case "shop":
				$temp = "街へ出掛けました";
				break;
			case "compound":
				if($st == "today") $temp = "調合を行うことにしました";
				else $temp = "調合を行いました";
				break;
		}
	} else if(preg_match('/^use potion\(([0-9]+)\)/',$action,$match)) {
		$temp = $game->potdata[$match[1]]["name"]."のポーションを使用";
	} else if(preg_match('/^open\(([0-9])([0-9])\)/',$action,$match)) {
		$temp = $game->matdata[$match[1]]["name"]."と".$game->matdata[$match[2]]["name"]."の調合を思いつきました";
	} else if(preg_match('/^open\(none\)/',$action,$match)) {
		$temp = "何も思いつきませんでした";
	}
	return array($temp,$mats);
}

function printOption() {

global $game;

if($game->hasPotion())
print("<input type=\"radio\" name=\"act\" value=\"potion\">ポーションの使用<br>");

foreach($game->readGameLog($game->player->id) as $l) {
	if(preg_match('/use potion\('.TOSI.'\)/',$l)) {
		print("<input type=\"radio\" name=\"act\" value=\"perspective\">透視する<br>");
		break;
	}
}

}

function array_flatten($array){
	$tmp = array();
	if(is_array($array)) foreach($array as $val) $tmp = array_merge($tmp, array_flatten($val));
	else $tmp[] = $array;
	return $tmp;
}



?>