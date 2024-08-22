<?php
/** DOC
@version 2.07
@license https://opensource.org/license/lgpl-3-0 GNU Public License
 
*#Pattern Beispiel: 
  $D['PATTERN']['PLATFORM']['D']['MANUFACTURER'] = [
	'Active'		=> ['Type' => 'checkbox'],
	'ParentId'		=> ['Type' => 'id', 'ForeignKey' => 1], #ForeignKey: Beim Pattern kann = 1 übergeben werden. Dadurch kann man ein Feld als Fremdschlüssel kennzeichnen. Bei der Ausgabe wird PARENT->CHILD ausgabe generiert, so dass auch nach Fremdschlüssel selekitert wird.
	];
 ## Mögliche Attribute: #ToDo: Attribute umsetzen:
 - Type : id, string, number
 - Min : (optional) bei string, gibt mindest Buchstaben an. bei numbers, bestimmt mindest Wert, bereich z.B: -100 oder -100.0000 dann ergibt ein float mit 4 nachkommastellen. ist Min Angegeben, so wird draus ein Pflichtfeld.
 - Max : (optional) bei string, gibt maximal Buchstaben an. bei numbers, bestimmt maximal Wert, bereich z.B: 1000 oder 100.0000 dann ergibt ein float mit 4 nachkommastellen.

*#SET Beispiel:
  $d['WAREHOUSE']['D']['W1'] = [
	'Active'	=> 1, //['Value' => -2 ],
	'Title'		=> 'Warehouse10', //['Value' => 'Warehouse1'],
	];
	$d['WAREHOUSE']['D']['W1']['STORAGE']['D']['W1S1'] = [
		'Active'	=> 0,
		'Title' => 'StorageA', //Wird '' oder NULL übergeben, so wird das Attribut gelöscht. Nur nicht bei Type=ForeignKey Da werden auch leere Felder übergeben und nur bei NULL gelöscht
	];
	$d['WAREHOUSE']['D']['W1']['STORAGE']['D']['W1S2'] = [
		'Active'	=> 0,
		'Title' => 'StorageB',
	];
	$CData->set_object_reqursive($d);

*#GET Beispiel:
	$dd['WAREHOUSE'] = []; // gib nur WAREHOUSE konnen aus
	$dd['WAREHOUSE']['STORAGE']['ARTICLE_STOCK'] = []; // Gib bis Ebene 3 die drei Knoten aus.
	$dd['WAREHOUSE']['W'][0]['ID'] = 'W1'; // Filtere Nach WAREHUSE ID W1
	$dd['WAREHOUSE']['STORAGE']['W'][0]['Active'] = [1]; // Gib nur Active STORAGE aller Warehouse aus
	$dd['STORAGE']['W'][0]['Title']['>'] = 'R002'; // Gib alle ab R002 aus. Möglich: [NOTIN|LIKE-%|LIKE%-|LIKE%%|>|>=|<=|<]
	$dd['STORAGE']['W'][0]['Title'] = ['R001','R002']; // Gib mit Tittle R001,T002 Datensätze aus
	$dd['STORAGE']['L']['START'] = 1; // Begine ab 1. Gilt für Storage, kann auch für unteren Knoten angegeben werden
	$dd['STORAGE']['L']['STEP'] = 2; // maximal 2 Datensätze ausgeben. Gilt für Storage, kann auch für unteren Knoten angegeben werden
	$dd['WAREHOUSE']['STORAGE']['ARTICLE_STOCK']['A']['Stock'] = ['SUM','COUNT','AVG','MIN','MAX']; #Aggregate COUNT,SUM,AVG,MIN,MAX Ermittelt für das Feld Stock. Ist nur für nummerische Werte möglich
	$dd['PLATFORM']['SUPPLIER']['W'][0]['ARTICLE']['W'][0]['ID'] = ['A1']; #Kann Filter einer höeren Ebene auf die untere Ebene Anwenden. Z.B: wenn ein Supplier gesucht ist, dieser nur Artiekl von ID A1 beinhalte.
	$dd['PLATFORM']['SUPPLIER']['W'][0]['ARTICLE']['W'][0]['ATTRIBUTE']['W'][0]['ID'] = ['ATT1','ATT2']; #Gib alle Supplier diese Artikel mit Attribut-ID ATT1 oder ATT2 beinhalten.

	#Nachschlage Tabelle am besten mit Punkt (optional) als Standard nutzen.
	$dd['ARTICLE']['ARTICLE.ATTRIBUTE']['D'][ARTICLE_ID.ATTRIBUTE_ID]['Title'] = 'Hallo';

	#Sortieren:
	$dd['WHAREGOUSE]['O'][0]['Stock'] = 'DESC';
	Sortieren Speziall Befehle: ID, UTIMESTAMP, ITIMESTAMP

===============================
##Changelog:
#2.07
+ backup Funktion hinzugefügt, zur erstellung von backups der Datenbanken. $CData->backup();
~ Fix: Falsche berechnung von Count Ausgebe behoben.
~ Fix: Falsche berechnung von Count bei einschrenkungen von Objekten.
#2.06
+ LGPL Lizensiert.
~ Überflüssige Tabelle wp_data_att_slot entfernt.
+ namespace wp; hinzugefügt
~ Überflüssige Tabelle wp_data_child entfernt.
! Sortieren nach einem Feld ist nicht möglich, behoben.
#2.05
+ Sortieren nach UTIMESTAMP und ITIMESTAMP hinzugefügt.
#2.04
+ Cache Level2 - Klasse für den Cache erstellt. Dadurch werden alle Abfragen an get_object gechacht und durch set_oject wird dieser bereinigt
! Fix: Wenn ein Zweig gelöscht wird, wurden die Attribute unter wp_data_att nicht mit gelöscht.
#2.03
! Fix: WHERE Abfrage überarbeitet, IDs können nun auch in OR Kombinationen unabhängig verwendet werden
~ Funktionen get_object_recursive und set_object_recursive in get_object und set_object umbennant.
+ ForeignKey: Beim Pattern kann = 1 übergeben werden. Dadurch kann man ein Feld als Fremdschlüssel kennzeichnen. Bei der Ausgabe wird PARENT->CHILD ausgabe generiert, so dass auch nach Fremdschlüssel selekitert wird.
~ Where Bedinungn überarbeitet. Die Where Bedinung kann nun Rekursive genutzt werden z.B: $D['ARTICLE']['W'][0]['ATTRIBUTE']['W'][0]['Value'] = 'test';
~ Where Oeration IN,NOTIN, LIKE, LIKE%,LIKE%%,LIKE-%,LIKE%-,>,<,<=,<=,<>,!=,= hinzugefügt.
#2.02
! Fix: kleine Fehler behebungen
! Fix: get_object hat Daten diese bereits an die Funktion übergeben wurden, verschluckt.
#2.01
~ Überarbeitung und vereinfachung der get und set Funktionen - Code
~ Performance Optimierung
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

@todo
BUG: IN Tabelle wp_data_att ist womöglich unter der Spalte path_hash Parent hash ID hinterlegt, so muss in  => parent_path_hash umbennant werden!.

*/
namespace wp;
class CData
{
	private $SQL;
	private $CCache;
	private $Param;
	private $BackupDestinationPath;
	private $BackupPassword;

