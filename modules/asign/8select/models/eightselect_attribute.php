<?php

/**
 * Attributes manager
 *
 */
class eightselect_attribute extends oxBase
{
    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'eightselect_attribute';

    /** @var array */
    protected $_aFields = [];

    /** @var array */
    protected $_aEightselectFieldsSorted = null;

    /**
     * All fields with additional data
     *
     * @var array
     */
    protected $_aEightselectFields = [
        'sku'            => [
            'propertyFeedName' => 'prop_sku',
            'labelName'    => 'SKU',
            'labelDescr'   => 'Die Sku ist einzigartig, sie enthält Modell, Farbe und Größe',
            'required'     => true,
            'configurable' => true,
            'forUpdate'    => true,
        ],
        'mastersku'      => [
            'propertyFeedName' => 'prop_parentSku',
            'labelName'    => '',
            'labelDescr'   => '',
            'required'     => true,
            'configurable' => false,
            'forUpdate'    => true,
        ],
        'status'         => [
            'propertyFeedName' => 'prop_isInStock',
            'labelName'    => '',
            'labelDescr'   => '',
            'required'     => true,
            'configurable' => false,
            'forUpdate'    => true,
        ],
        'ean'            => [
            'propertyFeedName' => 'prop_ean',
            'labelName'    => 'EAN-Code',
            'labelDescr'   => 'Standardisierte eindeutige Materialnummer nach EAN (European Article Number) oder UPC (Unified Product Code).',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => true,
        ],
        'model'          => [
            'propertyFeedName' => 'prop_model',
            'labelName'    => '',
            'labelDescr'   => '',
            'required'     => true,
            'configurable' => false,
            'forUpdate'    => true,
        ],
        'name1'          => [
            'propertyFeedName' => 'prop_name',
            'labelName'    => '',
            'labelDescr'   => '',
            'required'     => true,
            'configurable' => false,
            'forUpdate'    => true,
        ],
        'name2'          => [
            'labelName'    => 'Alternative Artikelbezeichnung',
            'labelDescr'   => 'Oft als Kurzbezeichnung in Listenansichten verwendet (z.B. "Freizeit-Hemd") oder für Google mit mehr Infos zur besseren Suche',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'kategorie1'     => [
            'labelName'    => '',
            'labelDescr'   => '',
            'required'     => true,
            'configurable' => false,
            'forUpdate'    => false,
        ],
        'kategorie2'     => [
            'labelName'    => '',
            'labelDescr'   => '',
            'required'     => false,
            'configurable' => false,
            'forUpdate'    => false,
        ],
        'kategorie3'     => [
            'labelName'    => '',
            'labelDescr'   => '',
            'required'     => false,
            'configurable' => false,
            'forUpdate'    => false,
        ],
        'streich_preis'  => [
            'propertyFeedName' => 'prop_retailPrice',
            'labelName'    => '',
            'labelDescr'   => '',
            'required'     => true,
            'configurable' => false,
            'forUpdate'    => true,
        ],
        'angebots_preis' => [
            'propertyFeedName' => 'prop_discountPrice',
            'labelName'    => '',
            'labelDescr'   => '',
            'required'     => true,
            'configurable' => false,
            'forUpdate'    => true,
        ],
        'groesse'        => [
            'propertyFeedName' => 'prop_size',
            'labelName'    => 'Größe',
            'labelDescr'   => 'Name der Varianten-Auswahl für "Größe" (siehe "Artikel verwalten" -> "Artikel" -> "Varianten" -> "Name der Auswahl")<br />Mehrfachauswahl möglich',
            'required'     => true,
            'configurable' => true,
            'forUpdate'    => true,
        ],
        'marke'          => [
            'propertyFeedName' => 'prop_brand',
            'labelName'    => '',
            'labelDescr'   => '',
            'required'     => true,
            'configurable' => false,
            'forUpdate'    => true,
        ],
        'bereich'        => [
            'labelName'    => 'Bereich',
            'labelDescr'   => 'Damit können Teilsortimente bezeichnet sein (z.B. Outdoor, Kosmetik, Trachten, Lifestyle)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'rubrik'         => [
            'labelName'    => 'Produktkategorie',
            'labelDescr'   => 'Bezeichnung der Artikelgruppen, die meist so in der Shopnavigation verwendet werden (z.B. Hosen, Jacken, Accessoires, Schuhe)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'abteilung'      => [
            'labelName'    => 'Abteilung',
            'labelDescr'   => 'Einteilung der Sortimente nach Zielgruppen (z.B. Damen, Herren, Kinder)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'kiko'           => [
            'labelName'    => 'KIKO',
            'labelDescr'   => 'Speziell für Kindersortimente: Einteilung nach Zielgruppen (z.B. Mädchen, Jungen, Baby)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'typ'            => [
            'labelName'    => 'Produkttyp / Unterkategorie',
            'labelDescr'   => 'Verfeinerung der Ebene PRODUKTKATEGORIE (z.B. PRODUKTKATEGORIE = Jacken; PRODUKTTYP = Lederjacken, Parkas, Blousons)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'farbe'          => [
            'propertyFeedName' => 'prop_color',
            'labelName'    => 'Farbe',
            'labelDescr'   => 'Name der Varianten-Auswahl für "Farbe" (siehe "Artikel verwalten" -> "Artikel" -> "Varianten" -> "Name der Auswahl")<br />Mehrfachauswahl möglich',
            'required'     => true,
            'configurable' => true,
            'forUpdate'    => true,
        ],
        'farbspektrum'   => [
            'labelName'    => 'Farbspektrum',
            'labelDescr'   => 'Farben sind einem Farbspektrum zugeordnet (z.B. Farbe: Himbeerrot > Farbspektrum: Rot)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'absatzhoehe'    => [
            'labelName'    => 'Absatzhöhe',
            'labelDescr'   => 'speziell bei Schuhen: Höhe des Absatzes (Format mit oder ohne Maßeinheit z.B. 5,5 cm oder 5,5)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'muster'         => [
            'labelName'    => 'Muster',
            'labelDescr'   => 'Farbmuster des Artikels (z.B. uni, einfarbig,  kariert, gestreift, Blumenmuster, einfarbig-strukturiert)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'aermellaenge'   => [
            'labelName'    => 'Ärmellänge',
            'labelDescr'   => 'speziell bei Oberbekleidung: Länge der Ärmel (z.B. normal, extra-lange Ärmel, ärmellos, 3/4 Arm)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'kragenform'     => [
            'labelName'    => 'Kragenform',
            'labelDescr'   => 'speziell bei Oberbekleidung: Beschreibung des Kragens oder Ausschnitts (z.B. Rollkragen, V-Ausschnitt, Blusenkragen, Haifischkragen)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'obermaterial'   => [
            'labelName'    => 'Art Obermaterial',
            'labelDescr'   => 'wesentliches Material des Artikels (z.B. Wildleder, Denim,  Edelstahl, Gewebe, Strick, Jersey, Sweat, Crash)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'passform'       => [
            'labelName'    => 'Passform',
            'labelDescr'   => 'in Bezug auf die Körperform, wird häufig für Hemden, Sakkos und Anzüge verwendet (z.B. schmal, bequeme Weite, slim-fit, regular-fit, comfort-fit, körpernah)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'schnitt'        => [
            'labelName'    => 'Schnitt',
            'labelDescr'   => 'in Bezug auf die Form des Artikels (z.B. Bootcut, gerades Bein, Oversized, spitzer Schuh)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'waschung'       => [
            'labelName'    => 'Waschung',
            'labelDescr'   => 'optische Wirkung des Materials (bei Jeans z.B.  used, destroyed, bleached, vintage)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'stil'           => [
            'labelName'    => 'Stil',
            'labelDescr'   => 'Stilrichtung des Artikels (z.B.  Business, Casual,  Ethno, Retro)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'sportart'       => [
            'labelName'    => 'Sportart',
            'labelDescr'   => 'speziell bei Sportartikeln (z.B. Handball, Bike, Bergsteigen)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'detail'         => [
            'labelName'    => 'Detail',
            'labelDescr'   => 'erwähnenswerte Details an Artikeln (z.B. Reißverschluss seitlich am Saum, Brusttasche, Volants, Netzeinsatz, Kragen in Kontrastfarbe)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'auspraegung'    => [
            'labelName'    => 'Ausführung & Maßangaben',
            'labelDescr'   => 'speziell für Sport und Outdoor. Wichtige Informationen, die helfen, den Artikel in das Sortiment einzuordnen (Beispiele: bei Rucksäcken: Volumen "30-55 Liter"; bei Skistöcken: Größenangaben in Maßeinheit "Körpergröße 160 bis 175cm")',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'baukasten'      => [
            'labelName'    => 'Baukasten',
            'labelDescr'   => 'SKU für eine direkte Verbindung zu 1:1  zusammengehörigen Artikeln',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'eigenschaft'    => [
            'labelName'    => 'Eigenschaft / Einsatzbereich',
            'labelDescr'   => 'speziell für Sport und Outdoor. Hinweise zum Einsatzbereich (Bsp. Schlafsack geeignet für Temparaturbereich 1 °C bis -16 °C, kratzfest, wasserdicht)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'fuellmenge'     => [
            'labelName'    => 'Füllmenge',
            'labelDescr'   => 'bezieht sich auf die Menge des Inhalts des Artikels (z.B. 200ml; 0,5 Liter, 3kg, 150 Stück)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'funktion'       => [
            'labelName'    => 'Funktion',
            'labelDescr'   => 'beschreibt Materialfunktionen und -eigenschaften (z.b. schnelltrocknend, atmungsaktiv, 100% UV-Schutz;  pflegeleicht, bügelleicht, körperformend)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'gruppe'         => [
            'labelName'    => 'Gruppe / Baukausten',
            'labelDescr'   => 'bezeichnet direkt zusammengehörige Artikel (z.B. Bikini-Oberteil "Aloha" und Bikini-Unterteil "Aloha" = Gruppe 1002918; Baukasten-Sakko "Ernie" und Baukasten-Hose "Bert" = Gruppe "E&B"). Dabei können auch mehr als 2 Artikel eine Gruppe bilden (z.B. Mix & Match: Gruppe "Hawaii" = 3 Bikini-Oberteile können mit 2 Bikini-Unterteilen frei kombiniert werden). Der Wert für eine Gruppe kann eine Nummer oder ein Name sein.',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'material'       => [
            'labelName'    => 'Material',
            'labelDescr'   => 'bezeichnet die genaue Materialzusammensetzung (z.B. 98% Baumwolle, 2% Elasthan)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'saison'         => [
            'labelName'    => 'Saison',
            'labelDescr'   => 'Beschreibt zu welcher Saison bzw. saisonalen Kollektion der Artikel gehört (z.B. HW18/19; Sommer 2018; Winter)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'serie'          => [
            'labelName'    => 'Serie',
            'labelDescr'   => 'Hier können Bezeichnungen für Serien übergeben werden, um Artikelfamilien oder Sondereditionen zu kennzeichnen (z.B. Expert Line, Mountain Professional)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'verschluss'     => [
            'labelName'    => 'Verschluss',
            'labelDescr'   => 'beschreibt Verschlussarten (z.B: Geknöpft, Reißverschluss,  Druckknöpfe, Klettverschluss; Haken & Öse)',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'produkt_url'    => [
            'propertyFeedName' => 'prop_url',
            'labelName'    => '',
            'labelDescr'   => '',
            'required'     => true,
            'configurable' => false,
            'forUpdate'    => true,
        ],
        'bilder'         => [
            'propertyFeedName' => 'images',
            'labelName'    => '',
            'labelDescr'   => '',
            'required'     => true,
            'configurable' => false,
            'forUpdate'    => true,
        ],
        'beschreibung'   => [
            'propertyFeedName' => 'prop_description',
            'labelName'    => 'Beschreibung HTML',
            'labelDescr'   => 'Der Beschreibungstext zum Artikel im HTML-Format',
            'required'     => true,
            'configurable' => true,
            'forUpdate'    => true,
        ],
        'beschreibung1'  => [
            'labelName'    => 'Beschreibung Text',
            'labelDescr'   => 'Der Beschreibungstext zum Artikel in Text-Format (automatische Konvertierung von HTML zu Text)',
            'required'     => true,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'beschreibung2'  => [
            'labelName'    => 'Alternativer Beschreibungstext',
            'labelDescr'   => 'Zusätzliche Informationen zum Produkt, technische Beschreibung, Kurzbeschreibung oder auch Keywords.',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
        'sonstiges'      => [
            'labelName'    => 'Sonstiges',
            'labelDescr'   => '',
            'required'     => false,
            'configurable' => true,
            'forUpdate'    => false,
        ],
    ];

    /**
     * Return all CSV field names in correct order
     *
     * @param bool get fields as sorted array (first: required; second; name)
     * @return array
     */
    public function getAllFields($blSorted = false)
    {
        if (!$blSorted) {
            return $this->_aEightselectFields;
        }

        if ($this->_aEightselectFieldsSorted !== null) {
            return $this->_aEightselectFieldsSorted;
        }

        $this->_aEightselectFieldsSorted = $this->_aEightselectFields;

        uasort($this->_aEightselectFieldsSorted, function ($a, $b) {
            $sALabel = str_replace(['Ä', 'Ö', 'Ü'], ['Aa', 'Oa', 'Ua'], $a['labelName']);
            $sBLabel = str_replace(['Ä', 'Ö', 'Ü'], ['Aa', 'Oa', 'Ua'], $b['labelName']);

            if ($a['required'] == $b['required']) {
                if ($sALabel == $sBLabel) {
                    return 0;
                }
                return $sALabel < $sBLabel ? -1 : 1;
            }
            return $a['required'] > $b['required'] ? -1 : 1;
        });

        return $this->_aEightselectFieldsSorted;
    }

    /**
     * @param string Field type (e.g. 'configurable' or 'forUpdate')
     * @return array
     */
    public function getFieldsByType($sFieldType, $blSorted = false)
    {
        if (isset($this->_aFieldsByType[$sFieldType])) {
            return $this->_aFieldsByType[$sFieldType];
        }

        $aFields = [];

        foreach ($this->getAllFields($blSorted) as $sName => $aFieldProps) {
            if ($aFieldProps[$sFieldType]) {
                $aFields[] = $sName;
            }
        }

        $this->_aFieldsByType[$sFieldType] = array_intersect_key($this->getAllFields($blSorted), array_flip($aFields));

        return $this->_aFieldsByType[$sFieldType];
    }
}