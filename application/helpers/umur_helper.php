<?php
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	function hitung_umur($birthday) {

		// Convert Ke Date Time
		$biday = new DateTime($birthday);
		$today = new DateTime();
		
		$diff = $today->diff($biday);
		
		// Display
		return $diff;
	}

	function hitung_umur_asesmen($birthday, $tgl_asesmen) {

		// Convert Ke Date Time
		$biday = new DateTime($birthday);
		$asesmen_day = new DateTime($tgl_asesmen);
		
		$diff = $asesmen_day->diff($biday);
		
		// Display
		return $diff;
	}

	function selisih_tanggal($awal=null,$akhir=null){
		if(($awal!==null) and ($akhir !== null)){
			// Convert Ke Date Time
			$date_awal = new DateTime($awal);
			$date_akhir = new DateTime($akhir);
			//hitung selisih tanggal dari kedua tanggal
			$diff = $date_akhir->diff($date_awal);
			return $diff;
		}else{
			return false;
		}
	}

	//menghitung tanggal kedepan 
	function tambah_hari($date=null,$i=1){
		if($date==null){
			$date = date('Y-m-d');
		}
		$timestamp = strtotime(date("Y-m-d", strtotime($date)) . " +".$i."days");
		return date('Y-m-d',$timestamp);
	}
	//menghitung tanggal keblelakang 
	function kurangi_hari($date=null,$i=1){
		if($date==null){
			$date = date('Y-m-d');
		}
		$timestamp = strtotime(date("Y-m-d", strtotime($date)) . " -".$i."days");
		return date('Y-m-d',$timestamp);
	}
