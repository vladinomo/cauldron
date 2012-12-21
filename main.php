<?php

require("./mod.php");
require("./game.php");
require("./player.php");
require("./define.php");

define("ADMINPASS", "adminpass");

session_start();

#ロック開始 ゲーム的に衝突するとアレなのでひとりずつ処理する
$lock_fp = fopen("lockfile","r+");
flock($lock_fp,LOCK_EX);

$game = new Game();

if(isset($_SESSION["id"])){
	$game->setPlayer($_SESSION["id"]);
}

if(isset($_POST["mode"])){
	switch($_POST["mode"]) {
		case "login":
			if(!isset($_POST["id"]) || !isset($_POST["pass"])){
				printLogin("不正なデータが送信されました");
			} else if(($loginres = $game->checkLogin($_POST["id"], $_POST["pass"])) != "ok"){
				printLogin($loginres);
			} else {
				$game->setPlayer($_POST["id"]);
				printActMain("");
			}
			break;
		case "select":
			if(!isset($_SESSION["id"])){
				printLogin("ログインし直してください");
			} else if(!isset($_POST["act"])){
				printActMain("行動を選択してください");
			} else {
				$mes = $game->setAction($_POST["act"]);
				printActMain($mes);
			}
			break;
		case "reload":
			printActMain("");
			break;
		case "logout":
			session_destroy();
			printLogin("");
			break;
		case "signup":
			if(!isset($_POST["id"]) || !isset($_POST["pass"]) || !isset($_POST["name"])){
				printLogin("不正なデータが送信されました");
			} else if(($signupres = $game->checkSignUp($_POST["id"], $_POST["pass"], $_POST["name"])) != "ok"){
				printLogin($signupres);
			} else {
				$game->makeNewPlayer($_POST["id"],$_POST["pass"],$_POST["name"]);
				$game->setPlayer($_POST["id"]);
				printActMain("");
			}
			break;
		case "admin":
			if(!isset($_POST["pass"]) || $_POST["pass"] != ADMINPASS){
				printAdmin();
			} else {
				printAdminMode();
			}
			break;
		case "adminmode":
			if(!isset($_POST["act"])){
				printAdmin();
			} else {
				switch($_POST["act"]){
					case "start":
						$game->startGame();
						session_destroy();
						printLogin("");
						break;
					case "reset":
						$game->resetGame();
						session_destroy();
						printLogin("");
						break;
				}
			}
			break;
	}
} else {
	if(isset($_SESSION["id"])){
		printActMain("");
	} else {
		printLogin("");
	}
}

#ロック解除
fclose($lock_fp);

?>

