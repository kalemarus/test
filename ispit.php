<?php
include __DIR__ . '/config.php';
session_start();
$greske = array();
if (!isset($_SESSION['korisnik_id'])) {
	$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	header('Location: ' . $url . '/index.php');
	exit;
}
$greske = array();
if (!isset($_SESSION['tip_korisnika']) || $_SESSION['tip_korisnika'] != Korisnik::PROFESOR) {
	array_push($greske, 'Morate biti profesor da bi ste pristupili ovoj stranici!');
}
$ispit = Ispit::nadjiPoId($_GET['ispit_id']);
if (empty($ispit)) {
	array_push($greske, 'Nije pronadjen ispit!!!');
}
if (!empty($_POST)) {
	$upozorenja = array();
	if (!is_array($_POST)) {
		array_push($upozorenja, 'Neispravan zahtev!!');
	}
	$broj_pitanja = $ispit->vratiBrojPitanja();
	for ($i = 1; $i<= $broj_pitanja; $i++) {
		if (empty($_POST["pitanje_{$i}"])) {
			array_push($upozorenja, "Pitanje br {$i} je prazno");
		}
		if (!isset($_POST["tacan_odgovor_{$i}"])) {
			array_push($upozorenja, "Nije izabran tacan odgovor za pitanje br {$i}!");
		}
		if (empty($_POST["odgovor_{$i}"])) {
			array_push($upozorenja, "Nepostoje odgovri za pitanje br {$i}!");
		}
		if (empty($upozorenja)) {
			foreach ($_POST["odgovor_{$i}"] as $key => $odgovor) {
				if (empty($odgovor)) {
					$br = $key + 1;
					array_push($upozorenja, "Odgovor br {$br} za pitanje br {$i} je prazan!");
				}
			}
		}
	}
	if (empty($upozorenja)) {
		Pitanje::izbrisiPitanjaZaIspit($ispit->vratiIspitId());
		for ($i = 1; $i<= $broj_pitanja; $i++) {
			$tacan_odgovor_id = $_POST["tacan_odgovor_{$i}"];
			$pitanje = new Pitanje();
			$pitanje->postaviIspitId($ispit->vratiIspitId());
			$pitanje->postaviPitanje($_POST["pitanje_{$i}"]);
			if ($pitanje->insertuj()) {
				foreach ($_POST["odgovor_{$i}"] as $key => $_odgovor) {
					$odgovor = new Odgovor();
					$odgovor->postaviPitanjeId($pitanje->vratiPitanjeId());
					$odgovor->postaviOdgovor($_odgovor);
					if ($odgovor->insertuj()) {
						if ($tacan_odgovor_id == $key) {
							$pitanje->postaviTacanOdgovorId($odgovor->vratiOdgovorId());
							$pitanje->snimi();
						}
					}
				}
			}
		}
		$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
		header('Location: ' . $url . '/profesor.php');
		exit;
	}
}
$pitanja_i_odgovori = Pitanje::nadjiPitanjaIOdgovoreZaPofesore($ispit->vratiIspitId());
include __DIR__ . '/public/views/ispiti.php';