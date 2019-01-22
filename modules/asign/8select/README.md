# 8select (CSE) Module

## Installation

1. Modul herunterladen: [Download from GitLab](https://gitlab.com/a-sign/oxid-modules/8select/-/archive/master/8select-master.zip)
2. Modul entpacken und in das Shopverzeichnis kopieren/hochladen

## Requirements: AWS SDK

Das Modul benötigt für den Upload der Feeds das AWS SDK. Dies muss im Shopverzeichnis unter "vendor/" installiert sein.

Mit diesem Modul erhalten Sie bereits eine composer.json für die nächsten Schritte. Wenn Sie diese bereits in das Shopverzeichnis kopiert haben, überspringen Sie die nächsten Code-Zeilen. Falls Sie eine eigene composer.json haben, muss vorab folgende Zeile hinzugefügt werden:

```
{
    "require": {
        "aws/aws-sdk-php": "^3.62"
    }
}
```

- Composer herunterladen und installieren:

```
curl -sS https://getcomposer.org/installer | php
```

- Die benötigten Pakete (AWS SDK etc.) installieren. Dies erzeugt auch das korrekte "vendor" Unterverzeichnis:

```
php composer.phar install
```

## Activation & Configuration

- Im OXID eSHOP Admin-Bereich in der Modul-Verwaltung das 8select-Modul auswählen und "Aktivieren"
- Unter dem Reiter "Einstell." müssen nun noch die API-ID und Feed-ID eingegeben werden
- Weitere Konfigurationsmöglichkeiten entnehmen Sie der Anleitung

## Uninstall

1. Das Modul im OXID eSHOP Admin-Bereich in der Modul-Verwaltung deaktivieren
2. Das Verzechnis "modules/asign/8select" löschen
3. Folgende Datenbank-Tabellen löschen:
    - eightselect_attribute2oxid
    - eightselect_attributes
    - eightselect_log
    
## Create Cronjobs

Command line:
```
php bin/eightselect_cron.php [arguments]
```

* Arguments:  
-e=[Command name]  
-s=[Shop ID]  
 
* Command names:  
-e=export_full  
-e=export_update  
-e=export_upload_full  
-e=export_upload_update  
-e=upload_full  
-e=upload_update  
  
Beispiel:
```
php bin/eightselect_cron.php -e=export_upload_full -s=1
```

## Changelog

Please see the [CHANGELOG file](/CHANGELOG.md).
