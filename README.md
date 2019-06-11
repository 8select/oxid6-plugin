# 8select (CSE) Module

## Installation

1. Modul herunterladen: [Download from GitLab](https://gitlab.com/a-sign/oxid-modules/8select/-/archive/master/8select-master.zip)
2. Modul entpacken und in das Shopverzeichnis source/moduls/asign/8select kopieren/hochladen
3. Composer-Autoload-Pfad eintragen in Shoproot composer.json
In der Node für autoload PSR-4 muss der folgende Eintrag hinzugefügt werden:
```
  "autoload": {
    "psr-4": {
      "ASign\\EightSelect\\": "./source/modules/asign/8select"
    }
  }
```
4. Anschließend muss der Composer Autoloader upgedatet werden: `composer dumpautoload`

## Activation & Configuration

- Im OXID eSHOP Admin-Bereich in der Modul-Verwaltung das 8select-Modul auswählen und "Aktivieren"
- Unter dem Reiter "Einstell." müssen nun noch die API-ID und Feed-ID eingegeben werden
- Weitere Konfigurationsmöglichkeiten entnehmen Sie der Anleitung

## Uninstall

1. Das Modul im OXID eSHOP Admin-Bereich in der Modul-Verwaltung deaktivieren
2. Das Verzechnis "modules/asign/8select" löschen
3. Folgende Datenbank-Tabellen löschen:
    - eightselect_log
    
## Changelog

Please see the [CHANGELOG file](/CHANGELOG.md).
