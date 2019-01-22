## Systemvoraussetzungen

### Kompatibel mit folgenden OXID eShop Versionen

- EE 5.0.x
- EE 5.1.x
- EE 5.2.x
- EE 5.3.x
- CE/PE 4.10.x

#### Ggf. kompatibel jedoch nicht getestet

- CE/PE 4.7.x
- CE/PE 4.8.x
- CE/PE 4.9.x

### Anforderungen an Webserver / MySQL / PHP / PHP Erweiterungen

Voraussetzung ist PHP 5.5.x.

Weitere Voraussetzungen ergeben sich aus denen der Oxid Version:

- [Oxid EE](https://docs.oxid-esales.com/eshop/de/5.3/installation/neu-installation/systemvoraussetzungen/systemvoraussetzungen-ee.html)
- [Oxid CE](https://docs.oxid-esales.com/eshop/de/5.3/installation/neu-installation/systemvoraussetzungen/systemvoraussetzungen-ce.html)
- [Oxid PE](https://docs.oxid-esales.com/eshop/de/5.3/installation/neu-installation/systemvoraussetzungen/systemvoraussetzungen-pe.html)

## Installation

1. Modul aus OXID eXchange laden.
1. Modul entpacken und in das Shopverzeichnis kopieren/hochladen.
1. via Composer `aws-sdk-php` installieren.

### Installation von aws-sdk-php

Das Modul benötigt für den Upload der Feeds das AWS SDK. Dieses muss im Shopverzeichnis unter im Ordner `vendor` installiert sein.

Mit diesem Modul erhalten Sie bereits eine `composer.json` für die nächsten Schritte. Wenn Sie diese bereits in das Shopverzeichnis kopiert haben, überspringen Sie die nächsten Code-Zeilen. Falls Sie eine eigene composer.json haben, muss vorab folgende Zeile hinzugefügt werden:

```
{
    "require": {
        "aws/aws-sdk-php": "^3.62"
    }
}
```

#### Composer herunterladen und installieren:

```
curl -sS https://getcomposer.org/installer | php
```

- Die benötigten Pakete (AWS SDK etc.) installieren. Dies erzeugt auch das korrekte "vendor" Unterverzeichnis:

```
php composer.phar install
```

## Modul aktivieren und konfigurieren

- Im OXID eShop Admin-Bereich in der Modul-Verwaltung das 8select-Modul auswählen und "Aktivieren"

![activate](./docs/oxid-activate.png)

- Unter dem Reiter "Einstell." müssen nun noch die API-ID und Feed-ID eingegeben werden

![config](./docs/oxid-config.png)

- Damit die 8select CSE die Produktdaten korrekt interpretieren kann, muss der Export konfiguriert werden
- Es müssen alle Produkteigenschaften als Größe markiert werden, die eine Größe definieren

![attribute-mapping](./docs/oxid-attribute-mapping.png)

## Produkt Export

Damit über die 8select CSE Produkt-Sets angeboten werden können, benötigt 8select ihre Produktdaten.
Diese werden von 8select aufbereitet und mit weiteren Attributen angereichert. Dies ermöglicht das Ausspielen der dynamischen Produkt-Sets.

Die Produktdaten müssen daher dauerhaft aktualisiert werden. Dies erfolgt mit Hilfe von Cronjobs, welche den Export im Hintergrund ausführen.

### Cronjobs auf dem Server anlegen

**Crontab aufrufen**

```
crontab -e
```

**Oxid Cronjob Aufruf einfügen**

```
/pfad/zu/php /pfad/zu/oxid/bin/eightselect_cron.php [arguments]
```

**Argumente**

```
-e=[Kommando]
-s=[Shop ID]
```

**Kommandos**

```
-e=export_full
-e=export_update
-e=export_upload_full
-e=export_upload_update
-e=upload_full
-e=upload_update
```

#### Beispiel

Voll-Export und Upload jeden Tag um 2 Uhr UTC.

```
0 2 * * * /usr/bin/php /opt/www/oxid/bin/eightselect_cron.php -e=export_upload_full -s=1 >/dev/null 2>&1
```

### Manueller Export

Wenn Sie den Cronjob eingerichtet haben, wird der Export entsprechend automatisch ausgeführt.
Für den initialen Export kann dieser auch manuell gestartet werden. Dies dauert unter Umständen jedoch sehr lange, da der manuelle Export nicht im Hintergrund gestartet wird.

![export](./docs/oxid-export.png)

## Deinstallation

1. Das Modul im OXID eSHOP Admin-Bereich in der Modul-Verwaltung deaktivieren
2. Das Verzechnis "modules/asign/8select" löschen
3. Folgende Datenbank-Tabellen löschen:

   - eightselect_attribute2oxid
   - eightselect_attributes
   - eightselect_log

## Changelog

Siehe [CHANGELOG](https://github.com/8select/oxid-plugin-sob/blob/master/CHANGELOG.md).
