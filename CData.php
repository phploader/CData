<?php
/** DOC
 * Pattern Beispiel: 
  $D['PATTERN']['PLATFORM']['D']['MANUFACTURER'] = [
	'Active'		=> ['Type' => 'checkbox'],
	];
 ## Mögliche Attribute: #ToDo: Attribute umsetzen:
 - Type : id, string, number
 - Min : (optional) bei string, gibt mindest Buchstaben an. bei numbers, bestimmt mindest Wert, bereich z.B: -100 oder -100.0000 dann ergibt ein float mit 4 nachkommastellen. ist Min Angegeben, so wird draus ein Pflichtfeld.
 - Max : (optional) bei string, gibt maximal Buchstaben an. bei numbers, bestimmt maximal Wert, bereich z.B: 1000 oder 100.0000 dann ergibt ein float mit 4 nachkommastellen.

 * SET Beispiel:
  $d['WAREHOUSE']['D']['W1'] = [
	'Active'	=> 1, //['Value' => -2 ],
	'Title'		=> 'Warehouse10', //['Value' => 'Warehouse1'],
	];
	$d['WAREHOUSE']['D']['W1']['STORAGE']['D']['W1S1'] = [
		'Active'	=> 0,
		'Title' => 'StorageA',
	];
	$d['WAREHOUSE']['D']['W1']['STORAGE']['D']['W1S2'] = [
		'Active'	=> 0,
		'Title' => 'StorageB',
	];
	$CData->set_object_reqursive($d);

	GET Beispiel:
	$dd['WAREHOUSE'] = []; // gib nur WAREHOUSE konnen aus
	$dd['WAREHOUSE']['STORAGE']['ARTICLE_STOCK'] = []; // Gib bis Ebene 3 die drei Knoten aus.
	$dd['WAREHOUSE']['W'][0]['ID'] = 'W1'; // Filtere Nach WAREHUSE ID W1
	$dd['WAREHOUSE']['STORAGE']['W'][0]['Active'] = [1]; // Gib nur Active STORAGE aller Warehouse aus
	$dd['STORAGE']['W'][0]['Title|>'] = 'R002'; // Gib alle ab R002 aus. Möglich: [NOTIN|LIKE-%|LIKE%-|LIKE%%|>|>=|<=|<]
	$dd['STORAGE']['W'][0]['Title'] = ['R001','R002']; // Gib mit Tittle R001,T002 Datensätze aus
	$dd['STORAGE']['L']['START'] = 1; // Begine ab 1. Gilt für Storage, kann auch für unteren Knoten angegeben werden
	$dd['STORAGE']['L']['STEP'] = 2; // maximal 2 Datensätze ausgeben. Gilt für Storage, kann auch für unteren Knoten angegeben werden
	$dd['WAREHOUSE']['STORAGE']['ARTICLE_STOCK']['A']['Stock'] = ['SUM','COUNT','AVG','MIN','MAX']; #Aggregate COUNT,SUM,AVG,MIN,MAX Ermittelt für das Feld Stock. Ist nur für nummerische Werte möglich
	$dd['PLATFORM']['SUPPLIER']['W'][0]['ARTICLE']['W'][0]['ID'] = ['A1']; #Kann Filter einer höeren Ebene auf die untere Ebene Anwenden. Z.B: wenn ein Supplier gesucht ist, dieser nur Artiekl von ID A1 beinhalte.

	#Nachschlage Tabelle am besten mit Punkt (optional) als Standard nutzen.
	$dd['ARTICLE']['ARTICLE.ATTRIBUTE']['D'][ARTICLE_ID.ATTRIBUTE_ID]['Title'] = 'Hallo';

	#Sortieren:
	$dd['WHAREGOUSE]['O'][0]['Stock'] = 'DESC';

	Mit Row & Slot Angabe je Attribut wird das Attribut in der wp_data_att_slot Tabelle gespeichert in Slot werden Sprachen nicht unterstüzt. Dadurch kann die Performance verbessert werden. Ohne diese Angabe wird es vertikal in der wp_data_att gespeichert


===============================
##Changelog:

#2.00
+ Möglichkeit, gleiche Knotennamen in unterschiedlichen Pfaden zu verwenden.
! Fix: Count Ausgabe beim Parent Array behandelt.
~ Warnings behandelt.
! Fix: Berechnung des Counts korrigiert.
+ Where ID-Abfrage einer höhere Ebene realisiert. Erweiterung dieser Funktion sollte durch ToDo100 erfolgen.
! Fix: Aggregierungsfunktionen überarbeitet
~ Performance: get_data und get_data_reqursive Optiomiert und jeweils um eine foreach Schleife reduziert.
~ In Pattern ist nicht mehr erforderlich Parent Type=id hinzuzufügen, dies wird automatisch berücksichtigt.
#1.03
+ Automatische Erstellung der Datenbankstruktur
! Fix: get_object savePatern Fix
#1.02
~ Die Übergabe einer SQL-Instanz wird entfernt. Stattdessen wird die Verbindung zur Datenbank beim Instanziieren der Klasse aufgebaut.
! Fix: Bei mehreren Vater-IDs wurden keine Ergebnisse ausgegeben.
#1.01
! Fix: Vater-ID wurde nicht an Unterknoten weitergegeben.
! Fix: Grundknoten wurde nicht unter wp_Data_att gespeichert, wenn dieser keine Attribute hatte.
#1.00
~ Entfernt Sonderlösung für Sprachunterscheidung. Die Sprachunterscheidung sollte durch die Standardstruktur realisiert werden.
~ ORDER BY kann nun mehrere Bedingungen annehmen.
! Fix: Problem beim Speichern von mehreren unterschiedlichen Knoten behoben.
! Fix: WHERE-Anweisung mit OR-Anweisung behoben.
+ Datenbankzugriff (SQLite) wurde in die Klasse integriert.

#1.00
+ Geburt

#Legende:
+ Neu
~ Überarbeitet
! Fehler
===============================

#ToDo:
#Performance Optimierung:
##1. #2 Cache: Speichert komplete Zweige als array in DB anhand des Filters. Wird gelöscht sobald Daten im Zweig sich ändern.
##2. #3 Cache: Speichert. Speichert alle Werte anhand Filter für einige Minuten bzw. für fest gelegten Zeitraum, ignoriert dafür jegliche Änderungen am Zweigen.
##3. # Pfad als Haswert (hash("crc32b", $str)) => path_hash speichern in jeder Tabelle. Dadurch ist die doppelte Bennenung von Typ_id 
		und id in unterschiedlichen Pfaden dann möglich.
		- Eine Verknüpfung der Pfade muss dann ermöglicht werden.
*/
class CData
{
	/**
	 * 'PATTERN'	=> Pattern
	 * 'DB'			=> [FILENAME,FLAGS] Datenbank Zugang
	*/
	function __construct($P)
	{
		if($P['DB']) {
			if(file_exists($P['DB']['FILENAME'])) {
				$this->SQL = new SQLite3($P['DB']['FILENAME'], ($P['DB']['FLAGS']??SQLITE3_OPEN_READWRITE) );
			} else {
				$this->SQL = new SQLite3($P['DB']['FILENAME']);
				$this->CreateDB();
			}
			if($P['PRAGMA']??false) {
				$this->SQL->exec($P['PRAGMA']);
			} else {
				$this->SQL->exec('
				PRAGMA busy_timeout = 5000;		PRAGMA cache_size = -2000;
				PRAGMA synchronous = 1;			PRAGMA foreign_keys = ON;
				PRAGMA temp_store = MEMORY;		PRAGMA default_temp_store = MEMORY;
				PRAGMA read_uncommitted = true;	PRAGMA journal_mode = wal;
				PRAGMA wal_autocheckpoint=1000;
				');
			}
		} else { exit('kein DB-Übergabe Parameter!'); }

		$this->PATTERN = $P['PATTERN'];
		$this->refreshCacheObjeckt = [];
		
	}

	function CreateDB() {
		$this->SQL->exec('
			CREATE TABLE IF NOT EXISTS "wp_data" (
			"id" text NOT NULL,
			"type_id" text NOT NULL,
			"path_hash" integer NOT NULL,
			"parent_type_id" text NOT NULL,
			"parent_data_id" text NOT NULL,
			"utimestamp" integer NULL DEFAULT (cast(strftime(\'%s\', \'now\') as int)),
			"itimestamp" integer NULL DEFAULT (cast(strftime(\'%s\', \'now\') as int)),
			PRIMARY KEY ("id", "type_id", "path_hash")
			);

			CREATE INDEX IF NOT EXISTS "wp_data_parent_type_id_parent_data_id" ON "wp_data" ("parent_type_id", "parent_data_id");
			CREATE INDEX IF NOT EXISTS "wp_data_id" ON "wp_data" ("id");
			CREATE INDEX IF NOT EXISTS "wp_data_type_id" ON "wp_data" ("type_id");
			CREATE INDEX IF NOT EXISTS "wp_data_utimestamp" ON "wp_data" ("utimestamp");
			CREATE INDEX IF NOT EXISTS "wp_data_to_type_id" ON "wp_data" ("parent_type_id");
			CREATE INDEX IF NOT EXISTS "wp_data_id_type_id" ON "wp_data" ("id", "type_id");
			CREATE INDEX IF NOT EXISTS "wp_data_id_type_id_to_type_id" ON "wp_data" ("id", "type_id");
			CREATE INDEX IF NOT EXISTS "wp_data_id_path_hash" ON "wp_data" ("path_hash");

			CREATE TABLE IF NOT EXISTS "wp_data_att" (
			"id" text COLLATE \'BINARY\' NOT NULL,
			"type_id" text NOT NULL,
			"path_hash" integer NOT NULL,
			"attribute_id" text NOT NULL,
			"value" text NULL,
			"sort" numeric NULL,
			"utimestamp" integer NULL DEFAULT (cast(strftime(\'%s\', \'now\') as int)),
			"itimestamp" integer NULL DEFAULT (cast(strftime(\'%s\', \'now\') as int)),
			PRIMARY KEY ("id", "type_id", "path_hash", "attribute_id")
			);

			CREATE INDEX IF NOT EXISTS "wp_data_att_value_hash" ON "wp_data_att" ("sort");
			CREATE INDEX IF NOT EXISTS "wp_data_att_id_type_id_attribute_id" ON "wp_data_att" ("id", "type_id", "attribute_id");
			CREATE INDEX IF NOT EXISTS "wp_data_att_id_type_id" ON "wp_data_att" ("id", "type_id");
			CREATE INDEX IF NOT EXISTS "wp_data_att_attribute_id" ON "wp_data_att" ("attribute_id");
			CREATE INDEX IF NOT EXISTS "wp_data_att_type_id" ON "wp_data_att" ("type_id");
			CREATE INDEX IF NOT EXISTS "wp_data_att_id" ON "wp_data_att" ("id");
			CREATE INDEX IF NOT EXISTS "wp_data_att_utimestamp" ON "wp_data_att" ("utimestamp");
			CREATE INDEX IF NOT EXISTS "wp_data_att_path_hash" ON "wp_data_att" ("path_hash");

			CREATE TABLE IF NOT EXISTS "wp_data_att_slot" (
			"id" text NOT NULL,
			"type_id" text NOT NULL,
			"path_hash" integer NOT NULL,
			"row_id" integer NOT NULL,
			"attribute_id0" text NULL,
			"value0" text NULL,
			"sort0" integer NULL,
			"utimestamp0" integer NULL,
			"itimestamp0" integer NULL,
			"attribute_id1" text NULL,
			"value1" text NULL,
			"sort1" integer NULL,
			"utimestamp1" integer NULL,
			"itimestamp1" integer NULL,
			"attribute_id2" text NULL,
			"value2" text NULL,
			"sort2" integer NULL,
			"utimestamp2" integer NULL,
			"itimestamp2" integer NULL,
			"attribute_id3" text NULL,
			"value3" text NULL,
			"sort3" integer NULL,
			"utimestamp3" integer NULL,
			"itimestamp3" integer NULL,
			"attribute_id4" text NULL,
			"value4" text NULL,
			"sort4" integer NULL,
			"utimestamp4" integer NULL,
			"itimestamp4" integer NULL,
			"attribute_id5" text NULL,
			"value5" text NULL,
			"sort5" integer NULL,
			"utimestamp5" integer NULL,
			"itimestamp5" integer NULL,
			"attribute_id6" text NULL,
			"value6" text NULL,
			"sort6" integer NULL,
			"utimestamp6" integer NULL,
			"itimestamp6" integer NULL,
			"attribute_id7" text NULL,
			"value7" text NULL,
			"sort7" integer NULL,
			"utimestamp7" integer NULL,
			"itimestamp7" integer NULL,
			"attribute_id8" text NULL,
			"value8" text NULL,
			"sort8" integer NULL,
			"utimestamp8" integer NULL,
			"itimestamp8" integer NULL,
			"attribute_id9" text NULL,
			"value9" text NULL,
			"sort9" integer NULL,
			"utimestamp9" integer NULL,
			"itimestamp9" integer NULL,
			PRIMARY KEY ("id", "type_id", "path_hash", "row_id")
			);

			CREATE INDEX IF NOT EXISTS "wp_data_att_slot_sort0" ON "wp_data_att_slot" ("sort0");
			CREATE INDEX IF NOT EXISTS "wp_data_att_slot_sort1" ON "wp_data_att_slot" ("sort1");
			CREATE INDEX IF NOT EXISTS "wp_data_att_slot_sort2" ON "wp_data_att_slot" ("sort2");
			CREATE INDEX IF NOT EXISTS "wp_data_att_slot_sort3" ON "wp_data_att_slot" ("sort3");
			CREATE INDEX IF NOT EXISTS "wp_data_att_slot_sort4" ON "wp_data_att_slot" ("sort4");
			CREATE INDEX IF NOT EXISTS "wp_data_att_slot_sort5" ON "wp_data_att_slot" ("sort5");
			CREATE INDEX IF NOT EXISTS "wp_data_att_slot_sort6" ON "wp_data_att_slot" ("sort6");
			CREATE INDEX IF NOT EXISTS "wp_data_att_slot_sort7" ON "wp_data_att_slot" ("sort7");
			CREATE INDEX IF NOT EXISTS "wp_data_att_slot_sort8" ON "wp_data_att_slot" ("sort8");
			CREATE INDEX IF NOT EXISTS "wp_data_att_slot_sort9" ON "wp_data_att_slot" ("sort9");
			CREATE UNIQUE INDEX IF NOT EXISTS "wp_data_att_slot_id_type_id_attribute_id9_language_id9" ON "wp_data_att_slot" ("id", "type_id", "attribute_id9");
			CREATE UNIQUE INDEX IF NOT EXISTS "wp_data_att_slot_id_type_id_attribute_id8_language_id8" ON "wp_data_att_slot" ("id", "type_id", "attribute_id8");
			CREATE UNIQUE INDEX IF NOT EXISTS "wp_data_att_slot_id_type_id_attribute_id7_language_id7" ON "wp_data_att_slot" ("id", "type_id", "attribute_id7");
			CREATE UNIQUE INDEX IF NOT EXISTS "wp_data_att_slot_id_type_id_attribute_id6_language_id6" ON "wp_data_att_slot" ("id", "type_id", "attribute_id6");
			CREATE UNIQUE INDEX IF NOT EXISTS "wp_data_att_slot_id_type_id_attribute_id5_language_id5" ON "wp_data_att_slot" ("id", "type_id", "attribute_id5");
			CREATE UNIQUE INDEX IF NOT EXISTS "wp_data_att_slot_id_type_id_attribute_id4_language_id4" ON "wp_data_att_slot" ("id", "type_id", "attribute_id4");
			CREATE UNIQUE INDEX IF NOT EXISTS "wp_data_att_slot_id_type_id_attribute_id3_language_id3" ON "wp_data_att_slot" ("id", "type_id", "attribute_id3");
			CREATE UNIQUE INDEX IF NOT EXISTS "wp_data_att_slot_id_type_id_attribute_id2_language_id2" ON "wp_data_att_slot" ("id", "type_id", "attribute_id2");
			CREATE UNIQUE INDEX IF NOT EXISTS "wp_data_att_slot_id_type_id_attribute_id1_language_id1" ON "wp_data_att_slot" ("id", "type_id", "attribute_id1");
			CREATE UNIQUE INDEX IF NOT EXISTS "wp_data_att_slot_id_type_id_attribute_id0_language_id0" ON "wp_data_att_slot" ("id", "type_id", "attribute_id0");
			CREATE INDEX IF NOT EXISTS "wp_data_att_slot_path_hash" ON "wp_data_att_slot" ("path_hash");

			CREATE TABLE IF NOT EXISTS "wp_data_cache" (
			"id" text NOT NULL,
			"type_id" text NOT NULL,
			"path_hash" integer NOT NULL,
			"data" blob NULL,
			"utimestamp" integer NULL DEFAULT (cast(strftime(\'%s\', \'now\') as int)),
			"itimestamp" integer NULL DEFAULT (cast(strftime(\'%s\', \'now\') as int)),
			PRIMARY KEY ("id", "type_id", "path_hash")
			);
			CREATE INDEX IF NOT EXISTS "wp_data_tmp_id" ON "wp_data_cache" ("id");
			CREATE INDEX IF NOT EXISTS "wp_data_tmp_type_id" ON "wp_data_cache" ("type_id");
			CREATE INDEX IF NOT EXISTS "wp_data_tmp_utimestamp" ON "wp_data_cache" ("utimestamp");
			CREATE INDEX IF NOT EXISTS "wp_data_tmp_path_hash" ON "wp_data_cache" ("path_hash");

			CREATE TABLE IF NOT EXISTS "wp_data_child" (
			"id" text NOT NULL,
			"type_id" text NOT NULL,
			"child_path_hash" integer NOT NULL,
			"child_type_id" text NOT NULL,
			"child_count" integer NULL DEFAULT 0,
			"utimestamp" integer NULL DEFAULT (cast(strftime(\'%s\', \'now\') as int)),
			"itimestamp" integer NULL DEFAULT (cast(strftime(\'%s\', \'now\') as int)),
			PRIMARY KEY ("id", "type_id", "child_path_hash", "child_type_id")
			);
			CREATE INDEX IF NOT EXISTS "wp_data_child_child_type_id" ON "wp_data_child" ("child_type_id");
			CREATE INDEX IF NOT EXISTS "wp_data_child_utimestamp" ON "wp_data_child" ("utimestamp");
			CREATE INDEX IF NOT EXISTS "wp_data_child_type_id" ON "wp_data_child" ("type_id");
			CREATE INDEX IF NOT EXISTS "wp_data_child_id" ON "wp_data_child" ("id");
			CREATE INDEX IF NOT EXISTS "wp_data_child_path_hash" ON "wp_data_child" ("child_path_hash");
		'); 
	}
	function getPattern(&$D) {
		$D['PATTERN'] = $this->PATTERN;
	}
	function CleanDB() {
		/*ToDo:
		1. Attribute und IDs löschen diese nicht mehr dem Patern entsprechen
		2. Lösche verwaiste data oder data_att aus der Datenbank die keinen Vater haben
		*/
	}

	/** ToDo: Wird nicht genutzt, ist momentan nur eine Idee
	 * Erstellt ein Pattern Nummer zuweisung und gibt eine Nummer als ID aus.
	 * search = Pattern oder Nummer
	*//*
	function _Pattern2Number($search) {
		if(!$this->Pattern2Number[ $search ]) {
			$this->SQL->query("INSERT INTO wp_data_pattern2number (pattern) VALUES ('{$search}') ");
		}

		if(!$this->Pattern2Number && !$this->Pattern2Number[ $search ]) {
			$qry = $this->SQL->query("SELECT id, pattern FROM wp_data_pattern2number");
			while ($a = $qry->fetchArray(SQLITE3_NUM)) {
				$this->Pattern2Number[ $a[1] ] = $a[0];
				$this->Pattern2Number[ $a[0] ] = $a[1];
			}
		}
		return $this->Pattern2Number[ $search ];
	}
	*/
	//Band
	private function set_object(&$D)
	{
		$D['PATH_HASH']??='';
		#$s = microtime(true);
		#echo 'START'.(microtime(true)-$s).'s<br>';
		foreach ((array) $D as $kType => $Type) {
			
			if ($this->PATTERN[$kType]??false) { #Pattern: Überprüft ob erlaubtes Type übergeben wurde
				
				foreach ((array) $Type['D'] as $kSup => $Sup) {
					
					
					#echo "{$kType}-{$kSup} = {$D['PATH_HASH2'][$kType][$kSup]} |";
					$this->refreshCacheObjeckt[ $D['PATH_HASH'].$kType ][ $kSup ] = $kSup; #ToDo: Wegen Slot Methode, wird mehrfach einem Type-Sup zugeordnet. #ToDo: Zusätzlich path_hash muss beachtet werden!
					if (($Sup['Active']??false) != -2) {
						$InsertID = true;
						#$_IU_DATA_ATT = null;
						$_IU_DATA_ATT_SLOT = null;
						foreach ((array) $Sup as $kATT => $ATT) {
						##	echo "Kind: {$kSup}-{$kATT}-{$ATT} | <br>";							
#echo $D['PATH_HASH2'][$ATT][$kATT];
	#$D['PATH_HASH'] = hash("crc32b", $D['PATH_HASH'][$ATT][$kATT]);
	##$D['PATH_HASH'] = $Sup['PATH_HASH2'];
							if($this->PATTERN[$kType][$kATT]??false) { #Pattern: Überprüfen ob erlaubtes Attribut des Types übergeben wurde
								
									if ($this->PATTERN[$kType][$kATT]['Type'] == 'id' && $ATT != '') { #IDs werden in wp_data Eingetragen!
										$InsertID = false;
										$IU_DATA??='';
										$IU_DATA .= (($IU_DATA) ? ',' : '') . "('{$kSup}','{$kType}','{$D['PATH_HASH']}','{$ATT}','{$kATT}')";
									}
									elseif ($this->PATTERN[$kType][$kATT]['Type'] == 'id' ) {#lösche ID
										$D_DATA??='';
										$D_DATA .= (($D_DATA) ? ' OR ' : '') . " (id = '{$kSup}' AND type_id = '{$kType}' AND parent_type_id = '{$kATT}')";
									}
									elseif ( isset($this->PATTERN[$kType][$kATT]['Row']) && isset($this->PATTERN[$kType][$kATT]['Slot']) ) { #Speichern in wp_data_att_slot
										
										if($this->PATTERN[$kType][$kATT]['Type'] == 'password') {
											$ATT = password_hash($ATT,PASSWORD_DEFAULT);
										}
										
										$_IU_DATA_ATT_SLOT[ $this->PATTERN[$kType][$kATT]['Row'] ][ $this->PATTERN[$kType][$kATT]['Slot'] ] .= ",'{$kATT}'";
										$_IU_DATA_ATT_SLOT[ $this->PATTERN[$kType][$kATT]['Row'] ][ $this->PATTERN[$kType][$kATT]['Slot'] ] .= (isset($ATT)) ? ",'".$this->_Value2SortHash($ATT)."'" : ",''";
										$_IU_DATA_ATT_SLOT[ $this->PATTERN[$kType][$kATT]['Row'] ][ $this->PATTERN[$kType][$kATT]['Slot'] ] .= (isset($ATT)) ? ",'".$this->SQL->escapeString($ATT)."'" : ",''";
										
									}
									else { #Speichern in wp_data_att
										if($ATT != '') {
											if($this->PATTERN[$kType][$kATT]['Type'] == 'password') {
												$ATT = password_hash($ATT,PASSWORD_DEFAULT);
											}
											$IU_DATA_ATT??='';
											$IU_DATA_ATT .= (($IU_DATA_ATT) ? ',' : '') . "('{$kSup}','{$kType}','{$D['PATH_HASH']}','{$kATT}'";
											$IU_DATA_ATT .= (isset($ATT)) ? ",'".$this->_Value2SortHash($ATT)."'" : ",NULL";
											$IU_DATA_ATT .= (isset($ATT)) ? ",'".$this->SQL->escapeString($ATT)."'" : ",NULL";
											$IU_DATA_ATT .= ")";
										}
										else {
											$D_DATA_ATT .= (($D_DATA_ATT) ? ' OR ' : '') ." (id = '{$kSup}' AND type_id = '{$kType}' AND attribute_id = '{$kATT}' )";
										}
									}
							}
						}
						
						
						foreach((array)$_IU_DATA_ATT_SLOT AS $kR => $vR) {
							$IU_DATA_ATT_SLOT .= ($IU_DATA_ATT_SLOT?',':'')." ('{$kSup}','{$kType}','{$D['PATH_HASH']}','{$kR}'";
							for($i = 0; $i < 10; $i++) {
								if($vR[$i])
									$IU_DATA_ATT_SLOT .= $vR[$i].",cast(strftime('%s', 'now') as int), cast(strftime('%s', 'now') as int)";
								else
									$IU_DATA_ATT_SLOT .= ",NULL,NULL,NULL,NULL,NULL";
							}
							$IU_DATA_ATT_SLOT .= ")";
						}
						
						

						if ($InsertID) {
							$IU_DATA??='';
							$IU_DATA .= (($IU_DATA) ? ',' : '') . "('{$kSup}','{$kType}','{$D['PATH_HASH']}','','')";
						}

					} else {
						$D_ALLDELETE??='';
						$D_ALLDELETE .= (($D_ALLDELETE) ? ' OR ' : '') . "(id = '{$kSup}' AND type_id = '{$kType}')";
					}
				}
			}
		}
		
		#echo 'I.'.(microtime(true)-$s).'s<br>';
		if ($IU_DATA??false) {
		
			$this->SQL->exec("INSERT INTO wp_data (id, type_id, path_hash, parent_data_id,parent_type_id) VALUES {$IU_DATA} 
						ON CONFLICT(id, type_id, path_hash) DO UPDATE SET
							parent_data_id =			CASE WHEN excluded.parent_data_id IS NOT NULL	AND ifnull(parent_data_id,'') <> excluded.parent_data_id		THEN excluded.parent_data_id ELSE parent_data_id END,
							parent_type_id =			CASE WHEN excluded.parent_type_id IS NOT NULL	AND ifnull(parent_type_id,'') <> excluded.parent_type_id		THEN excluded.parent_type_id ELSE parent_type_id END,
							utimestamp =	CASE WHEN 
												excluded.parent_type_id IS NOT NULL	AND ifnull(parent_type_id,'') <> excluded.parent_type_id
											THEN cast(strftime('%s', 'now') as int) ELSE utimestamp END
						"); #CURRENT_TIMESTAMP 1665989041
			#itimestamp =	CASE WHEN excluded.itimestamp IS NULL THEN excluded.cast(strftime('%s', 'now') as int) ELSE itimestamp END
		}
		#echo 'II.'.(microtime(true)-$s).'s<br>';

		if ($IU_DATA_ATT??false) {
			$this->SQL->query("INSERT INTO wp_data_att (id, type_id,path_hash, attribute_id, sort, value ) VALUES {$IU_DATA_ATT} 
						ON CONFLICT(id,  type_id, path_hash, attribute_id) DO UPDATE SET
							value =			CASE WHEN excluded.value IS NOT NULL	AND ifnull(value,'') <> excluded.value		THEN excluded.value ELSE value END,
							sort =			CASE WHEN excluded.sort IS NOT NULL	AND ifnull(sort,'') <> excluded.sort		THEN excluded.sort ELSE sort END,
							utimestamp =	CASE WHEN 
												excluded.value IS NOT NULL	AND ifnull(value,'') <> excluded.value
											THEN cast(strftime('%s', 'now') as int) ELSE utimestamp END
						"); #CURRENT_TIMESTAMP 1665989041
			#itimestamp =	CASE WHEN excluded.itimestamp IS NULL THEN excluded.cast(strftime('%s', 'now') as int) ELSE itimestamp END
		}
		
		if ($IU_DATA_ATT_SLOT??false) {
			$this->SQL->exec("INSERT INTO wp_data_att_slot (id, type_id,path_hash, row_id,
			attribute_id0, sort0, value0, utimestamp0, itimestamp0,
			attribute_id1, sort1, value1, utimestamp1, itimestamp1,
			attribute_id2, sort2, value2, utimestamp2, itimestamp2,
			attribute_id3, sort3, value3, utimestamp3, itimestamp3,
			attribute_id4, sort4, value4, utimestamp4, itimestamp4,
			attribute_id5, sort5, value5, utimestamp5, itimestamp5,
			attribute_id6, sort6, value6, utimestamp6, itimestamp6,
			attribute_id7, sort7, value7, utimestamp7, itimestamp7,
			attribute_id8, sort8, value8, utimestamp8, itimestamp8,
			attribute_id9, sort9, value9, utimestamp9, itimestamp9
			) VALUES {$IU_DATA_ATT_SLOT} 
						ON CONFLICT(id,  type_id, path_hash, row_id) DO UPDATE SET
							value0			=			CASE WHEN excluded.value0 IS NOT NULL			AND ifnull(value0,'') <> excluded.value0					THEN excluded.value0 ELSE value0 END,
							sort0			=			CASE WHEN excluded.sort0 IS NOT NULL			AND ifnull(sort0,'') <> excluded.sort0					THEN excluded.sort0 ELSE sort0 END,
							attribute_id0	=			CASE WHEN excluded.attribute_id0 IS NOT NULL	AND ifnull(attribute_id0,'') <> excluded.attribute_id0		THEN excluded.attribute_id0 ELSE attribute_id0 END,
							
							value1			=			CASE WHEN excluded.value1 IS NOT NULL			AND ifnull(value1,'') <> excluded.value1					THEN excluded.value1 ELSE value1 END,
							sort1			=			CASE WHEN excluded.sort1 IS NOT NULL			AND ifnull(sort1,'') <> excluded.sort1					THEN excluded.sort1 ELSE sort1 END,
							attribute_id1	=			CASE WHEN excluded.attribute_id1 IS NOT NULL	AND ifnull(attribute_id1,'') <> excluded.attribute_id1		THEN excluded.attribute_id1 ELSE attribute_id1 END,
							
							value2			=			CASE WHEN excluded.value2 IS NOT NULL			AND ifnull(value2,'') <> excluded.value2					THEN excluded.value2 ELSE value2 END,
							sort2			=			CASE WHEN excluded.sort2 IS NOT NULL			AND ifnull(sort2,'') <> excluded.sort2					THEN excluded.sort2 ELSE sort2 END,
							attribute_id2	=			CASE WHEN excluded.attribute_id2 IS NOT NULL	AND ifnull(attribute_id2,'') <> excluded.attribute_id2		THEN excluded.attribute_id2 ELSE attribute_id2 END,
							
							value3			=			CASE WHEN excluded.value3 IS NOT NULL			AND ifnull(value3,'') <> excluded.value3					THEN excluded.value3 ELSE value3 END,
							sort3			=			CASE WHEN excluded.sort3 IS NOT NULL			AND ifnull(sort3,'') <> excluded.sort3					THEN excluded.sort3 ELSE sort3 END,
							attribute_id3	=			CASE WHEN excluded.attribute_id3 IS NOT NULL	AND ifnull(attribute_id3,'') <> excluded.attribute_id3		THEN excluded.attribute_id3 ELSE attribute_id3 END,
							
							value4			=			CASE WHEN excluded.value4 IS NOT NULL			AND ifnull(value4,'') <> excluded.value4					THEN excluded.value4 ELSE value4 END,
							sort4			=			CASE WHEN excluded.sort4 IS NOT NULL			AND ifnull(sort4,'') <> excluded.sort4					THEN excluded.sort4 ELSE sort4 END,
							attribute_id4	=			CASE WHEN excluded.attribute_id4 IS NOT NULL	AND ifnull(attribute_id4,'') <> excluded.attribute_id4		THEN excluded.attribute_id4 ELSE attribute_id4 END,
							
							value5			=			CASE WHEN excluded.value5 IS NOT NULL			AND ifnull(value5,'') <> excluded.value5					THEN excluded.value5 ELSE value5 END,
							sort5			=			CASE WHEN excluded.sort5 IS NOT NULL			AND ifnull(sort5,'') <> excluded.sort5					THEN excluded.sort5 ELSE sort5 END,
							attribute_id5	=			CASE WHEN excluded.attribute_id5 IS NOT NULL	AND ifnull(attribute_id5,'') <> excluded.attribute_id5		THEN excluded.attribute_id5 ELSE attribute_id5 END,
							
							value6			=			CASE WHEN excluded.value6 IS NOT NULL			AND ifnull(value6,'') <> excluded.value6					THEN excluded.value0 ELSE value6 END,
							sort6			=			CASE WHEN excluded.sort6 IS NOT NULL			AND ifnull(sort6,'') <> excluded.sort6					THEN excluded.sort6 ELSE sort6 END,
							attribute_id6	=			CASE WHEN excluded.attribute_id6 IS NOT NULL	AND ifnull(attribute_id6,'') <> excluded.attribute_id6		THEN excluded.attribute_id6 ELSE attribute_id6 END,
							
							value7			=			CASE WHEN excluded.value7 IS NOT NULL			AND ifnull(value7,'') <> excluded.value7					THEN excluded.value0 ELSE value7 END,
							sort7			=			CASE WHEN excluded.sort7 IS NOT NULL			AND ifnull(sort7,'') <> excluded.sort7					THEN excluded.sort7 ELSE sort7 END,
							attribute_id7	=			CASE WHEN excluded.attribute_id7 IS NOT NULL	AND ifnull(attribute_id7,'') <> excluded.attribute_id7		THEN excluded.attribute_id7 ELSE attribute_id7 END,
							
							value8			=			CASE WHEN excluded.value8 IS NOT NULL			AND ifnull(value8,'') <> excluded.value8					THEN excluded.value8 ELSE value8 END,
							sort8			=			CASE WHEN excluded.sort8 IS NOT NULL			AND ifnull(sort8,'') <> excluded.sort8					THEN excluded.sort8 ELSE sort8 END,
							attribute_id8	=			CASE WHEN excluded.attribute_id8 IS NOT NULL	AND ifnull(attribute_id8,'') <> excluded.attribute_id8		THEN excluded.attribute_id8 ELSE attribute_id8 END,
							
							value9			=			CASE WHEN excluded.value9 IS NOT NULL			AND ifnull(value9,'') <> excluded.value9					THEN excluded.value9 ELSE value9 END,
							sort9			=			CASE WHEN excluded.sort9 IS NOT NULL			AND ifnull(sort9,'') <> excluded.sort9					THEN excluded.sort9 ELSE sort9 END,
							attribute_id9	=			CASE WHEN excluded.attribute_id9 IS NOT NULL	AND ifnull(attribute_id9,'') <> excluded.attribute_id9		THEN excluded.attribute_id9 ELSE attribute_id9 END,
							

							utimestamp0		=	CASE WHEN excluded.value0 IS NOT NULL	AND ifnull(value0,'') <> excluded.value0	THEN cast(strftime('%s', 'now') as int) ELSE utimestamp0 END,
							utimestamp1		=	CASE WHEN excluded.value1 IS NOT NULL	AND ifnull(value1,'') <> excluded.value1	THEN cast(strftime('%s', 'now') as int) ELSE utimestamp1 END,
							utimestamp2		=	CASE WHEN excluded.value2 IS NOT NULL	AND ifnull(value2,'') <> excluded.value2	THEN cast(strftime('%s', 'now') as int) ELSE utimestamp2 END,
							utimestamp3		=	CASE WHEN excluded.value3 IS NOT NULL	AND ifnull(value3,'') <> excluded.value3	THEN cast(strftime('%s', 'now') as int) ELSE utimestamp3 END,
							utimestamp4		=	CASE WHEN excluded.value4 IS NOT NULL	AND ifnull(value4,'') <> excluded.value4	THEN cast(strftime('%s', 'now') as int) ELSE utimestamp4 END,
							utimestamp5		=	CASE WHEN excluded.value5 IS NOT NULL	AND ifnull(value5,'') <> excluded.value5	THEN cast(strftime('%s', 'now') as int) ELSE utimestamp5 END,
							utimestamp6		=	CASE WHEN excluded.value6 IS NOT NULL	AND ifnull(value6,'') <> excluded.value6	THEN cast(strftime('%s', 'now') as int) ELSE utimestamp6 END,
							utimestamp7		=	CASE WHEN excluded.value7 IS NOT NULL	AND ifnull(value7,'') <> excluded.value7	THEN cast(strftime('%s', 'now') as int) ELSE utimestamp7 END,
							utimestamp8		=	CASE WHEN excluded.value8 IS NOT NULL	AND ifnull(value8,'') <> excluded.value8	THEN cast(strftime('%s', 'now') as int) ELSE utimestamp8 END,
							utimestamp9		=	CASE WHEN excluded.value9 IS NOT NULL	AND ifnull(value9,'') <> excluded.value9	THEN cast(strftime('%s', 'now') as int) ELSE utimestamp9 END,

							itimestamp0		=	CASE WHEN excluded.value0 IS NOT NULL	AND itimestamp0 IS NULL						THEN cast(strftime('%s', 'now') as int) ELSE itimestamp0 END,
							itimestamp1		=	CASE WHEN excluded.value1 IS NOT NULL	AND itimestamp1 IS NULL						THEN cast(strftime('%s', 'now') as int) ELSE itimestamp1 END,
							itimestamp2		=	CASE WHEN excluded.value2 IS NOT NULL	AND itimestamp2 IS NULL						THEN cast(strftime('%s', 'now') as int) ELSE itimestamp2 END,
							itimestamp3		=	CASE WHEN excluded.value3 IS NOT NULL	AND itimestamp3 IS NULL						THEN cast(strftime('%s', 'now') as int) ELSE itimestamp3 END,
							itimestamp4		=	CASE WHEN excluded.value4 IS NOT NULL	AND itimestamp4 IS NULL						THEN cast(strftime('%s', 'now') as int) ELSE itimestamp4 END,
							itimestamp5		=	CASE WHEN excluded.value5 IS NOT NULL	AND itimestamp5 IS NULL						THEN cast(strftime('%s', 'now') as int) ELSE itimestamp5 END,
							itimestamp6		=	CASE WHEN excluded.value6 IS NOT NULL	AND itimestamp6 IS NULL						THEN cast(strftime('%s', 'now') as int) ELSE itimestamp6 END,
							itimestamp7		=	CASE WHEN excluded.value7 IS NOT NULL	AND itimestamp7 IS NULL						THEN cast(strftime('%s', 'now') as int) ELSE itimestamp7 END,
							itimestamp8		=	CASE WHEN excluded.value8 IS NOT NULL	AND itimestamp8 IS NULL						THEN cast(strftime('%s', 'now') as int) ELSE itimestamp8 END,
							itimestamp9		=	CASE WHEN excluded.value9 IS NOT NULL	AND itimestamp9 IS NULL						THEN cast(strftime('%s', 'now') as int) ELSE itimestamp9 END
						"); #CURRENT_TIMESTAMP 1665989041
			#itimestamp =	CASE WHEN excluded.itimestamp IS NULL THEN excluded.cast(strftime('%s', 'now') as int) ELSE itimestamp END
		}
		#echo 'III.'.(microtime(true)-$s).'s<br>';
		if ($D_ALLDELETE??false) {
			#echo "DELETE ALL<br>";
			#echo "<br>DELETE FROM wp_data WHERE (id || type_id || platform_id) IN ({$D_ALLDELETE})";
			$this->SQL->query("DELETE FROM wp_data WHERE {$D_ALLDELETE}"); #Lösche Vater
			$this->SQL->query("DELETE FROM wp_data_att WHERE {$D_ALLDELETE}");
			$this->SQL->query("DELETE FROM wp_data_att_slot WHERE {$D_ALLDELETE}");
			$this->SQL->query("DELETE FROM wp_data_child WHERE {$D_ALLDELETE}");

			#Lösche weitere unter Ebenen. 'ToDo: Je mehr Ebenen, desto heufiger muss diese ausgeführt werden um entgültig zu bereinigen! D.h. es kann erst beim nächsten Delete restlichen Daten von anderen Delete beseitigen.
			$this->SQL->query("DELETE FROM wp_data AS dt2 WHERE 
								NOT EXISTS (SELECT 1 FROM wp_data WHERE dt2.parent_data_id = id AND  dt2.parent_type_id  = type_id)
								AND parent_data_id <> ''"); #Lösche RefIds

			#Lösche verwaiste Kinder Zweige aus wp_data_att ToDo: Performance Problem
			$this->SQL->query("DELETE FROM wp_data_att AS dta2 WHERE 
				NOT EXISTS (SELECT 1 FROM wp_data WHERE dta2.id = id AND dta2.type_id = type_id )
			");
			$this->SQL->query("DELETE FROM wp_data_att_slot AS dta2 WHERE 
				NOT EXISTS (SELECT 1 FROM wp_data WHERE dta2.id = id AND dta2.type_id = type_id )
			");
		}
		if ($D_DATA_ATT??false) { #Lösche leeres Attribut
			#echo "DELETE FROM wp_data_att WHERE {$D_DATA_ATT}<br>";
			##$this->SQL->query("DELETE FROM wp_data_att WHERE (id || type_id || attribute_id) IN ({$D_DATA_ATT})");
			$this->SQL->query("DELETE FROM wp_data_att WHERE {$D_DATA_ATT}");
		}

		if ($D_DATA??false) { #Lösche leeres Attribut
			#echo "DELETE DATA => {$D_DATA}<br>";
			$this->SQL->query("DELETE FROM wp_data WHERE {$D_DATA}");
		}
		
	}
	function set_object_reqursive(&$D = null) #$_to_id bei Funktion aufrunff nicht nutzen!
	{
		static $stLevel = 0;
		$this->set_object($D);

		$_savepattern = $this->PATTERN;
		#Prüfe auf weitere Knotten
		foreach ((array) $D as $kType => $Type) {
			
			if ($this->PATTERN[$kType]??false) { #Pattern: Überprüft ob erlaubtes Type übergeben wurde
				$d=null;
				$d['PATH_HASH'] = hash("crc32b", $D['PATH_HASH'].$kType);
				
				if($_savepattern[$kType]['D']??null) { #Ist eine Weitere Ebene verfügbar?
					$this->PATTERN = $_savepattern[$kType]['D'];

					foreach ((array) $Type['D'] as $kSup => $Sup) {


						if ($Sup['Active']??false != -2) {
							#Wenn das Objekt weitere Knoten beinhaltet, dann reqursive diese ebenfalls anlegen.
							foreach ((array) $Sup as $kATT => $ATT) {
								if (is_array($ATT) && $ATT['D']) { 
#			echo "Parent: {$kType}-{$kSup} | ";					
#$d['PATH_HASH2'][$kSup][$kATT] = hash("crc32b", $D['PATH_HASH'].$kType.$kSup);#Hash ID Pro Pfad


									foreach ((array) $ATT['D'] as $kSubATT => $SubATT) { #setze für jedes Attribute Parent ID
										$d[$kATT]['D'][$kSubATT] = &$ATT['D'][$kSubATT];
										$d[$kATT]['D'][$kSubATT][$kType] = $kSup;

										#echo "{$kATT}.{$kSubATT}-{$kType} = {$d[$kATT]['D'][$kSubATT]['LANGUAGE']} : <br>";
										##$d[$kATT]['D'][$kSubATT]['PATH_HASH2'] = hash("crc32b", $D['PATH_HASH'].$kType.$kSup);
										
										#echo $kATT.'>'.$kType.' : ';
										$this->PATTERN[$kATT][$kType] = ['Type' => 'id']; #Füge temporär Parent mit Id, damit in der höheren Ebene die ID zum Parent gespeichert werden kann.
									}
								}
							}

						}
					}
##print_R($d);
					$stLevel++;
					$this->set_object_reqursive($d);
					$stLevel--;
					$this->PATTERN = $_savepattern; #Wiederherstelle Pattern
				}
			}
		}

		if($stLevel == 0 && $this->refreshCacheObjeckt) {
			$this->_set_cache(); //führt Cache nur einmal am ende aus!
		}
	}

	/**
	 * Erzeugt ein float Wert aus einem Text Folge und kann für Sortierung in der Datenbank verwendet werden
	*/
	private function _Value2SortHash($s) {
		$pre = (int)($s);#Wen Zahlen vor stehen, dann wird nach Zahlen Größe sortiert. So dass 1 kleiner ist als 10
		$s = strtolower($s);
		$ar = unpack("C*", $s);
		$stLen = (count($ar) < 6)?count($ar):5;#Maximal die ersten 5 Zeichen
		$hash??='';
		for($i = 0; $i < $stLen; $i++){
			$hash .= str_pad($ar[$i+1],3,0,STR_PAD_LEFT);
		}
		return (float)"{$pre}.{$hash}";
	}
	private function _set_cache()
	{
		#$s = microtime(true);
		#echo (microtime(true)-$s).'s<br>';
		#ToDo: Performance Optimierung, wenn Nur nach aktuellen Pattern Typen selektiert wird

		#Lösche alte temp Einträge 1.2
		#$this->SQL->query("DELETE FROM wp_data_cache AS dtmp WHERE EXISTS (SELECT 1 FROM wp_data_att WHERE id = dtmp.id AND utimestamp > dtmp.utimestamp)");
		#$this->SQL->query("DELETE FROM wp_data_cache AS dtmp WHERE EXISTS (SELECT 1 FROM wp_data WHERE id = dtmp.id AND utimestamp > dtmp.utimestamp)");
		/*$this->SQL->query("DELETE FROM wp_data_cache AS dtmp WHERE 
			EXISTS (SELECT 1 FROM wp_data_att WHERE id = dtmp.id AND utimestamp > dtmp.utimestamp)
			OR EXISTS (SELECT 1 FROM wp_data WHERE id = dtmp.id AND utimestamp > dtmp.utimestamp)
		");
		*/
		$W??='';
		foreach((array)$this->refreshCacheObjeckt AS $_kT => $T) {
			$W .= ($W?' OR ':'')." ( (path_hash || type_id) = '{$_kT}' ";
			if($T){
				$_W = '';
				foreach((array)$T AS $_kID => $ID) {
					$_W .= ($_W?',':'')." '{$ID}' ";
				}
				$W .= " AND id IN ({$_W}) ";
			}
			$W .= " ) ";
		}
		
		$this->SQL->query("DELETE FROM wp_data_cache AS dtmp WHERE {$W}");
		#echo "1.".(microtime(true)-$s).'s<br>';$s = microtime(true);
		
		#wenn nichtg im Cache dann
		/*$qry = $this->SQL->query("SELECT id, type_id, attribute_id, value
											FROM wp_data_att dat
											WHERE NOT EXISTS (SELECT 1 FROM wp_data_cache WHERE dat.id = id AND dat.type_id = type_id )
											");
											*/
	
		$qry = $this->SQL->query("SELECT id, type_id, path_hash,
			attribute_id0, value0,
			attribute_id1, value1,
			attribute_id2, value2,
			attribute_id3, value3,
			attribute_id4, value4,
			attribute_id5, value5,
			attribute_id6, value6,
			attribute_id7, value7,
			attribute_id8, value8,
			attribute_id9, value9
		FROM wp_data_att_slot dat
		WHERE {$W}
		");

		while ($a = $qry->fetchArray(SQLITE3_ASSOC)) {
			for($i=0;$i< 10; $i++) {
				if($a['value'.$i]) {
					$set_d[$a['path_hash']][$a['type_id']]['D'][$a['id']][ $a['attribute_id'.$i] ] = $a['value'.$i];
					
				}
			}
		}

		#Speichere Sprach Attribute ab
		$qry = $this->SQL->query("SELECT id, type_id, path_hash, attribute_id, value
		FROM wp_data_att dat
		WHERE {$W}
		");
		while ($a = $qry->fetchArray(SQLITE3_ASSOC)) {
			$set_d[$a['path_hash']][$a['type_id']]['D'][$a['id']][$a['attribute_id']] = $a['value'];
		}

		#echo "2.".(microtime(true)-$s).'s<br>';$s = microtime(true);
		#Speichere Ids #Ids ausgeben; Ids sind nicht in Pattern enthalten, dadurch erkennt man dass dies Ids sind. Diese werden nur in temp gespeichert.
		/*
		$qry = $this->SQL->query("SELECT dt.id, dt.type_id
											,dt.parent_data_id , dt.parent_type_id 
											FROM wp_data dt
											WHERE NOT EXISTS (SELECT 1 FROM wp_data_cache WHERE dt.id = id AND dt.type_id = type_id )
											");
		*/
		$qry = $this->SQL->query("SELECT dt.id, dt.type_id, dt.path_hash
			,dt.parent_data_id , dt.parent_type_id 
			FROM wp_data dt
			WHERE {$W}
		");
		while ($a = $qry->fetchArray(SQLITE3_ASSOC)) {
			##if ($a['parent_data_id']) { #ToDo: Was passiert, wenn für ein Type mehrere Ids gespeichert wurden? Momentan wird die letzte ausgegeben. Und wenn an gleiches Type anderes Value gesendet wird, wird neu angelegt statt ersetzt.
				
				$set_d[ $a['path_hash'] ][$a['type_id']]['D'][$a['id']][$a['parent_type_id']]??='';
				$set_d[ $a['path_hash'] ][$a['type_id']]['D'][$a['id']][$a['parent_type_id']] .= (($set_d[$a['type_id']]['D'][$a['id']][$a['parent_type_id']])??false ? '|' : '') . $a['parent_data_id'];
			##}
		}
		#echo "3.".(microtime(true)-$s).'s<br>';$s = microtime(true);
		##print_r($set_d);
		#Speichere neue Daten im Cache
		#Erstelle Cache
		$IU_DATA_ATT = '';
		foreach ((array) $set_d as $kPath => $Path) {
			foreach ((array) $Path as $kType => $Type) {
				foreach ((array) $Type['D'] as $kSup => $Sup) {
					
					$IU_DATA_ATT .= (($IU_DATA_ATT) ? ',' : '') . "('{$kSup}','{$kType}','{$kPath}'";
					
					$json = $this->SQL->escapeString(json_encode($Sup));
					
					#$IU_DATA_ATT .= ",'" . (($json  != '{"":""}')?$json:'') . "'";
					$IU_DATA_ATT .= ",'" . str_replace([',"":""','"":"",','"":""'],'',$json) . "'"; #replace entfernt leere Key Werte
					
					$IU_DATA_ATT .= ")";
				}
			}
		}
		#echo "4.".(microtime(true)-$s).'s<br>';$s = microtime(true);
		if ($IU_DATA_ATT) {
			#$this->SQL->query("REPLACE INTO wp_data_cache (id, type_id, data) VALUES {$IU_DATA_ATT}");
			
			$this->SQL->query("REPLACE INTO wp_data_cache (id, type_id, path_hash,data) VALUES {$IU_DATA_ATT} 
								ON CONFLICT(id, type_id, path_hash) DO UPDATE SET
									data =			CASE WHEN excluded.data IS NOT NULL	AND ifnull(data,'') <> excluded.data		THEN excluded.data ELSE data END,
									utimestamp =	CASE WHEN excluded.data IS NOT NULL	AND ifnull(data,'') <> excluded.data
													THEN cast(strftime('%s', 'now') as int) ELSE utimestamp END
								");
								
		}
		#echo "5.".(microtime(true)-$s).'s<br>';$s = microtime(true);
		#Lösche verwaiste Cache Daten
		#ToDo: in die Clear Funktion zufügen. Bereinigung der DB muss nicht bei jedem Speichern erfolgen.
		####$this->SQL->query("DELETE FROM wp_data_cache AS dtmp WHERE NOT EXISTS (SELECT 1 FROM wp_data WHERE id = dtmp.id AND dtmp.type_id = type_id)");
		#echo "6.".(microtime(true)-$s).'s<br>';$s = microtime(true);
		#Erstelle Child_count
		
		
		#ToDo: Nur dann auslösen für Type, wenn für Kind deletes oder neue Datensätze dazu gekommen sind, auslösen.
		#ToDo: Parent path_hash wird nicht übergeben!
		$ids = implode("','",array_keys($this->refreshCacheObjeckt));
		
		$this->SQL->query("REPLACE INTO wp_data_child (id, type_id, child_path_hash, child_type_id, child_count) 
								SELECT parent_data_id, parent_type_id, path_hash, type_id, count(*) FROM wp_data
								WHERE (path_hash || type_id) IN ('{$ids}')
								GROUP BY parent_data_id,path_hash,type_id
								");
		
		#echo "7.".(microtime(true)-$s).'s<br>';
	}


	private function get_object(&$D = null, &$F= null)
	{
		$F['PATH_HASH']??='';
		foreach ((array) $F as $kType => $Type) {
			if ($this->PATTERN[$kType]??null) {
				
				$W = $L = $O = NULL;
				$F[$kType]['W']??=null;
				foreach ((array) $F[$kType]['W'] as $kR => $R) {
					$W .= (($W) ? ' OR ' : ' AND ( ') . ' ( ';
					$W_ATT = $W_toID = $W_ID = $W1 = '';
					foreach ((array) $R as $kWW => $WW) {
						if(!($WW['W']??false)) {#Prüft ob eine unter Where Abfrage des Kindes beinhaltet!
						$WW = (is_array($WW)) ? implode("','", $WW) : $WW; #Prüfe ob Array übergeben wurde

						$_kWW = explode('|',$kWW);#Splittet Attribut|Anweisung
						
						if (($_kWW[0]??false) == 'ID' ) {
							$W_ID .= (($W_ID) ? ' AND ' : '') . " dtmp.id IN ('{$WW}') AND dtmp.type_id = '{$kType}' AND path_hash = '{$F['PATH_HASH']}' "; # AND (dtmp.path = '{$this->path}' OR dtmp.path = '')

						} elseif (($this->PATTERN[$kType][$_kWW[0]]['Type']??null) == 'id') {
							
							###$W_toID .= (($W_toID) ? ' AND ' : '') . " dtmp.id IN (SELECT id FROM wp_data WHERE parent_type_id IN ('{$_kWW[0]}') AND parent_data_id IN ('{$WW}') ) ";

							##$W_toID .= (($W_toID) ? ' AND ' : '') . " dt.parent_type_id IN ('{$_kWW[0]}') AND dt.parent_data_id IN ('{$WW}')  ";

							$W_toID .= (($W_toID) ? ' AND ' : '') . "EXISTS (SELECT 1 FROM wp_data dt WHERE dtmp.id = dt.id AND dtmp.type_id = dt.type_id AND dt.parent_type_id IN ('{$_kWW[0]}') AND dt.parent_data_id IN ('{$WW}') ) AND path_hash = '{$F['PATH_HASH']}' ";

						} else {
							
							$this->PATTERN[$kType][$_kWW[0]]['Slot']??='';
							
							$_W = " value{$this->PATTERN[$kType][$_kWW[0]]['Slot']} IN ('{$WW}') ";
							if($_kWW[1]??null) {
								$_W = ($_kWW[1] == 'NOTIN') ? " value{$this->PATTERN[$kType][$_kWW[0]]['Slot']} NOT IN ('{$WW}') " : $_W;

								$_W = ($_kWW[1] == 'LIKE-%') ? " value{$this->PATTERN[$kType][$_kWW[0]]['Slot']} LIKE '{$WW}%' " : $_W;
								$_W = ($_kWW[1] == 'LIKE%-') ? " value{$this->PATTERN[$kType][$_kWW[0]]['Slot']} LIKE '%{$WW}' " : $_W;
								$_W = ($_kWW[1] == 'LIKE%%') ? " value{$this->PATTERN[$kType][$_kWW[0]]['Slot']} LIKE '%{$WW}%' " : $_W;
								$_W = ($_kWW[1] == 'NOTLIKE') ? " value{$this->PATTERN[$kType][$_kWW[0]]['Slot']} NOT LIKE '{$WW}' " : $_W;

								$_W = ($_kWW[1] == '<>') ? " value{$this->PATTERN[$kType][$_kWW[0]]['Slot']} <> '{$WW}' " : $_W;
								$_W = ($_kWW[1] == '=') ? " value{$this->PATTERN[$kType][$_kWW[0]]['Slot']} = '{$WW}' " : $_W;
								$_W = ($_kWW[1] == '>=') ? " value{$this->PATTERN[$kType][$_kWW[0]]['Slot']} >= '{$WW}' " : $_W;
								$_W = ($_kWW[1] == '<=') ? " value{$this->PATTERN[$kType][$_kWW[0]]['Slot']} <= '{$WW}' " : $_W;
								$_W = ($_kWW[1] == '>') ? " value{$this->PATTERN[$kType][$_kWW[0]]['Slot']} > '{$WW}' " : $_W;
								$_W = ($_kWW[1] == '<') ? " value{$this->PATTERN[$kType][$_kWW[0]]['Slot']} < '{$WW}' " : $_W;
							}

							#$W_ATT .= (($W_ATT) ? ' AND ' : '') . " EXISTS (SELECT dtmp.id FROM wp_data_att WHERE dtmp.id = id AND dtmp.type_id = type_id AND attribute_id IN ('{$_kWW[0]}') AND {$_W} ) ";
							if(isset($this->PATTERN[$kType][$_kWW[0]]['Row']) && isset($this->PATTERN[$kType][$_kWW[0]]['Slot'])) {
								$W_ATT .= (($W_ATT) ? ' AND ' : '') . " EXISTS (SELECT dtmp.id FROM wp_data_att_slot WHERE dtmp.id = id AND dtmp.type_id = type_id AND row_id = '{$this->PATTERN[$kType][$_kWW[0]]['Row']}' AND attribute_id{$this->PATTERN[$kType][$_kWW[0]]['Slot']} IN ('{$_kWW[0]}') AND {$_W} ) AND path_hash = '{$F['PATH_HASH']}' ";
							} else {
								$W_ATT .= (($W_ATT) ? ' AND ' : '') . " EXISTS (SELECT dtmp.id FROM wp_data_att WHERE dtmp.id = id AND dtmp.type_id = type_id AND attribute_id IN ('{$_kWW[0]}') AND {$_W} ) AND path_hash = '{$F['PATH_HASH']}' ";
							}
						}

						} elseif ($this->PATTERN[$kType]['D'][$kWW]??false) { #Überprüft Filter von Solcher Strucktur: $D2['PLATFORM']['SUPPLIER']['W'][0]['ARTICLE']['W'][0]['ID'] = ['A1'];
							#ToDo100: Rekursive erweitern, damit auch weitere unter Where Abfragen mit abgefragtn werden! Und nicht nur IDs
							$WW2 = (is_array($WW['W'][0]['ID'])) ? implode("','", $WW['W'][0]['ID']) : $WW['W'][0]['ID']; #Prüfe ob Array übergeben wurde
							#$W_ID .= (($W_ID) ? ' AND ' : '') . " dtmp.id IN ('{$WW}') AND dtmp.type_id = '{$kType}' AND path_hash = '{$F['PATH_HASH']}' "; 
							$W_ID .= (($W_ID) ? ' AND ' : '') . " EXISTS (SELECT id FROM wp_data WHERE id IN ('{$WW2}') AND type_id = '{$kWW}' AND parent_data_id = dtmp.id AND parent_type_id = dtmp.type_id ) "; #ToDo: AND path_hash = '{$F['PATH_HASH']}'
							
						}
					}
					$W1 .= ($W_ID) ? " {$W_ID} " : '';
					#$W1 .= ($W_ATT) ? (($W1) ? ' AND ' : '') . " EXISTS (SELECT dtmp.id FROM wp_data_att WHERE dtmp.id = id AND dtmp.type_id = type_id AND {$W_ATT}) " : '';
					$W1 .= ($W_ATT) ? (($W1) ? ' AND ' : '') . " {$W_ATT} " : '';
					
					#$W1 .= ($W_toID) ? (($W1) ? ' AND ' : '') . " dtmp.id IN (SELECT id FROM wp_data WHERE {$W_toID} ) " : '';
					$W1 .= ($W_toID) ? (($W1) ? ' AND ' : '') . " {$W_toID} " : '';
					$W .= $W1;
					$W .= ") ";
				}
				$W .= ($W) ? ") " : '';

				$L = (isset($F[$kType]['L']['STEP'])) ? "LIMIT 0,{$F[$kType]['L']['STEP']}" : $L;
				$L = (isset($F[$kType]['L']['START']) && $F[$kType]['L']['STEP']) ? "LIMIT {$F[$kType]['L']['START']},{$F[$kType]['L']['STEP']}" : $L;

				if ($F[$kType]['O']??false) {
					foreach ((array) $F[$kType]['O'] as $kR => $R) {
						foreach ((array) $R as $key => $value) {
							if ($_kWW[0] == 'ID') {
								$O .= (($O) ? ',' : '') . " dtmp.id {$value} ";
							} elseif ($this->PATTERN[$kType][$key]['Type'] == 'id') {
								$O .= (($O) ? ',' : '') . " (SELECT id FROM wp_data WHERE dtmp.id = id AND dtmp.type_id = type_id AND dtmp.path_hash = path_hash AND parent_type_id = '{$key}' ) {$value}";
							} else {
								if(isset($this->PATTERN[$kType][$key]['Row']) && isset($this->PATTERN[$kType][$key]['Slot'])) {
									#$O .= (($O) ? ',' : '') . " (SELECT ".(( in_array($this->PATTERN[$kType][$key]['Type'], ['number', 'checkbox']))? " CAST(value{$this->PATTERN[$kType][$key]['Slot']} AS REAL)":" value{$this->PATTERN[$kType][$key]['Slot']} ")." FROM wp_data_att_slot WHERE dtmp.id = id AND dtmp.type_id = type_id AND row_id = '{$this->PATTERN[$kType][$key]['Row']}' AND attribute_id{$this->PATTERN[$kType][$key]['Slot']} = '{$key}' ) {$value}";
									$O .= (($O) ? ',' : '') . " (SELECT sort{$this->PATTERN[$kType][$key]['Slot']} FROM wp_data_att_slot WHERE dtmp.id = id AND dtmp.type_id = type_id AND dtmp.path_hash = path_hash AND row_id = '{$this->PATTERN[$kType][$key]['Row']}' AND attribute_id{$this->PATTERN[$kType][$key]['Slot']} = '{$key}' ) {$value}";
								}
								else {
									#$O .= (($O) ? ',' : '') . " (SELECT ".(( in_array($this->PATTERN[$kType][$key]['Type'], ['number', 'checkbox']))? " CAST(value AS REAL)":" value ")." FROM wp_data_att WHERE dtmp.id = id AND dtmp.type_id = type_id AND attribute_id = '{$key}' ) {$value}";
									$O .= (($O) ? ',' : '') . " (SELECT sort FROM wp_data_att WHERE dtmp.id = id AND dtmp.type_id = type_id AND dtmp.path_hash = path_hash AND attribute_id = '{$key}' ) {$value}";
								}
								
							}
						}
					}
					$O = ($O) ? "ORDER BY {$O}" : '';
				}

				if(!isset($F[$kType]['L']['STEP']) || $F[$kType]['L']['STEP'] > 0) {
						$qry = $this->SQL->query("SELECT dtmp.id, dtmp.type_id, data 
										FROM wp_data_cache dtmp
										WHERE dtmp.type_id = '{$kType}'
										{$W} {$O} {$L}");
					

					while ($a = $qry->fetchArray(SQLITE3_NUM)) {
						$D[$a[1]]['D'][$a[0]] = array_replace_recursive((array)($D[$a[1]]['D'][$a[0]]??[]),(array) json_decode($a[2], 1));
						
						#Baut PARENT referenz auf, erforderlich für reqursive Aufruf für die Performance
						
						foreach ((array) $this->PATTERN[$a[1]] as $_kATT => $_ATT) {
							if (($_ATT['Type']??false) == 'id') {

								#Aggregierungsfunktionen ================
								if ($F[$a[1]]['A']??false) {
									foreach ((array) $F[$a[1]]['A'] as $kAG => $AG) {
										if (in_array('SUM', (array)$AG) || in_array('AVG', (array)$AG)) { #AVG benötigt SUM
											$D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['SUM']??=0;
											$D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['SUM'] += $D[$a[1]]['D'][$a[0]][$kAG];
										}
										if (in_array('COUNT', (array)$AG) || in_array('AVG', (array)$AG)) { #AVG benötigt COUNT
											$D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['COUNT']??=0;
											$D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['COUNT']++;
										}
										if (in_array('MIN', (array)$AG)) {
											$D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['MIN']??=0;
											$D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['MIN'] = (is_null($D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['MIN']) || $D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['MIN'] > $D[$a[1]]['D'][$a[0]][$kAG]) ? $D[$a[1]]['D'][$a[0]][$kAG] : $D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['MIN'];
										}
										if (in_array('MAX', (array)$AG)) {
											$D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['MAX'] ??=0;
											$D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['MAX'] = (is_null($D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['MAX']) || $D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['MAX'] < $D[$a[1]]['D'][$a[0]][$kAG]) ? $D[$a[1]]['D'][$a[0]][$kAG] : $D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['MAX'];
										}
										if (in_array('AVG', (array)$AG)) {
											$D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['AVG']??=0;
											$D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['AVG'] =
											$D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['SUM'] / $D[$_kATT]['D'][$D[$a[1]]['D'][$a[0]][$_kATT]]['CHILD'][$a[1]]['A'][$kAG]['COUNT'];
										}
									}
								}
								#================================================
								
								$D[$_kATT]['D'][ ($D[$a[1]]['D'][$a[0]][$_kATT]??null) ]['CHILD'][$a[1]]['D'][$a[0]] = &$D[$a[1]]['D'][$a[0]];

							}
							
						}
					}
				}
				
				#gibt Count aus
				#ToDo: count in Level2 Cache verlagern. Die Tabelle wp_data_child wäre somit überflüssig
				$qry = $this->SQL->query("SELECT dtc.id, dtc.type_id, child_type_id, child_count, child_path_hash
											FROM wp_data_child dtc
											WHERE child_type_id = '{$kType}' AND child_path_hash = '{$F['PATH_HASH']}' 
											AND EXISTS (SELECT 1 FROM wp_data dtmp WHERE dtc.id = dtmp.parent_data_id AND dtc.type_id = dtmp.parent_type_id AND dtc.child_path_hash = dtmp.path_hash {$W})
										");
				while ($a = $qry->fetchArray(SQLITE3_NUM)) {
					if($a[1]) {
						$D[ $a[1] ]['D'][ $a[0] ]['CHILD'][ $a[2] ]['COUNT'] = $a[3]; #für Kinder
					}
					else {
						$D[ $a[2] ]['COUNT'] = $a[3]; #für Parent
					}
					
				}
				
			}
		}
	}


		/**
	 * $D = &$D
	 * $F = optional können Filter übergabe werden um die Ausgabe einzuschrenken
	 */
	function get_object_reqursive(&$D = null, &$F = null)
	{
		static $stLevel = 0;
		$this->get_object($D, $F);

		$savePatern = $this->PATTERN;
		foreach ((array) $F as $kType => $Type) {
			
			if ($this->PATTERN[$kType]??null) { #Ist Type in Pattern vorhanden?
				
				$this->PATTERN = $savePatern[$kType]['D']??=[];

				foreach ((array) $this->PATTERN as $kPAT => $PAT) { #Durchlaufe alle Pattern
					#echo  "{$kPAT} > ";
					if (isset($Type[$kPAT])) { #ist allgemeine Bedinung vorhande PLATFORM.WAREHOUSE
						$f = $d = null; #$d muss je Durchgang resetet werden!
						
						#$d[$kPAT] = &$D[$kType][$kPAT];##ToDo: Prüfen ob übergabe Referenz keine Probleme bereitet.
						###$d[$kPAT] = $D[$kType][$kPAT]; #Übergebe W-Bedinung als unter Abfrage  #ToDo: Wichtig, wird diese über $F Übergeben oder?
						$f[$kPAT] = $Type[$kPAT];
						
						#Setze Filter nach Vater ID
						if($D[$kType]['D']??null) {
							foreach ((array) $D[$kType]['D'] as $kDat => $Dat) {
								$f[$kPAT]['W'][0][$kType][] = $kDat;#Übergebe ID vom Vater
								$this->PATTERN[$kPAT][$kType] = ['Type' => 'id']; #Füge temporär Parent mit Id, damit in der höheren Ebene die ID zum Parent gespeichert werden kann.
							}
						}
						#echo '-A----------------<br>';
						#print_r($d[$kPAT]['W']);
						#echo '-E---------------<br>';
						$f['PATH_HASH'] = hash("crc32b", ($F['PATH_HASH']??'').$kType);#Übergabe path_hash
						$stLevel++;
						$this->get_object_reqursive($d,$f);
						$stLevel--;

						#Daten Zuordnung
						if($d[$kType]['D']??null){
							foreach ((array) $d[$kType]['D'] as $kATT2 => $ATT2) {
								$D[$kType]['D'][$kATT2] = array_replace_recursive((array) ($D[$kType]['D'][$kATT2]??[]), (array) $d[$kType]['D'][$kATT2]['CHILD']);
								
							}
						}
						
					}
				}
			}
			$this->PATTERN = $savePatern; #Setze Pattern auf Ursprung zurück
		}

		
	}
}