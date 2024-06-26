## Pattern Beispiel: 
```
$D['PATTERN']['PLATFORM']['D']['MANUFACTURER'] = [
	'Active'		=> ['Type' => 'checkbox'],
	'ParentId'		=> ['Type' => 'id', 'ForeignKey' => 1], 
	];
```
**ForeignKey:** Beim Pattern kann = 1 übergeben werden. Dadurch kann man ein Feld als Fremdschlüssel kennzeichnen. Bei der Ausgabe wird PARENT->CHILD ausgabe generiert, so dass auch nach Fremdschlüssel selekitert wird.

### Mögliche Attribute: <!--(ToDo: Attribute umsetzen:)-->
 - Type : id, string, number
 - Min : (optional) bei string, gibt mindest Buchstaben an. bei numbers, bestimmt mindest Wert, bereich z.B: -100 oder -100.0000 dann ergibt ein float mit 4 nachkommastellen. ist Min Angegeben, so wird draus ein Pflichtfeld.
 - Max : (optional) bei string, gibt maximal Buchstaben an. bei numbers, bestimmt maximal Wert, bereich z.B: 1000 oder 100.0000 dann ergibt ein float mit 4 nachkommastellen.

# SET 
Beispiel:
```
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
```
- UPDATE, 


# GET 
Beispiel:	
```
$dd['WAREHOUSE'] = []; // gib nur WAREHOUSE konnen aus
$dd['WAREHOUSE']['STORAGE']['ARTICLE_STOCK'] = []; // Gib bis Ebene 3 die drei Knoten aus.
```
- Gibt Bedinungslos alle Daten aus.

### WHERE:
```
$dd['WAREHOUSE']['W'][0]['ID'] = 'W1'; // Filtere Nach WAREHUSE ID W1
$dd['WAREHOUSE']['STORAGE']['W'][0]['Active'] = [1]; // Gib nur Active STORAGE aller Warehouse aus
$dd['STORAGE']['W'][0]['Title']['>'] = 'R002'; // Gib alle ab R002 aus. Möglich: [NOTIN|LIKE-%|LIKE%-|LIKE%%|>|>=|<=|<]
$dd['STORAGE']['W'][0]['Title'] = ['R001','R002']; // Gib mit Tittle R001,T002 Datensätze aus
$dd['WAREHOUSE']['STORAGE']['ARTICLE_STOCK']['A']['Stock'] = ['SUM','COUNT','AVG','MIN','MAX']; #Aggregate COUNT,SUM,AVG,MIN,MAX Ermittelt für das Feld Stock. Ist nur für nummerische Werte möglich
$dd['PLATFORM']['SUPPLIER']['W'][0]['ARTICLE']['W'][0]['ID'] = ['A1']; #Kann Filter einer höeren Ebene auf die untere Ebene Anwenden. Z.B: wenn ein Supplier gesucht ist, dieser nur Artiekl von ID A1 beinhalte.
$dd['PLATFORM']['SUPPLIER']['W'][0]['ARTICLE']['W'][0]['ATTRIBUTE']['W'][0]['ID'] = ['ATT1','ATT2']; #Gib alle Supplier diese Artikel mit Attribut-ID ATT1 oder ATT2 beinhalten.
```
- OR: [W][0] | [W][1] | ...  Anweisungen
- AND: [W][0] | [W][0] | ... Anweisungen
- Vergleichsoperatoren: ['W'][0]['Title']['>'] Mäglich: NOTIN|LIKE-%|LIKE%-|LIKE%%|>|>=|<=|<


### Limit:
```
$dd['STORAGE']['L']['START'] = 1;
$dd['STORAGE']['L']['STEP'] = 2;
```
- START: (erforderlich) Begine ab 1. Gilt für Storage, kann auch für unteren Knoten angegeben werden
- STEP: (optional) maximal 2 Datensätze ausgeben. Gilt für Storage, kann auch für unteren Knoten angegeben werden

### Sortieren:
```
$dd['WHAREGOUSE]['O'][0]['Stock'] = 'DESC';
```
Sortieren Speziall Befehle: ID, UTIMESTAMP, ITIMESTAMP