	/**
	 * 'PATTERN'	=> Pattern
	 * 'DB'			=> [FILENAME,FLAGS] Datenbank Zugang
	 * 'BACKUP'		=> [DestinationPath] (optional)
	*/
	function __construct($P=null)
	{
		if($P['DB']) {
			if(file_exists($P['DB']['FILENAME'])) {
				$this->SQL = new \SQLite3($P['DB']['FILENAME'], ($P['DB']['FLAGS']??SQLITE3_OPEN_READWRITE) );
				$this->CCache = new CCache([ 'DB' => ['FILENAME' => $P['DB']['FILENAME'].'.cache' ] ]);
			} else {
				$this->SQL = new \SQLite3($P['DB']['FILENAME']);
				$this->CreateDB();
			}
			if($P['PRAGMA']??false) {
				$this->SQL->exec($P['PRAGMA']);
			} else {
				$this->SQL->exec("
				PRAGMA busy_timeout = 5000;		PRAGMA cache_size = -2000;
				PRAGMA synchronous = 1;			PRAGMA foreign_keys = ON;
				PRAGMA temp_store = MEMORY;		PRAGMA default_temp_store = MEMORY;
				PRAGMA read_uncommitted = true;	PRAGMA journal_mode = wal;
				PRAGMA wal_autocheckpoint=1000; PRAGMA encoding = 'UTF-8'; 
				");
			}
		} else { exit('kein DB-Übergabe Parameter!'); }

		$this->BackupDestinationPath = ($P['BACKUP']['DestinationPath'])??'backup/';
		

		$this->BackupPassword = $P['BACKUP']['BackupPassword'];
			
		$this->PATTERN = $P['PATTERN'];
		$this->Param['DB'] = $P['DB'];
		$this->refreshCacheObjeckt = [];
	}

	private function CreateDB() {
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

			CREATE TABLE IF NOT EXISTS "wp_data_cache" (
			"id" text NOT NULL,
			"type_id" text NOT NULL,
			"parent_path_hash" integer NOT NULL,
			"path_hash" integer NOT NULL,
			"data" blob NULL,
			"utimestamp" integer NULL DEFAULT (cast(strftime(\'%s\', \'now\') as int)),
			"itimestamp" integer NULL DEFAULT (cast(strftime(\'%s\', \'now\') as int)),
			PRIMARY KEY ("id", "type_id", "parent_path_hash")
			);
			CREATE INDEX IF NOT EXISTS "wp_data_tmp_id" ON "wp_data_cache" ("id");
			CREATE INDEX IF NOT EXISTS "wp_data_tmp_type_id" ON "wp_data_cache" ("type_id");
			CREATE INDEX IF NOT EXISTS "wp_data_tmp_utimestamp" ON "wp_data_cache" ("utimestamp");
			CREATE INDEX IF NOT EXISTS "wp_data_tmp_parent_path_hash" ON "wp_data_cache" ("parent_path_hash");
			CREATE INDEX IF NOT EXISTS "wp_data_tmp_path_hash" ON "wp_data_cache" ("path_hash");
		'); 
	}
	function getPattern(&$D) {
		$D['PATTERN'] = $this->PATTERN;
	}

	/**
	 * Erstellt eine Sicherung der Datenbank
	 */
	function backup() {
		if($this->Param['DB']) {
			#Erstelle Unter Verzeichnis
			if(!is_dir($this->BackupDestinationPath)) {
				mkdir($this->BackupDestinationPath,0777,true);
			}

			$datetime = date("YmdHis");
			#Backup der DB
			$path = pathinfo($this->Param['DB']['FILENAME']);
			$backup = new \SQLite3("{$this->BackupDestinationPath}{$datetime}_{$path['basename']}");
			$this->SQL->backup($backup);
			$backup->exec("VACUUM");
			$backup->close();

			#Ziped
			$zip = new \ZipArchive();
			if( $zip->open("{$this->BackupDestinationPath}{$datetime}_{$path['basename']}.zip", \ZipArchive::CREATE) ) {
				$zip->addFile("{$this->BackupDestinationPath}{$datetime}_{$path['basename']}","{$datetime}_{$path['basename']}");
				if($this->BackupPassword) {
					$zip->setEncryptionName("{$datetime}_{$path['basename']}", \ZipArchive::EM_AES_256, $this->BackupPassword);
				}
				$zip->close();

				#Lösche Sicherungskopie
				unlink("{$this->BackupDestinationPath}{$datetime}_{$path['basename']}");
			}
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

	function set_object(&$D = null, $Parent_Hash='', $Parent_Type = '', $Parent_Id = '' ) {
		static $stLevel = 0;
		
		static $IU_DATA = '';
		static $D_DATA = '';
		static $IU_DATA_ATT  = '';
		static $D_DATA_ATT  = '';
		static $_RefrechCache = [];
		
		$savePatern = $this->PATTERN;
		foreach ((array) $D AS $kType => $Type) { ##[PlATFORM]
			
			if (($this->PATTERN[$kType]??false) && is_array($Type)) { #Prüfe in Pattern ob diese erlaubt sind
				
				$this->CCache->flush(['Tag' => "{$kType}/"]); #Cache bereinigen #ToDo: prüfen ob in der ebene veräderungen vorgenohmen wurden und nicht einfach pauschal leeren.
				
				foreach ((array) $Type['D'] AS $kSup => $Sup) { #PlATFORM.D[x]
				
					$_Parent_Hash = hash("crc32b", $Parent_Hash.$kType.$kSup);
					$_RefrechCache[$Parent_Hash] = true; #aktuallisiere anhand des path_hash
					
					if(($Sup['Active']??false) != -2 ) { #Insert/ Update

						$IU_DATA .= (($IU_DATA) ? ',' : '') . "('{$kSup}','{$kType}','{$Parent_Hash}','{$Parent_Type}','{$Parent_Id}')";
						
						
						foreach ((array) $Sup as $kATT => $ATT) { #PlATFORM.D.x.[ATTRIBUTE]
							
								if (is_array($ATT) && isset($ATT['D'])) { #ist ein ATT eine weitere Ebene? PlATFORM.D.x.ATTRIBUTE.D.[x]
									$d[$kATT]['D'] = &$ATT['D'];
									##$d[$kATT]['D'][$kSubATT][$kType] = $kSup; 
									
									
									$stLevel++;
									$this->PATTERN = $savePatern[$kType]['D']??=[];
									$this->set_object($d,$_Parent_Hash,$kType,$kSup);
									$this->PATTERN = $savePatern; #Setze Pattern auf Ursprung zurück
									$stLevel--;
								}
								elseif($this->PATTERN[$kType][$kATT]??false) {
									if($ATT != '' || ($ATT !== NULL && $this->PATTERN[$kType][$kATT]['ForeignKey'] == 1) ) {
										$IU_DATA_ATT .= (($IU_DATA_ATT) ? ',' : '') . "('{$kSup}','{$kType}','{$Parent_Hash}','{$kATT}'";#Setze Attribute
										$IU_DATA_ATT .= (isset($ATT)) ? ",'".$this->_Value2SortHash($ATT)."'" : ",NULL";
										$IU_DATA_ATT .= (isset($ATT)) ? ",'".$this->SQL->escapeString($ATT)."'" : ",NULL";
										$IU_DATA_ATT .= ")";
									}
									else {
										$D_DATA_ATT .= (($D_DATA_ATT) ? ' OR ' : '') ." (id = '{$kSup}' AND type_id = '{$kType}' AND attribute_id = '{$kATT}' AND path_hash = '{$Parent_Hash}' )";
									}
									
								}
							
						}
						
						
					}
					else { #Delete
						$D_DATA .= (($D_DATA) ? ' OR ' : '') . " (id = '{$kSup}' AND type_id = '{$kType}' AND parent_type_id = '{$Parent_Type}' AND parent_data_id = '{$Parent_Id}' AND path_hash = '{$Parent_Hash}')";
						$D_DATA_ATT .= (($D_DATA_ATT) ? ' OR ' : '') ." (id = '{$kSup}' AND type_id = '{$kType}' AND path_hash = '{$Parent_Hash}' )"; #lösche alle Attribute
					}
				}
			}
			
			
		}
		
		if($stLevel == 0) {
			#1. Speichere in der DB
			#echo $IU_DATA;echo "<br>";
			#echo $IU_DATA_ATT;
			if ($IU_DATA??false) {
				$this->SQL->exec("INSERT INTO wp_data (id, type_id, path_hash, parent_type_id, parent_data_id) VALUES {$IU_DATA} 
							ON CONFLICT(id, type_id, path_hash) DO UPDATE SET
								parent_data_id =			CASE WHEN excluded.parent_data_id IS NOT NULL	AND ifnull(parent_data_id,'') <> excluded.parent_data_id		THEN excluded.parent_data_id ELSE parent_data_id END,
								parent_type_id =			CASE WHEN excluded.parent_type_id IS NOT NULL	AND ifnull(parent_type_id,'') <> excluded.parent_type_id		THEN excluded.parent_type_id ELSE parent_type_id END,
								utimestamp =	CASE WHEN 
													excluded.parent_type_id IS NOT NULL	AND ifnull(parent_type_id,'') <> excluded.parent_type_id
												THEN cast(strftime('%s', 'now') as int) ELSE utimestamp END
							");
			}
			if ($IU_DATA_ATT??false) {
				$this->SQL->query("INSERT INTO wp_data_att (id, type_id,path_hash, attribute_id, sort, value ) VALUES {$IU_DATA_ATT} 
							ON CONFLICT(id,  type_id, path_hash, attribute_id) DO UPDATE SET
								value =			CASE WHEN excluded.value IS NOT NULL	AND ifnull(value,'') <> excluded.value		THEN excluded.value ELSE value END,
								sort =			CASE WHEN excluded.sort IS NOT NULL	AND ifnull(sort,'') <> excluded.sort		THEN excluded.sort ELSE sort END,
								utimestamp =	CASE WHEN 
													excluded.value IS NOT NULL	AND ifnull(value,'') <> excluded.value
												THEN cast(strftime('%s', 'now') as int) ELSE utimestamp END
							");
			}
			#2. Lösche in der DB
			if ($D_DATA??false) {
				$this->SQL->query("DELETE FROM wp_data WHERE {$D_DATA}");
			}
			if ($D_DATA_ATT??false) { #Lösche leeres Attribut
				$this->SQL->query("DELETE FROM wp_data_att WHERE {$D_DATA_ATT}");
			}
			#Lösche weitere unter Ebenen. 'ToDo: Je mehr Ebenen, desto heufiger muss diese ausgeführt werden um entgültig zu bereinigen! D.h. es kann erst beim nächsten Delete restlichen Daten von anderen Delete beseitigen.
			$this->SQL->query("DELETE FROM wp_data AS dt2 WHERE 
								NOT EXISTS (SELECT 1 FROM wp_data WHERE dt2.parent_data_id = id AND  dt2.parent_type_id  = type_id)
								AND parent_data_id <> ''"); #Lösche RefIds
			#Lösche verwaiste Kinder Zweige aus wp_data_att ToDo: Performance Problem
			$this->SQL->query("DELETE FROM wp_data_att AS dta2 WHERE 
								NOT EXISTS (SELECT 1 FROM wp_data WHERE dta2.id = id AND dta2.type_id = type_id )
			");
			
			#3. invalidiere Cache
			$this->_set_cache($_RefrechCache);
		}
	}
	
	/**
	 * repariert die wp_data_cache bzw. stellt wieder her.
	 */
	function repair() {

			#2. Selektiere Datensätze anhand des PathHash
			$qry = $this->SQL->query("SELECT d.id, d.type_id, d.path_hash, attribute_id, value
						FROM wp_data d LEFT JOIN wp_data_att dat ON d.id = dat.id AND d.type_id = dat.type_id AND d.path_hash = dat.path_hash
						WHERE 1
			");
			while ($a = $qry->fetchArray(SQLITE3_ASSOC)) {
				$set_d[$a['path_hash']][$a['type_id']]['D'][$a['id']][$a['attribute_id']] = $a['value'];
			}
			
			#3. Speichere neue Datensätze im Cache ab
			$IU_DATA_ATT = '';
			foreach ((array) $set_d as $kPath => $Path) {
				foreach ((array) $Path as $kType => $Type) {
					foreach ((array) $Type['D'] as $kSup => $Sup) {
						
						$IU_DATA_ATT .= (($IU_DATA_ATT) ? ',' : '') . "('{$kSup}','{$kType}','{$kPath}'";
						
						$json = $this->SQL->escapeString(json_encode($Sup));
						$ChildHash = hash("crc32b", $kPath.$kType.$kSup);
						$IU_DATA_ATT .= ",'{$ChildHash}'";
						$IU_DATA_ATT .= ",'" . str_replace([',"":""','"":"",','"":""','"":null'],'',$json) . "'"; #replace entfernt leere Key Werte
						
						$IU_DATA_ATT .= ")";
					}
				}
			}
			
			if ($IU_DATA_ATT) {
				$this->SQL->query("REPLACE INTO wp_data_cache (id, type_id, parent_path_hash,path_hash,data) VALUES {$IU_DATA_ATT} 
									ON CONFLICT(id, type_id, parent_path_hash) DO UPDATE SET
										data =			CASE WHEN excluded.data IS NOT NULL	AND ifnull(data,'') <> excluded.data		THEN excluded.data ELSE data END,
										utimestamp =	CASE WHEN excluded.data IS NOT NULL	AND ifnull(data,'') <> excluded.data
														THEN cast(strftime('%s', 'now') as int) ELSE utimestamp END
									");
									
			}
	}
	private function _set_cache(&$RefrechCache_PathHash=null) {
		
		if($RefrechCache_PathHash) {
			
			#1. Lösche alte Datensätze anhand des PathHash
			$Keys = implode("','",array_keys($RefrechCache_PathHash));
			$this->SQL->query("DELETE FROM wp_data_cache WHERE parent_path_hash IN ('{$Keys}')");
			
			#2. Selektiere Datensätze anhand des PathHash
			$qry = $this->SQL->query("SELECT d.id, d.type_id, d.path_hash, attribute_id, value
						FROM wp_data d LEFT JOIN wp_data_att dat ON d.id = dat.id AND d.type_id = dat.type_id AND d.path_hash = dat.path_hash
						WHERE d.path_hash IN ('{$Keys}')
			");
			while ($a = $qry->fetchArray(SQLITE3_ASSOC)) {
				$set_d[$a['path_hash']][$a['type_id']]['D'][$a['id']][$a['attribute_id']] = $a['value'];
			}
			
			#3. Speichere neue Datensätze im Cache ab
			$IU_DATA_ATT = '';
			foreach ((array) $set_d as $kPath => $Path) {
				foreach ((array) $Path as $kType => $Type) {
					foreach ((array) $Type['D'] as $kSup => $Sup) {
						
						$IU_DATA_ATT .= (($IU_DATA_ATT) ? ',' : '') . "('{$kSup}','{$kType}','{$kPath}'";
						
						$json = $this->SQL->escapeString(json_encode($Sup));
						$ChildHash = hash("crc32b", $kPath.$kType.$kSup);
						$IU_DATA_ATT .= ",'{$ChildHash}'";
						$IU_DATA_ATT .= ",'" . str_replace([',"":""','"":"",','"":""','"":null'],'',$json) . "'"; #replace entfernt leere Key Werte
						
						$IU_DATA_ATT .= ")";
					}
				}
			}
			
			if ($IU_DATA_ATT) {
				$this->SQL->query("REPLACE INTO wp_data_cache (id, type_id, parent_path_hash,path_hash,data) VALUES {$IU_DATA_ATT} 
									ON CONFLICT(id, type_id, parent_path_hash) DO UPDATE SET
										data =			CASE WHEN excluded.data IS NOT NULL	AND ifnull(data,'') <> excluded.data		THEN excluded.data ELSE data END,
										utimestamp =	CASE WHEN excluded.data IS NOT NULL	AND ifnull(data,'') <> excluded.data
														THEN cast(strftime('%s', 'now') as int) ELSE utimestamp END
									");
									
			}
			
		}
		
	}

	/**
	 * $F = Filer übergabe 
	 * $Pattern = Pattern übergabe
	 * $Level = nur 0 oder garnichts übergeben, wird intern verwendet
	 */
	private function _get_where(&$F,&$Pattern,$Level=0) {
		if($F['W']??false) { #Prüft ob es sich um eine Where anweisung sich handelt
			$W .= ' AND ( ';
			foreach ((array) $F['W'] as $kOR => $OR) { #OR Bedinungen durchlaufen
				$WOR .= ($WOR)? ' OR ( ' : '';
				$WAND = '';
				foreach( (array) $OR AS $kAND => $AND ) { #AND Bedinungen durchlaufen
					if($kAND == 'ID') {
						#$Value = (is_array($AND)) ? implode("','", $AND) : $AND;
						#$WAND .= (($WAND)? ' AND ' : ' ')." dtmp{$Level}.id IN ('{$Value}') ";
						$Value = $this->_get_where_Operations($kAND,$AND,$Level);
						$WAND .= (($WAND)? ' AND ' : ' ')." {$Value} ";
					}
					elseif( in_array($kAND,array_keys((array)$Pattern) ) ) { #Prüfe ob das Attribut auch im Patern enthalten ist. z.B: Active
						#$Value = (is_array($AND)) ? implode("','", $AND) : $AND;
						#$WAND .= (($WAND)? ' AND ' : ' ')." EXISTS (SELECT 1 FROM wp_data_att dt WHERE dtmp{$Level}.id = dt.id AND dtmp{$Level}.type_id = dt.type_id AND dt.attribute_id IN ('{$kAND}') AND dt.value IN ('{$Value}') )";
						$Value = $this->_get_where_Operations($kAND, $AND,$Level);
						$WAND .= (($WAND)? ' AND ' : ' ')." EXISTS (SELECT 1 FROM wp_data_att dt WHERE dtmp{$Level}.parent_path_hash = dt.path_hash AND dtmp{$Level}.id = dt.id AND dtmp{$Level}.type_id = dt.type_id AND dt.attribute_id IN ('{$kAND}') AND ({$Value}) )";
					}
					elseif( in_array($kAND,array_keys((array)$Pattern['D']) )) {# Weitere Ebene Prüfen
						$WAND .= (($WAND)? ' AND ' : ' ')." EXISTS (SELECT 2 FROM wp_data_cache dtmp".($Level+1) ." WHERE dtmp".($Level+1).".parent_path_hash = dtmp{$Level}.path_hash ";
						$WAND .= $this->_get_where($AND,$Pattern['D'][$kAND],$Level+1);
						$WAND .= ' ) ';
					}
				}
				$WOR .= ($WOR)?"{$WAND} ) " : $WAND;
			}
			$W .= " {$WOR} ) ";
		}
		return $W??'';
	}

	/**
	 * Filds = Feld übergabe mit Oeration oder ohne z:B $Filds['ID'] = ['12'] (wird immer IN gewählt wenn kein Operator) Oder $Filds['ID']['IN'] = ['12'] Oder ...['ID']['IN'] = '12' ODER ...['ID']['LIKE%%'] = 'Test' ODER ...['ID']['LIKE%%'] = ['Test', 'Test2']
	 * Operatoren: IN;NOT IN;LIKE;LIKE%-;LIKE-%;LIKE%%;>;<;<=;<=;=;<>;!=
	 * $Level Muss von _get_where das Level übergeben werden!
	 */
	private function _get_where_Operations ($Fild,$Operation,$Level=0) {
		
		$OV = $aloneOV ='';
		$_Fild = ($Fild == 'ID')?"dtmp{$Level}.id":"dt.value";
		foreach ((array)$Operation AS $kOpe => $Ope) {
			$OV .= ($OV)? ' OR ' : '';
			$_Value = '';

			if( in_array($kOpe, ['LIKE','LIKE%','LIKE%','LIKE%%','LIKE%-','LIKE-%']) ) {
				$pre = (in_array($kOpe,['LIKE%','LIKE%%','LIKE%-']))?'%':'';
				$suf = (in_array($kOpe,['LIKE%','LIKE%%','LIKE-%']))?'%':'';
				if(is_array($Ope)) { #Wurde Value als Array übergeben?
					foreach((array)$Ope AS $k => $v) {
						$_Value .= ($_Value?' OR ':'')."{$_Fild} LIKE '{$pre}{$v}{$suf}' ";
					}
				}
				else {
					$_Value = " {$_Fild} LIKE '{$pre}{$Ope}{$suf}'";
				}
				$OV .= " {$_Value} ";
			}
			elseif( in_array($kOpe, ['>','<','>=','<=','<>','!=','=']) ) {
				if(is_array($Ope)) { #Wurde Value als Array übergeben?
					foreach((array)$Ope AS $k => $v) {
						$_Value .= ($_Value?' OR ':'')." {$_Fild} {$kOpe} '{$v}' ";
					}
				}
				else {
					$_Value = "{$_Fild} {$kOpe} '{$Ope}'";
				}
				$OV .= " {$_Value} ";
			}
			elseif( in_array($kOpe, ['IS NULL','NOT NULL']) ) {
				#Todo: Es werden keine Attribute mit NULL angelegt, daher muss bereits NOT EXISTS geprüft werden
				if(is_array($Ope)) { #Wurde Value als Array übergeben?
					foreach((array)$Ope AS $k => $v) {
						$_Value .= ($_Value?' OR ':'')." {$_Fild} {$kOpe} ";
					}
				}
				else {
					$_Value = "{$_Fild} {$kOpe} ";
				}
				$OV .= " {$_Value} ";
			}
			elseif( in_array($kOpe, ['BETWEEN','NOT BETWEEN']) ) {
				#Todo
			}
			elseif( in_array($kOpe, ['IN']) ) {
				if(is_array($Ope)) { #Wurde Value als Array übergeben?
					foreach((array)$Ope AS $k => $v) {
						$_Value .= ($_Value?',':'')."'{$v}'";
					}
				}
				else {
					$_Value = "'{$Ope}'";
				}
				$OV .= " {$_Fild} IN ({$_Value}) ";
			}
			elseif( in_array($kOpe, ['NOTIN', 'NOT IN']) ) {
				if(is_array($Ope)) { #Wurde Value als Array übergeben?
					foreach((array)$Ope AS $k => $v) {
						$_Value .= ($_Value?',':'')."'{$v}'";
					}
				}
				else {
					$_Value = "'{$Ope}'";
				}
				$OV .= " {$_Fild} NOT IN ({$_Value}) ";
			}
			else {
				$aloneOV .= ($aloneOV?',':" {$_Fild} IN (")."'{$Ope}'"; #Ohne Operator übergabe
			}
		}
		if(!$Operation) { #Wenn keine Operation und kein Wert übergeben wurde aber nur ein kFild, dann ist der Value als Leer zu betrachten
			$aloneOV .= ($aloneOV?',':" {$_Fild} IN (")."'{$Ope}'"; #Ohne Operator übergab 
		}
		$O .= ($aloneOV)?$aloneOV.')':'';
		$O .= "{$OV}";
	
	return " {$O} ";
}

	function get_object(&$D = null, &$F=null, $Parent_Hash=[], $Parent_Type = '', $Parent_Id = '') {
		static $stLevel = 0;
		
		if($stLevel==0) {
			$saveD = $D;
			$D = null;
		}
		
			$savePatern = $this->PATTERN;

			foreach((array)$F AS $kType => $Type) {
				#0 Cache Abfrage
				if($stLevel==0) {
					$_sqlmd5 = md5(serialize([$kType => $F[$kType] ]));
					$_CacheData = $this->CCache->get_cache($_sqlmd5);
				}
				if($stLevel==0 && isset($_CacheData[$_sqlmd5])) {
					$d = unserialize($_CacheData[$_sqlmd5]['Data']);
					$D[''][$kType] = $d[$kType];
				}
				else {
					$W1 = $W = $L = $W_ID = '';
					#1. Erstelle Bedinung
					$kHash = ($Parent_Hash)?implode("','",(array)$Parent_Hash[$kType]):"";
					
					
					#Durlaufe alle Felder um Informationen dazu zu erhalten, wei z.B: Type, ForeignKey
					foreach((array) ($savePatern[$kType]??[]) AS $kPF => $PF) {
						#Filtere ForeignKey Felder heraus
						if (isset($PF['ForeignKey'])) {#Ist Fremdschlüssel?
							$ForeignKeys[$kPF] = $PF['ForeignKey'];
						}
					}
					

					if($savePatern[$kType]['D']??null) { #Pürft ob weitere Ebene Vorhanden ist
						$f = $F[ $kType ];
					}

					$W = $this->_get_where($Type,$savePatern[$kType],0);
					$W = " (dtmp0.type_id = '{$kType}' AND dtmp0.parent_path_hash IN ('{$kHash}') ) {$W}";

					$W_count = " (dtmp0.type_id = '{$kType}' AND dtmp0.parent_path_hash IN ('{$kHash}') )"; #Für Count Berechnung, weil Count die absolute Maximum ausgibt ohne Filterung nach einzelnen Objekten.
					
					$L = (isset($F[$kType]['L']['STEP'])) ? "LIMIT 0,{$F[$kType]['L']['STEP']}" : $L;
					$L = (isset($F[$kType]['L']['START']) && $F[$kType]['L']['STEP']) ? "LIMIT {$F[$kType]['L']['START']},{$F[$kType]['L']['STEP']}" : $L;

					#Order By
					$O = '';
					
					if ($Type['O']??false) {
						
						foreach ((array) $F[$kType]['O'] as $kR => $R) {
							foreach ((array) $R as $key => $value) {
								if ($key == 'ID') {
									$O .= (($O) ? ',' : '') . " dtmp0.id {$value} ";
								}
								else if ($key == 'UTIMESTAMP') {
									$O .= (($O) ? ',' : '') . " (SELECT utimestamp FROM wp_data d WHERE dtmp0.parent_path_hash = d.path_hash AND dtmp0.id = d.id AND dtmp0.type_id = d.type_id ) {$value} ";
								}
								else if ($key == 'ITIMESTAMP') {
									$O .= (($O) ? ',' : '') . " (SELECT itimestamp FROM wp_data d WHERE dtmp0.parent_path_hash = d.path_hash AND dtmp0.id = d.id AND dtmp0.type_id = d.type_id ) {$value} ";
								} else {
									$O .= (($O) ? ',' : '') . " (SELECT sort FROM wp_data_att dt WHERE dtmp0.parent_path_hash = dt.path_hash AND dtmp0.id = dt.id AND dtmp0.type_id = dt.type_id AND attribute_id = '{$key}' ) {$value}";
								}
							}
						}
						$O = ($O) ? "ORDER BY {$O}" : '';
					}

					#2. Holle Daten
					$_Hash = null;
					
					##echo "SELECT id, type_id, parent_path_hash,path_hash, data FROM wp_data_cache dtmp0 WHERE {$W} {$O} {$L}<br>";
					$qry = $this->SQL->query("SELECT id, type_id, parent_path_hash,path_hash, data FROM wp_data_cache dtmp0 WHERE {$W} {$O} {$L}");
					while ($a = $qry->fetchArray(SQLITE3_ASSOC)) {
						$D[ $a['parent_path_hash'] ][ $a['type_id'] ]['D'][ $a['id'] ] = array_replace_recursive(
						(array) ($D[ $a['parent_path_hash'] ][ $a['type_id'] ]['D'][$a['id']]??[]),
						(array) json_decode($a['data'], 1));
						
						$d[ $a['path_hash'] ] = &$D[ $a['parent_path_hash'] ][ $a['type_id'] ]['D'][$a['id']];

						#ForeignKey Anhang
						foreach((array)($ForeignKeys??[]) AS $kFK => $FK) {
							if($savePatern[$kType][$kFK]['ForeignKey']) {
								$D[ $a['parent_path_hash'] ][ $a['type_id'] ][ $kFK ]['D'][ $D[ $a['parent_path_hash'] ][ $a['type_id'] ]['D'][$a['id']][ $kFK ] ][ $a['type_id'] ]['D'][$a['id']] 
								= &$D[ $a['parent_path_hash'] ][ $a['type_id'] ]['D'][$a['id']];
							}
						}
						#Kind
						foreach((array)$F[ $a['type_id'] ] AS $kChild => $Child) {
							$_Hash[ $kChild ][] = $a['path_hash'];
						}
					}
					

					#Lese Count aus
					$qry = $this->SQL->query("SELECT count(*) num, type_id, parent_path_hash, path_hash FROM wp_data_cache dtmp0 WHERE {$W_count} GROUP BY parent_path_hash,type_id");
					while ($a = $qry->fetchArray(SQLITE3_ASSOC)) {
						$D[ $a['parent_path_hash'] ][ $a['type_id'] ]['COUNT'] = $a['num'];
					}
					
					#3. gehe in die weitere Ebene
					if(($savePatern[$kType]['D']??null) && $_Hash) { #$_Hash=Wenn Parents nicht vorhaden sind, dann gibt es auch keine kinder
						$stLevel++;
						$this->PATTERN = $savePatern[$kType]['D']??=[];
						$this->get_object($d,$f,$_Hash);
						$this->PATTERN = $savePatern; #Setze Pattern auf Ursprung zurück
						$stLevel--;
					}

					if($stLevel == 0) {
						$_cache[ $_sqlmd5 ] = [
							'Source'	=> serialize([$kType => $F[$kType] ]),
							'Tag'		=> $kType.'/'.implode('/',array_keys( $F[$kType])),
							'Data'		=> serialize([$kType =>  $D[''][$kType]] ), #ToDo: Hier wird nicht nur die aktuelle ausgabe gespeichert, sondern die beigefügten Daten per $D zur Funktion
						];
						$this->CCache->set_cache($_cache);
					}
				}
					
			}
			

		
		if($stLevel == 0) {
			$D = array_replace_recursive((array)$saveD,(array)$D['']);
		}

	}

}

class CCache
{
	private $SQL;
	/**
	 * 'DB'			=> [FILENAME,FLAGS] Datenbank Zugang
	 * ToDo: 
	 * 1. alte ungenutzte Datensätze wieder löschen
	 * 2. TTL um 24 h verlängern wenn der wert abgefragt wird
	 * 3. Cache bereinigen wenn Datensätze sich geändert haben, anhand der tag Werte sollen entweder Werte entfernt werden oder durch Source der Cache aktuallisiert werden!
	 * 
	*/
	function __construct($P=null)
	{
		if($P['DB']) {
			if(file_exists($P['DB']['FILENAME'])) {
				$this->SQL = new \SQLite3($P['DB']['FILENAME'], ($P['DB']['FLAGS']??SQLITE3_OPEN_READWRITE) );
			} else {
				$this->SQL = new \SQLite3($P['DB']['FILENAME']);
				$this->CreateDB();
			}
			if($P['PRAGMA']??false) {
				$this->SQL->exec($P['PRAGMA']);
			} else {
				$this->SQL->exec("
				PRAGMA busy_timeout = 5000;		PRAGMA cache_size = -2000;
				PRAGMA synchronous = OFF;		PRAGMA foreign_keys = ON;
				PRAGMA temp_store = MEMORY;		PRAGMA default_temp_store = MEMORY;
				PRAGMA read_uncommitted = true;	PRAGMA journal_mode = wal;
				");
			}
			$this->Param['DB'] = $P['DB'];
		} else { exit('kein DB-Übergabe Parameter!'); }
	}

	private function CreateDB() {
		$this->SQL->exec('
			CREATE TABLE IF NOT EXISTS "wp_cache" (
			"id" text NOT NULL,
			"source" text NULL,
			"ttl" integer NOT NULL,
			"tag" text NULL,
			"data" blob NULL,
			"itimestamp" integer NULL DEFAULT (cast(strftime(\'%s\', \'now\') as int)),
			PRIMARY KEY ("id")
			);
			
			CREATE INDEX IF NOT EXISTS "wp_cache_id" ON "wp_cache" ("id");
			CREATE INDEX IF NOT EXISTS "wp_cache_tag" ON "wp_cache" ("tag");
			CREATE INDEX IF NOT EXISTS "wp_cache_ttl" ON "wp_cache" ("ttl");
			CREATE INDEX IF NOT EXISTS "wp_cache_itimestamp" ON "wp_cache" ("itimestamp");
			');
	}

	function backup($P=null) {
		if($this->Param['DB']) {
			$datetime = date("YmdHid"); 
			#Backup der DB
			$path = pathinfo($this->Param['DB']['FILENAME']);
			$_path = ($P['DestinationPath'])??$path['dirname'].'/';
			$backup = new \SQLite3("{$_path}{$datetime}_{$path['basename']}");
			$this->SQL->backup($backup);
			$backup->exec("VACUUM");
		}
	}

	/*
	* $P = optional array = [ 'Id', 'Tag' (string|array) (zusätzliche angabe von tag zum gruppieren von Caches) ]
	*/
	function get_cache($id) {
		$_id = (is_array($id))?implode("','",(array)$id):$id;
		$W = " id IN ('{$_id}')";
		#$now = time()-(24*60*60);
		$now = time();
		$qry = $this->SQL->query("SELECT id AS Id, source AS Source, ttl AS Ttl, tag AS Tag, data AS Data FROM  wp_cache WHERE {$W} AND ttl > {$now}" );
		while ($a = $qry->fetchArray(SQLITE3_ASSOC)) {
			$D[$a['Id']] = $a;
		}
		#Erhöhe TTL um weitere 24h wenn diese in der letzten Stunde einmal abgerufen wurde. Dadurch wird häufig abgefragter Cache am leben gehalten.
		$stmt = $this->SQL->prepare("UPDATE wp_cache SET ttl = {$now}+(24*60*60) WHERE {$W} AND ttl > {$now} AND ttl < {$now}+60*60 ");
		$stmt->execute();
	
		return $D??[];
	}

	/*
	* $P[ID] = optional array = [ 'Tag', 'Source', 'Ttl']
	*/
	function set_cache($P) {
		$stmt = $this->SQL->prepare('REPLACE INTO wp_cache (id, ttl, source, tag, data) VALUES (:id, :ttl, :source, :tag, :data)');
		foreach((array)$P AS $kP => $vP){
			$vP['Ttl'] = (isset($vP['Ttl']))?",'{$vP['Ttl']}'":time()+24*60*60;
			$stmt->bindParam(':id', $kP);
			$stmt->bindParam(':ttl', $vP['Ttl'], \SQLITE3_INTEGER );
			$stmt->bindParam(':source', $vP['Source']);
			$stmt->bindParam(':tag', $vP['Tag']);
			$stmt->bindParam(':data', $vP['Data'], SQLITE3_BLOB);
		}
		return $stmt->execute() !== false;
	}

	/* Cache bereinigen */
	function flush($P) {
		if($P['Tag']) {
			$this->SQL->query("DELETE FROM wp_cache WHERE tag LIKE '{$P['Tag']}%' ");
		}
		$time = time();
		$this->SQL->query("DELETE FROM wp_cache WHERE ttl < {$time} ");
	}
}